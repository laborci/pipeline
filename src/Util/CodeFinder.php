<?php namespace Atomino2\Util;

use Composer\Autoload\ClassLoader;

class CodeFinder {

	public function __construct(private ClassLoader $classLoader, private PathResolver $pathResolver) { }

	public function Psr4ClassSeeker(string $namespace, string $pattern = '*.php', bool $recursive = true): array {
		$path = $this->Psr4ResolveNamespace($namespace);
		return array_map(
			function ($file) use ($namespace, $path) { return $namespace . "\\" . str_replace("/", "\\", substr($file, strlen($path), -4)); },
			$this->fileSeeker($path, $pattern, $recursive)
		);
	}
	public function Psr4FileSeeker(string $namespace, string $pattern = '*.php'): array {
		$path = $this->Psr4ResolveNamespace($namespace);
		return !is_null($path) ? $this->fileSeeker($path, $pattern) : [];
	}

	public function Psr4ResolveNamespace(string $namespace): string|null {
		$path = $this->Psr4Resolve($namespace);
		return !is_null($path) ? $path . '/' : null;
	}

	public function Psr4ResolveClass(string $class): string|null {
		$path = $this->Psr4Resolve($class);
		return !is_null($path) ? $path . '.php' : null;
	}

	public function Psr4Resolve(string $name): string|null {
		/** @var ClassLoader $cl */
		$prefixesPsr4 = $this->classLoader->getPrefixesPsr4();
		$segments = explode('\\', $name);
		$realpath = [];
		$path = null;

		do {
			$ns = join('\\', $segments) . '\\';
			$path = array_key_exists($ns, $prefixesPsr4) ? $prefixesPsr4[$ns][0] : null;
			if (!is_null($path)) {
				$result = ($path . "/" . join("/", $realpath));
				return $this->pathResolver->realpath($result);
			}
			array_unshift($realpath, array_pop($segments));
		} while (!empty($segments));

		return null;
	}

	public function fileSeeker(string $path, string $pattern = '*', bool $recursive = true): array {
		$result = [];
		$files = glob($path . $pattern);
		foreach ($files as $file) if (is_file($file)) $result[] = $file;
		if ($recursive) {
			$dirs = glob($path . '*', GLOB_ONLYDIR);
			foreach ($dirs as $dir) $result = array_merge($result, static::fileSeeker($dir . '/', $pattern));
		}
		$result = array_map(fn($path)=>$this->pathResolver->realpath($path), $result);
		return $result;
	}

}