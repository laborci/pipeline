<?php

class_alias(\Atomino2\Database\Connection::class, App\DefaultConnection::class);

return [
	\App\DefaultConnection::class=>\DI\factory(fn(ApplicationConfig $cfg)=>new \Atomino2\Database\Connection($cfg("database.default-dsn")))
];