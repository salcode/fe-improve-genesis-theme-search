<?php
/**
 * Plugin Name: Iron Code Improve Genesis Theme Search
 * Plugin URI: https://salferrarello.com/improve-genesis-theme-search/
 * Description: Modify the search results page of a Genesis theme following the recommendations on https://yoast.com/wordpress-search/.
 * Version: 1.0.0
 * Requires PHP: 5.3
 * Author: Sal Ferrarello
 * Author URI: http://salferrarello.com/
 * License: Apache-2.0
 * License URI: https://spdx.org/licenses/Apache-2.0.html
 * Text Domain: fe-improve-genesis-theme-search
 * Domain Path: /languages
 *
 * @package fe-improve-genesis-theme-search
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Force the search results page to display excerpts, not full content.
add_action( 'genesis_before_loop', 'fe_sk_excerpts_search_page' );

// Emphasize the search term (when present) in the title.
add_filter( 'genesis_post_title_text', 'fe_genesis_emphasize_search_result_title' );

// Generate custom search result excerpt.
add_filter( 'wp_trim_excerpt', 'fe_custom_trim_excerpt' );

/**
 * Force the search results page to display excerpts, not full content.
 *
 * Originally from https://gist.github.com/nutsandbolts/7377351.
 * Prefix and function_exists() check added to avoid possible conflicts.
 */
if ( ! function_exists( 'fe_sk_excerpts_search_page' ) ) {
	function fe_sk_excerpts_search_page() {
		if ( is_search() ) {
			add_filter( 'genesis_pre_get_option_content_archive', function() {
				return 'excerpts';
			});
		}
	}
}

/**
 * Emphasize the search term (when present) in the title.
 *
 * @param string $title The post title.
 * @return string The title with strong tags around the current search query.
 */
if ( ! function_exists( 'fe_genesis_emphasize_search_result_title' ) ) {
	function fe_genesis_emphasize_search_result_title( $title ) {
		if ( ! is_search() ) {
			return $title;
		}
		return fe_emphasize( $title, get_search_query() );
	}
}

/**
 * Generate custom search result excerpt.
 *
 * Originally from https://yoast.com/wordpress-search/
 * Prefix and function_exists() check added to avoid possible conflicts.
 *
 * @param string $text  The text to be trimmed.
 * @return string       The trimmed text.
 */
if ( ! function_exists( 'fe_custom_trim_excerpt' ) ) {
	function fe_custom_trim_excerpt( $text = '' ) {
		$text = strip_shortcodes( $text );
		$text = apply_filters( 'the_content', $text );
		$text = str_replace( ']]>', ']]&gt;', $text );

		$excerpt_length = apply_filters( 'excerpt_length', 55 );

		$trimmed = wp_trim_words( $text, $excerpt_length, '' );

		if ( is_search() ) {
			$trimmed = fe_emphasize( $trimmed, get_search_query() );
		}

		return $trimmed . fe_modify_read_more_link();
	}
}

/**
 * Creates a custom read more link.
 *
 * Originally from https://yoast.com/wordpress-search/
 * Prefix and function_exists() check added to avoid possible conflicts.
 *
 * @return string The read more link.
 */
if ( ! function_exists( 'fe_modify_read_more_link' ) ) {
	function fe_modify_read_more_link() {
		return ' <a class="more-link" href="' . get_permalink() . '">Continue reading</a>';
	}
}

/**
 * Adds emphasis to the parts passed in $content that are equal to $search_query.
 * Originally from https://yoast.com/wordpress-search/
 * Prefix and function_exists() check added to avoid possible conflicts.
 *
 * @param $content The content to alter.
 * @param $search_query The search query to match against.
 *
 * @return string The emphasized text.
 */
if ( ! function_exists( 'fe_emphasize' ) ) {
	function fe_emphasize( $content, $search_query ) {
		$keys = array_map( 'preg_quote', explode( ' ', $search_query ) );
		return preg_replace( '/(' . implode( '|', $keys ) . ')/iu', '<strong class="search-excerpt">\0</strong>', $content );
	}
}
