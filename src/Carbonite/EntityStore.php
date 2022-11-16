<?php namespace Atomino2\Carbonite;


use Atomino2\Carbonite\Carbonizer\CarbonizedModel;
use Atomino2\Carbonite\Carbonizer\Model;
use Atomino2\Carbonite\Carbonizer\Persist;
use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\Select\Filter;
use Atomino2\Database\SmartSQL\SQL;
use DI\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class EntityStore {

	protected const entity = "";

	protected Container $di;
	private Model       $model;
	private Connection  $connection;
	/** @var Entity[] */
	private array $cache = [];
	private EventDispatcher $eventDispatcher;

	public function getDi(): Container { return $this->di; }
	public function getTable(): string { return $this->model->getTable(); }
	public function isMutable(): bool { return $this->model->isMutable(); }
	public function getConnection(): Connection { return $this->connection; }

	public function __construct(Container $di) {
		$this->di = $di;
		$this->model = (new \ReflectionClass(static::entity))->getParentClass()->getAttributes(CarbonizedModel::class)[0]->newInstance()->getModel();
		$this->model->initialize($di);
		$this->connection = $di->get($this->model->getConnection());
		$this->eventDispatcher = $di->get(EventDispatcher::class);
	}

	/**
	 * Creates a new blank entity. Always use this method of the store to create new object!
	 *
	 * @return Entity
	 */
	public final function create(): Entity {
		$properties = $this->model->getDefaults();
		$properties['id'] = null;
		return $this->createAndLoadProperties($properties);
	}

	private function createAndLoadProperties(array $properties): Entity {
		/** @var Entity $item */
		$item = $this->di->make(static::entity);
		$object = \Closure::bind(fn($store, $model, $eventDispatcher, $properties) => $this->__setup($store, $model, $eventDispatcher, $properties), $item, Entity::class)($this, $this->model, $this->eventDispatcher, $properties);
		if ($object->id !== null) $this->cache[$object->id] = $object;
		return $object;
	}

	/**
	 * Creates an entity from a record
	 * Should not be called directly
	 *
	 * @param array|null $record
	 * @return Entity|null
	 */
	public function createAndLoadRecord(array|null $record): null|Entity { return is_null($record) ? null : $this->createAndLoadProperties($this->model->build($record)); }

	private function getEntityProperties(Entity $item) { return \Closure::bind(fn() => $this->properties, $item, Entity::class)(); }

	/**
	 * Start a search
	 *
	 * @param ?Filter $filter
	 * @return EntityFinder
	 */
	public function search(?Filter $filter): EntityFinder { return new EntityFinder($this, $filter); }


	/**
	 * Get one entity by id
	 *
	 * @param int $id
	 * @return Entity|null
	 */
	public function pick(int $id): ?Entity {
		if (array_key_exists($id, $this->cache)) return $this->cache[$id];
		$record = $this->connection->getSmartQuery()->getRowById($this->getTable(), $id);
		return $this->createAndLoadRecord($record);
	}

	/**
	 * Collects entites by id list
	 * You can specify the order
	 *
	 * @param array $ids
	 * @param string|array $order
	 * @return array
	 */
	public function collect(array $ids, string|array ...$order): array {
		$result = [];
		foreach ($ids as $key => $id) if (array_key_exists($id, $this->cache)) {
			$result[] = $this->cache[$id];
			unset($ids[$key]);
		}
		if (count($ids)) {
			$this->search(SQL::filter(SQL::cmp('id', $ids)))->order(...$order)->get();
			$records = $this->connection->getSmartQuery()->getRowsById($this->getTable(), $ids);
		} else {
			$records = [];
		}
		return array_merge(array_map(fn($record) => $this->createAndLoadProperties($this->model->build($record)), $records), $result);
	}

	/**
	 * Deletes the entity from the database
	 * Should not be called directly
	 *
	 * @param Entity $item
	 * @return bool
	 */
	public function delete(Entity $item): bool {
		if (!$this->isMutable()) return false;
		unset($this->cache[$item->id]);
		return (bool)$this->connection->getSmartQuery()->deleteById($this->getTable(), $item->id);
	}
	/**
	 * Inserts the entity in the database
	 * Should not be called directly
	 *
	 * @param Entity $item
	 * @return int|false
	 */
	public function insert(Entity $item): int|false {
		if (!$this->isMutable()) return false;
		$properties = $this->getEntityProperties($item);
		foreach ($this->model->getProperties() as $name) if ($this->model->getProperty($name)->getPersist() === Persist::NEVER) unset($properties[$name]);
		$record = $this->model->store($properties);
		return $this->connection->getSmartQuery()->insert(table: $this->getTable(), data: $record);
	}

	/**
	 * Updates the entity in the database
	 * Should not be called directly
	 *
	 * @param Entity $item
	 * @return bool
	 */
	public function update(Entity $item): bool {
		if (!$this->isMutable()) return false;
		$properties = $this->getEntityProperties($item);
		foreach ($this->model->getProperties() as $name) if ($this->model->getProperty($name)->getPersist() !== Persist::ALWAYS) unset($properties[$name]);
		$record = $this->model->store($properties);
		return (bool)$this->connection->getSmartQuery()->updateById(table: $this->getTable(), id: $item->id, data: $record);
	}

	/**
	 * Updates one property of an entity in the database
	 * Should not be called directly
	 *
	 * @param Entity $item
	 * @param string $property
	 * @param mixed $value
	 * @return void
	 */
	public function updateProperty(Entity $item, string $property, mixed $value): void {
		$value = $this->model->getProperty($property)->store($value);
		$this->connection->getSmartQuery()->updateById($this->getTable(), $item->id, [$property => $value]);
	}
}