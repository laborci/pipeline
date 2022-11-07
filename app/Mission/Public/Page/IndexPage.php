<?php namespace App\Mission\Public\Page;

use App\Carbonite\Store\UserStore;
use Atomino2\Mercury\SmartResponder\Attr\Template;
use Atomino2\Mercury\SmartResponder\SmartResponder;
use Atomino2\Mercury\SmartResponder\SmartResponderEnv;
use Symfony\Component\HttpFoundation\Response;

#[Template("public", "index.twig")]
class IndexPage extends SmartResponder {

	public string $name;
	public string $friend;

	public function __construct(SmartResponderEnv $env, private UserStore $userStore) { parent::__construct($env); }


	public function respond(): Response {
		$this->userStore::search()->first();
		$this->friend = "John Lennon";
		$this->name = "Elvis Presley";
		return $this->render();
	}
}