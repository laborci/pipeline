<?php namespace Atomino2\Database\SmartSQL;

use Atomino2\Database\Connection;

class SqlEntity implements SqlGeneratorInterface {

	public function __construct(private string $entity) { }

	public function getSQL(Connection $connection): string { return $connection->getSqlHelper()->quoteEntity($this->entity); }


}