<?php namespace Atomino2\Database\SmartSQL\Select;

use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\Expression;
use Atomino2\Database\SmartSQL\SQL;
use Atomino2\Database\SmartSQL\SqlGeneratorInterface;

class GroupBy implements SqlGeneratorInterface {
	private array $fields = [];
	public function __construct() { }
	public function add(string|Expression ...$field): void {
		array_push($this->fields, ...$field);
	}
	public function getSql(Connection $connection): string {
		if (count($this->fields) === 0) return "";
		return SQL::expr("GROUP BY :e", $this->fields)->getSQL($connection);
	}
}