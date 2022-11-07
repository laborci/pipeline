<?php namespace Atomino2\Database\SmartSQL\Select;

use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\Expression;
use Atomino2\Database\SmartSQL\SQL;
use Atomino2\Database\SmartSQL\SqlGeneratorInterface;

class Fields implements SqlGeneratorInterface {
	private array $fields = [];
	public function __construct() { }
	public function add(string|Expression $field, string|null $as = null): void { $this->fields[] = ["key" => $field, "value" => $as]; }
	public function reset() { $this->fields = []; }
	public function getSql(Connection $connection): string {
		if (count($this->fields) === 0) return " * ";
		return SQL::expr(":d(',', ':e :if(\' AS :e\')')", $this->fields)->getSQL($connection);
	}
}