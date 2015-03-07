<?php
	$dir = dirname(__FILE__);

	#
	# load master catalog
	#

	$in = file_get_contents('../emoji.json');
	$catalog = json_decode($in, true);


	#
	# figure out image extent
	#

	$max = 0;

	foreach ($catalog as $row){

		$max = max($max, $row['sheet_x']);
		$max = max($max, $row['sheet_y']);

		if (count($row['skin_variations'])){
			foreach ($row['skin_variations'] as $row2){

				$max = max($max, $row2['sheet_x']);
				$max = max($max, $row2['sheet_y']);
			}
		}
	}


	#
	# bake sheets
	#

	#create_sheet('apple');
	#create_sheet('twitter');
	create_sheet('google');
	create_sheet('emojione');


	function create_sheet($type){

		$img_w = 64;

		echo "Creating $type : \n";

		global $catalog, $max;


		#
		# find the replacement glyph for this set
		#

		$replacement = null;
		foreach ($catalog as $row){
			if ($row['unified'] == '2753'){
				$replacement = $row["{$type}_img_path"];
			}
		}


		#
		# first, build out the compositing list
		#

		$comp = array();

		foreach ($catalog as $row){


			#
			# do we have the image in this set?
			#

			$main_img = null;

			if (!is_null($row["{$type}_img_path"])){

				$main_img = $row["{$type}_img_path"];
				$comp[] = array($row['sheet_x'], $row['sheet_y'], $main_img);
			}else{

				# apple is always our first fallback. after that, we'll try emojione
				# (since it has the missing flags), else fall back to the replacement glyph

				if ($row["apple_img_path"]){

					$main_img = $row["apple_img_path"];
					$comp[] = array($row['sheet_x'], $row['sheet_y'], $main_img);

				}elseif ($row["emojione_img_path"]){

					$main_img = $row["emojione_img_path"];
					$comp[] = array($row['sheet_x'], $row['sheet_y'], $main_img);

				}elseif ($replacement){

					# it's missing - try the fallback (2753)
					$main_img = $replacement;
					$comp[] = array($row['sheet_x'], $row['sheet_y'], $main_img);
				}
			}


			#
			# skin variations?
			#

			if (count($row['skin_variations'])){
				foreach ($row['skin_variations'] as $row2){

					$vari_img = $row2["{$type}_img_path"];
					if (is_null($vari_img)) $vari_img = $main_img;

					if ($vari_img){

						$comp[] = array($row2['sheet_x'], $row2['sheet_y'], $vari_img);
					}
				}
			}

		}


		#
		# next, build the strips one by one. we do this instead of doing it all
		# in one go so that we canm load/save the intermediate image much faster.
		#

		$mp = $max + 1;

		$pw = $mp * $img_w;
		$ph = $mp * $img_w;

		for ($i=0; $i<$mp; $i++){
			$ip = $i + 1;
			echo "col $ip/$mp : ";

			$dst = $GLOBALS['dir']."/../sheet_{$type}_{$img_w}_col{$i}.png";

			echo shell_exec("convert -size {$img_w}x{$ph} xc:none {$dst}");

			foreach ($comp as $row){
				if ($row[0] != $i) continue;

				$px = 0;
				$py = $row[1] * $img_w;

				$path = $GLOBALS['dir'].'/../'.$row[2];
				if (file_exists($path)){

					echo shell_exec("composite -geometry +{$px}+{$py} {$path} {$dst} {$dst}");
					echo '.';
				}else{
					echo "(not found: $src)";
				}		
			}

			echo " OK\n";
		}


		#
		# merge the strips
		#

		echo "merging ... ";

		$dst = $GLOBALS['dir']."/../sheet_{$type}_{$img_w}.png";

		echo shell_exec("convert -size {$pw}x{$ph} xc:none {$dst}");

		for ($i=0; $i<$mp; $i++){
			$src = $GLOBALS['dir']."/../sheet_{$type}_{$img_w}_col{$i}.png";

			$px = $i * $img_w;
			$py = 0;

			echo shell_exec("composite -geometry +{$px}+{$py} {$src} {$dst} {$dst}");
			echo '.';

			unlink($src);
		}

		echo " OK\n";

		echo "Optimizing sheet   ... ";
		echo shell_exec("convert {$dst} png32:{$dst}");
		echo "DONE\n\n";
	}

	function composite($sheet_x, $sheet_y, $img_w, $src, $dst){

		$px = $sheet_x * $img_w;
		$py = $sheet_y * $img_w;

		$path = $GLOBALS['dir'].'/../'.$src;
		if (file_exists($path)){

			echo shell_exec("composite -geometry +{$px}+{$py} {$path} {$dst} {$dst}");
			echo '.';
		}else{
			echo "(not found: $src)";
		}
	}
