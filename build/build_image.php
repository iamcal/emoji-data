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
	}


	#
	# bake sheets
	#

	create_sheet(64, null,		$dir.'/../gemoji/images/emoji/unicode/');
	create_sheet(72, 'twitter',	$dir.'/../img-twitter-72/');
	create_sheet(64, 'hangouts',	$dir.'/../img-hangouts-64/');



	function create_sheet($img_w, $type, $img_path){

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

			$src = "{$img_path}{$row['image']}";
			if (!file_exists($src)){
				# placeholder
				$src = "{$img_path}2753.png";
			}

			$px = $row['sheet_x'] * $img_w;
			$py = $row['sheet_y'] * $img_w;

			echo shell_exec("composite -geometry +{$px}+{$py} {$src} {$dst} {$dst}");
			echo '.';
		}

		echo " DONE\n";

		echo "Optimizing sheet   ... ";
		echo shell_exec("convert {$dst} png32:{$dst}");
		echo "DONE\n";
	}
