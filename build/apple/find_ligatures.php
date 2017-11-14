<?php
	#
	# this script finds all emoji in the catalog that are ligatures
	# then outputs them in such a way that extract.pl can pull the images out.
	#


	$json = file_get_contents('../../emoji.json');
	$obj = json_decode($json, true);

	foreach ($obj as $row){

		if (strpos($row['unified'], '-') !== false){
			echo "{$row['unified']} :{$row['short_name']}:\n";
		}


		if (isset($row['skin_variations']) && is_array($row['skin_variations'])){
			foreach ($row['skin_variations'] as $k => $var){
				echo "{$var['unified']}  :{$row['short_name']}: (tone $k)\n";
			}
		}
	}
