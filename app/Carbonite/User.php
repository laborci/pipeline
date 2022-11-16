<?php namespace App\Carbonite;

use App\Bundle\Attachment\AttachmentHandler;
use App\Carbonite\Machine\__User;
use App\DefaultConnection;
use App\Services\Attachment\Collection;
use App\Services\Attachment\CollectionHandler;
use App\Services\Attachment\Storage;
use Atomino2\Carbonite\Carbonizer\Access;
use Atomino2\Carbonite\Carbonizer\Carbonite;
use Atomino2\Carbonite\Event\BeforeInsert;
use Atomino2\Carbonite\Event\BeforeUpdate;
use Atomino2\Carbonite\Event\EventInterface;
use DI\Container;
use Respect\Validation\ChainedValidator;
use Respect\Validation\Validator;

class User extends __User {

	public function __construct(private Storage $storage) { }

	protected static function carbonize(): Carbonite {
		return (new Carbonite(DefaultConnection::class, 'user', true))
			->property('email', access: Access::HIDDEN, validator: fn(ChainedValidator|Validator $validator) => $validator->email())
			->property('password', access: Access::HIDDEN)
			->relation('boss', 'bossId', User::class, 'workers')
			->initializer(function (Container $di) {
				$di->get(Storage::class)->addCollection(new Collection(static::class, 'avatar', 1000000, 10, 'image/*'));
			})
		;
	}

	protected function initialize(): void {
		$this->addEventListener([BeforeInsert::class, BeforeUpdate::class], fn($event) => $this->onSave($event));
	}
	protected function onSave(EventInterface $event) { $event->cancelEvent(); }
	protected function validateEntity(): void { }

	protected function __setPassword(string $password) { $this->set(static::password, md5($password)); }
	protected function __setEmail(?string $email) { $this->set(static::email, $email); }
	protected function __getEmail(): ?string { return $this->get(static::email); }
	protected function __getAvatar(): CollectionHandler|null { return $this->storage->getCollectionHandler($this, 'avatar'); }

}