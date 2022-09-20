<?php namespace Atomino2\Debug\ANSIIFormatter;

use Atomino2\Debug\FormatterInterface;
use Codedungeon\PHPCliColors\Color;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class HttpRequestFormatter implements FormatterInterface {

	/**
	 * @param Request $payload
	 * @return string
	 */
	public function format(mixed $payload, string|null $channel = null): string {
		$text = Color::BLACK . Color::BG_LIGHT_MAGENTA . Color::BOLD . " " . $payload->getScheme() . " " . Color::RESET ;
		$text.= Color::LIGHT_WHITE_ALT.Color::BG_MAGENTA.' '.$payload->getMethod().' '.Color::RESET.' ';
		$text.= Color::MAGENTA.$payload->getHost().' ';
		$text.= Color::LIGHT_MAGENTA_ALT.$payload->getPathInfo();
		$text.= $payload->getQueryString() ? Color::MAGENTA.' ?'.urldecode($payload->getQueryString()) : '';
		return $text.Color::RESET;
	}
}