<?php namespace App\Services\Attachment;
class CollectionHandlerIterator  extends \ArrayIterator {
	public function __construct(private \Closure $getItems) { parent::__construct(($this->getItems)()); }
	public function current(): Attachment|null { return ($this->getItems)()[$this->key()]; }
}
