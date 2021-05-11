<?php
/**
 * Plugin Name: Quiet > JWT Authentication for WP-API
 * Plugin URI: https://github.com/hideki-masuoka/quiet-wp-api-jwt-auth
 * Description: Fix > Notice: register_rest_route was called incorrectly. #207
 * Version: 1.0.0
 * Author: fkuMnk
 * Author URI: https://github.com/hideki-masuoka
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: quiet-wp-api-jwt-auth
 * Domain Path: /i18n/languages/
 * Requires at least: 5.7
 *
 * @package QuietJWTAuth4WPRESTAPI
 */

namespace QuietJWTAuth4WPRESTAPI;

defined( 'ABSPATH' ) || exit;


/**
 * Check wp-api-jwt-auth version
 *
 * @return bool
 */
function check_version() {
	$jwt_auth_version = '1.2.6';
	$jwt_auth         = 'jwt-authentication-for-wp-rest-api/jwt-auth.php';
	if ( is_plugin_active( $jwt_auth ) ) {
		$data = get_plugin_data( plugin_dir_path( __DIR__ ) . $jwt_auth );
		return $jwt_auth_version === $data['Version'] ? true : false;
	}
	return false;
}

/**
 * Fix wp-api-jwt-auth
 * https://github.com/Tmeister/wp-api-jwt-auth/issues/207
 */
add_action(
	'rest_api_init',
	function () {
		if ( ! check_version() ) {
			return;
		}

		$jwt_auth = new \Jwt_Auth_Public( 'jwt-auth', '1' );
		register_rest_route(
			'jwt-auth/v1',
			'/token',
			array(
				'methods'             => 'POST',
				'callback'            => array( $jwt_auth, 'generate_token' ),
				'permission_callback' => '__return_true',
			),
			true
		);

		register_rest_route(
			'jwt-auth/v1',
			'/token/validate',
			array(
				'methods'             => 'POST',
				'callback'            => array( $jwt_auth, 'validate_token' ),
				'permission_callback' => '__return_true',
			),
			true
		);
	}
);

/**
 * Remove problematic function
 */
add_action(
	'plugins_loaded',
	function () {
		if ( ! check_version() ) {
			return;
		}

		global $wp_filter;
		$tag      = 'rest_api_init';
		$function = 'add_api_routes';

		if (
		isset( $wp_filter[ $tag ]->callbacks[10] ) &&
		! empty( $wp_filter[ $tag ]->callbacks[10] )
		) {
			$wp_filter[ $tag ]->callbacks[10] = array_filter(
				$wp_filter[ $tag ]->callbacks[10],
				function ( $v, $k ) use ( $function ) {
					return ( stripos( $k, $function ) === false );
				},
				ARRAY_FILTER_USE_BOTH
			);
		}
	}
);
