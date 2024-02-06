<?php
	ob_start();
	include(__DIR__.'/find_missing.php');
	$lines = explode("\n", trim(ob_get_contents()));
	ob_end_clean();

	$counts = [];
	$counts_v = [];

	foreach ($lines as $line){

		$v = '?';
		if (preg_match('!\[(.*?)\]$!', $line, $m)){
			$v = $m[1];
		}

		list($a) = explode(' ', $line);
		$counts[$a]++;

		$counts_v["{$a}-{$v}"]++;
	}

	print_r($counts);
	print_r($counts_v);
