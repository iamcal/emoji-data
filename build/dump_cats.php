<?php
	$out = array();

	$data = json_decode(file_get_contents('../emoji.json'), true);
	foreach ($data as $row){
		if ($row['category']){
			$out[] = array($row['category'], $row['sort_order'], $row['short_name']);
		}
	}

	usort($out, function($a, $b){
		if ($a[0] != $b[0]) return strcmp($a[0], $b[0]);
		if ($a[1] != $b[1]) return $a[1] - $b[1];
	});

	foreach ($out as $r){
		echo implode(' / ', $r)."\n";
	}

