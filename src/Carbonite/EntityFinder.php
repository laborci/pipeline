<?php namespace Atomino2\Carbonite;


use Atomino2\Database\SmartQuery\Finder;
use Atomino2\Database\SmartSQL\Expression;
use Atomino2\Database\SmartSQL\Select\Filter;

class EntityFinder {

	private Finder $finder;
	public function getFinder(): Finder { return $this->finder; }

	public function __construct(
		private readonly EntityStore $store,
		?Filter                      $filter
	) {
		$this->finder = $this->store->getConnection()->getFinder($this->store->getTable());
		$this->finder->where($filter);
	}

	/**
	 * Add an ascending order field
	 *
	 * @param string|Expression $field
	 * @return $this
	 */
	public function asc(string|Expression $field): static {
		$this->finder->asc($field);
		return $this;
	}
	/**
	 * Add a descending order field
	 *
	 * @param string|null $field
	 * @return $this
	 */
	public function desc(?string $field): static {
		$this->finder->desc($field);
		return $this;
	}
	/**
	 * Set the order to random
	 *
	 * @return $this
	 */
	public function random(): static {
		$this->finder->random();
		return $this;
	}
	/**
	 * Complex way to set order
	 * string argument will be an ASC order,
	 * array argument's first index (0) will be the field second index(1) will be the direction of the order
	 * direction can be ('ASC', 'asc', 1, true for ascending, otherwise descending)
	 *
	 * @param array|string ...$orders
	 * @return $this
	 */
	public function order(array|string ...$orders): static {
		$this->finder->order(...$orders);
		return $this;
	}

	/**
	 * Returns the count of the query result
	 * @return int
	 */
	public function count(): int { return $this->finder->count(); }

	/**
	 * returns the first result
	 * @return Entity|null
	 */
	public function first(): ?Entity {
		$data = $this->finder->first();
		return $this->store->createAndLoadRecord($data);
	}

	/**
	 * Gets a page of the resultset
	 *
	 * @param int $size the size of the page
	 * @param int $page the number of the page (starts with 1). It is a reference, on overflow or negative value, it will be overwritten
	 * @param int|bool|null $count reference to return the size of the whole result set
	 * @param bool $handleOverflow when true, it the page is more than the available pages, it will return the last page instead
	 * @return Entity[]
	 */
	public function page(int $size, int &$page = 1, int|bool|null &$count = false, bool $handleOverflow = true): array {
		if ($page < 1) $page = 1;
		$items = $this->get($size, $size * ($page - 1), $count);
		if (count($items) === 0 && $handleOverflow && $page !== 1) {
			$page = max(1, ceil($count / $size));
			$items = $this->get($size, $size * ($page - 1), $count);
		}
		return $items;
	}

	/**
	 * It returns entities, for the finder.
	 * You can specify the limit and offset
	 *
	 * @param int $limit
	 * @param int $offset
	 * @param int|bool|null $count reference to return the size of the whole result set
	 * @return Entity[]
	 */
	public function get(int $limit = 0, int $offset = 0, int|bool|null &$count = false): array {
		$records = $this->finder->get($limit, $offset, $count);
		return array_map(fn($record) => $this->store->createAndLoadRecord($record), $records->getRecords());
	}

}