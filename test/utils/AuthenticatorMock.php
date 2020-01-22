<?php
namespace sbronsted;

require 'vendor/autoload.php';

class AuthenticatorMock implements AuthenticatorEnable {
	public $retval = true;

	public function hasAccess() : bool {
		return $this->retval;
	}
}