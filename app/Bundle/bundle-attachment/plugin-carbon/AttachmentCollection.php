<?php namespace Atomino\Carbon\Plugins\Attachment;

use Atomino\Bundle\Attachment\AttachmentCollectionInterface;
use Atomino\Carbon\Plugin\PluginAttributeInterface;
use Atomino\Neutrons\Attr;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class AttachmentCollection extends Attr implements PluginAttributeInterface, AttachmentCollectionInterface {
	public function __construct(
		public string $field,
		public int $maxCount = 0,
		public int $maxSize = 0,
		public string|null $mimetype = null,
	) {
	}
}
