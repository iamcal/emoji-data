<?php
	$in = file_get_contents('../emoji.json');
	$catalog = json_decode($in, true);

	function format_codepoints($raw){

		if (!$raw) return '-';

		$out = array();

		foreach (explode('-', $raw) as $u){
			$out[] = "U+$u";
		}

		return implode(' ', $out);
	}

	function unicode_bytes($uni){

		$out = '';

		$cps = explode('-', $uni);
		foreach ($cps as $cp){
			$out .= emoji_utf8_bytes(hexdec($cp));
		}

		return $out;
	}

	function emoji_utf8_bytes($cp){

		if ($cp > 0x10000){
			# 4 bytes
			return	chr(0xF0 | (($cp & 0x1C0000) >> 18)).
				chr(0x80 | (($cp & 0x3F000) >> 12)).
				chr(0x80 | (($cp & 0xFC0) >> 6)).
				chr(0x80 | ($cp & 0x3F));
		}else if ($cp > 0x800){
			# 3 bytes
			return	chr(0xE0 | (($cp & 0xF000) >> 12)).
				chr(0x80 | (($cp & 0xFC0) >> 6)).
				chr(0x80 | ($cp & 0x3F));
		}else if ($cp > 0x80){
			# 2 bytes
			return	chr(0xC0 | (($cp & 0x7C0) >> 6)).
				chr(0x80 | ($cp & 0x3F));
		}else{
			# 1 byte
			return chr($cp);
		}
	}

?>
<html>
<head>
<meta charset="UTF-8" />
<title>Emoji Catalog</title>
<link rel="stylesheet" type="text/css" media="all" href="emoji.css" />
<style type="text/css">

body {
    font-size: 12px;
    font-family: Helvetica Neue, Helvetica, Arial, sans-serif;
}

table {
    border-radius: 0.41em;
    border: 1px solid #999;
    font-size: 12px;
}

table td {
    padding-left: 0.41em;
    padding-right: 0.41em;
}

table th {
    font-weight: bold;
    text-align: left;
    background: #BBB;
    color: #333;
    font-size: 14px;
    padding: 0.41em;
}

table tbody tr:nth-child(even) {
    background: #dedede;
}

table tbody td {
    padding: 0.41em;
}

</style>
</head>
<body>

<h1>Emoji Catalog</h1>

<table cellspacing="0" cellpadding="0">
	<tr>
		<th colspan="5">Images</th>
		<th>Official Name</th>
		<th>Short Name</th>
		<th>Code Point(s)</th>
		<th>Image Path</th>
		<th>Sheet</th>
	</tr>
	<tbody>

<?php
	function image_cell($has, $url){
		if ($has){
			echo "\t\t<td><img src=\"{$url}\" width=\"20\" height=\"20\" /></td>\n";
		}else{
			echo "\t\t<td style=\"text-align: center\">-</td>\n";
		}
	}

	foreach ($catalog as $row){

		echo "\t<tr>\n";
		image_cell($row['has_img_apple'    ], "img-apple-64/{$row['image']}");
		image_cell($row['has_img_google'   ], "img-google-64/{$row['image']}");
		image_cell($row['has_img_twitter'  ], "img-twitter-64/{$row['image']}");
		image_cell($row['has_img_facebook' ], "img-facebook-64/{$row['image']}");
		echo "\t\t<td>".unicode_bytes($row['unified'])."</td>\n";
		echo "\t\t<td>".HtmlSpecialChars(mb_strtolower($row['name'], 'UTF-8'))."</td>\n";
		echo "\t\t<td>:{$row['short_name']}:</td>\n";
		echo "\t\t<td>".format_codepoints($row['unified'])."</td>\n";
		echo "\t\t<td>{$row['image']}</td>\n";
		echo "\t\t<td>{$row['sheet_x']},{$row['sheet_y']}</td>\n";
		echo "\t</tr>\n";
	}
?>
	</tbody>
</table>

</body>
<html>
