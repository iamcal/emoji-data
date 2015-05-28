<?php
	$map = array(
		'swatch-type-1-2.png'	=> '1f3fb.png',
		'swatch-type-3.png'	=> '1f3fc.png',
		'swatch-type-4.png'	=> '1f3fd.png',
		'swatch-type-5.png'	=> '1f3fe.png',
		'swatch-type-6.png'	=> '1f3ff.png',		
	);

	foreach ($map as $src => $v){

		$dst = '../../img-apple-64/'.$v;

		exec("convert {$src} -resize 64x64 png32:{$dst}", $out, $code);

		if ($code){
			echo "ERROR:\n";
			echo "   ".$out."\n";
		}else{
			echo ".";
		}
	}

	echo "All done\n";
