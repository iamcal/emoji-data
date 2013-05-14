<?php
	$dir = dirname(__FILE__).'/..';


	# get sheet size
	$ident_out = shell_exec("/usr/bin/identify -format \"%w|%h|%m\" {$dir}/sheet_64.png 2>&1");
	$ident_lines = explode("\n", trim($ident_out));
	if (!count($ident_lines)) die("cant ident master sheet");
	list($w, $h, $format) = explode('|', $ident_lines[count($ident_lines)-1]);

	$iw = $w / 64;
	$ih = $h / 64;

	build_sheet(32);
	build_sheet(20);
	build_sheet(16);

	function build_sheet($size){

		global $iw, $ih, $dir;

		$pw = $iw * $size;
		$ph = $ih * $size;

		echo "Sheet $size : ";

		exec("convert {$dir}/sheet_64.png -resize {$pw}x{$ph} png32:{$dir}/sheet_{$size}.png", $out, $code);

		if ($code){
			echo "ERROR:\n";
			echo "   ".$out."\n";
		}else{
			echo "OK\n";
		}
	}
