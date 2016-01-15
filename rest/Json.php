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
		else if ($item instanceof JsonEnable ) {
				return $item->jsonEncode();
		}
		else if ($item instanceof stdclass) {
			return json_encode($item);
		}
		else {
			return json_encode($item);
		}
		return $result;
	}
}