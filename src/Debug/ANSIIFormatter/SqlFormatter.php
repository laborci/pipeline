<?php namespace Atomino\Bundle\Debug\ChannelFormatter;

use Codedungeon\PHPCliColors\Color;
use Atomino2\Debug\FormatterInterface;

class SqlFormatter implements FormatterInterface {
	public function format(mixed $payload, string|null $channel = null): string {
		$text = Color::BLACK . Color::BG_LIGHT_YELLOW . Color::BOLD . " sql " . Color::RESET . ' ';
		$text.= $payload;
		return $text.Color::RESET;
	}
}
