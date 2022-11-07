<?php namespace Atomino2\Database\SmartSQL;

use Atomino2\Database\Connection;

interface SqlGeneratorInterface {
	public function getSql(Connection $connection): string;
}