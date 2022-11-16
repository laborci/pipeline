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
use DI\Container;
use JsonSerializable;
use Respect\Validation\Exceptions\ValidatorException;
use Symfony\Component\Console\EventListener\ErrorListener;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @property-read int $id
 */
abstract class Entity implements JsonSerializable {

	private const id = 'id';
	private ?Model $model = null;
	/** @var array */
//	private array $eventListeners = [];
	private array $properties = [];
	private array $dirty      = [];
	/** @var PropertyHandler[] */
	private array $handlers = [];
	/** @var (bool|string)[] */
	private array $setters = [];
	/** @var (bool|string|\Closure)[] */
	private array           $getters = [];
	private EntityStore     $store;
	private EventDispatcher $itemEventDispatcher;
	private EventDispatcher $eventDispatcher;

	#region getters

	/**
	 * Checks if the object was inserted to database before
	 * (It checks the id property)
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function isExists(): bool { return (bool)$this->get(self::id); }
	/**
	 * Checks if any of the properties was modified after creation
	 *
	 * @return bool
	 */
	public function isDirty(): bool { return (bool)count($this->dirty); }
	/**
	 * Checks if the property was modified after creation
	 *
	 * @param string $field
	 * @return bool
	 */
	public function isFieldDirty(string $field): bool { return array_key_exists($field, $this->dirty); }
	/**
	 * Returns the model object
	 *
	 * @return Model
	 */
	protected function getModel(): Model { return $this->model; }
	/**
	 * Returns the store for the entity
	 *
	 * @return EntityStore
	 */
	protected function getStore(): EntityStore { return $this->store; }
	#endregion

	#region setup

	/**
	 * This method will be called by the model to make final touches
	 * @return Carbonite
	 */
	abstract protected static function carbonize(): Carbonite;
	/**
	 * This method will be called right after the creation of the object
	 * You can add extra initializations here, for example: add event listeners
	 *
	 * @return void
	 */
	protected function initialize(): void { }
	private function __setup(EntityStore $store, Model $model, EventDispatcher $eventDispatcher, array $properties): static {
		$this->model = $model;
		$this->store = $store;
		$this->properties = $properties;
		$this->itemEventDispatcher = new EventDispatcher();
		$this->eventDispatcher = $eventDispatcher;

		foreach ($model->getAccessors() as $name) {
			$accessor = $model->getAccessor($name);
			if ($accessor instanceof GetSet) {
				if (($method = $accessor->getGetMethod()) !== false) $this->getters[$name] = $method;
				if (($method = $accessor->getSetMethod()) !== false) $this->setters[$name] = $method;
			} elseif ($accessor instanceof Relation) {
				/** @var int|array $idKey */
				$key = $this->get($accessor->getIdKey());
				/** @var EntityStore $store */
				$store = $this->store->getDi()->get($accessor->getStore());
				$this->getters[$name] = fn() => $accessor->get($key, $store);
			}
		}

		foreach ($model->getAccessors() as $name) {
			$accessor = $model->getAccessor($name);
			if ($accessor instanceof Handler) {
				$method = $accessor->getMethod();
				$handler = $this->$method();
				\Closure::bind(fn($entity, $property, $value) => $this->__setup($entity, $property, $value), $handler, PropertyHandler::class)($this, $name, $this->get($name));
				$this->handlers[$name] = $handler;
			}
		}

		$this->initialize();
		return $this;
	}
	#endregion

	#region property handling
	public function __get(string $name): mixed {
		if (array_key_exists($name, $this->handlers)) {
			return $this->handlers[$name];
		} elseif (array_key_exists($name, $this->getters)) {
			$method = $this->getters[$name];
			if ($method === true) return $this->get($name);
			if (is_string($method)) return $this->$method();
			else return $method();
		} else {
			throw new \Exception(sprintf('THERE IS NO GETTER FOR PROPERTY %s in class %s', $name, static::class), E_USER_ERROR);
		}
	}
	public function __set(string $name, mixed $value) {
		if (array_key_exists($name, $this->setters)) ($method = $this->setters[$name]) === true ? $this->set($name, $value) : $this->$method($value);
		else throw new \Exception('THERE IS NO SETTER FOR PROPERTY ' . static::class . '::' . $name, E_USER_ERROR);
	}

	/**
	 * Gets a property value directly
	 * Use it from getters
	 *
	 * @param $name
	 * @return mixed
	 * @throws \Exception
	 */
	protected final function get($name): mixed {
		if (!$this->model->hasProperty($name)) throw new \Exception(sprintf("trying to GET not existing property (%s::%s)", static::class, $name), E_USER_ERROR);
		return $this->properties[$name];
	}
	/**
	 * Sets a property value directly
	 * Use it from setters
	 *
	 * @param $name
	 * @param $newValue
	 * @return void
	 * @throws \Exception
	 */
	protected final function set($name, $newValue): void {
		if (!$this->model->hasProperty($name)) throw new \Exception(sprintf("trying to SET not existing property (%s::%s)", static::class, $name), E_USER_ERROR);
		if ($this->properties[$name] !== $newValue) $this->dirty[$name] = true;
		$this->properties[$name] = $newValue;
	}
	private function getPropertyHandlerValues(string|null $property = null): void {
		foreach ($this->handlers as $name => $handler) if ($property === null || $name === $property) {
			$value = \Closure::bind(fn() => $this->getValue(), $handler, PropertyHandler::class)();
			$this->set($name, $value);
		}
	}
	#endregion

	#region Validation
	/**
	 * A place to implement the complex validation of the entity
	 * On validation error it should throw an EntityValidationException
	 *
	 * @throws EntityValidationException
	 */
	protected function validateEntity(): void { }

	/**
	 * @throws PropertyValidationException
	 */
	private function validateProperty(string $name) {
		$property = $this->model->getProperty($name);
		try {
			$property->assert($this->get($name), '{{property}}');
		} catch (ValidatorException $validatorException) {
			$messages = [];
			foreach ($validatorException->getChildren() as $child) $messages[] = $child->getMessage();
			throw new PropertyValidationException($name, ...$messages);
		}
	}

	/**
	 * This will validate the properties of your entity individually, and also performs the complex entity validation
	 *
	 * @throws CarboniteValidationException
	 */
	public final function validate() {
		$exception = null;
		foreach ($this->model->getProperties() as $name) {
			try {
				$this->validateProperty($name);
			} catch (PropertyValidationException $propertyValidationException) {
				$exception = $exception ?? new CarboniteValidationException(static::class, $this->id);
				$exception->addPropertyValidationException($propertyValidationException);
			}
		}

		try {
			$this->validateEntity();
		} catch (EntityValidationException $entityValidationException) {
			$exception = $exception ?? new CarboniteValidationException(static::class, $this->id);
			$exception->setEntityValidationException($entityValidationException);
		}
		if (!is_null($exception)) throw $exception;
	}
	#endregion

	#region event handling
	/**
	 * Fires an Atomino2\Carbonite\Event\EventInterface event to the entity, and the property handlers
	 *
	 * @param EventInterface $event
	 * @return EventInterface
	 */
	protected function dispatchEvent(EventInterface $event): EventInterface {
		$event = $this->itemEventDispatcher->dispatch($event);
		if (!$event->isCancelled()) $this->eventDispatcher->dispatch($event);
		return $event;
//		if (array_key_exists(get_class($event), $this->eventListeners)) {
//			foreach ($this->eventListeners[get_class($event)] as $handler) {
//				$handler($event);
//				if ($event->isCancelled()) break;
//			}
//		}
//		return $event;
	}

	/**
	 * Add an event listener EntityEvents (Atomino2\Carbonite\Event\EventInterface)
	 *
	 * @param string|array $events
	 * @param \Closure $handler
	 * @return void
	 */
	protected function addEventListener(string|array $events, \Closure $handler): void {
		if (is_string($events)) $events = [$events];
		foreach ($events as $event) $this->itemEventDispatcher->addListener($event, $handler);
//		foreach ($events as $event) {
//			if (!array_key_exists($event, $this->eventListeners)) $this->eventListeners[$event] = [];
//			$this->eventListeners[$event][] = $handler;
//		}
	}
	#endregion

	#region export / import
	/**
	 * Imports values typically from json
	 * You can specify the properties to import
	 *
	 * @param array $json
	 * @return void
	 * @throws \Exception
	 */
	public function import(array $json, string ...$properties): void {
		foreach ($json as $name => $value) if (count($properties) === 0 || in_array($name, $properties)) {
			if (array_key_exists($name, $this->setters)) {
				if ($this->setters[$name] === true) $this->__set($name, $this->model->getProperty($name)->import($value));
				else $this->__set($name, $value);
			}
			if (array_key_exists($name, $this->handlers) && $this->handlers[$name] instanceof PropertyHandlerImportInterface) $this->handlers[$name]->import($value);
		}
	}

	/**
	 * Exports values typically to json
	 * You can specify the properties to export
	 *
	 * @param string ...$properties
	 * @return array
	 * @throws \Exception
	 */
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

	/**
	 * Calls the export() method
	 * @return array
	 * @throws \Exception
	 */
	public function jsonSerialize(): array { return $this->export(); }
	#endregion

	#region crud
	/**
	 * Persists the object to database
	 * If the object has an id, it will update, otherwise it will insert it
	 * You can interact with the BeforeUpdate and BeforeInsert EntityEvents
	 * You can get notitification with the OnUpdate and OnInsert EntityEvents
	 *
	 * @param $force
	 * @return int|null
	 * @throws CarboniteValidationException
	 */
	public function save($force = false): ?int {
		$this->validate();
		$this->getPropertyHandlerValues();
		try {
			if ($this->isExists()) {
				if ($this->isDirty() || $force) {
					if ($this->dispatchEvent(new BeforeUpdate($this))->isCancelled()) return null;
					$this->getStore()->update($this);
					$this->dirty = [];
					$this->dispatchEvent(new OnUpdate($this));
				}
			} else {
				if ($this->dispatchEvent(new BeforeInsert($this))->isCancelled()) return null;
				$id = $this->getStore()->insert($this);
				if (is_int($id)) $this->set(self::id, $id);
				$this->dirty = [];
				$this->dispatchEvent(new OnInsert($this));
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
	/**
	 * Deletes the object from database
	 * You can interact with the BeforeDelete EntityEvent
	 * You can get notitification with the OnDelete EntityEvent
	 *
	 * Returns false on error or cancellation, or the is what was previously set
	 *
	 * @return false|int
	 * @throws \Exception
	 */
	public function delete(): false|int {
		if (!$this->isExists()) return false;
		if ($this->dispatchEvent(new BeforeDelete($this))->isCancelled()) return false;
		if ($this->store->delete($this)) {
			$id = $this->id;
			$this->set(self::id, null);
			$this->dispatchEvent(new OnDelete($this, $id));
			return $id;
		}
		return false;
	}

	/**
	 * Updtes an individual property to database.
	 * Works only when the object already has an ID
	 *
	 * @param string $property
	 * @param bool $force
	 * @return bool
	 * @throws \Exception
	 */
	protected function saveProperty(string $property, bool $force = false): bool {
		if (!$this->isExists()) return false;
		$this->getPropertyHandlerValues($property);
		if ($this->isFieldDirty($property) || $force) $this->store->updateProperty($this, $property, $this->get($property));
		unset($this->dirty[$property]);
		return true;
	}
	#endregion
}