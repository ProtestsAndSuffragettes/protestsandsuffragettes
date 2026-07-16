import { chromium } from '@playwright/test';

const baseURL = process.env.PNS_BASE_URL || 'http://localhost:10008';

const routes = [
	{ name: 'home', path: '/' },
	{ name: 'mary-barbour', path: '/herstories/mary-barbour/' },
	{ name: 'edu-giveaway', path: '/edu-giveaway/' },
	{ name: 'shop', path: '/shop/' },
];

const viewports = [
	{ name: 'mobile', width: 390, height: 900, isMobile: true },
	{ name: 'tablet', width: 900, height: 1000, isMobile: false },
	{ name: 'pre-wide', width: 1279, height: 1000, isMobile: false },
	{ name: 'wide-boundary', width: 1280, height: 1000, isMobile: false },
	{ name: 'desktop', width: 1440, height: 1000, isMobile: false },
	{ name: 'wide-desktop', width: 1920, height: 1000, isMobile: false },
];

function resolveURL(path) {
	return new URL(path, baseURL).toString();
}

async function waitForStablePage(page) {
	await page.waitForLoadState('domcontentloaded');

	await page.evaluate(async () => {
		await new Promise((resolve) => {
			setTimeout(resolve, 500);
		});

		if ('fonts' in document) {
			await document.fonts.ready.catch(() => undefined);
		}
	});
}

async function collectRoute(page) {
	return await page.evaluate(() => {
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
			const parent = element.parentElement;
			const parentComputed = parent ? getComputedStyle(parent) : null;

			return {
				index,
				selector: shortSelector(element),
				parentSelector: parent ? shortSelector(parent) : '',
				parentLayoutConstrained: Boolean(
					parent?.classList.contains('is-layout-constrained')
				),
				parentDisplay: parentComputed?.display || '',
				className: element.className,
				maxWidth: computed.maxWidth,
				maxInlineSize: computed.maxInlineSize,
				inlineSize: computed.inlineSize,
				width: Math.round(rect.width * 100) / 100,
				left: Math.round(rect.left * 100) / 100,
				right: Math.round(rect.right * 100) / 100,
				paddingLeft: computed.paddingLeft,
				paddingRight: computed.paddingRight,
				marginLeft: computed.marginLeft,
				marginRight: computed.marginRight,
			};
		}

		const alignwide = Array.from(document.querySelectorAll('.alignwide'))
			.filter((element) => element.getBoundingClientRect().width > 0)
			.map(readElement);

		const rootComputed = getComputedStyle(document.documentElement);

		return {
			url: window.location.href,
			scrollWidth: document.documentElement.scrollWidth,
			clientWidth: document.documentElement.clientWidth,
			scrollHeight: document.body.scrollHeight,
			themeWideSize: rootComputed
				.getPropertyValue('--wp--style--global--wide-size')
				.trim(),
			alignwide,
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
			await waitForStablePage(page);

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
