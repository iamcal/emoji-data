<?php
	# find all input images
	$files1 = glob(dirname(__FILE__).'/../gemoji/images/emoji/*.png');
	$files2 = glob(dirname(__FILE__).'/../gemoji/images/emoji/unicode/*.png');


	echo "Calculating checksums ... ";
	$map = array();
	foreach ($files2 as $f) {
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
				if (strpos($map_file, '-fe0f')) continue;
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


	echo "Building variations ..... ";
	$flat = array();
	foreach ($map as $sum => $files){
		foreach ($files as $file){
			if (strpos($file, '-fe0f')) continue;
			if (isset($flat[$sum])) continue;
			$flat[$sum] = $file;
		}
	}

	$vars_map = array();
	foreach ($map as $sum => $files){
		foreach ($files as $file){
			if (strpos($file, '-fe0f')){
				$plain =  pathinfo($flat[$sum], PATHINFO_FILENAME);
				$vari =  pathinfo($file, PATHINFO_FILENAME);

				$vars_map[$plain][] = $vari;
			}
		}
	}
	$fh = fopen('catalog_vars.php', 'w');
	fwrite($fh, '<'.'?php $catalog_vars = ');	
	fwrite($fh, var_export($vars_map, true));
	fwrite($fh, ";\n");
	fclose($fh);
	echo "DONE\n";
