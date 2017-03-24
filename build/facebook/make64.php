<?php
	thumb_set('img-messenger-128', 'img-messenger-64');
	thumb_set('img-facebook-96', 'img-facebook-64');


	function thumb_set($from, $to){

		$files = glob("../../{$from}/*.png");
		shell_exec("rm -f ../../{$to}/*.png");
		foreach ($files as $src){
			$bits = explode('/', $src);
			$dst = "../../{$to}/".array_pop($bits);
			exec("convert {$src} -resize 64x64 png32:{$dst}", $out, $code);
			if ($code){
				echo "ERROR:\n";
				echo "   ".$out."\n";
			}else{
				echo ".";
			}
		}
		echo "All done\n";
	}
