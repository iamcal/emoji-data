<?
	include('catalog.php');


	$out = array();

	foreach ($catalog as $row){

		$hex = '';
		foreach ($row['unicode'] as $cp) $hex .= sprintf('%x', $cp);

		$html = "<span class=\"emoji emoji$hex\"></span>";


		$out[] = array(
			'name'		=> $row['char_name']['title'],

			'unified'	=> $row['unicode'],
			'docomo'	=> $row['docomo']['unicode'],
			'kddi'		=> $row['au']['unicode'],
			'softbank'	=> $row['softbank']['unicode'],
			'google'	=> $row['google']['unicode'],

			'html'		=> $html,
		);
	}

	function format_codepoints($us){

		if (!count($us)) return '-';

		$out = array();

		foreach ($us as $u){
			$out[] = 'U+'.sprintf('%04X', $u);
		}

		return implode(' ', $out);
	}

?>
<html>
<head>

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
		<th colspan="2">Name</th>
		<th>Unified</th>
		<th>DoCoMo</th>
		<th>KDDI</th>
		<th>Softbank</th>
		<th>Google</th>
	</tr>
	<tbody>

<?
	foreach ($out as $row){

		echo "\t<tr>\n";
		echo "\t\t<td>$row[html]</td>\n";
		echo "\t\t<td>".HtmlSpecialChars(StrToLower($row['name']))."</td>\n";
		echo "\t\t<td>".format_codepoints($row['unified'])."</td>\n";
		echo "\t\t<td>".format_codepoints($row['docomo'])."</td>\n";
		echo "\t\t<td>".format_codepoints($row['kddi'])."</td>\n";
		echo "\t\t<td>".format_codepoints($row['softbank'])."</td>\n";
		echo "\t\t<td>".format_codepoints($row['google'])."</td>\n";
		echo "\t</tr>\n";
	}
?>
	</tbody>
</table>

</body>
<html>
