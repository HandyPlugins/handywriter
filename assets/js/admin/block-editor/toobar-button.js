/**
 * Toolbar functionalities
 */
/* eslint-disable react/destructuring-assignment, @wordpress/no-global-get-selection */

/* global HandywriterAdmin  */
import jQuery from 'jquery'; // eslint-disable-line import/no-unresolved
import Typewriter from 'typewriter-effect/dist/core';
import icons from '../icons';
import { getTypewriterSpeed } from '../utils';

const { __ } = wp.i18n;

const enableToolbarButtonOnBlocks = ['core/paragraph', 'core/heading']; // toolbar items only available for these blocks
const { createHigherOrderComponent } = wp.compose;
const { Fragment, useState } = wp.element;
const { BlockControls } = wp.blockEditor;
const { ToolbarGroup, ToolbarButton, Spinner, Modal, Notice } = wp.components;

/**
 * Add Custom Button to Paragraph Toolbar
 */
const withToolbarButton = createHigherOrderComponent((BlockEdit) => {
	return (props) => {
		const [isErrorModalOpen, setErrorModalOpen] = useState(false);
		const openErrorModal = () => setErrorModalOpen(true);
		const closeErrorModal = () => setErrorModalOpen(false);
		const [errorMessage, setErrorMessage] = useState('');

		const [isBusy, setBusy] = useState(false);
		const [isGrammarCorrecting, setGrammarCorrecting] = useState(false);

		// If current block is not allowed
		if (!enableToolbarButtonOnBlocks.includes(props.name)) {
			return <BlockEdit {...props} />;
		}

		const { setAttributes } = props;

		const selectedTextContent = () => {
			let selectedText = window.getSelection().toString();
			if (!selectedText) {
				selectedText = props.attributes.content;
			}

			return selectedText;
		};

		const generateMoreText = () => {
			let content_type = 'complete_paragraph';
			if (props.name === 'core/heading') {
				content_type = 'suggest_heading';
			}

			setBusy(true);
			jQuery
				.post(
					window.ajaxurl,
					{
						action: 'handywriter_create_content',
						input: selectedTextContent(),
						nonce: HandywriterAdmin.nonce,
						content_type,
					},
					function (response) {
						if (response.success) {
							const blockContent = props.attributes.content;
							const selectedText = selectedTextContent();
							let finalContent = blockContent;
							let appendedText = `${response.data.content}`;
							let endingPart = '';

							if (content_type === 'suggest_heading') {
								// complete replace heading
								appendedText = `${response.data.content} `;
								finalContent = appendedText;
							} else {
								appendedText = appendedText.replace(selectedText, '');

								if (blockContent === selectedText) {
									finalContent += appendedText;
								} else {
									finalContent = finalContent.replace(
										selectedText,
										`${selectedText}${appendedText}`,
									);
								}

								finalContent = finalContent.replace(
									/(?:\r\n|\r|\n)/g,
									"<br data-rich-text-line-break='true'>",
								);
							}

							const activeText = window.getSelection().toString();
							let typingContent = selectedText;

							if (activeText) {
								const activeTextStart = blockContent.indexOf(activeText);
								const activeTextEnd = activeTextStart + activeText.length;
								typingContent = blockContent.substring(0, activeTextEnd);
								endingPart = blockContent.substring(activeTextEnd);
							}

							if (content_type === 'suggest_heading') {
								typingContent = '';
							}

							const typewriter = new Typewriter(null, {
								delay: getTypewriterSpeed(appendedText.length),
								onCreateTextNode(character) {
									typingContent += character;
									const tempContent = typingContent + endingPart;
									setAttributes({ content: tempContent });
									return null;
								},
							});

							typewriter
								.typeString(appendedText)
								.callFunction(() => {
									setAttributes({ content: finalContent });
								})
								.start();

							setBusy(false);
						} else {
							setErrorMessage(__('An error occurred', 'handywriter'));
							if (response.data.message) {
								setErrorMessage(response.data.message);
							}
							openErrorModal();
						}
					},
				)
				.always(function () {
					setBusy(false);
				});
		};

		const fixGrammar = () => {
			setGrammarCorrecting(true);

			jQuery
				.post(
					window.ajaxurl,
					{
						action: 'handywriter_edit_content',
						input: selectedTextContent(),
						nonce: HandywriterAdmin.nonce,
					},
					function (response) {
						if (response.success) {
							const selectedText = selectedTextContent();
							const allText = props.attributes.content;
							let newText = allText.replace(selectedText, response.data.content);

							newText = newText.replace(
								/(?:\r\n|\r|\n)/g,
								"<br data-rich-text-line-break='true'>",
							);
							setAttributes({ content: newText });
						} else {
							setErrorMessage(__('An error occurred', 'handywriter'));
							if (response.data.message) {
								setErrorMessage(response.data.message);
							}
							openErrorModal();
						}
					},
				)
				.always(function () {
					setGrammarCorrecting(false);
				});
		};

		const toolbarIcon = () => {
			if (isBusy) {
				return <Spinner />;
			}
			return icons.menuItem;
		};

		const toolbarGrammmarIcon = () => {
			if (isGrammarCorrecting) {
				return <Spinner />;
			}
			return icons.toolbarGrammar;
		};

		return (
			<Fragment>
				{isErrorModalOpen && (
					<Modal title={__('Error', 'handywriter')} onRequestClose={closeErrorModal}>
						<Notice status="error" isDismissible={false}>
							{errorMessage}
						</Notice>
					</Modal>
				)}

				<BlockControls group="block">
					<ToolbarGroup>
						<ToolbarButton
							icon={toolbarIcon()}
							label={__('Generate content for this selection', 'handywriter')}
							isActive={isBusy}
							isBusy={isBusy}
							isPressed={isBusy}
							showTooltip={!isBusy}
							onClick={generateMoreText}
						/>
						<ToolbarButton
							icon={toolbarGrammmarIcon()}
							label={__(
								'Fix grammar and spelling error(s) for this selection',
								'handywriter',
							)}
							isActive={isGrammarCorrecting}
							onClick={fixGrammar}
						/>
					</ToolbarGroup>
				</BlockControls>
				<BlockEdit {...props} />
			</Fragment>
		);
	};
}, 'withToolbarButton');

wp.hooks.addFilter('editor.BlockEdit', 'handywriter/toolbar-button', withToolbarButton, 99);
