<?php
	$src = "../../emojione/assets/png";
	$dst = "../../img-emojione-64";

	$files = glob("{$src}/*.png");
	foreach ($files as $file){
		$base = basename($file);
		$dst_file = $dst.'/'.StrToLower($base);
		copy($file, $dst_file);
		echo '.';
	}
	echo "\ndone\n";
