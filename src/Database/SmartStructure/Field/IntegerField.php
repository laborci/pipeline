<?php namespace Atomino2\Database\SmartStructure\Field;

class IntegerField extends NumericField{
	protected function __construct($descriptor){
		parent::__construct($descriptor);
		$this->autoIncrement = strtoupper($this->extra) === 'AUTO_INCREMENT';
	}
}