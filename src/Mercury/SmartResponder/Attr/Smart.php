<?php namespace Atomino2\Mercury\SmartResponder\Attr;

use Atomino2\Util\Attr;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Smart extends Attr {
	public function __construct(
		public string $title = 'Atomino',
		public string $language = 'HU',
		public string $class = '',
		public string $favicon = "data:;base64,iVBORw0KGgo="
	) {
	}
}
