<?php
	$json = file_get_contents('../../emoji.json');
	$data = json_decode($json, true);

	shell_exec("rm -f ../../img-twitter-72/*.png");

	foreach ($data as $row){
		if (strlen($row['image'])){
			if ($row['non_qualified']){
				fetch($row['image'], StrToLower($row['non_qualified']).'.png');
			}else{
				fetch($row['image']);
			}
		}

		if (isset($row['skin_variations'])){
			foreach ($row['skin_variations'] as $row2){

				fetch($row2['image']);
			}
		}
	}


	function fetch($img, $alt_img=null){

		$src_img = $img;
		if (substr($src_img, 0, 2) == '00') $src_img = substr($src_img, 2, 2) . '-20e3.png';

		$dst = "../../img-twitter-72/{$img}";
		$src = "twemoji/2/72x72/{$src_img}";

		#echo "$src -> $dst\n";
		#return;

		if (!file_exists($src) && $alt_img){
			$new_src = "twemoji/2/72x72/{$alt_img}";
			if (file_exists($new_src)) $src = $new_src;
		}

		if (!file_exists($src)){
			echo "\nNot found: $src\n";
			return;
		}

		copy($src, $dst);
		echo '.';
	}
