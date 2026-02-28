<?php
/**
 * Plugin Name:       Weather Block
 * Description:       A simple block that show weather info from Weather API. Refactored for better maintenance.
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Version:           0.3.0
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
 * Helper function to get weather data from the API.
 * Centralizes the logic for Block Bindings, API REST and block rendering.
 *
 * @param string $city The name of the city.
 * @return array|WP_Error Array with weather data or WP_Error.
 */
function weatherblock_fetch_from_api( $city ) {
    // ----------------------------------------------------------
    // 1. Check the transient cache first (15-minute TTL per city)
    // ----------------------------------------------------------
    $cache_key = 'weatherblock_' . sanitize_key( $city );
    $cached    = get_transient( $cache_key );

    if ( false !== $cached ) {
        return $cached; // Return cached data, no API call needed.
    }

    // ----------------------------------------------------------
    // 2. Cache miss — fetch from OpenWeatherMap
    // ----------------------------------------------------------
    $api_key = get_option( 'weatherblock_api_key', '' );

    if ( empty( $api_key ) ) {
        return new WP_Error( 'no_api_key', 'Clave API no configurada' );
    }

    $url      = 'https://api.openweathermap.org/data/2.5/weather';
    // units=imperial is added for consistency (Fahrenheit)
    $full_url = $url . '?q=' . urlencode( $city ) . '&appid=' . $api_key . '&units=imperial';

    $response = wp_remote_get( $full_url );

    if ( is_wp_error( $response ) ) {
        return $response; // Network errors are never cached.
    }

    $response_code = wp_remote_retrieve_response_code( $response );
    if ( 200 !== $response_code ) {
        return new WP_Error( 'api_error', 'El proveedor devolvió el error: ' . $response_code );
    }

    $data = json_decode( wp_remote_retrieve_body( $response ) );

    if ( ! $data || ! isset( $data->name ) ) {
        return new WP_Error( 'invalid_data', 'Datos del clima inválidos recibidos' );
    }

    $result = array(
        'city'        => $data->name,
        'temperature' => $data->main->temp, // Fahrenheit due to units=imperial
        'description' => $data->weather[0]->description,
        'humidity'    => $data->main->humidity,
        'wind_speed'  => $data->wind->speed,
        'icon_code'   => $data->weather[0]->icon,
        'icon_url'    => 'http://openweathermap.org/img/wn/' . $data->weather[0]->icon . '@2x.png',
    );

    // ----------------------------------------------------------
    // 3. Store successful result in the transient cache
    //    WP_Error responses are intentionally NOT cached so the
    //    next request will retry the API automatically.
    // ----------------------------------------------------------
    set_transient( $cache_key, $result, 15 * MINUTE_IN_SECONDS );

    return $result;
}

/**
 * Clear the transient cache for a specific city.
 * Useful when an admin changes the API key or wants fresh data.
 *
 * @param string $city The city name whose cache should be cleared.
 */
function weatherblock_clear_cache( $city ) {
    delete_transient( 'weatherblock_' . sanitize_key( $city ) );
}

// ==========================================
// 2. ADMIN SETTINGS
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

    add_settings_section(
        'weatherblock_api_section',
        'Configuración de API',
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

function weatherblock_settings_page() {
    ?>
    <div class="wrap">
        <h1>Configuración de Weather Block</h1>
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
            'label'        => __( 'Nombre de Ciudad para Clima', 'weatherblock' ),
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
    // Fuente de datos del clima
    register_block_bindings_source(
        'weatherblock/weather-data',
        array(
            'label'              => __( 'Datos del Clima', 'weatherblock' ),
            'get_value_callback' => 'weatherblock_get_weather_data_binding',
            'uses_context'       => array( 'postId' ),
        )
    );
    
    // Fuente del nombre de la ciudad
    register_block_bindings_source(
        'weatherblock/city-name',
        array(
            'label'              => __( 'Nombre de Ciudad', 'weatherblock' ),
            'get_value_callback' => 'weatherblock_get_city_name_binding',
            'uses_context'       => array( 'postId' ),
        )
    );
}
add_action( 'init', 'weatherblock_register_bindings_sources' );

/**
 * Callback de Block Binding: Usa la función helper centralizada.
 */
function weatherblock_get_weather_data_binding( $source_args, $block_instance ) {
    // Logic to determine the city from the context or arguments
    $city_name = isset( $source_args['city'] ) ? $source_args['city'] : 'managua';
    
    // Call to centralized logic
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
 * REST API Callback: Uses the centralized helper function.
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
 * Widget Rendering: Uses the centralized helper function.
 */
function render_weather_widget( $attributes, $content, $block ) {
    $cityname = isset($attributes['cityName']) ? $attributes['cityName'] : 'managua';
    
    // Call to centralized logic
    $data = weatherblock_fetch_from_api( $cityname );

    if ( is_wp_error( $data ) ) {
        return '<p>Weather data not available: ' . esc_html( $data->get_error_message() ) . '</p>';
    }

    ob_start();
    ?>
    <section class="weather-card">
        <div class="main-weather">
            <h3><?php echo esc_html( $data['city'] ); ?></h3>
            <p><?php echo esc_html( $data['description'] ); ?></p>
            <img src="<?php echo esc_url( $data['icon_url'] ); ?>" alt="icon" />
        </div>
        <div class="temp-info">
            <p><?php echo esc_html( $data['temperature'] ); ?> F</p>
        </div>
    </section>
    <?php
    return ob_get_clean();
}