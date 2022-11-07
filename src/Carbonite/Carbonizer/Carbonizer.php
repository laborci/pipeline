<?php namespace Atomino2\Carbonite\Carbonizer;

use App\Carbonite\Machine\__UserFinder;
use Atomino2\Carbonite\Carbonizer\Accessor\Relation;
use Atomino2\Carbonite\Carbonizer\Property\EnumProperty;
use Atomino2\Carbonite\Carbonizer\Property\IntProperty;
use Atomino2\Carbonite\Carbonizer\Property\SetProperty;
use Atomino2\Cli\Style;
use Atomino2\Util\CodeFinder;
use Atomino2\Util\PathResolver;
use DI\Container;


class Carbonizer {

	private const ENTITY_TEMPLATE       = <<<'EOF'
<?php namespace {{ENTITY_NAMESPACE}};

use Atomino2\Carbonite\Carbonizer\Carbonite;
use {{MACHINE_NAMESPACE}}\__{{ENTITY_NAME}};

class {{ENTITY_NAME}} extends __{{ENTITY_NAME}} {
	protected static function carbonize(): Carbonite {
		return (new Carbonite(\App\DefaultConnection::class, '**TABLE**', true));
	}
}
EOF;
	private const ENTITY_STORE_TEMPLATE = <<<'EOF'
<?php namespace {{STORE_NAMESPACE}};

use {{MACHINE_NAMESPACE}}\__{{ENTITY_NAME}}Finder;
use App\Carbonite\{{ENTITY_NAME}};
use Atomino2\Carbonite\EntityStore;
use Atomino2\Database\SmartSQL\Select\Filter;

/**
 * Do not modify these annotations:
 * @method __{{ENTITY_NAME}}Finder search(Filter $filter)
 * @method {{ENTITY_NAME}}|null belongsTo(int|null $id)
 * @method {{ENTITY_NAME}}[] belongsToMany(int[] $ids)
 * @method __{{ENTITY_NAME}}Finder|null hasMany(string $property, int|null $id)
 * @method {{ENTITY_NAME}} pick(int $id)
 * @method {{ENTITY_NAME}}[] collect(...$ids)
 * @method {{ENTITY_NAME}} build(array $data)
 * @method {{ENTITY_NAME}} create()
 */
class {{ENTITY_NAME}}Store extends EntityStore {
	protected const entity = {{ENTITY_NAME}}::class;
}
EOF;
	private const ENTITY_BASE_TEMPLATE  = <<<'EOF'
<?php namespace {{MACHINE_NAMESPACE}};

use Atomino2\Carbonite\Entity;
use Atomino2\Carbonite\EntityFinder;
use {{ENTITY_NAMESPACE}}\{{ENTITY_NAME}};
use Atomino2\Carbonite\Carbonizer\CarbonizedModel;
use \Atomino2\Database\SmartSQL\Comparison;
/**
{{ANNOTATIONS}}
 */
#[CarbonizedModel('{{SERIALIZED_MODEL}}')]
abstract class __{{ENTITY_NAME}} extends Entity {
	const __STORE__ = \{{STORE_NAMESPACE}}\{{ENTITY_NAME}}Store::class;
{{CODE}}
}

/**
 * @method {{ENTITY_NAME}} first()
 * @method {{ENTITY_NAME}}[] page(int $size, int &$page = 1, int|bool|null &$count = false, $handleOverflow = true)
 * @method {{ENTITY_NAME}}[] get(?int $limit = null, ?int $offset = null, int|bool|null &$count = false)
 */
class __{{ENTITY_NAME}}Finder extends EntityFinder { }
EOF;


	private string       $entities;
	private string       $stores;
	private string       $machines;
	private CodeFinder   $codeFinder;
	private PathResolver $pathResolver;

	public function __construct(private Container $di) {
		$this->codeFinder = $di->get(CodeFinder::class);
		$this->pathResolver = $di->get(PathResolver::class);
	}

	public function update(Style|null $style = null) {
		$entities = $this->codeFinder->Psr4ClassSeeker($this->entities, '*.php', false);
		/** @var Carbonite[] $carbonites */
		$carbonites = [];
		$models = [];

		// COLLECT CARBONITES
		foreach ($entities as $entity) $carbonites[$entity] = \Closure::bind(fn($e) => $e::carbonize(), null, $entity)($entity);


		// CREATE MODELS
		foreach ($entities as $entity) $models[$entity] = new Model($entity, $carbonites[$entity], $this->di);

		// INJECT RELATION ACCESSORS
		foreach ($entities as $entity) {
			$relations = $carbonites[$entity]->getRelations();
			foreach ($relations as $relation) {
				$key = $relation['key'];
				$multi = !$models[$entity]->getProperty($key) instanceof IntProperty;
				$target = $relation['target'];
				$property = $relation['property'];
				$targetProperty = $relation['targetProperty'];

				$entityShortName = (new \ReflectionClass($entity))->getShortName();
				$targetShortName = (new \ReflectionClass($target))->getShortName();


				$accessor = new Relation(
					$key,
					$multi,
					Relation::RELATION,
					$target,
					$this->stores . '\\' . $targetShortName . 'Store',
					$multi ? $target . "[]" : $target
				);
				\Closure::bind(fn($property, $accessor) => $this->accessors[$property] = $accessor, $models[$entity], Model::class)($property, $accessor);
				if ($targetProperty !== null) {
					$entityShortName = $translate["{{ENTITY_NAME}}"] = (new \ReflectionClass($entity))->getShortName();
					$accessor = new Relation(
						$key,
						$multi,
						Relation::REVERSE,
						$entity,
						$this->stores . '\\' . $entityShortName . 'Store',
						$this->machines . '\\__' . $entityShortName . 'Finder'
					);
					\Closure::bind(fn($property, $accessor) => $this->accessors[$targetProperty] = $accessor, $models[$target], Model::class)($property, $accessor);
				}
			}
		}

		debug($models);

		// DUMP
		foreach ($models as $model) {
			$translate = $this->getTranslate();
			$entityShortName = $translate["{{ENTITY_NAME}}"] = (new \ReflectionClass($model->getEntity()))->getShortName();
			$translate["{{SERIALIZED_MODEL}}"] = serialize($model);

			$annotations = [];
			foreach ($model->getAccessors() as $name) {
				$accessor = $model->getAccessor($name);
				$type = $accessor->getType();
				if (substr($type, 0, 1) === '?') $type = 'null|' . substr($type, 1);
				$type = join('|', array_map(fn($type) => $type === ucfirst($type) ? '\\' . $type : $type, explode('|', $type)));

				$propertyAnnotation = match ($accessor->getAccess()) {
					Access::READ       => "@property-read",
					Access::WRITE      => "@property-write",
					Access::READ_WRITE => "@property",
				};
				$annotations[] = sprintf(" * %s %s $%s", $propertyAnnotation, $type, $name);
			}
			$translate['{{ANNOTATIONS}}'] = join("\n", $annotations);


			$code_constants = [];
			$code_comparators = [];
			$code_enums = [];
			foreach ($model->getProperties() as $name) {
				$code_constants[] = sprintf("\tconst %s = '%s';", $name, $name);
				$code_comparators[] = sprintf("\tpublic final static function %s(...\$values): Comparison { return new Comparison(self::%s, ...\$values); }", $name, $name);
				$property = $model->getProperty($name);
				if ($property instanceof SetProperty || $property instanceof EnumProperty) {
					$options = $property->getOptions();
					foreach ($options as $option) {
						$code_enums[] = sprintf("\tconst %s_%s = '%s';", strtoupper($name), $option, $option);
					}
				}
			}
			$translate['{{CODE}}'] = join("\n", $code_constants) . "\n"
				. join("\n", $code_enums) . "\n"
				. join("\n", $code_comparators);
			$this->createEntityBaseFile($entityShortName, $translate, $style);
		}
	}

	/**
	 * @property string $name
	 * @property string $email
	 * @property-read \App\Bundle\Password\PasswordHandler $password
	 * @property-read \App\Carbonite\User $boss
	 * @property int $bossId
	 * @property-read __UserFinder|\Atomino2\Carbonite\EntityFinder $workers
	 * @property-read \App\Bundle\Attachment\AttachmentHandler $attachments
	 */

	public function getTranslate() {
		return [
			"{{ENTITY_NAMESPACE}}"  => $this->entities,
			"{{ENTITY_NAME}}"       => "",
			"{{MACHINE_NAMESPACE}}" => $this->machines,
			"{{STORE_NAMESPACE}}"   => $this->stores,
			"{{CODE}}"              => "",
			"{{ANNOTATIONS}}"       => "",
			"{{SERIALIZED_MODEL}}"  => "",
		];
	}

	public function create(string $entity, Style|null $style = null) {

		$translate = $this->getTranslate();
		$translate["{{ENTITY_NAME}}"] = $entity;

		$this->createEntityFile($entity, $translate, $style);
		$this->createStoreFile($entity, $translate, $style);
		$this->createEntityBaseFile($entity, $translate, $style);
	}

	private function createEntityBaseFile($entity, $translate, Style|null $style = null) {
		$file = $this->codeFinder->Psr4ResolveClass($this->machines . '\\__' . $entity);
		$style?->_task(sprintf("Searching file %s", $this->pathResolver->short($file)));
		file_put_contents($file, strtr(static::ENTITY_BASE_TEMPLATE, $translate));
		$style?->_task_ok('File created');
	}
	private function createStoreFile($entity, $translate, Style|null $style = null) {
		$file = $this->codeFinder->Psr4ResolveClass($this->stores . '\\' . $entity . 'Store');
		$style?->_task(sprintf("Searching file %s", $this->pathResolver->short($file)));
		if (file_exists($file)) $style?->_task_error('Already exists');
		else {
			file_put_contents($file, strtr(static::ENTITY_STORE_TEMPLATE, $translate));
			$style?->_task_ok('File created');
		}
	}
	private function createEntityFile($entity, $translate, Style|null $style = null) {
		$file = $this->codeFinder->Psr4ResolveClass($this->entities . '\\' . $entity);
		$style?->_task(sprintf("Searching file %s", $this->pathResolver->short($file)));
		if (file_exists($file)) $style?->_task_error('Already exists');
		else {
			file_put_contents($file, strtr(static::ENTITY_TEMPLATE, $translate));
			$style?->_task_ok('File created');
		}
	}

	public function init(string $entities, string $stores, string $machine) {
		$this->entities = $entities;
		$this->stores = $stores;
		$this->machines = $machine;
	}

}
