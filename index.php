<?php
include "vendor/autoload.php";

use Atomino2\Pipeline\Context;
use DI\ContainerBuilder;

$builder = new ContainerBuilder();
$builder->addDefinitions([
	\Atomino2\DIContainerInterface::class => \DI\factory(fn(\DI\Container $di) => new \Atomino2\PhpDIContainer($di)),
]);
$di = $builder->build();

class MyHandler extends \Atomino2\Pipeline\Handler {
	public static function setup(string $message) { return self::make(func_get_args()); }
	public function handle(string $message = "") {
		echo "MyHandler running <" . $message . ">\n";
		if (!$this->isLast()) return $this->next() . "!!!";
		else return ".";
	}
}

class MyHandler2 extends \Atomino2\Pipeline\Handler {
	#[Context] private string $a = "";
	#[Context] private string $contextNotExists = "initialvalue";
	public static function setup(string $message) { return self::make(func_get_args()); }
	public function handle(string $message = "HELLOKA!") {
		echo "MyHandler2 running <" . $message . ">\n";
		if ($message === "x") $this->break();
		try {
			$result = $this->next();
		} catch (\Atomino2\Pipeline\Exceptions\EndOfPipelineException $e) {
			$result = '...';
		}
		echo "Context  <" . $this->a . ">\n";
		echo "Context  <" . $this->contextNotExists . ">\n";
		return $result;
	}
}

$pipeline = $di->make(\Atomino2\Pipeline\PipelineRunner::class);

echo $pipeline
	->add($pipeline()->pipe(MyHandler::setup("hello1"))->pipe(MyHandler2::setup("x")))
	->add($pipeline()->pipe(MyHandler::setup("hello2"))->pipe(MyHandler2::class))
	->exec(["a" => 24])
;

echo $pipeline()->pipe(MyHandler::setup("STANADLONEPIPELINE"))->pipe(MyHandler2::setup("xxx"))->exec();