<?php namespace Atomino2\Database\SmartStructure;

use Atomino2\Database\Connection;

class SmartStructure {

	private ?string $database = null;

	/** @var Table[] */
	private ?array $tables = null;

	public function __construct(private Connection $connection) { }

	public function getTables(): array {
		if (is_null($this->tables)) {
			$this->tables = [];
			$tables = $this->connection->query("SHOW FULL TABLES")->fetchAll(\PDO::FETCH_KEY_PAIR);
			foreach ($tables as $table => $type) $this->tables[$table] = new Table($this->connection, $table, $type);
		}
		return $this->tables;
	}
	public function getTable(string $table): ?Table { return $this->hasTable($table) ? $this->tables[$table] : null; }
	public function hasTable(string $table): bool { return array_key_exists($table, $this->getTables()); }
	public function getTableNames(): array { return array_keys($this->getTables()); }
	public function getDatabaseName(): string { return is_null($this->database) ? $this->database = $this->connection->query("select database()")->fetchColumn() : $this->database; }
}