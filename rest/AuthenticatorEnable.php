<?php

namespace ufds;

interface AuthenticatorEnable {
	/**
	 * This test if an rest request has access to this system
	 * @return boolean true on success and false on failure
	 */
	public function hasAccess();
}