<?php namespace App\Carbonite\Store;

use App\Carbonite\Machine\__UserFinder;
use App\Carbonite\User;
use Atomino2\Carbonite\EntityStore;
use Atomino2\Database\SmartSQL\Select\Filter;

/**
 * Do not modify these annotations:
 * @method __UserFinder search(Filter $filter)
 * @method User|null belongsTo(int|null $id)
 * @method User[] belongsToMany(int[] $ids)
 * @method __UserFinder|null hasMany(string $property, int|null $id)
 * @method User pick(int $id)
 * @method User[] collect(...$ids)
 * @method User createAndLoadRecord(array $data)
 * @method User create()
 */
class UserStore extends EntityStore {
	protected const entity = User::class;
}
