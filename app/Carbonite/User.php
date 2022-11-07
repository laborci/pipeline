<?php namespace App\Carbonite;

use App\Carbonite\Machine\__User;
use App\DefaultConnection;
use Atomino2\Carbonite\Carbonizer\Access;
use Atomino2\Carbonite\Carbonizer\Carbonite;
use Atomino2\Carbonite\Event\BeforeInsert;
use Atomino2\Carbonite\Event\BeforeUpdate;
use Atomino2\Carbonite\Event\EventInterface;
use Atomino2\Carbonite\Validation\EntityValidationException;
use Respect\Validation\ChainedValidator;
use Respect\Validation\Validator;

class User extends __User {

	public function __construct() { }

	protected static function carbonize(): Carbonite {
		return (new Carbonite(DefaultConnection::class, 'user', true))
			->property('email', validator: fn(ChainedValidator|Validator $validator) => $validator->email())
			->property('password', access: Access::HIDDEN)
		;
	}

	protected function initialize(): void {
		$this->addEventListener([BeforeInsert::class, BeforeUpdate::class], fn($event) => $this->onSave($event));
	}

	protected function onSave(EventInterface $event) {
		//		$event->cancelEvent();
	}

	protected function validateEntity(): null|EntityValidationException { return null; }


	protected function __setPassword(string $password) { $this->set(static::password, md5($password)); }

	protected function __setEmail(?string $email) { $this->set(static::email, $email); }
	protected function __getEmail(): ?string { return $this->get(static::email); }

//	protected function __handleAttachments(): AttachmentHandler {
//		return $this->di->make(AttachmentHandler::class)
//		                ->addCollection("avatar", maxFileSize: 10000, maxFileCount: 1)
//		                ->addCollection("gallery", maxFileSize: 10000, maxFileCount: 8)
//		;
//	}

//	private function __getAvatar(): AttachmentCollection { return $this->attachments->getCollection('avatar'); }
//	private function __getGallery(): AttachmentCollection { return $this->attachments->getCollection('gallery'); }

}