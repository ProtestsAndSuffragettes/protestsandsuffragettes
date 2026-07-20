import { expect, test, type Locator } from '@playwright/test';

const joinUsSelector =
	'.pns-primary-navigation a.wp-block-navigation-item__content:is([href$="/membership"], [href$="/membership/"])';
const brandRed = 'rgb(212, 0, 15)';
const neutral0 = 'rgb(255, 255, 255)';

type BurstMetrics = {
	backgroundImage: string;
	blockSize: number;
	content: string;
	opacity: number;
	rotation: number;
	scale: number;
	transitionDuration: string;
};

async function readRotation(element: Locator): Promise<number> {
	return element.evaluate((node) => {
		const matrix = new DOMMatrixReadOnly(getComputedStyle(node).transform);

		return (Math.atan2(matrix.b, matrix.a) * 180) / Math.PI;
	});
}

async function readBurstMetrics(label: Locator): Promise<BurstMetrics> {
	return label.evaluate((node) => {
		const computed = getComputedStyle(node, '::before');
		const matrix = new DOMMatrixReadOnly(computed.transform);

		return {
			backgroundImage: computed.backgroundImage,
			blockSize: Number.parseFloat(computed.blockSize),
			content: computed.content,
			opacity: Number.parseFloat(computed.opacity),
			rotation: (Math.atan2(matrix.b, matrix.a) * 180) / Math.PI,
			scale: Math.hypot(matrix.a, matrix.b),
			transitionDuration: computed.transitionDuration,
		};
	});
}

async function expectBurstState(
	label: Locator,
	expected: { opacity: number; rotation: number; scale: number }
): Promise<void> {
	await expect
		.poll(async () => (await readBurstMetrics(label)).opacity)
		.toBeCloseTo(expected.opacity, 2);
	await expect
		.poll(async () => (await readBurstMetrics(label)).rotation)
		.toBeCloseTo(expected.rotation, 1);
	await expect
		.poll(async () => (await readBurstMetrics(label)).scale)
		.toBeCloseTo(expected.scale, 2);
}

async function expectJoinUsTreatment(link: Locator): Promise<void> {
	const label = link.locator('.wp-block-navigation-item__label');

	await expect(link).toHaveAttribute('href', /\/membership\/?$/);
	await expect(link).toHaveCSS('color', brandRed);
	await expect(link).toHaveCSS('text-decoration-line', 'none');
	await expect.poll(() => readRotation(label)).toBeCloseTo(-2, 1);
	await expect(label).toHaveCSS('transition-duration', '0s');
}

async function expectNoStandardHover(link: Locator): Promise<void> {
	await link.hover();
	await expect(link).toHaveCSS('color', brandRed);
	await expect(link).toHaveCSS('background-color', 'rgba(0, 0, 0, 0)');
	await expect
		.poll(() =>
			link.evaluate(
				(element) => getComputedStyle(element, '::after').content
			)
		)
		.toBe('none');
}

async function expectStandardDrawerInteraction(link: Locator): Promise<void> {
	await expect(link).toHaveCSS('color', neutral0);
	await expect(link).toHaveCSS('background-color', brandRed);
	await expect
		.poll(() =>
			link.evaluate(
				(element) => getComputedStyle(element, '::after').content
			)
		)
		.toBe('none');
}

test('@navigation primary Join Us link has its durable burst treatment', async ({
	page,
}) => {
	await page.emulateMedia({ reducedMotion: 'no-preference' });
	await page.setViewportSize({ width: 1440, height: 1000 });
	await page.goto('/');

	const links = page.locator(joinUsSelector);
	const link = links.first();
	const label = link.locator('.wp-block-navigation-item__label');

	await expect(links).toHaveCount(1);
	await expect(link).toBeVisible();
	await expect(link).toHaveText(/join us/i);
	await expectJoinUsTreatment(link);
	expect(
		await label.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				color: computed.getPropertyValue('-webkit-text-stroke-color'),
				width: computed.getPropertyValue('-webkit-text-stroke-width'),
			};
		})
	).toEqual({ color: 'rgb(255, 255, 255)', width: '3px' });
	const restingBurst = await readBurstMetrics(label);
	expect(restingBurst.content).toBe('""');
	expect(restingBurst.backgroundImage).toContain('join-us-starburst.svg');
	expect(restingBurst.blockSize).toBeCloseTo(68, 0);
	expect(restingBurst.transitionDuration).toBe('0.12s, 0.18s');
	await expectBurstState(label, {
		opacity: 0,
		rotation: -10,
		scale: 0.18,
	});
	await expectNoStandardHover(link);
	await expectBurstState(label, { opacity: 1, rotation: 0, scale: 1 });
	expect((await readBurstMetrics(label)).transitionDuration).toBe(
		'0.14s, 0.34s'
	);

	await page.mouse.move(0, 0);
	await expectBurstState(label, {
		opacity: 0,
		rotation: -10,
		scale: 0.18,
	});
	await link.focus();
	await page.keyboard.press('Tab');
	await page.keyboard.press('Shift+Tab');
	await expect(link).toBeFocused();
	await expectJoinUsTreatment(link);
	await expectBurstState(label, { opacity: 1, rotation: 0, scale: 1 });
	await expect
		.poll(() =>
			link.evaluate(
				(element) => getComputedStyle(element, '::after').content
			)
		)
		.toBe('none');
});

test('@navigation reduced motion shows a static Join Us burst', async ({
	page,
}) => {
	await page.emulateMedia({ reducedMotion: 'reduce' });
	await page.setViewportSize({ width: 1440, height: 1000 });
	await page.goto('/');

	const link = page.locator(joinUsSelector).first();
	const label = link.locator('.wp-block-navigation-item__label');

	await link.hover();
	await expectBurstState(label, { opacity: 1, rotation: 0, scale: 1 });
	expect((await readBurstMetrics(label)).transitionDuration).toBe('0s');
});

test('@mobile-navigation mobile Join Us uses the standard drawer interaction', async ({
	page,
}) => {
	await page.setViewportSize({ width: 390, height: 900 });
	await page.goto('/');

	const openButton = page.locator(
		'.pns-primary-navigation > .wp-block-navigation__responsive-container-open'
	);
	await expect(openButton).toBeVisible();
	await openButton.click();

	const link = page.locator(joinUsSelector).first();
	const label = link.locator('.wp-block-navigation-item__label');

	await expect(link).toBeVisible();
	await expectJoinUsTreatment(link);
	expect(
		await label.evaluate((element) =>
			getComputedStyle(element).getPropertyValue(
				'-webkit-text-stroke-width'
			)
		)
	).toBe('0px');
	expect((await readBurstMetrics(label)).content).toBe('none');
	await link.hover();
	await expectStandardDrawerInteraction(link);
	await page.mouse.move(0, 0);
	await link.focus();
	await expectStandardDrawerInteraction(link);
	expect((await readBurstMetrics(label)).content).toBe('none');
});
