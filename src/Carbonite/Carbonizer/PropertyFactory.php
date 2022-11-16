<?php namespace Atomino2\Carbonite\Carbonizer;

use Atomino2\Carbonite\Carbonizer\Property\BoolProperty;
use Atomino2\Carbonite\Carbonizer\Property\DateProperty;
use Atomino2\Carbonite\Carbonizer\Property\DateTimeProperty;
use Atomino2\Carbonite\Carbonizer\Property\EnumProperty;
use Atomino2\Carbonite\Carbonizer\Property\FloatProperty;
use Atomino2\Carbonite\Carbonizer\Property\IntProperty;
use Atomino2\Carbonite\Carbonizer\Property\JsonProperty;
use Atomino2\Carbonite\Carbonizer\Property\SetProperty;
use Atomino2\Carbonite\Carbonizer\Property\StringProperty;

class PropertyFactory {
	public function __construct() { }
	public function factory(Field $field, int $persist = Persist::ALWAYS, int $access = Access::READ_WRITE, mixed $default = null): ?Property {
		if (in_array($field->columnType, ["TINYINT(1)"])) return new BoolProperty($field, $persist, $access, $default);
		if (in_array($field->dataType, ['INTEGER', 'INT', 'SMALLINT', 'TINYINT', 'MEDIUMINT', 'BIGINT'])) return new IntProperty($field, $persist, $access, $default);
		if (in_array($field->dataType, ['CHAR', 'VARCHAR', 'TINYTEXT', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT'])) return new StringProperty($field, $persist, $access, $default);
		if (in_array($field->dataType, ['DOUBLE', 'FLOAT'])) return new FloatProperty($field, $persist, $access, $default);
		if (in_array($field->dataType, ['ENUM'])) return new EnumProperty($field, $persist, $access, $default);
		if (in_array($field->dataType, ['SET'])) return new SetProperty($field, $persist, $access, $default);
		if (in_array($field->dataType, ['JSON'])) return new JsonProperty($field, $persist, $access, $default);
		if (in_array($field->dataType, ['DATE'])) return new DateProperty($field, $persist, $access, $default);
		if (in_array($field->dataType, ['DATETIME'])) return new DateTimeProperty($field, $persist, $access, $default);

		return null;
	}
}
