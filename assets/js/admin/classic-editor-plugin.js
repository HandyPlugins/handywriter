/* global tinymce, jQuery */
import Typewriter from 'typewriter-effect/dist/core';
import { getTypewriterSpeed } from './utils';
/* eslint-disable no-unused-vars */

const { __ } = wp.i18n;

const addTempErrMsg = (errMsg, timeout = 5000) => {
	const $html = `<div id="handywriter-classic-editor-error" class="notice notice-error notice-alt"><p>${errMsg}</p></div>`;

	jQuery('#wp-content-editor-tools').after($html);

	setTimeout(function () {
		jQuery('#handywriter-classic-editor-error').remove();
	}, timeout);
};

tinymce.PluginManager.add('handywriter_classic_editor_plugin', function (editor, url) {
	const imageBaseUrl = url.replace('/js', '/images');

	editor.addButton('handywriter_button', {
		icon: 'mce-widget mce-btn',
		image: `${imageBaseUrl}/handywriter-mce-icon.svg`,
		onclick() {
			const currentContent = editor.getContent({ format: 'text' }); // all content in the editor
			const currentSelection = editor.selection.getContent({ format: 'text' }); // selected content in the editor

			let input = currentSelection;
			if (!input.trim().length) {
				input = currentContent; // if no selection, use the whole content
			}

			if (!input.trim().length) {
				addTempErrMsg(
					__(
						'Empty content. Select or write some content to generate a result.',
						'handywriter',
					),
				);
				return false;
			}

			jQuery
				.post(
					window.ajaxurl,
					{
						beforeSend() {
							const msg = __('Generating...', 'handywriter');
							const $html = `<div id="handywriter-classic-editor-info" class="inline notice notice-info notice-alt"><p><span class="spinner is-active"></span>${msg}</p></div>`;
							jQuery('#wp-content-editor-tools').after($html);
						},
						action: 'handywriter_create_content',
						input,
						nonce: jQuery('#handywriter_admin_nonce').val(),
						content_type: 'complete_paragraph',
					},
					function (response) {
						jQuery('#handywriter-classic-editor-info').remove();
						if (response.success) {
							const generatedContent = response.data.content[0];
							if (currentSelection) {
								editor.insertContent(currentSelection); // add current selection without typewriting
							}

							const typewriter = new Typewriter(null, {
								delay: getTypewriterSpeed(generatedContent.length),
								onCreateTextNode(character) {
									editor.insertContent(character);
									return null;
								},
							});

							typewriter.typeString(generatedContent).start();
						} else {
							if (response.data.message) {
								addTempErrMsg(response.data.message);

								return;
							}

							addTempErrMsg(__('An error occurred!', 'handywriter'));
						}
					},
				)
				.always(function () {});

			return false;
		},
		tooltip: __('Generate content for this selection', 'handywriter'),
	});

	editor.addButton('handywriter_grammar_fixer_button', {
		icon: 'mce-widget mce-btn',
		image: `${imageBaseUrl}/handywriter-mce-icon-grammar-fixer.svg`,
		onclick() {
			const currentContent = editor.getContent(); // all content in the editor

			// selected content in the editor
			let input = editor.selection.getContent({ format: 'text' });
			if (!input.trim().length) {
				input = currentContent; // if no selection, use the whole content
			}

			if (!input.trim().length) {
				addTempErrMsg(
					__(
						'Empty content. Select or write some text to check grammar mistake(s).',
						'handywriter',
					),
				);
				return false;
			}

			jQuery
				.post(
					window.ajaxurl,
					{
						beforeSend() {
							const msg = __('Processing...', 'handywriter');
							const $html = `<div id="handywriter-classic-editor-info" class="inline notice notice-info notice-alt"><p><span class="spinner is-active"></span>${msg}</p></div>`;
							jQuery('#wp-content-editor-tools').after($html);
						},
						action: 'handywriter_edit_content',
						input,
						nonce: jQuery('#handywriter_admin_nonce').val(),
					},
					function (response) {
						jQuery('#handywriter-classic-editor-info').remove();
						if (response.success && response.data.content[0]) {
							const newText = currentContent.replace(input, response.data.content[0]);
							editor.setContent(newText);
						} else {
							if (response.data.message) {
								addTempErrMsg(response.data.message);

								return;
							}

							addTempErrMsg(__('An error occurred!', 'handywriter'));
						}
					},
				)
				.always(function () {});
			return false;
		},
		tooltip: __('Fix grammar and spelling error(s) for this selection', 'handywriter'),
	});
});
