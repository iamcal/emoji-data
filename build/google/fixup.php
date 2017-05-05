<?php
	$dh = opendir('../../img-google-136');
	while (($file = readdir($dh)) !== false){
		$new = str_replace("'-'", '-', $file);
		if ($new !== $file){
			echo "$file -> $new\n";
			rename("../../img-google-136/$file", "../../img-google-136/$new");
		}
	}
	echo "ALL DONE\n";
