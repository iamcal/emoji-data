<?php
	include('catalog.php');
	include('catalog_positions.php');

	$out = array();

	foreach ($catalog as $row){

		$img_key = StrToLower(encode_points($row['unicode']));
		$position = $position_data[$img_key];

		if (!is_array($position)){
			echo "No image for $img_key: {$row['char_name']['title']}\n";
			continue;
		}

		$out[] = array(
			'name'		=> $row['char_name']['title'],
			'unified'	=> encode_points($row['unicode']),
			'docomo'	=> encode_points($row['docomo'  ]['unicode']),
			'au'		=> encode_points($row['au'      ]['unicode']),
			'softbank'	=> encode_points($row['softbank']['unicode']),
			'google'	=> encode_points($row['google'  ]['unicode']),
			'image'		=> $img_key.'.png',
			'sheet_x'	=> $position['x'],
			'sheet_y'	=> $position['y'],
			'short_name'	=> 'FAKE',
		);

print_r($out);
exit;
	}

	function encode_points($points){
		$bits = array();
		if (is_array($points)){
			foreach ($points as $p){
				$bits[] = sprintf('%04X', $p);
			}
		}
		return implode('-', $bits);
	}
