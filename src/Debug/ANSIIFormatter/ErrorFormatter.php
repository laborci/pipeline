<?php namespace Atomino2\Debug\ANSIIFormatter;

use Atomino2\Debug\FormatterInterface;
use Atomino2\ErrorHandler\Error;
use Codedungeon\PHPCliColors\Color;

class ErrorFormatter implements FormatterInterface {

	public function __construct(private readonly string $root = "") {}

	private function formatFile(string|null $file): string {
		if(is_null($file)) return Color::LIGHT_YELLOW."?unknown?";
		if (str_starts_with($file, $this->root)) $file = trim(substr($file, strlen($this->root)), '/');
		$dir = Color::YELLOW . pathinfo($file, PATHINFO_DIRNAME);
		$file = Color::LIGHT_YELLOW . pathinfo($file, PATHINFO_BASENAME);
		return $dir . "/" . $file;
	}

	private function formatClass(string|null $class): string {
		if(is_null($class)) return Color::LIGHT_CYAN_ALT."?unknown?";
		$parts = explode("\\", $class);
		$class = Color::LIGHT_CYAN_ALT . array_pop($parts);
		$ns = Color::BLUE . join("\\", $parts) . "\\";
		return $ns . $class;
	}

	public function format(mixed $payload, string|null $channel = null): string {

		if ($payload instanceof Error) {
			$text = Color::LIGHT_YELLOW . Color::BG_LIGHT_RED . Color::BOLD . " " . $payload->getErrno() . " " . Color::RESET . ' ';
			$text .= Color::LIGHT_RED_ALT . $payload->getErrstr() . "\n";
			$text .= $this->formatFile($payload->getErrfile()) . COLOR::YELLOW . ' (' . $payload->getErrline().")";
			$traces = $payload->getTrace();
			if (count($traces)) {
				$text .= "\n";
				foreach ($traces as $trace) {
					$text .= Color::LIGHT_RED_ALT . Color::BOLD . '↖ ';
					$text .= ($this->formatFile($trace["file"]) ?? 'unknown') . COLOR::YELLOW . ' (' . ($trace['line'] ?? 'unknown').")";
					$text .= Color::RED . Color::BOLD . " " . (array_key_exists('class', $trace) ? $this->formatClass($trace['class']) : 'unknown') . " ".
						Color::GRAY . (array_key_exists('type', $trace) ? $trace['type'] : '?') . " ".
						Color::RED . $trace['function'] . "\n";
				}
			}
			return $text . Color::RESET;
		} else {
			return "ERROR OCCURED";
		}
	}
}
