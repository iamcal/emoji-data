<?php
	ob_start();
	include(__DIR__.'/find_missing.php');
	$lines = explode("\n", trim(ob_get_contents()));
	ob_end_clean();

	$counts = [];

	foreach ($lines as $line){

		list($a) = explode(' ', $line);
		$counts[$a]++;
	}

	print_r($counts);
