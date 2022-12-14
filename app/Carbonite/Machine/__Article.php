<?php namespace App\Carbonite\Machine;

use Atomino2\Carbonite\Entity;
use Atomino2\Carbonite\EntityFinder;
use App\Carbonite\Article;
use Atomino2\Carbonite\Carbonizer\CarbonizedModel;
use \Atomino2\Database\SmartSQL\Comparison;
/**
 * @property int $authorId
 * @property-read int $id
 * @property null|string $text
 * @property string $title
 * @property-read \App\Carbonite\User $author
 */
#[CarbonizedModel('O:35:"Atomino2\Carbonite\Carbonizer\Model":8:{s:9:" * entity";s:21:"App\Carbonite\Article";s:13:" * connection";s:21:"App\DefaultConnection";s:8:" * table";s:7:"article";s:10:" * mutable";b:1;s:10:" * uniques";a:2:{s:2:"id";a:2:{i:0;s:2:"id";i:1;s:5:"title";}s:12:"unique_email";a:1:{i:0;s:8:"authorId";}}s:13:" * properties";a:4:{s:8:"authorId";O:50:"Atomino2\Carbonite\Carbonizer\Property\IntProperty":11:{s:7:" * name";s:8:"authorId";s:10:" * persist";i:2;s:9:" * access";i:3;s:11:" * nullable";b:0;s:10:" * primary";b:0;s:10:" * virtual";b:0;s:10:" * default";N;s:12:" * validator";N;s:21:" * validatorDecorator";N;s:9:" * signed";b:0;s:11:" * dataType";s:3:"INT";}s:2:"id";O:50:"Atomino2\Carbonite\Carbonizer\Property\IntProperty":11:{s:7:" * name";s:2:"id";s:10:" * persist";i:0;s:9:" * access";i:1;s:11:" * nullable";b:0;s:10:" * primary";b:1;s:10:" * virtual";b:0;s:10:" * default";N;s:12:" * validator";N;s:21:" * validatorDecorator";N;s:9:" * signed";b:0;s:11:" * dataType";s:3:"INT";}s:4:"text";O:53:"Atomino2\Carbonite\Carbonizer\Property\StringProperty":10:{s:7:" * name";s:4:"text";s:10:" * persist";i:2;s:9:" * access";i:3;s:11:" * nullable";b:1;s:10:" * primary";b:0;s:10:" * virtual";b:0;s:10:" * default";N;s:12:" * validator";N;s:21:" * validatorDecorator";N;s:64:" Atomino2\Carbonite\Carbonizer\Property\StringProperty maxLength";i:4294967295;}s:5:"title";O:53:"Atomino2\Carbonite\Carbonizer\Property\StringProperty":10:{s:7:" * name";s:5:"title";s:10:" * persist";i:2;s:9:" * access";i:3;s:11:" * nullable";b:0;s:10:" * primary";b:0;s:10:" * virtual";b:0;s:10:" * default";N;s:12:" * validator";N;s:21:" * validatorDecorator";N;s:64:" Atomino2\Carbonite\Carbonizer\Property\StringProperty maxLength";i:512;}}s:12:" * accessors";a:5:{s:8:"authorId";O:45:"Atomino2\Carbonite\Carbonizer\Accessor\GetSet":4:{s:44:" Atomino2\Carbonite\Carbonizer\Accessor type";s:3:"int";s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet getMethod";b:1;s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet setMethod";b:1;s:53:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet access";i:3;}s:2:"id";O:45:"Atomino2\Carbonite\Carbonizer\Accessor\GetSet":4:{s:44:" Atomino2\Carbonite\Carbonizer\Accessor type";s:3:"int";s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet getMethod";b:1;s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet setMethod";b:0;s:53:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet access";i:1;}s:4:"text";O:45:"Atomino2\Carbonite\Carbonizer\Accessor\GetSet":4:{s:44:" Atomino2\Carbonite\Carbonizer\Accessor type";s:7:"?string";s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet getMethod";b:1;s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet setMethod";b:1;s:53:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet access";i:3;}s:5:"title";O:45:"Atomino2\Carbonite\Carbonizer\Accessor\GetSet":4:{s:44:" Atomino2\Carbonite\Carbonizer\Accessor type";s:6:"string";s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet getMethod";b:1;s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet setMethod";b:1;s:53:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet access";i:3;}s:6:"author";O:47:"Atomino2\Carbonite\Carbonizer\Accessor\Relation":5:{s:44:" Atomino2\Carbonite\Carbonizer\Accessor type";s:18:"App\Carbonite\User";s:54:" Atomino2\Carbonite\Carbonizer\Accessor\Relation multi";b:0;s:53:" Atomino2\Carbonite\Carbonizer\Accessor\Relation mode";b:1;s:52:" Atomino2\Carbonite\Carbonizer\Accessor\Relation key";s:8:"authorId";s:54:" Atomino2\Carbonite\Carbonizer\Accessor\Relation store";s:29:"App\Carbonite\Store\UserStore";}}s:48:" Atomino2\Carbonite\Carbonizer\Model initializer";N;}')]
abstract class __Article extends Entity {
	const authorId = 'authorId';
	const id = 'id';
	const text = 'text';
	const title = 'title';

	public final static function authorId(...$values): Comparison { return new Comparison(self::authorId, ...$values); }
	public final static function id(...$values): Comparison { return new Comparison(self::id, ...$values); }
	public final static function text(...$values): Comparison { return new Comparison(self::text, ...$values); }
	public final static function title(...$values): Comparison { return new Comparison(self::title, ...$values); }
}

/**
 * @method Article first()
 * @method Article[] page(int $size, int &$page = 1, int|bool|null &$count = false, $handleOverflow = true)
 * @method Article[] get(?int $limit = null, ?int $offset = null, int|bool|null &$count = false)
 */
class __ArticleFinder extends EntityFinder { }