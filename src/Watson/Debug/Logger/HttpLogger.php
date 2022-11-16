<?php namespace Atomino2\Watson\Debug\Logger;

use Atomino2\Watson\Debug\FormatterInterface;

class HttpLogger extends AbstractLogger {

	protected string $url;

	public function __construct(FormatterInterface $formatter, string $url) {
		$this->url = $url;
		parent::__construct($formatter);
	}

	public function write($message) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_exec($ch);
		curl_close($ch);
	}
}


