<?php
	# load positional data
	include('catalog_positions.php');


	create_alt_sheet(72, 'twitter');
	create_alt_sheet(64, 'hangouts');


	function create_alt_sheet($img_w, $type){

		global $position_data;


		$img_path = "../img-{$type}-{$img_w}/";

		$max_x = 0;
		$max_y = 0;
		foreach ($position_data as $pos){
			$max_x = max($max_x, $pos['x']);
			$max_y = max($max_y, $pos['y']);
		}

		$pw = ($max_x+1) * $img_w;
		$ph = ($max_y+1) * $img_w;



		echo "Compositing images ... ";
		$dst = dirname(__FILE__)."/../sheet_{$type}_{$img_w}.png";

		echo shell_exec("convert -size {$pw}x{$ph} xc:none {$dst}");

		foreach ($position_data as $k => $pos){

			$src = "{$img_path}{$k}.png";
			if (!file_exists($src)){
				# placeholder
				$src = "{$img_path}2753.png";
			}

			$px = $pos['x'] * $img_w;
			$py = $pos['y'] * $img_w;

			echo shell_exec("composite -geometry +{$px}+{$py} {$src} {$dst} {$dst}");
			echo '.';
		}

		echo " DONE\n";

		echo "Optimizing sheet   ... ";
		echo shell_exec("convert {$dst} png32:{$dst}");
		echo "DONE\n";
	}
