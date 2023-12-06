/* global jQuery */
/* eslint-disable */
import { noticeTemplate } from "./utils";
const { __ } = wp.i18n;

(function ($) {
	const imageSizes = {
		'dall-e-3': ['1024x1024', '1792x1024', '1024x1792'],
		'dall-e-2': ['256x256', '512x512', '1024x1024']
	};

	const $imageGeneratorModel = $('#image_generator_model');
	const $imageCount = $('#image_count');
	const $form = $('#handywriter-image-generator-form');

	function init() {
		if (!$form.length) {
			return;
		}
		populateImageResolutions();
		maybeAddPlaceholder();

		$imageGeneratorModel.on('change', toggleModel);
		$imageCount.on('change', maybeAddPlaceholder);
		$form.on('submit', handleSubmit);
		$('#hw-image-generator-section').on('click', '.hw-save-generated-image-btn', handleSaveImageClick);
	}

	function toggleModel() {
		const $qualityRow = $('#enable-hd-row');
		const currentModel = $imageGeneratorModel.val();
		populateImageResolutions();

		$('#image-style-row').toggle(currentModel === 'dall-e-3');
		$('#image-count-row').toggle(currentModel !== 'dall-e-3');

		// reset image count to 1 for placeholder
		if ('dall-e-3' === currentModel) {
			$('#image_count').val(1).trigger('change');
			$qualityRow.show();
		} else {
			$qualityRow.hide();
		}
	}

	function populateImageResolutions() {
		const currentModel = $imageGeneratorModel.val();
		const allowedSizes = imageSizes[currentModel];
		const options = allowedSizes.map(size => `<option value="${size}">${size}</option>`).join('');
		$('#image_generator_image_size').html(options);
	}

	function handleSubmit(e) {
		e.preventDefault();

		const $nonce = $('#handywriter_image_generator_nonce').val();
		const $ajax_url = $('#handywriter_ajax_url').val();
		const $errContainer = $('#hw-image-generator-result-msg');
		const $submitBtn = $('#submit-image-generate');
		const $resultsContainer = $('#image-generation-results');

		$.post($ajax_url, {
			beforeSend: () => {
				$('#image-generation-placeholder-results').show();
				$errContainer.empty();
				$submitBtn.addClass('sui-button-onload-text');
			},
			action: 'handywriter_image_generator',
			nonce: $nonce,
			data: $form.serialize(),
		}).done(response => {
			if(response.success){
				$('#image-generation-placeholder-results').hide();
				$resultsContainer.prepend(response.data.html);
			}else{
				const errorNotice = noticeTemplate(response.data.message, 'error');
				$errContainer.html(errorNotice).addClass('sui-padding-bottom');
			}
		}).fail(() => {
			const errorNotice = noticeTemplate(__('An error occurred while processing the request.', 'handywriter'), 'error');
			$errContainer.html(errorNotice).addClass('sui-padding-bottom');
		}).always(() => {
			$submitBtn.removeClass('sui-button-onload-text');
		});
	}

	function maybeAddPlaceholder() {
		const imageGenerationResults = document.getElementById("image-generation-placeholder-results");
		const imageNumberInput = document.getElementById("image_count");

		if (imageGenerationResults && imageNumberInput) {
			const numberOfPlaceholders = parseInt(imageNumberInput.value, 10) || 0;
			const placeholders = new Array(numberOfPlaceholders).fill(null).map((_, i) => createPlaceholderTemplate(i)).join('');
			imageGenerationResults.innerHTML = `<div class="sui-row hw-generated-image-result-set">${placeholders}</div>`;
		}
	}

	function createPlaceholderTemplate(index) {
		return `
            <div class="image-card">
                <div class="placeholder-col">
                    <!-- Placeholder content for item ${index + 1} -->
                </div>
            </div>
        `;
	}

	function handleSaveImageClick(e) {
		e.preventDefault();
		const $submitBtn = $(this);
		const $nonce = $('#handywriter_image_generator_nonce').val();
		const $ajax_url = $('#handywriter_ajax_url').val();
		const $resultsContainer = $submitBtn.parent('div');
		const $imageUrl = $submitBtn.parent('div').data('image-url');
		const $prompt = $submitBtn.parent('div').data('image-prompt');

		$.post($ajax_url, {
			beforeSend: () => {
				$submitBtn.addClass('sui-button-onload-text');
			},
			action: 'handywriter_image_save_to_media_library',
			nonce: $nonce,
			image_url: $imageUrl,
			prompt: $prompt
		}).done(response => {
			if (response.success) {
				const successMsg = noticeTemplate(response.data.message, 'success');
				$resultsContainer.html(successMsg);
			} else {
				const errorNotice = noticeTemplate(response.data.message, 'error');
				$resultsContainer.html(errorNotice);
				// restore the button after 3 seconds
				setTimeout(() => {
					$resultsContainer.html($submitBtn);
				}, 3000);
			}
		}).fail(() => {
			const errorNotice = noticeTemplate(__('An error occurred while processing the request.', 'handywriter'), 'error');
			$resultsContainer.html(errorNotice);
			setTimeout(() => {
				$resultsContainer.html($submitBtn);
			}, 3000);
		}).always(() => {
			$submitBtn.removeClass('sui-button-onload-text');
		});

	}


	init();
})(jQuery);
/* eslint-enable */
