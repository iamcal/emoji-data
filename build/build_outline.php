<?php
	error_reporting((E_ALL | E_STRICT) ^ E_NOTICE);

	$dir = dirname(__FILE__);

	include('common.php');
	include('sets.php');

	$in = file_get_contents(__DIR__.'/../emoji.json');
	$catalog = json_decode($in, true);

	foreach ($catalog as $row){
		if (!in_array(strtolower($row['name']), $outline_symbols)) {
			continue;
		};

		echo "Generating {$row['image']} for {$row['name']}\n";
		$src_img = str_replace('-outline.png', '.png', $row['image']);
		foreach ($image_sets as $set){
			foreach (array(160, 136, 96, 72, 64) as $sz){
				$src_path = "{$dir}/../img-{$set}-{$sz}/{$src_img}";
				$dst_path = "{$dir}/../img-{$set}-{$sz}/{$row['image']}";
				if (file_exists($src_path)){
					#echo "{$src_path} => {$dst_path}\n";
					generate_outline($src_path, $dst_path, $sz);
				}
			}
		}
	}


	function generate_outline($src_path, $dst_path, $size){

		$thickness = $size >= 96 ? 4 : 3;
		# Color of Signal emoji picker dialog background
		$color = '#f5f5f5';

		$cmd = "magick {$src_path} ".
			"'(' +clone -alpha extract -morphology dilate disk:{$thickness} -background '{$color}' -alpha shape ')' ".
			"-gravity center -compose dst-over -composite -strip -alpha background -quality 95 {$dst_path}";
		$fd_spec = array();
		$pipes = array();
		$cwd = __DIR__.'/..'; # chdir into parent directory first

		$res = proc_open($cmd, $fd_spec, $pipes, $cwd);

		$code = proc_close($res);

		if ($code > 0) {
			echo "Something went wrong\n\n";
			exit;
		}
	}
