<?php namespace Atomino2\Config;

use Atomino\Neutrons\DotNotation;
use Atomino2\Config\FileFilter\ConfigFileFilterInterface;
use Atomino2\Config\Reader\ConfigReaderInterface;
use Atomino2\Config\ValueParser\ConfigValueParserInterface;

class ConfigLoader {

	private array $configuration = [];
	/** @var ConfigReaderInterface[] */
	private array $readers = [];
	/** @var ConfigFileFilterInterface[] */
	private array $filters = [];
	/** @var ConfigValueParserInterface[] */
	private array $valueParsers = [];

	public function getConfiguration(): array { return $this->configuration; }

	public function __construct() { }

	public function addValueParser(ConfigValueParserInterface $parser) {
		$this->valueParsers[] = $parser;
	}

	public function addFilter(ConfigFileFilterInterface $filter) {
		$this->filters[] = $filter;
	}
	public function addReader(string|array $extension, ConfigReaderInterface $reader) {
		if (!is_array($extension)) $extension = [$extension];
		foreach ($extension as $ext) $this->readers[$ext] = $reader;
	}

	public function loadList(string $root, string $commaseparated) { $this->load(...array_map(fn(string $path) => $root . '/' . trim($path), explode(",", $commaseparated))); }

	public function load(string ...$path) {
		foreach ($path as $pathItem) {
			$parts = explode('/', $pathItem);
			$pattern = array_pop($parts);
			$dir = join("/", $parts);

			if (!is_dir($dir)) return [];

			$cwd = getcwd();
			chdir($dir);

			$matches = glob($pattern);
			$files = [];
			foreach ($matches as $filename) {
				$file = realpath($dir . '/' . $filename);
				if (file_exists($file) && !is_dir($file)) $files[$filename] = $file;
			}
			foreach ($this->filters as $filter) $files = $filter->filter($files);
			foreach ($files as $file) $this->loadFile($file);

			chdir($cwd);
		}
	}

	private function loadFile(string $file) {
		$filename = pathinfo($file, PATHINFO_BASENAME);
		$extension = pathinfo($file, PATHINFO_EXTENSION);
		if (array_key_exists($extension, $this->readers)) {
			$reader = $this->readers[$extension];
			$config = $reader->read($filename);
			$this->mergeIntoConfig($config);
		}
	}

	private function mergeIntoConfig(array $config) {
		$config = DotNotation::flatten($config);
		foreach ($this->valueParsers as $parser) {
			foreach ($config as $key => $value) {
				$result = $parser->parse($key, $value);
				if ($result !== false) {
					unset($config[$key]);
					$config[$result["key"]] = $result["value"];
				}
			}
		}
		$config = DotNotation::extract($config);
		$this->configuration = array_replace_recursive($this->configuration, $config);
	}
}