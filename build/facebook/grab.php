<?php
$json = file_get_contents('../../emoji.json');
$data = json_decode($json, true);
shell_exec("rm -f ../../img-facebook-128/*.png");
foreach ($data as $row){
	if (strlen($row['image'])) fetch($row['image']);
	if (isset($row['skin_variations'])){
		foreach ($row['skin_variations'] as $row2){
			fetch($row2['image']);
		}
	}
}
function fetch($img){
	/* based upon Twitter scraper and Javascript from Facebook see Github issue #56 */
	
	/* Emoji Config */
	$pixelRatio = 1;
	$schemaAuth = "https://www.facebook.com/images/emoji.php/v7";
	$fileExt = ".png";
	$supportedSizes = [16, 18, 20, 24, 28, 30, 32, 64, 128];
	$types = array('FBEMOJI' => 'f', 'FB_EMOJI_EXTENDED' => 'e', 'MESSENGER' => 'z', 'UNICODE' => 'u');
	/** hardcoded defaults: */
	$size = 128; // default = highest available
	$type = $types['FBEMOJI'];
	
	$size = in_array($size, $supportedSizes) ? $size : 128;
	$path = $pixelRatio . '/' . $size . '/' . $img . $fileExt;
	$check = checksum($path);
	$url_img = $schemaAuth . '/' . $type . $check . '/' . $path;
	
	$path = "../../img-facebook-128/{$img}"; /** files get stored here */
	$url = $schemaAuth . $url_img; /** files get retrieved from here */
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
?>
