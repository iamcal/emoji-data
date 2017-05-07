<?php
	#
	# this script publishes the prepared npm packages
	#

	$dir = __DIR__;

	include('sets.php');

	array_unshift($image_sets, 'base');

	foreach ($image_sets as $set){
		echo "Publishing $set ... ";
		chdir("{$dir}/../packages/{$set}");
		echo shell_exec('npm publish');
		echo "OK\n";
	}

	echo "All done!\n";

