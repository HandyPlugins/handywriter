<?php
/**
 * Settings modal(s)
 *
 * @package Handywriter\Admin
 */

// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="sui-modal sui-modal-lg">
	<div role="dialog"
		 id="hwusage-modal"
		 class="sui-modal-content"
		 aria-live="polite"
		 aria-modal="true"
		 aria-labelledby="hwusage-modal-title"
		 aria-describedby="hwusage-modal-desc"
	>

		<div class="sui-box">

			<button class="sui-screen-reader-text" data-modal-close=""><?php esc_html_e( 'Close', 'handywriter' ); ?></button>

			<div class="sui-box-header">

				<h3 id="hwusage-modal-title" class="sui-box-title"><?php esc_html_e( 'Usage Details', 'handywriter' ); ?></h3>

				<button class="sui-button-icon sui-button-float--right" data-modal-close="">
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this modal', 'handywriter' ); ?></span>
				</button>

			</div>
			<div class="sui-hidden sui-box-body" id="hw-usage-fetching">
				<div class="sui-content-center" style="text-align: center;">
					<span class="sui-icon-loader sui-loading" aria-hidden="true"> <?php esc_html_e( 'Fetching...', 'handywriter' ); ?></span>
				</div>
			</div>

			<div id="hwusage-modal-wrapper" class="sui-box-body">

			</div>
		</div>

	</div>
</div>
