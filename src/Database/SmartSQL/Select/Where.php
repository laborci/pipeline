<?php namespace Atomino2\Database\SmartSQL\Select;

use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\SQL;
use Atomino2\Database\SmartSQL\SqlGeneratorInterface;

class Where implements SqlGeneratorInterface {
	private array $filters = [];
	public function __construct(private string $keyword = "WHERE") { }
	public function add(null|Filter $filter): void { if (!is_null($filter)) $this->filters[] = $filter; }
	public function getSql(Connection $connection): string { return count($this->filters) === 0 ? " " : SQL::expr(":r :r('AND')", $this->keyword, $this->filters)->getSQL($connection); }
}