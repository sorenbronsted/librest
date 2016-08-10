<?php
namespace ufds;

require 'vendor/autoload.php';

class SsoMock {

	public function challengeCookie($cookie) {
		// allways authorized
	}
}