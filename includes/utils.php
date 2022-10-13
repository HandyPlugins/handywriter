<?php
/**
 * Utility functions for the plugin.
 *
 * This file is for custom helper functions.
 * These should not be confused with WordPress template
 * tags. Template tags typically use prefixing, as opposed
 * to Namespaces.
 *
 * @link    https://developer.wordpress.org/themes/basics/template-tags/
 * @package Handywriter
 */

namespace Handywriter\Utils;

use const Handywriter\Constants\CREDIT_USAGE_TRANSIENT;
use const Handywriter\Constants\HANDYWRITER_API_BASE;
use const Handywriter\Constants\LICENSE_INFO_TRANSIENT;
use const Handywriter\Constants\SETTING_OPTION;
use const Handywriter\Constants\LICENSE_KEY_OPTION;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get asset info from extracted asset files
 *
 * @param string $slug      Asset slug as defined in build/webpack configuration
 * @param string $attribute Optional attribute to get. Can be version or dependencies
 *
 * @return string|array
 */
function get_asset_info( $slug, $attribute = null ) {
	if ( file_exists( HANDYWRITER_PATH . 'dist/js/' . $slug . '.asset.php' ) ) {
		$asset = include HANDYWRITER_PATH . 'dist/js/' . $slug . '.asset.php';
	} elseif ( file_exists( HANDYWRITER_PATH . 'dist/css/' . $slug . '.asset.php' ) ) {
		$asset = include HANDYWRITER_PATH . 'dist/css/' . $slug . '.asset.php';
	} else {
		return null;
	}

	if ( ! empty( $attribute ) && isset( $asset[ $attribute ] ) ) {
		return $asset[ $attribute ];
	}

	return $asset;
}


/**
 * Is plugin activated network wide?
 *
 * @param string $plugin_file file path
 *
 * @return bool
 * @since  1.0
 */
function is_network_wide( $plugin_file ) {
	if ( ! is_multisite() ) {
		return false;
	}

	if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
		include_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	return is_plugin_active_for_network( plugin_basename( $plugin_file ) );
}

/**
 * Get settings with defaults
 *
 * @return array
 * @since  1.0
 */
function get_settings() {
	$defaults = [
		'role'                => 'administrator',
		'max_results'         => 3,
		'enable_history'      => false,
		'history_records_ttl' => 30,
	];

	if ( HANDYWRITER_IS_NETWORK ) {
		$settings = get_site_option( SETTING_OPTION, [] );
	} else {
		$settings = get_option( SETTING_OPTION, [] );
	}

	$settings = wp_parse_args( $settings, $defaults );

	return $settings;
}

/**
 * Get license key
 *
 * @return mixed|void
 * @since 1.0
 */
function get_license_key() {
	if ( defined( 'HANDYWRITER_LICENSE_KEY' ) && HANDYWRITER_LICENSE_KEY ) {
		return HANDYWRITER_LICENSE_KEY;
	}

	if ( HANDYWRITER_IS_NETWORK ) {
		$license_key = get_site_option( LICENSE_KEY_OPTION );
	} else {
		$license_key = get_option( LICENSE_KEY_OPTION );
	}

	/**
	 * Filter license key
	 *
	 * @hook   handywriter_license_key
	 *
	 * @param  {string} $license_key License key.
	 *
	 * @return {string} New value.
	 * @since  1.0
	 */
	return apply_filters( 'handywriter_license_key', $license_key );
}

/**
 * Get license url
 *
 * @return string|null
 * @since 1.0
 */
function get_license_url() {
	$license_url = home_url();

	if ( defined( 'HANDYWRITER_LICENSE_KEY' ) && is_multisite() ) {
		$license_url = network_site_url();
	}

	return $license_url;
}


/**
 * Get license status
 *
 * @return mixed|void
 */
function get_license_info() {
	$license_info = get_transient( LICENSE_INFO_TRANSIENT );
	$license_key  = get_license_key();
	$license_url  = get_license_url();

	if ( false === $license_info && $license_key ) {
		$api_params = array(
			'action'      => 'info',
			'license_key' => $license_key,
			'license_url' => $license_url,
		);

		$response = wp_remote_post(
			\Handywriter\Utils\get_license_endpoint(),
			array(
				'timeout'   => 15,
				'sslverify' => true,
				'body'      => $api_params,
			)
		);

		$license_info = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $license_info ) {
			set_transient( LICENSE_INFO_TRANSIENT, $license_info, HOUR_IN_SECONDS * 12 );

			return $license_info;
		}

		// If the response failed, try again in 30 minutes
		$license_info = [
			'success'        => false,
			'license_status' => 'unknown',
		];

		set_transient( LICENSE_INFO_TRANSIENT, $license_info, MINUTE_IN_SECONDS * 30 );
	}

	return $license_info;
}


/**
 * Return user-readable feedback message based on the API response of license check
 *
 * @return mixed|string
 */
function get_license_status_message() {
	// API response for license check
	$license_info = get_license_info();

	if ( $license_info && 'valid' === $license_info['license_status'] ) {
		$message = esc_html__( 'Your license is valid and activated.', 'handywriter' );

		if ( isset( $license_info['expires'] ) ) {
			if ( 'lifetime' === $license_info['expires'] ) {
				$message .= esc_html__( 'Lifetime License.', 'handywriter' );
			} else {
				$message .= sprintf(
				/* translators: %s: license key expiration time */
					esc_html__( 'Your license key expires on %s.' ),
					date_i18n( get_option( 'date_format' ), strtotime( $license_info['expires'], current_time( 'timestamp' ) ) ) // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
				);
			}
		}

		if ( $license_info['site_count'] && $license_info['license_limit'] ) {
			$message .= sprintf(
			/* translators: %1$s: the number of active sites. %2$s: max sites */
				esc_html__( 'You have %1$s / %2$s sites activated.', 'handywriter' ),
				absint( $license_info['site_count'] ),
				absint( $license_info['license_limit'] )
			);
		}
	}

	if ( $license_info && isset( $license_info['errors'] ) && ! empty( $license_info['errors'] ) ) {
		// first err code
		$error_keys = array_keys( $license_info['errors'] );
		$err_code   = isset( $error_keys[0] ) ? $error_keys[0] : 'unkdown';

		switch ( $err_code ) {
			case 'missing_license_key':
				$message = esc_html__( 'License key does not exist', 'handywriter' );
				break;

			case 'expired_license_key':
				$message = sprintf(
				/* translators: %s: license key expiration time */
					__( 'Your license key expired on %s.' ),
					date_i18n( get_option( 'date_format' ), strtotime( $license_info['expires'], current_time( 'timestamp' ) ) ) // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
				);
				break;
			case 'unregistered_license_domain':
				$message = esc_html__( 'Unregistered domain address', 'handywriter' );
				break;
			case 'invalid_license_or_domain':
				$message = esc_html__( 'Invalid license or url', 'handywriter' );
				break;
			case 'can_not_add_new_domain':
				$message = esc_html__( 'Can not add a new domain.', 'handywriter' );
				break;

			default:
				$message = esc_html__( 'An error occurred, please try again.', 'handywriter' );
				break;
		}
	}

	if ( ! $license_info || ( isset( $license_info['license_status'] ) && 'unknown' === $license_info['license_status'] ) ) {
		$message = esc_html__( 'Please enter a valid license key and activate it.', 'handywriter' );
	}

	return $message;
}

/**
 * Get minimum required capability to use handywriter
 *
 * @return mixed|null
 * @since 1.0
 */
function get_required_capability() {
	$settings = \Handywriter\Utils\get_settings();

	if ( 'super_admin' === $settings['role'] ) {
		$capability = 'manage_network';
	} else {
		$capabilities = get_role( $settings['role'] )->capabilities;
		$capabilities = array_keys( $capabilities );
		$capability   = $capabilities[0];
	}

	return apply_filters( 'handywriter_required_capability', $capability );
}

/**
 * Required capability to access license usage data
 *
 * @return string
 */
function get_required_capability_for_license_details() {
	$license_capability = HANDYWRITER_IS_NETWORK ? 'manage_network' : 'manage_options';

	if ( defined( 'HANDYWRITER_LICENSE_KEY' ) && is_multisite() ) {
		$license_capability = 'manage_network';
	}

	return $license_capability;
}


/**
 * Get token usage
 *
 * @return array|mixed
 */
function get_credit_usage() {
	$usage_endpoint = get_api_base_url() . 'handywriter-api/v1/usage';
	$usage_info     = get_transient( CREDIT_USAGE_TRANSIENT );
	$license_key    = get_license_key();
	$license_url    = get_license_url();

	if ( false === $usage_info && $license_key ) {
		$api_params = array(
			'license_key' => $license_key,
			'license_url' => $license_url,
		);

		$response = wp_remote_post(
			$usage_endpoint,
			array(
				'timeout'   => 15,
				'sslverify' => true,
				'body'      => $api_params,
			)
		);

		$usage_info = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $usage_info ) {
			set_transient( CREDIT_USAGE_TRANSIENT, $usage_info, MINUTE_IN_SECONDS * 5 );

			return $usage_info;
		}

		// If the response failed, try again in 30 minutes
		$usage_info = [
			'success'        => false,
			'license_status' => 'unknown',
		];

		set_transient( CREDIT_USAGE_TRANSIENT, $usage_info, MINUTE_IN_SECONDS * 1 );
	}

	return $usage_info;
}

/**
 * Get maximum results to create per request
 *
 * @return mixed|null
 */
function get_max_results() {
	$settings = \Handywriter\Utils\get_settings();

	return apply_filters( 'handywriter_number_of_results', $settings['max_results'] );
}


/**
 * ports \settings_errors for SUI
 *
 * @param string $setting        Slug title of a specific setting
 * @param bool   $sanitize       Whether to re-sanitize the setting value before returning errors
 * @param bool   $hide_on_update Whether hide or not hide on update
 *
 * @see settings_errors
 */
function settings_errors( $setting = '', $sanitize = false, $hide_on_update = false ) {

	if ( $hide_on_update && ! empty( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$settings_errors = get_settings_errors( $setting, $sanitize );

	if ( empty( $settings_errors ) ) {
		return;
	}

	$output = '';

	foreach ( $settings_errors as $key => $details ) {
		if ( 'updated' === $details['type'] ) {
			$details['type'] = 'sui-notice-success';
		}

		if ( in_array( $details['type'], array( 'error', 'success', 'warning', 'info' ), true ) ) {
			$details['type'] = 'sui-notice-' . $details['type'];
		}

		$css_id = sprintf(
			'setting-error-%s',
			esc_attr( $details['code'] )
		);

		$css_class = sprintf(
			'sui-notice %s settings-error is-dismissible',
			esc_attr( $details['type'] )
		);

		$output .= "<div id='$css_id' class='$css_class'> \n";
		$output .= "<div class='sui-notice-content'><div class='sui-notice-message'>";
		$output .= "<span class='sui-notice-icon sui-icon-info sui-md' aria-hidden='true'></span>";
		$output .= "<p>{$details['message']}</p></div></div>";
		$output .= "</div> \n";
	}

	echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Convert API error code to message
 * Instead of fetching error message directly,
 * get the error code and convert it to a message to make it easier to translate
 *
 * @param string $err_code Error code from the API
 *
 * @return mixed|string
 * @since 1.0
 */
function get_api_err_message( $err_code = '' ) {
	switch ( $err_code ) {
		case 'license_server_err':
			$message = esc_html__( 'License server is not available.', 'handywriter' );
			break;
		case 'no_payment_info':
			$message = esc_html__( 'No payment info found.', 'handywriter' );
			break;
		case 'invalid_license':
			$message = esc_html__( 'Invalid license key.', 'handywriter' );
			break;
		case 'invalid_domain':
			$message = esc_html__( 'License is invalid for this domain.', 'handywriter' );
			break;
		case 'license_expired':
			$message = esc_html__( 'Your license key has expired.', 'handywriter' );
			break;
		case 'license_disabled':
			$message = esc_html__( 'Your license key has been disabled.', 'handywriter' );
			break;
		case 'no_credits_left':
			$message = esc_html__( 'No credits left to perform this action.', 'handywriter' );
			break;
		case 'plagiarism_service_not_available':
			$message = esc_html__( 'Plagiarism service is not available.', 'handywriter' );
			break;
		case 'no_content_generated':
			$message = esc_html__( 'No content generated!', 'handywriter' );
			break;
		case 'no_content_edited':
			$message = esc_html__( 'No content edited!', 'handywriter' );
			break;
		case 'content_generation_service_error':
			$message = esc_html__( 'Failed request for content generation.', 'handywriter' );
			break;
		case 'content_edit_service_error':
			$message = esc_html__( 'Failed request for content edit.', 'handywriter' );
			break;
		case 'plagiarism_check_service_error':
			$message = esc_html__( 'Failed request for plagiarism check!', 'handywriter' );
			break;
		case 'rate_limit_exceeded':
			$message = esc_html__( 'Rate limit exceeded! Too many request within a minute.', 'handywriter' );
			break;
		default:
			$message = esc_html__( 'Unknown error.', 'handywriter' );
	}

	return $message;
}

/**
 * Get API Base URL
 *
 * @return string
 * @since 1.0
 */
function get_api_base_url() {
	$base_url = HANDYWRITER_API_BASE;

	// override base url when it defined in wp-config.php
	if ( defined( 'HANDYWRITER_API_BASE_URL' ) && HANDYWRITER_API_BASE_URL ) {
		$base_url = HANDYWRITER_API_BASE_URL;
	}

	return $base_url;
}

/**
 * Get license endpoint
 *
 * @return string
 * @since 1.0
 */
function get_license_endpoint() {
	return get_api_base_url() . 'paddlepress-api/v1/license/';
}
