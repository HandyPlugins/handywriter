<?php
/**
 * Image Generator Page Template
 *
 * @package Handywriter\Admin
 */

// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$image_models = \Handywriter\Utils\get_available_image_models();
$image_styles = \Handywriter\Utils\get_available_image_styles();
add_thickbox();

?>
<form id="handywriter-image-generator-form" method="post" action="">
	<?php wp_nonce_field( 'handywriter_image_generator_nonce', 'handywriter_image_generator_nonce' ); ?>
	<input type="hidden" name="handywriter_ajax_url" id="handywriter_ajax_url" value="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">

	<section id="hw-image-generator-section">
		<div class="sui-box">
			<div class="sui-box-header">
				<h2 class="sui-box-title"><?php esc_html_e( 'Image Generate', 'handywriter' ); ?></h2>
			</div>
			<div class="sui-box-body">
				<div class="sui-row">
					<div class="sui-col-md-3">
						<div class="sui-form-field">
							<label for="hw-image-generator-prompt" id="hw-image-generator-prompt-label" class="sui-label"><?php esc_html_e( 'Prompt', 'handywriter' ); ?></label>
							<textarea
								placeholder="<?php esc_html_e( 'What do you want to see?', 'handywriter' ); ?>"
								id="hw-image-generator-prompt"
								name="image_generator[prompt]"
								class="sui-form-control"
								aria-labelledby="hw-image-generator-prompt-label"
								rows="5"
								maxlength="1000"
								required
							></textarea>
							<span class="sui-description"><?php esc_html_e( 'Enter prompt for image generation.', 'handywriter' ); ?></span>
							<div class="sui-form-field">
								<label class="sui-label"><?php esc_html_e( 'Model', 'handywriter' ); ?> </label>
								<select id="image_generator_model" name="image_generator[model]" class="sui-select">
									<?php foreach ( $image_models as $model => $model_name ) : ?>
										<option value="<?php echo esc_attr( $model ); ?>"><?php echo esc_html( $model_name ); ?></option>
									<?php endforeach; ?>
								</select>

							</div>

							<div class="sui-form-field">
								<label class="sui-label"><?php esc_html_e( 'Resolution', 'handywriter' ); ?></label>
								<select id="image_generator_image_size" name="image_generator[size]" class="sui-select">
									<option value="1024x1024">1024x1024</option>
								</select>
							</div>

							<div class="sui-form-field" id="image-style-row">
								<label class="sui-label"><?php esc_html_e( 'Style', 'handywriter' ); ?></label>
								<select id="image_style" name="image_generator[style]" class="sui-select">
									<?php foreach ( $image_styles as $style => $style_name ) : ?>
										<option value="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $style_name ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="sui-form-field" id="image-count-row" style="display: none;">
								<label class="sui-label"><?php esc_html_e( 'Image Count', 'handywriter' ); ?></label>
								<input class="sui-form-control image-number" id="image_count" name="image_generator[count]" value="1" type="number" min="1" max="10">
								<span class="sui-description"><?php esc_html_e( 'Enter the number of images to generate. Note that generating a greater number of images will consume more credits.', 'handywriter' ); ?></span>
							</div>

							<div class="sui-form-field" role="radiogroup" id="enable-hd-row">
								<label class="sui-label"><?php esc_html_e( 'Quality', 'handywriter' ); ?></label>
								<label for="image_generator_standard_quality" class="sui-radio">
									<input
										value="standard"
										type="radio"
										name="image_generator[quality]"
										id="image_generator_standard_quality"
										aria-labelledby="image_generator_standard_quality_label"
										checked="checked"
									/>
									<span aria-hidden="true"></span>
									<span id="image_generator_standard_quality_label"><?php esc_html_e( 'Standard', 'handywriter' ); ?></span>
								</label>

								<label for="image_generator_hd_quality" class="sui-radio">
									<input
										value="hd"
										type="radio"
										name="image_generator[quality]"
										id="image_generator_hd_quality"
										aria-labelledby="image_generator_hd_quality_label"
									/>
									<span aria-hidden="true"></span>
									<span id="image_generator_hd_quality_label"><?php esc_html_e( 'HD', 'handywriter' ); ?></span>
								</label>
							</div>

							<div class="sui-row" id="hw-image-generator-result-msg">
							</div>

							<button id="submit-image-generate" type="submit" class="sui-button sui-button-blue sui-button-filled" aria-live="polite">
								<!-- Default State Content -->
								<span class="sui-button-text-default">
									<span class="sui-icon-photo-picture" aria-hidden="true"></span>
									<?php esc_html_e( 'Generate', 'handywriter' ); ?>
								</span>

								<!-- Loading State Content -->
								<span class="sui-button-text-onload">
									<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
									<?php esc_html_e( 'Generating...', 'handywriter' ); ?>
								</span>

								<!-- Button label for screen readers -->
								<span class="sui-screen-reader-text"><?php esc_html_e( 'Generate Images', 'handywriter' ); ?></span>
							</button>
						</div>

					</div>
					<div class="sui-col-md-9">
						<div id="image-generation-placeholder-results"></div>
						<div id="image-generation-results"></div>
					</div>
				</div>

			</div>

		</div>
	</section>

	<section id="results" class="sui-hiddens sui-margin">

	</section>

</form>

