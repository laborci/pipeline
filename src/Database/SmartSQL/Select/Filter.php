<?php namespace Atomino2\Database\SmartSQL\Select;

use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\SqlGeneratorInterface;
use Stringable;
use PDO;

class Filter implements SqlGeneratorInterface {
	const NOT = 1 << 0;
	const AND = 1 << 1;
	const OR = 1 << 2;

	/** @var Filter[] */
	private array $chain = [];

	protected function __construct(null|SqlGeneratorInterface|string $sql = null) {
		if (!is_null($sql)) $this->chain[] = ["operator" => 0, "sql" => $sql];
	}

	static public function create(null|SqlGeneratorInterface|string $sql = null): static { return new static($sql); }

	public function and(null|SqlGeneratorInterface|string $sql): static {
		$this->chain[] = ["operator" => self::AND, "sql" => $sql];
		return $this;
	}
	public function or(null|SqlGeneratorInterface|string $sql): static {
		$this->chain[] = ["operator" => self::OR, "sql" => $sql];
		return $this;
	}
	public function andNot(null|SqlGeneratorInterface|string $sql): static {
		$this->chain[] = ["operator" => self::AND + self::NOT, "sql" => $sql];
		return $this;
	}
	public function orNot(null|SqlGeneratorInterface|string $sql): static {
		$this->chain[] = ["operator" => self::OR + self::NOT, "sql" => $sql];
		return $this;
	}

	public function getSql(Connection $connection): string {
		$output = " ( ";
		foreach ($this->chain as $index => $item) {
			$exp = $item["sql"];
			if (is_null($exp)) continue;
			if (is_object($exp) && in_array(SqlGeneratorInterface::class, class_implements($exp))) $exp = $exp->getSQL($connection);
			if ($index > 0 && strlen($exp)) {
				if ($item["operator"] & self::AND) $output .= " AND ";
				if ($item["operator"] & self::OR) $output .= " OR ";
				if ($item["operator"] & self::NOT) $output .= " NOT ";
			}
			$output .= $exp;
		}
		$output .= " ) ";
		return $output;
	}
}