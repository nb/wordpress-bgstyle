<?php
/*
Plugin Name: По български
Plugin URI: http://wordpress.org/extend/plugins/bgstyle/
Description: Помага за по-доброто оформление за публикации на български език
Author: Николай Бачийски
Version: 0.04
Author URI: http://nikolay.org/
*/

class bg_style
{
    function bg_style()
    {
		global $wp_filter;
		// go through all filters and add our style-fixer after wptexturize
		foreach ($wp_filter as $tag => $filter) {
			$found_wptxt = 0;
			foreach ($wp_filter[$tag] as $priority => $functions) {
				if (!is_null($functions)) {
					foreach ($functions as $function) {
						if ("wptexturize" == $function['function']) {
							add_filter($tag, array(&$this, 'bgtexturize'), $priority+1);
							$found_wptxt = 1;
							break;
						}
					}
				}
			}
			if ($found_wptxt)
				continue;
		}
		// tell the browsers not to show quotes around <q> tags
		add_filter('wp_head', array(&$this, 'no_quotes_around_q'));
    }

	function no_quotes_around_q() {
?>
	<style type="text/css">
		q:before, q:after { content: '';}
	</style>
<?php
	}


	/*
	 Goes after wptexturize and changes the quotes to match the Bulgarian style
	
	 Most of the code of this function is taken from Kimmo Suominen's 'Finnish Quotes' plugin
	 and can be found here: http://kimmo.suominen.com/sw/finquote/finquote.php
	*/
    function bgtexturize($text) {
		$output = '';
				
		// Capture tags and everything inside them
		$tarr = preg_split("/(<.*>)/Us", $text, -1, PREG_SPLIT_DELIM_CAPTURE);

		$wp_can_has_quotes = ( function_exists('_x') && '&#8220;' != _x('&#8220;', 'opening curly quote') );
	
		// loop stuff
		if ( $wp_can_has_quotes ) {
			$output = $text;
		} else {
			$stop = count($tarr);
			$next = true;
			for ($i = 0; $i < $stop; $i++) {
				$curl = $tarr[$i];
				if (isset($curl{0}) && '<' != $curl{0} && $next) {
					// wptexturize uses the combination: &#8220; some text &#8221;
					// the Bulgarian style quotes are: &#8222; the same text &#8220;
					$curl = str_replace('&#8220;', '&#8222;', $curl);
					$curl = str_replace('&#8221;', '&#8220;', $curl);
				} elseif (strstr($curl, '<code') || strstr($curl, '<pre')
						|| strstr($curl, '<kbd' || strstr($curl, '<style')
						|| strstr($curl, '<script'))) {
					// strstr is fast
					$next = false;
				} else {
					$next = true;
				}
				$output .= $curl;
			}
		}
		$output = str_replace('<q>', '&#8222;<q>', $output);
		$output = str_replace('</q>', '</q>&#8220;', $output);
		$output = str_replace(' - ', '&ndash;', $output);
		// ndash is more typrographically correct in Bulgarian
		$output = str_replace('&mdash;', '&ndash;', $output);
		$output = str_replace('&mdash;', '&ndash;', $output);
		$output = str_replace('&#8212;', '&#8211;', $output);
		$output = preg_replace('/(\s+)й(\s+|\.|!|\?|,|<|&|;|:)/u', '\1&#1117;\2', $output);
		return $output;
    }
}

$_wp_bg_style =& new bg_style;