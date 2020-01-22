<?php
namespace sbronsted;

interface AuthenticatorEnable {
	/**
	 * This test if an rest request has access to this system
	 * @return bool
	 * 	True on success otherwise false
	 */
	public function hasAccess() : bool ;
}