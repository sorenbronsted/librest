<?php
namespace sbronsted;

class HeaderMock {
	public $sent;

	public function out($text) {
		$this->sent = $text;
	}
}