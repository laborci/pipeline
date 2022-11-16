<?php namespace Atomino2\Watson\Debug\Logger;

class StdOutLogger extends AbstractLogger {
	public function write($message) {
		$fp = fopen("php://stdout", "w");
		fwrite($fp, $message . "\n");
		fclose($fp);
	}
}
