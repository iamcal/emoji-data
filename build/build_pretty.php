<?php
	$data = json_decode(file_get_contents('../emoji.json'), true);

	$fh = fopen('../emoji_pretty.json', 'w');
	fwrite($fh, json_encode($data, JSON_PRETTY_PRINT));
	fclose($fh);

