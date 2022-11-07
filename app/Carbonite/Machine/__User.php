<?php namespace App\Carbonite\Machine;

use Atomino2\Carbonite\Entity;
use Atomino2\Carbonite\EntityFinder;
use App\Carbonite\User;
use Atomino2\Carbonite\Carbonizer\CarbonizedModel;
use \Atomino2\Database\SmartSQL\Comparison;
/**
 * @property null|array $attachments
 * @property null|\DateTime $created
 * @property null|string $email
 * @property null|string $group
 * @property null|string $guid
 * @property-read int $id
 * @property null|string $name
 * @property-write string $password
 * @property-read \App\Carbonite\Machine\__ArticleFinder $articles
 */
#[CarbonizedModel('O:35:"Atomino2\Carbonite\Carbonizer\Model":7:{s:9:" * entity";s:18:"App\Carbonite\User";s:13:" * connection";s:21:"App\DefaultConnection";s:8:" * table";s:4:"user";s:10:" * mutable";b:1;s:10:" * uniques";a:0:{}s:13:" * properties";a:8:{s:11:"attachments";O:51:"Atomino2\Carbonite\Carbonizer\Property\JsonProperty":9:{s:7:" * name";s:11:"attachments";s:10:" * persist";i:2;s:9:" * access";i:3;s:11:" * nullable";b:1;s:10:" * primary";b:0;s:10:" * virtual";b:0;s:10:" * default";N;s:12:" * validator";N;s:21:" * validatorDecorator";N;}s:7:"created";O:55:"Atomino2\Carbonite\Carbonizer\Property\DateTimeProperty":9:{s:7:" * name";s:7:"created";s:10:" * persist";i:2;s:9:" * access";i:3;s:11:" * nullable";b:1;s:10:" * primary";b:0;s:10:" * virtual";b:0;s:10:" * default";N;s:12:" * validator";N;s:21:" * validatorDecorator";N;}s:5:"email";O:53:"Atomino2\Carbonite\Carbonizer\Property\StringProperty":10:{s:7:" * name";s:5:"email";s:10:" * persist";i:2;s:9:" * access";i:3;s:11:" * nullable";b:1;s:10:" * primary";b:0;s:10:" * virtual";b:0;s:10:" * default";N;s:12:" * validator";N;s:21:" * validatorDecorator";N;s:64:" Atomino2\Carbonite\Carbonizer\Property\StringProperty maxLength";i:255;}s:5:"group";O:51:"Atomino2\Carbonite\Carbonizer\Property\EnumProperty":10:{s:7:" * name";s:5:"group";s:10:" * persist";i:2;s:9:" * access";i:3;s:11:" * nullable";b:1;s:10:" * primary";b:0;s:10:" * virtual";b:0;s:10:" * default";N;s:12:" * validator";N;s:21:" * validatorDecorator";N;s:10:" * options";a:2:{i:0;s:5:"admin";i:1;s:7:"visitor";}}s:4:"guid";O:53:"Atomino2\Carbonite\Carbonizer\Property\StringProperty":10:{s:7:" * name";s:4:"guid";s:10:" * persist";i:2;s:9:" * access";i:3;s:11:" * nullable";b:1;s:10:" * primary";b:0;s:10:" * virtual";b:0;s:10:" * default";N;s:12:" * validator";N;s:21:" * validatorDecorator";N;s:64:" Atomino2\Carbonite\Carbonizer\Property\StringProperty maxLength";i:36;}s:2:"id";O:50:"Atomino2\Carbonite\Carbonizer\Property\IntProperty":11:{s:7:" * name";s:2:"id";s:10:" * persist";i:0;s:9:" * access";i:1;s:11:" * nullable";b:0;s:10:" * primary";b:1;s:10:" * virtual";b:0;s:10:" * default";N;s:12:" * validator";N;s:21:" * validatorDecorator";N;s:9:" * signed";b:0;s:11:" * dataType";s:3:"INT";}s:4:"name";O:53:"Atomino2\Carbonite\Carbonizer\Property\StringProperty":10:{s:7:" * name";s:4:"name";s:10:" * persist";i:2;s:9:" * access";i:3;s:11:" * nullable";b:1;s:10:" * primary";b:0;s:10:" * virtual";b:0;s:10:" * default";N;s:12:" * validator";N;s:21:" * validatorDecorator";N;s:64:" Atomino2\Carbonite\Carbonizer\Property\StringProperty maxLength";i:16;}s:8:"password";O:53:"Atomino2\Carbonite\Carbonizer\Property\StringProperty":10:{s:7:" * name";s:8:"password";s:10:" * persist";i:2;s:9:" * access";i:0;s:11:" * nullable";b:1;s:10:" * primary";b:0;s:10:" * virtual";b:0;s:10:" * default";N;s:12:" * validator";N;s:21:" * validatorDecorator";N;s:64:" Atomino2\Carbonite\Carbonizer\Property\StringProperty maxLength";i:128;}}s:12:" * accessors";a:9:{s:11:"attachments";O:45:"Atomino2\Carbonite\Carbonizer\Accessor\GetSet":4:{s:44:" Atomino2\Carbonite\Carbonizer\Accessor type";s:6:"?array";s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet getMethod";b:1;s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet setMethod";b:1;s:53:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet access";i:3;}s:7:"created";O:45:"Atomino2\Carbonite\Carbonizer\Accessor\GetSet":4:{s:44:" Atomino2\Carbonite\Carbonizer\Accessor type";s:9:"?DateTime";s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet getMethod";b:1;s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet setMethod";b:1;s:53:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet access";i:3;}s:5:"email";O:45:"Atomino2\Carbonite\Carbonizer\Accessor\GetSet":4:{s:44:" Atomino2\Carbonite\Carbonizer\Accessor type";s:7:"?string";s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet getMethod";s:10:"__getEmail";s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet setMethod";s:10:"__setEmail";s:53:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet access";i:3;}s:5:"group";O:45:"Atomino2\Carbonite\Carbonizer\Accessor\GetSet":4:{s:44:" Atomino2\Carbonite\Carbonizer\Accessor type";s:7:"?string";s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet getMethod";b:1;s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet setMethod";b:1;s:53:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet access";i:3;}s:4:"guid";O:45:"Atomino2\Carbonite\Carbonizer\Accessor\GetSet":4:{s:44:" Atomino2\Carbonite\Carbonizer\Accessor type";s:7:"?string";s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet getMethod";b:1;s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet setMethod";b:1;s:53:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet access";i:3;}s:2:"id";O:45:"Atomino2\Carbonite\Carbonizer\Accessor\GetSet":4:{s:44:" Atomino2\Carbonite\Carbonizer\Accessor type";s:3:"int";s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet getMethod";b:1;s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet setMethod";b:0;s:53:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet access";i:1;}s:4:"name";O:45:"Atomino2\Carbonite\Carbonizer\Accessor\GetSet":4:{s:44:" Atomino2\Carbonite\Carbonizer\Accessor type";s:7:"?string";s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet getMethod";b:1;s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet setMethod";b:1;s:53:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet access";i:3;}s:8:"password";O:45:"Atomino2\Carbonite\Carbonizer\Accessor\GetSet":4:{s:44:" Atomino2\Carbonite\Carbonizer\Accessor type";s:6:"string";s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet getMethod";b:0;s:56:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet setMethod";s:13:"__setPassword";s:53:" Atomino2\Carbonite\Carbonizer\Accessor\GetSet access";i:2;}s:8:"articles";O:47:"Atomino2\Carbonite\Carbonizer\Accessor\Relation":6:{s:44:" Atomino2\Carbonite\Carbonizer\Accessor type";s:37:"App\Carbonite\Machine\__ArticleFinder";s:54:" Atomino2\Carbonite\Carbonizer\Accessor\Relation multi";b:0;s:53:" Atomino2\Carbonite\Carbonizer\Accessor\Relation mode";b:0;s:52:" Atomino2\Carbonite\Carbonizer\Accessor\Relation key";s:8:"authorId";s:55:" Atomino2\Carbonite\Carbonizer\Accessor\Relation target";s:21:"App\Carbonite\Article";s:54:" Atomino2\Carbonite\Carbonizer\Accessor\Relation store";s:32:"App\Carbonite\Store\ArticleStore";}}}')]
abstract class __User extends Entity {
	const __STORE__ = \App\Carbonite\Store\UserStore::class;
	const attachments = 'attachments';
	const created = 'created';
	const email = 'email';
	const group = 'group';
	const guid = 'guid';
	const id = 'id';
	const name = 'name';
	const password = 'password';
	const GROUP_admin = 'admin';
	const GROUP_visitor = 'visitor';
	public final static function attachments(...$values): Comparison { return new Comparison(self::attachments, ...$values); }
	public final static function created(...$values): Comparison { return new Comparison(self::created, ...$values); }
	public final static function email(...$values): Comparison { return new Comparison(self::email, ...$values); }
	public final static function group(...$values): Comparison { return new Comparison(self::group, ...$values); }
	public final static function guid(...$values): Comparison { return new Comparison(self::guid, ...$values); }
	public final static function id(...$values): Comparison { return new Comparison(self::id, ...$values); }
	public final static function name(...$values): Comparison { return new Comparison(self::name, ...$values); }
	public final static function password(...$values): Comparison { return new Comparison(self::password, ...$values); }
}

/**
 * @method User first()
 * @method User[] page(int $size, int &$page = 1, int|bool|null &$count = false, $handleOverflow = true)
 * @method User[] get(?int $limit = null, ?int $offset = null, int|bool|null &$count = false)
 */
class __UserFinder extends EntityFinder { }