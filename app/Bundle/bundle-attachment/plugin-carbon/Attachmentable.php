<?php namespace Atomino\Carbon\Plugins\Attachment;

use Atomino\Bundle\Attachment\AttachmentableInterface;
use Atomino\Bundle\Attachment\Collection;
use Atomino\Carbon\Generator\CodeWriter;
use Atomino\Carbon\Model;
use Atomino\Carbon\Plugin\Plugin;
use Atomino\Carbon\Plugins\Attachment\AttachmentCollection;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Attachmentable extends Plugin {

	/** @var \Atomino\Carbon\Plugins\Attachment\Attachmentable\AttachmentCollection[] */
	private array $collections;
	public function __construct(public string $field = 'attachments') { }

	protected function init(Model $model) {
		$this->collections = AttachmentCollection::all($model->getEntityReflection());
	}

	public function generate(\ReflectionClass $ENTITY, CodeWriter $codeWriter) {
		$collections = AttachmentCollection::all($ENTITY);
		$codeWriter->addInterface(AttachmentableInterface::class);
		$codeWriter->addAttribute("#[Immutable( '" . $this->field . "', true )]");
		$codeWriter->addAttribute("#[Protect( '" . $this->field . "', false, false )]");
		$codeWriter->addAttribute("#[RequiredField( '" . $this->field . "', \Atomino\Carbon\Field\JsonField::class )]");

		foreach ($collections as $collection) {
			$codeWriter->addAnnotation("@property-read \\" . Collection::class . " \$" . $collection->field);
			$codeWriter->addCode("protected final function __get" . ucfirst($collection->field) . '(){return $this->getAttachmentCollection("' . $collection->field . '");}');
		}
	}
	public function getTrait(): string|null { return AttachmentableTrait::class; }

	/** @var \Atomino\Bundle\Attachment\AttachmentCollectionInterface[] */
	public function getCollections(): array { return $this->collections; }
}