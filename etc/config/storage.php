<?php return [
	'storage' => [
		'url'              => '/~fs',
		'path@path'        => 'var/data/storage',
		'storage-table'    => '__fs_storage',
		'link-table'       => '__fs_link',
		'attachment-table' => '__fs_attachment',
		'img'              => [
			'url'           => '/~img',
			'path@path'     => 'var/data/img',
			'secret'        => 'secret',
			'lossy-quality' => 80,
		],
	],
];