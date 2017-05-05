<?php
	$json = file_get_contents('../../emoji.json');
	$data = json_decode($json, true);

	shell_exec("rm -f ../../img-twitter-72/*.png");

	foreach ($data as $row){
		if (strlen($row['image'])) fetch($row['image']);

		if (isset($row['skin_variations'])){
			foreach ($row['skin_variations'] as $row2){

				fetch($row2['image']);
			}
		}
	}


	function fetch($img){

		$src_img = $img;
		if (substr($src_img, 0, 2) == '00') $src_img = substr($src_img, 2, 2) . '-20e3.png';

		$dst = "../../img-twitter-72/{$img}";
		$src = "twemoji/2/72x72/{$src_img}";

		#echo "$src -> $dst\n";
		#return;

		if (file_exists($src)){
			copy($src, $dst);
			echo '.';
		}else{
			echo "\nNot found: $src\n";
		}
	}
