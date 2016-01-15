<?php

$loader = require '../vendor/autoload.php'; // Use composer autoloading
$loader->addClassMap(array( // Add you own classes here
	'YourClass' => 'somewhere/YourClass.php',
));

date_default_timezone_set("Europe/Copenhagen");
openlog("your-maske", LOG_PID | LOG_CONS, LOG_LOCAL0); // Requires LOG_LOCAL0 configured

$dic = DiContainer::instance();
$dic->config = new Config2('/somewhere/your-config.ini');
$dic->log = Log::createFromConfig(); // Loading log config from ini file
$dic->request = new Request(); // Required by SingleSignOnClient
$dic->sso = new SingleSignOnClient('librest-test');  // Required by this library
$dic->header = new Header(); // Required by this library
