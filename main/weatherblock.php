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

function render_weather_widget($attributes, $content, $block){

   	$cityname = $attributes['cityName'] ? $attributes['cityName'] : 'managua';
   
       
	//API variables
    $url = 'https://api.openweathermap.org/data/2.5/weather';
    $apiKey = '6b1cd5a24a18ee83c55372465790bed5';

    $fullUrl = $url . '?q=' . $cityname . '&appid=' . $apiKey;
    $response = wp_remote_get($fullUrl);

   
    if (is_wp_error($response)) {
		error_log("Error: ". $response->get_error_message());
		return false;
	}

    if ($cityname !== ''){

    $body = wp_remote_retrieve_body($response);

	$data = json_decode($body);


   $city = $data->name;
   $temp = $data->main->temp;
   $cityweather = $data->weather[0]->description;
   $hummidity = $data->main->humidity;
   $speed = $data->wind->speed;
   $weatherIcon = $data->weather[0]->icon;

  
   ob_start();
    ?>
        <section class="weather-card">
            <div class="main-weather">
                <div class="city">
                    <h3><?php echo esc_html( $city );?></h3>
                    <p> <?php echo esc_html( $cityweather );?></p>
                </div>
                <div class="weather-icon">
                    <img src="http://openweathermap.org/img/wn/<?php echo $weatherIcon ?>@2x.png" />
                </div>
            </div>
            
            <div class="temp-info">
                <p class="temp"> <span><?php echo esc_html( $temp )?></span> F </p>         
                <div class="humidity">
                    <img src="<?php echo plugin_dir_url(__FILE__) . 'assets/humidity.png'  ?>"/>
                    <p><?php echo esc_html($hummidity ) . ' ' . '%'; ?></p>
                </div>
               <div class="wind">
                    <img src="<?php echo plugin_dir_url(__FILE__) . 'assets/wind.png'  ?>"/>
                    <p><?php echo esc_html($speed ) . ' ' . 'mi/h'; ?></p>
               </div>
            </div>
        </section>


    <?php

   return ob_get_clean();
   }
}

