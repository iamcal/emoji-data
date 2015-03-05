<?php
	#
	# use the names DB from gemoji
	#

	$json = file_get_contents(dirname(__FILE__).'/../gemoji/db/emoji.json');
	$obj = json_decode($json, true);


	#
	# but also use our own older variations catalog, since some seem to
	# have gome missing in gemoji, but we still want to support them.
	#

	include('catalog_vars_old.php');
	$old_vars = $catalog_vars;


	#
	# build the mapping array
	#

	$names = array();
	$vars = array();

	foreach ($obj as $row){

		if (!strlen($row['emoji'])){
			foreach ($row['aliases'] as $alias){
				$names['_'][] = $alias;
			}
			continue;
		}

		$uni = $row['emoji'];
		$chars = preg_split('/(?<!^)(?!$)/u', $uni);

		$unis_all = array();
		$unis_basic = array();

		foreach ($chars as $char){
			$uni = utf8_bytes_to_uni_hex($char);
			$unis_all[] = $uni;
			if ($uni !== 'fe0f'){
				$unis_basic[] = $uni;
			}
		}

		$simple_basic = implode('-', $unis_basic);
		$simple_full = implode('-', $unis_all);


		#
		# variation?
		#

		if ($simple_basic !== $simple_full){
			$vars[$simple_basic] = array($simple_full);
		}else{
			if (count($old_vars[$simple_basic])){
				$vars[$simple_basic] = $old_vars[$simple_basic];
			}
		}


		#
		# name
		#

		sort($row['aliases']);

		$names[$simple_basic] = $row['aliases'];
	}

	sort($names['_']);
	ksort($names);

	ksort($vars);

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



	echo "Writing names map ....... ";
	$fh = fopen('catalog_names.php', 'w');
	fwrite($fh, '<'.'?php $catalog_names = ');
	fwrite($fh, var_export($names, true));
	fwrite($fh, ";\n");
	fclose($fh);
	echo "DONE\n";


	echo "Writing variations map ....... ";
	$fh = fopen('catalog_vars.php', 'w');
	fwrite($fh, '<'.'?php $catalog_vars = ');	
	fwrite($fh, var_export($vars, true));
	fwrite($fh, ";\n");
	fclose($fh);
	echo "DONE\n";
