<?php
/**
 * Core plugin functionality.
 *
 * @package Handywriter
 */

namespace Handywriter\History;

use function Handywriter\Utils\get_required_capability;
use const Handywriter\Constants\HISTORY_CRON_HOOK;
use const Handywriter\Constants\HISTORY_POST_TYPE;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Setup routine
 *
 * @return void
 * @since 1.0
 */
function setup() {
	add_action( 'init', __NAMESPACE__ . '\\register_history_post_type' );
	add_action( 'init', __NAMESPACE__ . '\\schedule_history_cleanup' );
	add_action( HISTORY_CRON_HOOK, __NAMESPACE__ . '\\cleanup_history' );
	add_filter( 'handywriter_history_post_title', __NAMESPACE__ . '\\shorten_post_title' );
}


/**
 * Register a CPT for storing history.
 *
 * @return void
 * @since 1.0
 */
function register_history_post_type() {
	$settings            = \Handywriter\Utils\get_settings();
	$settings_capability = HANDYWRITER_IS_NETWORK ? 'manage_network' : 'manage_options';
	$capability          = get_required_capability();

	if ( ! $settings['enable_history'] ) {
		return;
	}

	$labels = [
		'name'               => _x( 'History Records', 'post type general name', 'handywriter' ),
		'singular_name'      => _x( 'History Record', 'post type singular name', 'handywriter' ),
		'menu_name'          => _x( 'History', 'admin menu', 'handywriter' ),
		'name_admin_bar'     => _x( 'History', 'add new on admin bar', 'handywriter' ),
		'add_new'            => _x( 'Add New', 'add new item', 'handywriter' ),
		'add_new_item'       => __( 'Add New History Record', 'handywriter' ),
		'new_item'           => __( 'New History Record', 'handywriter' ),
		'edit_item'          => __( 'Edit History Record', 'handywriter' ),
		'view_item'          => __( 'View History Record', 'handywriter' ),
		'all_items'          => __( 'History', 'handywriter' ),
		'search_items'       => __( 'Search History Records', 'handywriter' ),
		'parent_item_colon'  => __( 'Parent History Records:', 'handywriter' ),
		'not_found'          => __( 'No record found.', 'handywriter' ),
		'not_found_in_trash' => __( 'No record found in Trash.', 'handywriter' ),
	];

	$args = [
		'labels'              => $labels,
		'description'         => __( 'History Records for Handywriter.', 'handywriter' ),
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_in_admin_bar'   => false,
		'show_ui'             => true,
		'show_in_menu'        => 'handywriter',
		'menu_position'       => 1,
		'supports'            => [
			'title',
			'editor',
			'author',
		],
		'capabilities'        => [
			'publish_posts'       => $capability,
			'edit_others_posts'   => $capability,
			'delete_posts'        => $capability,
			'delete_others_posts' => $capability,
			'read_private_posts'  => $capability,
			'edit_post'           => $capability,
			'delete_post'         => $capability,
			'read_post'           => $capability,
		],
	];

	if ( ! HANDYWRITER_IS_NETWORK && current_user_can( $settings_capability ) ) { // parent page exists
		$args['show_in_menu'] = 'handywriter';
	} else {
		$args['show_in_menu'] = 'handywriter-templates';
	}

	register_post_type( HISTORY_POST_TYPE, $args );
}

/**
 * Schedule cron action for history cleanup.
 *
 * @return void
 * @since 1.0
 */
function schedule_history_cleanup() {
	$settings  = \Handywriter\Utils\get_settings();
	$timestamp = wp_next_scheduled( HISTORY_CRON_HOOK );

	if ( $settings['enable_history'] && $settings['history_records_ttl'] > 0 && ! $timestamp ) {
		wp_schedule_event( time(), 'twicedaily', HISTORY_CRON_HOOK );

		return;
	}

	if ( ( ! $settings['enable_history'] || 0 === $settings['history_records_ttl'] ) && $timestamp ) {
		wp_clear_scheduled_hook( HISTORY_CRON_HOOK );

		return;
	}
}

/**
 * Cleanup history records
 *
 * @return void
 * @since 1.0
 */
function cleanup_history() {
	global $wpdb;
	$settings = \Handywriter\Utils\get_settings();
	if ( 0 === $settings['history_records_ttl'] ) {
		return;
	}

	$delete_timestamp = time() - ( DAY_IN_SECONDS * $settings['history_records_ttl'] );
	$deletion_time    = gmdate( 'Y-m-d H:i:s', $delete_timestamp );

	$history_posts_to_delete = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type=%s AND post_date_gmt<=%s", HISTORY_POST_TYPE, $deletion_time ) );

	foreach ( (array) $history_posts_to_delete as $history_post_id ) {
		wp_delete_post( $history_post_id, true );
	}
}

/**
 * Add a history record.
 *
 * @param array $request_args     Request parameters to content generation API other than licensing related parameters.
 * @param array $response_content Generated results
 *
 * @return int|\WP_Error
 * @since 1.0
 */
function add_to_history( $request_args, $response_content = [] ) {
	$settings = \Handywriter\Utils\get_settings();

	if ( ! $settings['enable_history'] ) {
		return;
	}

	$is_content_template = ( isset( $request_args['request_source'] ) && 'content_template' === $request_args['request_source'] ? true : false );
	$results_count       = count( $response_content );

	if ( $is_content_template ) {
		$post_title = get_content_template_title( $request_args, $results_count );
	} else {
		$post_title = get_content_type_title( $request_args, $results_count );
	}

	$post_title = apply_filters( 'handywriter_history_post_title', $post_title, $request_args, $response_content );

	$separator    = '<!-- Handywriter History Record Separator -->';
	$separator   .= '<hr>';
	$post_content = implode( $separator, $response_content );

	$post_id = wp_insert_post(
		[
			'post_type'    => HISTORY_POST_TYPE,
			'post_title'   => $post_title,
			'post_content' => $post_content,
			'post_status'  => 'publish',
		]
	);

	if ( $is_content_template ) {
		update_post_meta( $post_id, 'content_template', $request_args['content_template'] );
	} else {
		update_post_meta( $post_id, 'content_type', $request_args['content_type'] );
	}

	do_action( 'handywriter_history_record_added', $post_id, $post_title, $post_content, $request_args, $response_content );

	return $post_id;
}

/**
 * Prepare a post title for content template to use in history post.
 *
 * @param array $request_args  request parameters
 * @param int   $results_count number of results
 *
 * @return string
 * @since 1.0
 */
function get_content_template_title( $request_args, $results_count ) {
	$form_data = $request_args['form_data'];

	switch ( $form_data['content_template'] ) {
		case 'bullet-points':
			$title = sprintf(
			// translators: %1$s is the number of results, %2$s is the bullet point about.
				_n( '%1$d Bullet Point generated for %2$s', '%1$d Bullet Points generated for %2$s', $results_count, 'handywriter' ),
				$results_count,
				$form_data['bullet_point_for']
			);
			break;
		case 'case-study':
			$title = sprintf(
			// translators: %1$d is the number of results, %2$s is the case study for.
				_n( '%1$d Case Study generated about %2$s', '%1$d Case Studies generated about %2$s', $results_count, 'handywriter' ),
				$results_count,
				$form_data['case_study_for']
			);
			break;
		case 'call-to-action-ideas':
			$title = sprintf(
			// translators: %1$d is the number of results, %2$s is the call to action about
				_n( '%1$d Call to Action Idea generated about %2$s', '%1$d Call to Action Ideas generated about %2$s', $results_count, 'handywriter' ),
				$results_count,
				$form_data['call_to_action_ideas_about']
			);

			break;

		case 'blog-ideas':
			$title = sprintf(
			// translators: %1$d is the number of results, %2$s is the topic
				_n( '%1$d Blog Idea generated for %2$s', '%1$d Blog Ideas generated for %2$s', $results_count, 'handywriter' ),
				$results_count,
				$form_data['blog_ideas_name']
			);

			break;
		case 'product-descriptions':
			$title = sprintf(
			// translators: %1$d is the number of results, %2$s is the product description
				_n( '%1$d Ecommerce Product Description generated for %2$s', '%1$d Ecommerce Product Descriptions generated for %2$s', $results_count, 'handywriter' ),
				$results_count,
				$form_data['product_descriptions_info']
			);

			break;
		case 'google-ad-copy':
			$title = sprintf(
			// translators: %1$d is the number of results, %2$s advertisiment for
				_n( '%1$d Google Ad Copy generated for %2$s', '%1$d Google Ad Copies generated for %2$s', $results_count, 'handywriter' ),
				$results_count,
				$form_data['google_ad_copy_name']
			);

			break;
		case 'value-proposition':
			$title = sprintf(
			// translators: %1$d is the number of results, %2$s value proposition for
				_n( '%1$d Value Proposition generated about %2$s', '%1$d Value Propositions generated about %2$s', $results_count, 'handywriter' ),
				$results_count,
				$form_data['value_proposition_name']
			);

			break;
		case 'youtube-description':
			$title = sprintf(
			// translators: %1$d is the number of results, %2$s is the description of the video
				_n( '%1$d YouTube Video Description generated about %2$s', '%1$d YouTube Video Descriptions generated about %2$s', $results_count, 'handywriter' ),
				$results_count,
				$form_data['youtube_description_info']
			);

			break;
		case 'personal-bio':
			$title = sprintf(
			// translators: %1$d is the number of results, %2$s is the name of the person
				_n( '%1$d Personal Bio generated for %2$s', '%1$d Personal Bio generated for %2$s', $results_count, 'handywriter' ),
				$results_count,
				$form_data['personal_bio_name']
			);

			break;
	}

	return $title;
}

/**
 * Prepare a post title for editor contents to use in history post.
 *
 * @param array $request_args  request parameters
 * @param int   $results_count number of generated contents
 *
 * @return string
 * @since 1.0
 */
function get_content_type_title( $request_args, $results_count ) {
	$content_type = $request_args['content_type'];
	$input_text   = $request_args['input_text'];

	switch ( $content_type ) {
		case 'suggest_heading':
			$title = sprintf( 'Heading generated for: %s', $input_text );
			break;
		case 'blog_post':
			$title = sprintf( 'Blog post generated for: %s', $input_text );
			break;
		case 'suggest_title':
			$title = sprintf(
			// translators: %1$d is the number of results, %2$s is the title of the content
				_n( '%1$d Title generated for %2$s', '%1$d Titles generated for %2$s', $results_count, 'handywriter' ),
				$results_count,
				$input_text
			);

			break;
		case 'complete_paragraph':
			$title = sprintf( 'Paragraph completion for: %s', $input_text );
			break;
		case 'summarize_content':
			$title = sprintf(
			// translators: %1$d is the number of results, %2$s content
				_n( '%1$d Summary generated for %2$s', '%1$d Summaries generated for %2$s', $results_count, 'handywriter' ),
				$results_count,
				$input_text
			);
			break;
		case 'meta_description':
			$title = sprintf(
			// translators: %1$d is the number of results, %2$s user input (title or content)
				_n( '%1$d Meta Description generated for %2$s', '%1$d Meta Descriptions generated for %2$s', $results_count, 'handywriter' ),
				$results_count,
				$input_text
			);
			break;
		default:
			// translators:%s current time in mysql format
			$title = sprintf( esc_html__( 'Editor Content Generation on %s', 'handywriter' ), current_time( 'mysql' ) );
			break;
	}

	return $title;
}

/**
 * Shorten long titles for history records.
 *
 * @param string $title History Post Title.
 *
 * @return string
 * @since 1.0
 */
function shorten_post_title( $title ) {
	$title = strlen( $title ) > 120 ? substr( $title, 0, 120 ) . '...' : $title;

	return $title;
}
