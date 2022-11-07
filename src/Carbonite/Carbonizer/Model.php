<?php namespace Atomino2\Carbonite\Carbonizer;

use Atomino2\Carbonite\Carbonizer\Accessor\GetSet;
use Atomino2\Carbonite\Carbonizer\Accessor\Handler;
use Atomino2\Carbonite\Carbonizer\Accessor\Relation;
use Atomino2\Carbonite\Carbonizer\Property\JsonProperty;
use Atomino2\Database\Connection;
use DI\Container;

class Model {

	const HANDLE = "handle";
	const GET    = "get";
	const SET    = "set";
	const TYPE   = "type";
	const METHOD = "method";

	protected string $entity;
	protected string $connection;
	protected string $table;
	protected bool   $mutable;
	protected array  $uniques;
	/** @var Property[] */
	protected array $properties;
	/** @var Accessor[] */
	protected array $accessors;

	public function getEntity(): string { return $this->entity; }
	public function getConnection(): string { return $this->connection; }
	public function getTable(): string { return $this->table; }
	public function isMutable(): bool { return $this->mutable; }
	/**
	 * @return string[]
	 */
	public function getDefaults(): array {
		$defaults = [];
		foreach ($this->properties as $name => $property) $defaults[$name] = $property->getDefault();
		return $defaults;
	}

	public function getProperties(): array { return array_keys($this->properties); }
	public function hasProperty(string $name): bool { return array_key_exists($name, $this->properties); }
	public function getProperty(string $name): null|Property { return array_key_exists($name, $this->properties) ? $this->properties[$name] : null; }

	public function getUniques(): array { return array_keys($this->uniques); }
	public function hasUnique(string $name): bool { return array_key_exists($name, $this->uniques); }
	public function getUnique(string $name): array|null { return array_key_exists($name, $this->uniques) ? $this->uniques[$name] : null; }

	public function getAccessors(): array { return array_keys($this->accessors); }
	public function hasAccessor(string $name): bool { return array_key_exists($name, $this->accessors); }
	public function getAccessor(string $name): Accessor|null { return array_key_exists($name, $this->accessors) ? $this->accessors[$name] : null; }

	public function build(array $record): array {
		$properties = [];
		foreach ($record as $name => $value) $properties[$name] = $this->getProperty($name)?->build($value);
		return $properties;
	}
	public function store(array $properties): array {
		$record = [];
		foreach ($properties as $name => $value) $record[$name] = $this->getProperty($name)?->store($value);
		return $record;
	}

	public function __construct(
		string    $entity,
		Carbonite $carbonite,
		Container $di
	) {
		$this->entity = $entity;
		$this->mutable = $carbonite->mutable;
		$this->connection = $carbonite->connection;
		$this->table = $carbonite->table;

		/** @var Connection $database */
		$database = $di->get($carbonite->connection);
		$table = $database->getSmartStructure()->getTable($carbonite->table);
		$this->uniques = $table->getUniques();
		$this->properties = $this->collectProperties($table->getFieldData(), $carbonite, $di->get(PropertyFactory::class));
		$this->accessors = $this->createAccessors();
	}

	private function createAccessors(): array {
		$magicMethods = $this->collectMagicMethods();
		$accessors = [];
		// PROPERTY ACCESSORS
		foreach ($this->properties as $name => $property) {
			$access = $property->getAccess();
			if (array_key_exists($name, $magicMethods)) {
				$methods = $magicMethods[$name];
				$hasHandler = array_key_exists(self::HANDLE, $methods);
				$hasGetter = array_key_exists(self::GET, $methods);
				$hasSetter = array_key_exists(self::SET, $methods);
				if ($hasHandler) {
					$accessors[$name] = new Handler($methods[self::HANDLE][self::METHOD], $methods[self::HANDLE][self::TYPE]);
				} else {
					if ($hasGetter && $hasSetter && $methods[self::GET][self::TYPE] !== $methods[self::SET][self::TYPE]) trigger_error(sprintf("%s %s getter and setter type must be identical", $this->entity, $name), E_USER_ERROR);
					if ($hasGetter && !$hasSetter && $access & Access::WRITE && $methods[self::GET][self::TYPE] !== $property->getDataType()) trigger_error(sprintf("%s %s getter and property type must be identical", $this->entity, $name), E_USER_ERROR);
					if (!$hasGetter && $hasSetter && $access & Access::READ && $methods[self::SET][self::TYPE] !== $property->getDataType()) trigger_error(sprintf("%s %s setter and property type must be identical", $this->entity, $name), E_USER_ERROR);
					$accessors[$name] = new GetSet(
						$hasGetter ? $methods[self::GET][self::METHOD] : (bool)($access & Access::READ),
						$hasSetter ? $methods[self::SET][self::METHOD] : (bool)($access & Access::WRITE),
						$hasGetter ? $methods[self::GET][self::TYPE] : $methods[self::SET][self::TYPE]
					);
				}
			} else {
				$accessors[$name] = new GetSet(
					(bool)($access & Access::READ),
					(bool)($access & Access::WRITE),
					$property->getDataType(),
				);
			}
		}
		// VIRTUAL ACCESSORS
		foreach ($magicMethods as $name => $methods) {
			if (!array_key_exists($name, $accessors)) {
				$hasHandler = array_key_exists(self::HANDLE, $methods);
				$hasGetter = array_key_exists(self::GET, $methods);
				$hasSetter = array_key_exists(self::SET, $methods);
				if ($hasHandler) {
					$accessors[$name] = new Handler($methods[self::HANDLE][self::METHOD], $methods[self::HANDLE][self::TYPE]);

				} else {
					if ($hasGetter && $hasSetter && $methods[self::GET][self::TYPE] !== $methods[self::SET][self::TYPE]) trigger_error(sprintf("%s %s getter and setter type must be identical", $this->entity, $name), E_USER_ERROR);
					$accessors[$name] = new GetSet(
						$hasGetter ? $methods[self::GET][self::METHOD] : false,
						$hasSetter ? $methods[self::SET][self::METHOD] : false,
						$hasGetter ? $methods[self::GET][self::TYPE] : $methods[self::SET][self::TYPE]
					);

				}
			}
		}

		return $accessors;
	}

	/**
	 * @param array $fields
	 * @param Carbonite $carbonite
	 * @param PropertyFactory $propertyFactory
	 * @return Property[]
	 */
	private function collectProperties(array $fields, Carbonite $carbonite, PropertyFactory $propertyFactory): array {
		$properties = [];
		foreach ($fields as $field) {
			$field = new Field($field);
			$propPreset = $carbonite->getPropertyPreset($field->name);
			$property = $propertyFactory->factory($field, $propPreset['persist'], $propPreset['access'], $propPreset['default']);
			if (!is_null($property)) $properties[$property->getName()] = $property;
		}
		return $properties;
	}

	private function collectMagicMethods(): array {
		$reflection = new \ReflectionClass($this->entity);
		$methods = $reflection->getMethods();
		$magic = [];
		foreach ($methods as $method) {
			$methodName = $method->name;
			$getPattern = '__' . self::GET;
			$setPattern = '__' . self::SET;
			$handlePattern = '__' . self::HANDLE;
			if (str_starts_with($methodName, $getPattern) && $methodName !== $getPattern) {
				$property = lcfirst(substr($methodName, strlen($getPattern)));
				if (!array_key_exists($property, $magic)) $magic[$property] = [];
				$magic[$property][self::GET] = [self::METHOD => $method->name, self::TYPE => (string)$method->getReturnType()];
			} elseif (str_starts_with($methodName, $setPattern) && $methodName !== $setPattern) {
				$property = lcfirst(substr($methodName, strlen($setPattern)));
				if (!array_key_exists($property, $magic)) $magic[$property] = [];
				$magic[$property][self::SET] = [self::METHOD => $method->name, self::TYPE => (string)$method->getParameters()[0]->getType()];
			} elseif (str_starts_with($methodName, $handlePattern) && $methodName !== $handlePattern) {
				$property = lcfirst(substr($methodName, strlen($handlePattern)));
				if (!array_key_exists($property, $magic)) $magic[$property] = [];
				$magic[$property][self::HANDLE] = [self::METHOD => $method->name, self::TYPE => (string)$method->getReturnType()];
			}
		}
		return $magic;
	}

	public function __wakeup() {
		/** @var Carbonite $carbonite */
		$carbonite = \Closure::bind(fn($e) => $e::carbonize(), null, $this->entity)($this->entity);
		foreach ($this->properties as $name => $property) {
			$propPreset = $carbonite->getPropertyPreset($name);
			if (!is_null($propPreset['validator'])) {
				\Closure::bind(fn($validatorDecorator) => $this->validatorDecorator = $validatorDecorator, $property, Property::class)($propPreset['validator']);
			}
		}
	}
}