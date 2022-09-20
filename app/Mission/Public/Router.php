<?php namespace App\Mission\Public;

class Router extends \Atomino2\Mercury\Router\Router {
	protected function route() {
		$this(path: "/valami/:page=1")->pipe(Page\IndexPage::setup("Helloka"));
		$this(path: "/valami")->pipe(Page\IndexPage::setup("Beelloka"));
		$this()->pipe(Page\IndexPage::setup("ERROR-404"));
	}
}