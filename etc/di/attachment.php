<?php use App\DefaultConnection;
use App\Services\Attachment\Storage;
use Symfony\Component\EventDispatcher\EventDispatcher;

return [
Storage::class => \DI\factory(fn(EventDispatcher $eventDispatcher, DefaultConnection $connection, ApplicationConfig $cfg) => new Storage(
	$eventDispatcher,
	$cfg('storage.path'),
	$connection,
	$cfg('storage.storage-table'),
	$cfg('storage.link-table'),
	$cfg('storage.attachment-table')
)),
];