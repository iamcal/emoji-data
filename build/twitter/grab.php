<?php
	$json = file_get_contents('../../emoji.json');
	$data = json_decode($json, true);

	foreach ($data as $row){
		if (!strlen($row['image'])) continue;

		$url_img = $row['image'];
		if (substr($url_img, 0, 2) == '00') $url_img = substr($url_img, 2, 2) . '-20e3.png';

		$path = "../../img-twitter-72/{$row['image']}";
		$url = "http://abs.twimg.com/emoji/v1/72x72/{$url_img}";

		#echo "{$row['image']} - $path - $url\n";
		#continue;

		if (file_exists($path) && filesize($path)) continue;

		$out = array();
		$ret = 0;
		exec("wget -qO {$path} \"{$url}\"", $out, $ret);

		if ($ret){
			echo "X[{$row['image']}]";
			#print_r($out);

			if (file_exists($path) && !filesize($path)) unlink($path);
		}else{
			echo ".";
		}
	}
