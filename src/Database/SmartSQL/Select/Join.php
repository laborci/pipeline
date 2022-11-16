<?php namespace Atomino2\Database\SmartSQL\Select;

use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\SQL;
use Atomino2\Database\SmartSQL\SqlGeneratorInterface;

class Join implements SqlGeneratorInterface {

	private array $joins = [];

	public function add(string $table, string|null $alias, Filter|null $on, string $mode = "INNER") {
		$this->joins[] = compact('table', 'alias', 'on', 'mode');
	}

	public function getSQL(Connection $connection): string {
		$sql = [];
		foreach ($this->joins as $join) {
			$sql[] = SQL::expr(":r JOIN :e :if('AS :e') :if('ON :r')",
				$join["mode"], $join["table"], $join["alias"], $join["on"]
			)->getSql($connection);
		}
		return join(" ", $sql);
	}
}