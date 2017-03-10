<?php
	$dir = dirname(__FILE__).'/..';

	$sets = array('apple', 'twitter', 'google', 'emojione', 'facebook', 'messenger');
	$sizes = array(16, 20, 32);

	foreach ($sets as $set) {
		foreach ($sizes as $size) {
			build_sheet($set, $size);
		}
	}

	function build_sheet($type, $size){

		$src_size = 64;

		global $dir;

		$src = "sheet_{$type}_{$src_size}.png";
		$dst = "sheet_{$type}_{$size}.png";

		# get sheet size
		$ident_out = shell_exec("identify -format \"%w|%h|%m\" {$dir}/{$src} 2>&1");
		$ident_lines = explode("\n", trim($ident_out));
		if (!count($ident_lines)) die("cant ident master sheet");
		list($w, $h, $format) = explode('|', $ident_lines[count($ident_lines)-1]);

		$iw = $w / $src_size;
		$ih = $h / $src_size;

		$pw = $iw * $size;
		$ph = $ih * $size;

		echo "Sheet $src -> $dst : ";

		exec("convert {$dir}/{$src} -resize {$pw}x{$ph} png32:{$dir}/{$dst}", $out, $code);

		if ($code){
			echo "ERROR:\n";
			echo "   ".$out."\n";
		}else{
			echo "OK\n";
		}
	}
