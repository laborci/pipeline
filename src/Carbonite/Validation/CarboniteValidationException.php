<?php namespace Atomino2\Carbonite\Validation;

class CarboniteValidationException extends \Exception {

	private string $entityShortName;
	private readonly string $entityClass;
	private readonly ?int $entityId;
	public function getEntityClass(): string { return $this->entityClass; }
	public function getEntityShortName(): string { return $this->entityShortName; }
	public function getEntityId(): ?int { return $this->entityId; }

	public function isValid(): bool { return is_null($this->entityValidationException) && is_null($this->uniqueConstraintViolationException) && 0 === count($this->propertyValidationExceptions); }

	/** @var PropertyValidationException[] */
	private array $propertyValidationExceptions = [];
	private ?EntityValidationException $entityValidationException = null;
	private ?UniqueConstraintViolationException $uniqueConstraintViolationException = null;
	/**
	 * @return PropertyValidationException[]
	 */
	public function getPropertyValidationExceptions(): array { return $this->propertyValidationExceptions; }
	public function getEntityValidationException(): ?EntityValidationException { return $this->entityValidationException; }
	public function getUniqueConstraintViolationException(): ?UniqueConstraintViolationException { return $this->uniqueConstraintViolationException; }

	public function __construct(
		string $entityClass,
		?int   $entityId
	) {
		$this->entityId = $entityId;
		$this->entityClass = $entityClass;
		$this->entityShortName = (new \ReflectionClass($this->entityClass))->getShortName();
		parent::__construct();
	}

	public function setEntityValidationException(EntityValidationException|null $exception) {
		if (is_null($exception)) return;
		$this->entityValidationException = $exception;
	}
	public function addPropertyValidationException(PropertyValidationException|null $exception) {
		if (is_null($exception)) return;
		$this->propertyValidationExceptions[] = $exception;
	}
	public function setUniqueConstrainViolationException(UniqueConstraintViolationException|null $exception) {
		if (is_null($exception)) return;
		$this->uniqueConstraintViolationException = $exception;
	}
}