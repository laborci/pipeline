<?php namespace Atomino2\Carbonite\Event;

use Atomino2\Carbonite\Entity;

abstract class EventInterface {

	private bool                   $cancelled     = false;
	private null|\Throwable|string $cancelMessage = null;

	public function getItem(): Entity { return $this->item; }


	public function __construct(private Entity $item) { }

	public function isCancelled(): bool { return $this->cancelled; }

	public function getCancelMessage(): ?string {
		if (is_null($this->cancelMessage)) return null;
		if (is_string($this->cancelMessage)) return $this->cancelMessage;
		return $this->cancelMessage->getMessage();
	}

	public function getCancelException(): ?\Throwable {
		if (is_null($this->cancelMessage)) return null;
		if (is_string($this->cancelMessage)) return new \Exception($this->cancelMessage);
		return $this->cancelMessage;
	}

	public final function cancelEvent(null|\Throwable|string $message = null): void {
		$this->cancelled = true;
		$this->cancelMessage = $message;
	}
}