<?php namespace App\Carbonite;

use App\Carbonite\Machine\__Article;
use Atomino2\Carbonite\Carbonizer\Carbonite;

class Article extends __Article {

	public function __construct() { }

	protected static function carbonize(): Carbonite {
		return (new Carbonite(\App\DefaultConnection::class, 'article', true))
			->relation('author', User::class, 'authorId', 'articles')
		;
	}
}