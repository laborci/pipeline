<?php namespace App\Bundle\Password;

use Atomino2\Carbonite\PropertyHandler\PropertyHandler;

class TagHandler extends PropertyHandler {

	private ?string $passwordHash;

	protected function getValue(): string { return $this->passwordHash; }
	protected function initialize(mixed $value): void {
		$this->passwordHash = $value;
	}

	public function set(string $password) { $this->passwordHash = $this->hash($password); }
	public function checkPassword(string $password): bool { return $this->hash($password) === $this->passwordHash; }
	private function hash(string $string): string { return md5($string); }
}
