<?php namespace Atomino2\Watson\Debug\ANSIIFormatter;

use Atomino2\Cli\CliTree;
use Atomino2\Watson\Debug\FormatterInterface;
use Codedungeon\PHPCliColors\Color;

class UserFormatter implements FormatterInterface {

	public function format(mixed $payload, string|null $channel = null): string {
		$channel = ">";

		$text = Color::LIGHT_WHITE_ALT . Color::BG_BLUE . Color::BOLD . " " . $channel . " " . Color::RESET . ' ';

		$type = fn(string $string) => Color::LIGHT_GRAY . '(' . $string . ')' . Color::RESET . ' ';
		if (is_string($payload)) $text .= $type('string') . Color::YELLOW . '"' . $payload . '"';
		elseif (is_int($payload)) $text .= $type('int') . Color::LIGHT_YELLOW . $payload;
		elseif (is_float($payload)) $text .= $type('float') . Color::YELLOW . $payload;
		elseif (is_bool($payload)) $text .= $type('bool') . Color::BOLD . ($payload ? Color::LIGHT_GREEN . 'true' : Color::LIGHT_RED . 'false');
		elseif (is_null($payload)) $text .= $type('null') . Color::MAGENTA . 'null';
		elseif (is_resource($payload)) $text .= $type('resource') . Color::WHITE . $payload;

		elseif (is_array($payload)) {
			$text .= $type('array') . "\n";
			$text .= CliTree::draw($payload);
		} elseif (is_object($payload)) {
			$text .= $type(get_class($payload)) . "\n";
			$text .= CliTree::draw((array)$payload);
		} else $text .= '(unknown type)';

		return $text . Color::RESET;
	}
}
