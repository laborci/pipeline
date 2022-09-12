<?php

use Atomino2\Application\ApplicationInterface;
use Atomino2\DILoader\DILoader;
use Symfony\Component\Dotenv\Dotenv;

include 'vendor/autoload.php';
putenv("ROOT=" . __DIR__);

(new Dotenv())
	->usePutenv()
	->load(getenv("ROOT") . '/etc/.env')
;

(new DILoader())
	->loadList(getenv("ROOT"), getenv("DI"))
	->build(getenv("DI_CC") ? (getenv("ROOT") . "/" . getenv("DI_CC")) : null)
	->get(ApplicationInterface::class)
	->run()
;
