<?php
namespace ufds;

interface JsonEnable {
	/**
	 * This gives the object an changes to change which data it returned
	 * @param array $data
	 * @return array
	 */
	public function jsonEncode(array $data);
}