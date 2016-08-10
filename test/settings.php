<?php
namespace ufds;

$loader = require 'vendor/autoload.php';
$loader->addPsr4('ufds\\', __DIR__.'/utils');

date_default_timezone_set("Europe/Copenhagen");
openlog("ufds-librest", LOG_PID | LOG_CONS, LOG_LOCAL0);

$dic = DiContainer::instance();
$dic->config = new Config2('test/librest.ini');
$dic->log = Log::createFromConfig();
$dic->sso = new SsoMock();
$dic->header = new HeaderMock();
