
	$GLOBALS['emoji_maps']['html_to_unified'] = array_flip($GLOBALS['emoji_maps']['unified_to_html']);


	#
	# functions to convert incoming data into the unified format
	#

	function emoji_docomo_to_unified(	$text){ return emoji_convert($text, 'docomo_to_unified'); }
	function emoji_kddi_to_unified(		$text){ return emoji_convert($text, 'kddi_to_unified'); }
	function emoji_softbank_to_unified(	$text){ return emoji_convert($text, 'softbank_to_unified'); }
	function emoji_google_to_unified(	$text){ return emoji_convert($text, 'google_to_unified'); }


	#
	# functions to convert unified data into an outgoing format
	#

	function emoji_unified_to_docomo(	$text){ return emoji_convert($text, 'unified_to_docomo'); }
	function emoji_unified_to_kddi(		$text){ return emoji_convert($text, 'unified_to_kddi'); }
	function emoji_unified_to_softbank(	$text){ return emoji_convert($text, 'unified_to_softbank'); }
	function emoji_unified_to_google(	$text){ return emoji_convert($text, 'unified_to_google'); }
	function emoji_unified_to_html(		$text){ return emoji_convert($text, 'unified_to_html'); }
	function emoji_html_to_unified(		$text){ return emoji_convert($text, 'html_to_unified'); }



	function emoji_convert($text, $map){

		return str_replace(array_keys($GLOBALS['emoji_maps'][$map]), $GLOBALS['emoji_maps'][$map], $text);
	}

	function emoji_get_name($unified_cp){

		return $GLOBALS['emoji_maps']['names'][$unified_cp] ? $GLOBALS['emoji_maps']['names'][$unified_cp] : '?';
	}
