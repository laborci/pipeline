<?php namespace Atomino2\Database\SmartStructure\Field;

class DateField extends Field{
	protected ?int $datetimePrecision;

	protected function __construct($descriptor){
		parent::__construct($descriptor);
		$this->datetimePrecision = $descriptor["DATETIME_PRECISION"];
		$this->autoInsert = !is_null($this->default) && strtoupper($this->default) === 'CURRENT_TIMESTAMP';
		$this->autoUpdate = str_contains(strtoupper($this->extra), 'CURRENT_TIMESTAMP');
	}



}