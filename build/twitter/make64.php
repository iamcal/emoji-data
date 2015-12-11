<?php
	$files = glob("../../img-twitter-72/*.png");

	shell_exec("rm -f ../../img-twitter-64/*.png");

	foreach ($files as $src){

		$bits = explode('/', $src);
		$dst = '../../img-twitter-64/'.array_pop($bits);

		exec("convert {$src} -resize 64x64 png32:{$dst}", $out, $code);

		if ($code){
			echo "ERROR:\n";
			echo "   ".$out."\n";
		}else{
			echo ".";
		}
	}

	echo "All done\n";

