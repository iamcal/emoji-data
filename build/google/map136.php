<?php
	$src = "noto-emoji/build/compressed_pngs";
	$dst = "../../img-google-136";

	# remove existing
	shell_exec("rm -f $dst/*.png");

	# load catalog
	$json = file_get_contents('../../emoji.json');
	$emoji = json_decode($json, true);

	foreach ($emoji as $row){

		$out = $row['image'];
		$in = 'emoji_u'.str_replace('-', '_', $row['image']);

		if (!file_exists("$src/$in")){
			if ($row['non_qualified']){
				$in = 'emoji_u'.str_replace('-', '_', StrToLower($row['non_qualified'])).'.png';

				if (!file_exists("$src/$in")){
					#echo "X: $in\n";
				}
			}else{
				#echo "X: $in\n";
			}
		}

		if (!file_exists("$src/$in")){
			echo 'X';
		}else{
			copy("$src/$in", "$dst/$out");
			echo '.';
		}
	}
