<?php
/**
 * Content Templates Page
 *
 * @package Handywriter\Admin
 */

// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<form id="handywriter-content-form" method="post" action="">
	<?php wp_nonce_field( 'handywriter_content_template_nonce', 'handywriter_content_template_nonce' ); ?>
	<input type="hidden" name="handywriter_ajax_url" id="handywriter_ajax_url" value="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">

	<section id="content-template-selection">
		<div class="sui-box">

			<div class="sui-box-header">
				<h2 class="sui-box-title"><?php esc_html_e( 'Choose a Content Template', 'handywriter' ); ?></h2>
			</div>

			<div class="sui-box-selectors sui-box-selectors-col-3">
				<ul role="radiogroup">
					<li>
						<label for="content_template_blog_ideas" class="sui-box-selector">
							<input type="radio" name="content_template" value="blog-ideas" data-form-heading="<?php esc_html_e( 'Blog Ideas', 'handywriter' ); ?>" id="content_template_blog_ideas" aria-labelledby="blog-ideas-label" aria-describedby="blog_ideas_description">
							<span aria-hidden="true">
								<span class="sui-icon-blog" aria-hidden="true"></span>
								<span id="blog-ideas-label" aria-hidden="true"><?php esc_html_e( 'Blog Ideas', 'handywriter' ); ?></span>
							</span>
							<span id="blog_ideas_description" aria-hidden="true"><?php esc_html_e( 'Find a next blog idea', 'handywriter' ); ?></span>
						</label>
					</li>

					<li>
						<label for="content_template_product_descriptions" class="sui-box-selector">
							<input type="radio" name="content_template" value="product-descriptions" data-form-heading="<?php esc_html_e( 'Product Descriptions', 'handywriter' ); ?>" id="content_template_product_descriptions" aria-labelledby="product-descriptions-label" aria-describedby="product_descriptions_info">
							<span aria-hidden="true">
								<span class="sui-icon dashicons dashicons-cart"></span>
								<span id="product-descriptions-label" aria-hidden="true"><?php esc_html_e( 'E-commerce Product Descriptions', 'handywriter' ); ?></span>
							</span>
							<span id="product_descriptions_info" aria-hidden="true"><?php esc_html_e( 'Give more details about your products.', 'handywriter' ); ?></span>
						</label>
					</li>

					<li>
						<label for="content_template_google_ad_copy" class="sui-box-selector">
							<input type="radio" name="content_template" value="google-ad-copy" data-form-heading="<?php esc_html_e( 'Google Ad Copy', 'handywriter' ); ?>" id="content_template_google_ad_copy" aria-labelledby="google-ad-copy-label" aria-describedby="google_ad_copy_description">
							<span aria-hidden="true">
								<span class="sui-icon dashicons dashicons-google"></span>
								<span id="google-ad-copy-label" aria-hidden="true"><?php esc_html_e( 'Google Ad Copy', 'handywriter' ); ?></span>
							</span>
							<span id="google_ad_copy_description" aria-hidden="true"><?php esc_html_e( 'Generate an ad for your product or promotional campaign.', 'handywriter' ); ?></span>
						</label>
					</li>

					<li>
						<label for="content_template_tweet_ideas" class="sui-box-selector">
							<input type="radio" name="content_template" value="tweet-ideas" data-form-heading="<?php esc_html_e( 'Tweet Ideas', 'handywriter' ); ?>" id="content_template_tweet_ideas" aria-labelledby="tweet-ideas-label" aria-describedby="tweet_ideas_description">
							<span aria-hidden="true">
								<span class="sui-icon-social-twitter"></span>
								<span id="tweet-ideas-label" aria-hidden="true"><?php esc_html_e( 'Tweet Ideas', 'handywriter' ); ?></span>
							</span>
							<span id="tweet_ideas_description" aria-hidden="true"><?php esc_html_e( 'Easy to engage with your customers.', 'handywriter' ); ?></span>
						</label>
					</li>

					<li>
						<label for="content_template_youtube_description" class="sui-box-selector">
							<input type="radio" name="content_template" value="youtube-description" data-form-heading="<?php esc_html_e( 'Youtube Description', 'handywriter' ); ?>" id="content_template_youtube_description" aria-labelledby="youtube-description-label" aria-describedby="youtube-description-description">
							<span aria-hidden="true">
								<span class="sui-icon-social-youtube"></span>
								<span id="youtube-description-label" aria-hidden="true"><?php esc_html_e( 'Youtube Video Description', 'handywriter' ); ?></span>
							</span>
							<span id="youtube-description-description" aria-hidden="true"><?php esc_html_e( 'Ranks on search engine and informs your audience.', 'handywriter' ); ?></span>
						</label>
					</li>

					<li>
						<label for="content_template_personal_bio" class="sui-box-selector">
							<input type="radio" value="personal-bio" name="content_template" data-form-heading="<?php esc_html_e( 'Personal Bio', 'handywriter' ); ?>" id="content_template_personal_bio" aria-labelledby="personal-bio-label" aria-describedby="personal-bio-description">
							<span aria-hidden="true">
								<span class="sui-icon-profile-male" aria-hidden="true"></span>
								<span id="personal-bio-label" aria-hidden="true"><?php esc_html_e( 'Personal Bio', 'handywriter' ); ?></span>
							</span>
							<span id="personal-bio-description" aria-hidden="true"><?php esc_html_e( 'Creative bio about you.', 'handywriter' ); ?></span>
						</label>
						</a>
					</li>

					<li>
						<label for="content_template_call_to_action_ideas" class="sui-box-selector">
							<input type="radio" value="call-to-action-ideas" name="content_template" data-form-heading="<?php esc_html_e( 'Call to Action Ideas', 'handywriter' ); ?>" id="content_template_call_to_action_ideas" aria-labelledby="call-to-action-ideas-label" aria-describedby="call-to-action-ideas-description">
							<span aria-hidden="true">
								<span class="sui-icon-graph-line" aria-hidden="true"></span>
								<span id="call-to-action-ideas-label" aria-hidden="true"><?php esc_html_e( 'Call to Action Ideas', 'handywriter' ); ?></span>
							</span>
							<span id="call-to-action-ideas-description" aria-hidden="true"><?php esc_html_e( 'Create a call to action button that converts.', 'handywriter' ); ?></span>
						</label>
						</a>
					</li>

					<li>
						<label for="content_template_case_study" class="sui-box-selector">
							<input type="radio" value="case-study" name="content_template" data-form-heading="<?php esc_html_e( 'Case Study', 'handywriter' ); ?>" id="content_template_case_study" aria-labelledby="case-study-label" aria-describedby="case-study-description">
							<span aria-hidden="true">
								<span class="sui-icon-academy" aria-hidden="true"></span>
								<span id="case-study-label" aria-hidden="true"><?php esc_html_e( 'Case Study', 'handywriter' ); ?></span>
								<span class="sui-tag sui-tag-beta"><?php esc_html_e( 'Beta', 'handywriter' ); ?></span>
							</span>
							<span id="case-study-description" aria-hidden="true"><?php esc_html_e( 'Create a case study about the problem you solved for a brand.', 'handywriter' ); ?></span>
						</label>
						</a>
					</li>

					<li>
						<label for="content_template_bullet_points" class="sui-box-selector">
							<input type="radio" value="bullet-points" name="content_template" data-form-heading="<?php esc_html_e( 'Bullet Points', 'handywriter' ); ?>" id="content_template_bullet_points" aria-labelledby="bullet-points-label" aria-describedby="bullet-points-description">
							<span aria-hidden="true">
								<span class="sui-icon-list-bullet" aria-hidden="true"></span>
								<span id="bullet-points-label" aria-hidden="true"><?php esc_html_e( 'Bullet Points', 'handywriter' ); ?></span>
								<span class="sui-tag sui-tag-beta"><?php esc_html_e( 'Beta', 'handywriter' ); ?></span>
							</span>
							<span id="bullet-points-description" aria-hidden="true"><?php esc_html_e( 'Create a list about your product/service.', 'handywriter' ); ?></span>
						</label>
						</a>
					</li>

				</ul>

			</div>

		</div>

	</section>

	<section id="content-generator" class="sui-hidden sui-margin">
		<div class="sui-header">
			<div class="sui-actions-left">
				<button id="back-to-content-selection" type="button" class="sui-button sui-button-ghost">
					<span class="sui-icon-chevron-left" aria-hidden="true"></span> <?php esc_html_e( 'Back', 'handywriter' ); ?>
				</button>
			</div>
		</div>

		<div class="sui-box">
			<div class="sui-box-header">
				<h2 class="sui-box-title" id="content_template_generator_title"><?php esc_html_e( 'Content Type Generator', 'handywriter' ); ?></h2>
			</div>

			<div class="sui-box-body">
				<!-- Blog Ideas -->
				<fieldset id="blog-ideas-wrapper" class="sui-hidden">
					<div class="sui-form-field">
						<label for="blog_ideas_name" id="label-blog_ideas_name" class="sui-label">
							<?php esc_html_e( 'Name of your product/service?', 'handywriter' ); ?>
						</label>
						<input
							placeholder="Handywriter"
							id="blog_ideas_name"
							name="blog_ideas_name"
							class="sui-form-control"
							value=""
						/>
					</div>

					<div class="sui-form-field">

						<label for="blog_ideas_description" id="label-blog_ideas_description" class="sui-label"><?php esc_html_e( 'Description', 'handywriter' ); ?></label>

						<textarea
							placeholder="<?php esc_html_e( 'Handywriter is a WordPress plugin that allows to create content for your website in a few clicks.', 'handywriter' ); ?>"
							id="blog_ideas_description"
							name="blog_ideas_description"
							class="sui-form-control"
							aria-labelledby="label-blog_ideas_description"
						></textarea>
					</div>
				</fieldset>
				<!-- E-commerce Product Descriptions -->
				<fieldset id="product-descriptions-wrapper" class="sui-hidden">
					<div class="sui-form-field">

						<label for="product_descriptions_info" id="label-product_descriptions_info" class="sui-label"><?php esc_html_e( 'Description', 'handywriter' ); ?></label>

						<textarea
							placeholder="<?php esc_html_e( 'Zoom is a video conferencing platform for virtual meetings', 'handywriter' ); ?>"
							id="product_descriptions_info"
							name="product_descriptions_info"
							class="sui-form-control"
							aria-labelledby="label-product_descriptions_info"
						></textarea>
					</div>
				</fieldset>
				<!-- Google Ad Copy -->
				<fieldset id="google-ad-copy-wrapper" class="sui-hidden">
					<div class="sui-form-field">
						<label for="google_ad_copy_name" id="label-google_ad_copy_name" class="sui-label">
							<?php esc_html_e( 'Name of your product/service?', 'handywriter' ); ?>
						</label>
						<input
							placeholder="Handywriter"
							id="google_ad_copy_name"
							name="google_ad_copy_name"
							class="sui-form-control"
							value=""
						/>
					</div>

					<div class="sui-form-field">

						<label for="google_ad_copy_description" id="label-google_ad_copy_description" class="sui-label"><?php esc_html_e( 'Description', 'handywriter' ); ?></label>

						<textarea
							placeholder="<?php esc_html_e( 'An AI-powered content generation tool that creates quality content in seconds.', 'handywriter' ); ?>"
							id="google_ad_copy_description"
							name="google_ad_copy_description"
							class="sui-form-control"
							aria-labelledby="label-blog_ideas_description"
						></textarea>
					</div>
				</fieldset>
				<!-- Tweet Ideas -->
				<fieldset id="tweet-ideas-wrapper" class="sui-hidden">
					<div class="sui-form-field">
						<label for="tweet_ideas_name" id="label-tweet_ideas_name" class="sui-label">
							<?php esc_html_e( 'Name of your product/service?', 'handywriter' ); ?>
						</label>
						<input
							placeholder="Handywriter"
							id="tweet_ideas_name"
							name="tweet_ideas_name"
							class="sui-form-control"
							value=""
						/>
					</div>

					<div class="sui-form-field">

						<label for="tweet_ideas_description" id="label-tweet_ideas_description" class="sui-label"><?php esc_html_e( 'Description', 'handywriter' ); ?></label>

						<textarea
							placeholder="<?php esc_html_e( 'Handywriter is a WordPress plugin that allows to create content for your website in a few clicks.', 'handywriter' ); ?>"
							id="tweet_ideas_description"
							name="tweet_ideas_description"
							class="sui-form-control"
							aria-labelledby="label-blog_ideas_description"
						></textarea>
					</div>
				</fieldset>
				<!-- Youtube Video Description -->
				<fieldset id="youtube-description-wrapper" class="sui-hidden">
					<div class="sui-form-field">

						<label for="youtube_description_info" id="label-youtube_description_info" class="sui-label"><?php esc_html_e( 'What is your video about?', 'handywriter' ); ?></label>

						<textarea
							placeholder="<?php esc_html_e( 'a video about setting up a Shopify storefront including website, payment and shipping within 30 minutes.', 'handywriter' ); ?>"
							id="youtube_description_info"
							name="youtube_description_info"
							class="sui-form-control"
							aria-labelledby="label-youtube_description_info"
						></textarea>
					</div>
				</fieldset>
				<!-- Bio Ideas -->
				<fieldset id="personal-bio-wrapper" class="sui-hidden">
					<div class="sui-form-field">
						<label for="personal_bio_name" id="label-personal_bio_name" class="sui-label">
							<?php esc_html_e( 'What is your name?', 'handywriter' ); ?>
						</label>
						<input
							placeholder="John Snow"
							id="personal_bio_name"
							name="personal_bio_name"
							class="sui-form-control"
							value=""
						/>
					</div>

					<div class="sui-form-field">

						<label for="personal_bio_about" id="label-personal_bio_about" class="sui-label"><?php esc_html_e( 'Tell us about yourself', 'handywriter' ); ?></label>

						<textarea
							placeholder="loves archeology, photography, and travel. Also, a big fan of the latest tech and gadgets."
							id="personal_bio_about"
							name="personal_bio_about"
							class="sui-form-control"
							aria-labelledby="label-personal_bio_about"
						></textarea>
					</div>
				</fieldset>
				<!-- Call to Action Button -->
				<fieldset id="call-to-action-ideas-wrapper" class="sui-hidden">
					<div class="sui-form-field">
						<label for="call_to_action_ideas_about" id="label-call_to_action_ideas" class="sui-label">
							<?php esc_html_e( 'Description', 'handywriter' ); ?>
						</label>
						<input
							placeholder="download our free PDF"
							id="call_to_action_ideas_about"
							name="call_to_action_ideas_about"
							class="sui-form-control"
							value=""
						/>
					</div>
				</fieldset>
				<!-- Case Study -->
				<fieldset id="case-study-wrapper" class="sui-hidden">
					<div class="sui-form-field">
						<label for="case_study_for" id="label-case_study_for" class="sui-label">
							<?php esc_html_e( 'Case study for?', 'handywriter' ); ?>
						</label>
						<input
							placeholder="Acme"
							id="case_study_for"
							name="case_study_for"
							class="sui-form-control"
							value=""
						/>
						<span class="sui-description"><?php esc_html_e( '(Enter company/brand name.)' ); ?></span>
					</div>

					<div class="sui-form-field">
						<label for="case_study_info" id="label-case_study_info" class="sui-label"><?php esc_html_e( 'What happened?', 'handywriter' ); ?></label>
						<textarea
							placeholder="We helped to increase their sales by 50% in 3 months."
							id="case_study_info"
							name="case_study_info"
							class="sui-form-control"
							aria-labelledby="label-case_study_info"
						></textarea>
					</div>
				</fieldset>
				<!-- Bullet Points -->
				<fieldset id="bullet-points-wrapper" class="sui-hidden">
					<div class="sui-form-field">
						<label for="bullet_point_for" id="label-bullet_point_for" class="sui-label">
							<?php esc_html_e( 'Name of your product/service', 'handywriter' ); ?>
						</label>
						<input
							placeholder="Acme"
							id="bullet_point_for"
							name="bullet_point_for"
							class="sui-form-control"
							value=""
						/>
					</div>

					<div class="sui-form-field">
						<label for="bullet_point_info" id="label-bullet_point_info" class="sui-label"><?php esc_html_e( 'Tell us about your product', 'handywriter' ); ?></label>
						<textarea
							placeholder="We helped to increase their sales by 50% in 3 months."
							id="bullet_point_info"
							name="bullet_point_info"
							class="sui-form-control"
							aria-labelledby="label-bullet_point_info"
						></textarea>
					</div>
				</fieldset>
			</div>

			<div class="sui-box-footer">
				<button id="submit-content-generate" type="submit" class="sui-button sui-button-blue sui-button-filled" aria-live="polite">

					<!-- Default State Content -->
					<span class="sui-button-text-default">
						<span class="sui-icon-widget-settings-config" aria-hidden="true"></span>
						<?php esc_html_e( 'Create Content', 'handywriter' ); ?>
					</span>

					<!-- Loading State Content -->
					<span class="sui-button-text-onload">
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
						<?php esc_html_e( 'Generating...', 'handywriter' ); ?>
					</span>

				</button>
			</div>
		</div>

	</section>
</form>
<section id="results" class="sui-hiddens sui-margin">

</section>
