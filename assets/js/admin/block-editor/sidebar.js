/* global HandywriterAdmin, ClipboardJS  */
/* eslint-disable */

import Typewriter from 'typewriter-effect/dist/core';
import {getTypewriterSpeed, unslash} from '../utils';
import icons from '../icons.js'
import jQuery from 'jquery'; // eslint-disable-line import/no-unresolved
import "mark.js/dist/jquery.mark";

const {registerPlugin} = wp.plugins;
const {__, _n, sprintf} = wp.i18n;
const {Fragment, useState} = wp.element;
const {PluginSidebarMoreMenuItem, PluginSidebar} = wp.editPost;
const {
	Button,
	Modal,
	CardHeader,
	Card,
	CardBody,
	CardFooter,
	CardDivider,
	Panel,
	Notice,
	TextHighlight,
	ExternalLink
} = wp.components;

const HandywriterSidebarComponent = () => {
	const [isWritingBlogPost, setWritingBlogPost] = useState(false);
	const [sidebarNotice, setSidebarNotice] = useState('');

	const [isTitleModalOpen, setTitleModalOpen] = useState(false);
	const openTitleModal = () => setTitleModalOpen(true);
	const closeTitleModal = () => setTitleModalOpen(false);
	const [suggestedTitles, setSuggestedTitles] = useState('');
	const [isFetchingTitles, setFetchingTitles] = useState(false);

	const [isSummaryModalOpen, setSummaryModalOpen] = useState(false);
	const openSummaryModal = () => setSummaryModalOpen(true);
	const closeSummaryModal = () => setSummaryModalOpen(false);
	const [summary, setSummary] = useState('');
	const [isFetchingSummary, setFetchingSummary] = useState(false);

	const [isMetaModalOpen, setMetaModalOpen] = useState(false);
	const openMetaModal = () => setMetaModalOpen(true);
	const closeMetaModal = () => setMetaModalOpen(false);
	const [meta, setMeta] = useState('');
	const [isFetchingMeta, setFetchingMeta] = useState(false);

	const [isCheckingPlagiarism, setCheckingPlagiarism] = useState(false);
	const [plagiarism, setPlagiarism] = useState('');
	const [isPlagiarismCheckComplete, setPlagiarismCheckComplete] = useState(false);

	const [isProofreading, setProofreading] = useState(false);
	const [proofreading,setProofreadingResult] = useState('');
	const [isProofreadingComplete, setProofreadingComplete] = useState(false);
	const [isCorrected, setCorrected] = useState([]);

	/**
	 * Write a blog post for given title
	 */
	const ajaxWriteBlogPost = () => {
		let title = wp.data.select("core/editor").getEditedPostAttribute('title');
		setWritingBlogPost(true);

		if (!title) {
			setSidebarNotice({
				message: __('Please enter a title for your post first!', 'handywriter'),
				status: 'warning'
			});
			setWritingBlogPost(false);
			return;
		}

		jQuery.post(window.ajaxurl, {
				action: 'handywriter_create_content',
				input: title,
				nonce: HandywriterAdmin.nonce,
				content_type: 'blog_post'
			}, function (response) {
				if (response.success) {
					let blogPost = response.data.content[0];
					blogPost = blogPost.replace(/(?:\r\n|\r|\n)/g, "<br data-rich-text-line-break='true'>");

					let blocks = wp.data.select( 'core/block-editor' ).getBlocks();
					let insertBlock = true;
					let postBlock = wp.blocks.createBlock("core/paragraph", {
						content: ''
					});

					if ( blocks.length > 0) {
						let latestBlock = blocks[blocks.length - 1];
						if (latestBlock.name === 'core/paragraph' && latestBlock.attributes.content === '') {
							postBlock = latestBlock;
							insertBlock = false;
						}
					}

					if(insertBlock){
						wp.data.dispatch("core/block-editor").insertBlocks(postBlock);
					}

					let typingContent = '';
					const createBlogPostNode = function (character) {
						typingContent += character;
						wp.data.dispatch("core/block-editor").updateBlockAttributes(postBlock.clientId, {content: typingContent});
						return null;
					}

					let typewriter = new Typewriter(null, {
						delay: getTypewriterSpeed(blogPost.length),
						onCreateTextNode: createBlogPostNode,
					});

					typewriter
						.typeString(blogPost)
						.callFunction(() => {
							// setup content with line breaks
							wp.data.dispatch("core/block-editor").updateBlockAttributes(postBlock.clientId, {content: blogPost});
						})
						.start();

				} else if (response.data.message) {
					setSidebarNotice({
						message: response.data.message,
						status: 'error'
					});
				}
			}
		).always(function (response) {
			setWritingBlogPost(false);
		});

	}

	/**
	 * Make ajax request to get suggested titles
	 */
	const ajaxSuggestTitle = () => {
		let titleInput = wp.data.select("core/editor").getEditedPostAttribute('title');

		if (!titleInput) {
			titleInput = wp.data.select('core/editor').getEditedPostContent();
		}

		if(!titleInput) {
			setSidebarNotice({
				message: __('Please enter a title or write some content. We cannot suggest a new title without clue!', 'handywriter'),
				status: 'warning'
			});
			return;
		}

		setFetchingTitles(true);

		jQuery.post(window.ajaxurl, {
				action: 'handywriter_create_content',
				input: titleInput,
				nonce: HandywriterAdmin.nonce,
				content_type: 'suggest_title'
			}, function (response) {
				if (response.success) {
					setSuggestedTitles(response.data.content);
					openTitleModal();
				} else if (response.data.message) {
					setSidebarNotice({
						message: response.data.message,
						status: 'error'
					});
				}
			}
		).always(function () {
			setFetchingTitles(false);
		});
	}

	/**
	 * Cretae a summary for the post
	 */
	const ajaxCreateSummary = () => {
		let currentContent = wp.data.select('core/editor').getEditedPostContent();
		currentContent = currentContent.replace(/<[^>]*>?/gm, '');

		if(currentContent.length < 200) {
			setSidebarNotice({
				message: __('Please write some more content to get summary recommendations.', 'handywriter'),
				status: 'warning'
			});
			return;
		}

		setFetchingSummary(true);

		jQuery.post(window.ajaxurl, {
				action: 'handywriter_create_content',
				input: currentContent,
				nonce: HandywriterAdmin.nonce,
				content_type: 'summarize_content'
			}, function (response) {
				if (response.success) {
					setSummary(response.data.content);
					openSummaryModal();
				} else if (response.data.message) {
					setSidebarNotice({
						message: response.data.message,
						status: 'error'
					});
				}
			}
		).always(function () {
			setFetchingSummary(false);
		});
	}
	/**
	 * Make ajax request to get suggested titles
	 */
	const ajaxCreateMetaDescription = () => {
		let metaInput = wp.data.select("core/editor").getEditedPostAttribute('title');

		if (!metaInput) {
			metaInput = wp.data.select('core/editor').getEditedPostContent();
		}

		if(!metaInput) {
			setSidebarNotice({
				message: __('Please enter a title or write some content. We cannot suggest a meta description without clue!', 'handywriter'),
				status: 'warning'
			});
			return;
		}

		setFetchingMeta(true);

		jQuery.post(window.ajaxurl, {
				action: 'handywriter_create_content',
				input: metaInput,
				nonce: HandywriterAdmin.nonce,
				content_type: 'meta_description'
			}, function (response) {
				if (response.success) {
					setMeta(response.data.content);
					openMetaModal();
				} else if (response.data.message) {
					setSidebarNotice({
						message: response.data.message,
						status: 'error'
					});
				}
			}
		).always(function (response) {
			setFetchingMeta(false);
		});

	}

	/**
	 * Check plagiarism
	 */
	const ajaxCheckPlagiarism = () => {
		let currentContent = wp.data.select('core/editor').getEditedPostContent();
		setCheckingPlagiarism(true);

		jQuery.post(window.ajaxurl, {
				action: 'handywriter_check_plagiarism',
				input: currentContent,
				nonce: HandywriterAdmin.nonce,
			}, function (response) {
				if (response.success) {
					setPlagiarism(response.data);
					setPlagiarismCheckComplete(true);
				} else {
					if (response.data.message) {
						setSidebarNotice({
							message: response.data.message,
							status: 'error'
						});
						return;
					}

					setSidebarNotice({
						message: __('An error occurred', 'handywriter'),
						status: 'error'
					});
				}
			}
		).always(function () {
			setCheckingPlagiarism(false);
		});
	}
	/**
	 * Check plagiarism
	 */
	const ajaxProofreadingRequest = () => {
		let currentContent = wp.data.select('core/editor').getEditedPostContent();
		setProofreading(true);

		jQuery.post(window.ajaxurl, {
				action: 'handywriter_proofreading',
				input: currentContent,
				nonce: HandywriterAdmin.nonce,
			}, function (response) {
				if (response.success) {
					setProofreadingResult(response.data);
					setProofreadingComplete(true);
				} else {
					if (response.data.message) {
						setSidebarNotice({
							message: response.data.message,
							status: 'error'
						});
						return;
					}

					setSidebarNotice({
						message: __('An error occurred', 'handywriter'),
						status: 'error'
					});
				}
			}
		).always(function () {
			setProofreading(false);
		});
	}


	/**
	 * Set the post title to the suggested title
	 * @param title
	 */
	const setPostTitle = (title) => {
		const postType = wp.data.select('core/editor').getCurrentPostType();
		const postID = wp.data.select('core/editor').getCurrentPostId();

		var input = jQuery('.wp-block-post-title');
		input.text(''); // reset
		var customNodeCreator = function (character) {
			// Add character to input placeholder
			input.text(input.text() + character)
			// Return null to skip internal adding of dom node
			return null;
		}

		let typewriter = new Typewriter(null, {
			delay: getTypewriterSpeed(title.length),
			onCreateTextNode: customNodeCreator,
		});

		typewriter
			.typeString(title)
			.callFunction(() => {
				wp.data.dispatch('core').editEntityRecord('postType', postType, postID, {title: title}); // set actual title
			})
			.start();

		closeTitleModal();
	}


	/**
	 * Append summary block at the end of the post content
	 * @param summary
	 */
	const setContentSummary = (summary) => {
		summary = summary.replace(/(?:\r\n|\r|\n)/g, "<br data-rich-text-line-break='true'>");

		closeSummaryModal();
		const summaryBlock = wp.blocks.createBlock("core/paragraph", {
			content: ''
		})

		wp.data.dispatch("core/block-editor").insertBlocks(summaryBlock);

		let typingContent = '';
		const createSummaryNode = function (character) {
			typingContent += character;
			wp.data.dispatch("core/block-editor").updateBlockAttributes(summaryBlock.clientId, {content: typingContent});
			return null;
		}

		let typewriter = new Typewriter(null, {
			delay: getTypewriterSpeed(summary.length),
			onCreateTextNode: createSummaryNode,
		});


		typewriter
			.typeString(summary)
			.callFunction(() => {
				wp.data.dispatch("core/block-editor").updateBlockAttributes(summaryBlock.clientId, {content: summary});
			})
			.start();
	}

	const clipboard = new ClipboardJS('.copy-to-clipboard');

	clipboard.on('success', function (e) {
		jQuery(e.trigger).addClass('sui-tooltip');
		jQuery(e.trigger).attr('aria-label', __('Copied!', 'handywriter'));
		jQuery(e.trigger).attr('data-tooltip', __('Copied!', 'handywriter'));

		setTimeout(function () {
			jQuery(e.trigger).removeClass('sui-tooltip');
			jQuery(e.trigger).removeAttr('aria-label');
			jQuery(e.trigger).removeAttr('data-tooltip');
		}, 2000);

		e.clearSelection();
	});

	return (
		<Fragment>
			<PluginSidebar
				name="handywriter-sidebar"
				title={__('Handywriter Assistant', 'handywriter')}
			>
				<div id="handywriter-sidebar">
					{
						sidebarNotice &&
						<Fragment>
							<Notice
								status={sidebarNotice.status}
								isDismissible={true}
								onDismiss={() => setSidebarNotice(false)}
							>
								{sidebarNotice.message}
							</Notice>
						</Fragment>
					}
					<Card>
						<CardBody>
							<Panel>
								<Button variant="secondary"
										onClick={ajaxWriteBlogPost}
										isBusy={isWritingBlogPost}
										showTooltip={!isWritingBlogPost}
										label={__('Write a post for current post title', 'handywriter')}
										className="handywriter-button"
								>
									{__('Write a Post', 'handywriter')}
								</Button>

							</Panel>
							<br />

							<Panel>
								<Button variant="secondary"
										onClick={ajaxSuggestTitle}
										isBusy={isFetchingTitles}
										showTooltip={!isFetchingTitles}
										label={__('Title Recommendations', 'handywriter')}
										className="handywriter-button"
								>
									{__('Suggest a Title', 'handywriter')}
								</Button>
							</Panel>
							<br />

							<Panel>
								<Button variant="secondary"
										onClick={ajaxCreateSummary}
										isBusy={isFetchingSummary}
										showTooltip={!isFetchingSummary}
										label={__('Create a summary for this post', 'handywriter')}
										className="handywriter-button"
								>
									{__('Create a Summary', 'handywriter')}
								</Button>
							</Panel>

							<br />
							<Panel>
								<Button variant="secondary"
										onClick={ajaxCreateMetaDescription}
										isBusy={isFetchingMeta}
										showTooltip={!isFetchingMeta}
										label={__('Create a meta description for this post', 'handywriter')}
										className="handywriter-button"
								>
									{__('Create a Meta Description', 'handywriter')}
								</Button>
							</Panel>

							<br />
							<Panel>
								<Button variant="secondary"
										onClick={ajaxCheckPlagiarism}
										isBusy={isCheckingPlagiarism}
										showTooltip={!isCheckingPlagiarism}
										label={__('Check plagiarism for this post', 'handywriter')}
										className="handywriter-button"
								>
									{__('Plagiarism Check', 'handywriter')}
								</Button>
							</Panel>
							{
								isPlagiarismCheckComplete &&
								<Fragment>
									{plagiarism.count > 0 && (
										<Notice
											status="warning"
											isDismissible={false}
										>
											{__('Plagiarism has been detected! ', 'handywriter')}
											{sprintf(_n('%d result have been found.', '%d results have been found.', plagiarism.count, 'handywriter'), plagiarism.count)}
										</Notice>
									)
									}

									{plagiarism.count === 0 && (
										<Notice
											status="success"
											isDismissible={false}
										>
											{__('No plagiarism has been detected!', 'handywriter')}
										</Notice>
									)
									}

									{plagiarism.count > 0 && (
										<Panel>
											<ol>
												{plagiarism.matches.map((item, index) => {
													return (
														<li key={index}>
															<TextHighlight
																text={item.text}
																highlight={item.text}
															/>
															<br />

															{sprintf(__('%s%% of the similarity found on', 'handywriter'), item.similarity)}

															<ExternalLink style={{"overflowWrap": "break-word"}} className="plagiarism-link" href={item.url}
																		  rel="noopener"
																		  target="_blank"> {item.url}</ExternalLink>
														</li>
													)
												})
												}
											</ol>
										</Panel>
									)
									}

								</Fragment>
							}


							<br />
							<Panel>
								<Button variant="secondary"
										onClick={ajaxProofreadingRequest}
										isBusy={isProofreading}
										showTooltip={!isProofreading}
										label={__('Proofreading check for this post', 'handywriter')}
										className="handywriter-button"
								>
									{__('Proofreading', 'handywriter')}
								</Button>
							</Panel>
							{
								isProofreadingComplete &&
								<Fragment>
									{proofreading.matches.length > 0 && (
										<Notice
											status="warning"
											isDismissible={false}
										>
											{sprintf(_n('%d suggestion.', '%d suggestions.', proofreading.matches.length, 'handywriter'), proofreading.matches.length)}
										</Notice>
									)
									}

									{proofreading.matches.length === 0 && (
										<Notice
											status="success"
											isDismissible={false}
										>
											{__('No mistakes have been detected!', 'handywriter')}
										</Notice>
									)
									}

									{proofreading.matches.length > 0 && (
										<Panel>
											<ul>
												{proofreading.matches.map((item, index) => {
													const contextText = item.context.text;
													const HighlightedContextText = contextText.substr(item.context.offset, item.context.length)
													const sentenceUnslashed = unslash(item.sentence);
													const preText = unslash(contextText.substr(0, item.context.offset))
													const afterText = unslash(contextText.substr(item.context.offset+item.context.length))

													return (
														<li key={index}
															className="hw-highlight-sentence"
														>
															<Card>
																<CardBody>
																	<p><b>	{item.message} </b></p>
																	<p style={{textDecoration: isCorrected.includes(index) ? 'line-through' : '' }}>
																		{preText}<mark>{HighlightedContextText}</mark>{afterText}
																	</p>

																</CardBody>
																<CardFooter>
																	<Button
																		disabled={isCorrected.includes(index)}
																		onClick={() => {
																			jQuery('.wp-block-post-content').mark(sentenceUnslashed, {
																				"caseSensitive": true,
																				"accuracy": "complementary",
																				"separateWordSearch": false
																			});
																		}}
																		onMouseOut={() => {
																			jQuery('.wp-block-post-content').unmark();
																		}}
																		className="hw-card-button"
																		variant="secondary"
																		icon={isCorrected.includes(index) ? "hidden" : "visibility"}
																		showTooltip={true}
																		label={__('Click to Highlight', 'handywriter')}
																	>
																	</Button>

																	{item.replacements.length > 0 && (
																		<Button
																			onClick={() => {
																				const blocks = wp.data.select('core/block-editor').getBlocks();
																				let originalSentence = unslash(item.sentence)
																				let replace = new RegExp(HighlightedContextText, "g");
																				let correctedSentence = originalSentence.replace(replace, item.replacements[0].value);
																				let blockContent = '';

																				if (isCorrected.includes(index)) {
																					blocks.map((block, blockIndex) => {
																						if (block.name === 'core/paragraph' || block.name === 'core/heading') {
																							blockContent = block.attributes.content;
																							if (blockContent.includes(correctedSentence)) {
																								blockContent = blockContent.replace(correctedSentence, originalSentence);
																								wp.data.dispatch("core/block-editor").updateBlockAttributes(block.clientId, {content: blockContent});

																								setCorrected((current) =>
																									current.filter((correctedIndex) => correctedIndex !== index)
																								);
																							}
																						}
																					});
																				} else {
																					blocks.map((block, blockIndex) => {
																						if (block.name === 'core/paragraph' || block.name === 'core/heading') {
																							blockContent = block.attributes.content;
																							if (blockContent.includes(originalSentence)) {
																								blockContent = blockContent.replace(originalSentence, correctedSentence);
																								wp.data.dispatch("core/block-editor").updateBlockAttributes(block.clientId, {content: blockContent});
																								setCorrected([...isCorrected, index]);
																							}
																						}
																					});
																				}
																			}}
																			className="hw-card-button"
																			variant="secondary"
																			icon={isCorrected.includes(index) ? "image-rotate" : "yes"}
																		>
																		</Button>
																	)}

																</CardFooter>
															</Card>
														</li>
													)
												})
												}
											</ul>
										</Panel>
									)
									}

								</Fragment>
							}

						</CardBody>
					</Card>

					{isTitleModalOpen && (
						<Modal title={__("Title Recommendations", "handywriter")} onRequestClose={closeTitleModal}>
							<div id={'handywriter-modal-title-suggest'} style={{width: "500px"}}>
								{
									suggestedTitles.length > 0 && suggestedTitles.map((title, index) => {
										return (
											<Card key={index}>
												<CardBody onClick={() => {
													setPostTitle(title)
												}}>
													<Button className="hw-card-button" variant="link">{title}</Button>
												</CardBody>
												<CardDivider />
											</Card>
										)
									})
								}

								{suggestedTitles.length === 0 &&
									<div>
										{__('No results have been found.', 'handywriter')}
									</div>
								}

							</div>
						</Modal>
					)}

					{isSummaryModalOpen && (
						<Modal title={__("Summary Recommendations", 'handywriter')} onRequestClose={closeSummaryModal}>
							<div id={'handywriter-modal-summary-suggest'} style={{width: "500px"}}>
								{
									summary.length > 0 && summary.map((summary, index) => {
										return (
											<Card key={index}>
												<CardBody onClick={() => {
													setContentSummary(summary)
												}}>
													<Button className="hw-card-button" variant="link">{summary}</Button>
												</CardBody>
												<CardDivider />
											</Card>
										)
									})
								}

								{summary.length === 0 &&
									<div>
										{__('No results have been found.', 'handywriter')}
									</div>
								}

							</div>
						</Modal>
					)}

					{isMetaModalOpen && (
						<Modal title={__('Recommended Meta Descriptions', 'handywriter')}
							   onRequestClose={closeMetaModal}
						>


							<div id={'handywriter-modal-summary-suggest'} style={{width: "500px"}}>
								{meta.length > 0 &&
									<Notice
										status="info"
										isDismissible={false}
									>
											{__('Click to copy the meta description to the clipboard.', 'handywriter')}
									</Notice>
								}

								{
									meta.length > 0 && meta.map((description, index) => {
										return (
											<Card key={index}>
												<CardBody
													size="large"
												>
													<Button variant="link"
															showTooltip="true"
															label={__('Click to copy!', 'handywriter')}
															id={'meta-recommendation-' + index}
															data-clipboard-target={'#meta-recommendation-' + index}
															className="copy-to-clipboard hw-card-button"
													>
														{description}
													</Button>
												</CardBody>
												<CardDivider />
											</Card>
										)
									})
								}

								{meta.length === 0 &&
									<div>
										{__('No results have been found.', 'handywriter')}
									</div>
								}

							</div>
						</Modal>
					)}
				</div>
			</PluginSidebar>

			<PluginSidebarMoreMenuItem
				target='handywriter-sidebar'
			>
				{__('Handywriter Sidebar', 'handywriter')}
			</PluginSidebarMoreMenuItem>

		</Fragment>
	);
}


registerPlugin('handywriter-sidebar', {
	render: HandywriterSidebarComponent,
	icon: icons.menuItem
});
