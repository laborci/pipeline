<?php namespace App;

use App\Mission\Cli\TestCli;
use Atomino2\Application\ApplicationInterface;
use Atomino2\Carbonite\Cli\CarboniteCli;
use Symfony\Component\Console\Application;

class ApplicationCli implements ApplicationInterface {
	public function __construct(
		CarboniteCli $carboniteCli,
		TestCli      $testCli
	) {
		$application = new Application();
		$application->addCommands($carboniteCli->getCommands());
		$application->addCommands($testCli->getCommands());
		$application->run();
	}
}