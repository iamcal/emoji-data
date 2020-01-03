<?php
	# turn off notices, like the good old days
	error_reporting(E_ERROR | E_WARNING | E_PARSE);


	#
	# first, build a list of every image referenced in the final JSON
	#

	$json = file_get_contents('../emoji.json');
	$obj = json_decode($json, true);

	$files = array();
	foreach ($obj as $row){

		$files[$row['image']]++;

		if (is_array($row['skin_variations'])){
			foreach ($row['skin_variations'] as $var){
				$files[$var['image']]++;
			}
		}
	}


	#
	# now find unused ones
	#

	$found = glob("../img-apple-64/*.png");
	foreach ($found as $test){
		$bits = explode('/', $test);
		$last = array_pop($bits);

		if (!$files[$last]){

			$missing[$last] = 1;
		}
	}


	#
	# find out which prefixes have skin tones
	#

	$skins = array();

	foreach ($missing as $name => $junk){

		if (preg_match('!^(.*)-1f3f[b-f]\.png$!', $name, $m)){

			$skins[$m[1]] = 1;
			unset($missing[$name]);
		}
	}


	#
	# now build output
	#

	echo "<table border=1>\n";
	echo "<tr>";
	echo "<th>Image</th>";
	echo "<th>Code</th>";
	echo "<th>Official Name</th>";
	echo "<th>Skins?</th>";
	echo "</tr>\n";

	foreach ($missing as $fname => $junk){

		list($name, $ext) = explode('.', $fname);
		$uni = StrToUpper($name);

		$line = shell_exec("grep -e ^{$uni}\\; unicode/UnicodeData.txt");
		list($junk, $u_name) = explode(';', $line);

		$skin = $skins[$name] ? 'Yes' : '-';

		echo "<tr>";
		echo "<td><img src=\"img-apple-64/$fname\"></td>";
		echo "<td>U+$uni</td>";
		echo "<td>$u_name</td>";
		echo "<td>$skin</td>";
		echo "</tr>\n";
	}

	echo "</table>\n";
