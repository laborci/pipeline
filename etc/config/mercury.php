<?php return [
	"mercury" => [

		"smart-responder" => [
			"namespaces"            => [
				"public@path" => "/app/Mission/Public/@templates",
			],
			"cache-path@path"       => "/var/tmp/twig/",
			"frontend-version-file" => "/var/frontend-version",
			"debug"                 => true,
		],
		"middlewares"     => [
			"cache" => ["path@path" => "var/tmp/cache"],
		],
	],
];
