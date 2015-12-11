<?php
	# google images are too big to composite at speed, so we'll make 64px
	# versions from the 136px source. they are also of mixed size and aspect
	# ratio, so this helps normalize them.

	echo shell_exec("rm -f ../../img-google-64/*.png");

	$files = glob("../../img-google-136/*.png");

	foreach ($files as $src){

		$bits = explode('/', $src);
		$dst = '../../img-google-64/'.array_pop($bits);

		exec("convert {$src} -resize 64x64 -gravity center -background transparent -extent 64x64 png32:{$dst}", $out, $code);

		if ($code){
			echo "ERROR:\n";
			echo "   ".$out."\n";
		}else{
			echo ".";
		}
	}

	echo "All done\n";
