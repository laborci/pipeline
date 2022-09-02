<?php
include "vendor/autoload.php";

use Atomino2\Pipeline\Attributes\Context;
use Atomino2\Pipeline\Attributes\Argument;
use Atomino2\Pipeline\Exceptions\EndOfPipelineException;
use Atomino2\Pipeline\Handler;
use Atomino2\Pipeline\PipelineRunner;
use DI\ContainerBuilder;

$builder = new ContainerBuilder();
$builder->addDefinitions([
	\Atomino2\DIContainerInterface::class => \DI\factory(fn(\DI\Container $di) => new \Atomino2\PhpDIContainer($di)),
]);
$di = $builder->build();

class MyHandler extends Handler {
	#[Argument] private string $message;

	public static function setup(string $message) { return self::make(func_get_args()); }
	public function handle() {
		echo "MyHandler running <" . $this->message . ">\n";
		if (!$this->isLast()) return $this->next() . "!!!";
		else return ".";
	}
}

class MyHandler2 extends Handler {
	#[Context] private string $a;
	#[Context] private string $contextNotExists = "initialvalue";
	#[Argument] private string $message;

	public static function setup(string $message) { return self::make(func_get_args()); }
	public function handle() {
		echo "MyHandler2 running <" . $this->message . ">\n";
		if ($this->message === "x") $this->break();
		try {
			$result = $this->next();
		} catch (EndOfPipelineException $e) {
			$result = '...';
		}
		echo "Context  <" . $this->a . ">\n";
		echo "Context  <" . $this->contextNotExists . ">\n";
		return $result;
	}
}

$pipeline = $di->make(PipelineRunner::class);

echo $pipeline
	->add($pipeline()->pipe(MyHandler::setup("hello1"))->pipe(MyHandler2::setup("x")))
	->add($pipeline()->pipe(MyHandler::setup("hello2"))->pipe(MyHandler2::setup("xxxx")))
	->exec(["a" => 24])
;

echo $pipeline()->pipe(MyHandler::setup("STANADLONEPIPELINE"))->pipe(MyHandler2::setup("xxx"))->exec(["a"=>1]);