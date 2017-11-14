<?php
	error_reporting((E_ALL | E_STRICT) ^ E_NOTICE);

	#
	# show a list of missing codepoint images by provider
	#

	$providers = array(
		'apple',
	);

	$json = file_get_contents('../emoji.json');
	$obj = json_decode($json, true);

	foreach ($providers as $p){
	foreach ($obj as $row){

		if (!$row["has_img_$p"]){
			echo "$p missing {$row['unified']} / {$row['short_name']}\n";
		}

	}
	}

