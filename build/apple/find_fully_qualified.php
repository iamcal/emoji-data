<?php
	#
	# this script finds all emoji in the catalog that have a fully qualified
	# version, then outputs them in such a way that extract.pl can pull the images out.
	#


	$json = file_get_contents('../../emoji.json');
	$obj = json_decode($json, true);

	foreach ($obj as $row){

		if ($row['fully_qualified']){
			echo "{$row['unified']} {$row['fully_qualified']}\n";
		}
	}
