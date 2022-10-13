<?php
/**
 * Classic Editor modal(s)
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
		 id="hwcontent-modal"
		 class="sui-modal-content"
		 aria-live="polite"
		 aria-modal="true"
		 aria-labelledby="hwcontent-modal-title"
		 aria-describedby="hwcontent-modal-desc"
	>

		<div class="sui-box">
			<div class="sui-box-header">

				<h3 id="hwcontent-modal-title" class="sui-box-title"><?php esc_html_e( 'Results', 'handywriter' ); ?></h3>

				<button class="sui-button-icon sui-button-float--right" id="hwcontent-modal-close">
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this modal', 'handywriter' ); ?></span>
				</button>

			</div>
			<div class="sui-hidden sui-box-body" id="hw-modal-loader">
				<div class="sui-content-center" style="text-align: center;">
					<span class="sui-icon-loader sui-loading" aria-hidden="true"> <?php esc_html_e( 'Generating...', 'handywriter' ); ?></span>
				</div>
			</div>

			<div id="hwcontent-modal-message" class="sui-box-body">

			</div>
		</div>

	</div>
</div>
