<?php namespace Atomino2\Database\SmartSQL\Compare;

use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\SqlGeneratorInterface;

abstract class Compare implements SqlGeneratorInterface {

	protected string|null $field;
	protected Connection $connection;

	protected bool $not = false;

	public function __construct(string $field) { $this->field = $field; }

	abstract protected function createSQL(): string;

	public function getSQL(Connection $connection): string {
		$this->connection = $connection;
		return $this->createSQL();
	}

	protected function quoteValue(null|string|int|float|\DateTime|SqlGeneratorInterface $subject): string {
		if($subject instanceof SqlGeneratorInterface) return $subject->getSql($this->connection);
		return $this->connection->getSqlHelper()->quoteAndEscapeValue($subject);
	}

	protected function quoteEntity(null|string|SqlGeneratorInterface $subject): string {
		if($subject instanceof SqlGeneratorInterface) return $subject->getSql($this->connection);
		return $this->connection->getSqlHelper()->quoteEntity($subject);
	}


	public function not(): static {
		$this->not = true;
		return $this;
	}
}