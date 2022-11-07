<?php namespace Atomino2\Carbonite\Carbonizer\Property\Type;

use Atomino2\Carbonite\Carbonizer\Property;

abstract class NumTypeProperty extends Property {
	protected bool $signed;
	public function isSigned(): bool { return $this->signed; }
}