<?php
/**
 * Dashboard Page
 *
 * @package Handywriter
 */

namespace Handywriter\Admin\Dashboard;

use function Handywriter\History\add_to_history;
use function Handywriter\Utils\get_api_base_url;
use function Handywriter\Utils\get_api_err_message;
use function Handywriter\Utils\get_license_endpoint;
use function Handywriter\Utils\get_license_url;
use function Handywriter\Utils\get_max_results;
use function Handywriter\Utils\get_credit_usage;
use function Handywriter\Utils\get_license_info;
use function Handywriter\Utils\get_license_key;
use function Handywriter\Utils\get_license_status_message;
use function Handywriter\Utils\get_required_capability;
use function Handywriter\Utils\get_required_capability_for_license_details;
use const Handywriter\Constants\CREDIT_USAGE_TRANSIENT;
use const Handywriter\Constants\ICON_BASE64;
use const Handywriter\Constants\LICENSE_INFO_TRANSIENT;
use const Handywriter\Constants\LICENSE_KEY_OPTION;
use const Handywriter\Constants\MENU_SLUG;
use const Handywriter\Constants\SETTING_OPTION;

// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Setup routine
 *
 * @return void
 */
function setup() {
	if ( HANDYWRITER_IS_NETWORK ) {
		add_action( 'network_admin_menu', __NAMESPACE__ . '\\admin_menu' );
	} else {
		add_action( 'admin_menu', __NAMESPACE__ . '\\admin_menu', 8 );
	}

	add_action( 'admin_menu', __NAMESPACE__ . '\\child_admin_menu', 9 );

	add_filter( 'admin_body_class', __NAMESPACE__ . '\\add_sui_admin_body_class' );
	add_action( 'wp_ajax_handywriter_content_template_create_content', __NAMESPACE__ . '\\content_template_create_content_callback' );
	add_action( 'wp_ajax_handywriter_create_content', __NAMESPACE__ . '\\create_content_callback' );
	add_action( 'wp_ajax_handywriter_edit_content', __NAMESPACE__ . '\\edit_content_callback' );
	add_action( 'wp_ajax_handywriter_check_plagiarism', __NAMESPACE__ . '\\check_plagiarism_callback' );
	add_action( 'wp_ajax_handywriter_proofreading', __NAMESPACE__ . '\\proofreading_callback' );
	add_action( 'wp_ajax_handywriter_usage_details', __NAMESPACE__ . '\\usage_details_callback' );
	add_action( 'add_meta_boxes', __NAMESPACE__ . '\\register_meta_boxes' );
	add_action( 'admin_head', __NAMESPACE__ . '\\register_classic_editor_buttons' );
	add_action( 'admin_init', __NAMESPACE__ . '\\save_settings' );
}

/**
 * Add required class for shared UI
 *
 * @param string $classes css classes for admin area
 *
 * @return string
 * @see    https://wpmudev.github.io/shared-ui/installation/
 * @since  1.0
 */
function add_sui_admin_body_class( $classes ) {
	$classes .= ' sui-2-12-13 ';

	return $classes;
}


/**
 * Adds admin menu item
 *
 * @since 1.0
 */
function admin_menu() {
	global $handywriter_settings_page;

	$capability = HANDYWRITER_IS_NETWORK ? 'manage_network' : 'manage_options';

	$handywriter_settings_page = add_menu_page(
		esc_html__( 'Handywriter Settings', 'handywriter' ),
		esc_html__( 'Handywriter', 'handywriter' ),
		$capability,
		MENU_SLUG,
		__NAMESPACE__ . '\settings_page',
		ICON_BASE64
	);

	/**
	 * Different name submenu item, url point same address with parent.
	 */
	add_submenu_page(
		MENU_SLUG,
		esc_html__( 'Settings', 'handywriter' ),
		esc_html__( 'Settings', 'handywriter' ),
		$capability,
		MENU_SLUG
	);
}

/**
 * Add child admin menu
 * Parent settings menu and child menu items needs to be registered different due to network-wide settings consideration.
 *
 * @return void
 * @since 1.0
 */
function child_admin_menu() {
	$settings_capability = HANDYWRITER_IS_NETWORK ? 'manage_network' : 'manage_options';
	$capability          = get_required_capability();

	if ( ! HANDYWRITER_IS_NETWORK && current_user_can( $settings_capability ) ) { // parent page exists
		add_submenu_page(
			MENU_SLUG,
			esc_html__( 'Templates', 'handywriter' ),
			esc_html__( 'Templates', 'handywriter' ),
			$capability,
			MENU_SLUG . '-templates',
			__NAMESPACE__ . '\\content_templates_page'
		);
	} else {
		add_menu_page(
			esc_html__( 'Templates', 'handywriter' ),
			esc_html__( 'Handywriter', 'handywriter' ),
			$capability,
			MENU_SLUG . '-templates',
			__NAMESPACE__ . '\\content_templates_page',
			ICON_BASE64
		);
	}
}


/**
 * Settings page
 *
 * @since 1.0
 */
function settings_page() { ?>

	<main class="sui-wrap">
		<?php include HANDYWRITER_INC . 'admin/partials/header.php'; ?>
		<?php include HANDYWRITER_INC . 'admin/partials/settings.php'; ?>
		<?php include HANDYWRITER_INC . 'admin/partials/footer.php'; ?>
		<?php include HANDYWRITER_INC . 'admin/partials/modals.php'; ?>
	</main>

	<?php
}

/**
 * Create content callback
 *
 * @return void
 * @since 1.0
 */
function create_content_callback() {
	if ( ! check_ajax_referer( 'handywriter_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'Invalid ajax nonce!', 'handywriter' ) ] );
	}

	if ( ! current_user_can( get_required_capability() ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to perform this action!', 'handywriter' ) ] );
	}

	$endpoint     = get_api_base_url() . 'handywriter-api/v1/generate';
	$input        = sanitize_text_field( $_POST['input'] );
	$content_type = ( isset( $_POST['content_type'] ) ? sanitize_text_field( $_POST['content_type'] ) : '' );
	$settings     = \Handywriter\Utils\get_settings();

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
				array(
					'license_key'    => get_license_key(),
					'license_url'    => get_license_url(),
					'max_results'    => get_max_results(),
					'request_from'   => home_url(),
					'input_text'     => $input,
					'content_type'   => $content_type,
					'request_source' => 'editor',
					'language'       => $settings['language'],
					'user_id'        => get_current_user_id(),
				)
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

	$content = json_decode( wp_remote_retrieve_body( $request ), true );

	if ( ! $content['success'] ) {
		$err_code = ! empty( $content['data']['error_code'] ) ? $content['data']['error_code'] : '';

		wp_send_json_error(
			[
				'message' => get_api_err_message( $err_code ),
			]
		);
	}

	if ( empty( $content['data'] ) ) {
		wp_send_json_error(
			[
				'message' => esc_html__( 'No content generated!', 'handywriter' ),
			]
		);
	}

	if ( ! isset( $content['cache_hit'] ) || ! $content['cache_hit'] ) {
		add_to_history(
			[
				'input_text'     => $input,
				'content_type'   => $content_type,
				'request_source' => 'editor',
			],
			$content['data']['content']
		);
	}

	/**
	 * Prepare html output for classic editor.
	 */
	if ( isset( $_POST['editor'] ) && 'classic_editor' === $_POST['editor'] ) {
		$html  = '';
		$class = '';
		if ( 'suggest_title' === $content_type ) {
			$class = 'handywriter-set-title';
		} elseif ( 'summarize_content' === $content_type ) {
			$class = 'handywriter-set-summary';
		} elseif ( 'meta_description' === $content_type ) {
			$class = '';
		}

		if ( ! empty( $content['data']['content'] ) ) {
			if ( 'suggest_title' === $content_type ) {
				$html .= render_notification( esc_html__( 'Just click on one of the generated titles, and the title will be updated automatically in the editor.', 'handywriter' ) );
			} elseif ( 'summarize_content' === $content_type ) {
				$html .= render_notification( esc_html__( 'Just click on one of the generated summary, selected content will be appended to editor automatically.', 'handywriter' ) );
			}

			foreach ( $content['data']['content'] as $index => $generated_content ) {
				$el_id = 'item-' . $index;
				$html .= render_card( $el_id, $generated_content, $class );
			}
		} else {
			$html .= render_notification( esc_html__( 'No content generated!', 'handywriter' ), 'warning' );
		}

		$content['data'] = [ 'html' => $html ] + $content['data'];

	}

	if ( $content['data']['content'] ) {
		$content['data']['content'] = array_values( $content['data']['content'] ); // reindex array
	}

	wp_send_json_success( $content['data'] );
	exit;
}

/**
 * Edit given content callback
 *
 * @return void
 * @since 1.0
 */
function edit_content_callback() {
	if ( ! check_ajax_referer( 'handywriter_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'Invalid ajax nonce!', 'handywriter' ) ] );
	}

	if ( ! current_user_can( get_required_capability() ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to perform this action!', 'handywriter' ) ] );
	}

	$endpoint = get_api_base_url() . 'handywriter-api/v1/edits';
	$input    = sanitize_text_field( $_POST['input'] );

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
				array(
					'license_key'    => get_license_key(),
					'max_results'    => get_max_results(),
					'license_url'    => get_license_url(),
					'request_from'   => home_url(),
					'input_text'     => $input,
					'request_source' => 'editor',
					'user_id'        => get_current_user_id(),
				)
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

	$content = json_decode( wp_remote_retrieve_body( $request ), true );

	if ( ! $content['success'] ) {
		$err_code = ! empty( $content['data']['error_code'] ) ? $content['data']['error_code'] : '';

		wp_send_json_error(
			[
				'message' => get_api_err_message( $err_code ),
			]
		);
	}

	if ( empty( $content['data'] ) ) {
		wp_send_json_error(
			[
				'message' => esc_html__( 'The content could not edited!', 'handywriter' ),
			]
		);
	}

	if ( $content['data']['content'] ) {
		$content['data']['content'] = array_values( $content['data']['content'] ); // reindex array
	}

	wp_send_json_success( $content['data'] );
	exit;
}


/**
 * Create content callback
 *
 * @return void
 * @since 1.0
 */
function check_plagiarism_callback() {
	if ( ! check_ajax_referer( 'handywriter_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'Invalid ajax nonce!', 'handywriter' ) ] );
	}

	if ( ! current_user_can( get_required_capability() ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to perform this action!', 'handywriter' ) ] );
	}

	$endpoint = get_api_base_url() . 'handywriter-api/v1/plagiarism-check';
	$input    = sanitize_text_field( $_POST['input'] );

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
				array(
					'license_key'  => get_license_key(),
					'license_url'  => get_license_url(),
					'request_from' => home_url(),
					'input_text'   => $input,
					'user_id'      => get_current_user_id(),
				)
			),
		)
	);

	if ( is_wp_error( $request ) ) {
		wp_send_json_error( [ 'message' => $request->get_error_message() ] );
	}

	$content = json_decode( wp_remote_retrieve_body( $request ), true );

	if ( ! $content['success'] ) {
		$err_code = ! empty( $content['data']['error_code'] ) ? $content['data']['error_code'] : '';

		wp_send_json_error(
			[
				'message' => get_api_err_message( $err_code ),
			]
		);
	}

	if ( empty( $content['data'] ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'Plagiarism check is not complete!', 'handywriter' ) ] );
	}

	$classic_editor_html = '<div class="notice inline notice-success"><p>' . esc_html__( 'No plagiarism has been detected!', 'handywriter' ) . '</p></div>';
	if ( $content['data']['count'] > 0 ) {
		$classic_editor_html = '<div class="notice inline notice-warning"><p>' . esc_html__( 'Plagiarism has been detected!', 'handywriter' ) . '</p></div>';
	}

	if ( count( $content['data']['matches'] ) > 0 ) {
		$classic_editor_html .= '<ol>';
		foreach ( $content['data']['matches'] as $match ) {
			$classic_editor_html .= '<li>';
			$classic_editor_html .= '<p>';
			$classic_editor_html .= '<mark>';
			$classic_editor_html .= $match['text'];
			$classic_editor_html .= '</mark>';
			$classic_editor_html .= '</p>';
			/* translators: %s: similarity percentage*/
			$classic_editor_html .= sprintf( esc_html__( '%s%% of the similarity found on', 'handywriter' ), $match['similarity'] );
			$classic_editor_html .= ' <a target="blank" rel="noopener" href="' . esc_url( $match['url'] ) . '" class="plagiarism-link" style="overflow-wrap: break-word;" >' . $match['url'] . '</a>';

			$classic_editor_html .= '</li>';
		}
		$classic_editor_html .= '</ol>';
	}

	$content['data']['classic_editor_result'] = $classic_editor_html;

	wp_send_json_success( $content['data'] );
}

/**
 * Proofreadings callback
 *
 * @return void
 * @since 1.0
 */
function proofreading_callback() {
	if ( ! check_ajax_referer( 'handywriter_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'Invalid ajax nonce!', 'handywriter' ) ] );
	}

	if ( ! current_user_can( get_required_capability() ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to perform this action!', 'handywriter' ) ] );
	}

	$endpoint = get_api_base_url() . 'handywriter-api/v1/proofreading';

	$input = preg_replace( '#<br[/\s]*>#si', "\n", $_POST['input'] ); // convert br to new line
	$input = explode( '</p>', $input );

	foreach ( $input as $key => $value ) {
		$input[ $key ] = wp_strip_all_tags( $value );
	}

	$input           = implode( "\n\n", $input ); // end of the paragraph.
	$input           = sanitize_textarea_field( $input ); // final sanitization.
	$blocks          = parse_blocks( $_POST['input'] );
	$is_block_editor = ( ! empty( $blocks ) && ! empty( $blocks[0]['blockName'] ) );

	/**
	 * Get all text from blocks on block editor
	 */
	if ( $is_block_editor ) {
		$allowed_blocks = [ 'core/paragraph', 'core/heading' ];
		$content        = '';
		foreach ( $blocks as $block ) {
			if ( in_array( $block['blockName'], $allowed_blocks, true ) ) {
				$block_content = wp_strip_all_tags( $block['innerHTML'] );
				if ( ! empty( $block_content ) ) {
					$content .= wp_strip_all_tags( $block['innerHTML'] ) . PHP_EOL . PHP_EOL;
				}
			}
		}
		$input = $content;
	}

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
				array(
					'license_key'  => get_license_key(),
					'license_url'  => get_license_url(),
					'request_from' => home_url(),
					'input_text'   => $input,
					'user_id'      => get_current_user_id(),
				)
			),
		)
	);

	if ( is_wp_error( $request ) ) {
		wp_send_json_error( [ 'message' => $request->get_error_message() ] );
	}

	$content = json_decode( wp_remote_retrieve_body( $request ), true );

	if ( ! $content['success'] ) {
		$err_code = ! empty( $content['data']['error_code'] ) ? $content['data']['error_code'] : '';

		wp_send_json_error(
			[
				'message' => get_api_err_message( $err_code ),
			]
		);
	}

	if ( empty( $content['data'] ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'Proofreading is not complete!', 'handywriter' ) ] );
	}

	$matches = (array) $content['data']['matches'];

	$classic_editor_html = '<div class="notice inline notice-success"><p>' . esc_html__( 'No mistake has been found!', 'handywriter' ) . '</p></div>';

	if ( count( $matches ) > 0 ) {
		$classic_editor_html = '<div class="notice inline notice-warning"><p>';
		/* translators: %d: The count of proofreading suggestions */
		$classic_editor_html .= sprintf( _n( '%d suggestion.', '%d suggestions.', count( $matches ), 'handywriter' ), count( $matches ) );
		$classic_editor_html .= '</p></div>';

		$classic_editor_html .= '<ol>';
		foreach ( $matches as $match ) {

			$text               = $match['context']['text'];
			$highlight          = mb_substr( $text, $match['context']['offset'], $match['context']['length'] );
			$sentence           = wp_unslash( $match['sentence'] );
			$pre_text           = wp_unslash( mb_substr( $text, 0, $match['context']['offset'] ) );
			$after_text         = wp_unslash( mb_substr( $text, $match['context']['offset'] + $match['context']['length'] ) );
			$corrected_sentence = '';

			if ( ! empty( $match['replacements'][0] ) && ! empty( $match['replacements'][0]['value'] ) ) {
				$corrected_sentence = str_replace( $highlight, $match['replacements'][0]['value'], $sentence );
			}

			$classic_editor_html .= '<li>';
			$classic_editor_html .= '<p><b>' . esc_attr( $match['message'] ) . '</b></p>';
			$classic_editor_html .= '<p>';
			$classic_editor_html .= $pre_text;
			$classic_editor_html .= '<mark>';
			$classic_editor_html .= $highlight;
			$classic_editor_html .= '</mark>';
			$classic_editor_html .= $after_text;
			$classic_editor_html .= '</p>';

			$classic_editor_html .= '<div class="sui-wrap proofreading-item-footer">';
			$classic_editor_html .= '<button type="button" class="button sui-button sui-button-blue proofreader-highlight" data-sentence="' . wp_kses_post( $sentence ) . '">';
			$classic_editor_html .= '<span class="sui-icon-eye proofreader-icon" aria-hidden="true"></span>';
			$classic_editor_html .= '</button>';

			if ( ! empty( $corrected_sentence ) ) {
				$classic_editor_html .= '<button type="button" class="button sui-button sui-button-blue proofreader-fix" data-sentence="' . wp_kses_post( $sentence ) . '"  data-corrected-sentence="' . wp_kses_post( $corrected_sentence ) . '">';
				$classic_editor_html .= '<span class="sui-icon-check" aria-hidden="true"></span>';
				$classic_editor_html .= '</button>';
				$classic_editor_html .= '</div>';
			}

			$classic_editor_html .= '</li>';
		}
		$classic_editor_html .= '</ol>';
	}

	$content['data']['classic_editor_result'] = $classic_editor_html;

	wp_send_json_success( $content['data'] );
}

/**
 * Content Templates Page
 *
 * @return void
 * @since 1.0
 */
function content_templates_page() {
	?>
	<main class="sui-wrap">

		<?php include HANDYWRITER_INC . 'admin/partials/header.php'; ?>
		<?php include HANDYWRITER_INC . 'admin/partials/content-templates.php'; ?>
		<?php include HANDYWRITER_INC . 'admin/partials/footer.php'; ?>
	</main>
	<?php
}


/**
 * Render contents
 *
 * @param array  $contents Generated contents.
 * @param string $content_template Content template.
 * @return false|string
 * @since 1.0
 */
function render_results( $contents = [], $content_template = '' ) {
	ob_start();
	?>
	<div class="sui-box-header">
		<h3 class="sui-box-title">
			<?php /* translators: %s: The count of the results*/ ?>
			<?php printf( wp_kses_post( _n( '%s Result Generated', '%s Results Generated', count( $contents ), 'handywriter' ) ), count( $contents ) ); ?>
		</h3>
	</div>

	<?php foreach ( $contents as $index => $content ) : ?>
		<?php $id = $index + 1; ?>
		<?php $content = nl2br( $content ); ?>
		<div id="<?php printf( 'result-%d', absint( $id ) ); ?>" class="sui-box">
			<div id="<?php printf( 'result-%d-content', absint( $id ) ); ?>" class="sui-box-body">
				<p><?php echo wp_kses_post( $content ); ?></p>
			</div>

			<div class="sui-box-footer  sui-box-content-footer">
				<button type="button" class="sui-button copy-to-clipboard" data-clipboard-target="#<?php printf( 'result-%d-content', absint( $id ) ); ?>">
					<?php esc_html_e( 'Copy', 'handywriter' ); ?>
				</button>
			</div>
		</div>
	<?php endforeach; ?>
	<?php
	return ob_get_clean();
}

/**
 * Content template ajax callback
 *
 * @return void
 * @since 1.0
 */
function content_template_create_content_callback() {
	if ( ! check_ajax_referer( 'handywriter_content_template_nonce', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'Login link could not be generated because: Invalid ajax nonce!', 'handywriter' ) ] );
	}

	if ( ! current_user_can( get_required_capability() ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to perform this action!', 'handywriter' ) ] );
	}

	wp_parse_str( $_POST['formData'], $post_data );

	if ( empty( $post_data['content_template'] ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'Unknown content type.', 'handywriter' ) ] );
	}

	$endpoint = get_api_base_url() . 'handywriter-api/v1/content-template';
	$settings = \Handywriter\Utils\get_settings();

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
				array(
					'license_key'      => get_license_key(),
					'max_results'      => get_max_results(),
					'license_url'      => get_license_url(),
					'form_data'        => $post_data,
					'content_template' => $post_data['content_template'],
					'request_source'   => 'content_template',
					'user_id'          => get_current_user_id(),
					'language'         => $settings['language'],
				)
			),
		)
	);

	$content = json_decode( wp_remote_retrieve_body( $request ), true );

	if ( empty( $content['data'] ) || empty( $content['data']['content'] ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'No content has been generated!', 'handywriter' ) ] );
	}

	if ( ! isset( $content['cache_hit'] ) || ! $content['cache_hit'] ) {
		add_to_history(
			[
				'form_data'        => $post_data,
				'content_template' => $post_data['content_template'],
				'request_source'   => 'content_template',
			],
			$content['data']['content']
		);
	}

	$html_output = render_results( $content['data']['content'], $post_data['content_template'] );

	wp_send_json_success( $html_output );
}

/**
 * Register metabox for classic editor
 *
 * @return void
 * @since 1.0
 */
function register_meta_boxes() {
	global $post_type;

	if ( ! current_user_can( get_required_capability() ) ) {
		return;
	}

	$display_metabox = ( post_type_supports( $post_type, 'title' ) && post_type_supports( $post_type, 'editor' ) && 'handywriter-history' !== $post_type );

	/**
	 * Determine whether show or not show post metaboxes
	 * by defult it will be displayed if the post type supports title and editor
	 *
	 * @since  1.0
	 */
	$show_metabox = apply_filters( 'handywriter_show_metabox', $display_metabox, $post_type );

	if ( ! $show_metabox ) {
		return;
	}

	add_meta_box(
		'handywriter_post_meta',
		esc_html__( 'Handywriter Assistant', 'handywriter' ),
		__NAMESPACE__ . '\\render_metabox',
		'',
		'side',
		'high',
		[
			'__block_editor_compatible_meta_box' => false,
			'__back_compat_meta_box'             => true,
		]
	);
}

/**
 * Render metabox section for classic editor
 *
 * @return void
 * @since 1.0
 */
function render_metabox() {
	?>
	<?php wp_nonce_field( 'handywriter_admin_nonce', 'handywriter_admin_nonce' ); ?>
	<input type="hidden" name="handywriter_ajax_url" id="handywriter_ajax_url" value="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
	<main id="handywriter-classic-editor-meta-wrapper" class="sui-wrap">
		<div id="handywriter-classic-editor-meta-box" class="sui-box">
			<div id="handywriter-classic-editor-notice"></div><!-- notice wrapper -->
			<div class="sui-box-body">

				<div class="sui-form-field">
					<button role="button" class="sui-padding--hidden sui-button sui-button-ghost sui-button-blue" id="hw-write-post">
						<!-- Content Wrapper -->
						<span class="sui-loading-text"><?php esc_html_e( 'Write a Post', 'handywriter' ); ?></span>

						<!-- Spinning loading icon -->
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
					</button>
				</div>

				<div class="sui-form-field">
					<button role="button" class="sui-padding--hidden sui-button sui-button-ghost sui-button-blue" id="hw-suggest-title">
						<?php esc_html_e( 'Suggest a Title', 'handywriter' ); ?>
					</button>
				</div>

				<div class="sui-form-field">
					<button role="button" class="sui-button sui-button-ghost sui-button-blue" id="hw-create-summary">
						<?php esc_html_e( 'Create a Summary', 'handywriter' ); ?>
					</button>
				</div>

				<div class="sui-form-field">
					<button role="button" class="sui-button sui-button-ghost sui-button-blue" id="hw-create-meta-desc">
						<?php esc_html_e( 'Create a Meta Description', 'handywriter' ); ?>
					</button>
				</div>

				<div class="sui-form-field">
					<button type="button" id="hw-plagiarism-check" class="sui-button sui-button-ghost sui-button-blue">
						<?php esc_html_e( 'Plagiarism Check', 'handywriter' ); ?>
					</button>
				</div>

				<div class="sui-form-field">
					<button type="button" id="hw-proofreading" class="sui-button sui-button-ghost sui-button-blue">
						<?php esc_html_e( 'Proofreading', 'handywriter' ); ?>
					</button>
				</div>

			</div>
		</div>
		<?php include HANDYWRITER_INC . 'admin/partials/ce-modals.php'; ?>
	</main>

	<div id="plagiarism-check-results">
		<div class="plagiarism-checking" style="display: none;">
			<img src="<?php echo esc_url( admin_url() . 'images/spinner.gif' ); ?>" />
		</div>
		<div id="handywriter-plagiarism-check-results">
		</div>
	</div>

	<div id="proofreading-results">
		<div class="proofreading-checking" style="display: none;">
			<img src="<?php echo esc_url( admin_url() . 'images/spinner.gif' ); ?>" />
		</div>
		<div id="handywriter-proofreading-items">
		</div>
	</div>
	<?php

}

/**
 * Register classic editor (tinymce) buttons
 *
 * @return void
 */
function register_classic_editor_buttons() {
	if ( ! current_user_can( get_required_capability() ) ) {
		return;
	}

	// WYSIWYG mode?
	if ( 'true' === get_user_option( 'rich_editing' ) ) {
		add_filter( 'mce_external_plugins', __NAMESPACE__ . '\\add_tinymce_plugin' );
		add_filter( 'mce_buttons', __NAMESPACE__ . '\\register_tinymce_buttons' );
	}
}

/**
 * Register a custom tinymce plugin
 *
 * @param array $plugin_array TinyMCE plugins
 *
 * @return mixed
 * @since 1.0
 */
function add_tinymce_plugin( $plugin_array ) {
	$plugin_array['handywriter_classic_editor_plugin'] = HANDYWRITER_URL . 'dist/js/classic-editor-plugin.js';

	return $plugin_array;
}

/**
 * Add the buttons to the TinyMCE array of buttons that display, so they appear in the WYSIWYG editor
 *
 * @param array $buttons TinyMCE editor buttons
 *
 * @return mixed
 * @since 1.0
 */
function register_tinymce_buttons( $buttons ) {
	array_push( $buttons, 'handywriter_button' );

	return $buttons;
}

/**
 * Card template for JS callbacks in classic editor
 *
 * @param string $id          Element ID.
 * @param string $message     AI generated message
 * @param string $class_names Additional class names
 *
 * @return false|string
 * @since 1.0
 */
function render_card( $id, $message, $class_names = '' ) {
	ob_start();
	?>
	<div id="box-<?php echo esc_attr( $id ); ?>" class="sui-box">
		<div id="<?php echo esc_attr( $id ); ?>" data-clipboard-target="#<?php echo esc_attr( $id ); ?>"
			 class="sui-box-body sui-border-frame sui-tooltip copy-to-clipboard <?php echo esc_attr( $class_names ); ?>"
			 data-tooltip="<?php esc_html_e( 'Click to select this', 'handywriter' ); ?>">
			<p><?php echo esc_attr( $message ); ?></p>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Render an SUI notice message for classic editor use
 *
 * @param string $message Message
 * @param string $type    Type of notice
 * @return false|string
 * @since 1.0
 */
function render_notification( $message, $type = 'info' ) {
	ob_start();
	?>
	<div class="sui-notice sui-notice-<?php echo esc_attr( $type ); ?>">
		<div class="sui-notice-content">
			<div class="sui-notice-message">
				<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
				<p><?php echo esc_attr( $message ); ?></p>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}


/**
 * Save settings
 *
 * @return array $settings Settings
 * @since 1.0
 */
function save_settings() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$nonce = filter_input( INPUT_POST, 'handywriter_settings', FILTER_SANITIZE_SPECIAL_CHARS );
	if ( wp_verify_nonce( $nonce, 'handywriter_settings' ) ) {
		$settings                        = [];
		$settings['role']                = sanitize_text_field( filter_input( INPUT_POST, 'role', FILTER_SANITIZE_SPECIAL_CHARS ) );
		$settings['language']            = sanitize_text_field( filter_input( INPUT_POST, 'language', FILTER_SANITIZE_SPECIAL_CHARS ) );
		$settings['max_results']         = absint( filter_input( INPUT_POST, 'max_results', FILTER_SANITIZE_SPECIAL_CHARS ) );
		$settings['enable_history']      = ! empty( $_POST['enable_history'] );
		$settings['history_records_ttl'] = absint( $_POST['history_records_ttl'] );

		if ( HANDYWRITER_IS_NETWORK ) {
			update_site_option( SETTING_OPTION, $settings );
		} else {
			update_option( SETTING_OPTION, $settings );
		}

		add_settings_error( SETTING_OPTION, 'handywriter', esc_html__( 'Settings saved.', 'handywriter' ), 'success' );

		$license_key = sanitize_text_field( filter_input( INPUT_POST, 'license_key' ) );
		if ( HANDYWRITER_IS_NETWORK ) {
			update_site_option( LICENSE_KEY_OPTION, $license_key );
		} else {
			update_option( LICENSE_KEY_OPTION, $license_key, false );
		}

		if ( isset( $_POST['handywriter_license_activate'] ) ) {
			wp_remote_post(
				get_license_endpoint(),
				array(
					'timeout'   => 15,
					'sslverify' => true,
					'body'      => [
						'action'      => 'activate',
						'license_key' => $license_key,
						'license_url' => get_license_url(),
					],
				)
			);
			delete_transient( LICENSE_INFO_TRANSIENT );
			delete_transient( CREDIT_USAGE_TRANSIENT );
		} elseif ( isset( $_POST['handywriter_license_deactivate'] ) ) {
			wp_remote_post(
				get_license_endpoint(),
				array(
					'timeout'   => 15,
					'sslverify' => true,
					'body'      => [
						'action'      => 'deactivate',
						'license_key' => $license_key,
						'license_url' => get_license_url(),
					],
				)
			);
			delete_transient( LICENSE_INFO_TRANSIENT );
			delete_transient( CREDIT_USAGE_TRANSIENT );
		}

		if ( empty( $license_key ) ) {
			delete_transient( LICENSE_INFO_TRANSIENT );
			delete_transient( CREDIT_USAGE_TRANSIENT );
		}

		return $settings;
	}

}

/**
 * Ajax callback for license usage details
 *
 * @return void
 * @since 1.0
 */
function usage_details_callback() {
	if ( ! check_ajax_referer( 'handywriter_admin_nonce', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'You can not perform this action!', 'handywriter' ) ] );
	}

	if ( ! current_user_can( get_required_capability_for_license_details() ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'You can not perform this action!', 'handywriter' ) ] );
	}

	$endpoint = get_api_base_url() . 'handywriter-api/v1/usage/details';

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
				array(
					'license_key'  => get_license_key(),
					'license_url'  => get_license_url(),
					'request_from' => home_url(),
				)
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

	$content = json_decode( wp_remote_retrieve_body( $request ), true );

	if ( empty( $content['data'] ) ) {
		wp_send_json_error(
			[
				'message' => esc_html__( 'No usage info has been found!', 'handywriter' ),
			]
		);
	}

	$spendings = $content['data']['spendings'];

	ob_start();

	?>
	<?php if ( empty( $spendings ) ) : ?>
		<div role="alert" id="inline-notice-general" class="sui-notice sui-notice-warning sui-active" aria-live="assertive" style="display: block;">
			<div class="sui-notice-content">
				<div class="sui-notice-message">
					<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
					<p><?php esc_html_e( 'No record has been found yet! The details will shown here once you spend some credits.', 'handywriter' ); ?></div>
			</div>
		</div>
	<?php else : ?>
	<div role="alert" id="inline-notice-general" class="sui-notice sui-notice-blue sui-active" aria-live="assertive" style="display: block;">
		<div class="sui-notice-content">
			<div class="sui-notice-message">
				<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
				<p><?php esc_html_e( 'Shows most recent 50 usage.', 'handywriter' ); ?></div>
		</div>
	</div>

	<table class="sui-table">

		<thead>
		<tr>
			<th><?php esc_html_e( 'Time', 'handywriter' ); ?></th>
			<th><?php esc_html_e( 'Credits', 'handywriter' ); ?></th>
			<th><?php esc_html_e( 'Domain', 'handywriter' ); ?></th>
			<th><?php esc_html_e( 'Type', 'handywriter' ); ?></th>
		</tr>
		</thead>

		<tbody>
		<?php foreach ( $spendings as $spending_item ) : ?>
			<tr>
				<td data-time="<?php echo esc_attr( $spending_item['time'] ); ?>">
					<?php echo esc_attr( date_i18n( 'F j, Y H:i:s', strtotime( $spending_item['time'] ) ) ); ?>
				</td>
				<td><?php echo esc_attr( $spending_item['amount'] ); ?></td>
				<td><?php echo esc_attr( $spending_item['domain'] ); ?></td>
				<td><?php echo esc_attr( $spending_item['type'] ); ?></td>
			</tr>
		<?php endforeach; ?>

		</tbody>

		<tfoot>

		</tfoot>

	</table>
	<?php endif; ?>
	<?php
	$html = ob_get_clean();
	wp_send_json_success( [ 'html' => $html ] );
}
