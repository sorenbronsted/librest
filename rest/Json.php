<?php

class Json {
	public static function encode($item) {
		if ($item instanceof DbObject) {
			return '{"'.get_class($item).'":'.json_encode($item->getData()).'}';
		}
		else if ($item instanceof stdclass) {
			return json_encode($item);
		}
		else if ($item instanceof JsonEnable) {
			return $item->jsonEncode();
		}
		return json_encode($item);
	}
}