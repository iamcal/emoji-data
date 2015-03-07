<?php
	# hangouts images are too big to composite at speed, so we'll make 64px
	# versions from the 128px source

	$files = glob("../../img-hangouts-128/*.png");

	foreach ($files as $src){

		$bits = explode('/', $src);
		$dst = '../../img-hangouts-64/'.array_pop($bits);

		exec("convert {$src} -resize 64x64 png32:{$dst}", $out, $code);

		if ($code){
			echo "ERROR:\n";
			echo "   ".$out."\n";
		}else{
			echo ".";
		}
	}

	echo "All done\n";
