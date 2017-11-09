<?php
	error_reporting((E_ALL | E_STRICT) ^ E_NOTICE);

	$dir = dirname(__FILE__);

	include('catalog.php');
	include('common.php');


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
	# load codepoint variations
	# we do this before loading the `emoji_categories.json` so we can add
	# all of the *-FE0F variations from OS X/iOS
	#

	$raw = file('data_variations.txt');

	$variations = array();
	$variations_handled = array();

	foreach ($raw as $line){
		if (substr($line, 0, 1) == '#') continue;
		list($line, $junk) = explode('#', $line);
		list($key, $vars) = explode(';', trim($line));
		if (strlen($key)){
			$variations[$key] = explode('/', $vars);
		}
	}


	#
	# load zwj variations too
	#

	parse_unicode_specfile('unicode/emoji-zwj-sequences.txt', function($fields, $comment){

		$cps = explode(' ', $fields[0]);
		$hex_low = StrToLower(implode('-', $cps));

	#	$key1 = str_replace('-200d-', '-', $hex_low);
	#	if ($key1 != $hex_low){
	#		add_variation($key1, $hex_low);
	#	}

		$key2 = str_replace('-fe0f', '', $hex_low);
		if ($key2 != $hex_low){
			add_variation($key2, $hex_low);
		}

		$key3 = str_replace('-200d-', '-', str_replace('-fe0f', '', $hex_low));
		if ($key3 != $hex_low){
			add_variation($key3, $hex_low);
		}
	});

	function add_variation($base, $variation){
		global $variations;

		if (!is_array($variations[$base])){
			$variations[$base] = array();
		}

		if (!in_array($variation, $variations[$base])){
			$variations[$base][] = $variation;
		}
	}


	#
	# get versions for all emoji
	#

	echo "\nFetching versions : ";

	$versions = array();

	parse_unicode_specfile('unicode/emoji-data.txt', 'get_versions');
	parse_unicode_specfile('unicode/emoji-sequences.txt', 'get_versions');
	parse_unicode_specfile('unicode/emoji-zwj-sequences.txt', 'get_versions');

	function get_versions($fields, $comment){

		$hex_lows = array();

		if (strpos($fields[0], '..')){
			list($a, $b) = explode('..', $fields[0]);
			$a = hexdec($a);
			$b = hexdec($b);

			$cps = array();
			for ($i=$a; $i<=$b; $i++){
				$hex_lows[] = sprintf('%04x', $i);
			}
		}else{
			$cps = explode(' ', $fields[0]);
			$hex_lows = array(StrToLower(implode('-', $cps)));
		}

		if (preg_match('!^\s*(\d+\.\d+)!', $comment, $m)){
			foreach ($hex_lows as $hex_low){
				$GLOBALS['versions'][$hex_low] = $m[1];
			}
		}
	}

	echo " DONE\n";


	#
	# load category data
	#

	$category_map = array();
	$category_list = array();

	$lines = file('data_categories.txt');
	$last_cat = '?';
	$p = 1;
	foreach ($lines as $line){
		$line = rtrim($line);
		if (substr($line, 0, 1) == "\t"){
			$category_map[substr($line, 1)] = array($last_cat, $p);
			$p++;
		}else{
			$last_cat = $line;
			$p = 1;
			$category_list[] = $line;
		}
	}


	#
	# load emoji names
	#

	$short_names = array();

	load_short_names('data_emoji_names.txt');
	load_short_names('data_emoji_names_more.txt');
	load_short_names('data_emoji_names_v4.txt');
	load_short_names('data_emoji_names_v5.txt');

	function load_short_names($file){

		$raw = file($file);

		foreach ($raw as $line){

			if (strpos($line, '#') === 0) continue;
			$line = trim($line);
			if (!strlen($line)) continue;

			if (preg_match('!{.*?}!', $line)){
				$lines = expand_short_name_line($line);

				foreach ($lines as $line){
					load_short_name($line);
				}
			}else{
				load_short_name($line);
			}
		}
	}

	function expand_short_name_line($line){

		# expand gender first
		if (preg_match('!{(MAN/WOMAN|MALE/FEMALE|GENDER|M/W)}!', $line)){
			$a = str_replace(array('{MAN/WOMAN}', '{MALE/FEMALE}', '{GENDER}', '{M/W}'), array('1F468', '2642-FE0F', 'male', 'man'), $line);
			$b = str_replace(array('{MAN/WOMAN}', '{MALE/FEMALE}', '{GENDER}', '{M/W}'), array('1F469', '2640-FE0F', 'female', 'woman'), $line);
			return array_merge(
				expand_short_name_line($a),
				expand_short_name_line($b)
			);
		}

		# expand optional skin tones
		if (preg_match('#{SKIN}#', $line, $m)){
			$out = array();
			$out[] = str_replace('{SKIN}', '', $line);
			$out[] = str_replace('{SKIN}', '-1F3FB', $line).':skin-2';
			$out[] = str_replace('{SKIN}', '-1F3FC', $line).':skin-3';
			$out[] = str_replace('{SKIN}', '-1F3FD', $line).':skin-4';
			$out[] = str_replace('{SKIN}', '-1F3FE', $line).':skin-5';
			$out[] = str_replace('{SKIN}', '-1F3FF', $line).':skin-6';
			return $out;
		}

		# expand required skin tones
		if (preg_match('#{SKIN!}#', $line, $m)){
			$out = array();
			$out[] = str_replace('{SKIN!}', '-1F3FB', $line).':skin-2';
			$out[] = str_replace('{SKIN!}', '-1F3FC', $line).':skin-3';
			$out[] = str_replace('{SKIN!}', '-1F3FD', $line).':skin-4';
			$out[] = str_replace('{SKIN!}', '-1F3FE', $line).':skin-5';
			$out[] = str_replace('{SKIN!}', '-1F3FF', $line).':skin-6';
			return $out;
		}

		# give up
		return array($line);
	}

	function load_short_name($line){

		list($cp, $names) = explode(';', $line);

		if (isset($GLOBALS['short_names'][$cp])){
			$val = implode('/', $GLOBALS['short_names'][$cp]);
			echo "ignoring def for $line (already set to {$val})\n";
		}else{
			$GLOBALS['short_names'][$cp] = explode('/', $names);
		}
	}


	#
	# load obsolete mappings
	#

	$raw = file('data_obsoleted.txt');

	$obsoleted_by = array();
	$obsoletes = array();

	foreach ($raw as $line){
		list($line, $junk) = explode('#', $line);
		list($key, $var) = explode(';', StrToUpper(trim($line)));
		if (strlen($key)){
			$obsoleted_by[$key] = $var;
			$obsoletes[$var] = $key;
		}
	}

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
	# build the official list of emoji with skin variations
	#

	$skin_variations = array();

	parse_unicode_specfile('unicode/emoji-sequences.txt', function($fields, $comment){

		$cps = explode(' ', $fields[0]);
		$last = array_pop($cps);

		if (in_array($last, $GLOBALS['skin_variation_suffixes'])){

			$hex_low = StrToLower(implode('-', $cps));
			$GLOBALS['skin_variations'][$hex_low] = 1;
		}
	});


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

	echo "\nFinding emoji we have names for: ";

	foreach ($short_names as $uid => $names){

		$img_key = StrToLower($uid);
		if ($out_unis[$img_key]) continue;
		$out_unis[$img_key] = 1;

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


	#
	# include everything from emoji-data.txt
	#

	echo "\nProcessing emoji-data.txt : ";

	parse_unicode_specfile('unicode/emoji-data.txt', function($fields, $comment){

		echo '.';

		global $out_unis, $out;

		if (strpos($fields[0], '..')){
			list($a, $b) = explode('..', $fields[0]);
			$a = hexdec($a);
			$b = hexdec($b);

			$cps = array();
			for ($i=$a; $i<=$b; $i++){
				$cps[] = $i;
			}
		}else{
			$cp = sprintf('%04x', hexdec($fields[0]));
			$cps = array(hexdec($fields[0]));
		}

		foreach ($cps as $cp){

			$hex_low = sprintf('%04x', $cp);

			if ($out_unis[$hex_low]) continue;

			if ($cp == 0x0023) continue; # number sign
			if ($cp == 0x002A) continue; # asterisk
			if ($cp >= 0x0030 && $cp <= 0x0039) continue; # 0-9
			if ($cp >= 0x1F1E6 && $cp <= 0x1F1FF) continue; # flag letters

			$hex_up = StrToUpper($hex_low);
			$line = shell_exec("grep -e ^{$hex_up}\\; unicode/UnicodeData.txt");
			$line = trim($line);

			echo "\nno data for $cp/$hex_low, but found in emoji-data.txt : $line\n";
		}
	});

	echo " DONE\n";


	#
	# check against emoji-sequences.txt and emoji-zwj-sequences.txt
	#

	echo "\nProcessing emoji-(zwj-)?sequences.txt : ";

	parse_unicode_specfile('unicode/emoji-sequences.txt', 'check_sequence');
	parse_unicode_specfile('unicode/emoji-zwj-sequences.txt', 'check_sequence');

	function check_sequence($fields, $comment){

		echo '.';

		$cps = explode(' ', $fields[0]);
		$last = $cps[count($cps)-1];
		$hex_low = StrToLower(implode('-', $cps));

		# skip skin tone variations - we treat those specially
		if (in_array($last, $GLOBALS['skin_variation_suffixes'])) return;

		# is this already on the output list?
		if ($GLOBALS['out_unis'][$hex_low]) return;

		# is this an explicit variation we've already added to the output map?
		if ($GLOBALS['variations_handled'][$hex_low]) return;

		echo "\nFound sequence not supported: $hex_low / {$comment}\n";
	}

	echo " DONE\n";


	#
	# for zwj sequences with skin tones in them, attach to non-toned version as variations
	#

	$variations = array();

	$out = array_filter($out, function($row){
		if (strpos($row['short_name'], ':skin-') > 0){
			$GLOBALS['variations'][] = $row;
			return false;
		}
		return true;
	});

	$short_name_map = array();
	foreach ($out as $k => $row){
		$short_name_map[$row['short_name']] = $k;
	}

	$skin_codepoints = array(
		'skin-2' => '1F3FB',
		'skin-3' => '1F3FC',
		'skin-4' => '1F3FD',
		'skin-5' => '1F3FE',
		'skin-6' => '1F3FF',
	);

	foreach ($variations as $row){
		list($name, $skin) = explode(':', $row['short_name']);
		$skin_cp = $skin_codepoints[$skin];
		$key = $short_name_map[$name];

		if (isset($key) && isset($out[$key])){
			$out[$key]['skin_variations'][$skin_cp] = simplify_row($row);
		}else{
			echo "\nERROR: unable to find parent for {$row['short_name']}\n";
		}
	}

	function simplify_row($row){
		$out = array();

		foreach ($row as $k => $v){
			if (in_array($k, array('unified', 'image', 'sheet_x', 'sheet_y'))){
				$out[$k] = $v;
			}elseif (substr($k, 0, 8) == 'has_img_'){
				$out[$k] = $v;
			}
		}

		return $out;
	}


	#
	# process obsoletes
	#

	$cp_map = array();
	foreach ($out as $k => $row){
		$cp_map[$row['unified']] = $k;
	}

	foreach ($obsoleted_by as $k => $v){
		$idx = $cp_map[$k];
		$out[$idx]['obsoleted_by'] = $v;
	}
	
	foreach ($obsoletes as $k => $v){
		$idx = $cp_map[$k];
		$out[$idx]['obsoletes'] = $v;
	}


	#
	# check for duplicate short names
	#

	echo "Checking shortnames : ";

	$uniq = array();

	foreach ($out as $k => $row){

		echo '.';

		if ($row['unified']){
			$k = 'U+'.$row['unified'];
		}else{
			$k = "UNKNOWN/$k";
		}

		if (count($row['short_names'])){

			foreach ($row['short_names'] as $sn){

				if ($uniq[$sn]){
					echo "\nDuplicate shortname :{$sn}: for {$uniq[$sn]} and $k\n";
				}else{
					$uniq[$sn] = $k;
				}
			}

		}else{
			echo "\nCharacter with no shortname: $k\n";
		}
	}

	echo " DONE\n";





	function build_character_data($img_key, $short_names){

		$uni = StrToUpper($img_key);

		$line = shell_exec("grep -e ^{$uni}\\; unicode/UnicodeData.txt");
		list($junk, $name) = explode(';', $line);


		return simple_row($img_key, $short_names, array(
			'name'		=> $name,
			'unified'	=> $uni,
		));
	}


	function simple_row($img_key, $shorts, $props){

		$vars = $GLOBALS['variations'][$img_key];
		if (!is_array($vars)) $vars = array();
		foreach ($vars as $k => $v){
			$vars[$k] = StrToUpper($v);
			$GLOBALS['variations_handled'][$v] = 1;
		}

		if (!is_array($shorts)) $shorts = array();
		$short = count($shorts) ? $shorts[0] : null;

		$category = $GLOBALS['category_map'][$short];

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
			'added_in'	=> $GLOBALS['versions'][$img_key],
		);

		$ret['has_img_apple']		= file_exists("{$GLOBALS['dir']}/../img-apple-64/{$ret['image']}");
		$ret['has_img_google']		= file_exists("{$GLOBALS['dir']}/../img-google-64/{$ret['image']}");
		$ret['has_img_twitter']		= file_exists("{$GLOBALS['dir']}/../img-twitter-64/{$ret['image']}");
		$ret['has_img_emojione']	= file_exists("{$GLOBALS['dir']}/../img-emojione-64/{$ret['image']}");
		$ret['has_img_facebook']	= file_exists("{$GLOBALS['dir']}/../img-facebook-64/{$ret['image']}");
		$ret['has_img_messenger']	= file_exists("{$GLOBALS['dir']}/../img-messenger-64/{$ret['image']}");

		foreach ($props as $k => $v) $ret[$k] = $v;

		if ($GLOBALS['skin_variations'][$img_key] || file_exists("../img-apple-64/{$img_key}-1f3fb.png")){

			$ret['skin_variations'] = array();

			foreach ($GLOBALS['skin_variation_suffixes'] as $suffix){

				$var_uni = $props['unified'].'-'.$suffix;
				$var_img = $img_key.'-'.StrToLower($suffix).'.png';

				$variation = array(
					'unified'		=> $var_uni,
					'image'			=> $var_img,
					'sheet_x'		=> 0,
					'sheet_y'		=> 0,
					'added_in'		=> $GLOBALS['versions'][StrToLower($var_uni)],
					'has_img_apple'		=> file_exists("{$GLOBALS['dir']}/../img-apple-64/{$var_img}"),
					'has_img_google'	=> file_exists("{$GLOBALS['dir']}/../img-google-64/{$var_img}"),
					'has_img_twitter'	=> file_exists("{$GLOBALS['dir']}/../img-twitter-64/{$var_img}"),
					'has_img_emojione'	=> file_exists("{$GLOBALS['dir']}/../img-emojione-64/{$var_img}"),
					'has_img_facebook'	=> file_exists("{$GLOBALS['dir']}/../img-facebook-64/{$var_img}"),
					'has_img_messenger'	=> file_exists("{$GLOBALS['dir']}/../img-messenger-64/{$var_img}"),
				);

				$ret['skin_variations'][$suffix] = $variation;
			}
		}


		return $ret;
	}


	#
	# patch up category and sort order fields - build the current sort maps
	#

	$missing_categories = array();
	$shortname_map = array();
	$categories = array();

	foreach ($out as $k => $row){
		$shortname_map[$row['short_name']] = $k;
		if ($row['category']){
			$sort_key = sprintf('%05d', $row['sort_order']).'_'.$row['short_name'];
			$categories[$row['category']][$sort_key] = $row['short_name'];
		}else{
			$missing_categories[$row['short_name']] = 1;
		}
	}
	foreach ($categories as $k => $v){
		ksort($v);
		$categories[$k] = array_values($v);
	}


	#
	# for known emoji, set them into the correct categories
	#

	foreach (array_keys($missing_categories) as $k){
		if (substr($k, 0, 5) == 'flag-'){
			category_append($k, 'Flags');
		}
	}

	function category_append($id, $cat){
		global $categories, $missing_categories;

		if (!$missing_categories[$id]) return;

		$categories[$cat][] = $id;
		unset($missing_categories[$id]);
	}

	function category_insert_after($id, $after){
		global $categories, $missing_categories;

		if (!$missing_categories[$id]) return;

		foreach ($categories as $k => $emojis){
			$out = array();
			$found = false;
			foreach ($emojis as $emoji){
				$out[] = $emoji;
				if ($emoji == $after){
					$out[] = $id;
					$found = true;
				}
			}
			if ($found){
				$categories[$k] = $out;
				unset($missing_categories[$id]);
			}
		}
	}

	function category_insert_before($id, $before){
		global $categories, $missing_categories;

		if (!$missing_categories[$id]) return;

		foreach ($categories as $k => $emojis){
			$out = array();
			$found = false;
			foreach ($emojis as $emoji){
				if ($emoji == $before){
					$out[] = $id;
					$found = true;
				}
				$out[] = $emoji;
			}
			if ($found){
				$categories[$k] = $out;
				unset($missing_categories[$id]);
			}
		}
	}


	#
	# for obsoleted codepoints, steal category in one direction or another
	#

	$cp_map = array();
	foreach ($out as $k => $row){
		$cp_map[$row['unified']] = $k;
	}

	foreach ($missing_categories as $k => $junk){
		$row = $out[$shortname_map[$k]];

		if ($row['obsoletes']){
			$idx = $cp_map[$row['obsoletes']];
			$row2 = $out[$idx];
			$cat = find_assigned_cat($row2['short_name']);
			if ($cat){
				$categories[$cat][] = $row['short_name'];
				unset($missing_categories[$row['short_name']]);
			}
		}

		if ($row['obsoleted_by']){
			$idx = $cp_map[$row['obsoleted_by']];
			$row2 = $out[$idx];
			$cat = find_assigned_cat($row2['short_name']);
			if ($cat){
				$categories[$cat][] = $row['short_name'];
				unset($missing_categories[$row['short_name']]);
			}
		}
	}

	function find_assigned_cat($short_name){
		global $categories;
		foreach ($categories as $cat => $ids){
			foreach ($ids as $id){
				if ($id == $short_name) return $cat;
			}
		}
		return null;
	}

	#TODO



	#
	# find missing categories
	#

	echo "\n";
	foreach (array_keys($missing_categories) as $k){

		$row = $out[$shortname_map[$k]];

		echo "WARNING: no category for U+{$row['unified']} / :{$row['short_name']}:\n";
	}


	#
	# apply categories back into the output hash
	#

	foreach ($categories as $cat => $names){
		foreach ($names as $p => $name){
			$index = $shortname_map[$name];
			$out[$index]['category'] = $cat;
			$out[$index]['sort_order'] = $p+1;
		}
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


