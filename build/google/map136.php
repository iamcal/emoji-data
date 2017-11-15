<?php
	$src = "noto-emoji/build/compressed_pngs";
	$dst = "../../img-google-136";

	# remove existing
	shell_exec("rm -f $dst/*.png");

	# load catalog
	$json = file_get_contents('../../emoji.json');
	$emoji = json_decode($json, true);

	foreach ($emoji as $row){
		try_fetch($row);
		if (isset($row['skin_variations'])){
			foreach ($row['skin_variations'] as $row2){
				try_fetch($row2);
			}
		}
	}

	function try_fetch($row){

		global $src, $dst;

		$out = $row['image'];
		$in = array();

		$in[] = 'emoji_u'.str_replace('-', '_', $row['image']);

		if (isset($row['non_qualified']) && $row['non_qualified']){
			$in[] = 'emoji_u'.str_replace('-', '_', StrToLower($row['non_qualified'])).'.png';
		}else{
			if (strpos($row['image'], '-fe0f')){
				$in[] = 'emoji_u'.str_replace('-', '_', str_replace('-fe0f', '', $row['image']));
			}
		}

		foreach ($in as $inx){
			if (file_exists("$src/$inx")){
				copy("$src/$inx", "$dst/$out");
				echo '.';
				return;
			}
		}

		echo 'X';
	}
