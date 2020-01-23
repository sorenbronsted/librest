<?php


namespace sbronsted;


class OnlyJsonEnabled implements JsonEnable {

	/**
	 * @inheritDoc
	 */
	public function jsonEncode(array $data): array {
		$data['key'] = 'value';
		return $data;
	}
}