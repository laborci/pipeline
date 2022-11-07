<?php namespace Atomino2\Carbonite\Carbonizer;

class Field {

	public mixed $characterMaximumLength = null;
	public mixed $characterOctetLength = null;
	public mixed $characterSetName = null;
	public mixed $collationName = null;
	public mixed $columnComment = null;
	public mixed $columnDefault = null;
	public mixed $columnKey = null;
	public mixed $columnName = null;
	public mixed $columnType = null;
	public mixed $dataType = null;
	public mixed $datetimePrecision = null;
	public mixed $extra = null;
	public mixed $generationExpression = null;
	public mixed $isNullable = null;
	public mixed $numericPrecision = null;
	public mixed $numericScale = null;
	public mixed $ordinalPosition = null;
	public mixed $privileges = null;
	public mixed $srsId = null;
	public mixed $tableCatalog = null;
	public mixed $tableName = null;
	public mixed $tableSchema = null;
	public mixed $columnTypeRaw = null;

	public string|null $name = null;
	public array|null $options = null;


	public function __construct(array $data) {
		$rawData = $data;
		foreach ($data as $key => $value) if (is_string($value) && $key != 'COLUMN_NAME') $data[$key] = strtoupper($value);
		$this->characterMaximumLength = $data["CHARACTER_MAXIMUM_LENGTH"];
		$this->characterOctetLength = $data["CHARACTER_OCTET_LENGTH"];
		$this->characterSetName = $data["CHARACTER_SET_NAME"];
		$this->collationName = $data["COLLATION_NAME"];
		$this->columnComment = $data["COLUMN_COMMENT"];
		$this->columnDefault = $data["COLUMN_DEFAULT"];
		$this->columnKey = $data["COLUMN_KEY"];
		$this->columnName = $data["COLUMN_NAME"];
		$this->columnType = $data["COLUMN_TYPE"];
		$this->dataType = $data["DATA_TYPE"];
		$this->datetimePrecision = $data["DATETIME_PRECISION"];
		$this->extra = $data["EXTRA"];
		$this->generationExpression = $data["GENERATION_EXPRESSION"];
		$this->isNullable = $data["IS_NULLABLE"];
		$this->numericPrecision = $data["NUMERIC_PRECISION"];
		$this->numericScale = $data["NUMERIC_SCALE"];
		$this->ordinalPosition = $data["ORDINAL_POSITION"];
		$this->privileges = $data["PRIVILEGES"];
		$this->srsId = $data["SRS_ID"];
		$this->tableCatalog = $data["TABLE_CATALOG"];
		$this->tableName = $data["TABLE_NAME"];
		$this->tableSchema = $data["TABLE_SCHEMA"];

		$this->name = $rawData["COLUMN_NAME"];
		if (in_array($this->dataType, ["ENUM", "SET"])) {
			preg_match_all("/'(.*?)'/", $rawData["COLUMN_TYPE"], $matches);
			$this->options = $matches[1];
		}
	}
}