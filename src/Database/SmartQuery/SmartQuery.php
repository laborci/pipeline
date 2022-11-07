<?php namespace Atomino2\Database\SmartQuery;

use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\SQL;
use Atomino2\Database\SmartSQL\SqlGeneratorInterface;

class SmartQuery {

	public function __construct(private readonly Connection $connection) { }

	/**
	 * @param string|SqlGeneratorInterface $sql
	 * @return bool|\PDOStatement
	 * @throws \Exception
	 */
	private function query(string|SqlGeneratorInterface $sql): bool|\PDOStatement {
		$sql = is_string($sql) ? $sql : $sql->getSql($this->connection);
		return $this->connection->query($sql);
	}

	public function getValue(string $sql, ...$args) {
		$row = $this->getRow($sql, ...$args);
		return $row ? reset($row) : null;
	}
	protected function getRow(string $sql, ...$args) { return $this->query(SQL::expr($sql, ...$args))->fetch(\PDO::FETCH_ASSOC) ?: null; }
	public function getRows(string $sql, ...$args): array { return $this->query(SQL::expr($sql, ...$args))->fetchAll(\PDO::FETCH_ASSOC); }
	public function getValues(string $sql, ...$args): array { return $this->query(SQL::expr($sql, ...$args))->fetchAll(\PDO::FETCH_COLUMN, 0); }
	public function getValuesWithKey(string $sql, ...$args): array { return $this->query(SQL::expr($sql, ...$args))->fetchAll(\PDO::FETCH_KEY_PAIR); }
	public function getRowsWithKey(string $sql, ...$args): array { return $this->query(SQL::expr($sql, ...$args))->fetchAll(\PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC); }

	public function getRowById(string $table, int $id, string $idField = "id") { return $this->getRow("SELECT * FROM :e WHERE :e = :v", $table, $idField, $id); }
	public function getRowsById(string $table, array $ids, string $idField = "id"): array { return count($ids) === 0 ? [] : $this->getRows("SELECT * FROM :e WHERE :e IN (:v)", $table, $idField, $ids); }

	#region insert / update / delete
	public function insert(string $table, array $data, bool $ignore = false): int {
		$this->query(SQL::expr("INSERT :if('IGNORE') INTO :e (:dk) VALUE (:dv)", $ignore, $table, $data, $data));
		return $this->connection->getPdo()->lastInsertId();
	}
	public function update(string $table, string|SqlGeneratorInterface|array|null $filter, array $data): int { return $this->query(SQL::expr("UPDATE :e SET :d :ifn('WHERE ') :r('AND')", $table, $data, $filter))->rowCount(); }
	public function updateById(string $table, int $id, array $data, string $idField = "id"): int { return $this->update($table, [$idField => $id], $data); }
	public function delete(string $table, string|SqlGeneratorInterface|array|null $filter): int { return $this->query(SQL::expr("DELETE FROM :e WHERE :r", $table, $filter))->rowCount(); }
	public function deleteById(string $table, int $id, string $idField = 'id'): int { return $this->delete($table, [$idField => $id]); }
	#endregion
}
