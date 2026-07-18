import { expect, test, type Locator, type Page } from '@playwright/test';

type TransformMetrics = {
	rotation: number;
	scale: number;
};

type SplatMetrics = {
	backgroundColor: string;
	blockSize: number;
	content: string;
	determinant: number;
	insetBlockStart: number;
	insetInlineStart: number;
	maskImage: string;
	opacity: number;
	rotation: number;
	scale: number;
	transitionDelay: string;
	transitionDuration: string;
	zIndex: string;
};

async function readTransformMetrics(image: Locator): Promise<TransformMetrics> {
	return image.evaluate((element) => {
		const transform = getComputedStyle(element).transform;
		const matrix = new DOMMatrixReadOnly(transform);

		return {
			rotation: (Math.atan2(matrix.b, matrix.a) * 180) / Math.PI,
			scale: Math.hypot(matrix.a, matrix.b),
		};
	});
}

async function readSplatMetrics(
	link: Locator,
	pseudo: '::before' | '::after'
): Promise<SplatMetrics> {
	return link.evaluate((element, selectedPseudo) => {
		const computed = getComputedStyle(element, selectedPseudo);
		const matrix = new DOMMatrixReadOnly(computed.transform);

		return {
			backgroundColor: computed.backgroundColor,
			blockSize: Number.parseFloat(computed.blockSize),
			content: computed.content,
			determinant: matrix.a * matrix.d - matrix.b * matrix.c,
			insetBlockStart: Number.parseFloat(computed.insetBlockStart),
			insetInlineStart: Number.parseFloat(computed.insetInlineStart),
			maskImage:
				computed.maskImage ||
				computed.getPropertyValue('-webkit-mask-image'),
			opacity: Number.parseFloat(computed.opacity),
			rotation:
				((matrix.a * matrix.d - matrix.b * matrix.c < 0
					? Math.atan2(-matrix.b, -matrix.a)
					: Math.atan2(matrix.b, matrix.a)) *
					180) /
				Math.PI,
			scale: Math.hypot(matrix.a, matrix.b),
			transitionDelay: computed.transitionDelay,
			transitionDuration: computed.transitionDuration,
			zIndex: computed.zIndex,
		};
	}, pseudo);
}

async function expectSplatState(
	link: Locator,
	pseudo: '::before' | '::after',
	expected: { opacity: number; scale: number }
): Promise<void> {
	await expect
		.poll(async () => (await readSplatMetrics(link, pseudo)).opacity)
		.toBeCloseTo(expected.opacity, 2);
	await expect
		.poll(async () => (await readSplatMetrics(link, pseudo)).scale)
		.toBeCloseTo(expected.scale, 2);
}

async function expectLogoFlourish(page: Page, selector: string): Promise<void> {
	const link = page.locator(`${selector} a`).first();
	const image = link.locator('img');

	await expect(link).toBeVisible();
	await link.hover();
	await expect
		.poll(async () => (await readTransformMetrics(image)).rotation)
		.toBeCloseTo(2.5, 1);
	await expect
		.poll(async () => (await readTransformMetrics(image)).scale)
		.toBeCloseTo(1.035, 2);

	await page.mouse.move(0, 0);
	await expect
		.poll(() =>
			image.evaluate((element) => getComputedStyle(element).transform)
		)
		.toBe('none');

	await link.focus();
	await page.keyboard.press('Tab');
	await page.keyboard.press('Shift+Tab');
	await expect(link).toBeFocused();
	await expect
		.poll(async () => (await readTransformMetrics(image)).rotation)
		.toBeCloseTo(2.5, 1);
	await expect
		.poll(async () => (await readTransformMetrics(image)).scale)
		.toBeCloseTo(1.035, 2);

	await link.blur();
}

test('@smoke @mobile-smoke header and footer logos share the roll interaction', async ({
	page,
}) => {
	await page.emulateMedia({ reducedMotion: 'no-preference' });
	await page.goto('/');

	await expectLogoFlourish(page, '.pands-logo');
	await expectLogoFlourish(page, '.footer-logo');
});

test('@smoke @mobile-smoke header logo reveals three irregular splats', async ({
	page,
}) => {
	await page.emulateMedia({ reducedMotion: 'no-preference' });
	await page.goto('/');

	const header = page.locator('.pands-logo').first();
	const link = page.locator('.pands-logo .custom-logo-link').first();
	const logoSize = await link
		.locator('.custom-logo')
		.evaluate((element) => element.getBoundingClientRect().width);
	const yellowRest = await readSplatMetrics(link, '::before');
	const purpleRest = await readSplatMetrics(link, '::after');
	const mintRest = await readSplatMetrics(header, '::after');

	expect(yellowRest).toMatchObject({
		backgroundColor: 'rgb(250, 208, 39)',
		content: '""',
		transitionDuration: '0.11s, 0.18s',
		zIndex: '0',
	});
	expect(yellowRest.blockSize).toBeCloseTo((logoSize + 20) * 1.1, 0);
	expect(yellowRest.maskImage).toContain('join-us-starburst.svg');
	expect(yellowRest.determinant).toBeGreaterThan(0);
	expect(purpleRest).toMatchObject({
		backgroundColor: 'rgb(61, 32, 126)',
		content: '""',
		transitionDuration: '0.12s, 0.17s',
		zIndex: '0',
	});
	expect(purpleRest.blockSize).toBeCloseTo((logoSize + 8) * 1.1, 0);
	expect(yellowRest.blockSize).toBeGreaterThan(purpleRest.blockSize);
	const axisAngle =
		(Math.atan2(
			yellowRest.insetBlockStart - purpleRest.insetBlockStart,
			yellowRest.insetInlineStart - purpleRest.insetInlineStart
		) *
			180) /
		Math.PI;
	expect(Math.abs(axisAngle)).toBeGreaterThan(2);
	expect(Math.abs(axisAngle)).toBeLessThan(10);
	expect(purpleRest.maskImage).toContain('join-us-starburst.svg');
	expect(purpleRest.determinant).toBeLessThan(0);
	expect(mintRest).toMatchObject({
		backgroundColor: 'rgb(123, 220, 181)',
		content: '""',
		transitionDuration: '0.12s, 0.18s',
		zIndex: '1',
	});
	expect(
		await link
			.locator('.custom-logo')
			.evaluate((element) => getComputedStyle(element).zIndex)
	).toBe('2');
	expect(mintRest.blockSize).toBeCloseTo(logoSize + 8, 0);
	expect(mintRest.insetBlockStart).toBeGreaterThan(logoSize * 0.65);
	expect(mintRest.insetInlineStart).toBeGreaterThan(logoSize * 0.65);
	expect(mintRest.maskImage).toContain('join-us-starburst.svg');
	expect(mintRest.determinant).toBeLessThan(0);
	await expectSplatState(link, '::before', { opacity: 0, scale: 0.18 });
	await expectSplatState(link, '::after', { opacity: 0, scale: 0.15 });
	await expectSplatState(header, '::after', { opacity: 0, scale: 0.12 });

	await link.hover();
	await expectSplatState(link, '::before', { opacity: 1, scale: 1 });
	await expectSplatState(link, '::after', { opacity: 1, scale: 1 });
	await expectSplatState(header, '::after', { opacity: 1, scale: 1 });
	expect((await readSplatMetrics(link, '::before')).rotation).toBeCloseTo(
		6,
		1
	);
	expect((await readSplatMetrics(link, '::after')).rotation).toBeCloseTo(
		-5,
		1
	);
	expect((await readSplatMetrics(link, '::after')).transitionDelay).toBe(
		'0.035s'
	);
	expect((await readSplatMetrics(header, '::after')).transitionDelay).toBe(
		'0.055s'
	);
	expect((await readSplatMetrics(link, '::after')).determinant).toBeLessThan(
		0
	);

	await page.mouse.move(0, 0);
	await expectSplatState(link, '::before', { opacity: 0, scale: 0.18 });
	await expectSplatState(link, '::after', { opacity: 0, scale: 0.15 });
	await expectSplatState(header, '::after', { opacity: 0, scale: 0.12 });

	await link.focus();
	await page.keyboard.press('Tab');
	await page.keyboard.press('Shift+Tab');
	await expect(link).toBeFocused();
	await expectSplatState(link, '::before', { opacity: 1, scale: 1 });
	await expectSplatState(link, '::after', { opacity: 1, scale: 1 });
	await expectSplatState(header, '::after', { opacity: 1, scale: 1 });
});

test('@smoke @mobile-smoke logo motion respects reduced-motion preferences', async ({
	page,
}) => {
	await page.emulateMedia({ reducedMotion: 'reduce' });
	await page.goto('/');

	for (const selector of ['.pands-logo', '.footer-logo']) {
		const link = page.locator(`${selector} a`).first();
		const image = link.locator('img');

		await expect(link).toBeVisible();
		await link.hover();
		await expect(image).toHaveCSS('transform', 'none');
		await link.focus();
		await expect(image).toHaveCSS('transform', 'none');
	}

	const headerLink = page.locator('.pands-logo .custom-logo-link').first();
	const header = page.locator('.pands-logo').first();
	await headerLink.hover();
	await expectSplatState(headerLink, '::before', { opacity: 1, scale: 1 });
	await expectSplatState(headerLink, '::after', { opacity: 1, scale: 1 });
	await expectSplatState(header, '::after', { opacity: 1, scale: 1 });
	expect(
		(await readSplatMetrics(headerLink, '::before')).transitionDuration
	).toBe('0s');
	expect(
		(await readSplatMetrics(headerLink, '::after')).transitionDuration
	).toBe('0s');
	expect((await readSplatMetrics(header, '::after')).transitionDuration).toBe(
		'0s'
	);

	const footerLink = page.locator('.footer-logo .custom-logo-link').first();
	expect((await readSplatMetrics(footerLink, '::before')).content).toBe(
		'none'
	);
	expect((await readSplatMetrics(footerLink, '::after')).content).toBe(
		'none'
	);
	const footer = page.locator('.footer-logo').first();
	expect((await readSplatMetrics(footer, '::after')).content).toBe('none');
});
