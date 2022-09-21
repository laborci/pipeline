<?php

use Atomino2\Application\ApplicationInterface;
use Atomino2\Debug\Debug;
use Atomino2\DILoader\DILoader;
use Symfony\Component\Dotenv\Dotenv;

include 'vendor/autoload.php';
putenv("ROOT=" . __DIR__);

function debug(mixed ...$data) {
	foreach ($data as $item) Debug::getInstance()?->debug($item, "user");
}

(new Dotenv())
	->usePutenv()
	->load(getenv("ROOT") . '/etc/.env')
;

(new DILoader())
	->loadList(getenv("ROOT"), getenv("DI"))
	->build(getenv("DI_COMPILED_CONTAINER") ? (getenv("ROOT") . "/" . getenv("DI_CC")) : null)
	->get(ApplicationInterface::class)
	->run()
;
