<?php
	error_reporting((E_ALL | E_STRICT) ^ E_NOTICE);

	$dir = dirname(__FILE__);

	include('catalog.php');


	#
	# load text mappings
	#

	$text = array();	# smile -> :)
	$texts = array();	# smile -> [:) (: :D]

	$lines = file('data_text_toascii.txt');
	foreach ($lines as $line){
		$line = trim($line);
		if (strlen($line)){
			$bits = preg_split('!\s+!', $line, 2);
			$text[$bits[0]] = $bits[1];
		}
	}

	$lines = file('data_text_toemoji.txt');
	foreach ($lines as $line){
		$line = trim($line);
		if (strlen($line)){
			$bits = preg_split('!\s+!', $line, 2);
			$texts[$bits[1]][] = $bits[0];
		}
	}


	#
	# load category data
	#

	$category_map = array();
	$category_list = array();

	$json = file_get_contents('emoji_categories.json');
	$obj = json_decode($json, true);

	foreach ($obj['EmojiDataArray'] as $cat){
		list($junk, $cat_name) = explode('-', $cat['CVDataTitle']);
		$p = 1;
		foreach (explode(',', $cat['CVCategoryData']['Data']) as $glyph){

			$hex = StrToUpper(utf8_bytes_to_hex($glyph));
			$category_map[$hex] = array($cat_name, $p);

			if (preg_match('!-FE0F$!', $hex)){
				$category_map[substr($hex, 0, -5)] = array($cat_name, $p);
			}

			$p++;
		}

		$category_list[] = $cat_name;
	}


	#
	# load emoji names
	#

	$short_names = array();

	load_short_names('data_emoji_names.txt');
	load_short_names('data_emoji_names_more.txt');

	function load_short_names($file){

		$raw = file($file);

		foreach ($raw as $line){

			if (strpos($line, '#') === 0) continue;
			$line = trim($line);
			if (!strlen($line)) continue;

			list($cp, $names) = explode(';', $line);
			if (isset($GLOBALS['short_names'][$cp])){
				echo "ignoring def for $line\n";
			}else{
				$GLOBALS['short_names'][$cp] = explode('/', $names);
			}
		}
	}


	#
	# load codepoint variations
	#

	$raw = file('data_variations.txt');

	$variations = array();
	foreach ($raw as $line){
		list($key, $vars) = explode(';', trim($line));
		$variations[$key] = explode('/', $vars);
	}

	# manually patch in the ligature version of family/man-woman-boy
	$variations['1f46a'][] = '1f468-200d-1f469-200d-1f466';

	echo "OK\n";


	#
	# list of skin variations
	#

	$skin_variation_suffixes = array(
		1 => '1F3FB',
		2 => '1F3FC',
		3 => '1F3FD',
		4 => '1F3FE',
		5 => '1F3FF',
	);


	#
	# build the simple ones first
	#

	$out = array();
	$out_unis = array();

	foreach ($catalog as $row){

		$img_key = StrToLower(encode_points($row['unicode']));
		$shorts = $short_names[StrToUpper($img_key)];
		$name = $row['char_name']['title'];

		if (preg_match("!^REGIONAL INDICATOR SYMBOL LETTERS !", $name)){
			if (strlen($shorts[0]) == '2'){
				array_unshift($shorts, 'flag-'.$shorts[0]);
			}
		}

		$out[] = simple_row($img_key, $shorts, array(
			'name'		=> $name,
			'unified'	=> encode_points($row['unicode']),
			'docomo'	=> encode_points($row['docomo'  ]['unicode']),
			'au'		=> encode_points($row['au'      ]['unicode']),
			'softbank'	=> encode_points($row['softbank']['unicode']),
			'google'	=> encode_points($row['google'  ]['unicode']),
		));

		$out_unis[$img_key] = 1;
	}


	#
	# were there any codepoints we have an image for, but no data for?
	#

	echo "Finding emoji we have names for: ";

	foreach ($short_names as $uid => $names){

		$img_key = StrToLower($uid);
		if ($out_unis[$img_key]) continue;

		echo '.';

		if (substr($names[0], 0, 5) == 'flag-'){

			$out[] = simple_row($img_key, $names, array(
				'unified'	=> $uid,
				'name'		=> "REGIONAL INDICATOR SYMBOL LETTERS ".StrToUpper(substr($names[0], 5)),
			));
		}else{
			$out[] = build_character_data($img_key, $names);
		}
	}
	echo " DONE\n";


	function build_character_data($img_key, $short_names){

		global $text;

		$uni = StrToUpper($img_key);

		$line = shell_exec("grep -e ^{$uni}\\; UnicodeData.txt");
		list($junk, $name) = explode(';', $line);


		return simple_row($img_key, $short_names, array(
			'name'		=> $name,
			'unified'	=> $uni,
		));
	}


	function simple_row($img_key, $shorts, $props){

		$vars = $GLOBALS['variations'][$img_key];
		if (!is_array($vars)) $vars = array();
		foreach ($vars as $k => $v) $vars[$k] = StrToUpper($v);	

		if (!is_array($shorts)) $shorts = array();
		$short = count($shorts) ? $shorts[0] : null;

		$category = $GLOBALS['category_map'][$props['unified']];
		if (!is_array($category)){
			foreach ($vars as $v){
				if (is_array($GLOBALS['category_map'][$v])){
					$category = $GLOBALS['category_map'][$v];
				}
			}
		}

		$ret = array(
			'name'		=> null,
			'unified'	=> null,
			'variations'	=> $vars,
			'docomo'	=> null,
			'au'		=> null,
			'softbank'	=> null,
			'google'	=> null,
			'image'		=> $img_key.'.png',
			'sheet_x'	=> 0,
			'sheet_y'	=> 0,
			'short_name'	=> $short,
			'short_names'	=> $shorts,
			'text'		=> $GLOBALS['text'][$short],
			'texts'		=> $GLOBALS['texts'][$short],
			'category'	=> is_array($category) ? $category[0] : null,
			'sort_order'	=> is_array($category) ? $category[1] : null,
		);

		$ret['has_img_apple']		= file_exists("{$GLOBALS['dir']}/../img-apple-64/{$ret['image']}");
		$ret['has_img_google']		= file_exists("{$GLOBALS['dir']}/../img-google-64/{$ret['image']}");
		$ret['has_img_twitter']		= file_exists("{$GLOBALS['dir']}/../img-twitter-64/{$ret['image']}");
		$ret['has_img_emojione']	= file_exists("{$GLOBALS['dir']}/../img-emojione-64/{$ret['image']}");

		foreach ($props as $k => $v) $ret[$k] = $v;

		if (file_exists("../img-apple-64/{$img_key}-1f3fb.png")){

			$ret['skin_variations'] = array();

			foreach ($GLOBALS['skin_variation_suffixes'] as $suffix){

				$var_uni = $props['unified'].'-'.$suffix;
				$var_img = $img_key.'-'.StrToLower($suffix).'.png';

				$variation = array(
					'unified'		=> $var_uni,
					'image'			=> $var_img,
					'sheet_x'		=> 0,
					'sheet_y'		=> 0,
					'has_img_apple'		=> file_exists("{$GLOBALS['dir']}/../img-apple-64/{$var_img}"),
					'has_img_google'	=> file_exists("{$GLOBALS['dir']}/../img-google-64/{$var_img}"),
					'has_img_twitter'	=> file_exists("{$GLOBALS['dir']}/../img-twitter-64/{$var_img}"),
					'has_img_emojione'	=> file_exists("{$GLOBALS['dir']}/../img-emojione-64/{$var_img}"),
				);

				$ret['skin_variations'][$var_uni] = $variation;
			}
		}


		return $ret;
	}


	#
	# sort everything into a nice order
	#

	foreach ($out as $k => $v){
		$out[$k]['sort'] = str_pad($v['unified'], 20, '0', STR_PAD_LEFT);
	}

	usort($out, 'sort_rows');

	foreach ($out as $k => $v){
		unset($out[$k]['sort']);
	}

	function sort_rows($a, $b){
		return strcmp($a['sort'], $b['sort']);
	}


	#
	# assign positions
	#

	$y = 0;
	$x = 0;
	$total = 0;
	foreach ($out as $row){
		$total++;
		$total += count($row['skin_variations']);
	}
	$num = ceil(sqrt($total));


	foreach ($out as $k => $v){
		$out[$k]['sheet_x'] = $x;
		$out[$k]['sheet_y'] = $y;
		$y++;
		if ($y == $num){
			$x++;
			$y = 0;
		}

		if (count($out[$k]['skin_variations'])){
			foreach ($out[$k]['skin_variations'] as $k2 => $v2){
				$out[$k]['skin_variations'][$k2]['sheet_x'] = $x;
				$out[$k]['skin_variations'][$k2]['sheet_y'] = $y;

				$y++;
				if ($y == $num){
					$x++;
					$y = 0;
				}
			}
		}
	}


	#
	# write map
	#

	echo "Writing map: ";

	$fh = fopen('../emoji.json', 'w');
	fwrite($fh, json_encode($out));
	fclose($fh);

	echo "DONE\n";


	echo "Writing pretty map: ";

	$fh = fopen('../emoji_pretty.json', 'w');
	fwrite($fh, json_encode($out, JSON_PRETTY_PRINT));
	fclose($fh);

	echo "DONE\n";


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

