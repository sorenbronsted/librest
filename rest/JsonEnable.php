<?php
namespace sbronsted;

interface JsonEnable {
	/**
	 * This gives the object an changes to change which data it returned
	 * @param array
	 * 	An array $data
	 * @return array
	 * 	An array
	 */
	public function jsonEncode(array $data) : array;
}