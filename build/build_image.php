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

	foreach (array(64, 32, 20, 16) as $sz){
		create_sheet('apple', $sz);
		create_sheet('twitter', $sz);
		create_sheet('google', $sz);
		create_sheet('emojione', $sz);
		create_sheet('facebook', $sz);
		create_sheet('messenger', $sz);
	}


	function create_sheet($type, $img_w){

		#$img_w = 64;
		$space = 1;

		echo "Creating $type/$img_w : ";

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

		$geom = escapeshellarg("{$img_w}x{$img_w}+{$space}+{$space}");
		$tile = escapeshellarg("{$size}x{$size}");
		$dst = escapeshellarg("sheet_{$type}_{$img_w}.png");
		$cmd = "montage @- -geometry {$geom} -tile {$tile} -background none png32:{$dst}";

		# Read filenames on stdin
		$fd_spec = array(
			0 => array("pipe", "r")
		);

		$pipes = array();

		# chdir into parent directory first
		$cwd = __DIR__.'/..';

		$res = proc_open($cmd, $fd_spec, $pipes, $cwd);

		# Write out each filename
		foreach ($comp as $index => $file){
			if ($file !== null){
				fwrite($pipes[0], "{$file}\n");
				#echo '.';
			}else{
				fwrite($pipes[0], "null:\n");
				#echo ' ';
			}

			if ($index % $size == $size - 1){
				#echo "\n";
			}
		}

		fclose($pipes[0]);

		if (proc_close($res) > 0) {
			echo "Something went wrong\n\n";
			return;
		}

		echo "DONE\n";
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
