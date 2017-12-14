<?php

	function encode_points($points){
		$bits = array();
		if (is_array($points)){
			foreach ($points as $p){
				$bits[] = sprintf('%04X', $p);
			}
		}
		if (!count($bits)) return null;
		return implode('-', $bits);
	}

	function utf8_bytes_to_uni_hex($utf8_bytes){

		$bytes = array();

		foreach (str_split($utf8_bytes) as $ch){
			$bytes[] = ord($ch);
		}

		$codepoint = 0;
		if (count($bytes) == 1) $codepoint = $bytes[0];
		if (count($bytes) == 2) $codepoint = (($bytes[0] & 0x1F) << 6) | ($bytes[1] & 0x3F);
		if (count($bytes) == 3) $codepoint = (($bytes[0] & 0x0F) << 12) | (($bytes[1] & 0x3F) << 6) | ($bytes[2] & 0x3F);
		if (count($bytes) == 4) $codepoint = (($bytes[0] & 0x07) << 18) | (($bytes[1] & 0x3F) << 12) | (($bytes[2] & 0x3F) << 6) | ($bytes[3] & 0x3F);
		if (count($bytes) == 5) $codepoint = (($bytes[0] & 0x03) << 24) | (($bytes[1] & 0x3F) << 18) | (($bytes[2] & 0x3F) << 12) | (($bytes[3] & 0x3F) << 6) | ($bytes[4] & 0x3F);
		if (count($bytes) == 6) $codepoint = (($bytes[0] & 0x01) << 30) | (($bytes[1] & 0x3F) << 24) | (($bytes[2] & 0x3F) << 18) | (($bytes[3] & 0x3F) << 12) | (($bytes[4] & 0x3F) << 6) | ($bytes[5] & 0x3F);

		$str = sprintf('%x', $codepoint);
		return str_pad($str, 4, '0', STR_PAD_LEFT);
	}

	function utf8_bytes_to_hex($str){
		mb_internal_encoding('UTF-8');
		$out = array();
		while (strlen($str)){
			$out[] = utf8_bytes_to_uni_hex(mb_substr($str, 0, 1));
			$str = mb_substr($str, 1);
		}
		return implode('-', $out);
	}

	function parse_unicode_specfile($filename, $callback){

		$lines = file($filename);
		foreach ($lines as $line){
			$p = strpos($line , '#');
			$comment = '';
			if ($p !== false){
				$comment = trim(substr($line, $p+1));
				$line = substr($line, 0, $p);
			}
			$line = trim($line);
			if (!strlen($line)) continue;

			$bits = explode(';', $line);
			$fields = array();
			foreach ($bits as $bit){
				$fields[] = trim($bit);
			}

			call_user_func($callback, $fields, $comment);
		}
	}

	function cp_to_utf8_bytes($v){

		if ($v < 128){
			return chr($v);
		}

		if ($v < 2048){
			return chr(($v >> 6) + 192) . chr(($v & 63) + 128);
		}

		if ($v < 65536){
			return chr(($v >> 12) + 224) . chr((($v >> 6) & 63) + 128) . chr(($v & 63) + 128);
		}

		if ($v < 2097152){
			return chr(($v >> 18) + 240) . chr((($v >> 12) & 63) + 128) . chr((($v >> 6) & 63) + 128) . chr(($v & 63) + 128);
		}

		die("can't create codepoints for $v");
	}

