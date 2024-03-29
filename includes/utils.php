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
		'language'            => 'en_US',
		'max_results'         => 3,
		'enable_history'      => false,
		'history_records_ttl' => 30,
		'enable_typewriter'   => true,
		'enable_tts'          => true,
		'tts_disclosure'      => esc_html__( 'The voice you are hearing is generated by AI technology, not a human.', 'handywriter' ),
		'tts_model'           => 'tts-1-hd',
		'tts_voice'           => 'alloy',
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
		case 'audio_speech_service_error':
			$message = esc_html__( 'Failed request for audio generation!', 'handywriter' );
			break;
		case 'no_audio_generated':
			$message = esc_html__( 'No audio generated!', 'handywriter' );
			break;
		case 'character_limit_exceeded':
			$message = esc_html__( 'Character limit exceeded!', 'handywriter' );
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

/**
 * Get available languages
 * Some languages might not  be fully supported, not need to restrict AI capability
 *
 * @link https://gist.github.com/danielbachhuber/14af08c5faac07d5c0c182eb66b19b3e
 *
 * @return array
 */
function get_available_languages() {
	$languages = [
		'af'             => [
			'language'     => 'af',
			'english_name' => 'Afrikaans',
			'native_name'  => 'Afrikaans',
		],
		'ar'             => [
			'language'     => 'ar',
			'english_name' => 'Arabic',
			'native_name'  => 'العربية',
		],
		'ary'            => [
			'language'     => 'ary',
			'english_name' => 'Moroccan Arabic',
			'native_name'  => 'العربية المغربية',
		],
		'as'             => [
			'language'     => 'as',
			'english_name' => 'Assamese',
			'native_name'  => 'অসমীয়া',
		],
		'az'             => [
			'language'     => 'az',
			'english_name' => 'Azerbaijani',
			'native_name'  => 'Azərbaycan dili',
		],
		'azb'            => [
			'language'     => 'azb',
			'english_name' => 'South Azerbaijani',
			'native_name'  => 'گؤنئی آذربایجان',
		],
		'bel'            => [
			'language'     => 'bel',
			'english_name' => 'Belarusian',
			'native_name'  => 'Беларуская мова',
		],
		'bg_BG'          => [
			'language'     => 'bg_BG',
			'english_name' => 'Bulgarian',
			'native_name'  => 'Български',
		],
		'bn_BD'          => [
			'language'     => 'bn_BD',
			'english_name' => 'Bengali (Bangladesh)',
			'native_name'  => 'বাংলা',

		],
		'bo'             => [
			'language'     => 'bo',
			'english_name' => 'Tibetan',
			'native_name'  => 'བོད་ཡིག',
		],
		'bs_BA'          => [
			'language'     => 'bs_BA',
			'english_name' => 'Bosnian',
			'native_name'  => 'Bosanski',
		],
		'ca'             => [
			'language'     => 'ca',
			'english_name' => 'Catalan',
			'native_name'  => 'Català',
		],
		'ceb'            => [
			'language'     => 'ceb',
			'english_name' => 'Cebuano',
			'native_name'  => 'Cebuano',
		],
		'ckb'            => [
			'language'     => 'ckb',
			'english_name' => 'Kurdish (Sorani)',
			'native_name'  => 'كوردی‎',
		],
		'co'             => [
			'language'     => 'co',
			'english_name' => 'Corsican',
			'native_name'  => 'Corsu',
		],
		'cs_CZ'          => [
			'language'     => 'cs_CZ',
			'english_name' => 'Czech',
			'native_name'  => 'Čeština',
		],
		'cy'             => [
			'language'     => 'cy',
			'english_name' => 'Welsh',
			'native_name'  => 'Cymraeg',
		],
		'da_DK'          => [
			'language'     => 'da_DK',
			'english_name' => 'Danish',
			'native_name'  => 'Dansk',
		],
		'de_AT'          => [
			'language'     => 'de_AT',
			'english_name' => 'German (Austria)',
			'native_name'  => 'Deutsch (Österreich)',
		],
		'de_CH'          => [
			'language'     => 'de_CH',
			'english_name' => 'German (Switzerland)',
			'native_name'  => 'Deutsch (Schweiz)',
		],
		'de_CH_informal' => [
			'language'     => 'de_CH_informal',
			'english_name' => 'German (Switzerland, Informal)',
			'native_name'  => 'Deutsch (Schweiz, Du)',
		],
		'de_DE'          => [
			'language'     => 'de_DE',
			'english_name' => 'German',
			'native_name'  => 'Deutsch',
		],
		'de_DE_formal'   => [
			'language'     => 'de_DE_formal',
			'english_name' => 'German (Formal)',
			'native_name'  => 'Deutsch (Sie)',
		],
		'dzo'            => [
			'language'     => 'dzo',
			'english_name' => 'Dzongkha',
			'native_name'  => 'རྫོང་ཁ',
		],
		'el'             => [
			'language'     => 'el',
			'english_name' => 'Greek',
			'native_name'  => 'Ελληνικά',
		],
		'en_AU'          => [
			'language'     => 'en_AU',
			'english_name' => 'English (Australia)',
			'native_name'  => 'English (Australia)',
		],
		'en_CA'          => [
			'language'     => 'en_CA',
			'english_name' => 'English (Canada)',
			'native_name'  => 'English (Canada)',
		],
		'en_GB'          => [
			'language'     => 'en_GB',
			'english_name' => 'English (UK)',
			'native_name'  => 'English (UK)',
		],
		'en_NZ'          => [
			'language'     => 'en_NZ',
			'english_name' => 'English (New Zealand)',
			'native_name'  => 'English (New Zealand)',
		],
		'en_US'          => [
			'language'     => 'en_US',
			'english_name' => 'English (United States)',
			'native_name'  => 'English (United States)',
		],
		'en_ZA'          => [
			'language'     => 'en_ZA',
			'english_name' => 'English (South Africa)',
			'native_name'  => 'English (South Africa)',
		],
		'eo'             => [
			'language'     => 'eo',
			'english_name' => 'Esperanto',
			'native_name'  => 'Esperanto',
		],
		'es_AR'          => [
			'language'     => 'es_AR',
			'english_name' => 'Spanish (Argentina)',
			'native_name'  => 'Español de Argentina',
		],
		'es_CL'          => [
			'language'     => 'es_CL',
			'english_name' => 'Spanish (Chile)',
			'native_name'  => 'Español de Chile',
		],
		'es_CO'          => [
			'language'     => 'es_CO',
			'english_name' => 'Spanish (Colombia)',
			'native_name'  => 'Español de Colombia',
		],
		'es_ES'          => [
			'language'     => 'es_ES',
			'english_name' => 'Spanish (Spain)',
			'native_name'  => 'Español',
		],
		'es_GT'          => [
			'language'     => 'es_GT',
			'english_name' => 'Spanish (Guatemala)',
			'native_name'  => 'Español de Guatemala',
		],
		'es_MX'          => [
			'language'     => 'es_MX',
			'english_name' => 'Spanish (Mexico)',
			'native_name'  => 'Español de México',
		],
		'es_PE'          => [
			'language'     => 'es_PE',
			'english_name' => 'Spanish (Peru)',
			'native_name'  => 'Español de Perú',
		],
		'es_UY'          => [
			'language'     => 'es_UY',
			'english_name' => 'Spanish (Uruguay)',
			'native_name'  => 'Español de Uruguay',
		],
		'es_VE'          => [
			'language'     => 'es_VE',
			'english_name' => 'Spanish (Venezuela)',
			'native_name'  => 'Español de Venezuela',
		],
		'et'             => [
			'language'     => 'et',
			'english_name' => 'Estonian',
			'native_name'  => 'Eesti',
		],
		'eu'             => [
			'language'     => 'eu',
			'english_name' => 'Basque',
			'native_name'  => 'Euskara',
		],
		'fa_IR'          => [
			'language'     => 'fa_IR',
			'english_name' => 'Persian',
			'native_name'  => 'فارسی',
		],
		'fi'             => [
			'language'     => 'fi',
			'english_name' => 'Finnish',
			'native_name'  => 'Suomi',
		],
		'fr_BE'          => [
			'language'     => 'fr_BE',
			'english_name' => 'French (Belgium)',
			'native_name'  => 'Français de Belgique',
		],
		'fr_CA'          => [
			'language'     => 'fr_CA',
			'english_name' => 'French (Canada)',
			'native_name'  => 'Français du Canada',
		],
		'fr_FR'          => [
			'language'     => 'fr_FR',
			'english_name' => 'French (France)',
			'native_name'  => 'Français',
		],
		'fur'            => [
			'language'     => 'fur',
			'english_name' => 'Friulian',
			'native_name'  => 'Friulian',
		],
		'gd'             => [
			'language'     => 'gd',
			'english_name' => 'Scottish Gaelic',
			'native_name'  => 'Gàidhlig',
		],
		'gl_ES'          => [
			'language'     => 'gl_ES',
			'english_name' => 'Galician',
			'native_name'  => 'Galego',
		],
		'gu'             => [
			'language'     => 'gu',
			'english_name' => 'Gujarati',
			'native_name'  => 'ગુજરાતી',
		],
		'haz'            => [
			'language'     => 'haz',
			'english_name' => 'Hazaragi',
			'native_name'  => 'هزاره گی',
		],
		'he_IL'          => [
			'language'     => 'he_IL',
			'english_name' => 'Hebrew',
			'native_name'  => 'עברית',
		],
		'hi_IN'          => [
			'language'     => 'hi_IN',
			'english_name' => 'Hindi',
			'native_name'  => 'हिन्दी',
		],
		'hr'             => [
			'language'     => 'hr',
			'english_name' => 'Croatian',
			'native_name'  => 'Hrvatski',
		],
		'hsb'            => [
			'language'     => 'hsb',
			'english_name' => 'Upper Sorbian',
			'native_name'  => 'Hornjoserbšćina',
		],
		'hu_HU'          => [
			'language'     => 'hu_HU',
			'english_name' => 'Hungarian',
			'native_name'  => 'Magyar',
		],
		'hy'             => [
			'language'     => 'hy',
			'english_name' => 'Armenian',
			'native_name'  => 'Հայերեն',
		],
		'id_ID'          => [
			'language'     => 'id_ID',
			'english_name' => 'Indonesian',
			'native_name'  => 'Bahasa Indonesia',
		],
		'is_IS'          => [
			'language'     => 'is_IS',
			'english_name' => 'Icelandic',
			'native_name'  => 'Íslenska',
		],
		'it_IT'          => [
			'language'     => 'it_IT',
			'english_name' => 'Italian',
			'native_name'  => 'Italiano',
		],
		'ja'             => [
			'language'     => 'ja',
			'english_name' => 'Japanese',
			'native_name'  => '日本語',
		],
		'jv_ID'          => [
			'language'     => 'jv_ID',
			'english_name' => 'Javanese',
			'native_name'  => 'Basa Jawa',
		],
		'kab'            => [
			'language'     => 'kab',
			'english_name' => 'Kabyle',
			'native_name'  => 'Taqbaylit',
		],
		'ka_GE'          => [
			'language'     => 'ka_GE',
			'english_name' => 'Georgian',
			'native_name'  => 'ქართული',
		],
		'kk'             => [
			'language'     => 'kk',
			'english_name' => 'Kazakh',
			'native_name'  => 'Қазақ тілі',
		],
		'km'             => [
			'language'     => 'km',
			'english_name' => 'Khmer',
			'native_name'  => 'ភាសាខ្មែរ',
		],
		'kn'             => [
			'language'     => 'kn',
			'english_name' => 'Kannada',
			'native_name'  => 'ಕನ್ನಡ',
		],
		'ko_KR'          => [
			'language'     => 'ko_KR',
			'english_name' => 'Korean',
			'native_name'  => '한국어',
		],
		'lo'             => [
			'language'     => 'lo',
			'english_name' => 'Lao',
			'native_name'  => 'ພາສາລາວ',
		],
		'lt_LT'          => [
			'language'     => 'lt_LT',
			'english_name' => 'Lithuanian',
			'native_name'  => 'Lietuvių kalba',
		],
		'lv'             => [
			'language'     => 'lv',
			'english_name' => 'Latvian',
			'native_name'  => 'Latviešu valoda',
		],
		'mk_MK'          => [
			'language'     => 'mk_MK',
			'english_name' => 'Macedonian',
			'native_name'  => 'Македонски јазик',
		],
		'ml_IN'          => [
			'language'     => 'ml_IN',
			'english_name' => 'Malayalam',
			'native_name'  => 'മലയാളം',
		],
		'mn'             => [
			'language'     => 'mn',
			'english_name' => 'Mongolian',
			'native_name'  => 'Монгол',
		],
		'mr'             => [
			'language'     => 'mr',
			'english_name' => 'Marathi',
			'native_name'  => 'मराठी',
		],
		'ms_MY'          => [
			'language'     => 'ms_MY',
			'english_name' => 'Malay',
			'native_name'  => 'Bahasa Melayu',
		],
		'my_MM'          => [
			'language'     => 'my_MM',
			'english_name' => 'Myanmar (Burmese)',
			'native_name'  => 'ဗမာစာ',
		],
		'nb_NO'          => [
			'language'     => 'nb_NO',
			'english_name' => 'Norwegian (Bokmål)',
			'native_name'  => 'Norsk bokmål',
		],
		'ne_NP'          => [
			'language'     => 'ne_NP',
			'english_name' => 'Nepali',
			'native_name'  => 'नेपाली',
		],
		'nl_BE'          => [
			'language'     => 'nl_BE',
			'english_name' => 'Dutch (Belgium)',
			'native_name'  => 'Nederlands (België)',
		],
		'nl_NL'          => [
			'language'     => 'nl_NL',
			'english_name' => 'Dutch',
			'native_name'  => 'Nederlands',
		],
		'nl_NL_formal'   => [
			'language'     => 'nl_NL_formal',
			'english_name' => 'Dutch (Formal)',
			'native_name'  => 'Nederlands (Formeel)',
		],
		'nn_NO'          => [
			'language'     => 'nn_NO',
			'english_name' => 'Norwegian (Nynorsk)',
			'native_name'  => 'Norsk nynorsk',
		],
		'oci'            => [
			'language'     => 'oci',
			'english_name' => 'Occitan',
			'native_name'  => 'Occitan',
		],
		'pa_IN'          => [
			'language'     => 'pa_IN',
			'english_name' => 'Punjabi',
			'native_name'  => 'ਪੰਜਾਬੀ',
		],
		'pl_PL'          => [
			'language'     => 'pl_PL',
			'english_name' => 'Polish',
			'native_name'  => 'Polski',
		],
		'ps'             => [
			'language'     => 'ps',
			'english_name' => 'Pashto',
			'native_name'  => 'پښتو',
		],
		'pt_AO'          => [
			'language'     => 'pt_AO',
			'english_name' => 'Portuguese (Angola)',
			'native_name'  => 'Português de Angola',
		],
		'pt_BR'          => [
			'language'     => 'pt_BR',
			'english_name' => 'Portuguese (Brazil)',
			'native_name'  => 'Português do Brasil',
		],
		'pt_PT'          => [
			'language'     => 'pt_PT',
			'english_name' => 'Portuguese (Portugal)',
			'native_name'  => 'Português',
		],
		'pt_PT_ao90'     => [
			'language'     => 'pt_PT_ao90',
			'english_name' => 'Portuguese (Portugal, AO90)',
			'native_name'  => 'Português (AO90)',
		],
		'rhg'            => [
			'language'     => 'rhg',
			'english_name' => 'Rohingya',
			'native_name'  => 'Ruáinga',
		],
		'ro_RO'          => [
			'language'     => 'ro_RO',
			'english_name' => 'Romanian',
			'native_name'  => 'Română',
		],
		'ru_RU'          => [
			'language'     => 'ru_RU',
			'english_name' => 'Russian',
			'native_name'  => 'Русский',
		],
		'sah'            => [
			'language'     => 'sah',
			'english_name' => 'Yakut',
			'native_name'  => 'Саха тыла',
		],
		'si_LK'          => [
			'language'     => 'si_LK',
			'english_name' => 'Sinhala',
			'native_name'  => 'සිංහල',
		],
		'skr'            => [
			'language'     => 'skr',
			'english_name' => 'Saraiki',
			'native_name'  => 'سرائیکی',
		],
		'sk_SK'          => [
			'language'     => 'sk_SK',
			'english_name' => 'Slovak',
			'native_name'  => 'Slovenčina',
		],
		'sl_SI'          => [
			'language'     => 'sl_SI',
			'english_name' => 'Slovenian',
			'native_name'  => 'Slovenščina',
		],
		'snd'            => [
			'language'     => 'snd',
			'english_name' => 'Sindhi',
			'native_name'  => 'سنڌي',
		],
		'sq'             => [
			'language'     => 'sq',
			'english_name' => 'Albanian',
			'native_name'  => 'Shqip',
		],
		'sr_RS'          => [
			'language'     => 'sr_RS',
			'english_name' => 'Serbian',
			'native_name'  => 'Српски језик',
		],
		'sv_SE'          => [
			'language'     => 'sv_SE',
			'english_name' => 'Swedish',
			'native_name'  => 'Svenska',
		],
		'sw'             => [
			'language'     => 'sw',
			'english_name' => 'Swahili',
			'native_name'  => 'Kiswahili',
		],
		'szl'            => [
			'language'     => 'szl',
			'english_name' => 'Silesian',
			'native_name'  => 'Ślůnski',
		],
		'tah'            => [
			'language'     => 'tah',
			'english_name' => 'Tahitian',
			'native_name'  => 'Reo Tahiti',
		],
		'ta_IN'          => [
			'language'     => 'ta_IN',
			'english_name' => 'Tamil',
			'native_name'  => 'தமிழ்',
		],
		'te'             => [
			'language'     => 'te',
			'english_name' => 'Telugu',
			'native_name'  => 'తెలుగు',
		],
		'th'             => [
			'language'     => 'th',
			'english_name' => 'Thai',
			'native_name'  => 'ไทย',
		],
		'tl'             => [
			'language'     => 'tl',
			'english_name' => 'Tagalog',
			'native_name'  => 'Tagalog',
		],
		'tr_TR'          => [
			'language'     => 'tr_TR',
			'english_name' => 'Turkish',
			'native_name'  => 'Türkçe',
		],
		'tt_RU'          => [
			'language'     => 'tt_RU',
			'english_name' => 'Tatar',
			'native_name'  => 'Татарча',
		],
		'ug_CN'          => [
			'language'     => 'ug_CN',
			'english_name' => 'Uyghur',
			'native_name'  => 'ئۇيغۇرچە',
		],
		'uk'             => [
			'language'     => 'uk',
			'english_name' => 'Ukrainian',
			'native_name'  => 'Українська',
		],
		'ur'             => [
			'language'     => 'ur',
			'english_name' => 'Urdu',
			'native_name'  => 'اردو',
		],
		'uz_UZ'          => [
			'language'     => 'uz_UZ',
			'english_name' => 'Uzbek',
			'native_name'  => 'O‘zbekcha',
		],
		'vi'             => [
			'language'     => 'vi',
			'english_name' => 'Vietnamese',
			'native_name'  => 'Tiếng Việt',
		],
		'zh_CN'          => [
			'language'     => 'zh_CN',
			'english_name' => 'Chinese (China)',
			'native_name'  => '简体中文',
		],
		'zh_HK'          => [
			'language'     => 'zh_HK',
			'english_name' => 'Chinese (Hong Kong)',
			'native_name'  => '"香港中文版',
		],
		'zh_TW'          => [
			'language'     => 'zh_TW',
			'english_name' => 'Chinese (Taiwan)',
			'native_name'  => '繁體中文',
		],
	];

	return (array) apply_filters( 'handywriter_available_languages', $languages );
}


/**
 * Mask given string
 *
 * @param string $input_string  String
 * @param int    $unmask_length The length of unmask
 *
 * @return string
 * @since 1.2.1
 */
function mask_string( $input_string, $unmask_length ) {
	$output_string = substr( $input_string, 0, $unmask_length );

	if ( strlen( $input_string ) > $unmask_length ) {
		$output_string .= str_repeat( '*', strlen( $input_string ) - $unmask_length );
	}

	return $output_string;
}

/**
 * Get available TTS models
 *
 * @return array
 * @since 1.3
 */
function get_available_tts_models() {
	return [
		'tts-1'    => 'Text-to-speech 1',
		'tts-1-hd' => 'Text-to-speech 1 HD',
	];
}

/**
 * Get available TTS voices
 *
 * @return array
 * @since 1.3
 */
function get_available_tts_voices() {
	return [
		'alloy'   => 'Alloy',
		'echo'    => 'Echo',
		'fable'   => 'Fable',
		'onyx'    => 'Onyx',
		'nova'    => 'Nova',
		'shimmer' => 'Shimmer',
	];
}

/**
 * Get available image models
 *
 * @return array
 * @since 1.3
 */
function get_available_image_models() {
	return [
		'dall-e-3' => 'DALL-E 3',
		'dall-e-2' => 'DALL-E 2',
	];
}

/**
 * Get available image styles
 *
 * @return array
 * @since 1.3
 */
function get_available_image_styles() {
	return [
		'vivid'   => esc_html__( 'Vivid', 'handywriter' ),
		'natural' => esc_html__( 'Natural', 'handywriter' ),
	];
}
