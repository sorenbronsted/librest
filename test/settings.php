<?php
namespace sbronsted;

$loader = require 'vendor/autoload.php';
$loader->addPsr4('sbronsted\\', __DIR__.'/utils');

date_default_timezone_set("Europe/Copenhagen");
openlog("librest", LOG_PID | LOG_CONS, LOG_LOCAL0);

$dic = DiContainer::instance();
$dic->config = new Config2('test/librest.ini');
$dic->log = Log::createFromConfig();
$dic->header = new HeaderMock();
$dic->restAuthenticator = new AuthenticatorMock();
