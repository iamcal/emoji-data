<?php
	# needs to match the relevant lines in unicode/emoji-data.txt
	$version = '11.0 ';


	include('common.php');

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

			echo "$hex_up;".format_name($bits[1])."\n";
		}
	});



        parse_unicode_specfile('unicode/emoji-sequences.txt', 'get_sequence_names');
        parse_unicode_specfile('unicode/emoji-zwj-sequences.txt', 'get_sequence_names');

        function get_sequence_names($fields, $comment){

		if (strpos($comment, $GLOBALS['version']) === false) return;

                $uni = str_replace(' ', '-', trim($fields[0]));
                $name = trim($fields[2]);

		if (preg_match('! skin tone$!', $name)) return;

		echo "$uni;".format_name($name)."\n";
        }



	function format_name($str){
		return StrToLower(str_replace(' ', '_', $str));
	}
