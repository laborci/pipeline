<?php namespace App;

use App\Carbonite\Article;
use App\Carbonite\Store\ArticleStore;
use App\Carbonite\Store\UserStore;
use App\Carbonite\User;

use Atomino2\Application\ApplicationInterface;
use Atomino2\Carbonite\Carbonizer\Carbonizer;
use Atomino2\Carbonite\Carbonizer\Model;
use Atomino2\Carbonite\Validation\CarboniteValidationException;
use Atomino2\Cli\CliCommand;
use Atomino2\Cli\CliModule;
use Atomino2\Cli\CliTree;
use Atomino2\Cli\Command;
use Atomino2\Cli\Style;
use Atomino2\Util\CodeFinder;
use DI\Container;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Exceptions\ValidatorException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;

class CarboniteCli extends CliModule {

	public function __construct(private Carbonizer $carbonizer) { }

	#[Command("carbonite:update", "update", "Updte Carbonite entities")]
	public function update(CliCommand $command) {
		$command->define(function (Input $input, Output $output, Style $style) {
			$this->carbonizer->update($style);
		});
	}
	#[Command("carbonite:create", "create", "Create new Carbonite entity")]
	public function creates(CliCommand $command) {
		$command->addArgument('entity');
		$command->define(function (Input $input, Output $output, Style $style) {
			$this->carbonizer->create($input->getArgument('entity'), $style);
		});
	}
}

class ApplicationCli implements ApplicationInterface {
	public function __construct(
		Container $di,
		ArticleStore $articleStore
	) {

		debug($articleStore->pick(1)->author->articles->get()[0]->author->email);
//
////		$user = $userStore->pick(1);
////		debug($user->name);
die();

		$carbonizer = $di->get(Carbonizer::class);
		$carbonizer->init(\App\Carbonite::class, \App\Carbonite\Store::class, \App\Carbonite\Machine::class);

		$application = new Application();
		$carboniteCli = new CarboniteCli($carbonizer);
		$application->addCommands($carboniteCli->getCommands());
		$application->run();
		die();

//
//
//		/** @var CodeFinder $codeFinder */
//		$codeFinder = $di->get(CodeFinder::class);
//		$path = $codeFinder->Psr4ResolveNamespace(\App\Entity::class);
//
//		$classes = $codeFinder->Psr4ClassSeeker(\App\Carbonite::class, '*.php', false);
//		debug($classes);

//		$carbonizer->analyzeEntity(User::class, __DIR__."/Carbonite/models.php");
//		$carbonizer->initialize(
//		);

		$user = $userStore->pick(1);
		$users = $userStore->collect(1, 35);
		$user->bossId = 1;
		debug($user->boss->name);
//		debug($user->boss->workers->get());
//		$user = $userStore->create();
//		$user->name = "Elvis Presley";
//		$user->email = "elvis@elvis.hu";
//		debug($user->isDirty());
//
//		try {
//			$user->save();
//			debug("success");
//		} catch (CarboniteValidationException $e) {
//			debug("error");
//			foreach ($e->getPropertyValidationExceptions() as $exception) debug($exception->getProperty(), $exception->getMessages());
//			debug($e->getUniqueConstraintViolationException()?->getMessage());
//			debug($e->getEntityValidationException()?->getMessages());
//		}


//		/** @var EntityModel $ed */
//		$ed = include __DIR__."/Carbonite/models.php";
//
//
//		try{
//			$ed->getProperty('email')->assert("asdf.heu", "{{property}}");
//		}catch (\Exception $exception){
//			debug($exception->getMessages());
//		}

//		['email']->getValidator->assert("elvis@elvis.hu");

//		$reflection = new \ReflectionClass(User::class);
		//		$user = $userStore->create();
//		$user->password->set('galaga');
//		$user->save();
//		debug($user->export());
//		debug($user->password->checkPassword('galaga'));
//		$user->email = "elviselvishu";


//		$user->email = null;
//		try {
//			$user->validate();
//			debug("success");
//		} catch (CarboniteValidationException $e) {
//			debug("error");
//			debug($e->getEntityValidationException()?->getMessages());
//		}
	}
}