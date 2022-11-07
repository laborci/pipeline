<?php namespace App\Mission\Public;

class Router extends \Atomino2\Mercury\Router\Router {
	protected function route() {
		$this(path: "/valami/:page=1")->pipe(Page\IndexPage::class);
		$this(path: "/valami")->pipe(Page\IndexPage::class);
		$this()->pipe(Page\IndexPage::class);
	}
}