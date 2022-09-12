<?php

use Atomino2\Config\ConfigLoader;
use Atomino2\Config\Reader\DotSeparatedINIConfigReader;
use Atomino2\Config\Reader\JSONConfigReader;
use Atomino2\Config\Reader\PHPConfigReader;
use Atomino2\Config\ValueParser\EnvConfigValueParser;
use Atomino2\Config\ValueParser\PathConfigValueParser;

$loader = new ConfigLoader();
$loader->addReader("php", new PHPConfigReader());
$loader->addReader("json", new JSONConfigReader());
$loader->addReader("ini", new DotSeparatedINIConfigReader());
$loader->addValueParser(new PathConfigValueParser(getenv("ROOT")));
$loader->addValueParser(new EnvConfigValueParser());

$loader->loadList(getenv("ROOT"), getenv("CONFIG"));

return ["app-cfg" => $loader->getConfiguration()];

