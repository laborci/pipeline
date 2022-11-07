<?php namespace Atomino2\Database\SmartSQL;

use Atomino2\Database\Connection;

class Expression implements SqlGeneratorInterface {
	private array $arguments;
	private readonly string $sql;

	public function __construct(string $sql, mixed ...$arguments) {
		$this->sql = $sql;
		$this->arguments = $arguments;
	}
	public function getSQL(Connection $connection): string {
		return $connection->getSqlHelper()->expr($this->sql, ...$this->arguments);
	}
}