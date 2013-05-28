<?php
	$data = file_get_contents('gmail.html');

	preg_match_all('!button string="(.*?)"!', $data, $m);

	echo '<'.'?php'."\n";
	echo "";
	echo "\$catalog_hangouts = ";

	var_export($m[1]);

	echo ";\n";
