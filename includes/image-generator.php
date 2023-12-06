<?php
/**
 * Text-to-Speech functionality.
 *
 * @package Handywriter
 */

namespace Handywriter\ImageGenerator;

use function Handywriter\Core\script_url;
use function Handywriter\Utils\get_api_base_url;
use function Handywriter\Utils\get_license_key;
use function Handywriter\Utils\get_license_url;
use function Handywriter\Utils\get_required_capability;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Setup routine
 *
 * @return void
 */
function setup() {
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\admin_scripts' );
	add_action( 'wp_ajax_handywriter_image_generator', __NAMESPACE__ . '\\generate_image_callback' );
	add_action( 'wp_ajax_handywriter_image_save_to_media_library', __NAMESPACE__ . '\\save_image_to_media_library_callback' );
}

/**
 * Enqueue image generator scripts
 *
 * @param string $hook The current admin page.
 *
 * @return void
 */
function admin_scripts( $hook ) {
	if ( false === strpos( $hook, 'handywriter-image-generator' ) ) {
		return;
	}

	if ( ! current_user_can( get_required_capability() ) ) {
		return;
	}

	wp_register_script(
		'handywriter-image-generator',
		script_url( 'image-generator', 'image-generator' ),
		[
			'jquery',
			'lodash',
			'wp-i18n'
		],
		HANDYWRITER_VERSION,
		true
	);

	wp_enqueue_script( 'handywriter-image-generator' );

	wp_set_script_translations(
		'image-generator',
		'handywriter',
		plugin_dir_path( HANDYWRITER_PLUGIN_FILE ) . 'languages'
	);
}

/**
 * Generate image ajax callback
 *
 * @return void
 */
function generate_image_callback() {
	if ( ! check_ajax_referer( 'handywriter_image_generator_nonce', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'Invalid ajax nonce!', 'handywriter' ) ] );
	}

	if ( ! current_user_can( get_required_capability() ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to perform this action!', 'handywriter' ) ] );
	}

	// Ensure $_POST['data'] is set before using it.
	if ( ! isset( $_POST['data'] ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'No data provided.', 'handywriter' ) ] );
	}

	parse_str( wp_unslash( $_POST['data'] ), $form_data ); // phpcs:ignore

	// Prepare image generation request arguments.
	$image_generation_request_args = [
		'license_key'    => get_license_key(),
		'license_url'    => get_license_url(),
		'request_from'   => home_url(),
		'prompt'         => sanitize_text_field( $form_data['image_generator']['prompt'] ),
		'model'          => sanitize_text_field( $form_data['image_generator']['model'] ),
		'size'           => sanitize_text_field( $form_data['image_generator']['size'] ),
		'style'          => sanitize_text_field( $form_data['image_generator']['style'] ),
		'n'              => intval( $form_data['image_generator']['count'] ),
		'quality'        => ! empty( $form_data['image_generator']['quality'] ) ? sanitize_text_field( $form_data['image_generator']['quality'] ) : 'standard',
		'request_source' => 'image-generation-page',
		'language'       => \Handywriter\Utils\get_settings()['language'],
		'user_id'        => get_current_user_id(),
	];

	$image_generation_request_args = apply_filters(
		'handywriter_image_generation_request_args',
		$image_generation_request_args
	);

	$response = wp_remote_post(
		get_api_base_url() . 'handywriter-api/v1/images/generations',
		[
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'blocking'    => true,
			'headers'     => [ 'Content-Type' => 'application/json' ],
			'body'        => wp_json_encode( $image_generation_request_args ),
		]
	);

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( [ 'message' => $response->get_error_message() ] );
	}

	$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( isset( $response_body['data']['images'] ) && is_array( $response_body['data']['images'] ) ) {
		$images      = $response_body['data']['images'];
		$prompt      = sanitize_text_field( $form_data['image_generator']['prompt'] );
		$html_output = \Handywriter\ImageGenerator\result_set_row( $images, $prompt );

		wp_send_json_success( [ 'images' => $images, 'html' => $html_output ] );
	}

	wp_send_json_error( [ 'message' => esc_html__( 'Unable to generate image.', 'handywriter' ) ] );
}

/**
 * Save image to media library ajax callback
 *
 * @return void
 */
function save_image_to_media_library_callback() {
	if ( ! check_ajax_referer( 'handywriter_image_generator_nonce', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'Invalid ajax nonce!', 'handywriter' ) ] );
	}

	if ( ! current_user_can( get_required_capability() ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to perform this action!', 'handywriter' ) ] );
	}

	if ( ! isset( $_POST['image_url'] ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'No image url provided.', 'handywriter' ) ] );
	}

	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );


	$image_url      = esc_url_raw( $_POST['image_url'] ); // phpcs:ignore
	$prompt         = ! empty( $_POST['prompt'] ) ? sanitize_text_field( $_POST['prompt'] ) : '';
	$path           = wp_parse_url( $image_url, PHP_URL_PATH );
	$filename       = basename( $path );
	$file_extension = pathinfo( $filename, PATHINFO_EXTENSION );

	$tmp        = download_url( $image_url );
	$file_array = array(
		'name'     => sanitize_file_name( $filename ),
		'tmp_name' => $tmp
	);

	if ( is_wp_error( $tmp ) ) {
		@unlink( $file_array['tmp_name'] );
		wp_send_json_error( [ 'message' => $tmp->get_error_message() ] );
	}

	// make a nicer title for the image
	$words       = explode( ',', $prompt );
	$words       = array_map( 'trim', $words );
	$words       = count( $words ) > 1 ? array_slice( $words, 0, 2 ) : array_slice( $words, 0, 1 );
	$words       = implode( '-', $words );
	$image_title = sanitize_file_name( $words );
	$image_title = $image_title . '.' . $file_extension;

	$id = media_handle_sideload( $file_array, 0, $image_title, [ 'post_excerpt' => $prompt ] );
	if ( is_wp_error( $id ) ) {
		@unlink( $file_array['tmp_name'] );
		wp_send_json_error( [ 'message' => $id->get_error_message() ] );
	}

	wp_send_json_success( [ 'message' => esc_html__( 'Image successfully saved to media library.', 'handywriter' ) ] );
}

/**
 * Result set row
 *
 * @param array  $images Image urls.
 * @param string $prompt Image prompt.
 *
 * @return false|string
 * @since 1.3
 */
function result_set_row( $images, $prompt ) {
	ob_start();
	?>
	<div class="hw-separator"><?php echo gmdate( 'M d, h:i A', current_time( 'timestamp' ) ) ?></div>
	<span class="sui-label"><?php echo esc_html( $prompt ); ?></span>

	<div class="sui-row hw-generated-image-result-set">
		<?php foreach ( $images as $image ): ?>
			<?php echo image_card( $image, $prompt ); ?>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Image card
 *
 * @param string $url    Image url. (this urls are temporary and will be deleted after an hour)
 * @param string $prompt Image prompt.
 *
 * @return false|string
 * @since 1.3
 */
function image_card( $url, $prompt = '' ) {
	ob_start();
	?>
	<div class="image-card">
		<div class="image-item">
			<a href="<?php echo esc_url( add_query_arg( 'TB_iframe', 'true', $url ) ); ?>" class="thickbox">
				<img src="<?php echo esc_url( $url ); ?>">
			</a>
		</div>

		<div class="image-card-btn-group" data-image-url="<?php echo esc_url( $url ); ?>" data-image-prompt="<?php echo esc_html( $prompt ); ?>">

			<button class="sui-button sui-button-blue hw-save-generated-image-btn" aria-live="polite" type="button">
				<!-- Default State Content -->
				<span class="sui-button-text-default">
					<span class="sui-icon-download" aria-hidden="true"></span>
					<?php esc_html_e( 'Save to Media Library', 'handywriter' ); ?>
				</span>

				<!-- Loading State Content -->
				<span class="sui-button-text-onload">
					<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
					<?php esc_html_e( 'Saving to media library...', 'handywriter' ); ?>
				</span>

				<!-- Button label for screen readers -->
				<span class="sui-screen-reader-text"><?php esc_html_e( 'Save to Media Library', 'handywriter' ); ?></span>
			</button>
		</div>
	</div>

	<?php
	return ob_get_clean();
}
