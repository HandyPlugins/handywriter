/**
 * Handywriter text-to-speech functionality for Block Editor and Classic Editor.
 */

import jQuery from 'jquery';
import '@wpmudev/shared-ui/dist/js/_src/modal-dialog';
import {
	getTrimmedText,
	isTinyMCEActive,
	noticeTemplate,
	getTinymceContent,
	getSelectedText,
	isBlockEditor
} from './utils';
import icons from "./icons";

const { __ } = wp.i18n;
const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { BlockControls } = wp.blockEditor;
const { ToolbarGroup, ToolbarButton, Icon } = wp.components;

// Supported blocks for the toolbar items
const supportedBlocks = ['core/paragraph', 'core/heading'];

/**
 * Adds a custom button to the Paragraph Toolbar.
 */
const withToolbarButton = createHigherOrderComponent((BlockEdit) => {
	return (props) => {
		// Skip if current block is not supported
		if (!supportedBlocks.includes(props.name)) {
			return <BlockEdit {...props} />;
		}

		// Define the toolbar icon
		const toolbarIcon = () => {
			return icons.TTS;
		};

		// Retrieve the selected or entire block content
		const getBlockContent = () => {
			let selectedText = window.getSelection().toString();
			if (!selectedText) {
				selectedText = wp.data.select('core/block-editor')
					.getBlocks()
					.filter(block => supportedBlocks.includes(block.name))
					.map(block => block.attributes.content)
					.join('\n\n');
			}
			return selectedText;
		};

		// Open the modal for text-to-speech
		const openTTSModal = () => {
			const voiceContent = getBlockContent();
			// Replace <br> and <br/> tags with newline characters
			const textWithNewLines = voiceContent.replace(/<br\s*[\/]?>/gi, '\n');
			const modalTextarea = jQuery('#handywriter-tts-content');
			modalTextarea.val(jQuery('<div>').html(textWithNewLines).text().replace(/(<([^>]+)>)/ig, '')); // Strip HTML and set content

			window.SUI.openModal('handywriter-tts-modal', 'wpbody-content', undefined, true);
		};

		return (
			<Fragment>
				<BlockControls group="block">
					<ToolbarGroup>
						<ToolbarButton
							icon={toolbarIcon()}
							label={__('Turn text into audio', 'handywriter')}
							showTooltip="true"
							onClick={openTTSModal}
						/>
					</ToolbarGroup>
				</BlockControls>
				<BlockEdit {...props} />
			</Fragment>
		);
	};
}, 'withToolbarButton');

wp.hooks.addFilter('editor.BlockEdit', 'handywriter-tts/toolbar-button', withToolbarButton, 80);


(function ($) {
	const getPostTitle = () => {
		return isBlockEditor()
			? wp.data.select('core/editor').getEditedPostAttribute('title')
			: $('#titlewrap').find('input').val();
	};

	$('.handywriter-tts-classic-editor-btn').on('click', function () {
		const editorID = $(this).data('editor-id');
		const voiceContent = isTinyMCEActive()
			? getTinymceContent()
			: getTrimmedText(getSelectedText($('#content')) || $('#content').val());

		$('#handywriter-tts-editor-id').val(editorID);
		$('#handywriter-tts-content').text(voiceContent);

		SUI.openModal('handywriter-tts-modal', this, undefined, true, true, false);
	});

	$(document).on('click', '#handywriter-tts-modal-close', function (e) {
		e.preventDefault();
		jQuery('.wp-toolbar, .sui-modal').removeClass('sui-has-modal sui-active');
		window.SUI.closeModal();
	});

	$(document).on('handywriter-tts-audio-generated', function (e) {
		jQuery('.wp-toolbar').removeClass('sui-has-modal');
		jQuery('.sui-modal').removeClass('sui-active');
		window.SUI.closeModal();
	})

	$(document).on('submit', '#handywriter-tts-voice-generator-form', function (e) {
		e.preventDefault();
		const $errContainer = $('#handywriter_tts_result_msg');
		const $submitBtn = $('#handywriter-tts-generate-voice');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			beforeSend: function () {
				$errContainer.empty();
				$submitBtn.addClass('sui-button-onload-text');
			},
			data: {
				action: 'handywriter_create_audio',
				nonce: $('#handywriter_tts_nonce').val(),
				data: $(this).serialize(),
				title: getPostTitle()
			}
		}).done(function (response) {
			handleResponse(response, $errContainer, $submitBtn);
		}).fail(function () {
			const errorNotice = noticeTemplate(__('An error occurred while processing the request.','handywriter'), 'error');
			$errContainer.html(errorNotice);
		}).always(function () {
			$submitBtn.removeClass('sui-button-onload-text');
		});
	});

	function handleResponse(response, $errContainer, $submitBtn) {
		if (response.success) {
			$(document).trigger('handywriter-tts-audio-generated');
			insertAudioBlock(response);
		} else {
			const err = noticeTemplate(response.data.message, 'error');
			$errContainer.html(err);
		}
	}

	function insertAudioBlock(response) {
		if (isBlockEditor()) {
			let name = 'core/audio';
			let audioBlock = wp.blocks.createBlock(name, {
				id : response.data.attachment_id,
				src: response.data.attachment_url,
				caption: $('#tts_disclosure').val(),
			});
			wp.data.dispatch('core/block-editor').insertBlocks(audioBlock);
			return;
		}

		// fallback to classic editor
		if (wp && wp.media && wp.media.editor) {
			// remove selection to prevent selected content loss while adding audio into the TinyMCE editor
			if(wpActiveEditor && tinyMCE){
				tinyMCE.get(wpActiveEditor).selection.collapse();
			}

			wp.media.editor.activeEditor = editorID;
			let currentEditor = wp.media.editor.get(editorID);
			if (!currentEditor || (currentEditor.options && currentEditor.state !== currentEditor.options.state)) {
				currentEditor = wp.media.editor.add(editorID, {});
			}


			wp.media.frame = currentEditor;
			wp.media.frame.content.mode('browse'); // set browse mode all the time

			wp.media.frame.on('open', function () {
				// refresh and reset selection
				if (wp.media.frame.content.get() !== null) {
					wp.media.frame.content.get().collection._requery(true);
					wp.media.frame.content.get().options.selection.reset();
				}

				let selection = wp.media.frame.state().get('selection');
				let attachment = wp.media.attachment(response.data.attachment_id);
				attachment.set('type', 'audio');
				attachment.set('filename', 'handywriter-tts.mp3');
				attachment.set('meta', {
					bitrate     : 48000,
					bitrate_mode: 'cbr',
				});
				selection.multiple = false;
				selection.add(attachment);
			}, this);

			wp.media.frame.open();
		}
	}

})(jQuery);

