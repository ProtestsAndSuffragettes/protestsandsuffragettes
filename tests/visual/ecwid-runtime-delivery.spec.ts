import { expect, test, type Page } from '@playwright/test';

async function getEcwidAssetState(page: Page) {
	return page.evaluate(() => ({
		cartWidgets: document.querySelectorAll('.ec-cart-widget').length,
		ecwidCss: Boolean(document.querySelector('#ecwid-css-css')),
		ecwidFrontendScript: Boolean(
			document.querySelector(
				'#ecwid-frontend-js-js, #ecwid-frontend-js-js-extra, script[data-handles*="ecwid-frontend-js"]'
			)
		),
		ecwidHeadHints: Array.from(
			document.head.querySelectorAll<HTMLLinkElement>('link[href]')
		).filter(({ href }) =>
			/(?:app\.ecwid\.com|ecomm\.events|d1q3axnfhmyveb\.cloudfront\.net|dqzrr9k4bjpzk\.cloudfront\.net|d1oxsl77a1kjht\.cloudfront\.net)/.test(
				href
			)
		).length,
		staticTeaser: document.querySelectorAll('.ran-ecwid-shop-teaser-card')
			.length,
		storefront: Boolean(
			document.querySelector(
				'#static-ec-store-container, #dynamic-ec-store-container, .pns-shop-storefront'
			)
		),
	}));
}

test('@fast @ecwid native Ecwid runtime stays on storefront routes only', async ({
	page,
}) => {
	for (const route of ['/', '/news/', '/herstories/']) {
		await page.goto(route, { waitUntil: 'domcontentloaded' });

		const state = await getEcwidAssetState(page);

		expect(state.ecwidCss).toBe(false);
		expect(state.ecwidFrontendScript).toBe(false);
		expect(state.ecwidHeadHints).toBe(0);
		expect(state.cartWidgets).toBe(0);

		if ('/' === route) {
			expect(state.staticTeaser).toBeGreaterThanOrEqual(3);
		}
	}

	await page.goto('/shop/', { waitUntil: 'domcontentloaded' });

	const storefrontState = await getEcwidAssetState(page);

	expect(storefrontState.ecwidCss).toBe(true);
	expect(storefrontState.ecwidFrontendScript).toBe(true);
	expect(storefrontState.storefront).toBe(true);
	expect(storefrontState.cartWidgets).toBeGreaterThanOrEqual(1);
});
