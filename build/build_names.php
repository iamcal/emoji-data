<?php
	# find all input images
	$files1 = glob(dirname(__FILE__).'/../gemoji/images/emoji/*.png');
	$files2 = glob(dirname(__FILE__).'/../gemoji/images/emoji/unicode/*.png');


	echo "Calculating checksums ... ";
	$map = array();
	foreach ($files2 as $f) {
		if (strpos($f, '-fe0f')) continue;
		$sum = md5_file($f);
		if (!isset($map[$sum])) {
			$map[$sum] = array();
		}
		$map[$sum][] = $f;
	}
	echo "DONE\n";


	echo "Matching up ............. ";
	$out = array();
	foreach ($files1 as $f){

		$sum = md5_file($f);
		$n_text =  pathinfo($f, PATHINFO_FILENAME);

		if ($map[$sum]){
			foreach ($map[$sum] as $map_file) {
				$n_code = pathinfo($map_file, PATHINFO_FILENAME);
				#echo "$n_text -> $n_code\n";
				$out[$n_code][] = $n_text;
			}
		}else{
			#echo "$n_text -> ???????????????????????????\n";
			$out['_'][] = $n_text;
		}
	}
	echo "DONE\n";

	echo "Writing names map ....... ";
	$fh = fopen('catalog_names.php', 'w');
	fwrite($fh, '<'.'?php $catalog_names = ');
	fwrite($fh, var_export($out, true));
	fwrite($fh, ";\n");
	fclose($fh);
	echo "DONE\n";
