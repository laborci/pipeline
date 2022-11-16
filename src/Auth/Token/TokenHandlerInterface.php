<?php namespace Atomino2\Auth\Token;

interface TokenHandlerInterface {
	public function create(array|string|int $payload, array $header = []): string;
	public function resolve(string $token, &$header = null): array|string|int|false;
}