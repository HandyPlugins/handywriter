<?php
/**
 * Settings Page Header
 *
 * @package Handywriter\Admin
 */

use const Handywriter\Constants\DOCS_URL;
use const Handywriter\Constants\ICON_BASE64;

// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<header class="sui-header">
	<h1 class="sui-header-title">
		<img width="32" alt="<?php esc_html_e( 'Handywriter Icon', 'handywriter' ); ?>"
			 src="<?php echo ICON_BASE64; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
		<?php esc_html_e( 'Handywriter', 'handywriter' ); ?>
	</h1>

	<!-- Float element to Right -->
	<div class="sui-actions-right">
		<a href="<?php echo esc_url( DOCS_URL ); ?>" class="sui-button sui-button-blue" target="_blank">
			<i class="sui-icon-academy" aria-hidden="true"></i>
			<?php esc_html_e( 'Documentation', 'handywriter' ); ?>
		</a>
	</div>
</header>
<?php \Handywriter\Utils\settings_errors(); ?>
