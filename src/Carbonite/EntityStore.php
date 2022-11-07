<?php namespace Atomino2\Carbonite;


use Atomino2\Carbonite\Carbonizer\CarbonizedModel;
use Atomino2\Carbonite\Carbonizer\Model;
use Atomino2\Carbonite\Carbonizer\Models;
use Atomino2\Carbonite\Carbonizer\Property\IntProperty;
use Atomino2\Carbonite\Carbonizer\Property\JsonProperty;
use Atomino2\Carbonite\Carbonizer\Persist;
use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\Select\Filter;
use Atomino2\Database\SmartSQL\SQL;
use DI\Container;

abstract class EntityStore {

	protected const entity = "";

	protected const model = "";
	protected Container $di;
	private Model       $model;
	private Connection  $connection;
	/** @var Entity[] */
	private array $cache = [];

	public function getDi(): Container { return $this->di; }
	public function getTable(): string { return $this->model->getTable(); }
	public function isMutable(): bool { return $this->model->isMutable(); }
	public function getConnection(): Connection { return $this->connection; }

	public function __construct(Container $di) {
		$this->di = $di;
		$this->model = (new \ReflectionClass(static::entity))->getParentClass()->getAttributes(CarbonizedModel::class)[0]->newInstance()->getModel();
		$this->connection = $di->get($this->model->getConnection());
	}

	public function create(?array $properties = null): Entity {
		/** @var Entity $item */
		$item = $this->di->make(static::entity);
		$object = \Closure::bind(fn($store, $model, $properties) => $this->__setup($store, $model, $properties), $item, Entity::class)($this, $this->model, $properties);
		if ($object->id !== null) $this->cache[$object->id] = $object;
		return $object;
	}

	public function search(Filter $filter): EntityFinder { return new EntityFinder($this, $filter); }

	public function build(array|null $record): null|Entity {
		if (is_null($record)) return null;
		return $this->create($this->model->build($record));
	}

	public function pick(int $id): ?Entity {
		if (array_key_exists($id, $this->cache)) return $this->cache[$id];
		$record = $this->connection->getSmartQuery()->getRowById($this->getTable(), $id);
		return $this->build($record);
	}

	public function collect(...$ids): array {
		$result = [];
		foreach ($ids as $key => $id) if (array_key_exists($id, $this->cache)) {
			$result[] = $this->cache[$id];
			unset($ids[$key]);
		}
		$records = count($ids) ? $this->connection->getSmartQuery()->getRowsById($this->getTable(), $ids) : [];
		return array_merge(array_map(fn($record) => $this->create($this->model->build($record)), $records), $result);
	}

	public function belongsTo(int|null $id): Entity|null { return is_null($id) ? null : $this->pick($id); }
	public function belongsToMany(array $ids): array { return $this->collect($ids); }
	public function hasMany(string $property, int|null $id): ?EntityFinder {
		if ($this->model->getProperty($property) instanceof IntProperty) return $this->search(SQL::filter(SQL::cmp($property, $id)));
		if ($this->model->getProperty($property) instanceof JsonProperty) return $this->search(SQL::filter(SQL::cmp($property)->inJson($id)));
		return null;
	}

	public function delete(Entity $item): bool {
		if (!$this->isMutable()) return false;
		unset($this->cache[$item->id]);
		return (bool)$this->connection->getSmartQuery()->deleteById($this->getTable(), $item->id);
	}
	public function insert(Entity $item): int|false {
		if (!$this->isMutable()) return false;
		$properties = $this->getEntityProperties($item);
		foreach ($this->model->getProperties() as $name) if ($this->model->getProperty($name)->getPersist() === Persist::NEVER) unset($properties[$name]);
		$record = $this->model->store($properties);
		return $this->connection->getSmartQuery()->insert(table: $this->getTable(), data: $record);
	}

	public function update(Entity $item): bool {
		if (!$this->isMutable()) return false;
		$properties = $this->getEntityProperties($item);
		foreach ($this->model->getProperties() as $name) if ($this->model->getProperty($name)->getPersist() !== Persist::ALWAYS) unset($properties[$name]);
		$record = $this->model->store($properties);
		return (bool)$this->connection->getSmartQuery()->updateById(table: $this->getTable(), id: $item->id, data: $record);
	}

	private function getEntityProperties(Entity $item) { return \Closure::bind(fn() => $this->properties, $item, Entity::class)(); }

	public function updateProperty(int $id, string $property, mixed $value): void {
		//TODO: fill it
	}
}