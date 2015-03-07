<?php
	error_reporting((E_ALL | E_STRICT) ^ E_NOTICE);

	$dir = dirname(__FILE__);

	include('catalog.php');
	include('catalog_names.php');
	include('catalog_vars.php');


	#
	# load text mappings
	#

	$lines = file('catalog_text_toascii.txt');
	$text = array();
	foreach ($lines as $line){
		$line = trim($line);
		if (strlen($line)){
			$bits = preg_split('!\s+!', $line, 2);
			$text[$bits[0]] = $bits[1];
		}
	}


	#
	# load extra apple data
	#

	$json = file_get_contents('apple_extra.json');
	$apple_data = json_decode($json, true);


	#
	# find which apple images have skin variations
	#

	echo "Finding emoji with skin variations ... ";

	$skin_variations = array();

	$out = shell_exec(" ls -1 ../img-apple-160/ | grep \"\\.0\\.png\"");
	$files = explode("\n", trim($out));
	foreach ($files as $file){
		list($key) = explode('.', $file);
		$skin_variations[$key] = array(0);
		for ($i=1; $i<=5; $i++){
			if (file_exists("../img-apple-160/{$key}.{$i}.png")){
				$skin_variations[$key][] = $i;
			}
		}
	}

	echo "OK\n";

	$skin_varation_suffixes = array(
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
		$shorts = $catalog_names[$img_key];
		$name = $row['char_name']['title'];

		if (preg_match("!^REGIONAL INDICATOR SYMBOL LETTERS !", $name)){
			array_unshift($shorts, 'flag-'.$shorts[0]);
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

	echo "Finding extra emoji from UCD: ";

	foreach ($catalog_names as $uid => $names){
		if ($uid == '_') continue;

		if (!$out_unis[$uid]){
			echo  '.';
			$out[] = build_character_data($uid, $names);
		}
	}
	echo " DONE\n";


	#
	# extra non-standard CPs
	#

	echo "Adding extra Apple emoji: ";

	foreach ($apple_data['emoji'] as $cps => $arr){

		$img_key = StrToLower($cps);
		$img_key = str_replace('200d-', '', $img_key);

		$short_names = array($arr[0]);
		$name = StrToUpper($arr[1]);

		if (substr($arr[0], 0, 5) == 'flag-'){
			$short_names[] = substr($arr[0], 5);
			$name = "REGIONAL INDICATOR SYMBOL LETTERS ".StrToUpper(substr($arr[0], 5));
		}

		echo  '.';
		$out[] = simple_row($img_key, $short_names, array(
			'name'		=> $name,
			'unified'	=> $cps,
		));
	}

	echo "OK\n";


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

		$vars = $GLOBALS['catalog_vars'][$img_key];
		if (!is_array($vars)) $vars = array();
		foreach ($vars as $k => $v) $vars[$k] = StrToUpper($v);	

		if (!is_array($shorts)) $shorts = array();
		$short = count($shorts) ? $shorts[0] : null;

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
		);

		$ret['apple_img_path']		= find_image($props['unified'], $ret['image'], "img-apple-64/");
		$ret['google_img_path']		= find_image($props['unified'], $ret['image'], "img-google-64/");
		$ret['twitter_img_path']	= find_image($props['unified'], $ret['image'], "img-twitter-64/");
		$ret['emojione_img_path']	= find_image($props['unified'], $ret['image'], "emojione/assets/png/");

		foreach ($props as $k => $v) $ret[$k] = $v;

		$skin_key = StrToLower($props['unified']);
		if (count($GLOBALS['skin_variations'][$skin_key])){

			$ret['skin_variations'] = array();
			foreach ($GLOBALS['skin_variations'][$skin_key] as $id){
				if (!$id) continue;

				$apple_path = substr($ret['apple_img_path'], 0, -6).".{$id}.png";

				$variation = array(
					'unified'		=> $props['unified'].'-'.$GLOBALS['skin_varation_suffixes'][$id],
					'sheet_x'		=> 0,
					'sheet_y'		=> 0,
					'apple_img_path'	=> $apple_path,
					'google_img_path'	=> null,
					'twitter_img_path'	=> null,
					'emojione_img_path'	=> null,
				);

				$ret['skin_variations'][$variation['unified']] = $variation;
			}
		}


		return $ret;
	}

	function find_image($unified, $image, $img_path){

		$root = "{$GLOBALS['dir']}/../";

		$src = "{$img_path}{$image}";
		if (file_exists($root.$src)) return $src;

		list($a, $b) = explode('.', $image);
		$upper_name = StrToUpper($a).'.'.$b;
		$src = "{$img_path}{$upper_name}";
		if (file_exists($root.$src)) return $src;

		$key = StrToLower($unified);
		if (count($GLOBALS['skin_variations'][$key])){
			$src = "{$img_path}{$key}.0.png";
			if (file_exists($root.$src)) return $src;
		}

		$key_upper = StrToUpper($unified);
		if ($GLOBALS['apple_data']['images'][$key_upper]){
			$src = "{$img_path}{$GLOBALS['apple_data']['images'][$key_upper]}";
			if (file_exists($root.$src)) return $src;
		}

		return null;
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
