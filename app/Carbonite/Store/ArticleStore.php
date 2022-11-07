<?php namespace App\Carbonite\Store;

use App\Carbonite\Machine\__ArticleFinder;
use App\Carbonite\Article;
use Atomino2\Carbonite\EntityStore;
use Atomino2\Database\SmartSQL\Select\Filter;

/**
 * Do not modify these annotations:
 * @method __ArticleFinder search(Filter $filter)
 * @method Article|null belongsTo(int|null $id)
 * @method Article[] belongsToMany(int[] $ids)
 * @method __ArticleFinder|null hasMany(string $property, int|null $id)
 * @method Article pick(int $id)
 * @method Article[] collect(...$ids)
 * @method Article build(array $data)
 * @method Article create()
 */
class ArticleStore extends EntityStore {
	protected const entity = Article::class;
}