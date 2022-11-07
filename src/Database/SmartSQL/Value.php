<?php namespace Atomino2\Database\SmartSQL;

use Atomino2\Database\Connection;

class Value implements SqlGeneratorInterface {
	public function __construct(private string $value) { }
	public function getSQL(Connection $connection): string { return $connection->getSqlHelper()->quoteAndEscapeValue($this->value); }
}