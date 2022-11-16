<?php namespace Atomino2\Mercury\Pipeline;

use DI\Container;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PipeLine {
	/** @var Handler[] */
	private array   $handlers = [];
	private Request $request;
	private Context $context;
	private Request $originalRequest;

	public function __construct(private Container $di) { }

	public function pipe(string|\Closure $handler, array $args = []):static {
		$this->handlers[] = ['handler' => $handler, 'args' => $args];
		return $this;
	}

	public function stream(array $handlers): static {
		foreach ($handlers as $handler) {
			if (is_array($handler)) {
				$args = count($handler) > 1 ? $handler[1] : [];
				$handler = $handler[0];
			} else {
				$args = [];
			}
			$this->pipe($handler, $args);
		}
		return $this;
	}

	public function replace(string|\Closure $handler, array $args = []): static {
		$this->handlers = [];
		$this->pipe($handler, $args);
		return $this;
	}

	public function replaceStream(array $handlers): static {
		$this->handlers = [];
		$this->stream($handlers);
		return $this;
	}

	public function next(?Request $request = null): ?Response {
		if (count($this->handlers) === 0) return null;
		if (!is_null($request)) $this->request = $request;
		$handlerDesc = array_shift($this->handlers);
		$args = $handlerDesc['args'];
		$handlerName = $handlerDesc['handler'];
		/** @var Handler $handler */
		$handler = $this->di->make($handlerName);
		\Closure::bind(function (PipeLine $pipeline, Request $request, Context $context, array $args) {
			$this->pipeline = $pipeline;
			$this->request = $request;
			$this->context = $context;
			$this->args = new ParameterBag($args);
		}, $handler, Handler::class)($this, $this->request, $this->context, $args);
		return $handler->handle();
	}

	public function __invoke(?Request $request = null, ?ParameterBag $context = null): ?Response {
		$this->originalRequest = $this->request = $request ?? Request::createFromGlobals();
		$this->context = $context ?? new Context($this->originalRequest);
		return $this->next($request);
	}
}