<?php namespace Atomino2\Debug\ANSIIFormatter;

use Atomino2\Cli\CliTree;
use Atomino2\Debug\FormatterInterface;
use Codedungeon\PHPCliColors\Color;

class UserFormatter implements FormatterInterface {

	public function format(mixed $payload, string|null $channel = null): string {
		$channel = ">";

		$text = Color::LIGHT_WHITE_ALT . Color::BG_BLUE . Color::BOLD . " " . $channel . " " . Color::RESET . ' ';

		$type = fn(string $string) => Color::LIGHT_GRAY . Color::BG_BLACK . $string . ' ' . Color::RESET;
		$value = fn(string $string) => Color::LIGHT_CYAN . Color::BG_BLACK . Color::BOLD . $string . ' ' . Color::RESET;

		if (is_string($payload)) $text .= $value($payload);
		elseif (is_int($payload)) $text .= $type('int') . $value($payload);
		elseif (is_float($payload)) $text .= $type('float') . $value($payload);
		elseif (is_bool($payload)) $text .= $type('bool') . $value($payload ? Color::GREEN . 'true' : Color::RED . 'false');
		elseif (is_null($payload)) $text .= $type('null');
		elseif (is_resource($payload)) $text .= $type('resouce') . $value($payload);
		elseif (is_array($payload)) {
			$text .= $type('array') . "\n";
			$text .= CliTree::draw($payload);
		} elseif (is_object($payload)) {
			$text .= $type(get_class($payload)) . "\n";
			$text .= CliTree::draw((array)($payload));
		} else $text .= '(unknown type)';

		return $text.Color::RESET;
	}
}