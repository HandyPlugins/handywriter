<?php
/**
 * Settings page
 *
 * @package Handywriter\Admin
 */

// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found

use function Handywriter\Utils\get_credit_usage;
use function Handywriter\Utils\get_license_info;
use function Handywriter\Utils\get_license_key;
use function Handywriter\Utils\get_license_status_message;
use function Handywriter\Utils\get_required_capability_for_license_details;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$settings     = \Handywriter\Utils\get_settings();
$license_info = get_license_info();
?>

<form method="post" action="">
	<?php wp_nonce_field( 'handywriter_settings', 'handywriter_settings' ); ?>
	<section>
		<div class="sui-box">

			<div class="sui-box-header">
				<h2 class="sui-box-title"><?php esc_html_e( 'Settings', 'handywriter' ); ?></h2>
			</div>

			<div class="sui-box-body">

				<?php if ( ! defined( 'HANDYWRITER_LICENSE_KEY' ) ) : ?>
					<div class="sui-box-settings-row">
						<div class="sui-box-settings-col-1">
							<span class="sui-settings-label" id="license_key_label"><?php esc_html_e( 'License Key', 'handywriter' ); ?></span>
						</div>

						<div class="sui-box-settings-col-2">
							<div class="sui-form-field">
								<input
									name="license_key"
									id="license_key"
									class="sui-form-control sui-field-has-suffix"
									aria-labelledby="license_key_label"
									type="text"
									value="<?php echo esc_attr( get_license_key() ); ?>"
								/>
								<span class="sui-field-suffix">
									<?php if ( false !== $license_info && 'valid' === $license_info['license_status'] ) : ?>
										<input type="submit" class="sui-button sui-button-red" name="handywriter_license_deactivate" id="handywriter-save-settings" value="<?php esc_html_e( 'Deactivate', 'handywriter' ); ?>" />
									<?php else : ?>
										<input type="submit" class="sui-button sui-button-green" name="handywriter_license_activate" id="handywriter-save-settings" value="<?php esc_html_e( 'Activate', 'handywriter' ); ?>" />
									<?php endif; ?>
								</span>
								<span class="sui-description"><?php echo esc_html( get_license_status_message() ); ?></span>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( current_user_can( get_required_capability_for_license_details() ) ) : ?>
					<?php
					$credit_usage          = get_credit_usage();
					$current_usage_percent = 0;
					if ( ! empty( $credit_usage['data']['spent'] ) && ! empty( $credit_usage['data']['credits'] ) ) {
						$current_usage_percent = round( ( $credit_usage['data']['spent'] / $credit_usage['data']['credits'] ) * 100 );
					}
					?>
					<?php if ( ! empty( $credit_usage['success'] ) ) : ?>
						<div class="sui-box-settings-row">
							<div class="sui-box-settings-col-1">
								<span class="sui-settings-label"><?php esc_html_e( 'Credit Usage', 'handywriter' ); ?></span>
								<span class="sui-description"><?php esc_html_e( 'Current usage', 'handywriter' ); ?></span>
							</div>

							<div class="sui-box-settings-col-2">

								<div class="sui-progress-block">

									<div class="sui-progress">
										<span class="sui-progress-text"><?php printf( '%d%%', absint( $current_usage_percent ) ); ?></span>

										<div class="sui-progress-bar" aria-hidden="true">
											<span style="width: <?php echo esc_attr( $current_usage_percent ); ?>%"></span>
										</div>

									</div>

								</div>

								<div class="sui-progress-state">
									<span>
										<?php if ( $credit_usage['success'] && isset( $credit_usage['data']['remaining'] ) ) : ?>
											<?php /* translators: %d: The number of available credits*/ ?>
											<?php printf( esc_html__( '%d credits left. Data may be delayed up to 5 minutes.', 'handywriter' ), absint( ( $credit_usage['data']['remaining'] ) ) ); ?>
										<?php endif; ?>
									</span>
									<a href="#hwusage-modal" data-modal-open="hwusage-modal" data-esc-close="true" data-modal-mask="true" id="hw-show-usage-details">
										<?php esc_html_e( 'Show details', 'handywriter' ); ?>
									</a>
								</div>

							</div>

						</div>
					<?php endif; ?>
					<div class="sui-box-settings-row">
						<div class="sui-box-settings-col-1">
							<span class="sui-settings-label" id="role_key"><?php esc_html_e( 'Role', 'handywriter' ); ?></span>
						</div>

						<?php
						$roles = wp_roles()->get_names();

						if ( HANDYWRITER_IS_NETWORK && ! isset( $roles['super_admin'] ) ) {
							$roles = [ 'super_admin' => esc_html__( 'Super Admin', 'handywriter' ) ] + $roles;
						}

						?>
						<div class="sui-box-settings-col-2">
							<div class="sui-form-field">
								<select name="role" id="select-single-default" class="sui-select">
									<?php foreach ( $roles as $role => $role_name ) : ?>
										<option <?php selected( $role, $settings['role'] ); ?> value="<?php echo esc_attr( $role ); ?>">
											<?php echo esc_attr( translate_user_role( $role_name ) ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<span class="sui-description"><?php esc_html_e( 'Minimum required role to access Handywriter features', 'handywriter' ); ?></span>
							</div>
						</div>
					</div>
				<?php endif; ?>
				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
						<span class="sui-settings-label" id="role_key"><?php esc_html_e( 'Max results', 'handywriter' ); ?></span>
					</div>

					<div class="sui-box-settings-col-2">
						<div class="sui-form-field">
							<input
								name="max_results"
								id="max_results"
								class="sui-form-control sui-field-has-suffix"
								min="1"
								max="10"
								type="number"
								value="<?php echo absint( $settings['max_results'] ); ?>"
							/>
							<span class="sui-description"><?php esc_html_e( 'The maximum amount of content to generate at once. Increasing this number will allow burning credits faster.', 'handywriter' ); ?></span>
						</div>
					</div>
				</div>

				<div class="sui-box-settings-row">
					<div class="sui-box-settings-col-1">
						<span class="sui-settings-label" id="role_key"><?php esc_html_e( 'Enable History', 'handywriter' ); ?></span>
					</div>

					<div class="sui-box-settings-col-2">
						<div class="sui-form-field">
							<div class="sui-row">
								<label for="enable_history" class="sui-toggle">
									<input
											type="checkbox"
											id="enable_history"
											name="enable_history"
											aria-labelledby="enable_history_label"
											aria-describedby="enable_history_description"
											aria-controls="history_records_ttl_control"
											value="1"
											<?php checked( 1, $settings['enable_history'] ); ?>
									>

									<span class="sui-toggle-slider" aria-hidden="true"></span>
									<span id="enable_history_label" class="sui-toggle-label"><?php esc_html_e( 'Keep records of AI generated contents.', 'powered-cache' ); ?></span>
								</label>
							</div>

							<div style=" <?php echo( ! $settings['enable_history'] ? 'display:none' : '' ); ?>" tabindex="0" id="history_records_ttl_control">
								<div class="sui-row sui-margin-top">
									<div class="sui-form-field">
										<input
												name="history_records_ttl"
												id="history_records_ttl"
												class="sui-form-control sui-field-has-suffix"
												min="0"
												max="999"
												type="number"
												value="<?php echo absint( $settings['history_records_ttl'] ); ?>"
										/>
										<span class="sui-field-suffix"><?php esc_html_e( 'days', 'handywriter' ); ?></span>

										<span class="sui-description"><?php esc_html_e( 'Maximum number of days to keep history records. (Enter 0 to keep records indefinitely. Purging old records helps to keep your WordPress installation running optimally)', 'handywriter' ); ?></span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>

			<div class="sui-box-footer">
				<div class="sui-actions-left">
					<button type="submit" name="handywriter_form_action" value="save_settings" class="sui-button sui-button-blue">
						<i class="sui-icon-save" aria-hidden="true"></i>
						<?php esc_html_e( 'Update settings', 'handywriter' ); ?>
					</button>
				</div>
			</div>

		</div>

	</section>
</form>

