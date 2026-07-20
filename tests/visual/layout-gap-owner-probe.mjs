import { chromium } from '@playwright/test';

const baseURL = process.env.PNS_BASE_URL || 'http://localhost:10008';

const routes = [
	{ name: 'home', path: '/' },
	{ name: 'mary-barbour', path: '/herstories/mary-barbour/' },
	{ name: 'edu-giveaway', path: '/edu-giveaway/' },
	{ name: 'shop', path: '/shop/' },
];

const viewports = [
	{ name: 'desktop', width: 1440, height: 1000, isMobile: false },
	{ name: 'tablet', width: 900, height: 1000, isMobile: false },
	{ name: 'mobile', width: 390, height: 900, isMobile: true },
];

function resolveURL(path) {
	return new URL(path, baseURL).toString();
}

async function waitForStableAssets(page) {
	await page.evaluate(async () => {
		for (const image of Array.from(document.images)) {
			image.loading = 'eager';
		}

		const refreshAos = () => {
			const aos = window.AOS;

			aos?.refreshHard?.();
			aos?.refresh?.();
		};

		await new Promise((resolve) => {
			setTimeout(resolve, 250);
		});
		refreshAos();

		const step = Math.max(Math.floor(window.innerHeight * 0.75), 450);

		for (
			let scrollY = 0;
			scrollY <= document.body.scrollHeight;
			scrollY += step
		) {
			window.scrollTo(0, scrollY);
			refreshAos();
			await new Promise((resolve) => {
				setTimeout(resolve, 120);
			});
		}

		document.querySelectorAll('[data-aos]').forEach((element) => {
			element.classList.add('aos-init', 'aos-animate');
		});
		refreshAos();
		window.scrollTo(0, 0);
		await new Promise((resolve) => {
			setTimeout(resolve, 250);
		});
	});

	await page.addStyleTag({
		content: `
			*, *::before, *::after {
				animation-delay: 0s !important;
				animation-duration: 0s !important;
				caret-color: transparent !important;
				scroll-behavior: auto !important;
				transition-delay: 0s !important;
				transition-duration: 0s !important;
			}

			[data-aos] {
				opacity: 1 !important;
				pointer-events: auto !important;
				visibility: visible !important;
			}

			.wp-block-jetpack-slideshow_swiper-wrapper {
				transform: none !important;
			}
		`,
	});

	await page.evaluate(async () => {
		const timeout = new Promise((resolve) => {
			setTimeout(resolve, 2000);
		});

		const fontsReady =
			'fonts' in document
				? document.fonts.ready
						.then(() => undefined)
						.catch(() => undefined)
				: Promise.resolve();

		const imagesReady = Promise.all(
			Array.from(document.images).map((image) => {
				if (image.complete) {
					return Promise.resolve();
				}

				return new Promise((resolve) => {
					image.addEventListener('load', () => resolve(), {
						once: true,
					});
					image.addEventListener('error', () => resolve(), {
						once: true,
					});
				});
			})
		).then(() => undefined);

		await Promise.race([Promise.all([fontsReady, imagesReady]), timeout]);
	});
}

async function collectRoute(page) {
	return await page.evaluate(() => {
		const ownerSelectors = [
			['header-navigation', 'header .wp-block-navigation'],
			['navigation', '.wp-block-navigation'],
			['social-links', '.wp-block-social-links'],
			['columns', '.wp-block-columns'],
			['buttons', '.wp-block-buttons'],
			['entry-navigation', '.pns-entry-navigation'],
			['shop-intro', '.shop-intro'],
			['connect-social', '.pns-connect-social'],
			['contact-form-octopus', '.pns-contact-form-octopus'],
			['contact-form', '.pns-contact-form'],
			['active-dates', '.active-dates'],
			[
				'pns-image-strip',
				':is(.pns-image-strip, .pns-suffragette-image-strip)',
			],
			['pns-contact-form', '.pns-contact-form'],
			['pns-connect-social', '.pns-connect-social'],
			['pands-logo', '.pands-logo'],
			['site-blocks', '.wp-site-blocks'],
		];

		function ownerGuess(element) {
			const matches = ownerSelectors
				.filter(([, selector]) => element.closest(selector))
				.map(([owner]) => owner);

			return matches.length ? matches.join(' ') : 'unclassified';
		}

		function shortSelector(element) {
			const parts = [];
			let current = element;

			while (current && current !== document.body && parts.length < 5) {
				let part = current.tagName.toLowerCase();

				if (current.id) {
					part += `#${current.id}`;
				}

				const classes = Array.from(current.classList).slice(0, 5);

				if (classes.length) {
					part += `.${classes.join('.')}`;
				}

				parts.unshift(part);
				current = current.parentElement;
			}

			return parts.join(' > ');
		}

		function readElement(element, index) {
			const computed = getComputedStyle(element);
			const rect = element.getBoundingClientRect();

			return {
				index,
				ownerGuess: ownerGuess(element),
				selector: shortSelector(element),
				tagName: element.tagName.toLowerCase(),
				id: element.id || '',
				className: element.className || '',
				display: computed.display,
				position: computed.position,
				gap: computed.gap,
				rowGap: computed.rowGap,
				columnGap: computed.columnGap,
				marginBlockStart: computed.marginBlockStart,
				marginBlockEnd: computed.marginBlockEnd,
				marginTop: computed.marginTop,
				marginBottom: computed.marginBottom,
				inlineSize: computed.inlineSize,
				blockSize: computed.blockSize,
				width: Math.round(rect.width * 100) / 100,
				height: Math.round(rect.height * 100) / 100,
				offsetTop: Math.round(element.offsetTop * 100) / 100,
			};
		}

		const flexLayouts = Array.from(
			document.querySelectorAll('body .is-layout-flex')
		).map(readElement);

		const rootFlowSpacing = Array.from(
			document.querySelectorAll(
				'body .is-layout-flow > * + *, body .wp-site-blocks > * + *'
			)
		).map(readElement);

		const topLevelSiteSiblings = Array.from(
			document.querySelectorAll('body .wp-site-blocks > * + *')
		).map(readElement);

		const documentElement = document.documentElement;
		const rootComputed = getComputedStyle(documentElement);

		return {
			url: window.location.href,
			scrollHeight: document.body.scrollHeight,
			documentScrollHeight: documentElement.scrollHeight,
			bodyClientWidth: document.body.clientWidth,
			documentClientWidth: documentElement.clientWidth,
			rootBlockGap: rootComputed
				.getPropertyValue('--wp--style--block-gap')
				.trim(),
			flexLayouts,
			rootFlowSpacing,
			topLevelSiteSiblings,
		};
	});
}

const browser = await chromium.launch();
const results = [];

try {
	for (const viewport of viewports) {
		const context = await browser.newContext({
			viewport: {
				width: viewport.width,
				height: viewport.height,
			},
			isMobile: viewport.isMobile,
		});

		const page = await context.newPage();

		for (const route of routes) {
			await page.goto(resolveURL(route.path));
			await page.waitForLoadState('domcontentloaded');
			await waitForStableAssets(page);

			results.push({
				route: route.name,
				path: route.path,
				viewport: viewport.name,
				viewportWidth: viewport.width,
				...(await collectRoute(page)),
			});
		}

		await context.close();
	}
} finally {
	await browser.close();
}

console.log(JSON.stringify(results, null, 2));
