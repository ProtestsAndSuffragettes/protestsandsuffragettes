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

			return {
				index,
				selector: shortSelector(element),
				parentSelector: parent ? shortSelector(parent) : '',
				tagName: element.tagName.toLowerCase(),
				id: element.id || '',
				className: element.className || '',
				maxWidth: computed.maxWidth,
				maxInlineSize: computed.maxInlineSize,
				width: Math.round(rect.width * 100) / 100,
				parentWidth: parent
					? Math.round(parent.getBoundingClientRect().width * 100) /
						100
					: null,
			};
		}

		const constrainedChildren = Array.from(
			document.querySelectorAll(
				'body .is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull):not(.alignwide))'
			)
		)
			.filter((element) => element.getBoundingClientRect().width > 0)
			.map(readElement);

		const generatedSixtySixRules = Array.from(document.styleSheets)
			.filter(
				(sheet) =>
					sheet.ownerNode?.id === 'core-block-supports-inline-css'
			)
			.flatMap((sheet) => Array.from(sheet.cssRules || []))
			.map((rule) => rule.cssText)
			.filter((cssText) => cssText.includes('66.66%'));

		return {
			url: window.location.href,
			scrollHeight: document.body.scrollHeight,
			constrainedChildren,
			generatedSixtySixRules,
		};
	});
}

const browser = await chromium.launch();
const results = [];
let failures = 0;

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

			const result = {
				route: route.name,
				path: route.path,
				viewport: viewport.name,
				viewportWidth: viewport.width,
				...(await collectRoute(page)),
			};

			const invalidGeneratedRules =
				result.generatedSixtySixRules.length > 0;
			const invalidConstrainedWidths = result.constrainedChildren.filter(
				(element) => element.maxWidth === '66.66%'
			);

			if (invalidGeneratedRules || invalidConstrainedWidths.length) {
				failures += 1;
				result.failure = {
					invalidGeneratedRules,
					invalidConstrainedWidths,
				};
			}

			results.push(result);
		}

		await context.close();
	}
} finally {
	await browser.close();
}

console.log(JSON.stringify(results, null, 2));

if (failures > 0) {
	process.exit(1);
}
