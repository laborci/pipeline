<?php namespace Atomino2\Database\SmartSQL;

use Atomino2\Database\Connection;

class SqlHelper {
	public function __construct(private Connection $connection) { }

	public function buildSql(string|SqlGeneratorInterface|null $sql): string {
		if (is_null($sql)) return "";
		if (is_string($sql)) return $sql;
		return $sql->getSql($this->connection);
	}

	private function keyValueToSqlList(array $data, string $pattern = ":e = :v", string $glue = 'AND', bool $swap = false): string {
		$data = array_map(function (array $item) use ($pattern, $swap) {
			$key = array_key_exists("key", $item) ? $item["key"] : $item[0];
			$value = array_key_exists("value", $item) ? $item["value"] : $item[1];
			return $this->expr($pattern, ...($swap ? [$value, $key] : [$key, $value]));
		}, $data);
		return join(" " . trim($glue) . " ", $data);
	}

	private function dictionaryToSqlList(array $data, string $pattern = ":e = :v", string $glue = 'AND', bool $swap = false): string {
		$data = array_map(fn($key, $value) => $this->expr($pattern, ...($swap ? [$value, $key] : [$key, $value])), array_keys($data), array_values($data));
		return join(" " . trim($glue) . " ", $data);
	}

	/**
	 * @param array $data
	 * @return array Pre quoted and escaped array
	 */
	private function parseDictionary(array $data): array {
		$values = [];
		foreach ($data as $key => $value) $values[$this->quoteEntity($key)] = $value instanceof Expression ? $value->getSQL($this->connection) : $this->quoteAndEscapeValue($value);
		return $values;
	}

	public function quoteEntity(string|Expression|null $subject): string {
		if ($subject instanceof Expression) return $subject->getSQL($this->connection);
		if (is_null($subject)) return "";
		else return join(".", array_map(fn($value) => trim($value) === "*" ? "*" : "`" . trim($value, "`\t\n\r\0\x0B") . "`", explode('.', $subject)));
	}
	public function quoteAndEscapeValue(null|string|Expression $subject): string {
		if ($subject instanceof Expression) return $subject->getSQL($this->connection);
		if (is_null($subject)) return " NULL ";
		else return $this->connection->getPdo()->quote($subject);
	}
	public function quoteValue(null|string|Expression $subject): string {
		if ($subject instanceof Expression) return $subject->getSQL($this->connection);
		if (is_null($subject)) return " NULL ";
		else return ("'" . trim($subject, "'") . "'");
	}
	public function escapeValue(null|string|Expression $subject): string {
		if ($subject instanceof Expression) return $subject->getSQL($this->connection);
		if (is_null($subject)) return " NULL ";
		else return trim($this->connection->getPdo()->quote($subject), "'");
	}

	/**
	 * Use templates for your SQLs:
	 * "SELECT * FROM :e WHERE :e = :v", $table, $idField, $id
	 *
	 * :e - for sql entities
	 * :v - for values to escape
	 * :e1, :e2, :e3, etc... - for sql entities at argument position
	 * :v1, :v2, :v3, etc...  - for values to escape at argument position
	 * :r - for raw or sqlBuilder value. If value is a dictionary it acts like (:d)
	 * :esc - for escape value only (without quotes)
	 * :d(glue=",", pattern=":e=:v") - for dictionaries (field=>value)
	 *  ["name"=>"elvis presley, "created"=>SQL::expr("Now()")] -> `name`='elvis presley', 'created'=>Now()
	 * :ds(glue=",", pattern=":v=:e") - same as d, but swaps pattern arguments (value first, key second)
	 * :dk(glue=",", pattern=":e") - same as d, but only uses the keys of a dictionary
	 * :dv(glue=",", pattern=":v") - same as d, but only uses the values of a dictionary
	 * :if('argument') - writes out argument if bool value is true
	 * :ifn('argument') - writes out argument when next value is true
	 * :ifp('argument') - writes out argument when previous value is true
	 *
	 * you can escape commands with a single backspace
	 *
	 * @param string|null $sql
	 * @param array $arguments
	 * @return string
	 */
	public function expr(string|null $sql, ...$arguments): string {

		if (is_null($sql)) return " ";
		if (count($arguments) === 0) return $sql;

		$regex = "/(?<=\W|^)(?<!\\\):(e[0-9]*|v[0-9]*|r|esc|d|ds|dk|dv|if|ifn|ifp)(?:\(\s*('.*?(?<!\\\)')\s*\))*(?=\W|$)/";
		$found = preg_match_all($regex, $sql, $matches);

		$argIndex = 0;

		for ($i = 0; $i < $found; $i++) {
			$pattern = $matches[0][$i];
			$command = $matches[1][$i];
			$commandArgs = [];
			if ($matches[2][$i]) {
				$regex = "/(?<=\s|,|^)'(.*?)(?<!\\\)'\s*(?=\s|,|$)/";
				preg_match_all($regex, $matches[2][$i], $commandArgs);
				$commandArgs = array_map(fn($arg) => str_replace("\\'", "'", $arg), $commandArgs[1]);
			}

			$fixIndex = null;
			if (strlen($command) > 1) {
				if (str_starts_with($command, 'e')) {
					$fixIndex = intval(substr($command, 1));
					$command = 'e';
				} elseif (str_starts_with($command, 'v')) {
					$fixIndex = intval(substr($command, 1));
					$command = 'v';
				}
			}

			$value = "";
			switch ($command) {
				case "e":
					$arg = $arguments[is_null($fixIndex) ? $argIndex : $fixIndex];
					[$cGlue] = array_replace([", "], $commandArgs);
					$value = is_array($arg) ? join($cGlue, array_map(fn($arg) => $this->quoteEntity($arg), $arg)) : $this->quoteEntity($arg);
					if(is_null($fixIndex)) $argIndex++;
					break;
				case "v":
					$arg = $arguments[is_null($fixIndex) ? $argIndex : $fixIndex];
					[$cGlue] = array_replace([", "], $commandArgs);
					$value = is_array($arg) ? join($cGlue, array_map(fn($arg) => $this->quoteAndEscapeValue($arg), $arg)) : $this->quoteAndEscapeValue($arg);
					if(is_null($fixIndex)) $argIndex++;
					break;
				case "esc":
					$arg = $arguments[$argIndex];
					[$cGlue] = array_replace([", "], $commandArgs);
					$value = is_array($arg) ? join($cGlue, array_map(fn($arg) => $this->escapeValue($arg), $arg)) : $this->escapeValue($arg);
					$argIndex++;
					break;
				case "r":
					$arg = $arguments[$argIndex];
					if (is_array($arg)) {
						[$cGlue, $cPattern] = array_replace([", ", ":e=:v"], $commandArgs);
						$value = array_is_list($arg)
							? join($cGlue, array_map(fn($arg) => $this->buildSql($arg), $arg))
							: $this->dictionaryToSqlList($arg, $cPattern, $cGlue);
					} else {
						$value = $this->buildSql($arg);
					}
					$argIndex++;
					break;
				case "d":
					$arg = $arguments[$argIndex];
					[$cGlue, $cPattern] = array_replace([", ", ":e=:v"], $commandArgs);
					if (is_array($arg)) $value = !array_is_list($arg) ? $this->dictionaryToSqlList($arg, $cPattern, $cGlue) : $this->keyValueToSqlList($arg, $cPattern, $cGlue);
					$argIndex++;
					break;
				case "ds":
					$arg = $arguments[$argIndex];
					[$cGlue, $cPattern] = array_replace([", ", ":v=:e"], $commandArgs);
					if (is_array($arg)) $value = !array_is_list($arg) ? $this->dictionaryToSqlList($arg, $cPattern, $cGlue, true) : $this->keyValueToSqlList($arg, $cPattern, $cGlue, true);
					$argIndex++;
					break;
				case "dk":
					$arg = $arguments[$argIndex];
					[$cGlue, $cPattern] = array_replace([", ", ":e"], $commandArgs);
					if (is_array($arg)) $value = !array_is_list($arg) ? $this->dictionaryToSqlList($arg, $cPattern, $cGlue) : $this->keyValueToSqlList($arg, $cPattern, $cGlue);
					$argIndex++;
					break;
				case "dv":
					$arg = $arguments[$argIndex];
					[$cGlue, $cPattern] = array_replace([", ", ":v"], $commandArgs);
					if (is_array($arg)) $value = !array_is_list($arg) ? $this->dictionaryToSqlList($arg, $cPattern, $cGlue, true) : $this->keyValueToSqlList($arg, $cPattern, $cGlue, true);
					$argIndex++;
					break;
				case "if":
					$arg = $arguments[$argIndex];
					if ($arg) $value = array_key_exists(0, $commandArgs) ? $this->expr($commandArgs[0], $arg) : $this->buildSql($arg);
					$argIndex++;
					break;
				case "ifp":
					$arg = $arguments[$argIndex - 1];
					if ($arg) $value = array_key_exists(0, $commandArgs) ? $commandArgs[0] : $this->buildSql($arg);
					break;
				case "ifn":
					$arg = $arguments[$argIndex];
					if ($arg) $value = array_key_exists(0, $commandArgs) ? $commandArgs[0] : $this->buildSql($arg);
					break;
			}

			$search = "/(?<!\\\)" . preg_quote($pattern, "/") . "/";
			$sql = preg_replace($search, $value, $sql, 1);
		}

		return trim(str_replace("\\:", ":", $sql));
	}
}