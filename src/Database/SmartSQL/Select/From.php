<?php namespace Atomino2\Database\SmartSQL\Select;

use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\Expression;
use Atomino2\Database\SmartSQL\SqlGeneratorInterface;

class From implements SqlGeneratorInterface {
	public function __construct(private string|Expression $from) { }
	public function getSql(Connection $connection): string {
		return "FROM " . $connection->getSqlHelper()->quoteEntity($this->from);
	}
}