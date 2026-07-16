import { expect, test } from '@playwright/test';

const productId = '814453695';
const productPath =
	'/shop/Suffragette-Penny-A-Unique-Handmade-Replica-&-Tiny-act-of-rebellion-p814453695';

test('@fast @mobile-fast @shop @ecwid Ecwid inline runtime remains executable on hard loads', async ({
	page,
}) => {
	const relevantPageErrors: string[] = [];

	page.on('pageerror', (error) => {
		if (
			error.message === 'jQuery is not defined' ||
			error.message === 'Invalid or unexpected token'
		) {
			relevantPageErrors.push(error.message);
		}
	});

	await page.goto('/shop/', { waitUntil: 'domcontentloaded' });

	expect(
		await page
			.locator('#jquery-core-js')
			.evaluate((script) => script.parentElement?.tagName)
	).toBe('HEAD');

	const inlineScriptSource = await page
		.locator('script:not([src])')
		.allTextContents();
	expect(inlineScriptSource.join('\n')).not.toContain('&#038;&#038;');
	expect(relevantPageErrors).toEqual([]);
});

test('@fast @mobile-fast @shop @ecwid abandoned product pointer intents clear loading feedback', async ({
	page,
}) => {
	await page.goto('/shop/', { waitUntil: 'domcontentloaded' });

	const cardBody = page
		.locator(`.grid-product__wrap[data-product-id="${productId}"]`)
		.first()
		.locator('.grid-product__shadow');
	await cardBody.waitFor({ state: 'visible', timeout: 15000 });

	await cardBody.evaluate((element) => {
		element.dispatchEvent(
			new PointerEvent('pointerdown', {
				bubbles: true,
				button: 0,
				pointerId: 1,
				pointerType: 'touch',
			})
		);
	});

	await expect(page.locator('body')).toHaveAttribute(
		'data-pns-ecwid-loading',
		productId
	);
	await expect(page.getByRole('status')).toBeVisible();

	await cardBody.evaluate((element) => {
		element.dispatchEvent(
			new PointerEvent('pointercancel', {
				bubbles: true,
				pointerId: 1,
				pointerType: 'touch',
			})
		);
	});

	await expect(page.locator('body')).not.toHaveAttribute(
		'data-pns-ecwid-loading'
	);
	await expect(page.getByRole('status')).toBeHidden();
});

test('@fast @mobile-fast @shop @ecwid Ecwid product clicks expose accessible loading feedback', async ({
	page,
}) => {
	test.setTimeout(60000);

	await page.goto('/shop/', { waitUntil: 'domcontentloaded' });
	expect(
		await page
			.locator('#pns-theme-ecwid-loading-feedback-js')
			.evaluate((script) => script.parentElement?.tagName)
	).toBe('HEAD');

	const cardWrap = page
		.locator(`.grid-product__wrap[data-product-id="${productId}"]`)
		.first();
	await cardWrap.waitFor({ state: 'attached', timeout: 15000 });

	await page.evaluate(() => {
		const editorialLink = document.createElement('a');
		editorialLink.href = '/editorial-example-p123';
		editorialLink.addEventListener('click', (event) =>
			event.preventDefault()
		);
		document.body.append(editorialLink);
		editorialLink.click();
		editorialLink.remove();
	});
	await expect(page.locator('body')).not.toHaveAttribute(
		'data-pns-ecwid-loading'
	);

	await page.evaluate(
		({ expectedProductId, sourcePath }) => {
			window.history.pushState({}, '', sourcePath);

			const dynamicContainer = document.querySelector(
				'#dynamic-ec-store-container'
			);
			let store = dynamicContainer?.querySelector('.ec-store');

			if (!store && dynamicContainer) {
				store = document.createElement('div');
				store.className = 'ec-store';
				dynamicContainer.append(store);
			}

			const previousDetail = document.createElement('div');
			previousDetail.className = 'product-details';

			const previousTitle = document.createElement('h1');
			previousTitle.className = 'product-details__product-title';
			previousTitle.textContent = 'Previous product';
			previousDetail.append(previousTitle);
			store?.append(previousDetail);

			document.addEventListener(
				'click',
				(event) => {
					const target = event.target;

					if (
						target instanceof Element &&
						target.closest(
							`.grid-product__wrap[data-product-id="${expectedProductId}"]`
						)
					) {
						event.preventDefault();
						event.stopImmediatePropagation();
					}
				},
				true
			);
		},
		{ expectedProductId: productId, sourcePath: '/store/' }
	);

	await cardWrap.locator('.grid-product__shadow').click();

	const card = page.locator(`.grid-product--id-${productId}`);
	await expect(page.locator('body')).toHaveAttribute(
		'data-pns-ecwid-loading',
		productId
	);
	await expect(page.locator('body')).toHaveAttribute(
		'data-pns-ecwid-loading-stage',
		'navigation'
	);
	await expect(card).toHaveAttribute('aria-busy', 'true');
	await expect(card).toHaveCSS('cursor', 'progress');

	const status = page.getByRole('status');
	await expect(status).toBeVisible();
	await expect(status).toContainText('Loading');
	await expect(status).toContainText('Suffragette Penny');

	await page.evaluate((expectedPath) => {
		window.history.pushState({}, '', expectedPath);
		document
			.querySelector('#dynamic-ec-store-container .ec-store')
			?.append(document.createElement('span'));
	}, productPath);
	await page.waitForTimeout(50);
	await expect(page.locator('body')).toHaveAttribute(
		'data-pns-ecwid-loading',
		productId
	);
	await expect(page.locator('body')).toHaveAttribute(
		'data-pns-ecwid-loading-stage',
		'destination'
	);

	await page.evaluate(
		({ expectedProductId }) => {
			const store = document.querySelector(
				'#dynamic-ec-store-container .ec-store'
			);
			store?.querySelector('.product-details')?.remove();

			const detail = document.createElement('div');
			detail.className = 'product-details';
			detail.setAttribute('data-test-product-id', expectedProductId);

			const title = document.createElement('h1');
			title.className = 'product-details__product-title';
			title.textContent = 'Loaded product';
			detail.append(title);
			store?.append(detail);
		},
		{ expectedProductId: productId }
	);

	await expect(page.locator('body')).not.toHaveAttribute(
		'data-pns-ecwid-loading',
		productId
	);
	await expect(card).not.toHaveAttribute('aria-busy', 'true');
	await expect(status).toBeHidden();
});

test('@fast @mobile-fast @shop @ecwid Ecwid product routes stay visibly pending while the dynamic wrapper is unavailable', async ({
	page,
}) => {
	test.setTimeout(60000);

	await page.route(/^https:\/\/app\.ecwid\.com\/script\.js\?/, (route) =>
		route.abort('failed')
	);
	await page.goto(productPath, { waitUntil: 'domcontentloaded' });

	await expect(
		page.locator(
			'#dynamic-ec-store-container .product-details__product-title'
		)
	).toHaveCount(0);

	const body = page.locator('body');
	await expect(body).toHaveAttribute('data-pns-ecwid-loading', productId);
	await expect(body).toHaveAttribute(
		'data-pns-ecwid-loading-stage',
		'destination'
	);
	await expect(body).toHaveCSS('cursor', 'progress');

	const status = page.getByRole('status');
	await expect(status).toBeVisible();
	await expect(status).toContainText('Loading product');
});
