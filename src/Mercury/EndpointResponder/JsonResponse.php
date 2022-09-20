<?php

namespace Atomino2\Mercury\EndpointResponder;

class JsonResponse extends \Symfony\Component\HttpFoundation\JsonResponse {
	public function __construct(mixed $data = null, int $status = 200, array $headers = [], bool $json = false) {
		parent::__construct($data, $status, $headers, $json);
		$this->setEncodingOptions(JSON_PRETTY_PRINT | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	}
}