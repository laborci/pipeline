<?php namespace Atomino2\DILoader;

use DI\ContainerBuilder;

class DILoader {

	private ContainerBuilder $builder;
	/** @var string[] */
	private array $definitions = [];

	public function __construct() {
		$this->builder = new ContainerBuilder();
		$this->builder->useAutowiring(true);
	}

	public function build(string|null $compiledContainer = null) {
		if (!is_null($compiledContainer)) $this->builder->enableCompilation(dirname($compiledContainer), pathinfo($compiledContainer, PATHINFO_FILENAME));
		if (is_null($compiledContainer) || !file_exists($compiledContainer)) $this->loadDefinitions();
		return $this->builder->build();
	}

	public function loadList(string $root, string $commaSeparated): static {
		$list = array_map(fn(string $path) => $root . '/' . trim($path), explode(",", $commaSeparated));
		$this->load(...$list);
		return $this;
	}

	public function load(string ...$path): static {
		array_push($this->definitions, ...$path);
		return $this;
	}

	private function loadDefinitions() {
		$definitions = [];
		foreach ($this->definitions as $path) {
			$parts = explode('/', $path);
			$pattern = array_pop($parts);
			$dir = join("/", $parts);
			if (is_dir($dir)) {
				$cwd = getcwd();
				chdir($dir);
				$matches = glob($pattern);
				$files = [];
				foreach ($matches as $filename) {
					$file = realpath($dir . '/' . $filename);
					if (file_exists($file) && !is_dir($file)) $files[$filename] = $file;
				}
				array_push($definitions, ...array_values($files));
				chdir($cwd);
			}
		}
		$this->builder->addDefinitions(...$definitions);
	}
}