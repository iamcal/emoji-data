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

		$url_img = $img;
		if (substr($url_img, 0, 2) == '00') $url_img = substr($url_img, 2, 2) . '-20e3.png';

		$path = "../../img-twitter-72/{$img}";
		$url = "http://abs.twimg.com/emoji/v2/72x72/{$url_img}";

		#echo "{$img} - $path - $url\n";
		#continue;

		$out = array();
		$ret = 0;
		exec("wget -qO {$path} \"{$url}\"", $out, $ret);

		if ($ret){
			echo "X[{$img}]";
			#print_r($out);

			if (file_exists($path) && !filesize($path)) unlink($path);
		}else{
			echo ".";
		}
	}
