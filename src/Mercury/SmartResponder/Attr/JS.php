<?php namespace Atomino2\Mercury\SmartResponder\Attr;

use Atomino2\Util\Attr;
use Attribute;


#[Attribute( Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE )]
class JS extends Attr {
	public array $js;
	public function __construct(string ...$js){ $this->js = $js; }
}