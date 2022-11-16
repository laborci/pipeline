<?php namespace Atomino2\Database\SmartQuery;

use App\Carbonite\Store\UserStore;
use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\Expression;
use Atomino2\Database\SmartSQL\Select\Filter;
use Atomino2\Database\SmartSQL\Select\Select;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\CacheInterface;

class Finder {

	private int $cacheInterval = 0;

	public function __construct(
		private Connection      $connection,
		private Select          $select,
		private ?CacheInterface $cache = null,
	) {
	}

	public function resetFields(): static {
		$this->select->resetFields();
		return $this;
	}
	public function field(string|Expression $field, string|null $as = null): static {
		$this->select->field($field, $as);
		return $this;
	}
	public function groupBy(string $field, string ...$fields): static {
		$this->select->groupBy($field, ...$fields);
		return $this;
	}
	public function from(string|Expression $from): static {
		$this->select->from($from);
		return $this;
	}
	public function join(string $table, string|null $alias, Filter|null $on, string $mode = "INNER"): static {
		$this->select->join($table, $alias, $on, $mode);
		return $this;
	}
	public function having(null|Filter $where): static {
		$this->select->having($where);
		return $this;
	}
	public function where(null|Filter $where): static {
		$this->select->where($where);
		return $this;
	}
	public function asc(string|Expression $field): static {
		$this->select->asc($field);
		return $this;
	}
	public function desc(?string $field): static {
		$this->select->desc($field);
		return $this;
	}
	public function random(): static {
		$this->select->random();
		return $this;
	}
	public function order(array|string ...$orders): static {
		$this->select->order(...$orders);
		return $this;
	}

	public function cache(int $sec): static {
		$this->cacheInterval = $sec;
		return $this;
	}


	public function getValue(string|null $column = null): mixed {
		$record = $this->first();
		if (is_null($record)) return null;
		if (is_null($column)) return reset($record);
		if (!array_key_exists($column, $record)) return null;
		else return $record[$column];
	}

	public function getInteger(string|null $column = null): int|null { return is_null($value = $this->getValue($column)) ? null : intval(reset($value)); }

	public function getColumn(string|null $column = null, int $limit = 0, int $offset = 0): mixed {
		return array_map(
			fn($record) => is_null($column) || !array_key_exists($column, $record) ? reset($record) : $record[$column],
			$this->get($limit, $offset)->getRecords()
		);
	}

	public function first(): ?array { return $this->get(1)->getFirstRecord(); }

	public function get(int $limit = 0, int $offset = 0, bool $counting = false): Result {
		$sql = $this->select->getSelectSql($this->connection, $limit, $offset, $counting);
		if ($this->cache && $this->cacheInterval) {
			return $this->cache->get(md5($sql), function (CacheItem $item) use ($sql, $counting) {
				$item->expiresAfter($this->cacheInterval);
				return $this->fetch($sql, $counting);
			});
		}
		return $this->fetch($sql, $counting);
	}

	public function count(): int {
		$sql = $this->select->getCountSql($this->connection);
		$record = $this->fetch($sql, false)->getFirstRecord();
		return is_null($record) ? 0 : array_pop($record);
	}

	private function fetch(string $sql, bool $counting = false): Result {
		$records = $this->connection->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
		$count = $counting !== false ? $this->connection->query('SELECT FOUND_ROWS()')->fetch(\PDO::FETCH_COLUMN) : null;
		return new Result($records, $count);
	}
}
