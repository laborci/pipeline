<?php namespace Atomino2\Database\SmartStructure\Field;

abstract class OptionField extends Field{
	protected array $options = [];
	public function getOptions(): array{ return $this->options; }

	protected function __construct($descriptor){
		parent::__construct($descriptor);
		preg_match_all("/'(.*?)'/", $this->typeString, $matches);
		$this->options = $matches[1];
	}
}