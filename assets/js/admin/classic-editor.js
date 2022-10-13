/* global jQuery,ClipboardJS  */
/* eslint-disable */
const {__} = wp.i18n;
import '@wpmudev/shared-ui/dist/js/_src/modal-dialog';
import Typewriter from 'typewriter-effect/dist/core';
import {getTypewriterSpeed, isTinyMCEActive} from './utils';

(function ($) {

	const metaboxNotice = (message, type = 'error', timeout = 5000) => {
		const $html = noticeTemplate(message, type);

		$('#handywriter-classic-editor-notice').html($html);

		setTimeout(function () {
			$('#handywriter-classic-editor-notice').html('');
		}, timeout);
	};

	const noticeTemplate = (message, type = 'error') => {
		const $html = `<div class="sui-notice sui-notice-${type}">
							<div class="sui-notice-content">
								<div class="sui-notice-message">
									<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
									<p>${message}</p>
								</div>
							</div>
						</div>`;
		return $html;
	}

	$(document).on('click', '.sui-modal-overlay', function () {
		$('.sui-has-modal').removeClass('sui-has-modal');
	});
	$('#hwcontent-modal-close').on('click', function (e) {
		e.preventDefault();
		SUI.closeModal();
		$('.sui-has-modal').removeClass('sui-has-modal');
	});


	$('#hw-write-post').on('click', function (e) {
		e.preventDefault();

		const title = $('[name="post_title"]').val();
		const ajaxurl = $('#handywriter_ajax_url').val();
		const nonce = $('#handywriter_admin_nonce').val();

		if (!title) {
			metaboxNotice(__('Please enter a title for your post first!', 'handywriter'), 'warning');
			return;
		}

		$.post(ajaxurl, {
				beforeSend: function () {
					$('#hw-write-post').addClass('sui-button-onload');
				},
				action: 'handywriter_create_content',
				input: title,
				nonce: nonce,
				content_type: 'blog_post',
				editor: 'classic_editor'
			}, function (response) {
				if (response.success) {
					let tinyMceActive = isTinyMCEActive();
					let blogPost = response.data.content[0];
					let blogPostArr = blogPost.split(/(?:\r\n|\r|\n)/g);
					let typingContent = '';

					if (tinyMceActive) {
						typingContent = getTinymceContent();
					} else {
						typingContent = $('#content').val();
					}

					const blogPostTextNode = function (character) {
						typingContent += character;
						if (tinyMceActive) {
							tinyMCE.activeEditor.setContent(typingContent, {format: 'html'});
							// $('#content_ifr').contents().find('body').append(character);
						} else {
							$('#content').val(typingContent);
						}
					}

					let typewriter = new Typewriter(null, {
						delay: getTypewriterSpeed(blogPost.length),
						onCreateTextNode: blogPostTextNode,
					});

					if(tinyMceActive){
						blogPostArr.forEach(function (line) { // type line by line
							typewriter
								.typeString(line)
								.callFunction(function () {
									if (tinyMceActive) {
										typingContent += '<br>';
										tinyMCE.activeEditor.selection.select(tinyMCE.activeEditor.getBody(), true);
										tinyMCE.activeEditor.selection.collapse(false);
									}
								})
								.start();
						});
					}else{
						typewriter
							.typeString(blogPost)
							.start();
					}

				}else if (response.data.message) {
					metaboxNotice(response.data.message, 'error');
				}
			}
		).done(function () {
			$('#hw-write-post').removeClass('sui-button-onload');
		});

		return false;
	});

	$('#hw-suggest-title').on('click', function (e) {
		e.preventDefault();

		let titleInput = $('[name="post_title"]').val();
		const ajaxurl = $('#handywriter_ajax_url').val();
		const nonce = $('#handywriter_admin_nonce').val();

		if (!titleInput) {
			titleInput = getTinymceContent();
		}

		if (!titleInput) {
			metaboxNotice(__('Please enter a title or write some content. We cannot suggest a new title without clue!', 'handywriter'), 'warning');
			return;
		}

		SUI.openModal(
			'hwcontent-modal',
			this,
			undefined,
			true,
			true,
			true
		);


		$.post(ajaxurl, {
				beforeSend: function () {
					$('#hw-modal-loader').show();
					$('#hwcontent-modal-message').empty();
				},
				action: 'handywriter_create_content',
				input: titleInput,
				nonce: nonce,
				content_type: 'suggest_title',
				editor: 'classic_editor'
			}, function (response) {
				$('#hw-modal-loader').hide();
				const $container = $('#hwcontent-modal-message');

				if (response.success) {
					$container.html(response.data.html);
				}else if (response.data.message) {
					const err = noticeTemplate(response.data.message, 'error');
					$container.html(err);
				}
			}
		);
	});

	$('#hw-create-summary').on('click', function (e) {
		e.preventDefault();
		let content = getTinymceContent();
		content = content.replace(/<[^>]*>?/gm, '');

		const ajaxurl = $('#handywriter_ajax_url').val();
		const nonce = $('#handywriter_admin_nonce').val();

		if (content.length < 200) {
			metaboxNotice(__('Please write some more content to get summary recommendations.', 'handywriter'), 'warning');
			return;
		}

		SUI.openModal(
			'hwcontent-modal',
			this,
			undefined,
			true,
			true,
			true
		);

		$.post(ajaxurl, {
				beforeSend: function () {
					$('#hw-modal-loader').show();
					$('#hwcontent-modal-message').empty();
				},
				action: 'handywriter_create_content',
				input: content,
				nonce: nonce,
				content_type: 'summarize_content',
				editor: 'classic_editor'
			}, function (response) {
				$('#hw-modal-loader').hide();
				const $container = $('#hwcontent-modal-message');

				if (response.success) {
					$container.html(response.data.html);
				}else if (response.data.message) {
					const err = noticeTemplate(response.data.message, 'error');
					$container.html(err);
				}

			}
		);
	});

	$('#hw-create-meta-desc').on('click', function (e) {
		e.preventDefault();
		let metaInput = $('[name="post_title"]').val();
		const ajaxurl = $('#handywriter_ajax_url').val();
		const nonce = $('#handywriter_admin_nonce').val();

		if (!metaInput) {
			metaInput = getTinymceContent();
		}

		if (!metaInput) {
			metaboxNotice(__('Please enter a title or write some content. We cannot suggest a meta description without clue!', 'handywriter'), 'warning');
			return;
		}

		SUI.openModal(
			'hwcontent-modal',
			this,
			undefined,
			true,
			true,
			true
		);

		$.post(ajaxurl, {
			beforeSend: function () {
				$('#hw-modal-loader').show();
				$('#hwcontent-modal-message').empty();
			},
			action: 'handywriter_create_content',
			input: metaInput,
			nonce: nonce,
			content_type: 'meta_description',
			editor: 'classic_editor'
		}, function (response) {
			$('#hw-modal-loader').hide();
			const $container = $('#hwcontent-modal-message');

			if (response.success) {
				$container.html(response.data.html);
			} else if (response.data.message) {
				const err = noticeTemplate(response.data.message, 'error');
				$container.html(err);
			}

		});

	});

	$('#hw-plagiarism-check').on('click', function (e) {
		const ajaxurl = $('#handywriter_ajax_url').val();
		const nonce = $('#handywriter_admin_nonce').val();
		const currentContent = getTinymceContent();

		$.post(ajaxurl, {
				beforeSend: function () {
					$('.plagiarism-checking').show();
				},
				action: 'handywriter_check_plagiarism',
				input: currentContent,
				nonce: nonce,
			}, function (response) {
				if (response.success) {
					$('#handywriter-plagiarism-check-results').html(response.data.classic_editor_result)
				} else {
					if (response.data.message) {
						metaboxNotice(response.data.message, 'error');
						return;
					}

					metaboxNotice(__('An error occurred', 'handywriter'), 'error');
				}
			}
		).always(function () {
			$('.plagiarism-checking').hide();
		});
	});


	$('body').on('click', '.handywriter-set-title', setTitle);
	$('body').on('click', '.handywriter-set-summary', setSummary);

	function setTitle() {
		const title = $(this).text().trim();
		// $('[name="post_title"]').val(title);
		let input = $('[name="post_title"]');

		input.val(''); // reset
		let customNodeCreator = function (character) {
			// Add character to input placeholder
			input.val(input.val() + character)

			// Return null to skip internal adding of dom node
			return null;
		}


		let typewriter = new Typewriter(null, {
			delay: getTypewriterSpeed(title.length),
			onCreateTextNode: customNodeCreator,
		});

		typewriter
			.typeString(title)
			.start();

		SUI.closeModal();
		$('.sui-has-modal').removeClass('sui-has-modal');
	}


	function setSummary() {
		SUI.closeModal();
		$('.sui-has-modal').removeClass('sui-has-modal');

		const currentContent = getTinymceContent();
		const summary = $(this).text().trim();
		let summaryArr = summary.split(/(?:\r\n|\r|\n)/g);
		let tinyMceActive = isTinyMCEActive();
		let typingContent = '';

		if (tinyMceActive) {
			typingContent = getTinymceContent();
		} else {
			typingContent = $('#content').val();
		}

		const summaryTextNode = function (character) {
			typingContent += character;
			if (tinyMceActive) {
				tinyMCE.activeEditor.setContent(typingContent, {format: 'html'});
				// $('#content_ifr').contents().find('body').append(character);
			}else{
				$('#content').val(typingContent);
			}
		}


		let typewriter = new Typewriter(null, {
			delay: getTypewriterSpeed(summary.length),
			onCreateTextNode: summaryTextNode,
		});


		if (tinyMceActive) {
			summaryArr.forEach(function (line) {
				typewriter
					.typeString(line)
					.callFunction(function () {
						if (tinyMceActive) {

							tinyMCE.activeEditor.selection.select(tinyMCE.activeEditor.getBody(), true);
							tinyMCE.activeEditor.selection.collapse(false);
							typingContent += '<br>';

							// $('#content_ifr').contents().find('body').append("<br>");
						}
					})
					.start();

			});
		} else {
			typewriter
				.typeString(summary)
				.start();
		}

	}


	function getTinymceContent(editor_id, textarea_id) {
		if (typeof editor_id == 'undefined'){
			editor_id = wpActiveEditor;
		}

		if (typeof textarea_id == 'undefined') {
			textarea_id = editor_id;
		}

		if ($('#wp-' + editor_id + '-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id)) {
			return tinyMCE.get(editor_id).getContent();
		} else {
			return $('#' + textarea_id).val();
		}
	}

	function setTinymceContent(content, editor_id, textarea_id) {
		if (typeof editor_id == 'undefined') editor_id = wpActiveEditor;
		if (typeof textarea_id == 'undefined') textarea_id = editor_id;

		if (jQuery('#wp-' + editor_id + '-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id)) {
			return tinyMCE.get(editor_id).setContent(content);
		} else {
			return jQuery('#' + textarea_id).val(content);
		}
	}

	const clipboard = new ClipboardJS('.copy-to-clipboard');

	clipboard.on('success', function (e) {
		$(e.trigger).addClass('sui-tooltip');
		$(e.trigger).attr('aria-label', __('Copied!', 'handywriter'));
		$(e.trigger).attr('data-tooltip', __('Copied!', 'handywriter'));

		setTimeout(function () {
			$(e.trigger).removeClass('sui-tooltip');
			$(e.trigger).removeAttr('aria-label');
			$(e.trigger).removeAttr('data-tooltip');
		}, 2000);

		e.clearSelection();
	});


})(jQuery);

/* eslint-enable */
