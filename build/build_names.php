<?php
	# find all input images
	$files1 = glob(dirname(__FILE__).'/../gemoji/images/emoji/*.png');
	$files2 = glob(dirname(__FILE__).'/../gemoji/images/emoji/unicode/*.png');


	echo "Calculating checksums ... ";
	$map1 = array();
	$map2 = array();
	foreach ($files1 as $f) $map1[md5_file($f)] = $f;
	foreach ($files2 as $f) $map2[md5_file($f)] = $f;
	echo "DONE\n";

	echo "Matching up ............. ";
	$out = array();
	foreach ($map1 as $k => $v){

		if ($map2[$k]){
			$n1 = pathinfo($map1[$k], PATHINFO_FILENAME);
			$n2 = pathinfo($map2[$k], PATHINFO_FILENAME);
			$out[] = array($n2, $n1);
			unset($map2[$k]);
		}else{
			$n1 = pathinfo($map1[$k], PATHINFO_FILENAME);
			$out[] = array(null, $n1);
		}
	}
	foreach ($map2 as $v) $out[] = array(pathinfo($v, PATHINFO_FILENAME), null);
	echo "DONE\n";

	echo "Writing names map ....... ";
	$fh = fopen('catalog_names.php', 'w');
	fwrite($fh, '<'.'?php $catalog_names = ');
	fwrite($fh, var_export($map, true));
	fwrite($fh, ";\n");
	fclose($fh);
	echo "DONE\n";
