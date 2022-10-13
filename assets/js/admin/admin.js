/* global jQuery,HandywriterAdmin */
import '../../css/admin/admin-style.css';
import './content-template';
import '@wpmudev/shared-ui/dist/js/_src/modal-dialog';

(function ($) {
	// toggle TTL field based on state of checkbox
	$('#enable_history').on('change', function () {
		if ($(this).is(':checked')) {
			$('#history_records_ttl_control').show();
		} else {
			$('#history_records_ttl_control').hide();
		}
	});

	$('#hw-show-usage-details').on('click', function (e) {
		e.preventDefault();
		$.post(
			ajaxurl,
			{
				beforeSend() {
					$('#hw-usage-fetching').show();
					$('#hwusage-modal-wrapper').empty();
				},
				action: 'handywriter_usage_details',
				nonce: HandywriterAdmin.nonce,
			},
			function (response) {
				$('#hw-usage-fetching').hide();
				const $container = $('#hwusage-modal-wrapper');

				if (response.success) {
					$container.html(response.data.html);
				} else {
					const $html = `<div role="alert" id="inline-notice-general" class="sui-notice sui-notice-red sui-active" aria-live="assertive" style="display: block;">
						<div class="sui-notice-content">
							<div class="sui-notice-message">
								<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
								<p>${response.data.message}</p></div>
						</div>
					</div>`;
					$container.html($html);
				}
			},
		);
	});
})(jQuery);
