<?php namespace Atomino2\Carbonite\Carbonizer;

#[\Attribute(\Attribute::TARGET_CLASS)]
class CarbonizedModel {
	private Model|null $model = null;
	public function __construct(private string $serializedModel) { }
	public function getModel(): Model {
		if (is_null($this->model)) $this->model = unserialize($this->serializedModel);
		return $this->model;
	}
}