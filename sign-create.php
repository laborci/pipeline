<?php

$new_key_pair = openssl_pkey_new([
	"private_key_bits" => 2048,
	"private_key_type" => OPENSSL_KEYTYPE_RSA,
]);

openssl_pkey_export($new_key_pair, $private_key_pem);
$details = openssl_pkey_get_details($new_key_pair);
$public_key_pem = $details['key'];

file_put_contents('private_key.pem', $private_key_pem);
file_put_contents('public_key.pem', $public_key_pem);