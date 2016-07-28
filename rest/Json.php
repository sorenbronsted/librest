<?php

class Json {
	public static function encode($item) {
		$result = "";
		if (is_array($item)) {
			$result .= "[";
			foreach($item as $tmp) {
				if (strlen($result) > 1) {
					$result .= ",";
				}
				$result .= self::encode($tmp);
			}
			$result .= "]";
		}
		else if (is_null($item)) {
			return 'null';
		}
		else if ($item instanceof DbObject) {
			$data = $item->getData();
			if ($item instanceof JsonEnable) {
				$data = $item->onJsonEncode($data);
			}
			$tmp = array();
			foreach($data as $key => $value) {
				$tmp[$key] = (is_object($value) ? strval($value) : $value);
			}
			return '{"'.get_class($item).'":'.json_encode($tmp).'}';
		}
		else if ($item instanceof stdclass) {
			return json_encode($item);
		}
		else if ($item instanceof JsonEnable) {
			return $item->jsonEncode();
		}
		else {
			return json_encode($item);
		}
		return $result;
	}
}