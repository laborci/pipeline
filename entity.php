<?php

use App\Carbonite\User;
use Atomino2\Carbonite_OLD\ORMDescriptor;

interface SQLGeneratorInterface {
	public function getSQL(PDO $PDO): string;
}

class Where implements SQLGeneratorInterface {
	const NOT = 1 << 0;
	const AND = 1 << 1;
	const OR = 1 << 2;

	/** @var Where[] */
	private array $chain = [];
	private PDO $PDO;

	protected function __construct(null|Stringable|SQLGeneratorInterface|string $sql = null) {
		if (!is_null($sql)) $this->chain[] = ["operator" => 0, "sql" => $sql];
	}

	static public function create(null|Stringable|SQLGeneratorInterface|string $sql = null) {
		return new static($sql);
	}


	public function and(null|Stringable|SQLGeneratorInterface|string $sql) {
		$this->chain[] = ["operator" => self::AND, "sql" => $sql];
		return $this;
	}
	public function or(null|Stringable|SQLGeneratorInterface|string $sql) {
		$this->chain[] = ["operator" => self::OR, "sql" => $sql];
		return $this;
	}
	public function andNot(null|Stringable|SQLGeneratorInterface|string $sql) {
		$this->chain[] = ["operator" => self::AND + self::NOT, "sql" => $sql];
		return $this;
	}
	public function orNot(null|Stringable|SQLGeneratorInterface|string $sql) {
		$this->chain[] = ["operator" => self::OR + self::NOT, "sql" => $sql];
		return $this;
	}

	public function getSQL(PDO $PDO): string {
		$this->PDO = $PDO;
		$output = " ( ";
		foreach ($this->chain as $index => $item) {
			$exp = $item["sql"];
			if(is_null($exp)) continue;
			if (is_object($exp) && in_array(SQLGeneratorInterface::class, class_implements($exp))) $exp = $exp->getSQL($this->PDO);
			if ($index > 0 && strlen($exp)) {
				if ($item["operator"] & self::AND) $output .= " AND ";
				if ($item["operator"] & self::OR) $output .= " OR ";
				if ($item["operator"] & self::NOT) $output .= " NOT ";
			}
			$output .= $exp;
		}
		$output .= " ) ";
		return $output;
	}
}

abstract class SqlQuoter {

	const FIELD_RAW = 0;
	const FIELD_QUOTE_ENTITY = 1;
	const VALUE_RAW = 0;
	const VALUE_QUOTE_ENTITY = 1;
	const VALUE_QUOTE_AND_ESCAPE = 2;
	const VALUE_ESCAPE = 3;

	protected int $fieldQuoteMode = self::FIELD_QUOTE_ENTITY;
	protected int $valueQuoteMode = self::VALUE_QUOTE_AND_ESCAPE;

	protected $PDO;

	private function quoteEntity(?string $subject): string { return join(".", array_map(fn($value) => "`" . trim($value, "`") . "`", explode('.', $subject))); }
	private function quoteAndEscapeValue(?string $subject): string { return $this->PDO->quote($subject); }
	private function escapeValue(?string $subject): string { return trim($this->PDO->quote($subject), "'"); }
	protected function quoteField(?string $subject, int|null $mode = null) {
		if (is_null($mode)) $mode = $this->fieldQuoteMode;
		return $mode === self::FIELD_RAW ? $subject : $this->quoteEntity($subject);
	}
	protected function quoteValue(?string $subject, string|null $mode = null): string {
		if (is_null($mode)) $mode = $this->valueQuoteMode;
		return match ($mode) {
			self::VALUE_ESCAPE => $this->escapeValue($subject),
			self::VALUE_QUOTE_AND_ESCAPE => $this->quoteAndEscapeValue($subject),
			self::VALUE_QUOTE_ENTITY => $this->quoteEntity($subject),
			default => $subject
		};
	}
	protected function useArgs(string $sql, array $arguments) {
		if (count($arguments)) {
			foreach ($arguments as $key => $arg) {
				$key = $key + 1;
				if (str_contains($sql, ":" . $key)) {
					$value = is_array($arg) ? join(',', array_map(fn($arg) => $this->quoteValue($arg, self::VALUE_QUOTE_AND_ESCAPE), $arg)) : $this->quoteValue($arg, self::VALUE_QUOTE_AND_ESCAPE);
					$sql = str_replace(':' . $key, $value, $sql);
				}
				if (str_contains($sql, "$" . $key) && !is_array($arg)) {
					$sql = str_replace('$' . $key, $arg, $sql);
				}
				if (str_contains($sql, "@" . $key) && !is_array($arg)) {
					$value = $this->quoteField($arg, self::FIELD_QUOTE_ENTITY);
					$sql = str_replace('@' . $key, $value, $sql);
				}
			}
		}
		return $sql;
	}

}

abstract class Compare extends SqlQuoter implements SQLGeneratorInterface {

	protected string|null $field;
	public function __construct(string $field) { $this->field = $field; }
	abstract protected function createSQL(): string;
	public function getSQL(PDO $PDO): string {
		$this->PDO = $PDO;
		return $this->createSQL();
	}
	public function quoting(?int $value = null, ?int $field = null): static {
		if (!is_null($field)) $this->fieldQuoteMode = $field;
		if (!is_null($value)) $this->valueQuoteMode = $value;
		return $this;
	}
}


class CompareEquals extends Compare {
	private array $value;
	public function __construct(string $field, mixed ...$value) {
		parent::__construct($field);
		$this->value = $value;
	}
	public function createSQL(): string {
		$field = $this->quoteField($this->field);
		if (count($this->value) === 1) {
			if (is_null($this->value[0])) return $field . " IS NULL";
			else return $field . " = " . $this->quoteValue($this->value[0]);
		}
		if (count($this->value) === 0) return "";
		return $field . " IN (" . join(",", array_map(fn($item) => $this->quoteValue($item), $this->value)) . ")";
	}
}

class CompareNotEquals extends Compare {
	private array $value;
	public function __construct(string $field, mixed ...$value) {
		parent::__construct($field);
		$this->value = $value;
	}
	public function createSQL(): string {
		$field = $this->quoteField($this->field);
		if (count($this->value) === 1) {
			if (is_null($this->value[0])) return $field . " IS NOT NULL";
			else return $field . " != " . $this->quoteValue($this->value[0]);
		}
		if (count($this->value) === 0) return "";
		return $field . " NOT IN (" . join(",", array_map(fn($item) => $this->quoteValue($item), $this->value)) . ")";
	}
}

class CompareExpression extends SqlQuoter implements SQLGeneratorInterface {
	private array $arguments;
	public function __construct(private readonly string $sql, mixed ...$arguments) {
		$this->arguments = $arguments;
	}
	public function getSQL(PDO $PDO): string {
		$this->PDO = $PDO;
		return $this->useArgs($this->sql, $this->arguments);
	}
}


class Comparision {
	public function __construct(private string $field) { }
	public function equals(...$value): Compare { return new CompareEquals($this->field, ...$value); }
	public function isNull(): Compare { return new CompareEquals($this->field, null); }
	public function isNotNull(): Compare { return new CompareNotEquals($this->field, null); }
}

class SQL {
	static public function equals(string $field, ...$value): SQLGeneratorInterface { return new CompareEquals($field, ...$value); }
	static public function notEquals(string $field, ...$value): SQLGeneratorInterface { return new CompareNotEquals($field, ...$value); }
	static public function isNull(string $field): SQLGeneratorInterface { return new CompareEquals($field, null); }
	static public function isNotNull(string $field): SQLGeneratorInterface { return new CompareNotEquals($field, null); }
	static public function in(string $field, array $values): SQLGeneratorInterface { return new CompareEquals($field, ...$values); }
	static public function notIn(string $field, array $values): SQLGeneratorInterface { return new CompareNotEquals($field, ...$values); }

	static public function exp(string $sql, ...$arguments): SQLGeneratorInterface { return new CompareExpression($sql, ...$arguments); }

	static public function where(null|Stringable|SQLGeneratorInterface|string $sql = null): Where { return Where::create($sql); }
	static public function and(null|Stringable|SQLGeneratorInterface|string ...$sql): Where {
		$filter = Where::create();
		foreach ($sql as $sqlItem) $filter->and($sqlItem);
		return $filter;
	}
	static public function or(null|Stringable|SQLGeneratorInterface|string ...$sql): Where {
		$filter = Where::create();
		foreach ($sql as $sqlItem) $filter->or($sqlItem);
		return $filter;
	}
	static public function field(string $field): Comparision { return new Comparision($field); }
}

$pdo = new PDO("mysql:host=localhost;dbname=ap;user=root;password=root");

echo SQL::where("asdf")
        ->or(
	        SQL::and(
		        SQL::field("`name11`.`namefasz`")->equals("valami", "másvalami"),
		        SQL::field("name2.1234")->equals(null)
	        )
        )
        ->andNot(SQL::field("name")->equals("%vala'mi%", "megi'ntvalami"))
        ->orNot(
	        SQL::where(SQL::equals("name", "hello"))
	           ->and(SQL::equals("a", "b"))
	           ->or(SQL::exp("`fasszom` = @1 OR `faszom` = :2", "almafasz.bela", "balfasz"))
	           ->or(SQL::isNull("béla"))
	           ->or(null)
	           ->or(SQL::isNotNull("béla"))

        )->getSQL($pdo)
;


/*
- static::OPERATOR_IS => "${field} = {$this->quote($this->value)}",
- static::OPERATOR_IS_NULL => "${field} IS NULL",
- static::OPERATOR_IS_NOT_NULL => "${field} IS NOT NULL",
- static::OPERATOR_NOT_EQUAL => "${field} != {$this->quote($this->value)}",
- static::OPERATOR_NOT_IN => (empty($this->value) ? "" : "${field} NOT IN (" . join(',', array_map(fn($value) => $this->quote($value), $this->value)) . ")"),
- static::OPERATOR_IN => (empty($this->value) ? "" : "${field} IN (" . join(',', array_map(fn($value) => $this->quote($value), $this->value)) . ")"),

static::OPERATOR_LIKE => "${field} LIKE {$this->quote($this->value)}",
static::OPERATOR_IN_STRING => "${field} LIKE '%{$this->quote($this->value, self::QUOTE_WITHOUT_QM)}%'",
static::OPERATOR_STARTS => "${field} LIKE '%{$this->quote($this->value, self::QUOTE_WITHOUT_QM)}''",
static::OPERATOR_ENDS => "${field} LIKE '{$this->quote($this->value, self::QUOTE_WITHOUT_QM)}%'",

static::OPERATOR_GLOB => "${field} LIKE {$this->quote(strtr($this->value, ['*'=>'%', '?'=>'_']))}",
static::OPERATOR_REV_GLOB => "{$this->quote($this->value)} LIKE REPLACE(REPLACE(${field}, '*', '%'),'?','_')",
static::OPERATOR_REV_LIKE => "{$this->quote($this->value)} LIKE ${field}",
static::OPERATOR_REGEX => "${field} REGEXP '{$this->value}'",

static::OPERATOR_GT => "${field} > {$this->quote($this->value)}",
static::OPERATOR_GTE => "${field} >= {$this->quote($this->value)}",
static::OPERATOR_LT => "${field} < {$this->quote($this->value)}",
static::OPERATOR_LTE => "${field} <= {$this->quote($this->value)}",
static::OPERATOR_BETWEEN => "${field} BETWEEN {$this->quote($this->value[0])} AND {$this->quote($this->value[1])}",

static::OPERATOR_JSON_CONTAINS => "JSON_CONTAINS(${field}, {$this->quote($this->value[0])}, '{$this->value[1]}')",
static::OPERATOR_JSON_NOT_CONTAINS => "NOT JSON_CONTAINS(${field}, {$this->quote($this->value[0])}, '{$this->value[1]}')",
*/



//is("name", equals("1"));

//
//
//
//abstract class Chain implements \Stringable {
//
//	const NOT = 1;
//	const AND = 2;
//	const OR = 4;
//
//	protected int $operator = 0;
//	protected Chain|null $chained = null;
//
//	private function chain(null|Chain $chained, int $operator): Chain {
//		if (is_null($chained)) return $this;
//		$this->operator = $operator;
//		$this->chained = $chained;
//		return $chained;
//	}
//	public function and(null|Chain $chained) { return $this->chain($chained, self::AND); }
//	public function or(null|Chain $chained) { return $this->chain($chained, self::OR); }
//	public function andNot(null|Chain $chained) { return $this->chain($chained, self::AND + self::NOT); }
//	public function orNot(null|Chain $chained) { return $this->chain($chained, self::OR + self::NOT); }
//
//	protected function chainString() {
//		$output = "";
//		if ($this->operator !== 0 && $this->chained !== null) {
//			if ($this->operator & self::AND) $output .= " AND ";
//			if ($this->operator & self::OR) $output .= " OR ";
//			if ($this->operator & self::NOT) $output .= " NOT ";
//			$output .= $this->chained;
//		}
//		return $output;
//	}
//}
//
//class WhereBuilder extends Chain {
//	public function __construct(private null|Chain $filter) { }
//	public function __toString(): string {
//		$output = " ( ";
//		$output .= $this->filter;
//		$output .= $this->chainString();
//		$output .= " ) ";
//		return $output;
//	}
//}
//
//class Comparision {
//
//	public function __construct(private string $field) { }
//
//	public function eq($value) {
//		return new CompareEquals($this->field, $value);
//	}
//}
//
//abstract class Compare extends Chain {
//
//}
//
//class CompareEquals extends Compare {
//	public function __construct(private $field, private $value) { }
//	public function __toString(): string {
//		return $this->field ."=".$this->value;
//	}
//}
//
//
//function is(string $field) {
//	return new Comparision($field);
//}
//function filter(null|Chain $comparison) { return new WhereBuilder($comparison); }
//
//$filter = filter(is("name")->eq("elvis"))->and(is("email")->eq("ewer"));
//
//echo $filter;

//echo filter(filter($filter1->and(is("email")->eq("elvis@elvis.hu")))->or(is("field")->eq("valami")));


//class Foo {
//
//	private $data = "John";
//
//	public function sayHello() { echo "HELLó " . $this->data; }
//	public function getBar() {
//		$setter = fn($value) => $this->data = $value;
//		return new Bar($setter);
//	}
//}
//
//class Bar {
//	public function __construct(private \Closure $setter) { }
//	public function set($name) { ($this->setter)($name); }
//	public function mySet($name, $obj){
//		Closure::bind(fn($value)=>$this->data = $value, $obj, $obj)($name);
//	}
//}
//
//$foo = new Foo();
//$bar = $foo->getBar();
//$foo->sayHello();
//
//$bar->set("Elton");
//$foo->sayHello();
//
//$bar->mySet("BÉÉÉLA", $foo);
//$foo->sayHello();


//
//include 'vendor/autoload.php';
//
//$user = new User();
//
//$descriptor = new ORMDescriptor();
//$descriptor->registerEntity(User::class);
//$descriptor->analyze();
//
////$descriptor->getEntityDescriptor(User::class)->addEventHandler("MyEvent", "methodname");
////$descriptor->getEntityDescriptor(User::class)->addEventListener("MyEvent", "attachment");
//
//echo \Brick\VarExporter\VarExporter::export($descriptor->getDescription());
//
//
//$user->role = \App\Entity\Carbonite\User_Role::Admin;
//$user->groups[] = \App\Entity\Carbonite\User_Group::from("user");
//echo $user->role->value;
//var_dump($user->groups);
//echo (string)\App\Entity\Carbonite\User_Role::Admin;