<?php namespace Atomino2\Database\SmartSQL\Select;

use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\Expression;
use Atomino2\Database\SmartSQL\SQL;
use Atomino2\Database\SmartSQL\SqlGeneratorInterface;

class Order implements SqlGeneratorInterface {

	const ASC  = true;
	const DESC = false;

	private array $fields = [];
	public function add(string|Expression $field, bool $order = self::ASC): void { $this->fields[] = ["key" => $field, "value" => $order ? 'ASC' : 'DESC']; }
	public function getSql(Connection $connection): string {
		if (count($this->fields) === 0) return "";
		else return SQL::expr("ORDER BY :d(', ',':e :r')", $this->fields)->getSQL($connection);
	}
}