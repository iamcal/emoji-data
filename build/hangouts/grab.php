<?php
	#
	# build a map of unified and google codes to the image path
	#

	$json = file_get_contents('../../emoji.json');
	$data = json_decode($json, true);

	$map = array();

	foreach ($data as $row){
		if ($row['unified']) $map['U_'.StrToLower($row['unified'])] = $row['image'];
		if ($row['google' ]) $map['G_'.StrToLower($row['google' ])] = $row['image'];
	}


	#
	# now iterate over the known hangouts icons, checking we have a storage path
	# for each one.
	#

	$data = file_get_contents('gmail.html');
	preg_match_all('!button string="(.*?)"!', $data, $m);

	foreach ($m[1] as $code){

		$img = '?';
		if ($map["U_$code"]){
			$img = $map["U_$code"];
		}elseif ($map["G_$code"]){
			$img = $map["G_$code"];
		}else{
			$img = $code.'.png';
		}

		#$url = "https://ssl.gstatic.com/chat/emoji/3/emoji_u{$code}.png";
		$url = "https://mail.google.com/mail/e/{$code}";

		$out = array();
		$ret = 0;
		exec("wget -qO ../../img-hangouts-28/{$img} \"{$url}\"", $out, $ret);

		if ($ret){
			echo 'X';
			print_r($out);
		}else{
			echo ".";
		}
	}
