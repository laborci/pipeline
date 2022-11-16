<?php namespace App\Mission\Public;

class Router extends \App\Mission\Api\Router {
	protected function route(): void {
		$this(path: "/valami/:page=1")?->pipe(Page\IndexPage::class);
		$this(path: "/valami")?->pipe(Page\IndexPage::class);
		$this()?->pipe(Page\IndexPage::class);
	}
}