<?php namespace App\Mission;

use App\Mission\Public\Page\IndexPage;
use Atomino2\Mercury\Router\Router;

class MissionRouter extends Router {
	protected function route() {
		$this(host: "api.**")->pipe(Api\Router::class);
		$this(host: "www.**")->pipe(Public\Router::class);
		$this()->pipe(IndexPage::setup("ERROR 404 Host not found"));
	}
}