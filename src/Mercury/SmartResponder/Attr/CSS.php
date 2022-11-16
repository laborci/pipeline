<?php namespace Atomino2\Mercury\SmartResponder\Attr;

use Atomino2\Util\Attr;
use Attribute;


#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class CSS extends Attr {
	public array $css;
	public function __construct(string ...$css) { $this->css = $css; }
}