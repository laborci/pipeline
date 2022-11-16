<?php namespace Atomino\Carbon\Plugins\Attachment;

use Atomino\Bundle\Attachment\Collection;
use Atomino\Bundle\Attachment\Config;
use Atomino\Bundle\Attachment\Storage;
use Atomino\Carbon\Attributes\EventHandler;
use Atomino\Carbon\Entity;
use function Atomino\dic;

trait AttachmentableTrait {

	private Storage|null $AttachmentPlugin_storage = null;
	private array $AttachmentPlugin_stored_attachments = ['files' => [], 'collections' => []];

	#[EventHandler(Entity::EVENT_ON_LOAD)]
	protected function AttachmentPlugin_onLoad() {
		$data = $this->{Attachmentable::fetch(static::model())->field};
		$this->AttachmentPlugin_stored_attachments = $data === [] ? ['files' => [], 'collections' => []] : $data;
	}

	#[EventHandler(Entity::EVENT_BEFORE_UPDATE, Entity::EVENT_BEFORE_INSERT)]
	protected function AttachmentPlugin_BeforeSave() {
		$this->{Attachmentable::fetch(static::model())->field} = is_null($this->AttachmentPlugin_storage) ? $this->AttachmentPlugin_stored_attachments : $this->AttachmentPlugin_storage->jsonSerialize();
	}

	#[EventHandler(Entity::EVENT_BEFORE_DELETE)]
	protected function AttachmentPlugin_BeforeDelete() {
		$this->getAttachmentStorage()->purge();
	}

	protected function getAttachmentCollection(string $name): Collection|null {
		if (is_null($this->id)) return null;
		return $this->getAttachmentStorage()->getCollection($name);
	}

	public function getAttachmentStorage(): Storage {
		/** @var Entity $entity */
		$entity = $this;

		return
			is_null($this->AttachmentPlugin_storage) ?
				$this->AttachmentPlugin_storage = new Storage(
					$entity,
					$this->AttachmentPlugin_stored_attachments,
					Attachmentable::fetch(static::model())->getCollections(),
					Attachmentable::fetch(static::model())->field,
					static::model()->getContainer()
				) :
				$this->AttachmentPlugin_storage;
	}
}