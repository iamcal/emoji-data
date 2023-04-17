<?php
	# needs to match the relevant lines in unicode/emoji-data.txt
	$version = '15.0 ';


	include('common.php');

	#
	# load the names of all simple characters
	#

	$names_map = array();

	$fh = fopen('unicode/UnicodeData.txt', 'r');
	while (($line = fgets($fh)) !== false){
		list($cp, $name) = explode(';', $line);
		$names_map[$cp] = $name;
	}
	fclose($fh);



	$done = [];

	parse_unicode_specfile('unicode/emoji-data.txt', function($fields, $comment){

		if (strpos($comment, $GLOBALS['version']) === false) return;

                if (strpos($fields[0], '..')){
                        list($a, $b) = explode('..', $fields[0]);
                        $a = hexdec($a);
                        $b = hexdec($b);

                        $cps = array();
                        for ($i=$a; $i<=$b; $i++){
                                $cps[] = $i;
                        }
                }else{
                        $cp = sprintf('%04x', hexdec($fields[0]));
                        $cps = array(hexdec($fields[0]));
                }

		foreach ($cps as $cp){
			$hex_low = sprintf('%04x', $cp);
			$hex_up = StrToUpper($hex_low);

			$line = shell_exec("grep -e ^{$hex_up}\\; unicode/UnicodeData.txt");
			$line = trim($line);
			$bits = explode(';', $line);

			if (isset($GLOBALS['done'][$hex_up])) return;
			$GLOBALS['done'][$hex_up] = 1;

			echo "$hex_up;".format_name($bits[1])."\n";
		}
	});



        parse_unicode_specfile('unicode/emoji-sequences.txt', 'get_sequence_names');
        parse_unicode_specfile('unicode/emoji-zwj-sequences.txt', 'get_sequence_names');

        function get_sequence_names($fields, $comment){

		if (strpos($comment, $GLOBALS['version']) === false) return;

		$cp = trim($fields[0]);
		$name = trim($fields[2]);

		if (strpos($cp, '..') !== false){

			list($lo, $hi) = explode('..', $cp);
			$lo = hexdec($lo);
			$hi = hexdec($hi);

			for ($i=$lo; $i<=$hi; $i++){
				$cp = StrToUpper(dechex($i));
				$name = $GLOBALS['names_map'][$cp];
				got_sequence($cp, $name);
			}

		}else{
			got_sequence($cp, $name);
		}
	}


	function got_sequence($cp, $name){

		$cp = StrToUpper($cp);

                $uni = str_replace(' ', '-', $cp);

		# skip over all but one skin tone - this makes it easier
		# for us to turn the remaining single-case into an expansion
		if (strpos($name, 'medium-light skin tone') !== false) return;
		if (strpos($name, 'medium skin tone') !== false) return;
		if (strpos($name, 'medium-dark skin tone') !== false) return;
		if (strpos($name, 'dark skin tone') !== false) return;

		if (isset($GLOBALS['done'][$uni])) return;
		$GLOBALS['done'][$uni] = 1;

		echo "$uni;".format_name($name)."\n";
        }



	function format_name($str){
		return StrToLower(str_replace(' ', '_', $str));
	}
