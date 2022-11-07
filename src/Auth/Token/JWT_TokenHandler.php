<?php namespace Atomino2\Auth\Token;

class JWT_TokenHandler implements TokenHandlerInterface {

	private readonly string $secret;

	public function __construct(string $secret) {
		$this->secret = $secret;
	}

	public function create(array|string|int $payload, array $header = []): string {
		$header["alg"] = "HS512";
		$header["typ"] = "JWTTokenHandler";
		$header = $this->encode(json_encode($header));
		$payload = $this->encode(json_encode($payload));
		$signature = $this->sign($header . "." . $payload);
		return $header . "." . $payload . "." . $signature;
	}

	public function resolve(string $token, &$header = null): array|string|int|false {
		return $this->checkSignature($token, $payload, $header) ? $payload : false;
	}

	private function checkSignature(string $token, &$payload = null, &$header = null): bool {
		$token = explode('.', $token);
		if (count($token) !== 3) return false;
		[$c_header, $c_payload, $c_signature] = $token;
		$payload = json_decode(base64_decode($c_payload), true);
		$header = json_decode(base64_decode($c_header), true);
		return $c_signature === $this->sign($c_header . '.' . $c_payload);
	}

	private function sign(string $string): string { return $this->encode(hash_hmac('sha512', $string, $this->secret, true)); }
	private function encode(string $string): string { return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string)); }
}