<?php
	# find all input images
	$gemoji_path = dirname(__FILE__).'/../gemoji/images/emoji/unicode';
	$files = glob("$gemoji_path/*");


	echo "Mapping images     ... ";

	$map= array();

	foreach ($files as $file){
		if (!preg_match('!\.png$!', $file)) continue;
		$last = basename($file);
		$base = pathinfo($last, PATHINFO_FILENAME);
		$map[$base] = array();
	}

	$y = 0;
	$x = 0;
	$num = ceil(sqrt(count($map)));

	foreach ($map as $k => $v){
		$map[$k]['x'] = $x;
		$map[$k]['y'] = $y;
		$y++;
		if ($y == $num){
			$x++;
			$y = 0;
		}
	}

	echo "DONE\n";


	echo "Writing image map  ... ";
	$fh = fopen('catalog_positions.php', 'w');
	fwrite($fh, '<'.'?php $position_data = ');
	fwrite($fh, var_export($map, true));
	fwrite($fh, ";\n");
	fclose($fh);
	echo "DONE\n";
	#exit;


	echo "Compositing images ... ";
	$pw = 64 * $num;
	$ph = 64 * $num;
	$dst = dirname(__FILE__).'/../sheet_64.png';

	echo shell_exec("convert -size {$pw}x{$ph} null: -matte -compose Clear {$dst}");

	foreach ($map as $k => $v){

		$src = "{$gemoji_path}/{$k}.png";
		$px = $v['x']*64;;
		$py = $v['y']*64;

		echo shell_exec("composite -geometry +{$px}+{$py} {$src} {$dst} {$dst}");
		echo '.';
	}
	echo " DONE\n";

	echo "Optimizing sheet   ... ";
	echo shell_exec("convert {$dst} png32:{$dst}");
	echo "DONE\n";
