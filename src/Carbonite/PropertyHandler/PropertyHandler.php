<?php namespace Atomino2\Carbonite\PropertyHandler;

use Atomino2\Carbonite\Entity;
use Atomino2\Carbonite\Event\EventInterface;

abstract class PropertyHandler {

	private Entity $entity;
	private string $property;

	#region getters
	abstract protected function getValue(): mixed;
	final protected function getEntity(): Entity { return $this->entity; }
	final protected function getProperty(): string { return $this->property; }
	#endregion

	#region setup
	private function __setup(Entity $entity, $property, $value): void {
		$this->entity = $entity;
		$this->property = $property;
		$this->initialize($value);
	}
	abstract protected function initialize(mixed $value);
	#endregion

	#region entity bridge
	final protected function saveEntityProperty(): void {
		\Closure::bind(fn($property) => $this->saveProperty($property), $this->entity, Entity::class)($this->property);
	}
	final protected function addEntityEventListener(string|array $event, \Closure $handler): void {
		\Closure::bind(fn($event, $handler) => $this->addEventListener($event, $handler), $this->entity, Entity::class)($event, $handler);
	}
	final protected function dispatchEvent(EventInterface $event): void {
		\Closure::bind(fn($event) => $this->dispatchEvent($event), $this->entity, Entity::class)($event);
	}
	#endregion

}
