<?php
	$src = "emojione-assets/png/64";
	$dst = "../../img-emojione-64";

  $emojione_map_file = file_get_contents(__DIR__.'/emojione-assets/emoji.json');
  $emojione_map = json_decode($emojione_map_file, true);

	shell_exec("rm -f ../../img-emojione-64/*.png");

	$files = glob("{$src}/*.png");
	foreach ($files as $file){
    $original_name = basename($file, '.png');
		$new_name = $emojione_map[$original_name]['code_points']['output'];
		
		$target = StrToLower($new_name.'.png');

		$dst_file = $dst.'/'.$target;
		copy($file, $dst_file);
		echo '.';
	}
	echo "\ndone\n";
