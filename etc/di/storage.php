<?php use App\DefaultConnection;
use DI\Container;
use Atomino2\Attachment\Img\ImgCreatorGD2;
use Atomino2\Attachment\Img\ImgFactory;
use Atomino2\Attachment\Img\ImgFileResolver;
use Atomino2\Attachment\Img\ImgResolver;
use Atomino2\Attachment\Storage;
use Atomino2\Attachment\StoredFileResolver;
use Symfony\Component\EventDispatcher\EventDispatcher;
use function DI\factory;

return [
	Storage::class            => factory(fn(EventDispatcher $eventDispatcher, DefaultConnection $connection, ApplicationConfig $cfg, ImgFactory $imgFactory) => new Storage(
		$eventDispatcher,
		$connection,
		$imgFactory,
		$cfg('storage.url'),
		$cfg('storage.path'),
		$cfg('storage.storage-table'),
		$cfg('storage.link-table'),
		$cfg('storage.attachment-table'),
	)),
	ImgFactory::class         => factory(fn(ApplicationConfig $cfg) => new ImgFactory($cfg('storage.img.url'), $cfg('storage.img.secret'), $cfg('storage.img.lossy-quality'))),
	StoredFileResolver::class => factory(fn(ApplicationConfig $cfg) => new StoredFileResolver($cfg('storage.url'), $cfg('storage.path'))),
	ImgFileResolver::class    => factory(fn(Container $di, ApplicationConfig $cfg) => new ImgFileResolver($di, $cfg('storage.img.url'), $cfg('storage.img.path'), $cfg('storage.path'))),
	ImgResolver::class        => factory(fn(ImgCreatorGD2 $creator, ApplicationConfig $cfg) => new ImgResolver($creator, $cfg('storage.img.path'), $cfg('storage.img.secret'))),
];