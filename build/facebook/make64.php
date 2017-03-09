<?php
	$files = glob("../../img-facebook-128/*.png");
	shell_exec("rm -f ../../img-facebook-64/*.png");
	foreach ($files as $src){
		$bits = explode('/', $src);
		$dst = '../../img-facebook-64/'.array_pop($bits);
		exec("convert {$src} -resize 64x64 png32:{$dst}", $out, $code);
		if ($code){
			echo "ERROR:\n";
			echo "   ".$out."\n";
		}else{
			echo ".";
		}
	}
	echo "All done\n";
?>
