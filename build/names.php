<?php
	include('common.php');


	#
	# load a names map
	#

	$names = array();
	load_cldr_names('unicode/common/annotations/en.xml', $names);
	load_cldr_names('unicode/common/annotationsDerived/en.xml', $names);

	function load_cldr_names($source, &$names){

		$doc = DOMDocument::load($source);
		$nodes = $doc->documentElement->getElementsByTagName('annotation');
		foreach ($nodes as $node){

			$type = $node->attributes->getNamedItem("type");

			if ($type && $type->nodeValue == 'tts'){

				$k = $node->attributes->getNamedItem("cp")->nodeValue;
				$v = $node->nodeValue;

				$names[$k] = $v;
			}
		}
	}


	#
	# load all our emoji
	#

	$json = file_get_contents("../emoji.json");
	$obj = json_decode($json, true);

	echo "<table border=1>\n";
	echo "<tr><td>CPs</td><td>short</td><td>alts</td><td>CLDR</td><td>Version</td></tr>\n";

	foreach ($obj as $row){
		$bytes = '';

		$cps = explode('-', $row['unified']);
		foreach ($cps as $cp){
			if ($cp == 'FE0F') continue;
			$bytes .= cp_to_utf8_bytes(hexdec($cp));
		}

		$name = $names[$bytes];

		echo "<tr>";
		echo "<td>{$row['unified']}</td>";
		echo "<td><code>{$row['short_name']}</code></td>";

		$sns = array();
		foreach (array_slice($row['short_names'], 1) as $sn){
			$sns[] = "<code>$sn</code>";
		}
		echo "<td>".implode(', ', $sns)."</td>";
		echo "<td><code>{$name}</code></td>";
		echo "<td><code>{$row['added_in']}</code></td>";
		echo "</tr>\n";
	}

	echo "</table>\n";
