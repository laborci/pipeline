<?php namespace Atomino2\Cli;

use Codedungeon\PHPCliColors\Color;

class CliTree {


	static function draw(array $array, string|null $root = null) {

		$text = '';
		$treeLines = [];


		static::tree($array, $treeLines, [false]);

		foreach ($treeLines as $line) {
			$text .= Color::LIGHT_GRAY . $line['leaf'] . ' ' . Color::RESET;
			if ($line['hint']) {
				$text .= Color::CYAN . Color::BOLD . $line['key'] . Color::RESET;
				if ($line['hint'] === 'empty-array') $text .= Color::LIGHT_GRAY . ': ' . Color::WHITE . '[]' . Color::RESET;
				if ($line['type']) {
					$text .= Color::LIGHT_GRAY . ' (' . $line['type'] . ')' . Color::RESET;
				}
			} else {
				$text .= Color::BLUE . $line['key'] . Color::RESET;
				$text .= Color::LIGHT_GRAY . ': ' . Color::RESET;
				$text .= Color::BOLD . static::value($line['value']);
			}
			$text .= "\n" . Color::RESET;
		}
		return $text;
	}

	protected static function value($value): string {
		$text = '?';
		$type = fn(string $string) => '';
		if (is_string($value)) $text = $type('string') . Color::YELLOW . '"' . $value . '"';
		elseif (is_int($value)) $text = $type('int') . Color::LIGHT_YELLOW . $value;
		elseif (is_float($value)) $text = $type('float') . Color::YELLOW . $value;
		elseif (is_bool($value)) $text = $type('bool') . Color::BOLD . ($value ? Color::LIGHT_GREEN . 'true' : Color::RED . 'false');
		elseif (is_null($value)) $text = $type('null') . Color::LIGHT_GRAY . 'null';
		elseif (is_resource($value)) $text = $type('resource') . Color::WHITE . $value;
		return $text;
	}


	protected static function tree($branch, &$env, $level) {
		$branch = static::sort($branch);

		$keys = array_keys($branch);
		$last_key = end($keys);

		foreach ($branch as $key => $value) {
			$treeKey = explode("\0", $key);
			$treeKey = array_pop($treeKey);

			$leaf = '';
			for ($i = 1; $i < count($level); $i++) $leaf .= $level[$i] ? '   ' : ' │ ';
			$leaf .= $last_key === $key ? ' └─' : ' ├─';

			$hint = false;
			if (is_array($value)) $hint = count($value) ? 'array' : 'empty-array';
			if (is_object($value)) $hint = 'object';

			$type = false;
			if (is_object($value)) $type = get_class($value);

			if (is_object($value)) {
				if (count($level) === 1) $value = (array)$value;
				else $value = get_object_vars($value);
			}

			$env[] = [
				'leaf'  => $leaf,
				'key'   => $treeKey,
				'value' => $value,
				'hint'  => $hint,
				'type'  => $type,
			];

			if (is_array($value)) {
				$l = $level;
				$l[] = ($key === $last_key);
				static::tree($value, $env, $l);
			}
		}
	}

	protected static function sort(array $array): array {
		$values = [];
		$arrays = [];
		foreach ($array as $key => $value) {
			if (is_array($value)) $arrays[$key] = static::sort($value);
			else $values[$key] = $value;
		}
		ksort($values);
		ksort($arrays);
		return array_merge($values, $arrays);
	}
}

class Nothing { }

class ArrayValue { }

class EmptyArray { }