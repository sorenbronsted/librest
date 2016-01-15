<?php

$loader = require 'vendor/autoload.php';
$loader->addClassMap(array(
	'SsoMock' => 'test/utils/SsoMock.php',
	'HeaderMock' => 'test/utils/HeaderMock.php',
	'Sample' => 'test/utils/Sample.php',
));

date_default_timezone_set("Europe/Copenhagen");
openlog("ufds-librest", LOG_PID | LOG_CONS, LOG_LOCAL0);

$dic = DiContainer::instance();
$dic->config = new Config2('test/librest.ini');
$dic->log = Log::createFromConfig();
$dic->sso = new SsoMock();
$dic->header = new HeaderMock();
