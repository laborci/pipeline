<?php namespace Atomino2\Mercury\SmartResponder;

use Atomino2\Mercury\Responder\AbstractResponder;
use Atomino2\Mercury\SmartResponder\Attr\CSS;
use Atomino2\Mercury\SmartResponder\Attr\JS;
use Atomino2\Mercury\SmartResponder\Attr\Smart;
use Atomino2\Mercury\SmartResponder\Attr\Template;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

abstract class SmartResponder extends AbstractResponder {

	protected ParameterBag $smart;
	protected ParameterBag $smartData;
	protected ParameterBag $smartExtra;
	protected Environment $twig;

	protected string $template;

	private readonly SmartResponderEnv $env;

	public function __construct(SmartResponderEnv $env) { $this->env = $env; }

	public function run(): Response|null {

		$_template = Template::get(static::class);
		$this->template = $_template->template;
		$this->twig = $this->env->getTwigEnvironment();
		$this->env->setTwigLoaderMainNamespace($_template->namespace);
		$this->env->addTwigLoaderNamespace("smart", __DIR__ . '/@resources');

		$_smart = Smart::get(static::class);
		$class = $_smart ? $_smart->class : "";
		$language = $_smart ? $_smart->language : "EN";
		$title = $_smart ? $_smart->title : "Atomino";
		$favicon =  $_smart ? $_smart->favicon :"data:;base64,iVBORw0KGgo=";

		$js = array_unique(array_merge(...array_map(fn(JS $js)=>$js->js, JS::all(static::class))));
		$css = array_unique(array_merge(...array_map(fn(CSS $css)=>$css->css, CSS::all(static::class))));

		/** @var Request $originalRequest */
		$originalRequest = $this->ctx("original-request");
		$this->smartData = new ParameterBag();
		$this->smartExtra = new ParameterBag();
		$this->smart = new ParameterBag([
			"js"       => $js,
			"css"      => $css,
			"ver"      => $this->env->getFrontendVersion(),
			"class"    => $class,
			"language" => $language,
			"title"    => $title,
			"host"     => $originalRequest->getSchemeAndHttpHost(),
			"url"      => $originalRequest->getPathInfo(),
			"favicon"  => $favicon,
		]);

		return parent::run();
	}

	private function getPublicProperties() { return (new class { public function get($object): array { return get_object_vars($object); } })->get($this); }

	protected function render(): Response {
		$viewModel = $this->getPublicProperties();
		$smart = $this->smart->all();
		$smart["data"] = $this->smartData->all();
		$smart["encoded-data"] = base64_encode(json_encode($this->smartData->all()));
		$smart["extra"] = $this->smartExtra->all();

		return new Response($this->twig->render($this->template, ['smart' => $smart, 'model' => $viewModel,]));
	}
}

