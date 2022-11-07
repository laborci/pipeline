<?php namespace Atomino2\Auth\Authentication;

interface AuthenticationServiceInterface {
	public function getAuthenticated(): AuthenticableInterface|null;
	public function checkCredentials(string $login, string $password): bool;
}