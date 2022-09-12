<?php namespace Atomino2\Mercury\Router\Matcher;

class PatternProcessor {

	const TAILMODE_START = 1;
	const TAILMODE_END = 2;
	const TAILMODE_NONE = 0;

	private string|false $tail = false;
	private array $parameters = [];
	private string $regex;

	public function getTail(): bool|string { return $this->tail; }
	public function getParameters(): array { return $this->parameters; }

	public function __construct(
		string $separator,
		string $pattern,
		int    $tailMode = 0,
	) {
		$this->regex = $this->parsePattern($separator, $pattern, $tailMode);
	}

	private function parsePattern(string $separator, string $pattern, int $tailmode) {
		$tailRegex = "(?<__TAIL__>" . preg_quote($separator) . ".*?|.{0})";

		$segments = explode($separator, trim($pattern, $separator));

		$tail = false;
		if ($tailmode === self::TAILMODE_END && $tail = end($segments) == '**') array_pop($segments);
		elseif ($tailmode === self::TAILMODE_START && $tail = reset($segments) == '**') array_shift($segments);
		if (count($segments) === 0 && $tail) return "%^(?'__TAIL__'.*)$%";

		$segmentRegexes = [];
		foreach ($segments as $segment) $segmentRegexes[] = $this->parseSegment($segment, $separator);
		$segmentRegex = join(preg_quote($separator), $segmentRegexes);

		return '%^/*' .
			($tail && $tailmode === self::TAILMODE_START ? $tailRegex : "") .
			$segmentRegex .
			($tail && $tailmode === self::TAILMODE_END ? $tailRegex : "") .
			'/*$%';
	}

	private function parseSegment(string $segment, string $separator) {
		if ($segment === '*') return '[^/]+';
		elseif (preg_match('/^:(?<optional>\??)(?<name>.*?)(\((?<pattern>.+?)\))?(=(?<default>.*))?$/', $segment, $matches)) {
			$pattern = (array_key_exists('pattern', $matches) && strlen($matches['pattern'])) ? $matches['pattern'] : '[^/]+';
			$name = (array_key_exists('name', $matches) && strlen($matches['name'])) ? $matches['name'] : null;
			$optional = array_key_exists('optional', $matches) && strlen($matches['optional']);
			$default = array_key_exists('default', $matches) && strlen($matches['default']) ? $matches["default"] : false;
			if ($default && !is_null($name)) {
				$optional = true;
				$this->parameters[$name] = $default;
			}
			if (!is_null($name)) $pattern = "(?'" . $matches['name'] . "'" . $pattern . ")";
			if ($optional) $pattern = '?(' . preg_quote($separator) . $pattern . '|.{0})';
			return $pattern;
		} else return $segment;
	}


	public function match(string $subject): bool {
		if (preg_match($this->regex, $subject, $result)) {
			$result = array_filter($result, function ($key) { return !is_numeric($key); }, ARRAY_FILTER_USE_KEY);
			$result = array_map(function ($value) { return urldecode($value); }, $result);
			if (array_key_exists('__TAIL__', $result)) {
				$tail = $result['__TAIL__'];
				$this->tail = strlen($tail) ? $tail : false;
				unset($result['__TAIL__']);
			}
			foreach ($result as $key => $value) if ($value) $this->parameters[$key] = $value;
			return true;
		}
		return false;
	}
}