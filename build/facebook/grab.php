<?php

$json = file_get_contents('../../emoji.json');
$data = json_decode($json, true);

#shell_exec("rm -f ../../img-messenger-128/*.png");
#shell_exec("rm -f ../../img-facebook-128/*.png");


foreach ($data as $row){
	if (strlen($row['image'])){
		fetch($row['image']);
	}

	if (isset($row['skin_variations'])){
		foreach ($row['skin_variations'] as $row2){
			fetch($row2['image']);
		}
	}
}

function fetch($img){
	fetch_single($img, 'MESSENGER', 'img-messenger-128', 128);
	#fetch_single($img, 'FBEMOJI', 'img-facebook-72', 72);
}


function fetch_single($img, $type_key, $dir, $size){
	/* based upon Twitter scraper and Javascript from Facebook see Github issue #56 */

	# files get stored here
	$path = "../../{$dir}/{$img}";

	# use this for just fetching missing images
	if (file_exists($path)) return;
	
	# Emoji Config
	$supportedSizes = [16, 18, 20, 24, 28, 30, 32, 64, 128];
	$types = array('FBEMOJI' => 'f', 'FB_EMOJI_EXTENDED' => 'e', 'MESSENGER' => 'z', 'UNICODE' => 'u');

	$type = $types[$type_key];

	# try the simple version

	$url = build_url($type, $size, $img);
	
	if (try_fetch($url, $path)){
		echo '.';
		return;
	}


	# now try with underscores

	$url = build_url($type, $size, str_replace('-', '_', $img));

	if (try_fetch($url, $path)){
		echo '.';
		return;
	}

	echo "X[{$img}]";
}


function build_url($type, $size, $img){

	$pixelRatio = 1;
	$schemaAuth = "https://www.facebook.com/images/emoji.php/v7";

	$path = $pixelRatio . '/' . $size . '/' . $img;
	$check = checksum($path);
	$url = $schemaAuth . '/' . $type . $check . '/' . $path;
	
	return $url;
}

function try_fetch($url, $path){

	$out = array();
	$ret = 0;
	exec("wget -qO {$path} \"{$url}\"", $out, $ret);

	# error from wget?
	if ($ret){
		#print_r($out);
		@unlink($path);
		return false;
	}

	# PNG signature?
	$fp = fopen($path, 'r');
	$sig = fread($fp, 4);
	fclose($fp);

	if ($sig != "\x89PNG"){
		#print_r($out);
		@unlink($path);
		return false;
	}

	# ok!
	return true;
}
function encodeURIComponent($str) { /* a standard method in Javascript */
	return $str;
}
function unescape($str) {
	$trans = array('&amp;' => '&', '&lt;' => '<', '&gt;' => '>', '&quot;' => '"', '&#x27;' => "'");
	return strtr($str, $trans);
}
function checksum($subpath) {
	$checksumBase = 317426846;
	$base = $checksumBase;
//	$subpath = unescape(encodeURIComponent($subpath)); 
	for ($pos = 0; $pos < strlen($subpath); $pos++) {
		$base = ($base << 5) - $base + ord(substr($subpath, $pos, 1));
		$base &= 4294967295;
	}
	return base_convert(($base & 255), 10, 16);
}
