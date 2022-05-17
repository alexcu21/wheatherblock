<?php
/**
 * Plugin Name:       Weather Block
 * Description:       A simple block that show weather info from Weather API .
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Alex Cuadra
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       weatherblock
 *
 * @package           create-block
 */

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_weatherblock_block_init() {
	register_block_type( __DIR__ . '/build', array(
		'render_callback' => 'render_weather_widget'
	) );
}
add_action( 'init', 'create_block_weatherblock_block_init' );


function render_weather_widget(){
	$message = '<h3>Hello Weather</h3>';
	return $message;
}
