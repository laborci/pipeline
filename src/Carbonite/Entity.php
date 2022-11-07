<?php namespace Atomino2\Carbonite;

use Atomino2\Carbonite\Carbonizer\Accessor\GetSet;
use Atomino2\Carbonite\Carbonizer\Accessor\Handler;
use Atomino2\Carbonite\Carbonizer\Accessor\Relation;
use Atomino2\Carbonite\Carbonizer\Carbonite;
use Atomino2\Carbonite\Carbonizer\Model;
use Atomino2\Carbonite\Event\BeforeDelete;
use Atomino2\Carbonite\Event\BeforeInsert;
use Atomino2\Carbonite\Event\BeforeUpdate;
use Atomino2\Carbonite\Event\EventInterface;
use Atomino2\Carbonite\Event\OnDelete;
use Atomino2\Carbonite\Event\OnInsert;
use Atomino2\Carbonite\Event\OnUpdate;
use Atomino2\Carbonite\PropertyHandler\PropertyHandler;
use Atomino2\Carbonite\PropertyHandler\PropertyHandlerExportInterface;
use Atomino2\Carbonite\PropertyHandler\PropertyHandlerImportInterface;
use Atomino2\Carbonite\Validation\CarboniteValidationException;
use Atomino2\Carbonite\Validation\EntityValidationException;
use Atomino2\Carbonite\Validation\PropertyValidationException;
use Atomino2\Carbonite\Validation\UniqueConstraintViolationException;
use JsonSerializable;
use Respect\Validation\Exceptions\ValidatorException;

/**
 * @property-read int $id
 */
abstract class Entity implements JsonSerializable {

	const id = 'id';
	private ?Model $model = null;

	/** @var array */
	private array $eventListeners = [];

	private array $properties = [];
	private array $dirty      = [];

	/** @var PropertyHandler[] */
	private array $handlers = [];
	/** @var (bool|string)[] */
	private array $setters = [];
	/** @var (bool|string|\Closure)[] */
	private array        $getters = [];
	private ?EntityStore $store   = null;

	#region getters
	public function isExists(): bool { return (bool)$this->get(self::id); }
	public function isDirty(): bool { return (bool)count($this->dirty); }
	public function isFieldDirty(string $field): bool { return array_key_exists($field, $this->dirty); }
	protected function getModel(): Model { return $this->model; }
	protected function getStore(): ?EntityStore { return $this->store; }
	#endregion

	#region setup
	abstract protected static function carbonize(): Carbonite;

	private function __setup(EntityStore $store, Model $model, ?array $properties): static {
		$this->model = $model;
		$this->store = $store;
		$this->properties = !is_null($properties) ? $properties : $model->getDefaults();
		foreach ($model->getAccessors() as $name) {
			$accessor = $model->getAccessor($name);
			if ($accessor instanceof Handler) {
				$method = $accessor->getMethod();
				$handler = $this->$method();
				\Closure::bind(fn($entity, $property, $value) => $this->__setup($entity, $property, $value), $handler, PropertyHandler::class)($this, $name, $this->get($name));
				$this->handlers[$name] = $handler;
			} elseif ($accessor instanceof GetSet) {
				if (($method = $accessor->getGetMethod()) !== false) $this->getters[$name] = $method;
				if (($method = $accessor->getSetMethod()) !== false) $this->setters[$name] = $method;
			} elseif ($accessor instanceof Relation) {
				$this->getters[$name] = fn() => $accessor->get($this->get($accessor->getIdKey()), $this->store->getDi());
			}
		}
		$this->initialize();
		return $this;
	}
	protected function initialize(): void { }
	#endregion

	#region property handling
	public function __get(string $name): mixed {
		if (array_key_exists($name, $this->handlers)) return $this->handlers[$name];
		elseif (array_key_exists($name, $this->getters)) {
			$method = $this->getters[$name];
			if ($method === true) return $this->get($name);
			if (is_string($method)) return $this->$method();
			else return $method();
		} else throw new \Exception(sprintf('THERE IS NO GETTER FOR PROPERTY %s in class %s', $name, static::class), E_USER_ERROR);
	}
	public function __set(string $name, mixed $value) {
		if (array_key_exists($name, $this->setters)) ($method = $this->setters[$name]) === true ? $this->set($name, $value) : $this->$method($value);
		else throw new \Exception('THERE IS NO SETTER FOR PROPERTY ' . static::class . '::' . $name, E_USER_ERROR);
	}

	protected function get($name): mixed {
		if (!$this->model->hasProperty($name)) throw new \Exception(sprintf("trying to GET not existing property (%s::%s)", static::class, $name), E_USER_ERROR);
		return $this->properties[$name];
	}
	protected function set($name, $newValue): void {
		if (!$this->model->hasProperty($name)) throw new \Exception(sprintf("trying to SET not existing property (%s::%s)", static::class, $name), E_USER_ERROR);
		if ($this->properties[$name] !== $newValue) $this->dirty[$name] = true;
		$this->properties[$name] = $newValue;
	}
	private function getPropertyHandlerValues(): void {
		foreach ($this->handlers as $name => $handler) {
			$value = \Closure::bind(fn() => $this->getValue(), $handler, PropertyHandler::class)();
			$this->set($name, $value);
		}
	}
	#endregion

	#region Validation
	private function createValidationException(): CarboniteValidationException { return new CarboniteValidationException(static::class, $this->id); }
	protected function validateEntity(): null|EntityValidationException { return null; }
	private function validateProperties(CarboniteValidationException $exception) {
		foreach ($this->model->getProperties() as $name) {
			$property = $this->model->getProperty($name);
			try {
//				debugf('validating %s value=%s type=%s', $name, $this->get($name), gettype($this->get($name)));
				$property->assert($this->get($name), '{{property}}');
			} catch (ValidatorException $validatorException) {
				$messages = [];
				foreach ($validatorException->getChildren() as $child) $messages[] = $child->getMessage();
				$exception->addPropertyValidationException(new PropertyValidationException($name, ...$messages));
			}
		}
	}
	/**
	 * @throws CarboniteValidationException
	 */
	public final function validate() {
		$exception = $this->createValidationException();
		$this->validateProperties($exception);
		$exception->setEntityValidationException($this->validateEntity());
		if (!$exception->isValid()) throw $exception;
	}
	#endregion

	#region event handling
	protected function fireEvent(EventInterface $event): EventInterface {
		if (array_key_exists(get_class($event), $this->eventListeners)) {
			foreach ($this->eventListeners[get_class($event)] as $handler) {
				$handler($event);
				if ($event->isCancelled()) break;
			}
		}
		return $event;
	}
	protected function addEventListener(string|array $events, \Closure $handler): void {
		if (is_string($events)) $events = [$events];
		foreach ($events as $event) {
			if (!array_key_exists($event, $this->eventListeners)) $this->eventListeners[$event] = [];
			$this->eventListeners[$event][] = $handler;
		}
	}
	#endregion

	#region export / import
	public function import(array $json): void {
		foreach ($json as $name => $value) {
			if (array_key_exists($name, $this->setters)) {
				if ($this->setters[$name] === true) $this->__set($name, $this->model->getProperty($name)->import($value));
				else $this->__set($name, $value);
			}
			if (array_key_exists($name, $this->handlers) && $this->handlers[$name] instanceof PropertyHandlerImportInterface) $this->handlers[$name]->import($value);
		}
	}

	public function export(string ...$properties): array {
		$export = [];
		if (count($properties) === 0) $properties = $this->model->getAccessors();
		foreach ($properties as $name) {
			if (array_key_exists($name, $this->getters)) {
				if ($this->getters[$name] === true) return $this->model->getProperty($name)->export($this->__get($name));
				else $this->__get($name);
			}
			if (array_key_exists($name, $this->handlers) && $this->handlers[$name] instanceof PropertyHandlerExportInterface) $this->handlers[$name]->export();
		}
		return $export;
	}

	public function jsonSerialize(): array { return $this->export(); }
	#endregion

	#region crud

	public function save($force = false): ?int {
		$this->validate();
		$this->getPropertyHandlerValues();
		try {
			if ($this->isExists()) {
				if ($this->isDirty() || $force) {
					if ($this->fireEvent(new BeforeUpdate())->isCancelled()) return null;
					$this->getStore()->update($this);
					$this->fireEvent(new OnUpdate());
				}
			} else {
				if ($this->fireEvent(new BeforeInsert())->isCancelled()) return null;
				$id = $this->getStore()->insert($this);
				if (is_int($id)) $this->set(self::id, $id);
				$this->fireEvent(new OnInsert());
			}
			return $this->id;
		} catch (\PDOException $exception) {
			if ($exception->errorInfo[1] == 1062) {
				preg_match("/\.(\S+)'$/", $exception->getMessage(), $matches);
				$unique = $matches[1];
				$validationException = new CarboniteValidationException(static::class, $this->id);
				$validationException->setUniqueConstrainViolationException(new UniqueConstraintViolationException($unique, ...$this->model->getUnique($unique)));
				throw $validationException;
			} else {
				throw $exception;
			}
		}
	}

	public function delete(): bool {
		if (!$this->isExists()) return false;
		if ($this->fireEvent(new BeforeDelete())->isCancelled()) return false;
		$result = $this->store->delete($this);
		if ($result) {
			$event = new OnDelete($this->id);
			$this->set(self::id, null);
			$this->fireEvent($event);
		}
		return $result;
	}

	protected function saveProperty(string $property): bool {
		if (!$this->isExists()) return false;
		$this->getPropertyHandlerValues();
		if ($this->isDirty()) $this->store->updateProperty($this->id, $property, $this->get($property));
		return true;
	}
	#endregion
}