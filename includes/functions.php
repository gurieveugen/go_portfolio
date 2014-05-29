<?php		
/**
 * Common functions
 *
 * @package   Go - Portfolio
 * @author    Granth <granthweb@gmail.com>
 * @link      http://granthweb.com
 * @copyright 2013 Granth
 */

/**
 * Clean input fields
 */
 
function go_portfolio_clean_input( $input_data=array(), $html_allowed_keys=array(), $trash_keys=array() ) {
	foreach( $input_data as $data_key=>$data_value ) {
		if ( is_array( $data_value ) ) {
			 go_portfolio_clean_input( $data_value, $html_allowed_keys, $trash_keys );
		} elseif ( in_array( $data_key, $trash_keys ) ) {
				unset( $input_data[$data_key] );
				continue;
		} else {
				$input_data[$data_key]=stripslashes( trim( $input_data[$data_key] ) );
			if ( empty( $html_allowed_keys ) || !in_array( $data_key, $html_allowed_keys ) ) { 
				$input_data[$data_key] = sanitize_text_field( $input_data[$data_key] );
			}
		}
	}
	return $input_data;
}

/**
 * Custom excerpt function
 * Based on: http://bacsoftwareconsulting.com/blog/index.php/wordpress-cat/how-to-preserve-html-tags-in-wordpress-excerpt-without-a-plugin/
 */

function go_portfolio_wp_trim_excerpt( $text, $excerpt_word_count=25,  $excerpt_end = '...' ) {
	$raw_excerpt = $text;
	if ( '' == $text ) {
		/* retrieve the post content */
		$text = get_the_content( '' );
	 
		/* delete all shortcode tags from the content */
		$text = strip_shortcodes( $text );
	 
		$text = apply_filters( 'the_content', $text );
		$text = str_replace( ']]>', ']]&gt;', $text );
	 
		$allowed_tags = ''; /* Edit this to add allowed tags */
		$text = strip_tags( $text, $allowed_tags );
		$excerpt_length = apply_filters( 'excerpt_length', $excerpt_word_count ); 
		$excerpt_more = apply_filters( 'excerpt_more', 	$excerpt_end );
	 
		$words = preg_split( "/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY );
		
		if ( count( $words ) > $excerpt_length ) {
			array_pop( $words );
			$text = implode( ' ', $words );
			$text = $text . $excerpt_more;
		} else {
			$text = implode( ' ', $words );
		}
	}
	return apply_filters( 'wp_trim_excerpt', $text, $raw_excerpt, $excerpt_word_count, $excerpt_end );
}

remove_filter( 'get_the_excerpt', 'wp_trim_excerpt' );
add_filter( 'get_the_excerpt', 'go_portfolio_wp_trim_excerpt', 10, 3 );

?>
