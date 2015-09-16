<?php
	$list = glob('../../img-apple-160/*_UNKNOWN.png');

	$indexes = array();

	foreach ($list as $name){
		$parts = explode('/', $name);
		list($idx) = explode('_', array_pop($parts));
		$indexes[] = $idx;
	}


	$chunks = array_chunk($indexes, 10);

	echo "<table border=1>";
	foreach ($chunks as $chunk){
		echo "<tr>";
		foreach ($chunk as $idx){
			echo "<td><img src=\"../../img-apple-160/{$idx}_UNKNOWN.png\" width=\"100\"><br>{$idx}</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
