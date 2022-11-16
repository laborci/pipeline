<?php namespace Atomino2\Mercury;

use Atomino2\Watson\Logger\Logger;

class HttpRequestLogger extends Logger {
	/**
	 * @param \Symfony\Component\HttpFoundation\Request $payload
	 * @return string
	 */
	protected function stringify($payload): string {
		return "[" . $payload->getMethod() . "] " . $payload->getSchemeAndHttpHost() . $payload->getRequestUri();
	}
}