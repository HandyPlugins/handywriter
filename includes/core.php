<?php
/**
 * Core plugin functionality.
 *
 * @package Handywriter
 */

namespace Handywriter\Core;

use \WP_Error;
use Handywriter\Utils;
use const Handywriter\Constants\CREDIT_USAGE_TRANSIENT;
use const Handywriter\Constants\HISTORY_CRON_HOOK;
use const Handywriter\Constants\LICENSE_INFO_TRANSIENT;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Default setup routine
 *
 * @return void
 */
function setup() {
	add_action( 'init', __NAMESPACE__ . '\\i18n' );
	add_action( 'init', __NAMESPACE__ . '\\init' );
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\admin_scripts' );
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\admin_styles' );
	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\block_editor_assets' );

	do_action( 'handywriter_loaded' );
}

/**
 * Registers the default textdomain.
 *
 * @return void
 */
function i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'handywriter' );
	load_textdomain( 'handywriter', WP_LANG_DIR . '/handywriter/handywriter-' . $locale . '.mo' );
	load_plugin_textdomain( 'handywriter', false, plugin_basename( HANDYWRITER_PATH ) . '/languages/' );
}

/**
 * Initializes the plugin and fires an action other plugins can hook into.
 *
 * @return void
 */
function init() {
	do_action( 'handywriter_init' );
}

/**
 * The list of knows contexts for enqueuing scripts/styles.
 *
 * @return array
 */
function get_enqueue_contexts() {
	return [
		'admin',
		'frontend',
		'shared',
		'editorial',
		'classic-editor',
		'block-editor',
		'tts',
	];
}

/**
 * Generate an URL to a script, taking into account whether SCRIPT_DEBUG is enabled.
 *
 * @param string $script  Script file name (no .js extension)
 * @param string $context Context for the script ('admin', 'frontend', or 'shared')
 *
 * @return string|WP_Error URL
 */
function script_url( $script, $context ) {
	if ( ! in_array( $context, get_enqueue_contexts(), true ) ) {
		return new WP_Error( 'invalid_enqueue_context', 'Invalid $context specified in HandyWriter script loader.' );
	}

	return HANDYWRITER_URL . "dist/js/{$script}.js";

}

/**
 * Generate an URL to a stylesheet, taking into account whether SCRIPT_DEBUG is enabled.
 *
 * @param string $stylesheet Stylesheet file name (no .css extension)
 * @param string $context    Context for the script ('admin', 'frontend', or 'shared')
 *
 * @return string|\WP_Error URL or WP Error
 * @since 1.0
 */
function style_url( $stylesheet, $context ) {
	if ( ! in_array( $context, get_enqueue_contexts(), true ) ) {
		return new WP_Error( 'invalid_enqueue_context', 'Invalid $context specified in HandyWriter stylesheet loader.' );
	}

	return HANDYWRITER_URL . "dist/css/{$stylesheet}.css";
}

/**
 * Enqueue scripts for admin.
 *
 * @param string $hook hookname
 *
 * @return void
 */
function admin_scripts( $hook ) {
	if ( ! current_user_can( Utils\get_required_capability() ) ) {
		return;
	}

	$classic_editor_hooks = [ 'post-new.php', 'post.php' ];

	if ( in_array( $hook, $classic_editor_hooks, true ) ) {
		wp_enqueue_script(
			'handywriter-classic-editor',
			script_url( 'classic-editor', 'classic-editor' ),
			[
				'jquery',
				'clipboard',
				'wp-i18n',
				'lodash',
			],
			HANDYWRITER_VERSION,
			true
		);

		wp_enqueue_script( 'handywriter-classic-editor' );

		wp_set_script_translations(
			'handywriter-classic-editor',
			'handywriter',
			plugin_dir_path( HANDYWRITER_PLUGIN_FILE ) . 'languages'
		);
	}

	if ( in_array( $hook, $classic_editor_hooks, true ) || false !== stripos( $hook, 'handywriter' ) ) {
		wp_enqueue_script(
			'handywriter-admin',
			script_url( 'admin', 'admin' ),
			[
				'jquery',
				'clipboard',
				'lodash',
				'wp-i18n',
				'wp-edit-post',
				'wp-components',
				'wp-compose',
				'wp-data',
				'wp-edit-post',
				'wp-element',
				'wp-plugins',
			],
			HANDYWRITER_VERSION,
			true
		);

		$settings = \Handywriter\Utils\get_settings();

		$current_screen  = get_current_screen();
		$is_block_editor = method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor();

		$args = [
			'enableTypewriter' => boolval( $settings['enable_typewriter'] ),
			'nonce'            => wp_create_nonce( 'handywriter_admin_nonce' ),
			'isBlockEditor'    => $is_block_editor,
		];

		wp_localize_script(
			'handywriter-admin',
			'HandywriterAdmin',
			$args
		);
	}
}

/**
 * Register block editor assets
 *
 * @return void
 * @since 1.0
 */
function block_editor_assets() {
	if ( ! current_user_can( Utils\get_required_capability() ) ) {
		return;
	}

	wp_register_script(
		'handywriter-block-editor',
		script_url( 'block-editor', 'block-editor' ),
		[
			'jquery',
			'clipboard',
			'lodash',
			'wp-i18n',
			'wp-edit-post',
			'wp-components',
			'wp-compose',
			'wp-data',
			'wp-edit-post',
			'wp-element',
			'wp-plugins',
		],
		HANDYWRITER_VERSION,
		true
	);

	wp_enqueue_script( 'handywriter-block-editor' );

	wp_set_script_translations(
		'handywriter-block-editor',
		'handywriter',
		plugin_dir_path( HANDYWRITER_PLUGIN_FILE ) . 'languages'
	);

}

/**
 * Enqueue styles for admin.
 *
 * @param string $hook Hook name
 *
 * @return void
 */
function admin_styles( $hook ) {
	$classic_editor_hooks = [ 'post-new.php', 'post.php' ];

	if ( in_array( $hook, $classic_editor_hooks, true ) || false !== stripos( $hook, 'handywriter' ) ) {
		wp_enqueue_style(
			'handywriter-admin',
			style_url( 'admin', 'admin' ),
			[],
			HANDYWRITER_VERSION
		);
	}
}

/**
 * Deactivate the plugin
 *
 * @return void
 * @since 1.0
 */
function deactivate() {
	$timestamp = wp_next_scheduled( HISTORY_CRON_HOOK );
	if ( $timestamp ) {
		wp_clear_scheduled_hook( HISTORY_CRON_HOOK );
	}

	delete_transient( LICENSE_INFO_TRANSIENT );
	delete_transient( CREDIT_USAGE_TRANSIENT );
}
