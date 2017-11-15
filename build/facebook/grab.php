<?php

$json = file_get_contents('../../emoji.json');
$data = json_decode($json, true);

# comment these out to do incremental download
#shell_exec("rm -f ../../img-messenger-128/*.png");
#shell_exec("rm -f ../../img-facebook-96/*.png");


foreach ($data as $row){
	if (strlen($row['image'])){
		fetch($row);
	}

	if (isset($row['skin_variations'])){
		foreach ($row['skin_variations'] as $row2){
			fetch($row2);
		}
	}
}

echo "\n";

function fetch($row){
	$img = $row['image'];

	$alt_img = null;
	if (isset($row['non_qualified']) && $row['non_qualified']){
		$alt_img = StrToLower($row['non_qualified']).'.png';
	}

	fetch_single($img, $alt_img, 'MESSENGER', 'img-messenger-128', 128, 1);
	fetch_single($img, $alt_img, 'FBEMOJI', 'img-facebook-96', 32, 3);
}


function fetch_single($img, $alt_img, $type_key, $dir, $size, $ratio){
	/* based upon Twitter scraper and Javascript from Facebook see Github issue #56 */

	# files get stored here
	$path = "../../{$dir}/{$img}";

	# use this for just fetching missing images
	if (file_exists($path)) return;

	# Emoji Config
	$supportedSizes = [16, 18, 20, 24, 28, 30, 32, 64, 128];
	$types = array('FBEMOJI' => 'f', 'FB_EMOJI_EXTENDED' => 'e', 'MESSENGER' => 'z', 'UNICODE' => 'u');

	$type = $types[$type_key];


	# emoji.img = acbd-1234.png
	# facebook  = abcd_1234.png

	$url = build_url($type, $size, $ratio, str_replace('-', '_', $img));

	if (try_fetch($url, $path)){
		echo '.';
		return;
	}

	if ($alt_img){

		$url = build_url($type, $size, $ratio, str_replace('-', '_', $alt_img));

		if (try_fetch($url, $path)){
			echo '.';
			return;
		}
	}

	#echo "X[{$img}]";
	echo 'X';
	#sleep(1);
}


function build_url($type, $size, $pixelRatio, $img){

	$schemaAuth = "https://www.facebook.com/images/emoji.php/v7";

	$path = $pixelRatio . '/' . $size . '/' . $img;
	$check = checksum($path);
	$url = $schemaAuth . '/' . $type . $check . '/' . $path;

	return $url;
}

function try_fetch($url, $path){

	http_fetch($url, $path);

	if (!file_exists($path)){
		return false;
	}

	if (!filesize($path)){
		@unlink($path);
		return false;
	}

	# PNG signature?
	$fp = fopen($path, 'r');
	$sig = fread($fp, 4);
	fclose($fp);

	if ($sig != "\x89PNG"){
		#print_r($sig);
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


function http_fetch($url, $filename){

	$fh = fopen($filename, 'w');

	$options = array(
		CURLOPT_FILE	=> $fh,
		CURLOPT_TIMEOUT	=> 60,
		CURLOPT_URL	=> $url,
	);

	$options[CURLOPT_HTTPHEADER] = array(
		'Referer: https://www.facebook.com/',
		'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
	);

	$ch = curl_init();
	curl_setopt_array($ch, $options);
	curl_exec($ch);
	$ret = curl_getinfo($ch);
	curl_close($ch);

	fclose($fh);

	# show http error code?
	#echo "({$ret['http_code']})";

	#print_r($ret);
	#exit;
}


function http_fetch_old($url, $filename){

	$out = array();
	$ret = 0;
	exec("wget -qO {$filename} \"{$url}\"", $out, $ret);

	# error from wget?
	if ($ret){
		#print_r($out);
		@unlink($path);
		return false;
	}

	return true;
}
