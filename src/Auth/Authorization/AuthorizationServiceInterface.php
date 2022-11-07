<?php namespace Atomino2\Auth\Authorization;

interface AuthorizationServiceInterface {
	public function authorize(AuthorizableInterface $user, string $role):bool;
}