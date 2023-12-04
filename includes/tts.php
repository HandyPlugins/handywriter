<?php
/**
 * Text-to-Speech functionality.
 *
 * @package Handywriter
 */

namespace Handywriter\TTS;

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
	$settings = \Handywriter\Utils\get_settings();
	if ( ! $settings['enable_tts'] ) {
		return;
	}

	add_action( 'wp_ajax_handywriter_create_audio', __NAMESPACE__ . '\\create_audio_callback' );
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\editor_assets' );
	add_action( 'media_buttons', __NAMESPACE__ . '\\add_media_buttons' );
	add_action( 'admin_footer', __NAMESPACE__ . '\\render_template' );
	add_filter( 'media_send_to_editor', __NAMESPACE__ . '\\add_audio_disclosure', 10, 2 );
}

/**
 * Add audio disclosure on classic editor
 *
 * @param string $html Attachment HTML.
 * @param int    $id Attachment ID.
 *
 * @return string
 */
function add_audio_disclosure( $html, $id ) {
	$attachment = get_post( $id );

	if ( 'audio/mpeg' === $attachment->post_mime_type ) {
		$title    = get_the_title( $id );
		$filename = sanitize_file_name( $title );
		$filename = sprintf( 'hwaudio_%s', $filename );
		$filename = apply_filters( 'handywriter_audio_filename', $filename, $id );

		if ( false !== strpos( $filename, 'hwaudio_' ) ) {
			$settings = \Handywriter\Utils\get_settings();
			if ( ! empty( $settings['tts_disclosure'] ) ) {
				$html .= '<p class="handywriter-tts-audio-disclosure">' . esc_html( $settings['tts_disclosure'] ) . '</p>';
			}
		}
	}

	return $html;
}

/**
 * Add media button to classic editor
 *
 * @param string $editor_id Current editor ID eg: content
 *
 * @return void
 */
function add_media_buttons( $editor_id ) {
	?>
	<button type="button" class="button handywriter-tts-classic-editor-btn" data-editor-id="<?php echo esc_attr( $editor_id ); ?>">
		<span class="dashicons dashicons-controls-volumeon wp-media-buttons-icon"></span>
		<?php esc_html_e( 'Text to Speech', 'handywriter' ); ?>
	</button>
	<?php
}

/**
 * Render template for voice generation UI/modal
 *
 * @return true|void
 */
function render_template() {
	global $hook_suffix;
	$allowed_pages = [ 'post-new.php', 'post.php' ];

	if ( ! in_array( $hook_suffix, $allowed_pages, true ) ) {
		return true;
	}
	$tts_models = \Handywriter\Utils\get_available_tts_models();
	$tts_voices = \Handywriter\Utils\get_available_tts_voices();

	$settings = \Handywriter\Utils\get_settings();
	?>

	<main id="handywriter-classic-editor-meta-wrapper" class="sui-wrap">
		<div class="sui-modal sui-modal-xl">
			<div role="dialog" id="handywriter-tts-modal" class="sui-modal-content" aria-live="polite" aria-modal="true" aria-labelledby="handywriter-tts-modal-title" aria-describedby="handywriter-tts-modal-desc">
				<form id="handywriter-tts-voice-generator-form">
					<?php wp_nonce_field( 'handywriter_tts_nonce', 'handywriter_tts_nonce' ); ?>

					<input type="hidden" name="post_id" value="<?php echo esc_attr( get_the_ID() ); ?>">
					<input type="hidden" name="tts_disclosure" id="tts_disclosure" value="<?php echo esc_attr( $settings['tts_disclosure'] ); ?>">
					<input type="hidden" id="handywriter-tts-editor-id" name="editor_id" value="">
					<div class="sui-box">
						<div class="sui-box-header">
							<h3 id="handywriter-tts-modal-title" class="sui-box-title"><?php esc_html_e( 'Text to Speech', 'handywriter' ); ?></h3>
							<button class="sui-button-icon sui-button-float--right" id="handywriter-tts-modal-close">
								<span class="sui-icon-close sui-md" aria-hidden="true"></span>
								<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this modal', 'handywriter' ); ?></span>
							</button>
						</div>

						<div id="handywriter-tts-modal-message" class="sui-box-body">
							<div class="sui-form-field">
								<span class="sui-settings-label" id="label-handywriter-tts-content"><?php esc_html_e( 'What text do you want to convert to voice?', 'handywriter' ); ?></span>
								<textarea
									maxlength="4000"
									name="content"
									id="handywriter-tts-content"
									class="sui-form-control"
									aria-labelledby="label-handywriter-tts-content"
									style="width: 100%;max-width: 100%;min-height: 300px;"
									required
								></textarea>
							</div>

							<div class="sui-form-field">
								<span class="sui-settings-label" id="label_handywriter_tts_voice_model"><?php esc_html_e( 'TTS Model', 'handywriter' ); ?></span>

								<select name="tts_model" id="content-language" class="sui-select">
									<?php foreach ( $tts_models as $tts_model => $model_label ) : ?>
										<option <?php selected( $tts_model, $settings['tts_model'] ); ?> value="<?php echo esc_attr( $tts_model ); ?>">
											<?php echo esc_attr( $model_label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<span class="sui-description"><?php esc_html_e( 'TTS-1 is designed for real-time text-to-speech applications, while TTS-1-HD focuses on delivering higher quality outcomes.', 'handywriter' ); ?></span>
							</div>

							<div class="sui-form-field">
								<span class="sui-settings-label" id="label_handywriter_tts_voice"><?php esc_html_e( 'TTS Voice', 'handywriter' ); ?></span>
								<select name="tts_voice" id="content-language" class="sui-select">
									<?php foreach ( $tts_voices as $tts_voice => $voice_label ) : ?>
										<option <?php selected( $tts_voice, $settings['tts_voice'] ); ?> value="<?php echo esc_attr( $tts_voice ); ?>">
											<?php echo esc_attr( $voice_label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<span class="sui-description"><?php esc_html_e( 'The voice to use when generating the audio.', 'handywriter' ); ?></span>
							</div>

						</div>

						<div class="sui-box-footer">
							<span id="handywriter_tts_result_msg" class="sui-description"></span>
							<div class="sui-actions-right">
								<button class="sui-button sui-button-blue" type="submit" id="handywriter-tts-generate-voice" aria-live="polite">
									<!-- Default State Content -->
									<span class="sui-button-text-default"><?php esc_html_e( 'Generate', 'handywriter' ); ?></span>

									<!-- Loading State Content -->
									<span class="sui-button-text-onload">
										<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
										<?php esc_html_e( 'Generating...', 'handywriter' ); ?>
									</span>

								</button>
							</div>
						</div>

					</div>
				</form>
			</div>
		</div>

	</main>
	<?php
}


/**
 * Register editor assets
 *
 * @return void
 * @since 1.3
 */
function editor_assets() {
	if ( ! current_user_can( get_required_capability() ) ) {
		return;
	}

	wp_register_script(
		'handywriter-tts',
		script_url( 'tts', 'tts' ),
		[
			'jquery',
			'lodash',
			'wp-i18n',
			'wp-edit-post',
			'wp-components',
			'wp-compose',
			'wp-data',
			'wp-edit-post',
			'wp-element',
		],
		HANDYWRITER_VERSION,
		true
	);

	wp_enqueue_script( 'handywriter-tts' );

	wp_set_script_translations(
		'handywriter-tts',
		'handywriter',
		plugin_dir_path( HANDYWRITER_PLUGIN_FILE ) . 'languages'
	);

}


/**
 * Create content callback
 *
 * @return void JSON response
 * @since 1.3
 */
function create_audio_callback() {
	if ( ! check_ajax_referer( 'handywriter_tts_nonce', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'Invalid ajax nonce!', 'handywriter' ) ] );
	}

	if ( ! current_user_can( get_required_capability() ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to perform this action!', 'handywriter' ) ] );
	}

	parse_str( wp_unslash( $_POST['data'] ), $form_data ); // phpcs:ignore

	$endpoint = get_api_base_url() . 'handywriter-api/v1/audio/speech';
	$settings = \Handywriter\Utils\get_settings();

	$audio_request_args = apply_filters(
		'handywriter_audio_request_args',
		[
			'license_key'    => get_license_key(),
			'license_url'    => get_license_url(),
			'request_from'   => home_url(),
			'input_text'     => $form_data['content'],
			'model'          => $form_data['tts_model'],
			'voice'          => $form_data['tts_voice'],
			'request_source' => 'editor',
			'language'       => $settings['language'],
			'user_id'        => get_current_user_id(),
		]
	);

	$request = wp_remote_post(
		$endpoint,
		array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'blocking'    => true,
			'headers'     => array(
				'Content-Type' => 'application/json',
			),
			'body'        => wp_json_encode(
				$audio_request_args
			),
		)
	);

	if ( is_wp_error( $request ) ) {
		wp_send_json_error(
			[
				'message' => $request->get_error_message(),
			]
		);
	}

	$response = json_decode( wp_remote_retrieve_body( $request ), true );

	if ( isset( $response['data']['audio_data'] ) ) {
		$audio_data = base64_decode( $response['data']['audio_data'] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$post_id    = 0;

		if ( isset( $form_data['post_id'] ) ) {
			$post_id = absint( $form_data['post_id'] );
		}

		if ( $post_id > 0 ) {
			$title    = get_the_title( $post_id );
			$filename = sanitize_file_name( $title );
			$filename = wp_unique_id( sprintf( 'hwaudio_%s', $filename ) );
		}

		$filename   = apply_filters( 'handywriter_audio_filename', $filename, $post_id );
		$tmp        = wp_tempnam( $filename );
		$audio_file = file_put_contents( $tmp, $audio_data ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents

		if ( ! $audio_file ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Unable to store the audio file in the temporary directory.', 'handywriter' ) ] );
		}

		$file_array = [
			'name'     => $filename . '.mp3',
			'tmp_name' => $tmp,
		];

		$attachment_id = media_handle_sideload( $file_array, $post_id );
		if ( is_wp_error( $attachment_id ) ) {
			unlink( $tmp );

			wp_send_json_error(
				[
					'message' => $attachment_id->get_error_message(),
				]
			);
		}

		wp_send_json_success(
			[
				'attachment_id'  => absint( $attachment_id ),
				'attachment_url' => wp_get_attachment_url( $attachment_id ),
			]
		);
	}

	wp_send_json_error(
		[
			'message' => esc_html__( 'Unable to generate audio file.', 'handywriter' ),
		]
	);
}
