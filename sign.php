<?php
include 'vendor/autoload.php';

$jwt = new \Atomino2\Auth\Token\JWT_TTL_TokenHandler("king",1);
$token = $jwt->create("laborci", ["ttl"=>19]);
var_dump($token);
sleep(1);
var_dump($jwt->resolve($token));