<?php
/*
Plugin Name: По български
Plugin URI: http://wordpress.org/extend/plugins/bgstyle/
Description: Помага за по-доброто оформление за публикации на български език
Author: Николай Бачийски
Version: 0.04
Author URI: http://nikolay.bg/
*/

class BG_Style {

	public $quotes_translations = array(
		array( '&#8220;', 'opening curly quote', '&#8222;' ),
		array( '&#8220;', 'opening curly double quote', '&#8222;' ),
		array( '&#8221;', 'closing curly quote', '&#8220;' ),
		array( '&#8221;', 'closing curly double quote', '&#8220;' ),
	);

    function __construct() {
		$this->add_bgtexturize_after_wptexturize();
		add_filter( 'wp_head', array( &$this, 'no_quotes_around_q' ) );
		add_filter( 'gettext_with_context', array( $this, 'force_bulgarian_quotes' ), 10, 4 );
    }

	function add_bgtexturize_after_wptexturize() {
		global $wp_filter;
		foreach ( $wp_filter as $tag => $filter ) {
			$found_wptxt = 0;
			foreach ( $wp_filter[$tag] as $priority => $functions ) {
				if ( !is_null( $functions ) ) {
					foreach ( $functions as $function ) {
						if ( 'wptexturize' == $function['function'] ) {
							add_filter( $tag, array( &$this, 'bgtexturize' ), $priority + 1 );
							$found_wptxt = 1;
							break;
						}
					}
				}
			}
			if ( $found_wptxt ) {
				continue;
			}
		}
	}

	function no_quotes_around_q() {
?>
	<style type="text/css">
		q:before, q:after { content: '';}
	</style>
<?php
	}

	function force_bulgarian_quotes( $translation, $text, $context, $domain ) {
		foreach( $this->quotes_translations as $item ) {
			list( $english_quote, $quote_context, $bulgarian_quote) = $item;
			if ( $english_quote === $translation && $quote_context === $context ) {
				return $bulgarian_quote;
			}
		}
		return $translation;
	}

	/*
	 Goes after wptexturize and changes the quotes to match the Bulgarian style

	 Most of the code of this function is taken from Kimmo Suominen's 'Finnish Quotes' plugin
	 and can be found here: http://kimmo.suominen.com/sw/finquote/finquote.php
	*/
    function bgtexturize( $text ) {
		$text = str_replace( '<q>', '&#8222;<q>', $text );
		$text = str_replace( '</q>', '</q>&#8220;', $text );
		$text = str_replace( ' - ', '&ndash;', $text );
		// ndash is more typrographically correct in Bulgarian
		$text = str_replace( '&mdash;', '&ndash;', $text );
		$text = str_replace( '&mdash;', '&ndash;', $text );
		$text = str_replace( '&#8212;', '&#8211;', $text );
		$text = preg_replace( '/(\s+)й(\s+|\.|!|\?|,|<|&|;|:)/u', '\1&#1117;\2', $text );
		return $text;
    }
}

$_wp_bg_style = new BG_Style;
