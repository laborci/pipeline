<?php namespace Atomino2\Auth\Token;

class JWT_TTL_TokenHandler extends JWT_TokenHandler {

	private int $ttl;

	public function __construct(string $secret, int $ttl) {
		parent::__construct($secret);
		$this->ttl = $ttl;
	}

	public function create(array|int|string $payload, array $header = []): string {
		$header["issued"] = time();
		if (!array_key_exists("ttl", $header)) $header["ttl"] = $this->ttl;
		return parent::create($payload, $header);
	}

	public function resolve(string $token, &$header = []): array|string|int|false {
		$payload = parent::resolve($token, $header);
		if ($payload === false) return false;
		if ($header["ttl"] > 0 && $header["issued"] + $header["ttl"] < time()) return false;
		return $payload;
	}
}