<?php
/*
Plugin Name: По български
Plugin URI: http://wordpress.org/extend/plugins/bgstyle/
Description: Помага за по-лесно и приятно писане на публикации на български.
Author: Николай Бачийски
Version: 0.1
Author URI: http://nb.niichavo.org/
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
		q {font-style: italic;}
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
	
		// loop stuff
		$stop = count($tarr); $next = true;
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
		$output = str_replace('<q>', '&#8222;<q>', $output);
		$output = str_replace('</q>', '</q>&#8220;', $output);
		$output = str_replace(' й ', ' &#1117; ', $output);
		return $output;
    }
}

$_wp_bg_style =& new bg_style;

?>