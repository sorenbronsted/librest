<?php

class HeaderMock {
	public function out($text) {
		DiContainer::instance()->log->debug(__CLASS__, $text);
	}
}