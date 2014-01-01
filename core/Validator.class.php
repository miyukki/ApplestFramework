<?php

class Validator {
	public static function required($subject) {
		if(is_null($subject)) return false;
		// string
		return true;
	}

	public static function min_length($subject, $length) {
		if(strlen($subject) < $length) {
			return false;
		}
		return true;
	}

	public static function max_length($subject, $length) {
		if(strlen($subject) > $length) {
			return false;
		}
		return true;
	}

	public static function exact_length($subject, $length) {
		if(strlen($subject) !== $length) {
			return false;
		}
		return true;
	}

	public static function mb_min_length($subject, $length, $encoding = null) {
		if(is_null($encoding)) {
			if(mb_strlen($subject) < $length) {
				return false;
			}
		}else{
			if(mb_strlen($subject, $encoding) < $length) {
				return false;
			}
		}
		return true;
	}

	public static function mb_max_length($subject, $length, $encoding = null) {
		if(is_null($encoding)) {
			if(mb_strlen($subject) > $length) {
				return false;
			}
		}else{
			if(mb_strlen($subject, $encoding) > $length) {
				return false;
			}
		}
		return true;
	}

	public static function mb_exact_length($subject, $length, $encoding = null) {
		if(is_null($encoding)) {
			if(mb_strlen($subject) !== $length) {
				return false;
			}
		}else{
			if(mb_strlen($subject, $encoding) !== $length) {
				return false;
			}
		}
		return true;
	}

	public static function match_value($subject, $val) {
		if($subject != $val) {
			return false;
		}
		return true;
	}

	public static function match_pattern($subject, $pattern) {
		if(preg_match($pattern, $subject) !== 1) {
			return false;
		}
		return true;
	}

	// 
	public static function valid_date($subject, $format) {
		throw new Exception("未実装", 1);
		
		if(is_null($subject)) return false;
		return true;
	}

	public static function valid_email($subject) {
		return self::match_pattern($subject, '/^[^@]+@[0-9,A-Z,a-z][0-9,a-z,A-Z,_,\.,-]+\.(af|al|dz|as|ad|ao|ai|aq|ag|ar|am|aw|ac|au|at|az|bh|bd|bb|by|bj|bm|bt|bo|ba|bw|br|io|bn|bg|bf|bi|kh|cm|ca|cv|cf|td|gg|je|cl|cn|cx|cc|co|km|cg|cd|ck|cr|ci|hr|cu|cy|cz|dk|dj|dm|do|tp|ec|eg|sv|gq|er|ee|et|fk|fo|fj|fi|fr|gf|pf|tf|fx|ga|gm|ge|de|gh|gi|gd|gp|gu|gt|gn|gw|gy|ht|hm|hn|hk|hu|is|in|id|ir|iq|ie|im|il|it|jm|jo|kz|ke|ki|kp|kr|kw|kg|la|lv|lb|ls|lr|ly|li|lt|lu|mo|mk|mg|mw|my|mv|ml|mt|mh|mq|mr|mu|yt|mx|fm|md|mc|mn|ms|ma|mz|mm|na|nr|np|nl|an|nc|nz|ni|ne|ng|nu|nf|mp|no|om|pk|pw|pa|pg|py|pe|ph|pn|pl|pt|pr|qa|re|ro|ru|rw|kn|lc|vc|ws|sm|st|sa|sn|sc|sl|sg|sk|si|sb|so|za|gs|es|lk|sh|pm|sd|sr|sj|sz|se|ch|sy|tw|tj|tz|th|bs|ky|tg|tk|to|tt|tn|tr|tm|tc|tv|ug|ua|ae|uk|us|um|uy|uz|vu|va|ve|vn|vg|vi|wf|eh|ye|yu|zm|zw|com|net|org|gov|edu|int|mil|biz|info|name|pro|jp)$/i');
	}

	public static function valid_strict_email($subject) {
		//RF2822
		return self::match_pattern($subject, '/^(?:(?:(?:(?:[a-zA-Z0-9_!#\$\%&\'*+\/=?\^`{}~|\-]+)(?:\.(?:[a-zA-Z0-9_!#\$\%&\'*+\/=?\^`{}~|\-]+))*)|(?:"(?:\\[^\r\n]|[^\\"])*")))\@(?:(?:(?:(?:[a-zA-Z0-9_!#\$\%&\'*+\/=?\^`{}~|\-]+)(?:\.(?:[a-zA-Z0-9_!#\$\%&\'*+\/=?\^`{}~|\-]+))*)|(?:\[(?:\\\S|[\x21-\x5a\x5e-\x7e])*\])))$/');
	}

	public static function valid_url($subject) {
		return self::match_pattern($subject, '/^https?(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)/');
	}

	public static function valid_uri($subject) {
		return self::match_pattern($subject, '/([a-zA-Z][\-+.a-zA-Z\d]*):(?:((?:[\-_.!~*\'()a-zA-Z\d;?:@&=+$,]|%[a-fA-F\d]{2})(?:[\-_.!~*\'()a-zA-Z\d;\/?:@&=+$,\[\]]|%[a-fA-F\d]{2})*)|(?:(?:\/\/(?:
(?:(?:((?:[\-_.!~*\'()a-zA-Z\d;:&=+$,]|%[a-fA-F\d]{2})*)@)?(?:((?:(?:[a-zA-Z0-9\-.]|%\h\h)+|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|\[(?:(?:[a-fA-F\d]{1,4}:)*(?:[a-fA-F\d]{1,4}|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})|(?:(?:[a-fA-F\d]{1,4}:)*[a-fA-F\d]{1,4})?::(?:(?:[a-fA-F\d]{1,4}:)*(?:[a-fA-F\d]{1,4}|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}))?)\]))(?::(\d*))?))?|((?:[\-_.!~*\'()a-zA-Z\d$,;:@&=+]|%[a-fA-F\d]{2})+))|(?!\/\/))(\/(?:[\-_.!~*\'()a-zA-Z\d:@&=+$,]|%[a-fA-F\d]{2})*(?:;(?:[\-_.!~*\'()a-zA-Z\d:@&=+$,]|%[a-fA-F\d]{2})*)*(?:\/(?:[\-_.!~*\'()a-zA-Z\d:@&=+$,]|%[a-fA-F\d]{2})*(?:;(?:[\-_.!~*\'()a-zA-Z\d:@&=+$,]|%[a-fA-F\d]{2})*)*)*)?)(?:\?((?:[\-_.!~*\'()a-zA-Z\d;\/?:@&=+$,\[\]]|%[a-fA-F\d]{2})*))?)(?:\#((?:[\-_.!~*\'()a-zA-Z\d;\/?:@&=+$,\[\]]|%[a-fA-F\d]{2})*))?/x');
		
		if(is_null($subject)) return false;
		return true;
	}

	public static function valid_ipv4($subject) {
		return self::match_pattern($subject, '/((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])([.](?!$)|$)){4}/');
	}

	public static function valid_ipv6($subject) {
		return self::match_pattern($subject, '/((([0-9a-f]{1,4}:){7}([0-9a-f]{1,4}|:))|(([0-9a-f]{1,4}:){6}(:[0-9a-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9a-f]{1,4}:){5}(((:[0-9a-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9a-f]{1,4}:){4}(((:[0-9a-f]{1,4}){1,3})|((:[0-9a-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){3}(((:[0-9a-f]{1,4}){1,4})|((:[0-9a-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){2}(((:[0-9a-f]{1,4}){1,5})|((:[0-9a-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){1}(((:[0-9a-f]{1,4}){1,6})|((:[0-9a-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9a-f]{1,4}){1,7})|((:[0-9a-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/');
	}

	public static function numeric($subject) {
		if(!is_numeric($subject)) {
			return false;
		}
		return true;
	}

	public static function min_number($subject, $val) {
		if($subject < $val) {
			return false;
		}
		return true;
	}

	public static function max_number($subject, $val) {
		if($subject > $val) {
			return false;
		}
		return true;
	}
}