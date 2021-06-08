<?php

show_block("Components", '../../img-apple-160/*_COMPONENT.png');
show_block("Unknowns", '../../img-apple-160/*_UNKNOWN.png');
show_block("Glyphs", '../../img-apple-160/*_GLYPH.png');

function show_block($title, $glob){

	$list = glob($glob);

	$chunks = array_chunk($list, 10);

	echo "<h1>{$title}</title>";

	echo "<table border=1>";
	foreach ($chunks as $chunk){
		echo "<tr>";
		foreach ($chunk as $name){

			$parts = explode('/', $name);
			list($idx) = explode('_', array_pop($parts));

			echo "<td><img src=\"$name\" width=\"100\"><br>{$idx}</td>";
		}
		echo "</tr>";
	}
	echo "</table>";

}
