<?php
/**
 * Plugin Name:       Weather Block
 * Description:       A simple block that show weather info from Weather API. Refactored for better maintenance.
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Version:           0.2.0
 * Author:            Alex Cuadra
 * License:           GPL-2.0-or-later
 * Text Domain:       weatherblock
 *
 * @package           create-block
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ==========================================
// 1. HELPER FUNCTIONS
// ==========================================

/**
 * Helper function to check rate limiting.
 *
 * @param string $ip Client IP address.
 * @return bool True if rate limited, false otherwise.
 */
function weatherblock_is_rate_limited( $ip ) {
	$rate_limit_key = 'weatherblock_rate_limit_' . md5( $ip );
	$request_count = get_transient( $rate_limit_key );
	
	// Allow 30 requests per hour per IP
	$limit = apply_filters( 'weatherblock_rate_limit_count', 30 );
	$window = apply_filters( 'weatherblock_rate_limit_window', HOUR_IN_SECONDS );
	
	if ( false === $request_count ) {
		// First request in window
		set_transient( $rate_limit_key, 1, $window );
		return false;
	}
	
	if ( $request_count >= $limit ) {
		return true; // Rate limited
	}
	
	// Increment request count
	set_transient( $rate_limit_key, $request_count + 1, $window );
	return false;
}

/**
 * Helper function to fetch weather data from the API with caching (Transients API).
 *
 * @param string $city The name of the city.
 * @return array|WP_Error Array with weather data or WP_Error.
 */
function weatherblock_fetch_from_api( $city ) {
	// Check rate limiting first
	$client_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( weatherblock_is_rate_limited( $client_ip ) ) {
		return new WP_Error( 'rate_limited', __( 'Too many requests. Please try again later.', 'weatherblock' ) );
	}
	
	$city          = sanitize_text_field( strtolower( $city ) );
	$transient_key = 'weather_data_' . md5( $city );
	$cached_data   = get_transient( $transient_key );

	if ( false !== $cached_data ) {
		return $cached_data;
	}

	$api_key = get_option( 'weatherblock_api_key', '' );

	if ( empty( $api_key ) ) {
		return new WP_Error( 'no_api_key', __( 'Clave API no configurada', 'weatherblock' ) );
	}

	$url      = 'https://api.openweathermap.org/data/2.5/weather';
	$full_url = add_query_arg(
		array(
			'q'     => $city,
			'appid' => $api_key,
			'units' => 'imperial',
		),
		$url
	);

	$response = wp_remote_get( $full_url );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$response_code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $response_code ) {
		return new WP_Error( 'api_error', sprintf( __( 'El proveedor devolvió el error: %s', 'weatherblock' ), $response_code ) );
	}

	$data = json_decode( wp_remote_retrieve_body( $response ) );

	if ( ! $data || ! isset( $data->name ) ) {
		return new WP_Error( 'invalid_data', __( 'Datos del clima inválidos recibidos', 'weatherblock' ) );
	}

	$weather_data = array(
		'city'        => $data->name,
		'temperature' => round( $data->main->temp ),
		'description' => ucfirst( $data->weather[0]->description ),
		'humidity'    => $data->main->humidity,
		'wind_speed'  => $data->wind->speed,
		'icon_code'   => $data->weather[0]->icon,
		'icon_url'    => 'https://openweathermap.org/img/wn/' . $data->weather[0]->icon . '@2x.png',
	);

	// Save in cache - configurable duration with filter
	$cache_duration = apply_filters( 'weatherblock_cache_duration', 15 * MINUTE_IN_SECONDS, $city );
	set_transient( $transient_key, $weather_data, $cache_duration );

	return $weather_data;
}

// ==========================================
// 2. ADMIN CONFIGURATION
// ==========================================

function weatherblock_admin_menu() {
	add_options_page(
		'Weather Block',
		'Weather Block',
		'manage_options',
		'weatherblock-settings',
		'weatherblock_settings_page'
	);
}
add_action( 'admin_menu', 'weatherblock_admin_menu' );

function weatherblock_register_settings() {
	register_setting(
		'weatherblock_settings',
		'weatherblock_api_key',
		array(
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		)
	);
	
	// Add cache invalidation hook for API key changes
	add_action( 'update_option_weatherblock_api_key', 'weatherblock_clear_cache_on_key_change', 10, 2 );

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

function weatherblock_api_section_callback() {
	echo '<p>Configura tu clave API de OpenWeatherMap aquí.</p>';
}

function weatherblock_api_key_callback() {
	$api_key = get_option( 'weatherblock_api_key', '' );
	?>
	<input type="password" 
		   name="weatherblock_api_key" 
		   value="<?php echo esc_attr( $api_key ); ?>" 
		   class="regular-text" 
		   placeholder="Ingresa tu API Key" />
	<?php
}

/**
 * Clear weather cache when API key changes.
 *
 * @param string $old_value Old API key.
 * @param string $new_value New API key.
 */
function weatherblock_clear_cache_on_key_change( $old_value, $new_value ) {
	if ( $old_value !== $new_value ) {
		global $wpdb;
		// Clear all weather data transients
		$wpdb->query( 
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_weather_data_%'" 
		);
	}
}

function weatherblock_settings_page() {
	?>
	<div class="wrap">
		<h1>Weather Block Configuration</h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'weatherblock_settings' );
			do_settings_sections( 'weatherblock-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

// ==========================================
// 3. POST META & BLOCK BINDINGS
// ==========================================

function weatherblock_register_post_meta() {
	register_meta(
		'post',
		'weatherblock_city_name',
		array(
			'show_in_rest' => true,
			'single'       => true,
			'type'         => 'string',
			'label'        => __( 'City Name for Weather', 'weatherblock' ),
		)
	);
	
	register_meta(
		'post',
		'weatherblock_auto_refresh',
		array(
			'show_in_rest' => true,
			'single'       => true,
			'type'         => 'boolean',
			'default'      => false,
		)
	);
}
add_action( 'init', 'weatherblock_register_post_meta' );

function weatherblock_register_bindings_sources() {
	// Weather data source
	register_block_bindings_source(
		'weatherblock/weather-data',
		array(
			'label'              => __( 'Weather Data', 'weatherblock' ),
			'get_value_callback' => 'weatherblock_get_weather_data_binding',
			'uses_context'       => array( 'postId' ),
		)
	);
	
	// City name source
	register_block_bindings_source(
		'weatherblock/city-name',
		array(
			'label'              => __( 'City Name', 'weatherblock' ),
			'get_value_callback' => 'weatherblock_get_city_name_binding',
			'uses_context'       => array( 'postId' ),
		)
	);
}
add_action( 'init', 'weatherblock_register_bindings_sources' );

/**
 * Callback for Block Binding: Uses the central helper function.
 */
function weatherblock_get_weather_data_binding( $source_args, $block_instance ) {
	// Logic to determine the city from the context or arguments
	$city_name = isset( $source_args['city'] ) ? $source_args['city'] : 'managua';
	
	// Call the central logic
	$data = weatherblock_fetch_from_api( $city_name );

	if ( is_wp_error( $data ) ) {
		return array(
			'temperature'  => 'N/A',
			'description'  => $data->get_error_message(),
			'humidity'     => 'N/A',
			'wind_speed'   => 'N/A',
		);
	}
	
	return array(
		'temperature'  => $data['temperature'],
		'description'  => $data['description'],
		'humidity'     => $data['humidity'],
		'wind_speed'   => $data['wind_speed'],
	);
}

function weatherblock_get_city_name_binding( $source_args, $block_instance ) {
	return isset( $source_args['default'] ) ? $source_args['default'] : 'managua';
}

// ==========================================
// 4. API REST & BLOCK INITIALIZATION
// ==========================================

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
 * Conditional asset loading for better performance
 */
function weatherblock_conditional_assets() {
	// Only load assets if the block is present on the page
	if ( has_block( 'create-block/weatherblock' ) ) {
		wp_enqueue_style(
			'weatherblock-style',
			plugins_url( 'build/style-index.css', __FILE__ ),
			array(),
			filemtime( plugin_dir_path( __FILE__ ) . 'build/style-index.css' )
		);
		
		wp_enqueue_script(
			'weatherblock-view',
			plugins_url( 'build/view.js', __FILE__ ),
			array( 'wp-interactivity' ),
			filemtime( plugin_dir_path( __FILE__ ) . 'build/view.js' ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'weatherblock_conditional_assets' );

/**
 * Defer loading of assets in admin
 */
function weatherblock_admin_conditional_assets() {
	global $current_screen;
	
	// Only load editor assets when editing posts with blocks
	if ( $current_screen && $current_screen->is_block_editor ) {
		wp_enqueue_style(
			'weatherblock-editor-style',
			plugins_url( 'build/index.css', __FILE__ ),
			array(),
			filemtime( plugin_dir_path( __FILE__ ) . 'build/index.css' )
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'weatherblock_admin_conditional_assets' );

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
					'required' => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'weatherblock_register_rest_routes' );

/**
 * Callback for API REST: Uses the central helper function.
 */
function weatherblock_get_weather_rest( $request ) {
	$city = $request->get_param( 'city' );
	$data = weatherblock_fetch_from_api( $city );

	if ( is_wp_error( $data ) ) {
		return $data;
	}

	return array(
		'success' => true,
		'data'    => $data,
	);
}

/**
 * Render the widget: Uses the central helper function.
 */
function render_weather_widget( $attributes, $content, $block ) {
	$cityname = isset($attributes['cityName']) ? $attributes['cityName'] : 'managua';
	
	// Call the central logic
	$data = weatherblock_fetch_from_api( $cityname );

	if ( is_wp_error( $data ) ) {
		return '<p>' . sprintf( __( 'Weather data not available: %s', 'weatherblock' ), esc_html( $data->get_error_message() ) ) . '</p>';
	}

	ob_start();
	?>
	<section class="weather-card">
		<div class="main-weather">
			<h3><?php echo esc_html( $data['city'] ); ?></h3>
			<p><?php echo esc_html( $data['description'] ); ?></p>
			<img src="<?php echo esc_url( $data['icon_url'] ); ?>" alt="<?php echo esc_attr( $data['description'] ); ?>" />
		</div>
		<div class="temp-info">
			<p><?php echo esc_html( $data['temperature'] ); ?>°F</p>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
