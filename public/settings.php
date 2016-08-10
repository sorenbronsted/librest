<?php
namespace ufds;

require '../vendor/autoload.php'; // Use composer autoloading

date_default_timezone_set("Europe/Copenhagen");
openlog("your-maske", LOG_PID | LOG_CONS, LOG_LOCAL0); // Requires LOG_LOCAL0 configured

$dic = DiContainer::instance();
$dic->config = new Config2('/somewhere/your-config.ini');
$dic->log = Log::createFromConfig(); // Loading log config from ini file
$dic->header = new Header(); // Required by this library

/* If you need authentication you need to setup request and sso */
$dic->request = new Request(); // Required by SingleSignOnClient
$dic->sso = new SingleSignOnClient('librest-test');  // Required by this library
