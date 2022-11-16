<?php namespace Atomino2\Database\SmartSQL;

use Atomino2\Database\Connection;
use Atomino2\Database\SmartSQL\Compare\CompareBetween;
use Atomino2\Database\SmartSQL\Compare\CompareEquals;
use Atomino2\Database\SmartSQL\Compare\CompareJson;
use Atomino2\Database\SmartSQL\Compare\CompareLike;
use Atomino2\Database\SmartSQL\Compare\CompareOperator;

class Comparison implements SqlGeneratorInterface {

	private array           $values = [];
	private readonly string $field;

	public function __construct(string $field, mixed ...$values) {
		$this->field = $field;
		$this->values = $values;
	}

	public function getSql(Connection $connection): string { return SQL::equals($this->field, ...$this->values)->getSQL($connection); }

	public function is(...$value): CompareEquals { return SQL::equals($this->field, ...$value); }
	public function not(...$value): CompareEquals { return SQL::equals($this->field, ...$value); }
	public function equals(...$value): CompareEquals { return SQL::equals($this->field, ...$value); }
	public function notEquals(...$value): CompareEquals { return SQL::notEquals($this->field, ...$value); }
	public function in(...$value): CompareEquals { return SQL::in($this->field, ...$value); }
	public function notIn(...$value): CompareEquals { return SQL::notIn($this->field, ...$value); }
	public function isNull(...$value): CompareEquals { return SQL::isNull($this->field); }
	public function isNotNull(...$value): CompareEquals { return SQL::isNotNull($this->field); }

	public function like(string $value): CompareLike { return SQL::like($this->field, $value); }
	public function notLike(string $value): CompareLike { return SQL::notLike($this->field, $value); }
	public function inString(string $value): CompareLike { return SQL::inString($this->field, $value); }
	public function notInString(string $value): CompareLike { return SQL::notInString($this->field, $value); }
	public function startsWith(string $value): CompareLike { return SQL::startsWith($this->field, $value); }
	public function notStartsWith(string $value): CompareLike { return SQL::notStartsWith($this->field, $value); }
	public function endsWith(string $value): CompareLike { return SQL::endsWith($this->field, $value); }
	public function notEndsWith(string $value): CompareLike { return SQL::notEndsWith($this->field, $value); }
	public function glob(string $value): CompareLike { return SQL::glob($this->field, $value); }
	public function notGlob(string $value): CompareLike { return SQL::notGlob($this->field, $value); }
	public function revLike(string $value): CompareLike { return SQL::revLike($this->field, $value); }
	public function notRevLike(string $value): CompareLike { return SQL::notRevLike($this->field, $value); }
	public function revGlob(string $value): CompareLike { return SQL::revGlob($this->field, $value); }
	public function notRevGlob(string $value): CompareLike { return SQL::notRevGlob($this->field, $value); }
	public function regexp(string $value): CompareLike { return SQL::regexp($this->field, $value); }
	public function notRegexp(string $value): CompareLike { return SQL::notRegexp($this->field, $value); }

	public function GT(mixed $value): CompareOperator { return SQL::GT($this->field, $value); }
	public function notGT(mixed $value): CompareOperator { return SQL::notGT($this->field, $value); }
	public function GTE(mixed $value): CompareOperator { return SQL::GTE($this->field, $value); }
	public function notGTE(mixed $value): CompareOperator { return SQL::notGTE($this->field, $value); }
	public function LT(mixed $value): CompareOperator { return SQL::LT($this->field, $value); }
	public function notLT(mixed $value): CompareOperator { return SQL::notLT($this->field, $value); }
	public function LTE(mixed $value): CompareOperator { return SQL::LTE($this->field, $value); }
	public function notLTE(mixed $value): CompareOperator { return SQL::notLTE($this->field, $value); }

	public function between(mixed $value1, mixed $value2): CompareBetween { return SQL::between($this->field, $value1, $value2); }
	public function notBetween(mixed $value1, mixed $value2): CompareBetween { return SQL::notBetween($this->field, $value1, $value2); }

	public function inJson(mixed $value, string $path = "$"): CompareJson { return SQL::inJson($this->field, $value, $path); }
	public function notInJson(mixed $value, string $path = "$"): CompareJson { return SQL::notInJson($this->field, $value, $path); }
}