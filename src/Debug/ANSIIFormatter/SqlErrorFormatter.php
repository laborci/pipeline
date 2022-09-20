<?php namespace Atomino\Bundle\Debug\ChannelFormatter;

use Atomino2\Debug\FormatterInterface;
use Codedungeon\PHPCliColors\Color;

class SqlErrorFormatter implements FormatterInterface {
	public function format(mixed $payload, string|null $channel = null): string {
		$text = Color::LIGHT_YELLOW . Color::BG_LIGHT_RED . Color::BOLD . " sql " . Color::RESET . ' ';
		$text .= Color::LIGHT_RED_ALT . $payload["error"] . "\n";
		$text .= Color::WHITE . "sql: " . $payload["sql"];
		return $text . Color::RESET;
	}
}
