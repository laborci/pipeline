<?php namespace Atomino2\Database\SmartSQL;

use Atomino2\Database\SmartSQL\Compare\CompareBetween;
use Atomino2\Database\SmartSQL\Compare\CompareEquals;
use Atomino2\Database\SmartSQL\Compare\CompareJson;
use Atomino2\Database\SmartSQL\Compare\CompareLike;
use Atomino2\Database\SmartSQL\Compare\CompareNotEquals;
use Atomino2\Database\SmartSQL\Compare\CompareOperator;
use Atomino2\Database\SmartSQL\Select\Filter;

class SQL {
	static public function equals(string $field, mixed ...$value): CompareEquals { return new CompareEquals($field, ...$value); }
	static public function notEquals(string $field, mixed ...$value): CompareEquals { return (new CompareEquals($field, ...$value))->not(); }
	static public function in(string $field, array $values): CompareEquals { return new CompareEquals($field, ...$values); }
	static public function notIn(string $field, array $values): CompareEquals { return (new CompareEquals($field, ...$values))->not(); }
	static public function isNull(string $field): CompareEquals { return new CompareEquals($field, null); }
	static public function isNotNull(string $field): CompareEquals { return (new CompareEquals($field, null))->not(); }

	static public function like(string $field, string $value): CompareLike { return (new CompareLike($field, $value)); }
	static public function notLike(string $field, string $value): CompareLike { return (new CompareLike($field, $value))->not(); }
	static public function inString(string $field, string $value): CompareLike { return (new CompareLike($field, "%" . $value . "%")); }
	static public function notInString(string $field, string $value): CompareLike { return (new CompareLike($field, "%" . $value . "%"))->not(); }
	static public function startsWith(string $field, string $value): CompareLike { return (new CompareLike($field, $value . "%")); }
	static public function notStartsWith(string $field, string $value): CompareLike { return (new CompareLike($field, $value . "%"))->not(); }
	static public function endsWith(string $field, string $value): CompareLike { return (new CompareLike($field, "%" . $value)); }
	static public function notEndsWith(string $field, string $value): CompareLike { return (new CompareLike($field, "%" . $value))->not(); }
	static public function glob(string $field, string $value): CompareLike { return (new CompareLike($field, $value))->glob(); }
	static public function notGlob(string $field, string $value): CompareLike { return (new CompareLike($field, $value))->glob()->not(); }

	static public function revLike(string $field, string $value): CompareLike { return (new CompareLike($field, $value))->rev(); }
	static public function notRevLike(string $field, string $value): CompareLike { return (new CompareLike($field, $value))->rev()->not(); }
	static public function revGlob(string $field, string $value): CompareLike { return (new CompareLike($field, $value))->rev(); }
	static public function notRevGlob(string $field, string $value): CompareLike { return (new CompareLike($field, $value))->rev()->not(); }
	static public function regexp(string $field, string $value): CompareLike { return (new CompareLike($field, $value))->regex(); }
	static public function notRegexp(string $field, string $value): CompareLike { return (new CompareLike($field, $value))->regex()->not(); }

	static public function GT(string $field, mixed $value): CompareOperator { return (new CompareOperator($field, $value, CompareOperator::OPERATOR_GT)); }
	static public function notGT(string $field, mixed $value): CompareOperator { return (new CompareOperator($field, $value, CompareOperator::OPERATOR_GT))->not(); }
	static public function GTE(string $field, mixed $value): CompareOperator { return (new CompareOperator($field, $value, CompareOperator::OPERATOR_GTE)); }
	static public function notGTE(string $field, mixed $value): CompareOperator { return (new CompareOperator($field, $value, CompareOperator::OPERATOR_GTE))->not(); }
	static public function LT(string $field, mixed $value): CompareOperator { return (new CompareOperator($field, $value, CompareOperator::OPERATOR_LT)); }
	static public function notLT(string $field, mixed $value): CompareOperator { return (new CompareOperator($field, $value, CompareOperator::OPERATOR_LT))->not(); }
	static public function LTE(string $field, mixed $value): CompareOperator { return (new CompareOperator($field, $value, CompareOperator::OPERATOR_LTE)); }
	static public function notLTE(string $field, mixed $value): CompareOperator { return (new CompareOperator($field, $value, CompareOperator::OPERATOR_LTE))->not(); }

	static public function between(string $field, mixed $value1, mixed $value2): CompareBetween { return (new CompareBetween($field, $value1, $value2)); }
	static public function notBetween(string $field, mixed $value1, mixed $value2): CompareBetween { return (new CompareBetween($field, $value1, $value2))->not(); }

	static public function inJson(string $field, mixed $value, string $path = "$"): CompareJson { return (new CompareJson($field, $value, $path)); }
	static public function notInJson(string $field, mixed $value, string $path = "$"): CompareJson { return (new CompareJson($field, $value, $path))->not(); }

	static public function expr(string $sql, ...$arguments): Expression { return new Expression($sql, ...$arguments); }

	static public function filter(null|SqlGeneratorInterface|string $sql = null): Filter { return Filter::create($sql); }
	static public function and(null|SqlGeneratorInterface|string ...$sql): Filter {
		$filter = Filter::create();
		foreach ($sql as $sqlItem) $filter->and($sqlItem);
		return $filter;
	}
	static public function or(null|SqlGeneratorInterface|string ...$sql): Filter {
		$filter = Filter::create();
		foreach ($sql as $sqlItem) $filter->or($sqlItem);
		return $filter;
	}
	static public function cmp(string $field, ...$values): Comparison { return new Comparison($field, ...$values); }
	static public function entity(string $entity): SqlEntity { return new SqlEntity($entity); }
	static public function value(string $value): Value { return new Value($value); }
}