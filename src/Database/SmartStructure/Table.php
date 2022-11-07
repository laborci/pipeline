<?php namespace Atomino2\Database\SmartStructure;

use Atomino2\Database\Connection;
use Atomino2\Database\SmartStructure\Field\Field;

class Table {

	private ?Field $primary = null;
	/** @var Field[]|null */
	private ?array $fields = null;
	private ?array $uniques = null;

	public function __construct(private readonly Connection $connection, private readonly string $name, private readonly string $type) {
	}

	public function isView(): bool { return $this->type === 'VIEW'; }
	public function isTable(): bool { return $this->type !== 'VIEW'; }
	public function getName(): string { return $this->name; }

	public function getUniques(): array {
		if (is_null($this->uniques)) {
			$this->uniques = [];
			$uniques = $this->connection->getSmartQuery()->getRows(
			/** @lang SqlHelper */ "SHOW INDEXES FROM :e WHERE :e=:v AND :e!=:v",
				$this->name, "Non_unique", 0, "Key_name", "PRIMARY"
			);
			foreach ($uniques as $unique) {
				$key = $unique["Key_name"];
				$field = $unique["Column_name"];
				if (!array_key_exists($key, $this->uniques)) $this->uniques[$key] = [];
				$this->uniques[$key][] = $field;
			}
		}
		return $this->uniques;
	}

	public function hasUnique(string $name): bool {
		$this->getUniques();
		return array_key_exists($name, $this->uniques);
	}

	public function getUnique(string $name): array|null { return $this->hasUnique($name) ? $this->uniques[$name] : null; }

	public function getFieldData(): array {
		return $this->connection->getSmartQuery()->getRows(
			"SELECT * FROM :e WHERE :e = :v AND :e = :v",
			"information_schema.columns",
			"table_name",
			$this->name,
			"table_schema",
			$this->connection->getSmartStructure()->getDatabaseName()
		);
	}

	/**
	 * @return Field[]
	 */
	public function getFields(): array {
		if (is_null($this->fields)) {
			$this->fields = [];
			$fields = $this->getFieldData();
			foreach ($fields as $descriptor) {
				debug($descriptor);
				$field = Field::create($descriptor);
				if (!is_null($field) && $field->isPrimary()) $this->primary = $field;
				$this->fields[$descriptor["COLUMN_NAME"]] = $field;
			}
		}
		return $this->fields;
	}
	public function getField(string $name): ?Field { return $this->hasField($name) ? $this->fields[$name] : null; }
	public function hasField(string $name) {
		$this->getFields();
		return array_key_exists($name, $this->fields);
	}

	public function hasPrimary(): bool { return !is_null($this->getPrimary()); }
	public function getPrimary(): ?Field {
		$this->getFields();
		return $this->primary;
	}

}