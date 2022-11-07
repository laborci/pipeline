<?php namespace Atomino2\Database\SmartSQL\Select;

use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\Expression;
use Atomino2\Database\SmartSQL\SqlGeneratorInterface;

class Limit implements SqlGeneratorInterface {
	public function __construct(private int $limit, private int $offset = 0) { }
	public function getSql(Connection $connection): string {
		return "LIMIT " . $this->limit . " OFFSET " . $this->offset;
	}
}