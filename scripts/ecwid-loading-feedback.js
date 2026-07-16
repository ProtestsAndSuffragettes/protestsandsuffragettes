/**
 * Accessible loading feedback for Ecwid product navigation and hydration.
 */
(function () {
	const dynamicStoreSelector = '#dynamic-ec-store-container .ec-store';
	const productReadySelector = `${dynamicStoreSelector} .product-details__product-title`;
	const loadingMarkerAttribute = 'data-pns-ecwid-loading';
	const loadingStageAttribute = 'data-pns-ecwid-loading-stage';
	const loadingStatusClass = 'pns-ecwid-loading-status';
	const productIdPattern = /-p([0-9]+)(?:\/|[?#&]|$)/;
	const loadingIntentTimeout = 1000;
	const loadingSafetyTimeout = 15000;

	let loadingProductId = '';
	let loadingInitialReadyElement = null;
	let loadingInitialTitle = '';
	let loadingTargetName = '';
	let loadingIntentProductId = '';
	let loadingIntentTimer = 0;
	let loadingReadyFrame = 0;
	let loadingSafetyTimer = 0;
	let initialized = false;

	function getProductIdFromUrl(url) {
		const productUrl = new URL(url, window.location.href);
		const match =
			`${productUrl.pathname}${productUrl.search}${productUrl.hash}`.match(
				productIdPattern
			);

		return match ? match[1] : '';
	}

	function getCurrentProductId() {
		return getProductIdFromUrl(window.location.href);
	}

	function getCardElement(cardWrap) {
		return cardWrap.closest('.grid-product') || cardWrap;
	}

	function getStoreElement() {
		return document.querySelector('.pns-shop-storefront, .ec-store');
	}

	function getReadyProductTitle() {
		return Array.from(document.querySelectorAll(productReadySelector)).find(
			(element) => {
				if (
					!element.textContent?.trim() ||
					!element.getClientRects().length
				) {
					return false;
				}

				let currentElement = element;

				while (
					currentElement &&
					currentElement !== document.documentElement
				) {
					const style = window.getComputedStyle(currentElement);

					if (
						currentElement.hidden ||
						currentElement.getAttribute('aria-hidden') === 'true' ||
						style.display === 'none' ||
						style.visibility === 'hidden' ||
						style.visibility === 'collapse' ||
						Number.parseFloat(style.opacity) === 0
					) {
						return false;
					}

					currentElement = currentElement.parentElement;
				}

				return true;
			}
		);
	}

	function ensureLoadingStatus() {
		let status = document.querySelector(`.${loadingStatusClass}`);

		if (status) {
			return status;
		}

		status = document.createElement('div');
		status.className = loadingStatusClass;
		status.hidden = true;
		status.setAttribute('role', 'status');
		status.setAttribute('aria-live', 'polite');
		status.setAttribute('aria-atomic', 'true');

		const spinner = document.createElement('span');
		spinner.className = `${loadingStatusClass}__spinner`;
		spinner.setAttribute('aria-hidden', 'true');

		const label = document.createElement('span');
		label.className = `${loadingStatusClass}__label`;

		status.append(spinner, label);
		document.body.append(status);

		return status;
	}

	function clearProductLoading() {
		window.clearTimeout(loadingIntentTimer);
		window.clearTimeout(loadingSafetyTimer);
		window.cancelAnimationFrame(loadingReadyFrame);
		loadingIntentTimer = 0;
		loadingReadyFrame = 0;
		loadingSafetyTimer = 0;
		loadingProductId = '';
		loadingInitialReadyElement = null;
		loadingInitialTitle = '';
		loadingTargetName = '';
		loadingIntentProductId = '';

		if (document.body) {
			document.body.removeAttribute(loadingStageAttribute);
		}

		document
			.querySelectorAll(`[${loadingMarkerAttribute}]`)
			.forEach((element) => {
				element.removeAttribute(loadingMarkerAttribute);
				element.removeAttribute('aria-busy');
			});

		const status = document.querySelector(`.${loadingStatusClass}`);

		if (status) {
			status.hidden = true;
			const label = status.querySelector(`.${loadingStatusClass}__label`);

			if (label) {
				label.textContent = '';
			}
		}
	}

	function getProductName(cardWrap) {
		return (
			getCardElement(cardWrap)
				.querySelector('.grid-product__title-inner')
				?.textContent?.trim() || ''
		);
	}

	function syncLoadingSurface() {
		if (!loadingProductId) {
			return;
		}

		if (document.body) {
			document.body.setAttribute(
				loadingStageAttribute,
				getCurrentProductId() === loadingProductId
					? 'destination'
					: 'navigation'
			);
		}

		const storeElement = getStoreElement();

		if (storeElement) {
			if (
				storeElement.getAttribute(loadingMarkerAttribute) !==
				loadingProductId
			) {
				storeElement.setAttribute(
					loadingMarkerAttribute,
					loadingProductId
				);
			}

			if (storeElement.getAttribute('aria-busy') !== 'true') {
				storeElement.setAttribute('aria-busy', 'true');
			}
		}
	}

	function monitorProductReadiness() {
		if (!loadingProductId) {
			return;
		}

		syncLoadingSurface();

		if (!maybeFinishProductLoading()) {
			loadingReadyFrame = window.requestAnimationFrame(
				monitorProductReadiness
			);
		}
	}

	function startProductLoading(productId, cardWrap) {
		if (!document.body || !productId) {
			return;
		}

		const cardElement = cardWrap ? getCardElement(cardWrap) : null;

		if (loadingProductId === productId) {
			if (cardElement) {
				cardElement.setAttribute(loadingMarkerAttribute, productId);
				cardElement.setAttribute('aria-busy', 'true');
			}

			syncLoadingSurface();
			return;
		}

		clearProductLoading();
		loadingProductId = productId;
		loadingInitialReadyElement = getReadyProductTitle();
		loadingInitialTitle =
			loadingInitialReadyElement?.textContent?.trim() || '';

		const storeElement = getStoreElement();
		const status = ensureLoadingStatus();
		const productName = cardWrap ? getProductName(cardWrap) : '';
		const label = status.querySelector(`.${loadingStatusClass}__label`);
		const loadingMessage = productName
			? `Loading ${productName}\u2026`
			: 'Loading product\u2026';

		loadingTargetName = productName;

		document.body.setAttribute(loadingMarkerAttribute, productId);
		document.body.setAttribute(
			loadingStageAttribute,
			cardWrap ? 'navigation' : 'destination'
		);

		[cardElement, storeElement].filter(Boolean).forEach((element) => {
			element.setAttribute(loadingMarkerAttribute, productId);
			element.setAttribute('aria-busy', 'true');
		});

		if (label) {
			label.textContent = loadingMessage;
		}

		/*
		 * This must be paintable during the initiating pointer/key event. Ecwid can
		 * delay or replace the document before a timer or animation frame runs.
		 */
		status.hidden = false;

		loadingSafetyTimer = window.setTimeout(
			clearProductLoading,
			loadingSafetyTimeout
		);
		loadingReadyFrame = window.requestAnimationFrame(
			monitorProductReadiness
		);
	}

	function startProductIntent(productId, cardWrap) {
		startProductLoading(productId, cardWrap);
		loadingIntentProductId = productId;
		window.clearTimeout(loadingIntentTimer);
		loadingIntentTimer = window.setTimeout(() => {
			if (loadingIntentProductId === productId) {
				clearProductLoading();
			}
		}, loadingIntentTimeout);
	}

	function commitProductLoading(productId, cardWrap) {
		window.clearTimeout(loadingIntentTimer);
		loadingIntentTimer = 0;
		loadingIntentProductId = '';

		if (loadingProductId !== productId) {
			startProductLoading(productId, cardWrap);
		}
	}

	function maybeFinishProductLoading() {
		const readyElement = getReadyProductTitle();
		const readyTitle = readyElement?.textContent?.trim() || '';
		const routeIsReady =
			getCurrentProductId() === loadingProductId &&
			readyElement &&
			(!loadingInitialReadyElement ||
				readyElement !== loadingInitialReadyElement ||
				readyTitle !== loadingInitialTitle ||
				(loadingTargetName && readyTitle === loadingTargetName));

		if (loadingProductId && routeIsReady) {
			clearProductLoading();
			return true;
		}

		return false;
	}

	function startCurrentProductLoading() {
		const productId = getCurrentProductId();

		if (productId && !getReadyProductTitle()) {
			startProductLoading(productId);
		}
	}

	function shouldHandleProductLink(event, link, productId) {
		if (
			(event.button !== undefined && event.button !== 0) ||
			event.metaKey ||
			event.altKey ||
			event.ctrlKey ||
			event.shiftKey ||
			!productId ||
			link.hasAttribute('download') ||
			(link.target && link.target !== '_self') ||
			!link.closest('.ec-store, .ecwid')
		) {
			return false;
		}

		const targetUrl = new URL(link.href, window.location.href);

		return (
			targetUrl.origin === window.location.origin &&
			targetUrl.href !== window.location.href
		);
	}

	function getProductLinkContext(event) {
		const target = event.target;

		if (!(target instanceof Element)) {
			return null;
		}

		const link = target.closest('a[href]');

		if (!link) {
			return null;
		}

		const productId =
			link
				.closest('[data-product-id]')
				?.getAttribute('data-product-id') ||
			getProductIdFromUrl(link.href);

		if (!shouldHandleProductLink(event, link, productId)) {
			return null;
		}

		return {
			cardWrap: link.closest('[data-product-id]'),
			productId,
		};
	}

	function handleProductIntent(event) {
		if (event.type === 'keydown' && event.key !== 'Enter') {
			return;
		}

		const context = getProductLinkContext(event);

		if (!context) {
			return;
		}

		startProductIntent(context.productId, context.cardWrap);
	}

	function handleProductClick(event) {
		const context = getProductLinkContext(event);

		if (!context) {
			return;
		}

		commitProductLoading(context.productId, context.cardWrap);
	}

	function handleProductIntentCancellation() {
		if (loadingIntentProductId) {
			clearProductLoading();
		}
	}

	function watchEcwidHydration() {
		if (!document.body) {
			return;
		}

		const observer = new MutationObserver(() => {
			syncLoadingSurface();
		});

		observer.observe(document.body, {
			childList: true,
			subtree: true,
		});
	}

	function initialize() {
		if (initialized || !document.body) {
			return;
		}

		initialized = true;
		ensureLoadingStatus();
		startCurrentProductLoading();
		watchEcwidHydration();
	}

	function initializeWhenBodyExists() {
		if (document.body) {
			initialize();
			return;
		}

		const bodyObserver = new MutationObserver(() => {
			if (document.body) {
				bodyObserver.disconnect();
				initialize();
			}
		});

		bodyObserver.observe(document.documentElement, {
			childList: true,
		});
	}

	window.addEventListener('pointerdown', handleProductIntent, true);
	window.addEventListener(
		'pointercancel',
		handleProductIntentCancellation,
		true
	);
	window.addEventListener('dragstart', handleProductIntentCancellation, true);
	window.addEventListener('keydown', handleProductIntent, true);
	window.addEventListener('click', handleProductClick, true);
	window.addEventListener('pageshow', (event) => {
		if (event.persisted) {
			clearProductLoading();
			startCurrentProductLoading();
		}
	});

	initializeWhenBodyExists();
})();
