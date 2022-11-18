<?php namespace App\Mission\Cli;

use App\Carbonite\Store\UserStore;
use Atomino2\Cli\CliCommand;
use Atomino2\Cli\CliModule;
use Atomino2\Cli\Command;
use Atomino2\Cli\Style;
use Atomino2\Attachment\Storage;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;

class TestCli extends CliModule {

	public function __construct(private Storage $storage, private UserStore $userStore) {
	}

	#[Command("test")]
	public function creates(CliCommand $command) {

//		$filter = Filter::create(User::id(12))->and(User::created());
//		$this->userStore->search($filter->and())->page(12, $page);

		$command->define(function (Input $input, Output $output, Style $style) {
			$user = $this->userStore->pick(1);
			//debug($user->avatar->first->setTitle('FASZA'));
			debug($user->avatar->first->img?->crop(132, 332)->hires(1)->webp);

//			try {
//				$user->avatar->addFile(new File(__DIR__ . '/szkoko.pdf'));
//			} catch (AttachmentException $exception) {
//				debug($exception->getMessage());
//			}
//			try {
//				$user->avatar->addFile(new File(__DIR__ . '/avatar.jpg'));
//			} catch (AttachmentException $exception) {
//				debug($exception->getMessage());
//			}

//			$user->avatar->first->img

			//$user->avatar->setAttachmentTitle(2, 'Ez a kép');
//			$handler = $this->storage->getCollectionHandler($user, 'avatar');
//			foreach ($handler as $attachment)debug($attachment);
//			debug($handler->get('ava'));
//			$handler->moveToPosition(15, 3);
//			$file = $this->storage->addFile(new File(__DIR__ . '/avatar.jpg'));
//			$handler->getAttachments();
//			debug($file);
		});
	}
}