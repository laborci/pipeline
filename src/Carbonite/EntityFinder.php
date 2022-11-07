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

	public function asc(string|Expression $field): static {
		$this->finder->asc($field);
		return $this;
	}
	public function desc(?string $field): static {
		$this->finder->desc($field);
		return $this;
	}
	public function random(): static {
		$this->finder->random();
		return $this;
	}
	public function order(array|string ...$orders): static {
		$this->finder->order(...$orders);
		return $this;
	}

	public function count(): int { return $this->finder->count(); }

	public function first(): ?Entity {
		$data = $this->finder->first();
		return $this->store->build($data);
	}

	/** @return Entity[] */
	public function page(int $size, int &$page = 1, int|bool|null &$count = false, $handleOverflow = true): array {
		if ($page < 1) $page = 1;
		$items = $this->get($size, $size * ($page - 1), $count);
		if (count($items) === 0 && $handleOverflow && $page !== 1) {
			$page = max(1, ceil($count / $size));
			$items = $this->get($size, $size * ($page - 1), $count);
		}
		return $items;
	}

	/** @return Entity[] */
	public function get(int $limit = 0, int $offset = 0, int|bool|null &$count = false): array {
		$records = $this->finder->get($limit, $offset, $count);
		return array_map(fn($record)=>$this->store->build($record), $records->getRecords());
	}

}