<?php
	error_reporting((E_ALL | E_STRICT) ^ E_NOTICE);

	include('../common.php');


	#
	# build a codepoint to short-code map
	#

	$json = file_get_contents('../../emoji.json');
	$emoji = json_decode($json, true);

	$map = array();
	foreach ($emoji as $row){
		$map[$row['unified']] = $row['short_name'];
	}



	#
	# load category data
	#

	$json = file_get_contents('emoji_categories.json');
	$obj = json_decode($json, true);

	$cats = array();

	foreach ($obj['EmojiDataArray'] as $cat){

		list($junk, $cat_name) = explode('-', $cat['CVDataTitle']);
		$cats[$cat_name] = array();

		foreach (explode(',', $cat['CVCategoryData']['Data']) as $glyph){

			$hex = StrToUpper(utf8_bytes_to_hex($glyph));

			if ($map[$hex]){
				$cats[$cat_name][] = $map[$hex];
			}else{
				if (preg_match('!-FE0F$!', $hex)){
					$hex = substr($hex, 0, -5);
				}

				if ($map[$hex]){
					$cats[$cat_name][] = $map[$hex];
				}else{
					$cats[$cat_name][] = '#'.$hex;
				}
			}
		}
	}


	#
	# output category list
	#

	$fh = fopen('../data_categories.txt', 'w');

	foreach ($cats as $cat => $rows){
		fwrite($fh, "$cat\n");
		foreach ($rows as $row){
			fwrite($fh, "\t$row\n");
		}
	}

	fclose($fh);

	echo "Map written\n";
