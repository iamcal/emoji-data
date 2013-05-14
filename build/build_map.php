<?php
	include('catalog.php');

	$items = $catalog;


	#
	# build the final maps
	#

	$maps = array();

	$maps['names']		= make_names_map($items);
	$maps['kaomoji']	= get_all_kaomoji($items);

	#fprintf(STDERR, "fix Geta Mark ()  '〓' (U+3013)\n");
	#$items = fix_geta_mark($items);

	$maps["unified_to_docomo"]	= make_mapping($items, 'docomo');
	$maps["unified_to_kddi"]	= make_mapping($items, 'au');
	$maps["unified_to_softbank"]	= make_mapping($items, 'softbank');
	$maps["unified_to_google"]	= make_mapping($items, 'google');

	$maps["docomo_to_unified"]	= make_mapping_flip($items, 'docomo');
	$maps["kddi_to_unified"]	= make_mapping_flip($items, 'au');
	$maps["softbank_to_unified"]	= make_mapping_flip($items, 'softbank');
	$maps["google_to_unified"]	= make_mapping_flip($items, 'google');

	$maps["unified_to_html"]	= make_html_map($items);


	#
	# output
	# we could just use var_dump, but we get 'better' output this way
	#

	echo "<"."?php\n";

	echo "\n";
	echo "\t#\n";
	echo "\t# WARNING:\n";
	echo "\t# This code is auto-generated. Do not modify it manually.\n";
	echo "\t#\n";
	echo "\n";

	echo "\t\$GLOBALS['emoji_maps'] = array(\n";

	echo "\t\t'names' => array(\n";

	foreach ($maps[names] as $k => $v){

		$key_enc = format_string($k);
		$name_enc = "'".AddSlashes($v)."'";
		echo "\t\t\t$key_enc => $name_enc,\n";
	}

	echo "\t\t),\n";

	foreach ($maps as $k => $v){

		if ($k == 'names') continue;

		echo "\t\t'$k' => array(\n";

		$count = 0;
		echo "\t\t\t";
		foreach ($v as $k2 => $v2){
			$count++;
			if ($count % 5 == 0) echo "\n\t\t\t";
			echo format_string($k2).'=>'.format_string($v2).', ';
		}
		echo "\n";

		echo "\t\t),\n";
	}

	echo "\t);\n";


	echo file_get_contents('core_functions.php');



	##########################################################################################

	function get_all_kaomoji($mapping){
		$arr = array();

		foreach ($mapping as $map){
			if (isset($map['docomo']['kaomoji']) ) {
				$arr[ $map['docomo']['kaomoji'] ] = '1';
			}

			if (isset($map['au']['kaomoji']) ) {
				$arr[ $map['au']['kaomoji'] ] = '1';
			}

			if (isset($map['softbank']['kaomoji']) ) {
				$arr[ $map['softbank']['kaomoji'] ] = '1';
			}
		}

		return array_keys($arr);
	}

	function make_names_map($map){

		$out = array();
		foreach ($map as $row){

			$bytes = unicode_bytes($row['unicode']);

			$out[$bytes] = $row['char_name']['title'];
		}

		return $out;
	}

	function make_html_map($map){

		$out = array();
		foreach ($map as $row){

			$hex = '';
			foreach ($row['unicode'] as $cp) $hex .= sprintf('%x', $cp);

			$bytes = unicode_bytes($row['unicode']);

			$out[$bytes] = "<span class=\"emoji emoji$hex\"></span>";
		}

		return $out;
	}

	function make_mapping($mapping, $dest){

		$result = array();

		foreach ($mapping as $map){

			$src_char = unicode_bytes($map['unicode']);

			if (is_array($map[$dest]['unicode']) && count($map[$dest]['unicode'])){

				$dest_char = unicode_bytes($map[$dest]['unicode']);
			}else{
				$dest_char = $map[$dest]['kaomoji'];
			}

			$result[$src_char] = $dest_char;
		}

		return $result;
	}

	function make_mapping_flip($mapping, $src){
		$result = make_mapping($mapping, $src);
		$result = array_flip($result);
		unset($result[""]);
		return $result;
	}

	function unicode_bytes($cps){

		$out = '';

		foreach ($cps as $cp){
			$out .= emoji_utf8_bytes($cp);
		}

		return $out;
	}

	function emoji_utf8_bytes($cp){

		if ($cp > 0x10000){
			# 4 bytes
			return	chr(0xF0 | (($cp & 0x1C0000) >> 18)).
				chr(0x80 | (($cp & 0x3F000) >> 12)).
				chr(0x80 | (($cp & 0xFC0) >> 6)).
				chr(0x80 | ($cp & 0x3F));
		}else if ($cp > 0x800){
			# 3 bytes
			return	chr(0xE0 | (($cp & 0xF000) >> 12)).
				chr(0x80 | (($cp & 0xFC0) >> 6)).
				chr(0x80 | ($cp & 0x3F));
		}else if ($cp > 0x80){
			# 2 bytes
			return	chr(0xC0 | (($cp & 0x7C0) >> 6)).
				chr(0x80 | ($cp & 0x3F));
		}else{
			# 1 byte
			return chr($cp);
		}
	}

	function format_string($s){
		$out = ''; 
		for ($i=0; $i<strlen($s); $i++){
			$c = ord(substr($s,$i,1));
			if ($c >= 0x20 && $c < 0x80 && !in_array($c, array(34, 39, 92))){
				$out .= chr($c);
			}else{
				$out .= sprintf('\\x%02x', $c);
			}   
		}   
		return '"'.$out.'"';
	}   

