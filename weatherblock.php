<?php
/**
 * Plugin Name:       Weather Block
 * Description:       A simple block that show weather info from Weather API. Supports WP Abilities API.
 * Requires at least: 6.9
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
// 1. CORE LOGIC (HELPER FUNCTIONS)
// ==========================================

/**
 * Helper function to fetch weather data.
 * Centralizes logic for Block Bindings, REST API, and Abilities API.
 *
 * @param string $city The city name.
 * @return array|WP_Error Weather data array or WP_Error.
 */
function weatherblock_fetch_from_api( $city ) {
    $api_key = get_option( 'weatherblock_api_key', '' );
    
    if ( empty( $api_key ) ) {
        return new WP_Error( 'no_api_key', 'API key not configured' );
    }

    $url      = 'https://api.openweathermap.org/data/2.5/weather';
    $full_url = $url . '?q=' . urlencode( $city ) . '&appid=' . $api_key . '&units=imperial'; // Added units for consistency
    
    $response = wp_remote_get( $full_url );
    
    if ( is_wp_error( $response ) ) {
        return $response;
    }
    
    $response_code = wp_remote_retrieve_response_code( $response );
    if ( 200 !== $response_code ) {
        return new WP_Error( 'api_error', 'Provider returned error: ' . $response_code );
    }

    $data = json_decode( wp_remote_retrieve_body( $response ) );
    
    if ( ! $data || ! isset( $data->name ) ) {
        return new WP_Error( 'invalid_data', 'Invalid weather data received' );
    }

    return array(
        'city'        => $data->name,
        'temperature' => $data->main->temp, // Fahrenheit usually if units not specified or handled by logic
        'description' => $data->weather[0]->description,
        'humidity'    => $data->main->humidity,
        'wind_speed'  => $data->wind->speed,
        'icon_code'   => $data->weather[0]->icon,
        'icon_url'    => 'http://openweathermap.org/img/wn/' . $data->weather[0]->icon . '@2x.png',
    );
}

// ==========================================
// 2. ABILITIES API IMPLEMENTATION
// ==========================================

/**
 * Register Weather Categories.
 */
function weatherblock_register_abilities_categories() {
    if ( ! function_exists( 'wp_register_ability_category' ) ) {
        return;
    }

    wp_register_ability_category(
        'weather-utilities',
        array(
            'label'       => __( 'Weather Utilities', 'weatherblock' ),
            'description' => __( 'Tools to fetch weather data and manage weather context.', 'weatherblock' ),
        )
    );
}
add_action( 'wp_abilities_api_categories_init', 'weatherblock_register_abilities_categories' );

/**
 * Register Weather Abilities.
 */
function weatherblock_register_abilities() {
    if ( ! function_exists( 'wp_register_ability' ) ) {
        return;
    }

    // Ability 1: Get Current Weather
    // Use Case: An AI agent wants to know the weather in a specific location using the site's quota.
    wp_register_ability(
        'weatherblock/get-current-weather',
        array(
            'label'               => __( 'Get Current Weather', 'weatherblock' ),
            'description'         => __( 'Fetches real-time weather data for a specific city.', 'weatherblock' ),
            'category'            => 'weather-utilities',
            'input_schema'        => array(
                'type'        => 'string',
                'description' => __( 'The name of the city (e.g., London, Managua).', 'weatherblock' ),
                'default'     => 'Managua',
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'city'        => array( 'type' => 'string' ),
                    'temperature' => array( 'type' => 'number' ),
                    'description' => array( 'type' => 'string' ),
                    'humidity'    => array( 'type' => 'number' ),
                ),
            ),
            'execute_callback'    => 'weatherblock_ability_get_weather',
            'permission_callback' => function() {
                // Allow anyone with read access (or restrict to 'edit_posts' to save API quota)
                return current_user_can( 'read' );
            },
            'meta' => array(
                'show_in_rest' => true,
            ),
        )
    );

    // Ability 2: Set Post City Context
    // Use Case: An AI writing a travel blog post can automatically set the weather city meta field.
    wp_register_ability(
        'weatherblock/set-post-city',
        array(
            'label'               => __( 'Set Post Weather City', 'weatherblock' ),
            'description'         => __( 'Updates the associated city for the weather block in a specific post.', 'weatherblock' ),
            'category'            => 'weather-utilities',
            'input_schema'        => array(
                'type'       => 'object',
                'required'   => array( 'post_id', 'city' ),
                'properties' => array(
                    'post_id' => array(
                        'type'        => 'integer',
                        'description' => 'The ID of the post to update.',
                    ),
                    'city'    => array(
                        'type'        => 'string',
                        'description' => 'The city name to assign.',
                    ),
                ),
            ),
            'output_schema'       => array(
                'type'        => 'boolean',
                'description' => 'True if updated successfully.',
            ),
            'execute_callback'    => 'weatherblock_ability_set_post_city',
            'permission_callback' => function() {
                // Must have permission to edit posts generally
                return current_user_can( 'edit_posts' );
            },
            'meta' => array(
                'show_in_rest' => true,
            ),
        )
    );
}
add_action( 'wp_abilities_api_init', 'weatherblock_register_abilities' );

/**
 * Execution Callback for 'weatherblock/get-current-weather'
 */
function weatherblock_ability_get_weather( $city_input ) {
    $city = ! empty( $city_input ) ? $city_input : 'Managua';
    $data = weatherblock_fetch_from_api( $city );

    if ( is_wp_error( $data ) ) {
        // Throw exception or return structured error according to Abilities API standards
        return array( 'error' => $data->get_error_message() ); 
    }

    return $data;
}

/**
 * Execution Callback for 'weatherblock/set-post-city'
 */
function weatherblock_ability_set_post_city( $input ) {
    $post_id = isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;
    $city    = isset( $input['city'] ) ? sanitize_text_field( $input['city'] ) : '';

    if ( ! $post_id || empty( $city ) ) {
        return false;
    }

    // Verify user can edit THIS specific post
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return false;
    }

    return update_post_meta( $post_id, 'weatherblock_city_name', $city );
}

// ==========================================
// 3. ADMIN SETTINGS (UNCHANGED MOSTLY)
// ==========================================

function weatherblock_admin_menu() {
    add_options_page( 'Weather Block', 'Weather Block', 'manage_options', 'weatherblock-settings', 'weatherblock_settings_page' );
}
add_action( 'admin_menu', 'weatherblock_admin_menu' );

function weatherblock_register_settings() {
    register_setting( 'weatherblock_settings', 'weatherblock_api_key', array( 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ) );
    add_settings_section( 'weatherblock_api_section', 'API Configuration', 'weatherblock_api_section_callback', 'weatherblock-settings' );
    add_settings_field( 'weatherblock_api_key', 'OpenWeatherMap API Key', 'weatherblock_api_key_callback', 'weatherblock-settings', 'weatherblock_api_section' );
}
add_action( 'admin_init', 'weatherblock_register_settings' );

// ... (Callbacks for settings skipped for brevity, keeping existing logic) ...
function weatherblock_api_section_callback() { echo '<p>Configure OpenWeatherMap API key.</p>'; }
function weatherblock_api_key_callback() {
    $api_key = get_option( 'weatherblock_api_key', '' );
    echo '<input type="password" name="weatherblock_api_key" value="' . esc_attr( $api_key ) . '" class="regular-text" />';
}
function weatherblock_settings_page() {
    ?>
    <div class="wrap">
        <h1>Weather Block Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'weatherblock_settings' ); do_settings_sections( 'weatherblock-settings' ); submit_button(); ?>
        </form>
    </div>
    <?php
}

// ==========================================
// 4. POST META & BLOCK BINDINGS
// ==========================================

function weatherblock_register_post_meta() {
    register_meta( 'post', 'weatherblock_city_name', array( 'show_in_rest' => true, 'single' => true, 'type' => 'string' ) );
    register_meta( 'post', 'weatherblock_auto_refresh', array( 'show_in_rest' => true, 'single' => true, 'type' => 'boolean', 'default' => false ) );
}
add_action( 'init', 'weatherblock_register_post_meta' );

function weatherblock_register_bindings_sources() {
    register_block_bindings_source( 'weatherblock/weather-data', array( 'label' => __( 'Weather Data', 'weatherblock' ), 'get_value_callback' => 'weatherblock_get_weather_data_binding', 'uses_context' => array( 'postId' ) ) );
    register_block_bindings_source( 'weatherblock/city-name', array( 'label' => __( 'City Name', 'weatherblock' ), 'get_value_callback' => 'weatherblock_get_city_name_binding', 'uses_context' => array( 'postId' ) ) );
}
add_action( 'init', 'weatherblock_register_bindings_sources' );

/**
 * Block Binding Callback: Uses the centralized helper function
 */
function weatherblock_get_weather_data_binding( $source_args, $block_instance ) {
    // Logic to determine city from context or arguments
    $city_name = isset( $source_args['city'] ) ? $source_args['city'] : 'managua';
    
    // Call centralized logic
    $data = weatherblock_fetch_from_api( $city_name );

    if ( is_wp_error( $data ) ) {
        return array( 'temperature' => 'N/A', 'description' => $data->get_error_message() );
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
// 5. REST API & BLOCK INIT
// ==========================================

function create_block_weatherblock_block_init() {
    register_block_type( __DIR__ . '/build', array( 'render_callback' => 'render_weather_widget' ) );
}
add_action( 'init', 'create_block_weatherblock_block_init' );

function weatherblock_register_rest_routes() {
    register_rest_route( 'weatherblock/v1', '/weather', array(
        'methods' => 'GET',
        'callback' => 'weatherblock_get_weather_rest',
        'permission_callback' => '__return_true',
        'args' => array( 'city' => array( 'required' => true ) ),
    ) );
}
add_action( 'rest_api_init', 'weatherblock_register_rest_routes' );

/**
 * REST API Callback: Uses the centralized helper function
 */
function weatherblock_get_weather_rest( $request ) {
    $city = $request->get_param( 'city' );
    $data = weatherblock_fetch_from_api( $city );

    if ( is_wp_error( $data ) ) {
        return $data;
    }

    return array( 'success' => true, 'data' => $data );
}

function render_weather_widget( $attributes, $content, $block ) {
    $cityname = isset($attributes['cityName']) ? $attributes['cityName'] : 'managua';
    $data = weatherblock_fetch_from_api( $cityname );

    if ( is_wp_error( $data ) ) {
        return '<p>Weather data unavailable: ' . esc_html($data->get_error_message()) . '</p>';
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