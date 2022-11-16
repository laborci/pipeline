<?php namespace Atomino2\Mercury\SmartResponder;

use Twig\Cache\CacheInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class SmartResponderEnv {
	private FilesystemLoader $loader;
	private Environment      $twigEnvironment;
	private string           $frontendVersion;

	public function getFrontendVersion(): string { return $this->frontendVersion; }
	public function getTwigEnvironment(): Environment { return $this->twigEnvironment; }

	public function __construct(
		private readonly array      $namespaces,
		CacheInterface|string|false $cache = false,
		string|\Closure             $frontendVersion = "0",
		bool                        $debug = false
	) {
		$this->frontendVersion = (string)(is_callable($frontendVersion) ? $frontendVersion() : $frontendVersion);
		$this->loader = new FilesystemLoader();
		foreach ($this->namespaces as $namespace => $path) $this->loader->addPath($path, $namespace);
		$this->twigEnvironment = new Environment($this->loader, ["debug" => $debug, "auto_reload" => $debug]);
		$this->twigEnvironment->setCache($cache);
	}
	public function setTwigLoaderMainNamespace($namespace) { if (array_key_exists($namespace, $this->namespaces)) $this->loader->addPath($this->namespaces[$namespace], "__main__"); }
	public function addTwigLoaderNamespace($namespace, $path) { $this->loader->addPath($path, $namespace); }
}
