/*! Hungry Flamingo Blog Companion — blocks.js */
(function (wp) {
	'use strict';

	if (!wp || !wp.blocks || !wp.element || !wp.components || !wp.blockEditor || !wp.serverSideRender) {
		return;
	}

	var __ = wp.i18n.__;
	var createElement = wp.element.createElement;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var RangeControl = wp.components.RangeControl;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var ToggleControl = wp.components.ToggleControl;
	var ServerSideRender = wp.serverSideRender.default || wp.serverSideRender;
	var registerBlockType = wp.blocks.registerBlockType;

	function panel(children) {
		return createElement(
			InspectorControls,
			null,
			createElement(
				PanelBody,
				{
					title: __('Settings', 'hungry-flamingo-blog-companion'),
					initialOpen: true
				},
				children
			)
		);
	}

	function serverPreview(name, attributes) {
		return createElement(ServerSideRender, {
			block: name,
			attributes: attributes
		});
	}

	registerBlockType('hfb/post-stack', {
		title: __('Post Stack', 'hungry-flamingo-blog-companion'),
		description: __('Appends the continuous-reading stack for the current post.', 'hungry-flamingo-blog-companion'),
		category: 'widgets',
		icon: 'welcome-write-blog',
		attributes: {
			stackSize: {
				type: 'number',
				default: 5
			}
		},
		supports: {
			align: ['wide', 'full']
		},
		edit: function (props) {
			var attributes = props.attributes;

			return createElement(
				'div',
				null,
				panel([
					createElement(RangeControl, {
						key: 'stackSize',
						label: __('Stack size', 'hungry-flamingo-blog-companion'),
						min: 1,
						max: 10,
						value: attributes.stackSize,
						onChange: function (value) {
							props.setAttributes({ stackSize: value || 1 });
						}
					})
				]),
				serverPreview('hfb/post-stack', attributes)
			);
		},
		save: function () {
			return null;
		}
	});

	registerBlockType('hfb/related-posts', {
		title: __('Related Posts', 'hungry-flamingo-blog-companion'),
		description: __('Shows local related articles for the current public post.', 'hungry-flamingo-blog-companion'),
		category: 'widgets',
		icon: 'admin-links',
		attributes: {
			heading: {
				type: 'string',
				default: __('Read next', 'hungry-flamingo-blog-companion')
			},
			count: {
				type: 'number',
				default: 3
			},
			showExcerpt: {
				type: 'boolean',
				default: true
			}
		},
		supports: {
			align: ['wide', 'full']
		},
		edit: function (props) {
			var attributes = props.attributes;

			return createElement(
				'div',
				null,
				panel([
					createElement(TextControl, {
						key: 'heading',
						label: __('Heading', 'hungry-flamingo-blog-companion'),
						value: attributes.heading || '',
						onChange: function (value) {
							props.setAttributes({ heading: value });
						}
					}),
					createElement(RangeControl, {
						key: 'count',
						label: __('Post count', 'hungry-flamingo-blog-companion'),
						min: 1,
						max: 12,
						value: attributes.count,
						onChange: function (value) {
							props.setAttributes({ count: value || 1 });
						}
					}),
					createElement(ToggleControl, {
						key: 'showExcerpt',
						label: __('Show excerpts', 'hungry-flamingo-blog-companion'),
						checked: attributes.showExcerpt !== false,
						onChange: function (value) {
							props.setAttributes({ showExcerpt: value });
						}
					})
				]),
				serverPreview('hfb/related-posts', attributes)
			);
		},
		save: function () {
			return null;
		}
	});

	registerBlockType('hfb/reader-cta', {
		title: __('Reader CTA', 'hungry-flamingo-blog-companion'),
		description: __('Adds a provider-neutral post-end call-to-action slot.', 'hungry-flamingo-blog-companion'),
		category: 'widgets',
		icon: 'megaphone',
		attributes: {
			eyebrow: {
				type: 'string',
				default: __('Keep reading', 'hungry-flamingo-blog-companion')
			},
			title: {
				type: 'string',
				default: __('Find the next useful article', 'hungry-flamingo-blog-companion')
			},
			body: {
				type: 'string',
				default: __('Use this slot for a local editorial prompt, a series landing page, or an RSS follow link.', 'hungry-flamingo-blog-companion')
			},
			buttonText: {
				type: 'string',
				default: __('Browse articles', 'hungry-flamingo-blog-companion')
			},
			url: {
				type: 'string',
				default: ''
			}
		},
		supports: {
			align: ['wide', 'full']
		},
		edit: function (props) {
			var attributes = props.attributes;

			return createElement(
				'div',
				null,
				panel([
					createElement(TextControl, {
						key: 'eyebrow',
						label: __('Eyebrow', 'hungry-flamingo-blog-companion'),
						value: attributes.eyebrow || '',
						onChange: function (value) {
							props.setAttributes({ eyebrow: value });
						}
					}),
					createElement(TextControl, {
						key: 'title',
						label: __('Title', 'hungry-flamingo-blog-companion'),
						value: attributes.title || '',
						onChange: function (value) {
							props.setAttributes({ title: value });
						}
					}),
					createElement(TextareaControl, {
						key: 'body',
						label: __('Body', 'hungry-flamingo-blog-companion'),
						value: attributes.body || '',
						onChange: function (value) {
							props.setAttributes({ body: value });
						}
					}),
					createElement(TextControl, {
						key: 'buttonText',
						label: __('Button text', 'hungry-flamingo-blog-companion'),
						value: attributes.buttonText || '',
						onChange: function (value) {
							props.setAttributes({ buttonText: value });
						}
					}),
					createElement(TextControl, {
						key: 'url',
						label: __('Button URL', 'hungry-flamingo-blog-companion'),
						type: 'url',
						value: attributes.url || '',
						onChange: function (value) {
							props.setAttributes({ url: value });
						}
					})
				]),
				serverPreview('hfb/reader-cta', attributes)
			);
		},
		save: function () {
			return null;
		}
	});
})(window.wp);
