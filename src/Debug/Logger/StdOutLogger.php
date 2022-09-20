<?php namespace Atomino2\Debug\Logger;

use Atomino2\Debug\FormatterInterface;
use Psr\Log\LoggerInterface;

class StdOutLogger extends AbstractLogger {
	public function write($message) {
		$fp = fopen("php://stdout", "w");
		fwrite($fp, $message . "\n");
		fclose($fp);
	}
}


//**		$ch = curl_init();
//		curl_setopt($ch, CURLOPT_URL, $this->url);
//		curl_setopt($ch, CURLOPT_POST, true);
//		curl_setopt($ch, CURLOPT_POSTFIELDS, $record['formatted']);
//		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//		curl_exec($ch);
//		curl_close($ch);