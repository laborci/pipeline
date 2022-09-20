<?php namespace Atomino2\Cli;

use Codedungeon\PHPCliColors\Color;

class CliTree {
	static function draw(array $array, string|null $root = null) {

		$text = '';
		$env = [];
		$array = static::sort($array);

		$keys = array_keys($array);
		$last_key = end($keys);
		foreach ($array as $key => $value) {
			if (is_object($value)) {
				$key .= ' <' . get_class($value) . '>';
				$value = (array)($value);
			}
			$env[] = [$key === $last_key ? ' └─' : ' ├─', $key, is_array($value) ? null : (is_null($value) ? '' : $value)];
			if (is_array($value)) static::tree($value, $env, [$key === $last_key]);

		}
		if (!is_null($root)) $text .= Color::LIGHT_CYAN_ALT . $root . Color::RESET . "\n";
		foreach ($env as $item) {
			$text .= (Color::WHITE . $item[0] . ' ' . Color::RESET);
			if (is_null($item[2])) {
				$text .= (Color::CYAN . $item[1] . Color::RESET) . "\n";
			} else {
				$text .= (Color::GREEN . Color::BOLD . $item[1] . Color::RESET . ': ');
				if ($item[2] === '') {
					$text .= (Color::GREEN . Color::BOLD . '-' . COLOR::RESET . "\n");
				} else {
					$text .= (Color::WHITE . $item[2] . Color::RESET . "\n");
				}
			}
		}
		return $text;
	}
	protected static function tree($branch, &$env, $level) {
		$keys = array_keys($branch);
		$last_key = end($keys);
		foreach ($branch as $key => $value) {
			if (is_object($value)) $value = get_object_vars($value);
			$leaf = '';
			for ($i = 0; $i < count($level); $i++) {
				$leaf .= $level[$i] ? '   ' : ' │ ';
			}
			$leaf .= $last_key === $key ? ' └─' : ' ├─';
			$env[] = [$leaf, $key, is_array($value) ? null : (is_null($value) ? '' : $value)];
			$l = $level;
			$l[] = ($key === $last_key);
			if (is_array($value)) static::tree($value, $env, $l);
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