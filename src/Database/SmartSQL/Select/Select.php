<?php namespace Atomino2\Database\SmartSQL\Select;

use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\Expression;
use Atomino2\Database\SmartSQL\SQL;
use Atomino2\Database\SmartSQL\SqlGeneratorInterface;

class Select implements SqlGeneratorInterface {

	private Fields $fields;
	private ?GroupBy $groupBy = null;
	private ?From $from = null;
	private ?Join $join = null;
	private ?Where $having = null;
	private ?Where $where = null;
	private ?Order $order = null;
	private ?Limit $limit = null;

	public bool $counting = false;

	public function __construct(?string $table = null) {
		if (!is_null($table)) $this->from($table);
		$this->fields = new Fields();
	}

	public function resetFields(): static {
		$this->fields->reset();
		return $this;
	}

	public function field(string|Expression $field, string|null $as = null): static {
		$this->fields->add($field, $as);
		return $this;
	}

	public function groupBy(string $field, string ...$fields): static {
		if (is_null($this->groupBy)) $this->groupBy = new GroupBy();
		$this->groupBy->add($field, ...$fields);
		return $this;
	}

	public function from(string|Expression $from): static {
		$this->from = new From($from);
		return $this;
	}

	public function join(string $table, string|null $alias, Filter|null $on, string $mode = "INNER"): static {
		if (is_null($this->join)) $this->join = new Join();
		$this->join->add($table, $alias, $on, $mode);
		return $this;
	}

	public function having(null|Filter $where): static {
		if (is_null($this->having)) $this->having = new Where("HAVING");
		$this->having->add($where);
		return $this;
	}

	public function where(null|Filter $where): static {
		if (is_null($this->where)) $this->where = new Where();
		$this->where->add($where);
		return $this;
	}

	public function asc(string|Expression $field): static {
		if (is_null($this->order)) $this->order = new Order();
		$this->order->add($field);
		return $this;
	}
	public function desc(?string $field): static {
		if (is_null($this->order)) $this->order = new Order();
		$this->order->add($field, Order::DESC);
		return $this;
	}

	public function random(): static {
		if (is_null($this->order)) $this->order = new Order();
		$this->order->add(SQL::expr("Rand()"));
		return $this;
	}

	public function order(array|string ...$orders): static {
		if (is_null($this->order)) $this->order = new Order();
		foreach ($orders as $order) {
			if (is_array($order)) {
				$field = $order[0];
				$dir = (strtolower($order[1]) === 'asc' || $order[1] === 1 || $order[1] === true) ? Order::ASC : Order::DESC;
			} else {
				$field = $order;
				$dir = Order::ASC;
			}
			$this->order->add($field, $dir);
		}
		return $this;
	}

	public function limit(int $limit, int $offset = 0) {
		if ($limit === 0) $this->limit = null;
		else $this->limit = new Limit($limit, $offset);
	}

	public function getSelectSql(Connection $connection, int $limit = 0, int $offset = 0, bool $counting = false) {
		#region store settings
		$t_limit = $this->limit;
		$t_counting = $this->counting;
		#endregion
		$this->limit($limit, $offset);
		$this->counting = $counting;
		$sql = $this->getSql($connection);
		#region restore settings
		$this->limit = $t_limit;
		$this->counting = $t_counting;
		#endregion

		return $sql;
	}

	public function getCountSql(Connection $connection) {
		#region store settings
		$t_fields = $this->fields;
		$t_limit = $this->limit;
		$t_counting = $this->counting;
		$t_order = $this->order;
		#endregion
		$this->resetFields();
		$this->field(SQL::expr("Count(*) AS `count`"));
		$this->limit = null;
		$this->counting = false;
		$this->order = null;
		$sql = $this->getSql($connection);
		#region restore settings
		$this->fields = $t_fields;
		$this->limit = $t_limit;
		$this->counting = $t_counting;
		$this->order = $t_order;
		#endregion
		return $sql;
	}

	public function getSql(Connection $connection): string {
		return SQL::expr("SELECT :if('SQL_CALC_FOUND_ROWS') 
/*FIELDS */ :r 
/*FROM   */ :r 
/*JOIN   */ :r 
/*WHERE  */ :r 
/*GROUPBY*/ :r 
/*HAVING */ :r 
/*ORDER  */ :r 
/*LIMIT  */ :r",
			$this->counting,
			$this->fields,
			$this->from,
			$this->join,
			$this->where,
			$this->groupBy,
			$this->having,
			$this->order,
			$this->limit,
		)->getSQL($connection);
	}
}

