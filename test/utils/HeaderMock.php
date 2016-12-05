<?php
namespace ufds;

class HeaderMock {
	public $sent;

	public function out($text) {
		$this->sent = $text;
	}
}