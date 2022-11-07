<?php namespace Atomino2\Auth\Authentication;

use Atomino2\Auth\Token\TokenHandlerInterface;

class HTTPBearerAuthenticator implements AuthenticatorInterface {

	public function __construct(private readonly TokenHandlerInterface $tokenHandler) { }

	public function authenticate(): string|bool {
		$token = $this->getAuthBearerToken();
		return is_null($token) ? false : $this->tokenHandler->resolve($token);
	}

	private function getAuthBearerToken(): null|string {
		$header = null;
		if (isset($_SERVER['Authorization'])) $header = trim($_SERVER["Authorization"]);
		elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) $header = trim($_SERVER["HTTP_AUTHORIZATION"]);
		if (!empty($header) && preg_match('/Bearer\s(\S+)/', $header, $matches)) return $matches[1];
		return null;
	}

	public function createToken(string $id): string { return $this->tokenHandler->create($id); }

}