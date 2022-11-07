<?php

use Atomino2\Application\ApplicationInterface;
use Atomino2\Application\PreBootInterface;
use Atomino2\Debug\Debug;
use Atomino2\DILoader\DILoader;
use Symfony\Component\Dotenv\Dotenv;

include 'vendor/autoload.php';
putenv("ROOT=" . __DIR__);

function debug(mixed ...$data): void { foreach ($data as $item) Debug::getInstance()?->debug($item); }
function debugf(string $format, ...$args) { \debug(sprintf($format, ...$args)); }
function inject(object $object, string $property, mixed $value, ?string $scope = null): void { \Closure::bind(fn($property, $value) => $this->$property = $value, $object, $scope ?: $object)($property, $value); }
function staticInject(string $class, string $property, mixed $value): void {
	$ref = new ReflectionClass($class);
	if ($ref->hasProperty($property)) {
		$prop = $ref->getProperty($property);
		$prop->setAccessible(true);
		$prop->setValue($value);
		$prop->setAccessible(false);
	}
}

(function (): void {
	(new Dotenv())
		->usePutenv()
		->load(getenv("ROOT") . '/etc/.env')
	;

	$di = (new DILoader())
		->loadList(getenv("ROOT"), getenv("DI"))
		->build(getenv("DI_COMPILED_CONTAINER") ? (getenv("ROOT") . "/" . getenv("DI_CC")) : null)
	;

	if ($di->has(PreBootInterface::class)) $di->get(PreBootInterface::class);
	$di->get(ApplicationInterface::class);

})();

