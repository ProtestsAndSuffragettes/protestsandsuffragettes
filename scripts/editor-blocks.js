(function (wp) {
	const { Button, FocalPointPicker, Placeholder, Spinner } = wp.components;
	const { createHigherOrderComponent } = wp.compose;
	const { useSelect } = wp.data;
	const { domReady } = wp;
	const { createElement, Fragment, useEffect } = wp.element;
	const { addFilter } = wp.hooks;
	const { __ } = wp.i18n;

	const navigationBlockName = 'core/navigation';
	const syncedPatternBlockName = 'core/block';
	const primaryNavigationSlug = 'pns-primary-navigation';
	const ctaNavigationClass = 'pns-cross-site-banner-cta';
	const lightSurfaceEditorClass = 'is-pns-light-surface-template';
	const fullWidthNewsEditorClass = 'is-pns-full-width-news-template';
	const standardPostEditorClass = 'is-pns-standard-post';
	const lightSurfaceTemplates = new Set([
		'page',
		'page-light-surface',
		'page-light-surface-wide-content',
		'page-light-surface-no-contact-form',
		'page-search',
		'single',
		'single-full-width-news',
	]);
	const fullWidthNewsTemplates = new Set(['single-full-width-news']);
	const featuredImageFocusSettings = window.pnsThemeFeaturedImageFocus || {};
	const featuredImageFocusMetaKeys =
		featuredImageFocusSettings.metaKeys || {};
	const featuredImageFocusPostTypes = new Set(
		featuredImageFocusSettings.postTypes || []
	);
	const refSlugAttribute = 'pnsRefSlug';
	const hasFeaturedImageFocusPluginSupport = Boolean(
		wp.coreData?.useEntityProp &&
		wp.editPost?.PluginDocumentSettingPanel &&
		wp.plugins?.registerPlugin
	);
	/*
	 * Editor bridge index:
	 * - pnsRefSlug metadata and live ref resolution for Navigation/synced patterns.
	 * - Theme-owned Navigation overlay cleanup and inspector-panel hiding.
	 * - Navigation block-gap preview variables for CTA and primary nav fixtures.
	 * - Template-specific canvas class parity.
	 * - Featured-image focus panel registration, ordering, and meta updates.
	 * - Template-gated editorial header strapline controls.
	 */

	const isCtaNavigation = (attributes = {}) =>
		typeof attributes.className === 'string' &&
		attributes.className.split(/\s+/).includes(ctaNavigationClass);

	const isPrimaryNavigation = (attributes = {}) =>
		attributes?.[refSlugAttribute] === primaryNavigationSlug;

	const isThemeOwnedOverlayNavigation = (attributes = {}) =>
		isPrimaryNavigation(attributes) || isCtaNavigation(attributes);

	const addNavigationMetadataAttributes = (settings, name) => {
		if (![navigationBlockName, syncedPatternBlockName].includes(name)) {
			return settings;
		}

		return {
			...settings,
			attributes: {
				...settings.attributes,
				[refSlugAttribute]: {
					type: 'string',
				},
			},
		};
	};

	const useNavigationRecords = () =>
		useSelect(
			(select) =>
				select('core').getEntityRecords('postType', 'wp_navigation', {
					per_page: -1,
					status: 'publish',
					orderby: 'title',
					order: 'asc',
					_fields: 'id,slug,title',
				}),
			[]
		);

	const useSyncedPatternRecords = () =>
		useSelect(
			(select) =>
				select('core').getEntityRecords('postType', 'wp_block', {
					per_page: -1,
					status: 'publish',
					orderby: 'title',
					order: 'asc',
					_fields: 'id,slug,title',
				}),
			[]
		);

	const getRecordBySlug = (records, slug) => {
		if (!Array.isArray(records) || !slug) {
			return undefined;
		}

		return records.find((record) => record?.slug === slug);
	};

	const TemplateReferencePlaceholder = ({ isResolving }) =>
		createElement(
			Placeholder,
			{
				className: 'pns-template-reference-placeholder',
				label: __(
					'Synced pattern reference',
					'protestsandsuffragettes'
				),
			},
			isResolving
				? createElement(Spinner)
				: __(
						'The referenced synced pattern could not be found.',
						'protestsandsuffragettes'
					)
		);

	const isZeroCssSpacing = (value) =>
		typeof value === 'string' &&
		/^0(?:px|rem|em|%)?(?:\s+0(?:px|rem|em|%)?)*$/.test(value.trim());

	const getNavigationBlockGapValue = (blockGap, preserveZero = false) => {
		if (typeof blockGap === 'string') {
			return isZeroCssSpacing(blockGap) && !preserveZero ? '' : blockGap;
		}

		if (blockGap && typeof blockGap === 'object') {
			const rowGap =
				isZeroCssSpacing(blockGap.top) && !preserveZero
					? ''
					: blockGap.top || '';
			const columnGap =
				isZeroCssSpacing(blockGap.left) && !preserveZero
					? ''
					: blockGap.left || '';

			if (rowGap && columnGap) {
				return `${rowGap} ${columnGap}`;
			}

			return columnGap || rowGap;
		}

		return '';
	};

	const withCtaNavigationOverlayCleanup = createHigherOrderComponent(
		(BlockEdit) => (props) => {
			const shouldClean =
				navigationBlockName === props.name &&
				isCtaNavigation(props.attributes);
			const {
				customOverlayBackgroundColor,
				customOverlayTextColor,
				overlay,
				overlayBackgroundColor,
				overlayMenu,
				overlayTextColor,
			} = props.attributes || {};

			useEffect(() => {
				if (!shouldClean) {
					return;
				}

				if (
					overlayMenu === 'never' &&
					!customOverlayBackgroundColor &&
					!customOverlayTextColor &&
					!overlay &&
					!overlayBackgroundColor &&
					!overlayTextColor
				) {
					return;
				}

				props.setAttributes({
					customOverlayBackgroundColor: undefined,
					customOverlayTextColor: undefined,
					overlay: undefined,
					overlayBackgroundColor: undefined,
					overlayMenu: 'never',
					overlayTextColor: undefined,
				});
			}, [
				shouldClean,
				customOverlayBackgroundColor,
				customOverlayTextColor,
				overlay,
				overlayBackgroundColor,
				overlayMenu,
				overlayTextColor,
			]);

			return createElement(BlockEdit, props);
		},
		'withCtaNavigationOverlayCleanup'
	);

	const withNavigationOverlayTemplateCleanup = createHigherOrderComponent(
		(BlockEdit) => (props) => {
			const shouldClean =
				navigationBlockName === props.name &&
				isPrimaryNavigation(props.attributes);
			const { hasIcon, icon, overlay } = props.attributes || {};

			useEffect(() => {
				if (
					!shouldClean ||
					(!overlay && !icon && hasIcon === undefined)
				) {
					return;
				}

				props.setAttributes({
					hasIcon: undefined,
					icon: undefined,
					overlay: undefined,
				});
			}, [shouldClean, hasIcon, icon, overlay]);

			return createElement(BlockEdit, props);
		},
		'withNavigationOverlayTemplateCleanup'
	);

	const withNavigationRefSlugResolution = createHigherOrderComponent(
		(BlockEdit) => (props) => {
			const refSlug = props.attributes?.[refSlugAttribute];
			const navigations = useNavigationRecords();
			const syncedPatterns = useSyncedPatternRecords();
			const isReferenceBackedBlock = [
				navigationBlockName,
				syncedPatternBlockName,
			].includes(props.name);
			const hasSlugReference =
				typeof refSlug === 'string' && refSlug.length > 0;
			const records =
				navigationBlockName === props.name
					? navigations
					: syncedPatterns;
			const refRecord =
				navigationBlockName === props.name
					? getRecordBySlug(navigations, refSlug)
					: getRecordBySlug(syncedPatterns, refSlug);
			const shouldResolve =
				isReferenceBackedBlock && hasSlugReference && refRecord?.id;

			if (
				syncedPatternBlockName === props.name &&
				hasSlugReference &&
				!refRecord?.id
			) {
				return createElement(TemplateReferencePlaceholder, {
					isResolving: !Array.isArray(records),
				});
			}

			const resolvedProps = shouldResolve
				? {
						...props,
						attributes: {
							...props.attributes,
							ref: refRecord.id,
						},
					}
				: props;

			return createElement(BlockEdit, resolvedProps);
		},
		'withNavigationRefSlugResolution'
	);

	const withNavigationGapPreview = createHigherOrderComponent(
		(BlockListBlock) => (props) => {
			const className = props.attributes?.className || '';
			const isCta = className.split(/\s+/).includes(ctaNavigationClass);
			const savedBlockGap = props.attributes?.style?.spacing?.blockGap;
			const blockGap = getNavigationBlockGapValue(savedBlockGap, !isCta);
			const ctaBlockGap = isCta
				? getNavigationBlockGapValue(savedBlockGap)
				: '';

			if (
				navigationBlockName !== props.name ||
				(!blockGap && !ctaBlockGap)
			) {
				return createElement(BlockListBlock, props);
			}

			return createElement(BlockListBlock, {
				...props,
				wrapperProps: {
					...props.wrapperProps,
					style: {
						...props.wrapperProps?.style,
						...(blockGap
							? {
									'--pns--navigation--gap': blockGap,
								}
							: {}),
						...(ctaBlockGap
							? {
									'--pns--cross-site-banner-cta-gap':
										ctaBlockGap,
								}
							: {}),
					},
				},
			});
		},
		'withNavigationGapPreview'
	);

	const setNavigationOverlayPanelHidden = (panel, isHidden) => {
		panel.classList.toggle('is-pns-navigation-overlay-panel', isHidden);
		panel.hidden = isHidden;

		if (isHidden) {
			panel.setAttribute('aria-hidden', 'true');
			return;
		}

		panel.removeAttribute('aria-hidden');
	};

	const getSelectedBlock = () => {
		const blockEditorStore = wp.data.select('core/block-editor');

		return blockEditorStore?.getSelectedBlock?.();
	};

	const isCoreNavigationOverlayPanel = (panel) => {
		if (
			panel.querySelector(
				[
					'.wp-block-navigation__overlay-selector',
					'.wp-block-navigation__overlay-menu-preview',
					'.wp-block-navigation__overlay-preview',
					'.wp-block-navigation__deleted-overlay-warning',
				].join(',')
			)
		) {
			return true;
		}

		const title = panel.querySelector(
			'.components-panel__body-title button, .components-panel__body-title'
		);

		return title?.textContent?.trim() === __('Overlay');
	};

	const refreshNavigationOverlayPanelVisibility = () => {
		const selectedBlock = getSelectedBlock();
		const isNavigationSelected =
			selectedBlock?.name === navigationBlockName &&
			isThemeOwnedOverlayNavigation(selectedBlock?.attributes);

		document
			.querySelectorAll(
				'.components-panel__body.is-pns-navigation-overlay-panel'
			)
			.forEach((panel) => setNavigationOverlayPanelHidden(panel, false));

		if (!isNavigationSelected) {
			return;
		}

		document
			.querySelectorAll('.components-panel__body')
			.forEach((panel) => {
				if (isCoreNavigationOverlayPanel(panel)) {
					setNavigationOverlayPanelHidden(panel, true);
				}
			});
	};

	const observeNavigationOverlayPanel = () => {
		let isRefreshScheduled = false;
		const scheduleRefresh = () => {
			if (isRefreshScheduled) {
				return;
			}

			isRefreshScheduled = true;
			window.requestAnimationFrame(() => {
				isRefreshScheduled = false;
				refreshNavigationOverlayPanelVisibility();
			});
		};

		wp.data.subscribe(scheduleRefresh);

		if (document.body) {
			new MutationObserver(scheduleRefresh).observe(document.body, {
				childList: true,
				subtree: true,
			});
		}

		scheduleRefresh();
	};

	const getEditedPostTemplate = () => {
		const editorStore = wp.data.select('core/editor');
		const template =
			editorStore?.getEditedPostAttribute?.('template') ||
			editorStore?.getCurrentPost?.()?.template ||
			'';

		if (template && 'default' !== template) {
			return template;
		}

		const postType =
			editorStore?.getCurrentPostType?.() ||
			editorStore?.getCurrentPost?.()?.type ||
			'';
		const defaultTemplateByPostType = {
			page: 'page',
			post: 'single',
		};

		return defaultTemplateByPostType[postType] || '';
	};

	const getEditedPostType = () => {
		const editorStore = wp.data.select('core/editor');

		return (
			editorStore?.getCurrentPostType?.() ||
			editorStore?.getCurrentPost?.()?.type ||
			''
		);
	};

	const updateLightSurfaceEditorCanvas = () => {
		const editedPostTemplate = getEditedPostTemplate();
		const isStandardPost = 'post' === getEditedPostType();
		const isLightSurfaceTemplate =
			lightSurfaceTemplates.has(editedPostTemplate);
		const isFullWidthNewsTemplate =
			fullWidthNewsTemplates.has(editedPostTemplate);
		const editorCanvas = document.querySelector(
			'iframe[name="editor-canvas"]'
		);
		const editorCanvasBody = editorCanvas?.contentDocument?.body;

		document.body.classList.toggle(
			lightSurfaceEditorClass,
			isLightSurfaceTemplate
		);
		editorCanvasBody?.classList.toggle(
			lightSurfaceEditorClass,
			isLightSurfaceTemplate
		);
		document.body.classList.toggle(
			fullWidthNewsEditorClass,
			isFullWidthNewsTemplate
		);
		editorCanvasBody?.classList.toggle(
			fullWidthNewsEditorClass,
			isFullWidthNewsTemplate
		);
		document.body.classList.toggle(standardPostEditorClass, isStandardPost);
		editorCanvasBody?.classList.toggle(
			standardPostEditorClass,
			isStandardPost
		);
	};

	const observeLightSurfaceEditorCanvas = () => {
		let isRefreshScheduled = false;
		const scheduleRefresh = () => {
			if (isRefreshScheduled) {
				return;
			}

			isRefreshScheduled = true;
			window.requestAnimationFrame(() => {
				isRefreshScheduled = false;
				updateLightSurfaceEditorCanvas();
			});
		};

		wp.data.subscribe(scheduleRefresh);

		if (document.body) {
			new MutationObserver(scheduleRefresh).observe(document.body, {
				childList: true,
				subtree: true,
			});
		}

		scheduleRefresh();
	};

	const getFeaturedImageFocusPostType = () =>
		useSelect((select) => {
			const editor = select('core/editor');

			return (
				editor?.getCurrentPostType?.() ||
				editor?.getCurrentPost?.()?.type ||
				''
			);
		}, []);

	const getFeaturedImageFocusMediaId = () =>
		useSelect(
			(select) =>
				select('core/editor')?.getEditedPostAttribute?.(
					'featured_media'
				) || 0,
			[]
		);

	const getFeaturedImageFocusMedia = (mediaId) =>
		useSelect(
			(select) => (mediaId ? select('core')?.getMedia?.(mediaId) : null),
			[mediaId]
		);

	const normalizeFeaturedImageFocusCoordinate = (value) => {
		const number = parseFloat(value);

		if (!Number.isFinite(number)) {
			return 0.5;
		}

		return Math.min(1, Math.max(0, number));
	};

	const moveFeaturedImageFocusPanelFirst = () => {
		const panel = document.querySelector(
			'.components-panel__body.pns-featured-image-focus-panel'
		);
		const panelContainer = panel?.parentElement;
		const firstPanel = panelContainer?.querySelector(
			'.components-panel__body'
		);

		if (!panel || !panelContainer || !firstPanel || firstPanel === panel) {
			return;
		}

		panelContainer.insertBefore(panel, firstPanel);
	};

	const moveJetpackSeoPanelBelowTags = () => {
		const seoPanel = document.querySelector(
			'.components-panel__body.jetpack-seo-panel'
		);
		const panelContainer = seoPanel?.parentElement;
		const tagsPanel = Array.from(panelContainer?.children || []).find(
			(panel) =>
				panel.classList?.contains('components-panel__body') &&
				panel.querySelector('button')?.textContent?.trim() === 'Tags'
		);

		if (
			!seoPanel ||
			!tagsPanel ||
			seoPanel.previousElementSibling === tagsPanel
		) {
			return;
		}

		tagsPanel.insertAdjacentElement('afterend', seoPanel);
	};

	const observeFeaturedImageFocusPanelPosition = () => {
		let isRefreshScheduled = false;
		const scheduleRefresh = () => {
			if (isRefreshScheduled) {
				return;
			}

			isRefreshScheduled = true;
			window.requestAnimationFrame(() => {
				isRefreshScheduled = false;
				moveFeaturedImageFocusPanelFirst();
				moveJetpackSeoPanelBelowTags();
			});
		};

		if (document.body) {
			new MutationObserver(scheduleRefresh).observe(document.body, {
				childList: true,
				subtree: true,
			});
		}

		scheduleRefresh();
	};

	const FeaturedImageFocusPanel = () => {
		const postType = getFeaturedImageFocusPostType();
		const mediaId = getFeaturedImageFocusMediaId();
		const media = getFeaturedImageFocusMedia(mediaId);
		const [meta, setMeta] = wp.coreData.useEntityProp(
			'postType',
			postType || 'post',
			'meta'
		);
		const xMetaKey = featuredImageFocusMetaKeys.x;
		const yMetaKey = featuredImageFocusMetaKeys.y;
		const isSupportedPostType =
			postType && featuredImageFocusPostTypes.has(postType);

		if (
			!isSupportedPostType ||
			!xMetaKey ||
			!yMetaKey ||
			!Number.isFinite(Number(mediaId)) ||
			Number(mediaId) <= 0
		) {
			return null;
		}

		const focusPoint = {
			x: normalizeFeaturedImageFocusCoordinate(meta?.[xMetaKey]),
			y: normalizeFeaturedImageFocusCoordinate(meta?.[yMetaKey]),
		};
		const imageUrl =
			media?.source_url || media?.media_details?.sizes?.full?.source_url;

		if (!imageUrl) {
			return null;
		}

		return createElement(
			wp.editPost.PluginDocumentSettingPanel,
			{
				className: 'pns-featured-image-focus-panel',
				name: 'pns-featured-image-focus',
				title: __('Featured image focus', 'protestsandsuffragettes'),
			},
			createElement(
				Fragment,
				null,
				createElement(FocalPointPicker, {
					label: __('Focus point', 'protestsandsuffragettes'),
					url: imageUrl,
					value: focusPoint,
					onChange(value) {
						setMeta({
							...meta,
							[xMetaKey]: normalizeFeaturedImageFocusCoordinate(
								value?.x
							),
							[yMetaKey]: normalizeFeaturedImageFocusCoordinate(
								value?.y
							),
						});
					},
				}),
				createElement(
					Button,
					{
						variant: 'secondary',
						onClick() {
							setMeta({
								...meta,
								[xMetaKey]: 0.5,
								[yMetaKey]: 0.5,
							});
						},
					},
					__('Reset to center', 'protestsandsuffragettes')
				)
			)
		);
	};

	addFilter(
		'blocks.registerBlockType',
		'pns/navigation-metadata-attributes',
		addNavigationMetadataAttributes
	);

	addFilter(
		'editor.BlockEdit',
		'pns/navigation-ref-slug-resolution',
		withNavigationRefSlugResolution
	);

	addFilter(
		'editor.BlockEdit',
		'pns/cta-navigation-overlay-cleanup',
		withCtaNavigationOverlayCleanup
	);

	addFilter(
		'editor.BlockEdit',
		'pns/navigation-overlay-template-cleanup',
		withNavigationOverlayTemplateCleanup
	);

	addFilter(
		'editor.BlockListBlock',
		'pns/navigation-gap-preview',
		withNavigationGapPreview
	);

	domReady(() => {
		observeNavigationOverlayPanel();
		observeLightSurfaceEditorCanvas();
		observeFeaturedImageFocusPanelPosition();
	});

	if (hasFeaturedImageFocusPluginSupport) {
		wp.plugins.registerPlugin('pns-featured-image-focus', {
			render: FeaturedImageFocusPanel,
		});
	}
})(window.wp);
