<?php namespace Atomino2\Mercury\SmartResponder\Attr;

use Atomino2\Util\Attr;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Template extends Attr {
	public array $alternativeTemplates;
	public function __construct(public string $namespace, public string $template) { }
}
