<?php
	$src = "../../emojione/assets/png";
	$dst = "../../img-emojione-64";

	shell_exec("rm -f ../../img-emojione-64/*.png");

	$files = glob("{$src}/*.png");
	foreach ($files as $file){
		$base = basename($file);
		$target = StrToLower($base);

		if (preg_match('!^(1f468|1f469)-!', $target)){
			$parts = explode('-', $target);
			if (count($parts) > 2){
				$target = implode('-200d-', $parts);
			}
		}

		$dst_file = $dst.'/'.$target;
		copy($file, $dst_file);
		echo '.';
	}
	echo "\ndone\n";
