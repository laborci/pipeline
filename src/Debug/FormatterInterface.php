<?php

namespace Atomino2\Debug;

interface FormatterInterface {
	public function format(mixed $payload, string|null $channel = null): string;
}