<?php
	error_reporting((E_ALL | E_STRICT) ^ E_NOTICE);

	#
	# load master catalog
	#

	$in = file_get_contents(__DIR__.'/../emoji.json');
	$catalog = json_decode($in, true);


	#
	# figure out image extent
	#

	$max = 0;

	foreach ($catalog as $row){

		$max = max($max, $row['sheet_x']);
		$max = max($max, $row['sheet_y']);

		if (isset($row['skin_variations'])){
			foreach ($row['skin_variations'] as $row2){

				$max = max($max, $row2['sheet_x']);
				$max = max($max, $row2['sheet_y']);
			}
		}
	}

	$size = $max + 1;


	#
	# bake sheets
	#

	foreach (array(false, true) as $clean){
		foreach (array(64, 32, 20, 16) as $sz){
			create_sheet('apple', $sz, $clean);
			create_sheet('twitter', $sz, $clean);
			create_sheet('google', $sz, $clean);
			create_sheet('facebook', $sz, $clean);
		}
	}


	function create_sheet($type, $img_w, $clean){

		#$img_w = 64;
		$space = 1;

		$mode = $clean ? 'clean' : 'simple';

		echo "Creating $type/$img_w/$mode : ";

		global $catalog, $size;


		#
		# we always want to include the actual type image, but have a fallback list if
		# those are missing
		#

		if ($clean){
			$try_order = array($type);
		}else{
			$try_order = array($type, 'apple', 'google', 'twitter', 'facebook');
		}


		#
		# first, build out the compositing list
		#

		$comp = array_fill(0, $size*$size, null);

		$image_map = array();
		foreach ($catalog as $row){
			$image_map[$row['unified']] = $row;
			if (is_array($row['skin_variations'] ?? null)){
				foreach ($row['skin_variations'] as $row2){
					$image_map[$row2['unified']] = $row2;
				}
			}
		}

		foreach ($catalog as $row){

			$index = $size*$row['sheet_y'] + $row['sheet_x'];

			#
			# do we have the image in this set?
			#

			$comp[$index] = find_image($try_order, $row, $image_map);


			#
			# skin variations?
			#

			if (isset($row['skin_variations'])){
				foreach ($row['skin_variations'] as $row2){

					$index = $size*$row2['sheet_y'] + $row2['sheet_x'];

					$comp[$index] = find_image($try_order, $row2, $image_map);
				}
			}

		}

		$geom = escapeshellarg("{$img_w}x{$img_w}+{$space}+{$space}");
		$tile = escapeshellarg("{$size}x{$size}");

		if ($clean){
			$dst = escapeshellarg("sheets-clean/sheet_{$type}_{$img_w}_clean.png");
		}else{
			$dst = escapeshellarg("sheet_{$type}_{$img_w}.png");
		}

		# Build the montage input (list of filenames)
		$files = '';
		$file_list = [];
		foreach ($comp as $index => $file){
			if ($file !== null){
				$files = $files." {$file}";
				$file_list[] = $file;
				#echo '.';
			}else{
				$files = $files." null:";
				$file_list[] = "null:";
				#echo ' ';
			}

			if ($index % $size == $size - 1){
				#echo "\n";
			}
		}

		$fh = fopen(__DIR__.'/../input.txt', 'w');
		foreach ($file_list as $file){
			fputs($fh, "$file\n");
		}
		fclose($fh);

		$cmd = "montage @input.txt -geometry {$geom} -tile {$tile} -background none png32:{$dst}";
		$fd_spec = array();
		$pipes = array();
		$cwd = __DIR__.'/..'; # chdir into parent directory first

		$res = proc_open($cmd, $fd_spec, $pipes, $cwd);

		$code = proc_close($res);

		unlink(__DIR__.'/../input.txt');

		if ($code > 0) {
			echo "Something went wrong\n\n";
			exit;
			return;
		}

		echo "DONE\n";
	}


	function find_image($try_order, $row, $map){

		#
		# try our priority list
		#

		foreach ($try_order as $try_type){

			if ($try_type != $try_order[0]){
				#echo "Trying $try_type for {$try_order[0]}/{$row['unified']}/{$row['short_name']}\n";
			}

			if ($row["has_img_{$try_type}"]){
				return "img-{$try_type}-64/{$row['image']}";
			}
			if (isset($row['obsoleted_by'])){
				$row2 = $map[$row['obsoleted_by']];
				if ($row2["has_img_{$try_type}"]){

					return "img-{$try_type}-64/{$row2['image']}";
				}
			}
		}


		#
		# it's missing - try the fallback (2753)
		#

		foreach ($try_order as $try_type){

			$path = "img-{$try_type}-64/2753.png";

			if (file_exists("../$path")) return $path;
		}

		return 'MISSING.png';
	}

