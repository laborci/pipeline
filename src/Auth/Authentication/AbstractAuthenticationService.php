<?php namespace Atomino2\Auth\Authentication;

abstract class AbstractAuthenticationService implements AuthenticationServiceInterface {

	protected AuthenticableInterface|null|false $user;

	public function __construct(private readonly AuthenticatorInterface $authenticator) { }

	abstract function createUser(string $id): AuthenticableInterface|null;
	abstract public function checkCredentials(string $login, string $password): bool;

	public function getAuthenticated(bool $force = false): AuthenticableInterface|null {
		if ($this->user === false || $force) $this->user = $this->createUser($this->authenticator->authenticate());
		return $this->user;
	}
}
