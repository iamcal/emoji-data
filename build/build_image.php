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

	create_sheet(64, 'apple');
	#create_sheet(72, 'twitter');
	#create_sheet(64, 'hangouts');
	#create_sheet(64, 'emojione');


	function create_sheet($img_w, $type){

		global $catalog, $max;

		$pw = ($max+1) * $img_w;
		$ph = ($max+1) * $img_w;


		echo "Compositing images ... ";

		if ($type){
			$dst = dirname(__FILE__)."/../sheet_{$type}_{$img_w}.png";
		}else{
			$dst = dirname(__FILE__)."/../sheet_{$img_w}.png";
		}

		echo shell_exec("convert -size {$pw}x{$ph} xc:none {$dst}");

		foreach ($catalog as $row){

			#
			# do we have the image in this set?
			#

			$main_img = null;

			if (!is_null($row["{$type}_img_path"])){

				$main_img = $row["{$type}_img_path"];
				composite($row['sheet_x'], $row['sheet_y'], $img_w, $main_img, $dst);

			}else{

				# we should putr the substitutuion image in here
				# 2753.png
			}


			#
			# skin variations?
			#

			if (count($row['skin_variations'])){
				foreach ($row['skin_variations'] as $row2){

					$vari_img = $row2["{$type}_img_path"];
					if (is_null($vari_img)) $vari_img = $main_img;

					if ($vari_img){

						composite($row2['sheet_x'], $row2['sheet_y'], $img_w, $vari_img, $dst);
					}
				}
			}
		}

		echo " DONE\n";

		echo "Optimizing sheet   ... ";
		echo shell_exec("convert {$dst} png32:{$dst}");
		echo "DONE\n";
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
