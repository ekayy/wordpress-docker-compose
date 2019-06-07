<?php
/**
 * Prism-woo Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package prism-woo
 */

add_action('wp_enqueue_scripts', 'generatepress_parent_theme_enqueue_styles');

/**
 * Enqueue scripts and styles.
 */
function generatepress_parent_theme_enqueue_styles()
{
	wp_enqueue_style('generatepress-style', get_template_directory_uri() . '/style.css');
	wp_enqueue_style(
		'prism-woo-style',
		get_stylesheet_directory_uri() . '/style.css',
		array('generatepress-style')
	);
}

add_action('wp_enqueue_scripts', function () {
	$script = "jQuery( document ).ready( function($) {
        $( '.wc-menu-item a' ).on( 'click', function( e ) {
            e.preventDefault();
            generateOffside.open();
        } );
    } );";

	wp_add_inline_script('generate-offside', $script);
});
