<?php
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

	create_sheet('apple');
	create_sheet('twitter');
	create_sheet('google');
	create_sheet('emojione');
	create_sheet('facebook');
	create_sheet('messenger');


	function create_sheet($type){

		$img_w = 64;

		echo "Creating $type : \n";

		global $catalog, $size;


		#
		# we always want to include the actual type image, but have a fallback list if
		# those are missing
		#

		$try_order = array($type, 'apple', 'emojione', 'google', 'twitter', 'facebook', 'messenger');


		#
		# first, build out the compositing list
		#

		$comp = array_fill(0, $size*$size, null);

		foreach ($catalog as $row){

			$index = $size*$row['sheet_y'] + $row['sheet_x'];

			#
			# do we have the image in this set?
			#

			$comp[$index] = find_image($try_order, $row);


			#
			# skin variations?
			#

			if (isset($row['skin_variations'])){
				foreach ($row['skin_variations'] as $row2){

					$index = $size*$row2['sheet_y'] + $row2['sheet_x'];

					$comp[$index] = find_image($try_order, $row2);
				}
			}

		}

		$list_file = __DIR__."/{$type}.txt";
		$geom = escapeshellarg("{$img_w}x{$img_w}");
		$tile = escapeshellarg("{$size}x{$size}");
		$dst = escapeshellarg("sheet_{$type}_{$img_w}.png");
		$cmd = "montage @{$list_file} -geometry {$geom} -tile {$tile} -background none png32:{$dst}";

		# chdir into parent directory first
		$cwd = __DIR__.'/..';

		# Write out each filename
		$files = "";

		foreach ($comp as $index => $file){
			if ($file !== null){
				$files .= "{$file}\n";
				echo '.';
			}else{
				$files .= "null:\n";
				echo 'x';
			}

			if ($index % $size == $size - 1){
				echo "\n";
			}
		}
		file_put_contents($list_file, $files);

		$pipes = array();
		$res = proc_open($cmd, array(), $pipes, $cwd);

		if (proc_close($res) > 0) {
			echo "Something went wrong\n\n";
			return;
		}

		echo "DONE\n\n";
	}


	function find_image($try_order, $row){

		#
		# try our priority list
		#

		foreach ($try_order as $try_type){

			if ($row["has_img_{$try_type}"]){

				return "img-{$try_type}-64/{$row['image']}";
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
