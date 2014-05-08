<?
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
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>Emoji Catalog</title>
<link rel="stylesheet" type="text/css" media="all" href="emoji.css" />
<style type="text/css">

body {
    font-size: 12px;
    font-family: Arial, Helvetica, sans-serif;
}

table {
    -webkit-border-radius: 0.41em;
    -moz-border-radius: 0.41em;
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
		<th colspan="5">Name</th>
		<th>Short Name</th>
		<th>ASCII</th>
		<th>Unified</th>
		<th>DoCoMo</th>
		<th>KDDI</th>
		<th>Softbank</th>
		<th>Google</th>
		<th>Image</th>
		<th>Sheet</th>
	</tr>
	<tbody>

<?
	foreach ($catalog as $row){

		$gemoji_img = $row['image'];
		if (substr($row['image'], 0, 3) == '002' || (substr($row['image'], 0, 3) == '003')){
			$gemoji_img = substr($row['image'], 0, 4).'-20e3.png';
		}

		echo "\t<tr>\n";
		echo "\t\t<td><img src=\"gemoji/images/emoji/unicode/{$gemoji_img}\" width=\"20\" height=\"20\" /></td>\n";
		echo "\t\t<td><img src=\"img-hangouts-64/{$row['image']}\" width=\"20\" height=\"20\" /></td>\n";
		echo "\t\t<td><img src=\"img-twitter-72/{$row['image']}\" width=\"20\" height=\"20\" /></td>\n";
		echo "\t\t<td>".unicode_bytes($row['unified'])."</td>\n";
		echo "\t\t<td>".HtmlSpecialChars(StrToLower($row['name']))."</td>\n";

		echo "\t\t<td>:{$row['short_name']}:</td>\n";
		echo "\t\t<td>{$row['text']}</td>\n";

		echo "\t\t<td>".format_codepoints($row['unified'])."</td>\n";
		echo "\t\t<td>".format_codepoints($row['docomo'])."</td>\n";
		echo "\t\t<td>".format_codepoints($row['au'])."</td>\n";
		echo "\t\t<td>".format_codepoints($row['softbank'])."</td>\n";
		echo "\t\t<td>".format_codepoints($row['google'])."</td>\n";

		echo "\t\t<td>{$row['image']}</td>\n";
		echo "\t\t<td>{$row['sheet_x']},{$row['sheet_y']}</td>\n";

		echo "\t</tr>\n";
	}
?>
	</tbody>
</table>

</body>
<html>
