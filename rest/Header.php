<?php
namespace ufds;

class Header {
	public function out($text) {
		header($text);
	}
}