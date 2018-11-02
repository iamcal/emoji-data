<?php
	error_reporting((E_ALL | E_STRICT) ^ E_NOTICE);

	$dir = dirname(__FILE__);

	include('catalog.php');
	include('common.php');


	#
	# the softbank mapping in the catalog is bad, so use our custom version
	#

	$softbank_map = array();

	$lines = file('data_softbank_map.txt');
	foreach ($lines as $line){
		$line = trim($line);
		if (strlen($line) == 0) continue;
		if (substr($line, 0, 1) == '#') continue;
		list($softbank, $unified) = explode(' ', $line);

		$softbank_map[StrToLower($unified)] = $softbank;
	}


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
	# load the names of all simple characters
	#

	$names_map = array();

	$fh = fopen('unicode/UnicodeData.txt', 'r');
	while (($line = fgets($fh)) !== false){
		list($cp, $name) = explode(';', $line);
		$names_map[$cp] = $name;
	}
	fclose($fh);


	#
	# load category data and start building fully-qualified map
	#

	$category_map = array();	# CP => (Category-name, Global-order)
	$categories = array();		# Category-name => []
	$qualified_map = array();	# non-qualified-CP => fully-qualified-CP

	$lines = file('unicode/emoji-test.txt');
	$last_cat = '?';
	$p = 1;
	foreach ($lines as $line){
		if (!strlen(trim($line))) continue;
		$line = rtrim($line);
		if (substr($line, 0, 9) == '# group: '){
			$last_cat = substr($line, 9);
			$categories[$last_cat] = array();
		}elseif (substr($line, 0, 1) == '#'){
			continue;
		}else{
			list($cp) = explode(';', $line);
			$cp = trim(StrToLower($cp));
			$cp = preg_replace('!\s+!', '-', $cp);
			$category_map[$cp] = array($last_cat, $p);
			$p++;

			$cp_nq = str_replace('-fe0f', '', $cp);
			if ($cp != $cp_nq) $qualified_map[$cp_nq] = $cp;
		}
	}

	# add categories for the skin tone patches
	$category_map['1f3fb'] = array('Skin Tones', $p++);
	$category_map['1f3fc'] = array('Skin Tones', $p++);
	$category_map['1f3fd'] = array('Skin Tones', $p++);
	$category_map['1f3fe'] = array('Skin Tones', $p++);
	$category_map['1f3ff'] = array('Skin Tones', $p++);

	# patch in some CPs missing from the data file
	$qualified_map['0023-20e3'] = '0023-fe0f-20e3';
	$qualified_map['002a-20e3'] = '002a-fe0f-20e3';
	$qualified_map['0030-20e3'] = '0030-fe0f-20e3';
	$qualified_map['0031-20e3'] = '0031-fe0f-20e3';
	$qualified_map['0032-20e3'] = '0032-fe0f-20e3';
	$qualified_map['0033-20e3'] = '0033-fe0f-20e3';
	$qualified_map['0034-20e3'] = '0034-fe0f-20e3';
	$qualified_map['0035-20e3'] = '0035-fe0f-20e3';
	$qualified_map['0036-20e3'] = '0036-fe0f-20e3';
	$qualified_map['0037-20e3'] = '0037-fe0f-20e3';
	$qualified_map['0038-20e3'] = '0038-fe0f-20e3';
	$qualified_map['0039-20e3'] = '0039-fe0f-20e3';

	$rev_qualified_map = array_flip($qualified_map);

	#
	# Fetching sequence names
	#

	echo "Fetching sequence names : ";

	$sequence_names = array();

	parse_unicode_specfile('unicode/emoji-sequences.txt', 'get_sequence_names');
	parse_unicode_specfile('unicode/emoji-zwj-sequences.txt', 'get_sequence_names');

	function get_sequence_names($fields, $comment){

		$uni = StrToLower(str_replace(' ', '-', trim($fields[0])));
		$name = trim($fields[2]);

		$GLOBALS['sequence_names'][$uni] = $name;
	}

	echo "DONE\n";


	#
	# get versions for all emoji
	#

	echo "Fetching versions : ";

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

	echo "DONE\n";




	#
	# load emoji names
	#

	$short_names = array();

	echo "Loading short names : ";
	load_short_names('data_emoji_names.txt');
	load_short_names('data_emoji_names_more.txt');
	load_short_names('data_emoji_names_v4.txt');
	load_short_names('data_emoji_names_v5.txt');
	load_short_names('data_emoji_names_v11.txt');
	echo "DONE\n";

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

	echo "Loading obsolete mappings : ";

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

	echo "DONE\n";


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

	echo "Loading emoji sequences : ";

	$skin_variations = array();

	parse_unicode_specfile('unicode/emoji-sequences.txt', function($fields, $comment){

		$cps = explode(' ', $fields[0]);
		$last = array_pop($cps);

		if (in_array($last, $GLOBALS['skin_variation_suffixes'])){

			$hex_low = StrToLower(implode('-', $cps));
			$GLOBALS['skin_variations'][$hex_low] = 1;
		}
	});

	echo "DONE\n";


	#
	# build the simple ones first
	#

	echo "Building emoji from base catalog : ";

	$out = array();
	$out_unis = array();
	$c = 0;

	foreach ($catalog as $row){

		$img_key = StrToLower(encode_points($row['unicode']));
		$shorts = $short_names[StrToUpper($img_key)];
		$name = $row['char_name']['title'];

		if (preg_match("!^REGIONAL INDICATOR SYMBOL LETTERS !", $name)){
			if (strlen($shorts[0]) == '2' && count($shorts) == 1){
				array_unshift($shorts, 'flag-'.$shorts[0]);
			}
		}

		add_row($img_key, $shorts, array(
			'name'		=> $name,
			'docomo'	=> encode_points($row['docomo'  ]['unicode']),
			'au'		=> encode_points($row['au'      ]['unicode']),
			'google'	=> encode_points($row['google'  ]['unicode']),
		));

		$c++;
	}

	echo "DONE ($c)\n";


	#
	# were there any codepoints we have an image for, but no data for?
	#

	echo "Building emoji we have shortcodes for : ";

	$c = 0;

	foreach ($short_names as $uid => $names){

		$img_key = StrToLower($uid);
		if ($out_unis[$img_key]) continue;

		if (substr($names[0], 0, 5) == 'flag-'){

			add_row($img_key, $names, array(
				'unified'	=> $uid,
				'name'		=> "REGIONAL INDICATOR SYMBOL LETTERS ".StrToUpper(substr($names[0], 5)),
			));
		}else{
			add_row($img_key, $names);
		}

		$c++;
	}
	echo "DONE ($c)\n";


	#
	# include everything from emoji-data.txt
	#

	echo "Checking emoji-data.txt for missing emoji : ";

	parse_unicode_specfile('unicode/emoji-data.txt', function($fields, $comment){


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

			if ($fields[1] == 'Extended_Pictographic') continue;

			$hex_up = StrToUpper($hex_low);
			$line = shell_exec("grep -e ^{$hex_up}\\; unicode/UnicodeData.txt");
			$line = trim($line);

			echo "\nno data for $cp/$hex_low from emoji-data.txt: $fields[0];$fields[1] : $line\n";
		}
	});

	echo "DONE\n";

	#
	# check against emoji-sequences.txt and emoji-zwj-sequences.txt
	#

	echo "Checking emoji-(zwj-)?sequences.txt for missing emoji : ";

	parse_unicode_specfile('unicode/emoji-sequences.txt', 'check_sequence');
	parse_unicode_specfile('unicode/emoji-zwj-sequences.txt', 'check_sequence');

	function check_sequence($fields, $comment){

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

	echo "DONE\n";


	#
	# for zwj sequences with skin tones in them, attach to non-toned version as variations
	#

	echo "Mapping skin tones to base versions : ";

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
			if (in_array($k, array('unified', 'non_qualified', 'image', 'sheet_x', 'sheet_y', 'added_in'))){
				$out[$k] = $v;
			}elseif (substr($k, 0, 8) == 'has_img_'){
				$out[$k] = $v;
			}
		}

		return $out;
	}

	echo "DONE\n";

	#
	# process obsoletes
	#

	echo "Attaching obsoletes to their bases : ";

	$cp_map = array();
	$cp_map_skin = array();
	foreach ($out as $k => $row){
		$cp_map[$row['unified']] = $k;
		if (isset($row['skin_variations'])){
			foreach ($row['skin_variations'] as $k2 => $row2){
				$cp_map_skin[$row2['unified']] = array($k, $k2);
			}
		}
	}

	foreach ($obsoleted_by as $k => $v){
		$idx = $cp_map[$k];
		if ($idx){
			$out[$idx]['obsoleted_by'] = $v;
		}else{
			$idx = $cp_map_skin[$k];
			if (is_array($idx)){
				$out[$idx[0]]['skin_variations'][$idx[1]]['obsoleted_by'] = $v;
			}else{
				echo "\nERROR: unable to find index for cp {$k} while adding obsoletes\n";
			}
		}
	}

	foreach ($obsoletes as $k => $v){
		$idx = $cp_map[$k];
		if ($idx){
			$out[$idx]['obsoletes'] = $v;
		}else{
			$idx = $cp_map_skin[$k];
			if (is_array($idx)){
				$out[$idx[0]]['skin_variations'][$idx[1]]['obsoletes'] = $v;
			}else{
				echo "\nERROR: unable to find index for cp {$k} while adding obsoletes\n";
			}
		}
	}

	echo "DONE\n";


	#
	# check for duplicate short names
	#

	echo "Checking for duplicate shortnames : ";

	$uniq = array();

	foreach ($out as $k => $row){

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
			print_r($row);
			exit;
		}
	}

	echo "DONE\n";



	#
	# core functions
	#

	function add_row($img_key, $short_names, $props = array()){

		# if we get passed a non-qualified version, swap it for the fq version
		if (isset($GLOBALS['qualified_map'][$img_key])){
			$img_key = $GLOBALS['qualified_map'][$img_key];
		}

		if (isset($GLOBALS['out_unis'][$img_key])){
			echo "\nERROR: trying to set duplicate emoji $img_key\n";
			print_r($short_names);
			print_r($props);
			foreach ($GLOBALS['out'] as $row){
				if ($row['image'] == $img_key.'.png'){
					echo "Matches:\n";
					print_r($row);
				}
			}
			exit;
		}

		if (!isset($props['name'])){
			$props['name'] = $GLOBALS['names_map'][StrToUpper($img_key)];
		}
		if (!isset($props['unified'])){
			$props['unified'] = StrToUpper($img_key);
		}

		$row = simple_row($img_key, $short_names, $props);

    // TODO: This causes emoji to be silently discarded. Bad!
    if ($row != null) {
      $GLOBALS['out'][] = $row;
      $GLOBALS['out_unis'][$img_key] = 1;
      if ($row['non_qualified']) $GLOBALS['out_unis'][StrToLower($row['non_qualified'])] = 1;
    }
	}

	function simple_row($img_key, $shorts, $props){

		if (!is_array($shorts)) $shorts = array();
		$short = count($shorts) ? $shorts[0] : null;

		$added = $GLOBALS['versions'][$img_key];

		$nq = null;
		if ($GLOBALS['rev_qualified_map'][$img_key]){
			$nq = StrToUpper($GLOBALS['rev_qualified_map'][$img_key]);
			if (!$added){
				$added = $GLOBALS['versions'][StrToLower($nq)];
			}
		}

		$softbank = null;
		if ($GLOBALS['softbank_map'][$img_key]){
			$softbank = $GLOBALS['softbank_map'][$img_key];
			unset($GLOBALS['softbank_map'][$img_key]);
		}else{
			if ($nq && $GLOBALS['softbank_map'][StrToLower($nq)]){
				$softbank = $GLOBALS['softbank_map'][StrToLower($nq)];
				 unset($GLOBALS['softbank_map'][StrToLower($nq)]);
			}
		}

		$category = $GLOBALS['category_map'][$img_key];
    // TODO: Make sure every emoji has a category! Currently man/woman super
    // hero/villian do not.
    if (!$category) {
      print "\nNot in category map! $img_key\n";
      return null;
    }

		if ($props['name']){
			if (preg_match("!^REGIONAL INDICATOR SYMBOL LETTERS !", $props['name'])){

				if ($GLOBALS['sequence_names'][$img_key]){

					$props['name'] = $GLOBALS['sequence_names'][$img_key].' Flag';
				}
			}
		}


		$ret = array(
			'name'		=> null,
			'unified'	=> null,
			'non_qualified'	=> $nq,
			'docomo'	=> null,
			'au'		=> null,
			'softbank'	=> $softbank,
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
			'added_in'	=> $added,
		);

		$ret['has_img_apple']		= file_exists("{$GLOBALS['dir']}/../img-apple-64/{$ret['image']}");
		$ret['has_img_google']		= file_exists("{$GLOBALS['dir']}/../img-google-64/{$ret['image']}");
		$ret['has_img_twitter']		= file_exists("{$GLOBALS['dir']}/../img-twitter-64/{$ret['image']}");
		$ret['has_img_facebook']	= file_exists("{$GLOBALS['dir']}/../img-facebook-64/{$ret['image']}");
		$ret['has_img_messenger']	= file_exists("{$GLOBALS['dir']}/../img-messenger-64/{$ret['image']}");

		foreach ($props as $k => $v) $ret[$k] = $v;

		$has_skin_vars = false;
		$skin_vars_base = $img_key;

		if ($GLOBALS['skin_variations'][$img_key]) $has_skin_vars = true;
		if (file_exists("../img-apple-64/{$img_key}-1f3fb.png")) $has_skin_vars = true;
		if ($nq){
			if ($GLOBALS['skin_variations'][StrTolower($nq)]){
				$has_skin_vars = true;
				$skin_vars_base = StrTolower($nq);
			}
		}

		if ($has_skin_vars){

			$ret['skin_variations'] = array();

			foreach ($GLOBALS['skin_variation_suffixes'] as $suffix){

				$var_uni	= StrToUpper($skin_vars_base.'-'.$suffix);
				$var_img_key	= StrToLower($skin_vars_base.'-'.$suffix);
				$var_img	= $var_img_key.'.png';

				$var_nq = null;
				if ($GLOBALS['rev_qualified_map'][$var_img_key]){
					$var_nq = StrToUpper($GLOBALS['rev_qualified_map'][$var_img_key]);
				}


				$variation = array(
					'unified'		=> $var_uni,
					'non_qualified'		=> $var_nq,
					'image'			=> $var_img,
					'sheet_x'		=> 0,
					'sheet_y'		=> 0,
					'added_in'		=> $GLOBALS['versions'][StrToLower($var_uni)],
					'has_img_apple'		=> file_exists("{$GLOBALS['dir']}/../img-apple-64/{$var_img}"),
					'has_img_google'	=> file_exists("{$GLOBALS['dir']}/../img-google-64/{$var_img}"),
					'has_img_twitter'	=> file_exists("{$GLOBALS['dir']}/../img-twitter-64/{$var_img}"),
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

	echo "Building category sort orders : ";

	$missing_categories = array();
	$shortname_map = array();

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

	echo "DONE\n";


	#
	# find missing categories
	#

	echo "Checking for missing categories : ";
	foreach (array_keys($missing_categories) as $k){

		$row = $out[$shortname_map[$k]];

		echo "\nWARNING: no category for U+{$row['unified']} / :{$row['short_name']}:\n";
	}
	echo "DONE\n";


	#
	# apply categories back into the output hash
	#

	echo "Setting categories : ";

	foreach ($categories as $cat => $names){
		foreach ($names as $p => $name){
			$index = $shortname_map[$name];
			$out[$index]['category'] = $cat;
			$out[$index]['sort_order'] = $p+1;
		}
	}

	echo "DONE\n";


	#
	# output category info
	#

	echo "Saving categories : ";

	$fh = fopen('../categories.json', 'w');
	fwrite($fh, json_encode($categories, JSON_PRETTY_PRINT));
	fclose($fh);

	echo "DONE\n";


	#
	# sort everything into a nice order
	#

	echo "Sorting output list : ";

	foreach ($out as $k => $v){
		$out[$k]['sort'] = str_pad($v['unified'], 20, '0', STR_PAD_RIGHT);
	}

	usort($out, 'sort_rows');

	foreach ($out as $k => $v){
		unset($out[$k]['sort']);
	}

	function sort_rows($a, $b){
		return strcmp($a['sort'], $b['sort']);
	}

	echo "DONE\n";


	#
	# assign positions
	#

	echo "Assigning grid positions : ";

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

	echo "DONE\n";


	#
	# write map
	#

	echo "Writing map : ";

	$fh = fopen('../emoji.json', 'w');
	fwrite($fh, json_encode($out));
	fclose($fh);

	echo "DONE\n";


	echo "Writing pretty map : ";

	$fh = fopen('../emoji_pretty.json', 'w');
	fwrite($fh, json_encode($out, JSON_PRETTY_PRINT));
	fclose($fh);

	echo "DONE\n";


	if (count($softbank_map)){
		echo "Missing ".count($softbank_map)." codepoint(s) for softbank: ".implode(', ', array_keys($softbank_map))."\n";
	}


	echo "-- ALL DONE --\n";
