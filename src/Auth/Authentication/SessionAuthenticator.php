<?php namespace Atomino2\Auth\Authentication;

class SessionAuthenticator implements AuthenticatorInterface {
	public function __construct(private $key = "AUTHENTICATED") { }
	public function authenticate(): string|bool { return array_key_exists($this->key, $_SESSION) ? $_SESSION[$this->key] : false; }
	public function set(string $id) { $_SESSION[$this->key] = $id; }
	public function drop() { unset($_SESSION[$this->key]); }
}