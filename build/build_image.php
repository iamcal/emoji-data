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
		# find the replacement glyph for this set
		#

		$replacement = null;
		foreach ($catalog as $row){
			if ($row['unified'] == '2753'){
				$replacement = "img-{$type}-64/{$row['image']}";
			}
		}


		#
		# first, build out the compositing list
		#

		$comp = array_fill(0, $size*$size, null);

		foreach ($catalog as $row){

			$index = $size*$row['sheet_y'] + $row['sheet_x'];

			#
			# do we have the image in this set?
			#

			$main_img = null;

			while (1){

				#
				# prefer the image is the set we're building, duh
				#

				if ($row["has_img_{$type}"]){

					$comp[$index] = "img-{$type}-64/{$row['image']}";
					break;
				}


				#
				# apple is always our first fallback. after that, we'll try emojione
				# (since it has the missing flags), else fall back to the replacement glyph
				#

				$try_order = array('apple', 'emojione', 'google', 'twitter');

				foreach ($try_order as $try_type){

					if ($row["has_img_{$try_type}"]){

						$comp[$index] = "img-{$try_type}-64/{$row['image']}";
						break 2;
					}
				}


				#
				# it's missing - try the fallback (2753)
				#

				$comp[$index] = $replacement;
				echo "Unable to find any images for U+{$row['unified']}\n";
				break;
			}


			#
			# skin variations?
			#

			if (isset($row['skin_variations'])){
				foreach ($row['skin_variations'] as $row2){

					$index = $size*$row2['sheet_y'] + $row2['sheet_x'];
					$vari_img = $row2["has_img_{$type}"];

					# uncomment this line if you want each variations position to
					# have the 'main' image inserted. this makes using it slightly
					# easier, at the cost of heavier sheets.
					#if (is_null($vari_img)) $vari_img = $main_img;

					if ($vari_img){

						$comp[$index] = "img-{$type}-64/{$row2['image']}";
					}
				}
			}

		}

		$geom = escapeshellarg("{$img_w}x{$img_w}");
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
				echo '.';
			}else{
				fwrite($pipes[0], "null:\n");
				echo ' ';
			}

			if ($index % $size == $size - 1){
				echo "\n";
			}
		}

		fclose($pipes[0]);

		if (proc_close($res) > 0) {
			echo "Something went wrong\n\n";
			return;
		}

		echo "DONE\n\n";
	}
