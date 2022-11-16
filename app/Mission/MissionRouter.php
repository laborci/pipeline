<?php namespace App\Mission;

use App\Mission\Public\Page\IndexPage;
use Atomino2\Mercury\Pipeline\Router;

//use Atomino2\Mercury\Router\Router;

class MissionRouter extends Router {
	protected function route():void {
		$this(host: "api.**")?->pipe(Api\Router::class);
		$this(host: "www.**")?->pipe(Public\Router::class);
		$this()?->pipe(IndexPage::class);
	}
}