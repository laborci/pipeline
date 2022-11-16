<?php namespace Atomino\Bundle\Attachment;

class CollectionIterator extends \ArrayIterator {
	public function __construct(private Collection $collection) { parent::__construct($collection->files); }
	public function current(): Attachment|null { return $this->collection->storage->getAttachment($this->collection->files[$this->key()]); }
}