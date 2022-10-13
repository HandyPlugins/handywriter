/* global jQuery, ClipboardJS  */
/* eslint-disable */
(function ($) {
	$('input[name="content_template"]').on('click', function () {
		$('#content-template-selection').hide();
		$('#content-generator').show();
		const $heading = $(this).data('form-heading');

		if ($heading) {
			$('#content_template_generator_title').text($heading);
		}

		const $el = `#${$(this).val()}-wrapper`;
		$('#handywriter-content-form fieldset').hide(); // hide all fieldsets
		$($el).find('input, textarea').prop('required', true); // make required fields in selection

		$($el).show();
	});

	$('#back-to-content-selection').on('click', function () {
		$('#handywriter-content-form').find('input, textarea').prop('required', false); // remove required
		$('#content-template-selection').show();
		$('#content-generator').hide();
		$('#handywriter-content-form fieldset').hide();
		$('#results').html('');
		$('#results').addClass('sui-hidden');
		$('#submit-content-generate').removeClass('sui-button-onload-text');
	});

	const clipboard = new ClipboardJS('.copy-to-clipboard');

	clipboard.on('success', function (e) {
		$(e.trigger).addClass('sui-tooltip');
		$(e.trigger).attr('aria-label', 'Copied!');
		$(e.trigger).attr('data-tooltip', 'Copied!');

		setTimeout(function () {
			$(e.trigger).removeClass('sui-tooltip');
			$(e.trigger).removeAttr('aria-label');
			$(e.trigger).removeAttr('data-tooltip');
		}, 2000);

		e.clearSelection();
	});

	$('#handywriter-content-form').on('submit', function (e) {
		e.preventDefault();

		const $nonce = $('#handywriter_content_template_nonce').val();
		const $ajax_url = $('#handywriter_ajax_url').val();

		$.post(
			$ajax_url,
			{
				beforeSend() {
					$('#submit-content-generate').addClass('sui-button-onload-text');
				},
				action: 'handywriter_content_template_create_content',
				nonce: $nonce,
				formData: $(this).serialize(),
			},
			function (response) {
				if (response.success) {
					$('#results').html(response.data);
				} else {
					const template = `<div role="alert" id="inline-notice-general" class="sui-notice sui-notice-green sui-notice-yellow sui-notice-red sui-active" aria-live="assertive" style="display: block;">
						<div class="sui-notice-content">
							<div class="sui-notice-message">
								<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
								<p>${response.data.message}</p>
							</div>
						</div>
					</div>`;

					$('#results').html(template);
				}

				$('#results').removeClass('sui-hidden');

			},
		).done(function (response) {
			$('#submit-content-generate').removeClass('sui-button-onload-text');
		});
	});
})(jQuery);
/* eslint-enable */
