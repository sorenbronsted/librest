<?php
namespace ufds;

require 'vendor/autoload.php';

class AuthenticatorMock implements AuthenticatorEnable {
	public $retval = true;

	public function hasAccess() {
		return $this->retval;
	}
}