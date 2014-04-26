<?php
	$dir = dirname(__FILE__).'/..';

	build_sheet(64, null, 32);
	build_sheet(64, null, 20);
	build_sheet(64, null, 16);

	build_sheet(72, 'twitter', 64);
	build_sheet(72, 'twitter', 32);
	build_sheet(72, 'twitter', 20);
	build_sheet(72, 'twitter', 16);

	build_sheet(64, 'hangouts', 32);
	build_sheet(64, 'hangouts', 20);
	build_sheet(64, 'hangouts', 16);

	function build_sheet($src_size, $type, $size){

		global $dir;

		$src = $type ? "sheet_{$type}_{$src_size}.png" : "sheet_{$src_size}.png";
		$dst = $type ? "sheet_{$type}_{$size}.png" : "sheet_{$size}.png";

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
