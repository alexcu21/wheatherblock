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

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add admin menu for Weather Block settings.
 */
function weatherblock_admin_menu() {
	add_options_page(
		'Weather Block Settings',
		'Weather Block',
		'manage_options',
		'weatherblock-settings',
		'weatherblock_settings_page'
	);
}
add_action( 'admin_menu', 'weatherblock_admin_menu' );

/**
 * Register settings for Weather Block.
 */
function weatherblock_register_settings() {
	register_setting(
		'weatherblock_settings',
		'weatherblock_api_key',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		)
	);

	add_settings_section(
		'weatherblock_api_section',
		'API Configuration',
		'weatherblock_api_section_callback',
		'weatherblock-settings'
	);

	add_settings_field(
		'weatherblock_api_key',
		'OpenWeatherMap API Key',
		'weatherblock_api_key_callback',
		'weatherblock-settings',
		'weatherblock_api_section'
	);
}
add_action( 'admin_init', 'weatherblock_register_settings' );

/**
 * Register post meta for block bindings.
 */
function weatherblock_register_post_meta() {
	register_meta(
		'post',
		'weatherblock_city_name',
		array(
			'show_in_rest' => true,
			'single'       => true,
			'type'         => 'string',
			'label'        => __( 'Weather Block City Name', 'weatherblock' ),
		)
	);
	
	register_meta(
		'post',
		'weatherblock_auto_refresh',
		array(
			'show_in_rest' => true,
			'single'       => true,
			'type'         => 'boolean',
			'label'        => __( 'Auto Refresh Weather', 'weatherblock' ),
			'default'      => false,
		)
	);
}
add_action( 'init', 'weatherblock_register_post_meta' );

/**
 * Register custom block bindings sources.
 */
function weatherblock_register_bindings_sources() {
	// Weather data source
	register_block_bindings_source(
		'weatherblock/weather-data',
		array(
			'label'              => __( 'Weather Data', 'weatherblock' ),
			'get_value_callback' => 'weatherblock_get_weather_data',
			'uses_context'       => array( 'postId' ),
		)
	);
	
	// City name source
	register_block_bindings_source(
		'weatherblock/city-name',
		array(
			'label'              => __( 'City Name', 'weatherblock' ),
			'get_value_callback' => 'weatherblock_get_city_name',
			'uses_context'       => array( 'postId' ),
		)
	);
}
add_action( 'init', 'weatherblock_register_bindings_sources' );

/**
 * Get weather data for block bindings.
 */
function weatherblock_get_weather_data( $source_args, $block_instance ) {
	$city_name = isset( $source_args['city'] ) ? $source_args['city'] : 'managua';
	$api_key   = get_option( 'weatherblock_api_key', '' );
	
	if ( empty( $api_key ) ) {
		return array(
			'temperature'  => 'N/A',
			'description'  => 'API not configured',
			'humidity'     => 'N/A',
			'wind_speed'   => 'N/A',
		);
	}
	
	// Fetch weather data (reuse existing logic)
	$url      = 'https://api.openweathermap.org/data/2.5/weather';
	$full_url = $url . '?q=' . $city_name . '&appid=' . $api_key;
	$response = wp_remote_get( $full_url );
	
	if ( is_wp_error( $response ) ) {
		return array(
			'temperature'  => 'Error',
			'description'  => 'Data unavailable',
			'humidity'     => 'N/A',
			'wind_speed'   => 'N/A',
		);
	}
	
	$data = json_decode( wp_remote_retrieve_body( $response ) );
	
	return array(
		'temperature'  => $data->main->temp ?? 'N/A',
		'description'  => $data->weather[0]->description ?? 'N/A',
		'humidity'     => $data->main->humidity ?? 'N/A',
		'wind_speed'   => $data->wind->speed ?? 'N/A',
	);
}

/**
 * Get city name for block bindings.
 */
function weatherblock_get_city_name( $source_args, $block_instance ) {
	return isset( $source_args['default'] ) ? $source_args['default'] : 'managua';
}

/**
 * API section callback.
 */
function weatherblock_api_section_callback() {
	echo '<p>Configure your OpenWeatherMap API key to use your own API quota and ensure better reliability.</p>';
	echo '<p><strong>Note:</strong> An API key is required for the Weather Block to function properly.</p>';
}

/**
 * API key field callback.
 */
function weatherblock_api_key_callback() {
	$api_key = get_option( 'weatherblock_api_key', '' );
	$masked_key = '';
	
	// Mask the API key for display (show only first 4 and last 4 characters).
	if ( ! empty( $api_key ) ) {
		$key_length = strlen( $api_key );
		if ( $key_length > 8 ) {
			$masked_key = substr( $api_key, 0, 4 ) . str_repeat( '*', $key_length - 8 ) . substr( $api_key, -4 );
		} else {
			$masked_key = str_repeat( '*', $key_length );
		}
	}
	?>
	<input type="password" 
		   id="weatherblock_api_key" 
		   name="weatherblock_api_key" 
		   value="<?php echo esc_attr( $api_key ); ?>" 
		   class="regular-text" 
		   placeholder="Enter your OpenWeatherMap API key" />
	<?php if ( ! empty( $api_key ) ) : ?>
		<p class="description">
			<strong>Current key:</strong> <?php echo esc_html( $masked_key ); ?> 
			<button type="button" id="toggle-api-key" class="button button-small" style="margin-left: 10px;">
				Show/Hide
			</button>
		</p>
	<?php endif; ?>
	<p class="description">
		Get your free API key from <a href="https://openweathermap.org/api" target="_blank">OpenWeatherMap</a>
	</p>
	
	<script>
	jQuery(document).ready(function($) {
		$('#toggle-api-key').on('click', function() {
			var input = $('#weatherblock_api_key');
			if (input.attr('type') === 'password') {
				input.attr('type', 'text');
			} else {
				input.attr('type', 'password');
			}
		});
	});
	</script>
	<?php
}

/**
 * Settings page HTML.
 */
function weatherblock_settings_page() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		
		<form method="post" action="options.php">
			<?php
			settings_fields( 'weatherblock_settings' );
			do_settings_sections( 'weatherblock-settings' );
			submit_button( 'Save Settings' );
			?>
		</form>

		<div class="weatherblock-info" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-left: 4px solid #0073aa;">
			<h3>How to get an OpenWeatherMap API Key:</h3>
			<ol>
				<li>Visit <a href="https://openweathermap.org/api" target="_blank">OpenWeatherMap API</a></li>
				<li>Sign up for a free account</li>
				<li>Navigate to your API keys section</li>
				<li>Copy your API key and paste it above</li>
				<li>Save the settings</li>
			</ol>
			<p><strong>Free tier includes:</strong> 1,000 calls per day, which is sufficient for most websites.</p>
			<p><strong>Security:</strong> Your API key is stored securely and masked in the settings page.</p>
		</div>
	</div>
	<?php
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_weatherblock_block_init() {
	register_block_type(
		__DIR__ . '/build',
		array(
			'render_callback' => 'render_weather_widget',
		)
	);
}
add_action( 'init', 'create_block_weatherblock_block_init' );

/**
 * Register REST API routes.
 */
function weatherblock_register_rest_routes() {
	register_rest_route(
		'weatherblock/v1',
		'/weather',
		array(
			'methods'             => 'GET',
			'callback'            => 'weatherblock_get_weather_rest',
			'permission_callback' => '__return_true',
			'args'                => array(
				'city' => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'weatherblock_register_rest_routes' );

/**
 * REST API callback for weather data.
 */
function weatherblock_get_weather_rest( $request ) {
	$city    = $request->get_param( 'city' );
	$api_key = get_option( 'weatherblock_api_key', '' );
	
	if ( empty( $api_key ) ) {
		return new WP_Error( 'no_api_key', 'API key not configured', array( 'status' => 400 ) );
	}
	
	$url      = 'https://api.openweathermap.org/data/2.5/weather';
	$full_url = $url . '?q=' . $city . '&appid=' . $api_key;
	$response = wp_remote_get( $full_url );
	
	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'api_error', 'Failed to fetch weather data', array( 'status' => 500 ) );
	}
	
	$data = json_decode( wp_remote_retrieve_body( $response ) );
	
	if ( ! $data || ! isset( $data->name ) ) {
		return new WP_Error( 'invalid_data', 'Invalid weather data received', array( 'status' => 500 ) );
	}
	
	return array(
		'success' => true,
		'data'    => array(
			'city'        => $data->name,
			'temperature' => $data->main->temp,
			'description' => $data->weather[0]->description,
			'humidity'    => $data->main->humidity,
			'wind_speed'  => $data->wind->speed,
			'icon_url'    => 'http://openweathermap.org/img/wn/' . $data->weather[0]->icon . '@2x.png',
		),
	);
}

/**
 * Enqueue interactive scripts.
 */
function weatherblock_enqueue_interactive_scripts() {
	if ( has_block( 'create-block/weatherblock' ) ) {
		wp_enqueue_script(
			'weatherblock-interactive',
			plugin_dir_url( __FILE__ ) . 'build/view.js',
			array( 'wp-interactivity' ),
			'0.1.0',
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'weatherblock_enqueue_interactive_scripts' );

/**
 * Render callback for the weather widget block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 * @return string|false Rendered block HTML or false on error.
 */
function render_weather_widget( $attributes, $content, $block ) {
	$cityname = $attributes['cityName'] ? $attributes['cityName'] : 'managua';

	// Get API key from settings.
	$api_key = get_option( 'weatherblock_api_key', '' );
	if ( empty( $api_key ) ) {
		return '<p>Please configure your OpenWeatherMap API key in <a href="' . admin_url( 'options-general.php?page=weatherblock-settings' ) . '">Weather Block Settings</a>.</p>';
	}

	// API variables.
	$url = 'https://api.openweathermap.org/data/2.5/weather';

	$full_url = $url . '?q=' . $cityname . '&appid=' . $api_key;
	$response = wp_remote_get( $full_url );

	if ( is_wp_error( $response ) ) {
		error_log( 'Weather Block Error: ' . $response->get_error_message() );
		return '<p>Weather data temporarily unavailable.</p>';
	}

	$response_code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $response_code ) {
		error_log( 'Weather Block API Error: HTTP ' . $response_code );
		return '<p>Weather data temporarily unavailable.</p>';
	}

	if ( $cityname !== '' ) {
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );

		// Check if data is valid.
		if ( ! $data || ! isset( $data->name ) ) {
			error_log( 'Weather Block Error: Invalid API response' );
			return '<p>Weather data temporarily unavailable.</p>';
		}

		$city         = $data->name;
		$temp         = $data->main->temp;
		$city_weather = $data->weather[0]->description;
		$humidity     = $data->main->humidity;
		$speed        = $data->wind->speed;
		$weather_icon = $data->weather[0]->icon;

		ob_start();
		?>
		<section class="weather-card">
			<div class="main-weather">
				<div class="city">
					<h3><?php echo esc_html( $city ); ?></h3>
					<p><?php echo esc_html( $city_weather ); ?></p>
				</div>
				<div class="weather-icon">
					<img src="http://openweathermap.org/img/wn/<?php echo esc_attr( $weather_icon ); ?>@2x.png" alt="<?php echo esc_attr( $city_weather ); ?>" />
				</div>
			</div>

			<div class="temp-info">
				<p class="temp">
					<span><?php echo esc_html( $temp ); ?></span> F
				</p>
				<div class="humidity">
					<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'assets/humidity.png' ); ?>" alt="Humidity" />
					<p><?php echo esc_html( $humidity ) . ' %'; ?></p>
				</div>
				<div class="wind">
					<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'assets/wind.png' ); ?>" alt="Wind" />
					<p><?php echo esc_html( $speed ) . ' mi/h'; ?></p>
				</div>
			</div>
		</section>
		<?php

		return ob_get_clean();
	}
}
