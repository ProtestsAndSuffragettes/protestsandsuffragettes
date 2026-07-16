import { readFileSync } from 'node:fs';
import { join } from 'node:path';

import { expect, test, type Locator, type Page } from '@playwright/test';

function taggedTitle(title: string, ...tags: string[]): string {
	return `${tags.map((tag) => `@${tag}`).join(' ')} ${title}`;
}

function annotateAcceptedStateInventory(label: string, value: unknown): void {
	const serialized = JSON.stringify(value);
	const description =
		serialized.length > 900 ? `${serialized.slice(0, 897)}...` : serialized;

	test.info().annotations.push({
		type: 'accepted-state-inventory',
		description: `${label}: ${description}`,
	});
}

const themeJson = JSON.parse(
	readFileSync(join(process.cwd(), 'theme.json'), 'utf8')
) as {
	settings: {
		color: {
			defaultDuotone: boolean;
			defaultGradients: boolean;
			duotone: Array<{ slug: string; colors: string[] }>;
			gradients: Array<{ slug: string; gradient: string }>;
		};
		layout: {
			wideSize: string;
		};
	};
};
const themeWideSize = Number.parseFloat(themeJson.settings.layout.wideSize);

const routes = [
	{
		name: 'home',
		path: '/',
		tags: [
			'smoke',
			'mobile-smoke',
			'fast',
			'mobile-fast',
			'mobile-full',
			'snapshot',
		],
	},
	{
		name: 'mary-barbour',
		path: '/herstories/mary-barbour/',
		maxDiffPixelRatio: 0.08,
		snapshotSettleMs: 3000,
		tags: [
			'smoke',
			'mobile-smoke',
			'fast',
			'mobile-fast',
			'mobile-full',
			'snapshot',
		],
	},
	{
		name: 'membership',
		path: '/membership/',
		tags: ['fast', 'mobile-fast', 'mobile-full', 'snapshot'],
	},
	{
		name: 'edu-giveaway',
		path: '/edu-giveaway/',
		snapshotSettleMs: 5000,
		tags: ['audit', 'snapshot', 'emailoctopus', 'vendor', 'slow'],
	},
	{
		name: 'shop',
		path: '/shop/',
		waitForEcwidGrid: true,
		tags: ['audit', 'snapshot', 'shop', 'ecwid', 'vendor', 'slow'],
	},
	{
		name: 'pattern-qa',
		path: '/pns-pattern-qa/',
		snapshotSettleMs: 3000,
		tags: ['audit', 'snapshot', 'pattern', 'slow'],
	},
];

const contentRhythmRoutes = [
	'/about/',
	'/shenanigans/',
	'/about/gender-inclusion-policy/',
	'/news/work-with-us-argyll/',
	'/news/work-with-us-past-deadlines/',
];

const genericTemplateLightSurfaceRoutes = [
	{
		name: '404',
		path: '/phase-2-light-surface-missing-route/',
		selector: '.pns-template-404',
		textSelector: 'p',
	},
	{
		name: 'search results',
		path: '/search/suffrage/',
		selector: '.pns-template-search',
		textSelector:
			':is(p, .wp-block-post-excerpt, .wp-block-post-excerpt__excerpt)',
	},
	{
		name: 'search no results',
		path: '/search/unlikely-search-token-xyz/',
		selector: '.pns-template-search',
		textSelector: ':is(p, .wp-block-query-no-results)',
	},
	{
		name: 'archive',
		path: '/category/opportunities/',
		selector: '.pns-template-archive',
		textSelector:
			':is(p, .wp-block-post-excerpt, .wp-block-post-excerpt__excerpt)',
	},
	{
		name: 'single',
		path: '/news/work-with-us-past-deadlines/',
		selector: '.pns-template-single',
		textSelector: ':is(p, li)',
	},
];

const lightSurfaceExcludedRoutes = [
	{
		name: 'front page',
		path: '/',
		expectedMainClass: 'pns-template-page',
	},
	{
		name: 'about page',
		path: '/about/',
		expectedMainClass: 'pns-template-page',
	},
	{
		name: 'artworks page',
		path: '/artworks/',
		expectedMainClass: 'pns-template-page',
	},
	{
		name: 'herstories page',
		path: '/herstories/',
		expectedMainClass: 'pns-template-herstories-archive',
	},
	{
		name: 'herstory child page',
		path: '/herstories/mary-barbour/',
		expectedMainClass: 'pns-template-page',
	},
];

const shopLightSurfaceRoutes = [
	{
		name: 'shop page',
		path: '/shop/',
	},
	{
		name: 'shop cart page',
		path: '/shop/cart',
	},
];

const savedBackgroundSectionRoutes = [
	'/herstories/',
	'/artworks/',
	'/shenanigans/',
	'/educational-resources/',
];

const redLineQuoteRoutes = [
	'/',
	'/herstories/mary-barbour/',
	'/artworks/',
	'/herstories/',
	'/educational-resources/',
	'/about/',
	'/shenanigans/',
	'/news/glasgow-herstory-workshops/',
	'/news/workshop-unleashing-the-suffragette-spirit/',
	'/edu-giveaway/',
];

const fullWidthNewsPostContracts = [
	{
		route: '/news/glasgow-herstory-workshops/',
		title: 'Glasgow Herstory Workshops',
		primaryStrapline: 'Highlighting women who campaigned',
		secondaryStrapline: null,
	},
	{
		route: '/news/workshop-unleashing-the-suffragette-spirit/',
		title: 'Workshop – Unleashing the Suffragette Spirit',
		primaryStrapline: 'We’re hosting a workshop on Women’s Empowerment',
		secondaryStrapline: 'Learn about inspiring women activists',
	},
];

const patternIdentityRoutes = [
	{
		path: '/privacy-policy/',
		selector: '.pns-basic-centred-content',
		minCount: 1,
	},
	{
		path: '/about/gender-inclusion-policy/',
		selector: '.pns-basic-centred-content',
		minCount: 1,
	},
	{
		path: '/news/work-with-us-argyll/',
		selector: '.pns-basic-centred-content',
		minCount: 0,
	},
	{ path: '/', selector: '.pns-split-section', minCount: 3 },
	{ path: '/about/', selector: '.pns-split-section', minCount: 4 },
	{ path: '/artworks/', selector: '.pns-split-section', minCount: 4 },
	{ path: '/herstories/', selector: '.pns-split-section', minCount: 3 },
	{
		path: '/educational-resources/',
		selector: '.pns-split-section',
		minCount: 4,
	},
	{ path: '/shenanigans/', selector: '.pns-split-section', minCount: 4 },
	{
		path: '/herstories/mary-barbour/',
		selector: '.pns-text-only-section',
		minCount: 1,
	},
	{
		path: '/herstories/mary-barbour/',
		selector: '.pns-split-section',
		minCount: 1,
	},
	{
		path: '/herstories/mary-barbour/',
		selector: '.pns-suffragette-facts',
		minCount: 1,
	},
	{
		path: '/herstories/mary-barbour/',
		selector: '.pns-image-strip',
		minCount: 1,
	},
];

async function waitForStableAssets(page: Page) {
	await page.evaluate(async () => {
		for (const image of Array.from(document.images)) {
			image.loading = 'eager';
		}

		const refreshAos = () => {
			const aos = (
				window as typeof window & {
					AOS?: {
						refresh?: () => void;
						refreshHard?: () => void;
					};
				}
			).AOS;

			aos?.refreshHard?.();
			aos?.refresh?.();
		};

		await new Promise<void>((resolve) => {
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
			await new Promise<void>((resolve) => {
				setTimeout(resolve, 120);
			});
		}

		document.querySelectorAll('[data-aos]').forEach((element) => {
			element.classList.add('aos-init', 'aos-animate');
		});
		refreshAos();
		window.scrollTo(0, 0);
		await new Promise<void>((resolve) => {
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
		const timeout = new Promise<void>((resolve) => {
			setTimeout(resolve, 2000);
		});

		const fontsReady =
			'fonts' in document
				? document.fonts.ready
						.then(() => undefined)
						.catch(() => undefined)
				: Promise.resolve();

		const imagesReady = Promise.all(
			Array.from(document.images)
				.filter((image) => {
					const rect = image.getBoundingClientRect();

					return (
						image.loading !== 'lazy' ||
						rect.top < window.innerHeight * 2
					);
				})
				.map((image) => {
					if (image.complete) {
						return Promise.resolve();
					}

					return new Promise<void>((resolve) => {
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

async function waitForContractReady(page: Page) {
	await page.evaluate(async () => {
		const timeout = new Promise<void>((resolve) => {
			setTimeout(resolve, 500);
		});
		const fontsReady =
			'fonts' in document
				? document.fonts.ready
						.then(() => undefined)
						.catch(() => undefined)
				: Promise.resolve();
		const templateRevealReady = Promise.all(
			(
				document.querySelector('main.pns-template')?.getAnimations() ??
				[]
			).map((animation) =>
				animation.finished.then(() => undefined).catch(() => undefined)
			)
		).then(() => undefined);

		await Promise.race([
			Promise.all([fontsReady, templateRevealReady]),
			timeout,
		]);
	});
}

async function waitForStablePageHeight(page: Page) {
	let lastHeight = -1;
	let stableReads = 0;

	for (let attempt = 0; attempt < 24; attempt += 1) {
		const height = await page.evaluate(
			() => document.documentElement.scrollHeight
		);

		if (height === lastHeight) {
			stableReads += 1;
		} else {
			lastHeight = height;
			stableReads = 0;
		}

		if (stableReads >= 3) {
			return;
		}

		await page.waitForTimeout(250);
	}
}

async function waitForEcwidGrid(page: Page) {
	await page
		.locator('.grid-product__title-inner')
		.nth(8)
		.waitFor({ state: 'visible', timeout: 15000 });
	await page.waitForFunction(() => {
		const titles = Array.from(
			document.querySelectorAll('.grid-product__title-inner')
		);
		const prices = Array.from(
			document.querySelectorAll('.grid-product__price')
		);

		return (
			titles.length >= 9 &&
			prices.length >= 9 &&
			titles.every((title) => title.textContent?.trim())
		);
	});
	await waitForStableAssets(page);
	await waitForStablePageHeight(page);
}

function visualMasks(page: Page): Locator[] {
	return [
		page.locator('#wpadminbar'),
		page.locator('.cli-bar-container'),
		page.locator('.cky-consent-container'),
		page.locator('.cookie-law-info-bar'),
		page.locator('.wp-block-jetpack-slideshow'),
		page.locator('.wp-block-jetpack-slideshow_pagination'),
	];
}

for (const route of routes) {
	test(
		taggedTitle(`visual snapshot: ${route.name}`, ...route.tags),
		async ({ page }, testInfo) => {
			if ('snapshotSettleMs' in route) {
				testInfo.setTimeout(60000);
			}

			await page.goto(route.path);
			await page.waitForLoadState('domcontentloaded');
			await waitForStableAssets(page);

			if ('waitForEcwidGrid' in route) {
				await waitForEcwidGrid(page);
			}

			if ('snapshotSettleMs' in route) {
				await page.waitForTimeout(route.snapshotSettleMs);
			}

			await expect(page).toHaveScreenshot(`${route.name}.png`, {
				animations: 'disabled',
				fullPage: true,
				mask: visualMasks(page),
				maxDiffPixelRatio:
					'maxDiffPixelRatio' in route
						? route.maxDiffPixelRatio
						: 0.02,
				timeout: 30000,
			});
		}
	);
}

test(
	taggedTitle(
		'membership tier cards retain their content, CTA, and responsive grid',
		'fast',
		'mobile-fast',
		'mobile-full',
		'layout'
	),
	async ({ page }) => {
		for (const [width, expectedColumns] of [
			[1450, 4],
			[1449, 2],
			[1024, 2],
			[800, 2],
			[390, 1],
		] as const) {
			await page.setViewportSize({ width, height: 900 });
			await page.goto('/membership/', { waitUntil: 'domcontentloaded' });
			await waitForStableAssets(page);

			const pageContent = page.locator(
				'main.pns-template-page-light-surface-wide-content'
			);
			const tiers = page.locator('.pns-membership-tiers');
			const cards = tiers.locator('.pns-membership-tier');
			const actionCards = tiers.locator('.pns-membership-tier__card');
			const benefits = tiers.locator('.pns-membership-tier__benefits');

			await expect(pageContent).toBeVisible();
			await expect(
				page.getByRole('heading', {
					level: 1,
					name: 'Join the Future of Women’s History',
				})
			).toBeVisible();
			await expect(cards).toHaveCount(4);
			await expect(actionCards).toHaveCount(4);
			await expect(benefits).toHaveCount(4);
			await expect(actionCards.locator('.wp-block-cover')).toHaveCount(4);

			for (const name of [
				'Community Champion',
				'Education Empowerer',
				'Research Revolutionary',
				'Creative Catalyst',
			]) {
				const card = cards.filter({
					has: page.getByRole('heading', {
						exact: true,
						level: 4,
						name,
					}),
				});
				const image = card.locator('img');
				const headingLinks = card.locator('h4 a');
				const cta = card.locator('.wp-block-buttons a');

				await expect(card).toHaveCount(1);
				await expect(image).toHaveAttribute('alt', '');
				await expect(headingLinks).toHaveCount(2);
				await expect(cta).toHaveCount(1);
				expect(
					await image.evaluate((element) => element.naturalWidth)
				).toBeGreaterThan(0);
				const membershipLinks = await card
					.locator('h4 a, .wp-block-buttons a')
					.evaluateAll((links) =>
						links.map((link) => ({
							href: link.getAttribute('href'),
							rel: link.getAttribute('rel'),
							target: link.getAttribute('target'),
						}))
					);

				expect(membershipLinks).toHaveLength(3);
				for (const link of membershipLinks) {
					expect(link).toMatchObject({
						href: 'https://www.patreon.com/cw/protestsandsuffragettes/membership',
						rel: expect.stringContaining('noopener'),
						target: '_blank',
					});
				}
			}

			const layout = await tiers.evaluate((element) => ({
				columns: getComputedStyle(element)
					.gridTemplateColumns.trim()
					.split(/\s+/).length,
				scrollWidth: document.documentElement.scrollWidth,
				viewportWidth: window.innerWidth,
			}));

			expect(layout.columns).toBe(expectedColumns);
			expect(layout.scrollWidth).toBeLessThanOrEqual(
				layout.viewportWidth + 1
			);

			const cardTreatment = await actionCards.evaluateAll((elements) =>
				elements.map((card) => {
					const image = card.querySelector('img');
					const cover = card.querySelector('.wp-block-cover');
					const buttonGroup = card.querySelector('.wp-block-buttons');
					const cardBox = card.getBoundingClientRect();
					const buttonBox = buttonGroup?.getBoundingClientRect();
					const coverBox = cover?.getBoundingClientRect();

					return {
						buttonInset: buttonBox
							? Math.round(cardBox.bottom - buttonBox.bottom)
							: null,
						coverHeight: coverBox
							? Math.round(coverBox.height)
							: null,
						hasFocalPoint: Boolean(
							image?.getAttribute('data-object-position')
						),
						height: Math.round(cardBox.height),
						imageObjectFit: image
							? getComputedStyle(image).objectFit
							: null,
					};
				})
			);
			const tierTreatment = await cards.evaluateAll((elements) =>
				elements.map((tier) => {
					const tierBox = tier.getBoundingClientRect();
					const cardBox = tier
						.querySelector('.pns-membership-tier__card')
						?.getBoundingClientRect();
					const benefitsBox = tier
						.querySelector('.pns-membership-tier__benefits')
						?.getBoundingClientRect();

					return {
						cardBottom: cardBox ? Math.round(cardBox.bottom) : null,
						cardHeight: cardBox ? Math.round(cardBox.height) : null,
						display: getComputedStyle(tier).display,
						benefitsTop: benefitsBox
							? Math.round(benefitsBox.top)
							: null,
						height: Math.round(tierBox.height),
						top: Math.round(tierBox.top),
					};
				})
			);

			for (const card of cardTreatment) {
				expect(card.coverHeight).toBeGreaterThan(0);
				expect(card.hasFocalPoint).toBe(true);
				expect(card.imageObjectFit).toBe('cover');
				expect(card.buttonInset).toBeGreaterThanOrEqual(0);
			}

			expect(
				Math.max(
					...cardTreatment.map((card) => card.buttonInset ?? 0)
				) -
					Math.min(
						...cardTreatment.map((card) => card.buttonInset ?? 0)
					)
			).toBeLessThanOrEqual(1);

			if (width >= 1450) {
				for (const tier of tierTreatment) {
					expect(tier.display).toBe('grid');
				}
				expect(
					Math.max(...cardTreatment.map((card) => card.height)) -
						Math.min(...cardTreatment.map((card) => card.height))
				).toBeLessThanOrEqual(1);
				expect(
					Math.max(...tierTreatment.map((tier) => tier.height)) -
						Math.min(...tierTreatment.map((tier) => tier.height))
				).toBeLessThanOrEqual(1);
				expect(
					Math.max(
						...tierTreatment.map((tier) => tier.benefitsTop ?? 0)
					) -
						Math.min(
							...tierTreatment.map(
								(tier) => tier.benefitsTop ?? 0
							)
						)
				).toBeLessThanOrEqual(1);
			}

			if (width >= 782 && width < 1450) {
				for (const [first, second] of [
					[tierTreatment[0], tierTreatment[1]],
					[tierTreatment[2], tierTreatment[3]],
				]) {
					expect(
						Math.abs(first.height - second.height)
					).toBeLessThanOrEqual(1);
					expect(
						Math.abs(
							(first.cardHeight ?? 0) - (second.cardHeight ?? 0)
						)
					).toBeLessThanOrEqual(1);
					expect(
						Math.abs(
							(first.cardBottom ?? 0) - (second.cardBottom ?? 0)
						)
					).toBeLessThanOrEqual(1);
					expect(
						Math.abs(
							(first.benefitsTop ?? 0) - (second.benefitsTop ?? 0)
						)
					).toBeLessThanOrEqual(1);
				}
			}
		}
	}
);

for (const searchHeroContract of [
	{
		name: 'search landing',
		path: '/search/',
		template: '.pns-template-page-search',
	},
	{
		name: 'search results',
		path: '/search/Work/',
		template: '.pns-template-search',
	},
]) {
	test(
		taggedTitle(
			`${searchHeroContract.name} uses the shared Enhanced Cover hero rail`,
			'fast',
			'search',
			'template'
		),
		async ({ page }) => {
			await page.goto(searchHeroContract.path);
			await page.waitForLoadState('domcontentloaded');

			const hero = page.locator(
				`${searchHeroContract.template} > .wp-block-ran-enhanced-cover.pns-page-hero:first-child`
			);
			const heroCopy = hero.locator(
				'.pns-hero__inner > .pns-copy-column.pns-hero-copy'
			);

			await expect(hero).toHaveCount(1);
			await expect(hero).toHaveClass(/\bpns-section\b/);
			await expect(hero).toHaveClass(/\bpns-layout\b/);
			await expect(hero).toHaveClass(/\bpns-site-frame-panel\b/);
			await expect(heroCopy).toHaveCount(1);
			await expect(heroCopy.locator('h1')).toContainText(
				'Know your Herstory'
			);

			const heroRail = await hero.evaluate((element) => {
				const inner =
					element.querySelector<HTMLElement>('.pns-section-inner');
				const copy =
					element.querySelector<HTMLElement>('.pns-hero-copy');
				const title = copy?.querySelector<HTMLElement>('h1');

				if (!inner || !copy || !title) {
					return null;
				}

				return {
					copyPadding: Number.parseFloat(
						getComputedStyle(copy).paddingInlineStart
					),
					innerLeft: inner.getBoundingClientRect().left,
					titleLeft: title.getBoundingClientRect().left,
				};
			});

			expect(heroRail).not.toBeNull();
			expect(heroRail?.copyPadding ?? 0).toBeGreaterThan(0);
			expect(
				Math.abs(
					(heroRail?.titleLeft ?? 0) -
						((heroRail?.innerLeft ?? 0) +
							(heroRail?.copyPadding ?? 0))
				)
			).toBeLessThan(1);
		}
	);
}

test(
	taggedTitle(
		'native search landing page resolves',
		'fast',
		'search',
		'template'
	),
	async ({ page }) => {
		await page.goto('/search/');
		await page.waitForLoadState('domcontentloaded');

		await expect(
			page.locator('.pns-template-page-search.pns-light-surface')
		).toBeVisible();
		await expect(
			page.getByRole('heading', { name: /^Know your Herstory/ })
		).toBeVisible();
		await expect(
			page.locator(
				'.pns-template-page-search > .wp-block-ran-enhanced-cover.pns-page-hero:first-child'
			)
		).toHaveCount(1);
		await expect(page.getByPlaceholder('Search the site')).toBeVisible();
	}
);

test(
	taggedTitle(
		'native search returns editorial results',
		'fast',
		'search',
		'template'
	),
	async ({ page }) => {
		await page.setViewportSize({ width: 1100, height: 900 });
		await page.goto('/search/Work/');
		await page.waitForLoadState('domcontentloaded');

		await expect(
			page.locator('.pns-template-search.pns-light-surface')
		).toBeVisible();
		await expect(
			page.locator(
				'.pns-template-search > .wp-block-ran-enhanced-cover.pns-page-hero:first-child'
			)
		).toHaveCount(1);
		await expect(
			page.getByRole('heading', { name: /^Know your Herstory/ })
		).toBeVisible();
		await expect(page.getByPlaceholder('Search the site')).toBeVisible();
		await expect(page.locator('.wp-block-post-title a')).not.toHaveCount(0);
		await expect(page.locator('.pns-search-result')).not.toHaveCount(0);
		const firstSearchImage = page
			.locator('.pns-search-result__thumbnail img')
			.first();
		await expect(firstSearchImage).toHaveClass(/\bsize-square\b/);
		await expect(firstSearchImage).toHaveCSS('object-fit', 'cover');
		await expect(
			page.locator('.pns-search-result__terms a').first()
		).toBeVisible();
		await expect(
			page.locator('.pns-search-result__terms').first()
		).toHaveClass(/\bpns-taxonomy-pills\b/);
		await expect(
			page.locator('.pns-search-result__term-list').first()
		).toHaveClass(/\bpns-taxonomy-pills__list\b/);
		const firstSearchCategoryPill = page
			.locator('.pns-search-result__terms .taxonomy-category a')
			.first();
		await expect(firstSearchCategoryPill).toHaveCSS('min-height', '24px');
		await expect(firstSearchCategoryPill).toHaveCSS('padding-left', '8px');
		await expect(firstSearchCategoryPill).toHaveCSS('font-size', '12px');
		await expect(firstSearchCategoryPill).toHaveCSS(
			'background-color',
			'rgb(0, 107, 95)'
		);
		await expect(firstSearchCategoryPill).toHaveCSS(
			'color',
			'rgb(255, 255, 255)'
		);
		const firstSearchTitleLink = page
			.locator('.pns-search-result__body .wp-block-post-title a')
			.first();
		const firstSearchTitleMotion = await firstSearchTitleLink.evaluate(
			(element) => {
				const computed = getComputedStyle(element);

				return {
					transitionDuration: computed.transitionDuration,
					transitionProperty: computed.transitionProperty,
				};
			}
		);
		const firstSearchImageMotion = await firstSearchImage.evaluate(
			(element) => {
				const computed = getComputedStyle(element);

				return {
					transitionDuration: computed.transitionDuration,
					transitionProperty: computed.transitionProperty,
				};
			}
		);

		expect(firstSearchTitleMotion.transitionProperty).not.toContain('all');
		expect(firstSearchTitleMotion.transitionProperty).toContain(
			'text-decoration-thickness'
		);
		expect(firstSearchTitleMotion.transitionDuration).not.toBe('0s');
		expect(firstSearchImageMotion.transitionProperty).toBe('filter');
		expect(firstSearchImageMotion.transitionDuration).not.toBe('0s');

		const firstDesktopResult = page.locator('.pns-search-result').first();
		const desktopLayout = await firstDesktopResult.evaluate((result) => {
			const thumbnail = result.querySelector<HTMLElement>(
				'.pns-search-result__thumbnail'
			);
			const body = result.querySelector<HTMLElement>(
				'.pns-search-result__body'
			);
			const thumbnailRect = thumbnail?.getBoundingClientRect();
			const bodyRect = body?.getBoundingClientRect();

			return thumbnailRect && bodyRect
				? {
						bodyLeft: bodyRect.left,
						thumbnailRight: thumbnailRect.right,
					}
				: null;
		});

		expect(desktopLayout).not.toBeNull();
		expect(desktopLayout?.bodyLeft).toBeGreaterThan(
			desktopLayout?.thumbnailRight ?? 0
		);
		const firstSearchFigureBox = await page
			.locator('.pns-search-result__thumbnail')
			.first()
			.boundingBox();

		expect(firstSearchFigureBox).not.toBeNull();
		expect(
			Math.abs(
				(firstSearchFigureBox?.width ?? 0) -
					(firstSearchFigureBox?.height ?? 0)
			)
		).toBeLessThanOrEqual(1);

		await page.setViewportSize({ width: 390, height: 900 });
		await page.goto('/search/Work/');
		await page.waitForLoadState('domcontentloaded');

		const firstMobileResult = page.locator('.pns-search-result').first();
		const mobileLayout = await firstMobileResult.evaluate((result) => {
			const thumbnail = result.querySelector<HTMLElement>(
				'.pns-search-result__thumbnail'
			);
			const body = result.querySelector<HTMLElement>(
				'.pns-search-result__body'
			);
			const thumbnailRect = thumbnail?.getBoundingClientRect();
			const bodyRect = body?.getBoundingClientRect();

			return thumbnailRect && bodyRect
				? {
						bodyTop: bodyRect.top,
						thumbnailBottom: thumbnailRect.bottom,
					}
				: null;
		});

		expect(mobileLayout).not.toBeNull();
		expect(mobileLayout?.bodyTop).toBeGreaterThanOrEqual(
			(mobileLayout?.thumbnailBottom ?? 0) - 1
		);

		const searchListSeparators = await page
			.locator('.pns-search-results__list')
			.evaluate((list) => {
				const firstResult =
					list.querySelector<HTMLElement>('.pns-search-result');
				const secondResult =
					list.querySelectorAll<HTMLElement>('.pns-search-result')[1];
				const firstStyles = firstResult
					? getComputedStyle(firstResult)
					: null;
				const secondStyles = secondResult
					? getComputedStyle(secondResult)
					: null;
				const listStyles = getComputedStyle(list);

				return {
					firstBottomBorder: firstStyles?.borderBottomWidth,
					firstTopBorder: firstStyles?.borderTopWidth,
					listGap: listStyles.rowGap,
					secondTopBorder: secondStyles?.borderTopWidth,
				};
			});

		expect(searchListSeparators.listGap).toBe('0px');
		expect(searchListSeparators.firstTopBorder).toBe('1px');
		expect(searchListSeparators.firstBottomBorder).toBe('0px');
		expect(searchListSeparators.secondTopBorder).toBe('1px');

		await expect(
			page.locator('.pns-template-search .wp-block-query-pagination')
		).toBeVisible();

		const pagination = page.locator(
			'.pns-template-search .wp-block-query-pagination'
		);
		const paginationSpacing = await pagination.evaluate((element) => {
			const main = element.closest<HTMLElement>(
				'main.pns-template-search'
			);
			const computed = getComputedStyle(element);
			const mainComputed = main ? getComputedStyle(main) : null;

			return {
				mainPaddingBottom: mainComputed?.paddingBottom,
				paddingBottom: computed.paddingBottom,
				paddingTop: computed.paddingTop,
			};
		});

		expect(paginationSpacing.mainPaddingBottom).toBe('0px');
		expect(parseFloat(paginationSpacing.paddingTop)).toBeGreaterThanOrEqual(
			48
		);
		expect(
			parseFloat(paginationSpacing.paddingBottom)
		).toBeGreaterThanOrEqual(48);
		await expect(
			pagination.locator('a.page-numbers', { hasText: '2' })
		).toHaveAttribute('href', /\/search\/Work\/page\/2\/$/);
		await expect(
			pagination.getByRole('link', { name: 'Next' })
		).toHaveAttribute('href', /\/search\/Work\/page\/2\/$/);

		const pageNumber = pagination.locator('a.page-numbers', {
			hasText: '2',
		});
		await pageNumber.scrollIntoViewIfNeeded();

		const pageNumberRest = await pageNumber.evaluate((element) => {
			const computed = getComputedStyle(element);
			const rect = element.getBoundingClientRect();

			return {
				rect: {
					height: rect.height,
					width: rect.width,
					x: rect.x,
					y: rect.y,
				},
				transitionDuration: computed.transitionDuration,
				transitionProperty: computed.transitionProperty,
			};
		});

		expect(pageNumberRest.transitionProperty).not.toContain('all');
		expect(pageNumberRest.transitionProperty).toContain('box-shadow');
		expect(pageNumberRest.transitionProperty).toContain(
			'text-decoration-thickness'
		);
		expect(pageNumberRest.transitionDuration).not.toBe('0s');

		await pageNumber.hover();

		const pageNumberHover = await pageNumber.evaluate((element) => {
			const computed = getComputedStyle(element);
			const rect = element.getBoundingClientRect();

			return {
				rect: {
					height: rect.height,
					width: rect.width,
					x: rect.x,
					y: rect.y,
				},
				textDecorationLine: computed.textDecorationLine,
			};
		});

		expect(pageNumberHover.textDecorationLine).toBe('underline');
		expect(
			Math.abs(pageNumberHover.rect.x - pageNumberRest.rect.x)
		).toBeLessThan(0.5);
		expect(
			Math.abs(pageNumberHover.rect.y - pageNumberRest.rect.y)
		).toBeLessThan(0.5);
		expect(
			Math.abs(pageNumberHover.rect.width - pageNumberRest.rect.width)
		).toBeLessThan(0.5);
		expect(
			Math.abs(pageNumberHover.rect.height - pageNumberRest.rect.height)
		).toBeLessThan(0.5);

		await page.goto('/search/Gender%20Inclusion%20Policy/');
		await page.waitForLoadState('domcontentloaded');

		const pageSearchResult = page
			.locator('.pns-search-result')
			.filter({ hasText: 'Gender Inclusion Policy Statement' });

		await expect(pageSearchResult).toBeVisible();
		await expect(
			pageSearchResult.locator('.wp-block-post-date')
		).toBeHidden();
		await expect(
			pageSearchResult.locator('.pns-post-card__meta')
		).toBeHidden();
		const authoredSearchMeta = page
			.locator('.wp-block-post.type-post .pns-search-result__meta')
			.first();
		await expect(
			authoredSearchMeta.locator('.wp-block-post-date')
		).toBeVisible();
		await expect(
			authoredSearchMeta.locator('.wp-block-post-author a')
		).toHaveAttribute('href', /\/author\/protests-and-suffragettes\/$/);
		expect(
			await authoredSearchMeta
				.locator('.wp-block-post-author')
				.evaluate(
					(element) => getComputedStyle(element, '::before').content
				)
		).toBe('none');
	}
);

test(
	taggedTitle(
		'query-string searches redirect to the canonical native route',
		'fast',
		'search',
		'template'
	),
	async ({ page }) => {
		for (const [path, target] of [
			['/?s=Work', '/search/Work/'],
			['/search/?s=Work', '/search/Work/'],
			['/?s=Work&paged=2', '/search/Work/page/2/'],
		]) {
			const response = await page.request.get(path, { maxRedirects: 0 });
			const location = response.headers().location;

			expect(response.status()).toBe(301);
			expect(location).toMatch(new RegExp(`${target}$`));
		}

		await page.goto('/search/');
		await expect(page).toHaveURL(/\/search\/$/);
		await expect(
			page.locator('main.pns-template-page-search')
		).toBeVisible();
	}
);

test(
	taggedTitle(
		'horizontal search cards keep square media through the footer',
		'fast',
		'search',
		'layout'
	),
	async ({ page }) => {
		await page.setViewportSize({ width: 1800, height: 900 });
		await page.goto('/search/Festive/');
		await page.waitForLoadState('domcontentloaded');

		const firstResult = page
			.locator('.pns-search-result')
			.filter({ hasText: 'Festive Markets' });
		await expect(firstResult).toBeVisible();

		const desktopLayout = async () =>
			firstResult.evaluate((result) => {
				const thumbnail = result.querySelector<HTMLElement>(
					'.pns-search-result__thumbnail'
				);
				const body = result.querySelector<HTMLElement>(
					'.pns-search-result__body'
				);
				const title = result.querySelector<HTMLElement>(
					'.pns-search-result__body .wp-block-post-title'
				);
				const pills = result.querySelector<HTMLElement>(
					'.pns-search-result__footer'
				);
				const thumbnailImage = thumbnail?.querySelector('img');
				const thumbnailRect = thumbnail?.getBoundingClientRect();
				const bodyRect = body?.getBoundingClientRect();
				const titleRect = title?.getBoundingClientRect();
				const pillsRect = pills?.getBoundingClientRect();
				const thumbnailStyle = thumbnailImage
					? getComputedStyle(thumbnailImage)
					: null;

				return thumbnailRect && bodyRect && titleRect && pillsRect
					? {
							bodyHeight: bodyRect.height,
							bodyLeft: bodyRect.left,
							pillsBottom: pillsRect.bottom,
							thumbnailBorderWidth:
								thumbnailStyle?.borderTopWidth,
							thumbnailBottom: thumbnailRect.bottom,
							thumbnailHeight: thumbnailRect.height,
							thumbnailRight: thumbnailRect.right,
							thumbnailWidth: thumbnailRect.width,
							titleLeft: titleRect.left,
						}
					: null;
			});

		const alignedDesktopLayout = await desktopLayout();
		expect(alignedDesktopLayout).not.toBeNull();
		expect(alignedDesktopLayout?.bodyLeft).toBeCloseTo(
			(alignedDesktopLayout?.thumbnailRight ?? 0) + 24,
			0
		);
		expect(alignedDesktopLayout?.titleLeft).toBeCloseTo(
			alignedDesktopLayout?.bodyLeft ?? 0,
			0
		);
		expect(
			Math.abs(
				(alignedDesktopLayout?.thumbnailWidth ?? 0) -
					(alignedDesktopLayout?.thumbnailHeight ?? 0)
			)
		).toBeLessThanOrEqual(1);
		expect(alignedDesktopLayout?.thumbnailBorderWidth).toBe('1px');
		expect(
			(alignedDesktopLayout?.thumbnailBottom ?? 0) -
				(alignedDesktopLayout?.pillsBottom ?? 0)
		).toBeGreaterThanOrEqual(0);

		await page.setViewportSize({ width: 390, height: 900 });
		await expect
			.poll(async () =>
				firstResult.evaluate((result) => {
					const thumbnail = result.querySelector<HTMLElement>(
						'.pns-search-result__thumbnail'
					);
					const body = result.querySelector<HTMLElement>(
						'.pns-search-result__body'
					);
					const thumbnailRect = thumbnail?.getBoundingClientRect();
					const bodyRect = body?.getBoundingClientRect();

					return (bodyRect?.top ?? 0) - (thumbnailRect?.bottom ?? 0);
				})
			)
			.toBeGreaterThanOrEqual(-1);
	}
);

test(
	taggedTitle(
		'horizontal search cards bottom-align the excerpt divider and taxonomy pills',
		'fast',
		'search',
		'layout'
	),
	async ({ page }) => {
		await page.setViewportSize({ width: 1800, height: 900 });
		await page.goto('/search/Workshop/');
		await page.waitForLoadState('domcontentloaded');

		const card = page
			.locator('.pns-search-result')
			.filter({ hasText: 'Unleashing the Suffragette Spirit' });
		await expect(card).toBeVisible();

		const layout = await card.evaluate((result) => {
			const image = result.querySelector<HTMLElement>(
				'.pns-search-result__thumbnail'
			);
			const body = result.querySelector<HTMLElement>(
				'.pns-search-result__body'
			);
			const meta = result.querySelector<HTMLElement>(
				'.pns-search-result__meta'
			);
			const excerpt = result.querySelector<HTMLElement>(
				'.wp-block-post-excerpt'
			);
			const footer = result.querySelector<HTMLElement>(
				'.pns-search-result__footer'
			);
			const imageRect = image?.getBoundingClientRect();
			const bodyRect = body?.getBoundingClientRect();
			const metaRect = meta?.getBoundingClientRect();
			const excerptRect = excerpt?.getBoundingClientRect();
			const footerRect = footer?.getBoundingClientRect();

			return imageRect &&
				bodyRect &&
				metaRect &&
				excerptRect &&
				footerRect
				? {
						bodyBottom: bodyRect.bottom,
						excerptHeight: excerptRect.height,
						excerptTop: excerptRect.top,
						footerBottom: footerRect.bottom,
						footerTop: footerRect.top,
						imageBottom: imageRect.bottom,
						metaBottom: metaRect.bottom,
						excerptBottom: excerptRect.bottom,
					}
				: null;
		});

		expect(layout).not.toBeNull();
		expect(
			Math.abs((layout?.imageBottom ?? 0) - (layout?.bodyBottom ?? 0))
		).toBeLessThanOrEqual(1);
		expect(
			(layout?.imageBottom ?? 0) - (layout?.footerBottom ?? 0)
		).toBeGreaterThanOrEqual(8);
		expect(
			(layout?.imageBottom ?? 0) - (layout?.footerBottom ?? 0)
		).toBeLessThanOrEqual(12);
		expect(
			(layout?.footerTop ?? 0) - (layout?.excerptBottom ?? 0)
		).toBeCloseTo(10, 0);
		expect(
			(layout?.excerptTop ?? 0) - (layout?.metaBottom ?? 0)
		).toBeLessThanOrEqual(12);
		expect(layout?.excerptHeight).toBeGreaterThan(100);
	}
);

test(
	taggedTitle(
		'native search renders no-results state',
		'fast',
		'search',
		'template'
	),
	async ({ page }) => {
		await page.goto('/search/unlikely-search-token-xyz/');
		await page.waitForLoadState('domcontentloaded');

		await expect(page.locator('.wp-block-post-title a')).toHaveCount(0);
		await expect(page.locator('.wp-block-query-no-results')).toContainText(
			'No results matched your search.'
		);
	}
);

test(
	taggedTitle(
		'news archive renders the current PNS archive structure',
		'fast',
		'archive',
		'navigation',
		'template'
	),
	async ({ page }) => {
		await page.goto('/news/');
		await page.waitForLoadState('domcontentloaded');

		await expect(page.locator('.pns-template-news-archive')).toBeVisible();
		await expect(page.locator('.pns-template-news-archive')).toHaveClass(
			/\bpns-light-surface\b/
		);
		await expect(
			page.locator('.pns-template-news-archive .pns-page-hero')
		).toBeVisible();
		await expect(
			page.getByRole('heading', { name: 'Featured News' })
		).toBeVisible();
		const featuredNewsBlock = page.locator(
			'.pns-template-news-archive .wp-block-pns-featured-post.pns-featured-post'
		);
		await expect(featuredNewsBlock).toHaveCount(1);
		expect(
			await page.evaluate(() => {
				const hero = document.querySelector(
					'.pns-template-news-archive .pns-page-hero'
				);
				const featured = document.querySelector(
					'.pns-template-news-archive .wp-block-pns-featured-post'
				);

				return !!(
					hero &&
					featured &&
					hero.compareDocumentPosition(featured) &
						Node.DOCUMENT_POSITION_FOLLOWING
				);
			})
		).toBe(true);
		const featuredNewsSection =
			featuredNewsBlock.locator('.pns-split-section');
		await expect(featuredNewsSection).toBeVisible();
		await expect(featuredNewsSection).toHaveClass(
			/\bis-style-pns-edge-media-left\b/
		);
		const splitSectionStyleText = await page
			.locator('style#pns-split-section-style-inline-css')
			.evaluate((style) => style.textContent ?? '');
		expect(splitSectionStyleText).toContain('.pns-split-section');
		await expect(
			featuredNewsSection.locator('.pns-split-section__copy')
		).toBeVisible();
		await expect(
			featuredNewsSection.locator(
				'.pns-split-section__media-column .wp-block-post-featured-image a'
			)
		).toBeVisible();
		await expect(
			featuredNewsSection.locator(
				'.pns-split-section__media-column .wp-block-post-featured-image'
			)
		).toHaveCSS('margin-bottom', '0px');
		await expect(
			featuredNewsSection.locator(
				'.pns-split-section__media-column .wp-block-post-featured-image img'
			)
		).toHaveClass(/\bsize-square\b/);
		await expect(
			featuredNewsBlock.locator('.wp-block-post-title a')
		).toHaveAttribute('href', /\/news\/.+\/$/);
		await expect(
			featuredNewsBlock.locator(
				'.pns-featured-post__meta .wp-block-post-date time'
			)
		).toHaveAttribute('datetime', /T/);
		await expect(
			featuredNewsBlock.locator(
				'.pns-featured-post__meta .wp-block-post-author a'
			)
		).toHaveAttribute('href', /\/author\/.+\/$/);
		await expect(
			featuredNewsBlock.locator('.wp-block-post-excerpt')
		).toBeVisible();
		await expect(
			featuredNewsBlock.locator('.pns-featured-post__meta')
		).toHaveCSS('flex-wrap', 'nowrap');
		await expect(
			featuredNewsBlock.locator('.wp-block-post-excerpt')
		).toHaveCSS('border-bottom-width', '1px');
		await expect(
			featuredNewsBlock.locator('.pns-featured-post__footer')
		).toHaveCSS('padding-top', '16px');
		await expect(
			featuredNewsBlock.locator('.pns-featured-post__footer')
		).toHaveClass(/\bpns-taxonomy-pills\b/);
		await expect(
			featuredNewsBlock.locator('.pns-featured-post__term-list').first()
		).toHaveClass(/\bpns-taxonomy-pills__list\b/);
		const featuredNewsCategoryPill = featuredNewsBlock
			.locator('.pns-featured-post__term-list.taxonomy-category a')
			.first();
		await expect(featuredNewsCategoryPill).toHaveCSS('min-height', '24px');
		await expect(featuredNewsCategoryPill).toHaveCSS('padding-left', '8px');
		await expect(featuredNewsCategoryPill).toHaveCSS('font-size', '12px');
		await expect(featuredNewsCategoryPill).toHaveCSS(
			'background-color',
			'rgb(0, 107, 95)'
		);
		await expect(featuredNewsCategoryPill).toHaveCSS(
			'color',
			'rgb(255, 255, 255)'
		);
		await expect(
			page.getByRole('heading', { name: 'More News' })
		).toBeVisible();
		await expect(
			page
				.locator(
					'.pns-news-more-section .wp-block-post-featured-image img'
				)
				.first()
		).toHaveClass(/\bsize-card\b/);
		await expect(
			page
				.locator(
					'.pns-news-more-section .wp-block-post-featured-image img'
				)
				.first()
		).toHaveCSS('object-fit', 'cover');
		await expect(
			page
				.locator('.pns-news-more-section .wp-block-post-featured-image')
				.first()
		).toHaveCSS('height', '200px');
		await expect(
			page
				.locator('.pns-news-more-section .wp-block-post-featured-image')
				.first()
		).toHaveCSS('background-color', 'rgb(0, 107, 95)');
		const firstNewsCard = page
			.locator('.pns-news-more-section .pns-archive-card.pns-post-card')
			.first();
		const firstNewsTitleLink = firstNewsCard
			.locator('.wp-block-post-title a')
			.first();
		const firstNewsImage = firstNewsCard
			.locator('.wp-block-post-featured-image img')
			.first();
		const firstNewsTitleMotion = await firstNewsTitleLink.evaluate(
			(element) => {
				const computed = getComputedStyle(element);

				return {
					transitionDuration: computed.transitionDuration,
					transitionProperty: computed.transitionProperty,
				};
			}
		);
		const firstNewsImageMotion = await firstNewsImage.evaluate(
			(element) => {
				const computed = getComputedStyle(element);

				return {
					transitionDuration: computed.transitionDuration,
					transitionProperty: computed.transitionProperty,
				};
			}
		);

		await expect(firstNewsCard).toBeVisible();
		await expect(
			firstNewsCard.locator('.pns-post-card__meta .wp-block-post-date')
		).toBeVisible();
		await expect(
			firstNewsCard.locator('.pns-post-card__footer').first()
		).toHaveClass(/\bpns-taxonomy-pills\b/);
		const firstNewsExcerpt = firstNewsCard.locator(
			'> .wp-block-post-excerpt'
		);
		await expect(firstNewsExcerpt).toHaveCSS('border-bottom-width', '1px');
		await expect(firstNewsExcerpt).toHaveCSS(
			'border-bottom-style',
			'solid'
		);
		await expect(firstNewsExcerpt).toHaveCSS('padding-bottom', '16px');
		await expect(
			firstNewsCard.locator('> .pns-post-card__footer')
		).toHaveCSS('padding-top', '16px');
		await expect(
			firstNewsCard.locator('.pns-post-card__term-list').first()
		).toHaveClass(/\bpns-taxonomy-pills__list\b/);
		const firstNewsCategoryPill = firstNewsCard
			.locator('.pns-post-card__footer .taxonomy-category a')
			.first();
		await expect(firstNewsCategoryPill).toHaveCSS('min-height', '24px');
		await expect(firstNewsCategoryPill).toHaveCSS('padding-left', '8px');
		await expect(firstNewsCategoryPill).toHaveCSS('font-size', '12px');
		await expect(firstNewsCategoryPill).toHaveCSS(
			'background-color',
			'rgb(0, 107, 95)'
		);
		await expect(firstNewsCategoryPill).toHaveCSS(
			'color',
			'rgb(255, 255, 255)'
		);
		expect(firstNewsTitleMotion.transitionProperty).not.toContain('all');
		expect(firstNewsTitleMotion.transitionProperty).toContain(
			'text-decoration-thickness'
		);
		expect(firstNewsTitleMotion.transitionDuration).not.toBe('0s');
		expect(firstNewsImageMotion.transitionProperty).toBe('filter');
		expect(firstNewsImageMotion.transitionDuration).not.toBe('0s');
	}
);

test(
	taggedTitle(
		'news archive pagination omits the landing-page featured story',
		'fast',
		'archive',
		'template'
	),
	async ({ page }) => {
		await page.setViewportSize({ width: 2048, height: 1200 });
		await page.goto('/news/?query-1-page=2');
		await page.waitForLoadState('domcontentloaded');

		await expect(page.locator('.pns-template-news-archive')).toBeVisible();
		await expect(
			page.getByRole('heading', { name: 'Featured News' })
		).toHaveCount(0);
		await expect(
			page.locator(
				'.pns-template-news-archive .wp-block-pns-featured-post.pns-featured-post'
			)
		).toHaveCount(0);
		await expect(
			page.getByRole('heading', { name: 'More News' })
		).toBeVisible();
		await expect(
			page
				.locator(
					'.pns-news-more-section .pns-archive-card.pns-post-card'
				)
				.first()
		).toBeVisible();
		await expect(
			page.locator('.pns-news-more-section .page-numbers')
		).toHaveCount(2);
		await expect(
			page.locator('.pns-news-more-section .page-numbers', {
				hasText: '3',
			})
		).toHaveCount(0);

		const paginationSurface = await page
			.locator('.pns-news-more-section .pns-query-pagination')
			.evaluate((element) => {
				const surface = getComputedStyle(element, '::before');
				const frame = element.closest<HTMLElement>(
					'.pns-site-frame-panel'
				);

				return {
					frameWidth: frame?.getBoundingClientRect().width,
					surfaceWidth: Number.parseFloat(surface.inlineSize),
				};
			});

		expect(paginationSurface.frameWidth).toBeTruthy();
		expect(paginationSurface.surfaceWidth).toBeCloseTo(
			paginationSurface.frameWidth ?? 0,
			0
		);
	}
);

test(
	taggedTitle(
		'archive and search pagination blend into their light page surface',
		'fast',
		'archive',
		'search',
		'template'
	),
	async ({ page }) => {
		for (const route of [
			{ path: '/category/news/', template: '.pns-template-archive' },
			{ path: '/search/Work/', template: '.pns-template-search' },
		]) {
			await page.goto(route.path);
			await page.waitForLoadState('domcontentloaded');

			const colors = await page
				.locator(`${route.template} .pns-query-pagination`)
				.evaluate((element) => {
					const page = element.closest<HTMLElement>('main');

					return {
						page: page
							? getComputedStyle(page).backgroundColor
							: null,
						pagination: getComputedStyle(element, '::before')
							.backgroundColor,
					};
				});

			expect(colors.page).toBeTruthy();
			expect(colors.pagination).toBe(colors.page);
		}
	}
);

test(
	taggedTitle(
		'single posts use native PNS entry navigation',
		'fast',
		'navigation',
		'template'
	),
	async ({ page }) => {
		await page.goto('/news/work-with-us-past-deadlines/');
		await page.waitForLoadState('domcontentloaded');

		const navigation = page.locator(
			'.pns-template-single .pns-entry-navigation'
		);
		const singleHeader = page.locator(
			'.pns-template-single :is(.pns-single-header, .pns-single-header__inner)'
		);
		const singleFeaturedImage = page.locator(
			'.pns-template-single :is(.pns-single-featured-image, .wp-block-cover)'
		);
		const singleTermPill = page
			.locator(
				'.pns-template-single .pns-single-terms.pns-taxonomy-pills a'
			)
			.first();

		await expect(singleHeader).toBeVisible();
		await expect(page.locator('.pns-single-title')).toBeVisible();
		await expect(page.locator('.pns-single-meta')).toBeVisible();
		await expect(
			page.locator('.pns-single-terms.pns-taxonomy-pills')
		).toBeVisible();
		await expect(singleTermPill).toHaveCSS(
			'border-top-left-radius',
			'999px'
		);
		await expect(singleTermPill).toHaveCSS('min-height', '24px');
		await expect(singleTermPill).toHaveCSS('font-size', '12px');
		await expect(singleTermPill).toHaveCSS(
			'background-color',
			'rgb(0, 107, 95)'
		);
		await expect(singleTermPill).toHaveCSS('color', 'rgb(255, 255, 255)');
		await expect(singleTermPill).toHaveCSS('font-weight', '700');
		await expect(singleTermPill).toHaveCSS('text-decoration-line', 'none');
		await expect(singleFeaturedImage.locator('img')).toHaveCSS(
			'object-fit',
			'cover'
		);

		const singleHeaderBox = await singleHeader.boundingBox();
		const singleFeaturedImageBox = await singleFeaturedImage.boundingBox();
		const previousNavigationBox = await navigation
			.getByRole('link', { name: 'Previous' })
			.boundingBox();
		const backToNewsBox = await navigation
			.getByRole('link', { name: 'Back to News' })
			.boundingBox();
		const nextNavigationBox = await navigation
			.getByRole('link', { name: 'Next' })
			.boundingBox();
		const singleFeaturedImageClass = await singleFeaturedImage
			.first()
			.evaluate((element) => element.className);
		const singleHeaderUsesCover = String(singleFeaturedImageClass).includes(
			'wp-block-cover'
		);

		expect(singleHeaderBox).not.toBeNull();
		expect(singleFeaturedImageBox).not.toBeNull();
		expect(previousNavigationBox).not.toBeNull();
		expect(backToNewsBox).not.toBeNull();
		expect(nextNavigationBox).not.toBeNull();
		annotateAcceptedStateInventory('single post navigation boxes', {
			backToNewsBox,
			nextNavigationBox,
			previousNavigationBox,
			singleFeaturedImageBox,
			singleHeaderBox,
		});
		expect(singleHeaderBox?.height ?? 0).toBeLessThanOrEqual(
			singleHeaderUsesCover ? 360 : 320
		);
		expect(singleFeaturedImageBox?.height ?? 0).toBeLessThanOrEqual(
			singleHeaderUsesCover ? 431 : 321
		);
		expect(
			Math.abs(
				(previousNavigationBox?.y ?? 0) +
					(previousNavigationBox?.height ?? 0) / 2 -
					((backToNewsBox?.y ?? 0) + (backToNewsBox?.height ?? 0) / 2)
			)
		).toBeLessThanOrEqual(16);
		expect(
			Math.abs(
				(nextNavigationBox?.y ?? 0) +
					(nextNavigationBox?.height ?? 0) / 2 -
					((backToNewsBox?.y ?? 0) + (backToNewsBox?.height ?? 0) / 2)
			)
		).toBeLessThanOrEqual(16);

		await expect(navigation).toHaveCount(1);
		await expect(
			page.locator('.pns-template-single .wp-block-post-navigation-link')
		).not.toHaveCount(0);
		await expect(
			navigation.getByRole('link', { name: 'Previous' })
		).toHaveAttribute(
			'href',
			/\/news\/workshop-unleashing-the-suffragette-spirit\/$/
		);
		await expect(
			navigation.getByRole('link', { name: 'Back to News' })
		).toHaveAttribute('href', /\/news\/$/);
		await expect(
			navigation.getByRole('link', { name: 'Next' })
		).toHaveAttribute('href', /\/news\/work-with-us-argyll\/$/);
		await expect(
			page
				.locator('.pns-template-single .wp-block-post-content a')
				.filter({ hasText: /Back to news/i })
		).toHaveCount(0);
		await expect(
			navigation.locator('.wp-block-post-navigation-link__arrow-previous')
		).toHaveText('←');
		await expect(
			navigation.locator('.wp-block-post-navigation-link__arrow-next')
		).toHaveText('→');

		const previousLink = navigation.getByRole('link', { name: 'Previous' });
		await previousLink.scrollIntoViewIfNeeded();

		const previousRest = await previousLink.evaluate((element) => {
			const computed = getComputedStyle(element);
			const rect = element.getBoundingClientRect();

			return {
				rect: {
					height: rect.height,
					width: rect.width,
					x: rect.x,
					y: rect.y,
				},
				transitionDuration: computed.transitionDuration,
				transitionProperty: computed.transitionProperty,
			};
		});

		expect(previousRest.transitionProperty).not.toContain('all');
		expect(previousRest.transitionProperty).toContain('box-shadow');
		expect(previousRest.transitionProperty).toContain(
			'text-decoration-thickness'
		);
		expect(previousRest.transitionDuration).not.toBe('0s');

		await previousLink.hover();

		const previousHover = await previousLink.evaluate((element) => {
			const computed = getComputedStyle(element);
			const rect = element.getBoundingClientRect();

			return {
				rect: {
					height: rect.height,
					width: rect.width,
					x: rect.x,
					y: rect.y,
				},
				textDecorationLine: computed.textDecorationLine,
			};
		});

		expect(previousHover.textDecorationLine).toBe('underline');
		expect(
			Math.abs(previousHover.rect.x - previousRest.rect.x)
		).toBeLessThan(0.5);
		expect(
			Math.abs(previousHover.rect.y - previousRest.rect.y)
		).toBeLessThan(0.5);
		expect(
			Math.abs(previousHover.rect.width - previousRest.rect.width)
		).toBeLessThan(0.5);
		expect(
			Math.abs(previousHover.rect.height - previousRest.rect.height)
		).toBeLessThan(0.5);
	}
);

for (const contract of fullWidthNewsPostContracts) {
	test(
		taggedTitle(
			`full-width news post uses a content-owned Enhanced Cover hero: ${contract.route}`,
			'fast',
			'layout',
			'template',
			'mobile-full',
			'mobile-layout'
		),
		async ({ page }) => {
			await page.goto(contract.route);
			await page.waitForLoadState('domcontentloaded');
			await waitForContractReady(page);

			const viewportWidth = page.viewportSize()?.width ?? 0;
			const main = page.locator('.pns-template-single-full-width-news');
			const pageHero = main.locator(
				'.wp-block-ran-enhanced-cover.pns-page-hero'
			);
			const heroCopy = pageHero.locator('.pns-hero-copy');
			const heroTitle = heroCopy.locator('h1');
			const heroPrimaryStrapline = heroCopy.locator(
				'.pns-editorial-strapline--primary'
			);
			const heroSecondaryStrapline = heroCopy.locator(
				'.pns-editorial-strapline--secondary'
			);
			const heroDetails = heroCopy.locator('.wp-block-pns-post-details');
			const heroMetadata = heroDetails.locator(
				'.wp-block-pns-post-metadata'
			);
			const heroPostMeta = heroMetadata.locator('.pns-post-meta');
			const heroTerms = heroDetails.locator('.pns-single-terms');
			const heroMedia = pageHero.locator('.ran-video-cover__media');
			const postContent = main.locator('.wp-block-post-content');
			const firstColumns = main
				.locator('.pns-split-section__columns')
				.first();

			await expect(main).toHaveCount(1);
			await expect(page.locator('.pns-single-title')).toHaveCount(0);
			await expect(pageHero).toHaveCount(1);
			await expect(
				main.locator('.wp-block-cover.pns-page-hero')
			).toHaveCount(0);
			await expect(heroTitle).toHaveText(contract.title);
			await expect(heroPrimaryStrapline).toContainText(
				contract.primaryStrapline
			);
			await expect(heroDetails).toHaveCount(1);
			await expect(heroMetadata).toHaveCount(1);
			await expect(heroMetadata).toBeVisible();
			await expect(heroPostMeta).toBeVisible();
			await expect(heroTerms).toBeVisible();
			await expect(heroCopy.locator('.pns-single-terms')).toHaveCount(1);
			await expect(heroMedia).toBeVisible();
			expect(
				await heroMedia.evaluate(
					(media) => getComputedStyle(media).objectPosition
				)
			).toMatch(/%/);
			await expect(
				page.locator('.pns-full-width-news-hero-meta')
			).toHaveCount(0);
			await expect(
				postContent.locator(
					':scope > .wp-block-ran-enhanced-cover.pns-page-hero:first-child'
				)
			).toHaveCount(1);
			await expect(postContent.locator('.wp-block-cover')).toHaveCount(2);

			if (contract.secondaryStrapline) {
				await expect(heroSecondaryStrapline).toContainText(
					contract.secondaryStrapline
				);
			} else {
				await expect(heroSecondaryStrapline).toBeHidden();
			}
			await expect(
				heroPostMeta.getByRole('link', {
					name: 'Protests and Suffragettes',
				})
			).toHaveCSS('color', 'rgb(255, 255, 255)');
			await expect(heroDetails).toHaveCSS('color', 'rgb(255, 255, 255)');

			const mainBox = await main.boundingBox();
			const pageHeroBox = await pageHero.boundingBox();
			const heroDetailsBox = await heroDetails.boundingBox();
			const heroPostMetaBox = await heroPostMeta.boundingBox();
			const heroTermsBox = await heroTerms.boundingBox();
			const heroPrimaryStraplineBox =
				await heroPrimaryStrapline.boundingBox();
			const firstColumnsBox = await firstColumns.boundingBox();
			const firstColumnsContract = await firstColumns.evaluate(
				(element) => {
					const parent = element.parentElement;
					const styles = getComputedStyle(element);

					return {
						parentClassName: parent?.className ?? '',
						marginInlineStart: Number.parseFloat(
							styles.marginInlineStart
						),
						marginInlineEnd: Number.parseFloat(
							styles.marginInlineEnd
						),
					};
				}
			);
			const isEdgeMediaSection =
				firstColumnsContract.parentClassName.includes(
					'is-style-pns-edge-media-left'
				) ||
				firstColumnsContract.parentClassName.includes(
					'is-style-pns-edge-media-right'
				);

			expect(mainBox).not.toBeNull();
			expect(pageHeroBox).not.toBeNull();
			expect(heroDetailsBox).not.toBeNull();
			expect(heroPostMetaBox).not.toBeNull();
			expect(heroTermsBox).not.toBeNull();
			expect(heroPrimaryStraplineBox).not.toBeNull();
			expect(heroDetailsBox?.y ?? 0).toBeGreaterThanOrEqual(
				(heroPrimaryStraplineBox?.y ?? 0) +
					(heroPrimaryStraplineBox?.height ?? 0)
			);
			expect(heroTermsBox?.y ?? 0).toBeGreaterThanOrEqual(
				(heroPostMetaBox?.y ?? 0) + (heroPostMetaBox?.height ?? 0)
			);
			expect(firstColumnsBox).not.toBeNull();
			expect(Math.abs(mainBox?.x ?? 0)).toBeLessThanOrEqual(1);
			expect(
				Math.abs((mainBox?.width ?? 0) - viewportWidth)
			).toBeLessThanOrEqual(1);
			expect(Math.abs(pageHeroBox?.x ?? 0)).toBeLessThanOrEqual(1);
			expect(
				Math.abs((pageHeroBox?.width ?? 0) - viewportWidth)
			).toBeLessThanOrEqual(1);
			if (isEdgeMediaSection) {
				expect(Math.abs(firstColumnsBox?.x ?? 0)).toBeLessThanOrEqual(
					1
				);
				expect(
					Math.abs((firstColumnsBox?.width ?? 0) - viewportWidth)
				).toBeLessThanOrEqual(1);
			} else {
				expect(firstColumnsBox?.x ?? 0).toBeGreaterThanOrEqual(0);
				expect(
					(firstColumnsBox?.x ?? 0) + (firstColumnsBox?.width ?? 0)
				).toBeLessThanOrEqual(viewportWidth);
				expect(
					Math.abs(
						(firstColumnsBox?.x ?? 0) * 2 +
							(firstColumnsBox?.width ?? 0) -
							viewportWidth
					)
				).toBeLessThanOrEqual(1);
				expect(
					Math.abs(
						firstColumnsContract.marginInlineStart -
							firstColumnsContract.marginInlineEnd
					)
				).toBeLessThanOrEqual(1);
			}
		}
	);
}

test(
	taggedTitle(
		'herstory entries use PNS entry navigation arrows',
		'fast',
		'navigation',
		'template'
	),
	async ({ page }) => {
		await page.goto('/herstories/georgiana-solomon/');
		await page.waitForLoadState('domcontentloaded');

		const navigation = page.locator(
			'.pns-template-herstory-single .pns-herstory-entry-navigation'
		);

		await expect(navigation).toHaveCount(1);
		await expect(
			navigation.getByRole('link', { name: 'Previous' })
		).toHaveAttribute('href', /\/herstories\/agnes-dollan\/$/);
		await expect(
			navigation.getByRole('link', { name: 'Back to Herstories' })
		).toHaveAttribute('href', /\/herstories\/$/);
		await expect(
			navigation.getByRole('link', { name: 'Next' })
		).toHaveAttribute('href', /\/herstories\/helen-fraser\/$/);
		await expect(
			navigation.locator('.wp-block-post-navigation-link__arrow-previous')
		).toHaveText('←');
		await expect(
			navigation.locator('.wp-block-post-navigation-link__arrow-next')
		).toHaveText('→');
	}
);

test(
	taggedTitle(
		'herstories archive grid thumbnails use card image size',
		'fast',
		'archive',
		'template'
	),
	async ({ page }) => {
		await page.goto('/herstories/');
		await page.waitForLoadState('domcontentloaded');

		const template = page.locator('main.pns-template-herstories-archive');

		await expect(template).toBeVisible();
		await expect(template).toHaveCSS('overflow-x', 'clip');

		const featuredHerstorySection = template
			.locator('.wp-block-pns-featured-post.pns-featured-post')
			.first();
		await expect(featuredHerstorySection).toHaveCount(1);
		expect(
			await page.evaluate(() => {
				const hero = document.querySelector(
					'.pns-template-herstories-archive .pns-page-hero'
				);
				const featured = document.querySelector(
					'.pns-template-herstories-archive .wp-block-pns-featured-post'
				);

				return !!(
					hero &&
					featured &&
					hero.compareDocumentPosition(featured) &
						Node.DOCUMENT_POSITION_FOLLOWING
				);
			})
		).toBe(true);
		const featuredHerstorySplit =
			featuredHerstorySection.locator('.pns-split-section');
		await expect(featuredHerstorySection).toHaveClass(
			/\bpns-featured-post--herstory\b/
		);
		await expect(featuredHerstorySplit).toHaveClass(
			/\bis-style-pns-edge-media-left\b/
		);
		await expect(
			featuredHerstorySplit.locator(
				'.pns-split-section__media-column .wp-block-post-featured-image'
			)
		).toBeVisible();
		const featuredHerstoryFigure = featuredHerstorySplit.locator(
			'.pns-split-section__media-column .wp-block-post-featured-image'
		);
		await expect(featuredHerstoryFigure).toHaveCSS('margin-bottom', '0px');
		await expect(featuredHerstoryFigure).toHaveCSS('position', 'relative');
		await expect(featuredHerstoryFigure.locator('img')).toHaveClass(
			/\bsize-square\b/
		);
		const captionOverlay = await featuredHerstoryFigure.evaluate(
			(figure) => {
				const caption = document.createElement('figcaption');
				caption.textContent = 'Caption';
				figure.append(caption);

				const computed = getComputedStyle(caption);
				const styles = {
					backgroundColor: computed.backgroundColor,
					bottom: computed.bottom,
					color: computed.color,
					fontSize: computed.fontSize,
					marginBottom: computed.marginBottom,
					position: computed.position,
				};

				caption.remove();

				return styles;
			}
		);
		expect(captionOverlay.position).toBe('absolute');
		expect(captionOverlay.bottom).toBe('0px');
		expect(captionOverlay.marginBottom).toBe('0px');
		expect(captionOverlay.backgroundColor).toBe('rgba(32, 32, 32, 0.58)');
		expect(captionOverlay.color).toBe('rgb(255, 255, 255)');
		expect(parseFloat(captionOverlay.fontSize)).toBeLessThanOrEqual(16);
		await expect(
			featuredHerstorySplit.locator(
				'.pns-split-section__media-column .wp-block-jetpack-slideshow'
			)
		).toHaveCount(0);
		await expect(
			featuredHerstorySection.locator('.wp-block-post-title a')
		).toHaveAttribute('href', /\/herstories\/.+\/$/);
		await expect(
			featuredHerstorySection.locator(
				'.pns-featured-post__meta .wp-block-post-date time'
			)
		).toHaveAttribute('datetime', /T/);
		await expect(
			featuredHerstorySection.locator(
				'.pns-featured-post__meta .wp-block-post-author a'
			)
		).toHaveAttribute('href', /\/author\/.+\/$/);
		await expect(
			featuredHerstorySection.locator('.wp-block-post-excerpt')
		).toBeVisible();
		await expect(
			featuredHerstorySection.locator('.pns-featured-post__meta')
		).toHaveCSS('flex-wrap', 'nowrap');
		await expect(
			featuredHerstorySection.locator('.wp-block-post-excerpt')
		).toHaveCSS('border-bottom-width', '1px');
		await expect(
			featuredHerstorySection.locator('.pns-featured-post__footer')
		).toHaveCSS('padding-top', '16px');
		await expect(
			featuredHerstorySection.locator('.pns-featured-post__footer')
		).toHaveClass(/\bpns-taxonomy-pills\b/);

		const recoveringHerstoriesSection = template
			.locator('.pns-split-section')
			.filter({
				has: page.getByRole('heading', {
					name: 'Recovering Herstories',
					exact: true,
				}),
			})
			.first();
		await expect(recoveringHerstoriesSection).toHaveClass(
			/\bis-style-pns-edge-media-right\b/
		);

		const recoveringCopyColumn = recoveringHerstoriesSection.locator(
			'.pns-split-section__copy-column'
		);
		await expect(recoveringCopyColumn).not.toHaveAttribute('data-aos');

		const recoveringGeometry = await recoveringHerstoriesSection.evaluate(
			(section) => {
				const columns = section.querySelector<HTMLElement>(
					'.pns-split-section__columns'
				);
				const copyColumn = section.querySelector<HTMLElement>(
					'.pns-split-section__copy-column'
				);
				const copy = section.querySelector<HTMLElement>(
					'.pns-split-section__copy'
				);
				const mediaColumn = section.querySelector<HTMLElement>(
					'.pns-split-section__media-column'
				);

				if (!columns || !copyColumn || !copy || !mediaColumn) {
					return null;
				}

				const columnsRect = columns.getBoundingClientRect();
				const copyColumnRect = copyColumn.getBoundingClientRect();
				const copyRect = copy.getBoundingClientRect();
				const mediaColumnRect = mediaColumn.getBoundingClientRect();
				const isStacked = window.matchMedia(
					'(max-width: 59.999rem)'
				).matches;

				return {
					columnsViewportEndDelta:
						window.innerWidth - columnsRect.right,
					columnsViewportStartDelta: columnsRect.left,
					copyCenterDelta:
						copyRect.left +
						copyRect.width / 2 -
						(copyColumnRect.left + copyColumnRect.width / 2),
					copyMediaJoinDelta:
						copyColumnRect.right - mediaColumnRect.left,
					copyTrackEndDelta: columnsRect.right - copyColumnRect.right,
					copyTrackStartDelta: copyColumnRect.left - columnsRect.left,
					mediaTrackEndDelta:
						columnsRect.right - mediaColumnRect.right,
					mediaTrackStartDelta:
						mediaColumnRect.left - columnsRect.left,
					isStacked,
				};
			}
		);

		expect(recoveringGeometry).not.toBeNull();
		expect(
			Math.abs(recoveringGeometry?.columnsViewportStartDelta ?? 0)
		).toBeLessThanOrEqual(1);
		expect(
			Math.abs(recoveringGeometry?.columnsViewportEndDelta ?? 0)
		).toBeLessThanOrEqual(1);
		expect(
			Math.abs(recoveringGeometry?.mediaTrackEndDelta ?? 0)
		).toBeLessThanOrEqual(1);
		expect(
			Math.abs(recoveringGeometry?.copyCenterDelta ?? 0)
		).toBeLessThanOrEqual(1);

		if (recoveringGeometry?.isStacked) {
			expect(
				Math.abs(recoveringGeometry.copyTrackStartDelta)
			).toBeLessThanOrEqual(1);
			expect(
				Math.abs(recoveringGeometry.copyTrackEndDelta)
			).toBeLessThanOrEqual(1);
			expect(
				Math.abs(recoveringGeometry.mediaTrackStartDelta)
			).toBeLessThanOrEqual(1);
		} else {
			expect(
				recoveringGeometry?.copyTrackStartDelta ?? 0
			).toBeGreaterThan(0);
			expect(
				Math.abs(recoveringGeometry?.copyMediaJoinDelta ?? 0)
			).toBeLessThanOrEqual(1);
		}

		const overflow = await page.evaluate(() => ({
			documentWidth: document.documentElement.scrollWidth,
			viewportWidth: window.innerWidth,
		}));

		expect(overflow.documentWidth).toBeLessThanOrEqual(
			overflow.viewportWidth + 1
		);
		await expect(
			page
				.locator(
					'.pns-herstories-more-section .wp-block-post-featured-image img'
				)
				.first()
		).toHaveClass(/\bsize-card\b/);
		await expect(
			page
				.locator(
					'.pns-herstories-more-section .wp-block-post-featured-image img'
				)
				.first()
		).toHaveCSS('object-fit', 'cover');
		await expect(
			page
				.locator(
					'.pns-herstories-more-section .wp-block-post-featured-image'
				)
				.first()
		).toHaveCSS('height', '200px');
		await expect(
			page
				.locator(
					'.pns-herstories-more-section .wp-block-post-featured-image'
				)
				.first()
		).toHaveCSS('background-color', 'rgb(0, 107, 95)');
		const firstHerstoryCard = page
			.locator(
				'.pns-herstories-more-section .pns-archive-card.pns-post-card'
			)
			.first();
		const firstHerstoryTitleLink = firstHerstoryCard
			.locator('.wp-block-post-title a')
			.first();
		const firstHerstoryImage = firstHerstoryCard
			.locator('.wp-block-post-featured-image img')
			.first();
		const firstHerstoryTitleMotion = await firstHerstoryTitleLink.evaluate(
			(element) => {
				const computed = getComputedStyle(element);

				return {
					transitionDuration: computed.transitionDuration,
					transitionProperty: computed.transitionProperty,
				};
			}
		);
		const firstHerstoryImageMotion = await firstHerstoryImage.evaluate(
			(element) => {
				const computed = getComputedStyle(element);

				return {
					transitionDuration: computed.transitionDuration,
					transitionProperty: computed.transitionProperty,
				};
			}
		);

		await expect(firstHerstoryCard).toBeVisible();
		const firstHerstoryRowCardHeights = await page
			.locator(
				'.pns-herstories-more-section .pns-archive-card.pns-post-card'
			)
			.evaluateAll((cards) =>
				cards
					.slice(0, 3)
					.map((card) => card.getBoundingClientRect().height)
			);
		expect(
			Math.max(...firstHerstoryRowCardHeights) -
				Math.min(...firstHerstoryRowCardHeights)
		).toBeLessThanOrEqual(1);
		const firstHerstoryMeta = firstHerstoryCard.locator(
			'.pns-post-card__meta'
		);
		await expect(
			firstHerstoryMeta.locator('.wp-block-post-date')
		).toBeVisible();
		await expect(
			firstHerstoryMeta.locator('.wp-block-post-author a')
		).toHaveAttribute('href', /\/author\/tsbeall\/$/);
		expect(
			await firstHerstoryMeta
				.locator('.wp-block-post-author')
				.evaluate(
					(element) => getComputedStyle(element, '::before').content
				)
		).toBe('"|"');
		await expect(
			firstHerstoryCard.locator('.pns-post-card__footer').first()
		).toHaveClass(/\bpns-taxonomy-pills\b/);
		const firstHerstoryExcerpt = firstHerstoryCard.locator(
			'> .wp-block-post-excerpt'
		);
		await expect(firstHerstoryExcerpt).toHaveCSS(
			'border-bottom-width',
			'1px'
		);
		await expect(firstHerstoryExcerpt).toHaveCSS(
			'border-bottom-style',
			'solid'
		);
		await expect(firstHerstoryExcerpt).toHaveCSS('padding-bottom', '16px');
		await expect(
			firstHerstoryCard.locator('> .pns-post-card__footer')
		).toHaveCSS('padding-top', '16px');
		expect(firstHerstoryTitleMotion.transitionProperty).not.toContain(
			'all'
		);
		expect(firstHerstoryTitleMotion.transitionProperty).toContain(
			'text-decoration-thickness'
		);
		expect(firstHerstoryTitleMotion.transitionDuration).not.toBe('0s');
		expect(firstHerstoryImageMotion.transitionProperty).toBe('filter');
		expect(firstHerstoryImageMotion.transitionDuration).not.toBe('0s');
	}
);

test(
	taggedTitle(
		'page template reveal is gated and reduced-motion safe',
		'fast',
		'template'
	),
	async ({ page }) => {
		await page.emulateMedia({ reducedMotion: 'no-preference' });
		await page.goto('/');
		await page.waitForLoadState('domcontentloaded');

		const motion = await page.evaluate(() => {
			const template = document.querySelector('main.pns-template');
			const computed = template ? getComputedStyle(template) : null;

			return {
				bodyClass: document.body.className,
				animationDuration: computed?.animationDuration ?? '',
				animationName: computed?.animationName ?? '',
				opacity: computed?.opacity ?? '',
				transform: computed?.transform ?? '',
			};
		});

		expect(motion.bodyClass).toContain('pns-template-reveal-enabled');
		expect(motion.animationName).toBe('pns-template-reveal');
		expect(motion.animationDuration).toBe('0.22s');
		expect(motion.opacity).not.toBe('0');
		expect(motion.transform).toBe('none');

		await page.emulateMedia({ reducedMotion: 'reduce' });
		await page.goto('/');
		await page.waitForLoadState('domcontentloaded');

		const reducedMotion = await page.evaluate(() => {
			const template = document.querySelector('main.pns-template');
			const computed = template ? getComputedStyle(template) : null;

			return {
				bodyClass: document.body.className,
				animationName: computed?.animationName ?? '',
				opacity: computed?.opacity ?? '',
				transform: computed?.transform ?? '',
			};
		});

		expect(reducedMotion.bodyClass).toContain(
			'pns-template-reveal-enabled'
		);
		expect(reducedMotion.animationName).toBe('none');
		expect(reducedMotion.opacity).toBe('1');
		expect(reducedMotion.transform).toBe('none');
	}
);

for (const route of genericTemplateLightSurfaceRoutes) {
	test(
		taggedTitle(
			`generic template light surface contract: ${route.name}`,
			'fast',
			'template',
			'layout'
		),
		async ({ page }) => {
			await page.goto(route.path);
			await page.waitForLoadState('domcontentloaded');
			await waitForContractReady(page);

			await expect(page.locator(route.selector)).toBeVisible();

			const styles = await page.evaluate(
				({ selector, textSelector }) => {
					const surface = document.querySelector(selector);

					if (!surface) {
						return null;
					}

					function read(element: Element | null) {
						if (!element) {
							return null;
						}

						const computed = getComputedStyle(element);

						return {
							backgroundColor: computed.backgroundColor,
							color: computed.color,
							hasTextColor:
								element.classList.contains('has-text-color'),
						};
					}

					const link =
						Array.from(
							surface.querySelectorAll<HTMLAnchorElement>(
								'a:not(.wp-block-button__link):not([rel="tag"])'
							)
						).find(
							(candidate) => !candidate.closest('.pns-page-hero')
						) ?? null;
					const heading =
						Array.from(
							surface.querySelectorAll<HTMLElement>(
								':is(h1, h2, .wp-block-heading, .wp-block-post-title)'
							)
						).find(
							(candidate) => !candidate.closest('.pns-page-hero')
						) ?? null;

					return {
						className: surface.className,
						documentWidth: document.documentElement.scrollWidth,
						heading: read(heading),
						link: read(link),
						surface: read(surface),
						text: read(surface.querySelector(textSelector)),
						viewportWidth: window.innerWidth,
					};
				},
				{
					selector: route.selector,
					textSelector: route.textSelector,
				}
			);

			expect(styles?.className).toContain('pns-light-surface');
			expect(styles?.heading).not.toBeNull();
			expect(styles?.text).not.toBeNull();
			expect(styles?.surface?.backgroundColor).toBe('rgb(255, 255, 255)');
			expect(styles?.surface?.color).toBe('rgb(79, 77, 73)');
			if (styles?.heading?.hasTextColor) {
				expect(styles.heading.color).not.toBe('rgb(255, 255, 255)');
			} else {
				expect(styles?.heading?.color).toBe('rgb(22, 22, 22)');
			}
			expect(styles?.text?.color).not.toBe('rgb(255, 255, 255)');

			if (styles?.link) {
				expect(styles.link.color).not.toBe('rgb(255, 255, 255)');
			}

			expect(styles?.documentWidth ?? 0).toBeLessThanOrEqual(
				(styles?.viewportWidth ?? 0) + 1
			);
		}
	);
}

for (const route of lightSurfaceExcludedRoutes) {
	test(
		taggedTitle(
			`light surface does not leak onto page templates: ${route.name}`,
			'audit',
			'template'
		),
		async ({ page }) => {
			await page.goto(route.path);
			await page.waitForLoadState('domcontentloaded');
			await waitForContractReady(page);

			const template = page.locator('main.pns-template').first();
			await expect(template).toBeVisible();
			await expect(template).toHaveClass(
				new RegExp(`\\b${route.expectedMainClass}\\b`)
			);
			await expect(template).not.toHaveClass(/\bpns-light-surface\b/);
			await expect(page.locator('.pns-light-surface')).toHaveCount(0);
		}
	);
}

for (const route of shopLightSurfaceRoutes) {
	test(
		taggedTitle(
			`shop uses wide-content light surface template: ${route.name}`,
			'audit',
			'shop',
			'template'
		),
		async ({ page }) => {
			await page.goto(route.path);
			await page.waitForLoadState('domcontentloaded');
			await waitForContractReady(page);

			const template = page.locator('main.pns-template').first();
			const postContent = page.locator('.wp-block-post-content').first();
			const shopHero = postContent.locator(
				'> .wp-block-cover.pns-page-hero'
			);
			const shopHeroCopy = shopHero.locator('.pns-hero-copy');
			const shopStorefront = page.locator('.pns-shop-storefront').first();

			await expect(template).toBeVisible();
			await expect(template).toHaveClass(
				/\bpns-template-page-light-surface-wide-content\b/
			);
			await expect(template).toHaveClass(/\bpns-light-surface\b/);
			await expect(postContent).not.toHaveClass(/\bpns-light-surface\b/);
			await expect(postContent).not.toHaveClass(/\bpns-shop-surface\b/);
			await expect(shopHero).toHaveCount(1);
			await expect(shopHeroCopy).toHaveCount(1);
			await expect(shopHeroCopy.locator('h1')).toHaveText('Shop P&S');

			const heroRail = await shopHero.evaluate((hero) => {
				const inner =
					hero.querySelector<HTMLElement>('.pns-section-inner');
				const copy = hero.querySelector<HTMLElement>('.pns-hero-copy');
				const title = copy?.querySelector<HTMLElement>('h1');

				if (!inner || !copy || !title) {
					return null;
				}

				return {
					copyPadding: Number.parseFloat(
						getComputedStyle(copy).paddingInlineStart
					),
					innerLeft: inner.getBoundingClientRect().left,
					titleLeft: title.getBoundingClientRect().left,
				};
			});

			expect(heroRail).not.toBeNull();
			expect(
				Math.abs(
					(heroRail?.titleLeft ?? 0) -
						((heroRail?.innerLeft ?? 0) +
							(heroRail?.copyPadding ?? 0))
				)
			).toBeLessThan(1);
			await expect(shopStorefront).toBeVisible();
			await expect(
				shopStorefront.locator('.pns-section-frame')
			).toHaveCount(1);
			await expect(template.locator('.pns-connect-social')).toHaveCount(
				0
			);
			await expect(template.locator('.pns-contact-form')).toHaveCount(0);
			await expect(page.locator('.pns-light-surface')).toHaveCount(1);
		}
	);
}

test(
	taggedTitle(
		'contact page keeps its editor-owned form in the light-surface template',
		'fast',
		'mobile-fast',
		'mobile-full',
		'template',
		'layout',
		'mobile-layout'
	),
	async ({ page }) => {
		await page.goto('/contact-us/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		const template = page.locator('main.pns-template').first();

		await expect(template).toBeVisible();
		await expect(template).toHaveClass(
			/\bpns-template-page-light-surface\b/
		);
		await expect(template).toHaveClass(/\bpns-light-surface\b/);

		const contract = await page.evaluate(() => {
			const surface = document.querySelector(
				'main.pns-template-page-light-surface'
			);
			const content = surface?.querySelector('.wp-block-post-content');
			const hero = content?.firstElementChild as HTMLElement | null;
			const heroHeading = hero?.querySelector<HTMLElement>('h1');
			const heroBounds = hero?.getBoundingClientRect();
			const heroHeadingBounds = heroHeading?.getBoundingClientRect();
			const railProbe = document.createElement('div');
			railProbe.style.cssText =
				'position:absolute;visibility:hidden;inline-size:var(--pns--layout--content-rail);block-size:1px;';
			document.body.append(railProbe);
			const contentRail = railProbe.getBoundingClientRect().width;
			railProbe.remove();

			return {
				contactFormIds: Array.from(
					content?.querySelectorAll(
						'input[name="contact-form-id"]'
					) ?? []
				).map((input) => input.getAttribute('value')),
				emailOctopusBlocks: content?.querySelectorAll(
					'[data-form="3637e2c8-ff87-11ef-8552-6b8c59d486cb"], .emailoctopus-form-wrapper, .emailoctopus-form'
				).length,
				documentWidth: document.documentElement.scrollWidth,
				fieldGroups: {
					checkbox: content?.querySelectorAll(
						'.wp-block-jetpack-field-checkbox'
					).length,
					email: content?.querySelectorAll(
						'.wp-block-jetpack-field-email'
					).length,
					name: content?.querySelectorAll(
						'.wp-block-jetpack-field-name'
					).length,
					textarea: content?.querySelectorAll(
						'.wp-block-jetpack-field-textarea'
					).length,
				},
				jetpackContainers: content?.querySelectorAll(
					'#contact-form-6236.jetpack-contact-form-container'
				).length,
				jetpackForms: content?.querySelectorAll(
					'form.jetpack-contact-form__form.has-jetpack-form-layout'
				).length,
				heroHeadingLeft: heroHeadingBounds?.left,
				heroIsOpeningVideoCover: hero?.classList.contains(
					'wp-block-ran-enhanced-cover'
				),
				heroLeft: heroBounds?.left,
				heroWidth: heroBounds?.width,
				postContentUsesContentFrame:
					content?.classList.contains('pns-content-frame'),
				contentRail,
				viewportWidth: window.innerWidth,
			};
		});

		expect(contract.jetpackContainers).toBe(1);
		expect(contract.jetpackForms).toBe(1);
		expect(contract.contactFormIds).toEqual(['6236']);
		expect(contract.fieldGroups.name).toBe(1);
		expect(contract.fieldGroups.email).toBe(1);
		expect(contract.fieldGroups.textarea).toBe(1);
		expect(contract.fieldGroups.checkbox).toBe(1);
		expect(contract.emailOctopusBlocks).toBe(0);
		expect(contract.postContentUsesContentFrame).toBe(true);
		expect(contract.heroIsOpeningVideoCover).toBe(true);
		expect(contract.heroLeft).toBeCloseTo(0, 0);
		expect(contract.heroWidth).toBeCloseTo(contract.viewportWidth, 0);
		expect(contract.heroHeadingLeft).toBeCloseTo(
			Math.max((contract.viewportWidth - themeWideSize) / 2, 0) +
				contract.contentRail,
			0
		);
		expect(contract.documentWidth).toBeLessThanOrEqual(
			contract.viewportWidth + 1
		);
	}
);

test(
	taggedTitle(
		'short template surfaces reserve scoped content height',
		'fast',
		'mobile-fast',
		'layout',
		'mobile-layout'
	),
	async ({ page }) => {
		test.setTimeout(60_000);

		const cases = [
			{
				path: '/contact-success/',
				selector:
					'main.pns-template-page-light-surface-no-contact-form',
				floor: 'no-contact',
			},
			{
				path: '/search/',
				selector: 'main.pns-template-page-search',
				floor: 'short',
			},
			{
				path: '/search/unlikely-search-token-xyz/',
				selector: 'main.pns-template-search',
				floor: 'short',
			},
			{
				path: '/phase-2-light-surface-missing-route/',
				selector: 'main.pns-template-404',
				floor: 'short',
			},
		] as const;

		for (const route of cases) {
			await page.goto(route.path);
			await page.waitForLoadState('domcontentloaded');
			await waitForContractReady(page);

			const contract = await page.evaluate((currentRoute) => {
				const main = document.querySelector(currentRoute.selector);

				if (!main) {
					return null;
				}

				const computed = getComputedStyle(main);
				const viewportHeight = window.innerHeight;
				const expectedFloor =
					currentRoute.floor === 'no-contact'
						? Math.min(
								52 * 16,
								Math.max(42 * 16, viewportHeight * 0.82)
							)
						: Math.min(
								36 * 16,
								Math.max(28 * 16, viewportHeight * 0.52)
							);

				return {
					documentWidth: document.documentElement.scrollWidth,
					mainHeight: main.getBoundingClientRect().height,
					minBlockSize: parseFloat(computed.minBlockSize),
					viewportWidth: window.innerWidth,
					expectedFloor,
				};
			}, route);

			expect(contract).not.toBeNull();
			expect(contract?.minBlockSize ?? 0).toBeGreaterThanOrEqual(
				(contract?.expectedFloor ?? 0) - 2
			);
			expect(contract?.mainHeight ?? 0).toBeGreaterThanOrEqual(
				(contract?.expectedFloor ?? 0) - 2
			);
			expect(contract?.documentWidth ?? 0).toBeLessThanOrEqual(
				(contract?.viewportWidth ?? 0) + 1
			);
		}

		for (const route of [
			{
				path: '/news/',
				selector: 'main.pns-template-news-archive',
			},
			{
				path: '/herstories/',
				selector: 'main.pns-template-herstories-archive',
			},
		]) {
			await page.goto(route.path);
			await page.waitForLoadState('domcontentloaded');
			await waitForContractReady(page);

			const minBlockSize = await page.evaluate((currentRoute) => {
				const main = document.querySelector(currentRoute.selector);

				if (!main) {
					return -1;
				}

				const parsed = parseFloat(getComputedStyle(main).minBlockSize);

				return Number.isFinite(parsed) ? parsed : 0;
			}, route);

			expect(minBlockSize).toBeLessThanOrEqual(1);
		}
	}
);

test(
	taggedTitle(
		'shop storefront reserves space before Ecwid hydration',
		'fast',
		'mobile-fast',
		'layout',
		'mobile-layout',
		'shop',
		'ecwid'
	),
	async ({ page }) => {
		await page.goto('/shop/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		const initial = await page.evaluate(() => {
			const storefront = document.querySelector('.pns-shop-storefront');

			if (!storefront) {
				return null;
			}

			const computed = getComputedStyle(storefront);
			const expectedFloor = Math.min(
				110 * 16,
				Math.max(
					72 * 16,
					window.innerHeight * 1.8,
					window.innerWidth * 1.15
				)
			);

			return {
				documentWidth: document.documentElement.scrollWidth,
				height: storefront.getBoundingClientRect().height,
				minBlockSize: parseFloat(computed.minBlockSize),
				viewportWidth: window.innerWidth,
				expectedFloor,
			};
		});

		expect(initial).not.toBeNull();
		expect(initial?.minBlockSize ?? 0).toBeGreaterThanOrEqual(
			(initial?.expectedFloor ?? 0) - 2
		);
		expect(initial?.height ?? 0).toBeGreaterThanOrEqual(
			(initial?.expectedFloor ?? 0) - 2
		);
		expect(initial?.documentWidth ?? 0).toBeLessThanOrEqual(
			(initial?.viewportWidth ?? 0) + 1
		);

		await page.waitForTimeout(1500);

		const hydrated = await page.evaluate(() => {
			const storefront = document.querySelector('.pns-shop-storefront');

			return {
				documentWidth: document.documentElement.scrollWidth,
				height: storefront?.getBoundingClientRect().height ?? 0,
				viewportWidth: window.innerWidth,
			};
		});

		expect(hydrated.height).toBeGreaterThanOrEqual(
			(initial?.height ?? 0) - 2
		);
		expect(hydrated.documentWidth).toBeLessThanOrEqual(
			hydrated.viewportWidth + 1
		);
	}
);

test(
	taggedTitle(
		'light surface core buttons and base form controls stay readable at rest and on hover',
		'fast',
		'template'
	),
	async ({ page }) => {
		await page.goto('/phase-2-light-surface-missing-route/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		await page.evaluate(() => {
			const surface = document.querySelector('.pns-light-surface');

			if (!surface) {
				return;
			}

			const fixture = document.createElement('div');
			fixture.id = 'pns-light-surface-button-fixture';
			fixture.className = 'wp-block-button';
			fixture.innerHTML =
				'<a class="wp-block-button__link wp-element-button" href="#light-surface-button-fixture">Light surface button</a>';
			surface.append(fixture);

			const outlineFixture = document.createElement('div');
			outlineFixture.id = 'pns-light-surface-outline-button-fixture';
			outlineFixture.className = 'wp-block-button is-style-outline';
			outlineFixture.innerHTML =
				'<a class="wp-block-button__link wp-element-button" href="#light-surface-outline-button-fixture">Secondary action</a>';
			surface.append(outlineFixture);

			const disabledPairFixture = document.createElement('div');
			disabledPairFixture.id =
				'pns-light-surface-disabled-button-pair-fixture';
			disabledPairFixture.className = 'wp-block-buttons';
			disabledPairFixture.innerHTML =
				'<div class="wp-block-button"><a class="wp-block-button__link wp-element-button disabled" href="#light-surface-disabled-primary-button-fixture" aria-disabled="true" tabindex="-1">Disabled primary</a></div><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button disabled" href="#light-surface-disabled-outline-button-fixture" aria-disabled="true" tabindex="-1">Disabled secondary</a></div>';
			surface.append(disabledPairFixture);

			const entryNavigationActionFixture = document.createElement('div');
			entryNavigationActionFixture.id =
				'pns-light-surface-entry-navigation-action-fixture';
			entryNavigationActionFixture.className =
				'wp-block-button pns-entry-navigation__action';
			entryNavigationActionFixture.innerHTML =
				'<a class="wp-block-button__link wp-element-button" href="#light-surface-entry-navigation-action-fixture">Entry navigation action</a>';
			surface.append(entryNavigationActionFixture);

			const formFixture = document.createElement('form');
			formFixture.id = 'pns-light-surface-submit-fixture';
			formFixture.innerHTML =
				'<label for="pns-form-text-fixture">Text fixture</label><input id="pns-form-text-fixture" type="text" value="Text fixture"><label for="pns-form-select-fixture">Select fixture</label><select id="pns-form-select-fixture"><option>Option fixture</option></select><label for="pns-form-textarea-fixture">Textarea fixture</label><textarea id="pns-form-textarea-fixture">Textarea fixture</textarea><label for="pns-form-disabled-text-fixture">Disabled text fixture</label><input id="pns-form-disabled-text-fixture" type="text" value="Disabled text fixture" disabled><label><input id="pns-form-checkbox-fixture" type="checkbox" checked>Checkbox fixture</label><label><input id="pns-form-radio-fixture" type="radio" checked>Radio fixture</label><button type="button">Native fixture</button><input type="submit" value="Submit fixture"><input type="submit" value="Disabled fixture" disabled>';
			surface.append(formFixture);
		});

		const button = page.locator(
			'#pns-light-surface-button-fixture .wp-block-button__link:not(.has-text-color)'
		);
		const outlineButton = page.locator(
			'#pns-light-surface-outline-button-fixture .wp-block-button__link:not(.has-text-color)'
		);
		const disabledPrimaryButton = page.locator(
			'#pns-light-surface-disabled-button-pair-fixture .wp-block-button:not(.is-style-outline) .wp-block-button__link:not(.has-text-color)'
		);
		const disabledOutlineButton = page.locator(
			'#pns-light-surface-disabled-button-pair-fixture .wp-block-button.is-style-outline .wp-block-button__link:not(.has-text-color)'
		);
		const entryNavigationAction = page.locator(
			'#pns-light-surface-entry-navigation-action-fixture .wp-block-button__link:not(.has-text-color)'
		);
		const nativeButton = page.locator(
			'#pns-light-surface-submit-fixture button[type="button"]'
		);
		const submit = page.locator(
			'#pns-light-surface-submit-fixture input[type="submit"]:not(:disabled)'
		);
		const disabledSubmit = page.locator(
			'#pns-light-surface-submit-fixture input[type="submit"]:disabled'
		);
		const textInput = page.locator('#pns-form-text-fixture');
		const select = page.locator('#pns-form-select-fixture');
		const textarea = page.locator('#pns-form-textarea-fixture');
		const disabledTextInput = page.locator(
			'#pns-form-disabled-text-fixture'
		);
		const checkbox = page.locator('#pns-form-checkbox-fixture');
		const radio = page.locator('#pns-form-radio-fixture');

		await expect(button).toBeVisible();
		await expect(outlineButton).toBeVisible();
		await expect(disabledPrimaryButton).toBeVisible();
		await expect(disabledOutlineButton).toBeVisible();
		await expect(entryNavigationAction).toBeVisible();
		await expect(nativeButton).toBeVisible();
		await expect(submit).toBeVisible();
		await expect(disabledSubmit).toBeVisible();
		await expect(textInput).toBeVisible();
		await expect(select).toBeVisible();
		await expect(textarea).toBeVisible();
		await expect(disabledTextInput).toBeVisible();
		await expect(checkbox).toBeVisible();
		await expect(radio).toBeVisible();

		const rest = await button.evaluate((element) => {
			const computed = getComputedStyle(element);
			const after = getComputedStyle(element, '::after');

			return {
				afterBackgroundColor: after.backgroundColor,
				afterTransitionProperty: after.transitionProperty,
				backgroundColor: computed.backgroundColor,
				boxShadow: computed.boxShadow,
				color: computed.color,
				fontFamily: computed.fontFamily,
				fontSize: computed.fontSize,
				insetBlockStart: computed.insetBlockStart,
				insetInlineStart: computed.insetInlineStart,
				transitionProperty: computed.transitionProperty,
			};
		});

		expect(rest.backgroundColor).toBe('rgb(61, 32, 126)');
		expect(rest.color).toBe('rgb(255, 255, 255)');
		expect(rest.boxShadow).toContain('rgb(123, 220, 181)');
		expect(rest.afterBackgroundColor).toBe('rgba(0, 0, 0, 0)');
		expect(rest.afterTransitionProperty).toBe('background-color');
		expect(rest.transitionProperty).not.toContain('all');
		expect(rest.transitionProperty).toContain('box-shadow');

		const outlineRest = await outlineButton.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				backgroundColor: computed.backgroundColor,
				boxShadow: computed.boxShadow,
				color: computed.color,
			};
		});
		const disabledOutlineRest = await disabledOutlineButton.evaluate(
			(element) => {
				const computed = getComputedStyle(element);

				return {
					backgroundColor: computed.backgroundColor,
					boxShadow: computed.boxShadow,
					color: computed.color,
					cursor: computed.cursor,
					fontFamily: computed.fontFamily,
					fontSize: computed.fontSize,
					opacity: computed.opacity,
					transform: computed.transform,
				};
			}
		);
		const disabledPrimaryRest = await disabledPrimaryButton.evaluate(
			(element) => {
				const computed = getComputedStyle(element);

				return {
					backgroundColor: computed.backgroundColor,
					boxShadow: computed.boxShadow,
					color: computed.color,
					cursor: computed.cursor,
					fontFamily: computed.fontFamily,
					fontSize: computed.fontSize,
					opacity: computed.opacity,
				};
			}
		);
		const nativeRest = await nativeButton.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				backgroundColor: computed.backgroundColor,
				boxShadow: computed.boxShadow,
				color: computed.color,
				fontFamily: computed.fontFamily,
				fontSize: computed.fontSize,
			};
		});
		const submitRest = await submit.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				backgroundColor: computed.backgroundColor,
				color: computed.color,
				fontFamily: computed.fontFamily,
				fontSize: computed.fontSize,
			};
		});
		const disabledRest = await disabledSubmit.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				boxShadow: computed.boxShadow,
				cursor: computed.cursor,
				fontFamily: computed.fontFamily,
				fontSize: computed.fontSize,
				opacity: computed.opacity,
				transform: computed.transform,
			};
		});
		const clearRest = await entryNavigationAction.evaluate((element) => {
			const computed = getComputedStyle(element);
			const after = getComputedStyle(element, '::after');

			return {
				afterBackgroundColor: after.backgroundColor,
				backgroundColor: computed.backgroundColor,
				boxShadow: computed.boxShadow,
				color: computed.color,
				textDecorationLine: computed.textDecorationLine,
			};
		});

		expect(outlineRest.backgroundColor).toBe('rgba(0, 0, 0, 0)');
		expect(outlineRest.boxShadow).toContain('rgb(61, 32, 126)');
		expect(outlineRest.color).toBe('rgb(61, 32, 126)');

		await outlineButton.hover();
		await page.mouse.down();

		const outlinePressed = await outlineButton.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				boxShadow: computed.boxShadow,
				transform: computed.transform,
			};
		});

		await page.mouse.up();

		expect(outlinePressed.boxShadow).toContain('rgb(31, 159, 114)');
		expect(outlinePressed.transform).not.toBe('none');
		expect(disabledOutlineRest.backgroundColor).toBe('rgb(240, 240, 240)');
		expect(disabledOutlineRest.boxShadow).toContain('rgb(211, 205, 195)');
		expect(disabledOutlineRest.color).not.toBe(outlineRest.color);
		expect(disabledOutlineRest.cursor).toBe('not-allowed');
		expect(disabledOutlineRest.fontFamily).toBe(rest.fontFamily);
		expect(disabledOutlineRest.fontSize).toBe(rest.fontSize);
		expect(disabledOutlineRest.opacity).toBe('1');
		expect(disabledPrimaryRest.backgroundColor).toBe('rgb(61, 32, 126)');
		expect(disabledPrimaryRest.boxShadow).toContain('rgb(123, 220, 181)');
		expect(disabledPrimaryRest.color).toBe('rgb(255, 255, 255)');
		expect(disabledPrimaryRest.cursor).toBe('not-allowed');
		expect(disabledPrimaryRest.fontFamily).toBe(rest.fontFamily);
		expect(disabledPrimaryRest.fontSize).toBe(rest.fontSize);
		expect(Number.parseFloat(disabledPrimaryRest.opacity)).toBeLessThan(1);
		expect(nativeRest.backgroundColor).toBe('rgb(61, 32, 126)');
		expect(nativeRest.boxShadow).toContain('rgb(123, 220, 181)');
		expect(nativeRest.color).toBe('rgb(255, 255, 255)');
		expect(nativeRest.fontFamily).toBe(rest.fontFamily);
		expect(nativeRest.fontSize).toBe(rest.fontSize);
		expect(submitRest.backgroundColor).toBe('rgb(61, 32, 126)');
		expect(submitRest.color).toBe('rgb(255, 255, 255)');
		expect(submitRest.fontFamily).toBe(rest.fontFamily);
		expect(submitRest.fontSize).toBe(rest.fontSize);
		expect(disabledRest.cursor).toBe('not-allowed');
		expect(disabledRest.fontFamily).toBe(rest.fontFamily);
		expect(disabledRest.fontSize).toBe(rest.fontSize);
		expect(Number.parseFloat(disabledRest.opacity)).toBeLessThan(1);
		expect(clearRest.backgroundColor).toBe('rgba(0, 0, 0, 0)');
		expect(clearRest.boxShadow).toContain('0px 0px 0px 0px');
		expect(clearRest.afterBackgroundColor).toBe('rgba(0, 0, 0, 0)');
		expect(clearRest.color).toBe('rgb(22, 22, 22)');
		expect(clearRest.textDecorationLine).toBe('none');

		const formRest = await textInput.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				backgroundColor: computed.backgroundColor,
				borderColor: computed.borderColor,
				borderRadius: computed.borderRadius,
				borderStyle: computed.borderStyle,
				borderWidth: computed.borderWidth,
				boxSizing: computed.boxSizing,
				color: computed.color,
				fontFamily: computed.fontFamily,
				fontSize: computed.fontSize,
				inlineSize: computed.inlineSize,
				lineHeight: computed.lineHeight,
				paddingBlockStart: computed.paddingBlockStart,
				paddingInlineStart: computed.paddingInlineStart,
			};
		});
		const selectRest = await select.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				backgroundColor: computed.backgroundColor,
				borderColor: computed.borderColor,
				boxSizing: computed.boxSizing,
				minBlockSize: computed.minBlockSize,
				paddingInlineStart: computed.paddingInlineStart,
			};
		});
		const textareaRest = await textarea.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				backgroundColor: computed.backgroundColor,
				borderColor: computed.borderColor,
				minBlockSize: computed.minBlockSize,
				paddingInlineStart: computed.paddingInlineStart,
				resize: computed.resize,
			};
		});
		const disabledTextRest = await disabledTextInput.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				backgroundColor: computed.backgroundColor,
				cursor: computed.cursor,
				opacity: computed.opacity,
			};
		});
		const choiceRest = await checkbox.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				accentColor: computed.accentColor,
				blockSize: computed.blockSize,
				inlineSize: computed.inlineSize,
				marginInlineEnd: computed.marginInlineEnd,
			};
		});
		const radioRest = await radio.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				accentColor: computed.accentColor,
				blockSize: computed.blockSize,
				inlineSize: computed.inlineSize,
			};
		});

		expect(formRest.backgroundColor).toBe('rgb(255, 255, 255)');
		expect(formRest.borderColor).toBe('rgb(140, 143, 148)');
		expect(formRest.borderStyle).toBe('solid');
		expect(formRest.borderWidth).toBe('1px');
		expect(formRest.borderRadius).toBe('0px');
		expect(formRest.boxSizing).toBe('border-box');
		expect(formRest.color).toBe('rgb(79, 77, 73)');
		expect(formRest.fontFamily).toContain('Libre Franklin');
		expect(formRest.fontSize).toBe('16px');
		expect(formRest.inlineSize).not.toBe('auto');
		expect(formRest.paddingBlockStart).toBe('26px');
		expect(formRest.paddingInlineStart).toBe('26px');
		expect(selectRest.backgroundColor).toBe(formRest.backgroundColor);
		expect(selectRest.borderColor).toBe(formRest.borderColor);
		expect(selectRest.boxSizing).toBe('border-box');
		expect(Number.parseFloat(selectRest.minBlockSize)).toBeGreaterThan(78);
		expect(selectRest.paddingInlineStart).toBe(formRest.paddingInlineStart);
		expect(textareaRest.backgroundColor).toBe(formRest.backgroundColor);
		expect(textareaRest.borderColor).toBe(formRest.borderColor);
		expect(textareaRest.minBlockSize).toBe('160px');
		expect(textareaRest.paddingInlineStart).toBe(
			formRest.paddingInlineStart
		);
		expect(textareaRest.resize).toBe('vertical');
		expect(disabledTextRest.backgroundColor).toBe('rgb(240, 240, 240)');
		expect(disabledTextRest.cursor).toBe('not-allowed');
		expect(Number.parseFloat(disabledTextRest.opacity)).toBeLessThan(1);
		expect(choiceRest.accentColor).toBe('rgb(61, 32, 126)');
		expect(choiceRest.blockSize).toBe('18px');
		expect(choiceRest.inlineSize).toBe('18px');
		expect(choiceRest.marginInlineEnd).toBe('8px');
		expect(radioRest.accentColor).toBe(choiceRest.accentColor);
		expect(radioRest.blockSize).toBe(choiceRest.blockSize);
		expect(radioRest.inlineSize).toBe(choiceRest.inlineSize);

		await textInput.focus();
		const formFocus = await textInput.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				borderColor: computed.borderColor,
				borderWidth: computed.borderWidth,
				boxShadow: computed.boxShadow,
				outlineStyle: computed.outlineStyle,
			};
		});

		expect(formFocus.borderColor).toBe('rgb(0, 0, 0)');
		expect(formFocus.borderWidth).toBe('2px');
		expect(formFocus.boxShadow).toContain('rgb(0, 0, 0)');
		expect(formFocus.outlineStyle).toBe('none');

		await button.scrollIntoViewIfNeeded();
		const restBox = await button.boundingBox();
		await button.hover();
		const hoverBox = await button.boundingBox();

		if (!restBox || !hoverBox) {
			throw new Error('Expected light surface button geometry.');
		}

		expect(Math.abs(hoverBox.x - restBox.x)).toBeLessThan(0.5);
		expect(Math.abs(hoverBox.y - restBox.y)).toBeLessThan(0.5);
		expect(Math.abs(hoverBox.width - restBox.width)).toBeLessThan(0.5);
		expect(Math.abs(hoverBox.height - restBox.height)).toBeLessThan(0.5);

		const hover = await button.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				backgroundColor: computed.backgroundColor,
				boxShadow: computed.boxShadow,
				color: computed.color,
				insetBlockStart: computed.insetBlockStart,
				insetInlineStart: computed.insetInlineStart,
			};
		});

		expect(hover.backgroundColor).toBe('rgb(61, 32, 126)');
		expect(hover.color).toBe('rgb(255, 255, 255)');
		expect(hover.insetBlockStart).toBe(rest.insetBlockStart);
		expect(hover.insetInlineStart).toBe(rest.insetInlineStart);
		await expect
			.poll(
				async () =>
					button.evaluate(
						(element) => getComputedStyle(element).boxShadow
					),
				{ timeout: 1000 }
			)
			.toContain('rgb(31, 159, 114)');
		await expect
			.poll(
				async () =>
					button.evaluate(
						(element) =>
							getComputedStyle(element, '::after').backgroundColor
					),
				{ timeout: 1000 }
			)
			.toBe('rgba(0, 0, 0, 0)');

		await outlineButton.hover();
		await page.waitForTimeout(220);
		const outlineHover = await outlineButton.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				backgroundColor: computed.backgroundColor,
				boxShadow: computed.boxShadow,
				color: computed.color,
			};
		});
		expect(outlineHover.backgroundColor).toBe('rgba(0, 0, 0, 0)');
		expect(outlineHover.color).toBe('rgb(61, 32, 126)');
		await expect
			.poll(
				async () =>
					outlineButton.evaluate(
						(element) => getComputedStyle(element).boxShadow
					),
				{ timeout: 1000 }
			)
			.toContain('rgb(31, 159, 114)');

		const disabledPrimaryBox = await disabledPrimaryButton.boundingBox();
		const disabledOutlineBox = await disabledOutlineButton.boundingBox();

		if (!disabledPrimaryBox || !disabledOutlineBox) {
			throw new Error('Expected disabled button geometry.');
		}

		expect(
			Math.abs(disabledOutlineBox.y - disabledPrimaryBox.y)
		).toBeLessThan(0.5);
		expect(
			Math.abs(disabledOutlineBox.height - disabledPrimaryBox.height)
		).toBeLessThan(0.5);

		const disabledOutlineBeforeHover = await disabledOutlineButton.evaluate(
			(element) => {
				const computed = getComputedStyle(element);

				return {
					backgroundColor: computed.backgroundColor,
					boxShadow: computed.boxShadow,
					color: computed.color,
					transform: computed.transform,
				};
			}
		);
		await disabledOutlineButton.hover();
		await page.waitForTimeout(220);
		const disabledOutlineAfterHover = await disabledOutlineButton.evaluate(
			(element) => {
				const computed = getComputedStyle(element);

				return {
					backgroundColor: computed.backgroundColor,
					boxShadow: computed.boxShadow,
					color: computed.color,
					transform: computed.transform,
				};
			}
		);

		expect(disabledOutlineAfterHover).toEqual(disabledOutlineBeforeHover);

		await button.hover();
		const hoverTransform = await button.evaluate(
			(element) => getComputedStyle(element).transform
		);
		await page.mouse.down();
		await page.waitForTimeout(220);
		const active = await button.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				boxShadow: computed.boxShadow,
				transform: computed.transform,
			};
		});
		await page.mouse.up();

		expect(active.boxShadow).toContain('0px 0px 0px 0px');
		expect(active.transform).not.toBe(hoverTransform);
		expect(active.transform).not.toBe('none');

		const disabledBeforeHover = await disabledSubmit.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				boxShadow: computed.boxShadow,
				transform: computed.transform,
			};
		});
		await disabledSubmit.hover();
		await page.waitForTimeout(220);
		const disabledAfterHover = await disabledSubmit.evaluate((element) => {
			const computed = getComputedStyle(element);

			return {
				boxShadow: computed.boxShadow,
				transform: computed.transform,
			};
		});

		expect(disabledAfterHover).toEqual(disabledBeforeHover);

		await entryNavigationAction.hover();
		await page.waitForTimeout(220);
		const clearHover = await entryNavigationAction.evaluate((element) => {
			const computed = getComputedStyle(element);
			const after = getComputedStyle(element, '::after');

			return {
				afterBackgroundColor: after.backgroundColor,
				backgroundColor: computed.backgroundColor,
				boxShadow: computed.boxShadow,
				color: computed.color,
				textDecorationLine: computed.textDecorationLine,
			};
		});

		expect(clearHover.backgroundColor).toBe('rgba(0, 0, 0, 0)');
		expect(clearHover.boxShadow).toContain('0px 0px 0px 0px');
		expect(clearHover.afterBackgroundColor).toBe('rgba(0, 0, 0, 0)');
		expect(clearHover.color).toBe('rgb(22, 22, 22)');
		expect(clearHover.textDecorationLine).toBe('underline');

		await page.evaluate(() => {
			document
				.querySelector('#pns-light-surface-button-fixture')
				?.remove();
			document
				.querySelector('#pns-light-surface-outline-button-fixture')
				?.remove();
			document
				.querySelector(
					'#pns-light-surface-disabled-button-pair-fixture'
				)
				?.remove();
			document
				.querySelector(
					'#pns-light-surface-entry-navigation-action-fixture'
				)
				?.remove();
			document
				.querySelector('#pns-light-surface-submit-fixture')
				?.remove();
		});
	}
);

for (const route of contentRhythmRoutes) {
	test(
		taggedTitle(`content typography rhythm: ${route}`, 'audit', 'layout'),
		async ({ page }) => {
			await page.goto(route);
			await page.waitForLoadState('domcontentloaded');
			await waitForContractReady(page);

			const rhythm = await page.evaluate(() => {
				function read(selector: string) {
					const element = document.querySelector(selector);

					if (!element) {
						return null;
					}

					const computed = getComputedStyle(element);

					return {
						fontFamily: computed.fontFamily,
						fontSize: computed.fontSize,
						fontWeight: computed.fontWeight,
						lineHeight: computed.lineHeight,
						marginBottom: computed.marginBottom,
						paddingLeft: computed.paddingLeft,
						textWrap: computed.getPropertyValue('text-wrap'),
					};
				}

				function readFirstNonEmptyParagraph() {
					const element = Array.from(
						document.querySelectorAll('.entry-content p')
					).find(
						(paragraph) =>
							paragraph.textContent?.trim() &&
							!paragraph.closest(
								'.pns-hero-copy, .pns-split-section__cta, .wp-block-navigation, .wp-block-buttons, .wp-block-search, .emailoctopus-form, .ec-store, .ecwid'
							)
					);

					if (!element) {
						return null;
					}

					const computed = getComputedStyle(element);

					return {
						fontFamily: computed.fontFamily,
						marginBottom: computed.marginBottom,
						textWrap: computed.getPropertyValue('text-wrap'),
					};
				}

				function readFirstContentList() {
					const element = document.querySelector(
						'.entry-content :is(ul, ol):not(.wp-block-navigation__container):not(.wp-block-post-template):not(.wp-block-social-links):not(.wp-block-query-pagination):not(.wp-block-jetpack-slideshow *):not(.ec-store *):not(.ecwid *)'
					);

					if (!element) {
						return null;
					}

					const item = element.querySelector('li');
					const nestedList = element.querySelector('li :is(ul, ol)');
					const computed = getComputedStyle(element);
					const itemComputed = item ? getComputedStyle(item) : null;
					const markerComputed = item
						? getComputedStyle(item, '::marker')
						: null;
					const nestedComputed = nestedList
						? getComputedStyle(nestedList)
						: null;

					return {
						itemColor: itemComputed?.color ?? '',
						lineHeight: computed.lineHeight,
						marginBottom: computed.marginBottom,
						markerColor: markerComputed?.color ?? '',
						markerFontWeight: markerComputed?.fontWeight ?? '',
						nestedMarginTop: nestedComputed?.marginTop ?? '',
						paddingLeft: computed.paddingLeft,
						textWrap:
							itemComputed?.getPropertyValue('text-wrap') ?? '',
					};
				}

				return {
					heading: read('.entry-content .wp-block-heading'),
					list: readFirstContentList(),
					paragraph: readFirstNonEmptyParagraph(),
				};
			});

			annotateAcceptedStateInventory(
				`content typography rhythm ${route}`,
				rhythm
			);
			expect(
				rhythm.heading ?? rhythm.paragraph ?? rhythm.list
			).not.toBeNull();
			expect(rhythm.heading?.fontFamily ?? '').not.toBe('');
			expect(rhythm.paragraph?.fontFamily ?? '').not.toBe('');
			if (rhythm.list) {
				expect(
					Number.parseFloat(rhythm.list.paddingLeft)
				).toBeGreaterThan(20);
				expect(rhythm.list.itemColor).not.toBe('');
			}
		}
	);
}

test(
	taggedTitle('heading stack rhythm keeps content grouped', 'fast', 'layout'),
	async ({ page }) => {
		await page.goto('/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		const styles = await page.evaluate(() => {
			const fixture = document.createElement('div');
			fixture.id = 'pns-heading-rhythm-fixture';
			fixture.className = 'entry-content';
			fixture.innerHTML = `
				<h2 id="pns-heading-rhythm-heading" class="wp-block-heading">Fixture heading</h2>
				<p id="pns-heading-rhythm-copy">Fixture copy.</p>
				<p id="pns-heading-rhythm-prior">Prior paragraph.</p>
				<h3 id="pns-heading-rhythm-next-heading" class="wp-block-heading">Next fixture heading</h3>
				<p id="pns-heading-rhythm-next-copy">Next fixture copy.</p>
				<div class="pns-section">
					<div class="is-layout-flow">
						<h2 id="pns-heading-rhythm-section-heading" class="wp-block-heading">Section heading</h2>
						<p id="pns-heading-rhythm-section-copy">Section copy.</p>
					</div>
				</div>
				<div class="pns-section">
					<div class="pns-content-frame is-layout-flow">
						<h2 id="pns-heading-rhythm-frame-heading" class="wp-block-heading">Content frame heading</h2>
						<p id="pns-heading-rhythm-frame-copy">Content frame copy.</p>
					</div>
				</div>
				<div class="pns-section">
					<div class="pns-content-frame is-layout-flow" style="--wp--style--block-gap: 40px;">
						<h2 id="pns-heading-rhythm-custom-frame-heading" class="wp-block-heading">Custom content frame heading</h2>
						<p id="pns-heading-rhythm-custom-frame-copy">Custom content frame copy.</p>
					</div>
				</div>
			`;
			document.body.appendChild(fixture);

			function readGap(previousSelector: string, nextSelector: string) {
				const previous = document.querySelector(previousSelector);
				const next = document.querySelector(nextSelector);

				if (!previous || !next) {
					return null;
				}

				const previousRect = previous.getBoundingClientRect();
				const nextRect = next.getBoundingClientRect();
				const computed = getComputedStyle(next);

				return {
					marginTop: computed.marginTop,
					visualGap: `${Math.round(
						nextRect.top - previousRect.bottom
					)}px`,
				};
			}

			return {
				followGap: readGap(
					'#pns-heading-rhythm-heading',
					'#pns-heading-rhythm-copy'
				),
				leadGap: readGap(
					'#pns-heading-rhythm-prior',
					'#pns-heading-rhythm-next-heading'
				),
				sectionFollowGap: readGap(
					'#pns-heading-rhythm-section-heading',
					'#pns-heading-rhythm-section-copy'
				),
				contentFrameFollowGap: readGap(
					'#pns-heading-rhythm-frame-heading',
					'#pns-heading-rhythm-frame-copy'
				),
				customContentFrameFollowGap: readGap(
					'#pns-heading-rhythm-custom-frame-heading',
					'#pns-heading-rhythm-custom-frame-copy'
				),
			};
		});

		expect(styles.followGap?.marginTop).toBe('10px');
		expect(styles.followGap?.visualGap).toBe('10px');
		expect(styles.leadGap?.marginTop).toBe('32px');
		expect(styles.leadGap?.visualGap).toBe('32px');
		expect(styles.sectionFollowGap?.marginTop).toBe('10px');
		expect(styles.sectionFollowGap?.visualGap).toBe('10px');
		expect(styles.contentFrameFollowGap?.marginTop).toBe('10px');
		expect(styles.contentFrameFollowGap?.visualGap).toBe('10px');
		expect(styles.customContentFrameFollowGap?.marginTop).toBe('40px');
		expect(styles.customContentFrameFollowGap?.visualGap).toBe('40px');
	}
);

for (const route of savedBackgroundSectionRoutes) {
	test(
		taggedTitle(
			`saved background groups use semantic section hooks: ${route}`,
			'audit',
			'pattern'
		),
		async ({ page }) => {
			await page.goto(route);
			await page.waitForLoadState('domcontentloaded');
			await waitForContractReady(page);

			const backgroundGroups = await page.evaluate(() =>
				Array.from(
					document.querySelectorAll<HTMLElement>(
						'.wp-block-group.has-background'
					)
				).map((element) => {
					const computed = getComputedStyle(element);
					const padding = [
						computed.paddingTop,
						computed.paddingRight,
						computed.paddingBottom,
						computed.paddingLeft,
					];

					return {
						className: element.className,
						hasPnsHook: Array.from(element.classList).some(
							(className) => className.startsWith('pns-')
						),
						padding,
						text: element.textContent
							?.replace(/\s+/g, ' ')
							.trim()
							.slice(0, 80),
					};
				})
			);

			expect(backgroundGroups.length).toBeGreaterThan(0);
			annotateAcceptedStateInventory(
				`saved background groups ${route}`,
				backgroundGroups.filter(
					(group) =>
						!group.hasPnsHook ||
						group.padding.some((value) => value !== '0px')
				)
			);
		}
	);
}

test(
	taggedTitle(
		'PNS section theme inverts copy and CTA buttons on dark backgrounds',
		'fast',
		'pattern'
	),
	async ({ page }) => {
		await page.goto('/pns-pattern-qa/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		const styles = await page.evaluate(() => {
			const section =
				document.querySelector<HTMLElement>('.pns-split-section');
			const heading =
				section?.querySelector<HTMLElement>('.wp-block-heading');
			const paragraph = Array.from(
				section?.querySelectorAll<HTMLElement>('p') ?? []
			).find(
				(element) =>
					!element.classList.contains('pns-split-section__cta')
			);
			const button = section?.querySelector<HTMLElement>(
				'.wp-block-button__link'
			);

			if (!section || !heading || !paragraph || !button) {
				return null;
			}

			const read = (element: HTMLElement) => {
				const computed = getComputedStyle(element);

				return {
					animationName: computed.animationName,
					backgroundColor: computed.backgroundColor,
					boxShadow: computed.boxShadow,
					color: computed.color,
				};
			};

			const darkBackgroundClasses = [
				'has-brand-purple-background-color',
				'has-deep-purple-background-color',
				'has-neutral-800-background-color',
			];

			section.classList.remove(
				...darkBackgroundClasses,
				'has-neutral-950-color',
				'has-text-color'
			);
			section.classList.add(
				'has-neutral-0-background-color',
				'has-background'
			);
			heading.classList.remove('has-neutral-950-color', 'has-text-color');

			const light = {
				button: read(button),
				heading: read(heading),
				paragraph: read(paragraph),
			};

			section.classList.remove('has-neutral-0-background-color');
			section.classList.add(
				'has-brand-purple-background-color',
				'has-background'
			);
			heading.classList.remove('has-neutral-950-color', 'has-text-color');

			const dark = {
				button: read(button),
				heading: read(heading),
				paragraph: read(paragraph),
			};

			section.classList.add('has-neutral-950-color', 'has-text-color');
			heading.classList.add('has-brand-red-color', 'has-text-color');

			const explicit = {
				heading: read(heading),
				paragraph: read(paragraph),
			};

			const outsideButton = document.createElement('a');
			outsideButton.className = 'wp-block-button__link wp-element-button';
			outsideButton.textContent = 'Outside section';
			document.body.append(outsideButton);

			const outside = read(outsideButton);
			outsideButton.remove();

			return { dark, explicit, light, outside };
		});

		expect(styles).not.toBeNull();

		if (!styles) {
			return;
		}

		expect(styles.light.heading.color).toBe('rgb(22, 22, 22)');
		expect(styles.light.paragraph.color).toBe('rgb(79, 77, 73)');
		expect(styles.light.button.backgroundColor).toBe('rgb(61, 32, 126)');
		expect(styles.light.button.color).toBe('rgb(255, 255, 255)');
		expect(styles.light.button.boxShadow).toContain('rgb(123, 220, 181)');

		annotateAcceptedStateInventory('section theme dark background styles', {
			dark: styles.dark,
			explicit: styles.explicit,
			light: styles.light,
		});
		expect(styles.dark.heading.color).not.toBe('');
		expect(styles.dark.paragraph.color).not.toBe('');
		expect(styles.dark.button.backgroundColor).toBe('rgb(255, 255, 255)');
		expect(styles.dark.button.color).toBe('rgb(61, 32, 126)');
		expect(styles.dark.button.boxShadow).toContain('rgb(123, 220, 181)');

		expect(styles.explicit.heading.color).toBe('rgb(212, 0, 15)');
		expect(styles.explicit.paragraph.color).toBe('rgb(22, 22, 22)');

		expect(styles.outside.backgroundColor).toBe('rgb(61, 32, 126)');
		expect(styles.outside.color).toBe('rgb(255, 255, 255)');
	}
);

test(
	taggedTitle(
		'homepage welcome hero retains its brand-purple dark surface',
		'fast',
		'layout'
	),
	async ({ page }) => {
		await page.goto('/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		const hero = page.locator('.pns-welcome-header');
		await expect(hero).toHaveClass(/\bpns-dark-surface\b/);
		await expect(hero).toHaveClass(/\bhas-brand-purple-background-color\b/);
		await expect(hero).not.toHaveClass(/\bis-light\b/);
		await expect(hero).toHaveCSS('background-color', 'rgb(61, 32, 126)');
		await expect(hero.locator('.wp-block-heading').first()).toHaveCSS(
			'color',
			'rgb(255, 255, 255)'
		);
	}
);

test(
	taggedTitle(
		'explicit dark surfaces keep inverse copy inside light templates',
		'fast',
		'pattern'
	),
	async ({ page }) => {
		await page.goto('/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		const styles = await page.evaluate(() => {
			const connect = document.querySelector<HTMLElement>(
				'.pns-connect-social'
			);
			const fixture = document.createElement('section');
			fixture.className =
				'pns-section pns-dark-surface has-brand-purple-background-color has-background';
			fixture.innerHTML = [
				'<h2 class="wp-block-heading">Automatic dark heading</h2>',
				'<p>Automatic dark copy</p>',
				'<p class="has-brand-red-color has-text-color">Editor-set red copy</p>',
			].join('');
			document.querySelector('main.pns-light-surface')?.append(fixture);

			const read = (element: Element | null) => {
				if (!(element instanceof HTMLElement)) {
					return null;
				}

				const computed = getComputedStyle(element);
				return {
					className: element.className,
					color: computed.color,
					surfaceHeading: computed
						.getPropertyValue('--pns-surface-heading')
						.trim(),
					surfaceText: computed
						.getPropertyValue('--pns-surface-text')
						.trim(),
				};
			};

			const result = {
				connect: read(connect),
				connectHeading: read(
					connect?.querySelector('.wp-block-heading') ?? null
				),
				connectParagraph: read(connect?.querySelector('p') ?? null),
				fixture: read(fixture),
				fixtureExplicit: read(fixture.querySelector('.has-text-color')),
				fixtureHeading: read(
					fixture.querySelector('.wp-block-heading')
				),
				fixtureParagraph: read(fixture.querySelector('p')),
			};

			fixture.remove();
			return result;
		});

		expect(styles.connect?.className).toContain('pns-dark-surface');
		expect(styles.connectHeading?.color).toBe('rgb(255, 255, 255)');
		expect(styles.connectParagraph?.color).toBe('rgb(255, 255, 255)');
		expect(styles.fixture?.surfaceText).toBeTruthy();
		expect(styles.fixture?.surfaceHeading).toBeTruthy();
		expect(styles.fixtureHeading?.color).toBe('rgb(255, 255, 255)');
		expect(styles.fixtureParagraph?.color).toBe('rgb(255, 255, 255)');
		expect(styles.fixtureExplicit?.color).toBe('rgb(212, 0, 15)');
	}
);

test(
	taggedTitle(
		'caption element defaults stay core-compatible until PNS caption design exists',
		'audit',
		'template'
	),
	async ({ page }) => {
		await page.goto('/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		const captions = await page.evaluate(() => {
			const fixture = document.createElement('figcaption');
			fixture.className = 'wp-element-caption';
			fixture.textContent = 'Caption style contract fixture';
			document.body.append(fixture);

			return Array.from(
				document.querySelectorAll<HTMLElement>('.wp-element-caption')
			).map((element) => {
				const computed = getComputedStyle(element);

				return {
					color: computed.color,
					fontFamily: computed.fontFamily,
					fontSize: computed.fontSize,
					fontWeight: computed.fontWeight,
					textAlign: computed.textAlign,
					text: element.textContent?.trim(),
				};
			});
		});

		expect(captions.length).toBeGreaterThan(0);
		expect(captions[0]?.color).toBe('rgb(85, 85, 85)');
		expect(captions[0]?.fontFamily).toContain('Libre Franklin');
		expect(captions[0]?.fontSize).toBe('14px');
		expect(captions[0]?.fontWeight).toBe('400');
		expect(captions[0]?.textAlign).toBe('start');
	}
);

test(
	taggedTitle(
		'navigation controls expose only the supported typography color and gap surfaces',
		'fast',
		'navigation',
		'pattern'
	),
	async ({ page }) => {
		await page.goto('/pns-pattern-qa/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		const styles = await page.evaluate(() => {
			const nav = document.createElement('nav');
			nav.className =
				'wp-block-navigation is-layout-flex has-brand-red-color has-text-color';
			nav.setAttribute(
				'style',
				'font-size: 23px; --pns--navigation--gap: 31px;'
			);

			const list = document.createElement('ul');
			list.className = 'wp-block-navigation__container';

			const item = document.createElement('li');
			item.className = 'wp-block-navigation-item';

			const link = document.createElement('a');
			link.className = 'wp-block-navigation-item__content';
			link.textContent = 'Supported nav control';

			item.append(link);
			list.append(item);
			nav.append(list);
			document.body.append(nav);

			const navStyles = getComputedStyle(nav);
			const listStyles = getComputedStyle(list);
			const itemStyles = getComputedStyle(item);
			const linkStyles = getComputedStyle(link);
			const controlled = {
				color: linkStyles.color,
				fontSize: linkStyles.fontSize,
				gap: navStyles.gap,
				listGap: listStyles.gap,
				listPaddingLeft: listStyles.paddingLeft,
				listStyleType: itemStyles.listStyleType,
				linkTextWrap: linkStyles.getPropertyValue('text-wrap'),
			};

			nav.classList.add('has-text-lead-font-size');
			nav.removeAttribute('style');

			const largePreset = getComputedStyle(link).fontSize;

			nav.remove();

			return {
				...controlled,
				largePreset,
			};
		});

		expect(styles.color).toBe('rgb(212, 0, 15)');
		expect(styles.fontSize).toBe('23px');
		expect(styles.gap).toBe('31px');
		expect(styles.listGap).toBe('31px');
		expect(styles.listPaddingLeft).toBe('0px');
		expect(styles.listStyleType).toBe('none');
		annotateAcceptedStateInventory(
			'navigation control typography surfaces',
			styles
		);
		expect(styles.linkTextWrap).not.toBe('');
		expect(Number.parseFloat(styles.largePreset)).toBeGreaterThan(0);
	}
);

test(
	taggedTitle(
		'core selector ownership fixtures keep compatibility and component scopes distinct',
		'fast',
		'layout',
		'navigation',
		'pattern'
	),
	async ({ page }) => {
		await page.goto('/pns-pattern-qa/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		await page.evaluate(() => {
			const fixture = document.createElement('section');
			fixture.id = 'pns-selector-ownership-fixture';
			fixture.className = 'entry-content';
			fixture.innerHTML = `
				<p id="pns-selector-large" class="has-title-large-font-size">Large preset fallback</p>
				<p id="pns-selector-x-large" class="has-title-display-font-size">Extra large preset fallback</p>
				<p id="pns-selector-medium" class="has-text-lead-font-size">Medium preset fallback</p>
				<div id="pns-selector-alignwide" class="alignwide">Wide compatibility block</div>
				<div id="pns-selector-generated-flow" class="is-layout-flow">
					<p>Generated first child</p>
					<p id="pns-selector-generated-flow-second">Generated second child</p>
				</div>
				<ul id="pns-selector-content-list">
					<li>First content item</li>
					<li id="pns-selector-content-list-second">Second content item</li>
				</ul>
				<nav id="pns-selector-generic-nav" class="wp-block-navigation is-layout-flex">
					<ul class="wp-block-navigation__container">
						<li class="wp-block-navigation-item wp-block-navigation-submenu has-child">
							<a class="wp-block-navigation-item__content" href="#">Generic parent</a>
							<button class="wp-block-navigation-item__content wp-block-navigation-submenu__toggle" type="button">
								<span class="wp-block-navigation__submenu-icon"><svg viewBox="0 0 10 10"><path d="M1 3l4 4 4-4"></path></svg></span>
							</button>
							<ul class="wp-block-navigation__submenu-container">
								<li class="wp-block-navigation-item"><a class="wp-block-navigation-item__content" href="#">Generic child</a></li>
							</ul>
						</li>
					</ul>
				</nav>
				<nav id="pns-selector-primary-nav" class="wp-block-navigation pns-primary-navigation is-layout-flex">
					<ul class="wp-block-navigation__container">
						<li class="wp-block-navigation-item wp-block-navigation-submenu has-child">
							<a class="wp-block-navigation-item__content" href="#">Primary parent</a>
							<button class="wp-block-navigation-item__content wp-block-navigation-submenu__toggle" type="button">
								<span class="wp-block-navigation__submenu-icon"><svg viewBox="0 0 10 10"><path d="M1 3l4 4 4-4"></path></svg></span>
							</button>
							<ul class="wp-block-navigation__submenu-container">
								<li class="wp-block-navigation-item"><a class="wp-block-navigation-item__content" href="#">Primary child</a></li>
							</ul>
						</li>
					</ul>
				</nav>
				<div id="pns-selector-vendor-list" class="emailoctopus-form">
					<ul><li>Vendor item</li><li id="pns-selector-vendor-list-second">Vendor second item</li></ul>
				</div>
				<blockquote id="pns-selector-quote" class="wp-block-quote"><p>Quote paragraph owner</p></blockquote>
				<div id="pns-selector-light-surface" class="pns-section pns-light-surface">
					<h2 class="wp-block-heading">Light heading</h2>
					<p>Light paragraph</p>
					<a href="#">Light link</a>
					<hr class="wp-block-separator">
					<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#">Light button</a></div>
				</div>
			`;
			document.body.append(fixture);
		});

		function readSelectorContracts() {
			return page.evaluate(() => {
				function read(selector: string, pseudo?: string) {
					const element = document.querySelector(selector);

					if (!element) {
						return null;
					}

					const computed = getComputedStyle(element, pseudo);
					const bounds = element.getBoundingClientRect();

					return {
						backgroundColor: computed.backgroundColor,
						borderColor: computed.borderColor,
						borderRightColor: computed.borderRightColor,
						borderRightWidth: computed.borderRightWidth,
						color: computed.color,
						content: computed.content,
						fontFamily: computed.fontFamily,
						fontSize: computed.fontSize,
						fontWeight: computed.fontWeight,
						lineHeight: computed.lineHeight,
						marginTop: computed.marginTop,
						paddingLeft: computed.paddingLeft,
						paddingRight: computed.paddingRight,
						stroke: computed.stroke,
						textWrap: computed.getPropertyValue('text-wrap'),
						width: bounds.width,
					};
				}

				return {
					alignwide: read('#pns-selector-alignwide'),
					contentList: read('#pns-selector-content-list'),
					contentListSecond: read(
						'#pns-selector-content-list-second'
					),
					contentListMarker: read(
						'#pns-selector-content-list-second',
						'::marker'
					),
					generatedFlowSecond: read(
						'#pns-selector-generated-flow-second'
					),
					genericNavItem: read(
						'#pns-selector-generic-nav .wp-block-navigation__submenu-container .wp-block-navigation-item'
					),
					genericNavLink: read(
						'#pns-selector-generic-nav .wp-block-navigation__submenu-container a'
					),
					genericNavList: read(
						'#pns-selector-generic-nav .wp-block-navigation__container'
					),
					genericNavSubmenuIcon: read(
						'#pns-selector-generic-nav .wp-block-navigation__submenu-icon svg'
					),
					large: read('#pns-selector-large'),
					lightButton: read(
						'#pns-selector-light-surface .wp-block-button__link'
					),
					lightHeading: read(
						'#pns-selector-light-surface .wp-block-heading'
					),
					lightLink: read('#pns-selector-light-surface a'),
					lightParagraph: read('#pns-selector-light-surface p'),
					lightSeparator: read(
						'#pns-selector-light-surface .wp-block-separator'
					),
					lightSurface: read('#pns-selector-light-surface'),
					medium: read('#pns-selector-medium'),
					primaryNavItem: read(
						'#pns-selector-primary-nav .wp-block-navigation__submenu-container .wp-block-navigation-item'
					),
					primaryNavLink: read(
						'#pns-selector-primary-nav .wp-block-navigation__submenu-container a'
					),
					primaryNavSubmenuIcon: read(
						'#pns-selector-primary-nav .wp-block-navigation__submenu-icon svg'
					),
					quoteText: read('#pns-selector-quote p'),
					vendorList: read('#pns-selector-vendor-list ul'),
					vendorListSecond: read('#pns-selector-vendor-list-second'),
					xLarge: read('#pns-selector-x-large'),
				};
			});
		}

		const styles = await readSelectorContracts();
		const viewportWidth = page.viewportSize()?.width ?? 0;
		const expectedAlignwideWidth =
			viewportWidth > 1280 ? viewportWidth / 2 + 640 : viewportWidth;

		const largeFontSize = Number.parseFloat(styles.large?.fontSize ?? '0');
		const largeLineHeight = Number.parseFloat(
			styles.large?.lineHeight ?? '0'
		);
		const xLargeFontSize = Number.parseFloat(
			styles.xLarge?.fontSize ?? '0'
		);
		const xLargeLineHeight = Number.parseFloat(
			styles.xLarge?.lineHeight ?? '0'
		);
		const mediumFontSize = Number.parseFloat(
			styles.medium?.fontSize ?? '0'
		);

		expect(largeFontSize).toBeGreaterThanOrEqual(36);
		expect(largeFontSize).toBeLessThanOrEqual(46);
		expect(largeLineHeight).toBeGreaterThan(largeFontSize);
		expect(largeLineHeight).toBeLessThan(largeFontSize * 1.25);
		expect(xLargeFontSize).toBeGreaterThanOrEqual(44);
		expect(xLargeFontSize).toBeLessThanOrEqual(56);
		expect(xLargeLineHeight).toBeGreaterThan(xLargeFontSize);
		expect(xLargeLineHeight).toBeLessThan(xLargeFontSize * 1.08);
		expect(mediumFontSize).toBeGreaterThanOrEqual(17.75);
		expect(mediumFontSize).toBeLessThanOrEqual(20);
		expect(
			Number.parseFloat(styles.generatedFlowSecond?.marginTop ?? '0')
		).toBeGreaterThan(0);
		expect(
			Number.parseFloat(styles.contentList?.paddingLeft ?? '0')
		).toBeGreaterThan(20);
		expect(styles.contentList?.textWrap).toBe('pretty');
		expect(
			Number.parseFloat(styles.contentListSecond?.marginTop ?? '0')
		).toBeGreaterThan(0);
		expect(styles.contentListMarker?.fontWeight).toBe('700');
		expect(styles.genericNavList?.paddingLeft).toBe('0px');
		expect(styles.vendorList?.paddingLeft).toBe('0px');
		expect(styles.vendorListSecond?.marginTop).toBe('0px');
		expect(Number.parseFloat(styles.alignwide?.width ?? '0')).toBeCloseTo(
			expectedAlignwideWidth,
			0
		);
		expect(styles.quoteText?.fontFamily).toContain('Rubik');
		expect(styles.quoteText?.fontWeight).toBe('800');
		expect(
			Number.parseFloat(styles.quoteText?.lineHeight ?? '0')
		).toBeGreaterThan(Number.parseFloat(styles.quoteText?.fontSize ?? '0'));
		annotateAcceptedStateInventory('core selector ownership styles', {
			genericNavItem: styles.genericNavItem,
			genericNavLink: styles.genericNavLink,
			genericNavSubmenuIcon: styles.genericNavSubmenuIcon,
			lightButton: styles.lightButton,
			lightHeading: styles.lightHeading,
			lightParagraph: styles.lightParagraph,
			lightSeparator: styles.lightSeparator,
			lightSurface: styles.lightSurface,
			primaryNavItem: styles.primaryNavItem,
			primaryNavLink: styles.primaryNavLink,
			primaryNavSubmenuIcon: styles.primaryNavSubmenuIcon,
		});
		expect(styles.lightSurface?.backgroundColor).not.toBe('');
		expect(styles.lightParagraph?.color).not.toBe('');
		expect(styles.lightHeading?.color).not.toBe('');
		expect(styles.lightLink?.color).not.toBe('');
		expect(styles.lightSeparator?.borderColor).not.toBe('');
		expect(styles.lightButton?.backgroundColor).not.toBe('');
		expect(styles.lightButton?.color).not.toBe('');
		expect(styles.genericNavItem?.borderRightWidth).toBeTruthy();
		expect(styles.genericNavLink?.paddingRight).toBeTruthy();
		expect(styles.primaryNavItem?.borderRightWidth).toBeTruthy();
		expect(styles.primaryNavLink?.paddingRight).toBeTruthy();

		await page
			.locator(
				'#pns-selector-generic-nav .wp-block-navigation-item > a.wp-block-navigation-item__content'
			)
			.first()
			.hover({ force: true });
		const genericHover = await page.evaluate(() => {
			const element = document.querySelector(
				'#pns-selector-generic-nav .wp-block-navigation-item > a.wp-block-navigation-item__content'
			);

			return element
				? getComputedStyle(element, '::after').content
				: null;
		});

		expect(genericHover).toBe('none');

		await page
			.locator(
				'#pns-selector-primary-nav .wp-block-navigation-item > a.wp-block-navigation-item__content'
			)
			.first()
			.hover({ force: true });
		const primaryHover = await page.evaluate(() => {
			const element = document.querySelector(
				'#pns-selector-primary-nav .wp-block-navigation-item > a.wp-block-navigation-item__content'
			);

			return element
				? getComputedStyle(element, '::after').content
				: null;
		});

		expect(primaryHover).toBe(viewportWidth >= 782 ? '""' : 'none');
	}
);

test(
	taggedTitle(
		'split-section layout variants move copy and media columns',
		'fast',
		'mobile-fast',
		'mobile-full',
		'layout',
		'mobile-layout',
		'pattern'
	),
	async ({ page }) => {
		await page.goto('/pns-pattern-qa/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		const measurements = [];

		for (const width of [390, 768, 800, 900, 960, 1054, 1280]) {
			await page.setViewportSize({ width, height: 1000 });

			const variants = await page.evaluate(() => {
				const section =
					document.querySelector<HTMLElement>('.pns-split-section');

				if (!section) {
					return null;
				}

				const variantNames = [
					'media-left',
					'media-right',
					'edge-media-left',
					'edge-media-right',
				];
				const variantClasses = variantNames.map(
					(variant) => `is-style-pns-${variant}`
				);
				const rect = (element: HTMLElement) => {
					const bounds = element.getBoundingClientRect();

					return {
						bottom: Math.round(bounds.bottom),
						height: Math.round(bounds.height),
						left: Math.round(bounds.left),
						right: Math.round(bounds.right),
						top: Math.round(bounds.top),
						width: Math.round(bounds.width),
					};
				};

				return variantNames.map((variant) => {
					section.classList.remove(...variantClasses);
					section.classList.add(`is-style-pns-${variant}`);

					const copyColumn = section.querySelector<HTMLElement>(
						'.pns-split-section__copy-column'
					);
					const copy = section.querySelector<HTMLElement>(
						'.pns-split-section__copy'
					);
					const heading = section.querySelector<HTMLElement>(
						'.pns-split-section__copy .wp-block-heading'
					);
					const media = section.querySelector<HTMLElement>(
						'.pns-split-section__media-column'
					);
					const mediaImage = media?.querySelector<HTMLElement>('img');
					const columns = section.querySelector<HTMLElement>(
						'.pns-split-section__columns'
					);

					if (
						!copyColumn ||
						!copy ||
						!heading ||
						!media ||
						!mediaImage ||
						!columns
					) {
						return null;
					}

					const copyStyles = getComputedStyle(copy);
					const copyColumnRect = copyColumn.getBoundingClientRect();
					const mediaRect = media.getBoundingClientRect();
					const columnsRect = columns.getBoundingClientRect();

					return {
						columnsDisplay: getComputedStyle(columns).display,
						columns: rect(columns),
						columnsLeft: Math.round(columnsRect.left),
						columnsRight: Math.round(columnsRect.right),
						copy: rect(copy),
						copyColumn: rect(copyColumn),
						copyBeforeMedia:
							Math.round(copyColumnRect.left) ===
							Math.round(mediaRect.left)
								? copyColumnRect.top < mediaRect.top
								: copyColumnRect.left < mediaRect.left,
						heading: rect(heading),
						media: rect(media),
						mediaImage: rect(mediaImage),
						mediaObjectFit: getComputedStyle(mediaImage).objectFit,
						padding: {
							bottom: Math.round(
								parseFloat(copyStyles.paddingBottom)
							),
							left: Math.round(
								parseFloat(copyStyles.paddingLeft)
							),
							right: Math.round(
								parseFloat(copyStyles.paddingRight)
							),
							top: Math.round(parseFloat(copyStyles.paddingTop)),
						},
						variant,
					};
				});
			});

			expect(variants).not.toBeNull();

			if (variants) {
				measurements.push({ width, variants });
			}
		}

		expect(measurements).toHaveLength(7);

		for (const { width, variants } of measurements) {
			expect(variants.filter(Boolean)).toHaveLength(4);
			expect(variants).toEqual(
				expect.arrayContaining([
					expect.objectContaining({
						copyBeforeMedia: false,
						variant: 'media-left',
					}),
					expect.objectContaining({
						copyBeforeMedia: true,
						variant: 'media-right',
					}),
					expect.objectContaining({
						columnsDisplay: 'grid',
						copyBeforeMedia: false,
						variant: 'edge-media-left',
					}),
					expect.objectContaining({
						columnsDisplay: 'grid',
						copyBeforeMedia: true,
						variant: 'edge-media-right',
					}),
				])
			);
			const expectedPadding =
				variants.find((variant) => variant?.variant === 'media-right')
					?.padding.left ?? 0;

			expect(expectedPadding).toBeGreaterThan(0);

			for (const variant of variants) {
				expect(variant?.columnsDisplay).toBe('grid');
				expect(variant?.columnsLeft).toBeGreaterThanOrEqual(-1);
				expect(variant?.columnsRight).toBeLessThanOrEqual(width + 1);
				expect(variant?.padding.top).toBe(expectedPadding);
				expect(variant?.padding.bottom).toBe(expectedPadding);
				expect(variant?.heading.top ?? 0).toBeGreaterThanOrEqual(
					(variant?.copy.top ?? 0) + expectedPadding - 1
				);
				expect(variant?.mediaObjectFit).toBe('cover');
				expect(variant?.mediaImage.left ?? 0).toBeGreaterThanOrEqual(
					(variant?.media.left ?? 0) - 1
				);
				expect(variant?.mediaImage.left ?? 0).toBeLessThanOrEqual(
					(variant?.media.left ?? 0) + 1
				);
				expect(variant?.mediaImage.right ?? 0).toBeLessThanOrEqual(
					(variant?.media.right ?? 0) + 1
				);
				expect(variant?.mediaImage.right ?? 0).toBeGreaterThanOrEqual(
					(variant?.media.right ?? 0) - 1
				);
				expect(variant?.mediaImage.top ?? 0).toBeGreaterThanOrEqual(
					(variant?.media.top ?? 0) - 1
				);
				expect(variant?.mediaImage.top ?? 0).toBeLessThanOrEqual(
					(variant?.media.top ?? 0) + 1
				);
				expect(variant?.mediaImage.bottom ?? 0).toBeLessThanOrEqual(
					(variant?.media.bottom ?? 0) + 1
				);
				expect(variant?.mediaImage.bottom ?? 0).toBeGreaterThanOrEqual(
					(variant?.media.bottom ?? 0) - 1
				);
			}

			const mediaRight = variants.find(
				(variant) => variant?.variant === 'media-right'
			);
			const mediaLeft = variants.find(
				(variant) => variant?.variant === 'media-left'
			);
			const edgeMediaRight = variants.find(
				(variant) => variant?.variant === 'edge-media-right'
			);
			const edgeMediaLeft = variants.find(
				(variant) => variant?.variant === 'edge-media-left'
			);
			for (const variant of [mediaLeft, mediaRight]) {
				expect(variant?.padding.left).toBe(expectedPadding);
				expect(variant?.padding.right).toBe(expectedPadding);
			}

			if (width < 960) {
				for (const variant of [edgeMediaLeft, edgeMediaRight]) {
					expect(variant?.padding.left).toBe(expectedPadding);
					expect(variant?.padding.right).toBe(expectedPadding);
					expect(variant?.media.left ?? 0).toBeLessThanOrEqual(1);
					expect(variant?.media.right ?? 0).toBeGreaterThanOrEqual(
						width - 1
					);
					expect(variant?.media.width ?? 0).toBeGreaterThanOrEqual(
						width - 1
					);
				}
			} else {
				expect(edgeMediaRight?.padding.left).toBe(expectedPadding);
				expect(edgeMediaRight?.padding.right).toBe(expectedPadding);
				expect(edgeMediaLeft?.padding.left).toBe(expectedPadding);
				expect(edgeMediaLeft?.padding.right).toBe(expectedPadding);
				expect(edgeMediaRight?.media.right ?? 0).toBeGreaterThanOrEqual(
					width - 1
				);
				expect(edgeMediaLeft?.media.left ?? 0).toBeLessThanOrEqual(1);
				expect(edgeMediaRight?.media.width).toBeCloseTo(
					edgeMediaRight?.copyColumn.width ?? 0,
					0
				);
				expect(edgeMediaRight?.media.top).toBeCloseTo(
					edgeMediaRight?.copyColumn.top ?? 0,
					0
				);
				expect(edgeMediaLeft?.media.width).toBeCloseTo(
					edgeMediaLeft?.copyColumn.width ?? 0,
					0
				);
				expect(edgeMediaLeft?.media.top).toBeCloseTo(
					edgeMediaLeft?.copyColumn.top ?? 0,
					0
				);

				for (const variant of variants) {
					expect(variant?.media.top ?? 0).toBeGreaterThanOrEqual(
						(variant?.columns.top ?? 0) - 1
					);
					expect(variant?.media.bottom ?? 0).toBeLessThanOrEqual(
						(variant?.columns.bottom ?? 0) + 1
					);
					expect(
						variant?.mediaImage.height ?? 0
					).toBeGreaterThanOrEqual((variant?.media.height ?? 0) - 1);
				}
			}
		}
	}
);

test(
	taggedTitle(
		'layout width contract maps helpers to content wide and site frames',
		'fast',
		'mobile-fast',
		'mobile-full',
		'layout',
		'mobile-layout',
		'pattern'
	),
	async ({ page }) => {
		await page.goto('/pns-pattern-qa/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		const contract = await page.evaluate(() => {
			const fixture = document.createElement('div');
			fixture.style.boxSizing = 'border-box';
			fixture.style.inlineSize = '2000px';
			fixture.style.position = 'absolute';
			fixture.style.visibility = 'hidden';
			fixture.style.insetInlineStart = '0';
			fixture.style.insetBlockStart = '0';

			const addProbe = (className: string) => {
				const element = document.createElement('div');
				element.className = className;
				element.style.blockSize = '1px';
				fixture.append(element);

				return element;
			};

			const addInlineSizeProbe = (inlineSize: string) => {
				const element = document.createElement('div');
				element.style.boxSizing = 'border-box';
				element.style.blockSize = '1px';
				element.style.inlineSize = inlineSize;
				fixture.append(element);

				return element;
			};

			const pnsContentProbe = addInlineSizeProbe(
				'var(--pns--layout--content-size)'
			);
			const wpContentProbe = addInlineSizeProbe(
				'var(--wp--style--global--content-size, 44rem)'
			);
			const pnsWideProbe = addInlineSizeProbe(
				'var(--pns--layout--wide-size)'
			);
			const wpWideProbe = addInlineSizeProbe(
				'var(--wp--style--global--wide-size)'
			);
			const sectionFrameProbe = addInlineSizeProbe(
				'var(--pns--layout--section-frame-size)'
			);

			const sectionInner = addProbe('pns-section-inner');
			const sectionFrame = addProbe('pns-section-frame');
			const contentFrame = addProbe('pns-content-frame');
			const copyColumn = addProbe('pns-copy-column');

			document.body.append(fixture);

			const rootStyles = getComputedStyle(document.documentElement);
			const read = (element: HTMLElement) => {
				const computed = getComputedStyle(element);
				const rect = element.getBoundingClientRect();

				return {
					maxInlineSize: computed.maxInlineSize,
					width: rect.width,
				};
			};

			const values = {
				aliases: {
					contentSize: rootStyles
						.getPropertyValue('--pns--layout--content-size')
						.trim(),
					sectionFrameSize: rootStyles
						.getPropertyValue('--pns--layout--section-frame-size')
						.trim(),
					wideSize: rootStyles
						.getPropertyValue('--pns--layout--wide-size')
						.trim(),
				},
				contentFrame: read(contentFrame),
				copyColumn: read(copyColumn),
				pnsContentProbe: read(pnsContentProbe),
				pnsWideProbe: read(pnsWideProbe),
				sectionFrame: read(sectionFrame),
				sectionInner: read(sectionInner),
				sectionFrameProbe: read(sectionFrameProbe),
				wpContentProbe: read(wpContentProbe),
				wpWideProbe: read(wpWideProbe),
			};

			fixture.remove();

			const section =
				document.querySelector<HTMLElement>('.pns-split-section');

			if (!section) {
				return { ...values, splitSection: null };
			}

			const variantClasses = [
				'is-style-pns-media-left',
				'is-style-pns-media-right',
				'is-style-pns-edge-media-left',
				'is-style-pns-edge-media-right',
			];

			const rect = (element?: HTMLElement | null) => {
				if (!element) {
					return null;
				}

				const bounds = element.getBoundingClientRect();

				return {
					left: bounds.left,
					right: bounds.right,
					width: bounds.width,
				};
			};

			const measureSplitVariant = (variant: string) => {
				section.classList.remove(...variantClasses);
				section.classList.add(`is-style-pns-${variant}`);

				const columns = section.querySelector<HTMLElement>(
					'.pns-split-section__columns'
				);
				const copyColumn = section.querySelector<HTMLElement>(
					'.pns-split-section__copy-column'
				);
				const copy = section.querySelector<HTMLElement>(
					'.pns-split-section__copy'
				);
				const heading = section.querySelector<HTMLElement>(
					'.pns-split-section__copy .wp-block-heading'
				);
				const media = section.querySelector<HTMLElement>(
					'.pns-split-section__media-column'
				);
				const columnsStyles = columns
					? getComputedStyle(columns)
					: null;

				return {
					columns: rect(columns),
					columnGap: columnsStyles?.columnGap ?? null,
					copy: rect(copy),
					copyColumn: rect(copyColumn),
					gridTemplateColumns:
						columnsStyles?.gridTemplateColumns ?? null,
					heading: rect(heading),
					media: rect(media),
				};
			};

			return {
				...values,
				splitSection: {
					edgeMediaLeft: measureSplitVariant('edge-media-left'),
					edgeMediaRight: measureSplitVariant('edge-media-right'),
					mediaLeft: measureSplitVariant('media-left'),
					mediaRight: measureSplitVariant('media-right'),
					viewportWidth: window.innerWidth,
				},
			};
		});

		expect(contract.aliases.contentSize).toBeTruthy();
		expect(contract.aliases.wideSize).toBeTruthy();
		expect(contract.aliases.sectionFrameSize).toBeTruthy();

		const contentWidth = contract.pnsContentProbe.width;
		const sectionFrameWidth = contract.sectionFrameProbe.width;

		expect(contentWidth).toBeGreaterThan(0);
		expect(contract.pnsContentProbe.width).toBeGreaterThanOrEqual(
			contract.wpContentProbe.width - 1
		);
		expect(contract.pnsContentProbe.width).toBeLessThanOrEqual(
			contract.wpContentProbe.width + 1
		);
		expect(contract.pnsWideProbe.width).toBeGreaterThanOrEqual(
			contract.wpWideProbe.width - 1
		);
		expect(contract.pnsWideProbe.width).toBeLessThanOrEqual(
			contract.wpWideProbe.width + 1
		);

		expect(contract.contentFrame.width).toBeGreaterThanOrEqual(
			contentWidth - 1
		);
		expect(contract.contentFrame.width).toBeLessThanOrEqual(
			contentWidth + 1
		);
		expect(contract.copyColumn.width).toBeGreaterThanOrEqual(
			contentWidth - 1
		);
		expect(contract.copyColumn.width).toBeLessThanOrEqual(contentWidth + 1);

		expect(contract.sectionInner.width).toBeGreaterThanOrEqual(
			sectionFrameWidth - 1
		);
		expect(contract.sectionInner.width).toBeLessThanOrEqual(
			sectionFrameWidth + 1
		);
		expect(contract.sectionFrame.width).toBeGreaterThanOrEqual(
			sectionFrameWidth - 1
		);
		expect(contract.sectionFrame.width).toBeLessThanOrEqual(
			sectionFrameWidth + 1
		);
		expect(contract.sectionInner.width).toBeGreaterThan(contentWidth);
		expect(contract.sectionFrame.width).toBeGreaterThan(contentWidth);

		expect(contract.splitSection).not.toBeNull();

		if (!contract.splitSection) {
			return;
		}

		for (const variant of [
			contract.splitSection.edgeMediaLeft,
			contract.splitSection.edgeMediaRight,
		]) {
			expect(variant.columns?.width ?? 0).toBeGreaterThanOrEqual(
				contract.splitSection.viewportWidth - 1
			);
			expect(variant.copy?.width ?? 0).toBeLessThanOrEqual(
				contentWidth + 1
			);
		}

		if (contract.splitSection.viewportWidth >= 960) {
			const contentCenter = contract.splitSection.viewportWidth / 2;

			expect(contract.splitSection.edgeMediaLeft.columnGap).toBe('0px');
			expect(contract.splitSection.edgeMediaRight.columnGap).toBe('0px');
			expect(
				contract.splitSection.edgeMediaLeft.copyColumn?.left ?? 0
			).toBeGreaterThanOrEqual(contentCenter - 1);
			expect(
				contract.splitSection.edgeMediaLeft.media?.right ?? 0
			).toBeGreaterThanOrEqual(contentCenter - 1);
			expect(
				contract.splitSection.edgeMediaRight.copyColumn?.right ?? 0
			).toBeLessThanOrEqual(contentCenter + 1);
			expect(
				contract.splitSection.edgeMediaRight.media?.right ?? 0
			).toBeGreaterThanOrEqual(contract.splitSection.viewportWidth - 1);
			expect(
				contract.splitSection.edgeMediaRight.heading?.left ?? 0
			).toBeGreaterThanOrEqual(
				(contract.splitSection.edgeMediaRight.copyColumn?.left ?? 0) - 1
			);
			expect(
				contract.splitSection.edgeMediaRight.heading?.left ?? 0
			).toBeLessThanOrEqual(
				(contract.splitSection.edgeMediaRight.copyColumn?.left ?? 0) + 1
			);
			expect(
				contract.splitSection.edgeMediaLeft.heading?.right ?? 0
			).toBeGreaterThanOrEqual(
				(contract.splitSection.edgeMediaLeft.copyColumn?.right ?? 0) - 1
			);
			expect(
				contract.splitSection.edgeMediaLeft.heading?.right ?? 0
			).toBeLessThanOrEqual(
				(contract.splitSection.edgeMediaLeft.copyColumn?.right ?? 0) + 1
			);
		}
	}
);

test(
	taggedTitle(
		'split-section edge rails retain copy gutters on wide desktop',
		'fast',
		'layout',
		'pattern'
	),
	async ({ page }) => {
		test.skip(
			test.info().project.name !== 'desktop',
			'Wide split-section rail check is desktop-only.'
		);

		await page.setViewportSize({ width: 2048, height: 1000 });
		await page.goto('/pns-pattern-qa/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		const variants = await page.evaluate(() => {
			const section =
				document.querySelector<HTMLElement>('.pns-split-section');

			if (!section) {
				return null;
			}

			const variantClasses = [
				'is-style-pns-media-left',
				'is-style-pns-media-right',
				'is-style-pns-edge-media-left',
				'is-style-pns-edge-media-right',
			];

			const rect = (element?: HTMLElement | null) => {
				if (!element) {
					return null;
				}

				const bounds = element.getBoundingClientRect();

				return {
					left: bounds.left,
					right: bounds.right,
					width: bounds.width,
				};
			};

			const measure = (variant: string) => {
				section.classList.remove(...variantClasses);
				section.classList.add(`is-style-pns-${variant}`);

				return {
					copyColumn: rect(
						section.querySelector<HTMLElement>(
							'.pns-split-section__copy-column'
						)
					),
					copy: rect(
						section.querySelector<HTMLElement>(
							'.pns-split-section__copy'
						)
					),
					copyPadding: (() => {
						const copy = section.querySelector<HTMLElement>(
							'.pns-split-section__copy'
						);

						if (!copy) {
							return null;
						}

						const styles = getComputedStyle(copy);

						return {
							left: parseFloat(styles.paddingLeft),
							right: parseFloat(styles.paddingRight),
						};
					})(),
					heading: rect(
						section.querySelector<HTMLElement>(
							'.pns-split-section__copy .wp-block-heading'
						)
					),
					media: rect(
						section.querySelector<HTMLElement>(
							'.pns-split-section__media-column'
						)
					),
				};
			};

			return {
				edgeMediaLeft: measure('edge-media-left'),
				edgeMediaRight: measure('edge-media-right'),
				mediaLeft: measure('media-left'),
				mediaRight: measure('media-right'),
				viewportWidth: window.innerWidth,
			};
		});

		expect(variants).not.toBeNull();

		if (!variants) {
			return;
		}

		const expectNear = (received: number, expected: number) => {
			expect(received).toBeGreaterThanOrEqual(expected - 1);
			expect(received).toBeLessThanOrEqual(expected + 1);
		};

		const contentCenter = variants.viewportWidth / 2;

		expect(variants.edgeMediaRight.copyPadding?.left ?? 0).toBeGreaterThan(
			0
		);
		expect(variants.edgeMediaRight.copyPadding?.right ?? 0).toBeGreaterThan(
			0
		);
		expect(variants.edgeMediaLeft.copyPadding?.left ?? 0).toBeGreaterThan(
			0
		);
		expect(variants.edgeMediaLeft.copyPadding?.right ?? 0).toBeGreaterThan(
			0
		);
		expect(variants.edgeMediaRight.copyPadding?.left ?? 0).toBeCloseTo(
			variants.edgeMediaRight.copyPadding?.right ?? 0,
			0
		);
		expect(variants.edgeMediaLeft.copyPadding?.left ?? 0).toBeCloseTo(
			variants.edgeMediaLeft.copyPadding?.right ?? 0,
			0
		);
		expectNear(variants.edgeMediaRight.media?.left ?? 0, contentCenter);
		expectNear(variants.edgeMediaLeft.media?.right ?? 0, contentCenter);
	}
);

for (const route of patternIdentityRoutes) {
	test(
		taggedTitle(
			`published section has reusable pattern identity: ${route.path} ${route.selector}`,
			'audit',
			'pattern'
		),
		async ({ page }) => {
			await page.goto(route.path);
			await page.waitForLoadState('domcontentloaded');
			await waitForContractReady(page);

			const sections = await page.evaluate((selector) => {
				return Array.from(
					document.querySelectorAll<HTMLElement>(selector)
				).map((element) => ({
					className: element.className,
					hasPnsSection: element.classList.contains('pns-section'),
					text: element.textContent
						?.replace(/\s+/g, ' ')
						.trim()
						.slice(0, 80),
				}));
			}, route.selector);

			expect(sections.length).toBeGreaterThanOrEqual(route.minCount);
			annotateAcceptedStateInventory(
				`published section identity ${route.path} ${route.selector}`,
				sections.filter((section) => !section.hasPnsSection)
			);
		}
	);
}

for (const route of redLineQuoteRoutes) {
	test(
		taggedTitle(
			`red-line quote covers use pattern hooks: ${route}`,
			'audit',
			'pattern'
		),
		async ({ page }) => {
			await page.goto(route);
			await page.waitForLoadState('domcontentloaded');
			await waitForContractReady(page);

			const redLineQuoteCovers = await page.evaluate(() =>
				Array.from(document.images)
					.filter((image) =>
						(
							image.currentSrc ||
							image.getAttribute('src') ||
							''
						).includes('Red-Keyline.svg')
					)
					.map((image) =>
						image.closest<HTMLElement>('.wp-block-cover')
					)
					.filter((cover): cover is HTMLElement => Boolean(cover))
					.map((cover) => ({
						className: cover.className,
						hasPatternClass: cover.classList.contains(
							'pns-blockquote-with-red-line'
						),
						hasQuoteClass: cover.classList.contains('pns-quotes'),
						hasSectionClass:
							cover.classList.contains('pns-section'),
						text: cover.textContent
							?.replace(/\s+/g, ' ')
							.trim()
							.slice(0, 80),
					}))
			);

			expect(redLineQuoteCovers.length).toBeGreaterThan(0);
			annotateAcceptedStateInventory(
				`red-line quote hooks ${route}`,
				redLineQuoteCovers.filter(
					(cover) =>
						!cover.hasPatternClass ||
						!cover.hasQuoteClass ||
						!cover.hasSectionClass
				)
			);
		}
	);
}

test(
	taggedTitle(
		'homepage cascade contracts',
		'smoke',
		'fast',
		'layout',
		'navigation'
	),
	async ({ page }) => {
		await page.goto('/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		const styles = await page.evaluate(() => {
			function read(selector: string) {
				const element = document.querySelector(selector);

				if (!element) {
					return null;
				}

				const computed = getComputedStyle(element);

				return {
					boundingHeight: element.getBoundingClientRect().height,
					boundingTop: element.getBoundingClientRect().top,
					boundingWidth: element.getBoundingClientRect().width,
					borderRadius: computed.borderRadius,
					blockGap: computed.getPropertyValue(
						'--wp--style--block-gap'
					),
					boxShadow: computed.boxShadow,
					display: computed.display,
					fill: computed.fill,
					fontFamily: computed.fontFamily,
					fontSize: computed.fontSize,
					fontWeight: computed.fontWeight,
					fontVariationSettings: computed.fontVariationSettings,
					gap: computed.gap,
					height: computed.height,
					left: computed.left,
					lineHeight: computed.lineHeight,
					marginBottom: computed.marginBottom,
					marginLeft: computed.marginLeft,
					marginRight: computed.marginRight,
					marginTop: computed.marginTop,
					maxHeight: computed.maxHeight,
					maxWidth: computed.maxWidth,
					minHeight: computed.minHeight,
					objectFit: computed.objectFit,
					overflowX: computed.overflowX,
					paddingBottom: computed.paddingBottom,
					position: computed.position,
					color: computed.color,
					paddingLeft: computed.paddingLeft,
					paddingRight: computed.paddingRight,
					paddingTop: computed.paddingTop,
					right: computed.right,
					textTransform: computed.textTransform,
					top: computed.top,
					transform: computed.transform,
					width: computed.width,
				};
			}

			function readFontFaces() {
				const fontFaces: Array<{
					display: string;
					family: string;
					weight: string;
				}> = [];

				for (const sheet of Array.from(document.styleSheets)) {
					try {
						for (const rule of Array.from(sheet.cssRules)) {
							if (rule.type !== CSSRule.FONT_FACE_RULE) {
								continue;
							}

							const style = (rule as CSSFontFaceRule).style;

							fontFaces.push({
								display: style.getPropertyValue('font-display'),
								family: style.getPropertyValue('font-family'),
								weight: style.getPropertyValue('font-weight'),
							});
						}
					} catch {
						continue;
					}
				}

				return fontFaces;
			}

			return {
				alignwide: read('.alignwide'),
				body: read('body'),
				button: read('.wp-block-button__link'),
				buttonGroup: read('.wp-block-buttons'),
				columns: read('.wp-block-columns'),
				connectSocialIntro: read(
					'.pns-connect-social .pns-copy-column > .wp-block-heading + p'
				),
				connectSocialColumns: read('.pns-connect-social__columns'),
				connectSocialImage: read('.pns-connect-social__image img'),
				contactFormIntro: read(
					'.pns-contact-form .pns-copy-column > .wp-block-heading + p'
				),
				cover: read('.wp-block-cover, .wp-block-cover-image'),
				coverInner: read(
					'.wp-block-cover.has-custom-content-position.has-custom-content-position .wp-block-cover__inner-container, .wp-block-cover-image.has-custom-content-position.has-custom-content-position .wp-block-cover__inner-container'
				),
				footerCopyright: read('.footer-wt p'),
				footerContactGroup: read(
					'footer .pns-footer .wp-block-column.has-link-color > .wp-block-group.is-layout-constrained'
				),
				footerLogo: read('.footer-logo img'),
				footerNavigationItem: read(
					'footer .pns-footer .wp-block-navigation .wp-block-navigation-item__content'
				),
				footerSeparator: read(
					'footer .pns-footer .wp-block-column.has-link-color > .wp-block-group.is-layout-constrained > .wp-block-separator'
				),
				fontFaces: readFontFaces(),
				footerBottomBar: read('footer .pns-footer-bottom-bar'),
				groupWithBackground: read('.wp-block-group.has-background'),
				heading: read('.wp-block-heading'),
				imageBlock: read('.wp-block-image'),
				imageCaption: read('.is-dark-theme .wp-block-image figcaption'),
				largeText: read(
					'main .has-title-large-font-size:not(.wp-block-navigation):not(.wp-block-navigation-item)'
				),
				mediumText: read(
					'main .has-text-lead-font-size:not(.wp-block-navigation):not(.wp-block-navigation-item)'
				),
				heroCopy: read('.pns-hero-copy'),
				navigation: read('.wp-block-navigation'),
				navigationItem: read(
					'header .wp-block-navigation .wp-block-navigation-item__content'
				),
				navigationOpenIcon: read(
					'header .wp-block-navigation__responsive-container-open svg'
				),
				navigationOpenButton: read(
					'header .pns-primary-navigation button[aria-label="Open menu"]'
				),
				navigationSubmenuIcon: read(
					'header .wp-block-navigation .wp-block-navigation__submenu-icon'
				),
				navigationSubmenuAnchor: read(
					'header .wp-block-navigation .wp-block-navigation__submenu-container a'
				),
				navigationSubmenuItem: read(
					'header .wp-block-navigation .wp-block-navigation-submenu .wp-block-navigation-item'
				),
				navigationTopItem: read(
					'header .wp-block-navigation .wp-block-navigation__container > .wp-block-navigation-item'
				),
				plainH2: read(
					'h2.wp-block-heading:not(.has-text-lead-font-size):not(.has-title-large-font-size):not(.has-title-display-font-size)'
				),
				pnsSectionRoot: read(
					'.entry-content > .pns-section, main .pns-section'
				),
				pnsContactFrame: read('.pns-contact-form > .pns-section-frame'),
				pnsContactSection: read('.pns-contact-form'),
				pnsSyncedColumns: read(
					'.pns-contact-form .wp-block-columns, .pns-connect-social .wp-block-columns'
				),
				separator: read('.wp-block-separator'),
				slideshow: read('.wp-block-jetpack-slideshow'),
				slideshowChild: read('.wp-block-jetpack-slideshow > *'),
				slideshowContainer: read(
					'.wp-block-jetpack-slideshow .wp-block-jetpack-slideshow_container, .wp-block-jetpack-slideshow .swiper-container'
				),
				slideshowFigure: read(
					'.wp-block-jetpack-slideshow .wp-block-jetpack-slideshow_slide figure'
				),
				slideshowImage: read('.wp-block-jetpack-slideshow_image'),
				slideshowNext: read(
					'.wp-block-jetpack-slideshow .wp-block-jetpack-slideshow_button-next'
				),
				slideshowPagination: read(
					'.wp-block-jetpack-slideshow .wp-block-jetpack-slideshow_pagination'
				),
				slideshowPaginationBullets: read(
					'.wp-block-jetpack-slideshow .wp-block-jetpack-slideshow_pagination.swiper-pagination-bullets'
				),
				slideshowPrev: read(
					'.wp-block-jetpack-slideshow .wp-block-jetpack-slideshow_button-prev'
				),
				savedFlowChild: read('.entry-content .is-layout-flow > p + p'),
				siteMain: read('.wp-site-blocks > main'),
				socialAnchor: read('.wp-block-social-links .wp-social-link a'),
				socialIcon: read(
					'.wp-block-social-links.is-style-logos-only .wp-social-link svg'
				),
				socialLinks: read('.wp-block-social-links'),
				xLargeText: read(
					'main .wp-block-cover h1, main .wp-block-cover-image h1, main .has-title-display-font-size:not(.wp-block-navigation):not(.wp-block-navigation-item)'
				),
				constrainedContent: Array.from(
					document.querySelectorAll<HTMLElement>(
						'body .is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull):not(.alignwide))'
					)
				)
					.filter(
						(element) => element.getBoundingClientRect().width > 0
					)
					.map((element) => ({
						tagName: element.tagName.toLowerCase(),
						className: element.className,
						maxWidth: getComputedStyle(element).maxWidth,
						inShopIntro: Boolean(element.closest('.shop-intro')),
					})),
				markup: {
					coverCount: document.querySelectorAll(
						'.wp-block-cover, .wp-block-cover-image'
					).length,
					coverInnerCount: document.querySelectorAll(
						'.wp-block-cover.has-custom-content-position.has-custom-content-position .wp-block-cover__inner-container, .wp-block-cover-image.has-custom-content-position.has-custom-content-position .wp-block-cover__inner-container'
					).length,
					imageBlockCount:
						document.querySelectorAll('.wp-block-image').length,
					slideshowPaginationBulletsCount: document.querySelectorAll(
						'.wp-block-jetpack-slideshow .wp-block-jetpack-slideshow_pagination.swiper-pagination-bullets'
					).length,
					socialServices: Array.from(
						document.querySelectorAll(
							'footer .wp-block-social-links .wp-social-link'
						)
					).map((element) =>
						Array.from(element.classList)
							.find((className) =>
								className.startsWith('wp-social-link-')
							)
							?.replace('wp-social-link-', '')
					),
					footerSocialListCount: document.querySelectorAll(
						'footer .pns-footer .wp-block-social-links'
					).length,
					primaryNavigationClassCount: document.querySelectorAll(
						'header .wp-block-navigation.pns-primary-navigation'
					).length,
					desktopSubmenuOverviewLabels: Array.from(
						document.querySelectorAll(
							'header .pns-primary-navigation .pns-navigation-submenu-overview > a'
						)
					).map((element) => element.textContent?.trim()),
				},
			};
		});

		annotateAcceptedStateInventory('homepage cascade styles', {
			alignwide: styles.alignwide,
			body: styles.body,
			button: styles.button,
			buttonGroup: styles.buttonGroup,
			constrainedContent: styles.constrainedContent,
			groupWithBackground: styles.groupWithBackground,
			navigation: styles.navigation,
			pnsContactFrame: styles.pnsContactFrame,
			pnsContactSection: styles.pnsContactSection,
			pnsSyncedColumns: styles.pnsSyncedColumns,
			savedFlowChild: styles.savedFlowChild,
			siteMain: styles.siteMain,
			slideshow: styles.slideshow,
			socialLinks: styles.socialLinks,
		});
		expect(styles.body?.overflowX).toBeTruthy();
		expect(styles.body?.blockGap).toBeTruthy();
		expect(styles.body?.fontFamily).toContain('Libre Franklin');
		expect(styles.body?.fontFamily).toContain('Segoe UI');
		expect(styles.heading?.fontFamily).toContain('Rubik');
		expect(styles.heading?.fontFamily).toContain('Trebuchet MS');
		expect(
			styles.fontFaces.find((fontFace) =>
				fontFace.family.includes('Libre Franklin')
			)?.display
		).toBe('swap');
		expect(
			styles.fontFaces.find((fontFace) =>
				fontFace.family.includes('Rubik')
			)?.display
		).toBe('swap');
		expect(styles.siteMain?.overflowX).toBeTruthy();
		expect(styles.siteMain?.marginTop).toBeTruthy();
		expect(styles.savedFlowChild?.marginTop).toBeTruthy();
		expect(styles.constrainedContent.length).toBeGreaterThan(0);
		expect(styles.columns?.marginBottom).toBeTruthy();
		expect(styles.connectSocialIntro?.marginTop).toBeTruthy();
		expect(styles.contactFormIntro?.marginTop).toBeTruthy();
		expect(styles.markup.coverCount).toBeGreaterThan(0);
		expect(styles.cover?.paddingTop).toBeTruthy();
		expect(styles.cover?.paddingRight).toBeTruthy();
		expect(styles.cover?.paddingBottom).toBeTruthy();
		expect(styles.cover?.paddingLeft).toBeTruthy();
		expect(styles.coverInner?.width).toBeTruthy();
		expect(styles.markup.imageBlockCount).toBeGreaterThan(0);
		expect(styles.imageBlock?.marginTop).toBeTruthy();
		expect(styles.imageBlock?.marginRight).toBeTruthy();
		expect(styles.imageBlock?.marginBottom).toBeTruthy();
		expect(styles.imageBlock?.marginLeft).toBeTruthy();
		if (styles.imageCaption) {
			expect(styles.imageCaption.color).toBeTruthy();
		}
		expect(styles.groupWithBackground?.paddingTop).toBeTruthy();
		expect(styles.groupWithBackground?.paddingRight).toBeTruthy();
		expect(styles.groupWithBackground?.paddingBottom).toBeTruthy();
		expect(styles.groupWithBackground?.paddingLeft).toBeTruthy();
		expect(styles.separator?.marginTop).toBeTruthy();
		expect(styles.separator?.marginBottom).toBeTruthy();
		expect(styles.footerSeparator?.boundingWidth ?? 0).toBeLessThanOrEqual(
			styles.footerContactGroup?.boundingWidth ?? 0
		);
		expect(styles.footerSeparator?.boundingWidth ?? 0).toBeLessThanOrEqual(
			400
		);
		expect(styles.navigation?.display).toBe('flex');
		const navViewportWidth = page.viewportSize()?.width ?? 0;
		expect(navViewportWidth).toBeGreaterThan(0);
		expect(styles.navigation?.gap).toBeTruthy();
		expect(styles.markup.primaryNavigationClassCount).toBe(1);
		expect(
			styles.markup.desktopSubmenuOverviewLabels.length
		).toBeGreaterThan(0);
		expect(styles.navigation?.paddingLeft).toBeTruthy();
		expect(styles.navigation?.paddingRight).toBeTruthy();
		expect(styles.pnsSectionRoot?.marginTop).toBeTruthy();
		expect(styles.pnsContactSection?.marginTop).toBeTruthy();
		expect(styles.pnsContactFrame?.paddingTop).toBeTruthy();
		expect(styles.pnsContactFrame?.paddingBottom).toBeTruthy();
		if (styles.pnsSyncedColumns) {
			expect(styles.pnsSyncedColumns.gap).toBeTruthy();
			expect(styles.pnsSyncedColumns.marginTop).toBeTruthy();
		}
		expect(styles.navigationItem?.fontSize).toBeTruthy();
		expect(styles.navigationOpenIcon?.fill).toBeTruthy();
		if (styles.navigationOpenButton) {
			expect(styles.navigationOpenButton.paddingRight).toBeTruthy();
		}
		if ((page.viewportSize()?.width ?? 0) >= 600) {
			expect(styles.navigationSubmenuIcon?.marginLeft).toBeTruthy();
		}
		expect(styles.navigationTopItem?.paddingLeft).toBeTruthy();
		if (styles.navigationSubmenuItem && styles.navigationSubmenuAnchor) {
			expect(styles.navigationSubmenuItem.marginLeft).toBeTruthy();
			expect(styles.navigationSubmenuItem.marginRight).toBeTruthy();
			expect(styles.navigationSubmenuItem.paddingLeft).toBeTruthy();
			expect(styles.navigationSubmenuItem.paddingRight).toBeTruthy();
			expect(styles.navigationSubmenuAnchor.paddingTop).toBeTruthy();
			expect(styles.navigationSubmenuAnchor.paddingRight).toBeTruthy();
			expect(styles.navigationSubmenuAnchor.paddingBottom).toBeTruthy();
			expect(styles.navigationSubmenuAnchor.paddingLeft).toBeTruthy();
		}
		const alignwideViewportWidth = page.viewportSize()?.width ?? 0;
		const expectedAlignwideWidth =
			alignwideViewportWidth > 1280
				? alignwideViewportWidth / 2 + 640
				: alignwideViewportWidth;
		const renderedAlignwideWidth = Number.parseFloat(
			styles.alignwide?.width || '0'
		);
		expect(renderedAlignwideWidth).toBeGreaterThanOrEqual(
			expectedAlignwideWidth - 1
		);
		expect(renderedAlignwideWidth).toBeLessThanOrEqual(
			expectedAlignwideWidth + 1
		);
		expect(styles.button?.borderRadius).toBeTruthy();
		expect(styles.button?.boxShadow).toBeTruthy();
		expect(styles.buttonGroup?.gap).toBeTruthy();
		expect(styles.buttonGroup?.marginTop).toBeTruthy();
		expect(styles.footerCopyright?.color).toBe('rgb(255, 255, 255)');
		expect(styles.footerLogo?.maxWidth).toBeTruthy();
		expect(styles.footerNavigationItem?.fontSize).toBeTruthy();
		expect(styles.footerNavigationItem?.fontVariationSettings).toBeTruthy();
		expect(styles.footerNavigationItem?.lineHeight).toBeTruthy();
		const viewportWidth = page.viewportSize()?.width ?? 0;
		const expectedMediumFontSize = Math.min(
			Math.max(17, 16 + viewportWidth * 0.0045),
			20
		);
		const expectedLargeFontSize = Math.min(
			Math.max(36, 28 + viewportWidth * 0.0175),
			46
		);
		const expectedXLargeFontSize = Math.min(
			Math.max(44, 35.2 + viewportWidth * 0.02),
			56
		);
		const expectedHeroPaddingLeft = viewportWidth >= 782 ? '32px' : '16px';

		expect(
			Number.parseFloat(styles.mediumText?.fontSize ?? '0')
		).toBeCloseTo(expectedMediumFontSize, 0);
		expect(
			Number.parseFloat(styles.largeText?.fontSize ?? '0')
		).toBeCloseTo(expectedLargeFontSize, 0);
		expect(Number.parseFloat(styles.plainH2?.fontSize ?? '0')).toBeCloseTo(
			expectedLargeFontSize,
			0
		);
		expect(
			Number.parseFloat(styles.xLargeText?.fontSize ?? '0')
		).toBeCloseTo(expectedXLargeFontSize, 0);
		expect(expectedHeroPaddingLeft).toBeTruthy();
		expect(styles.heroCopy?.marginTop).toBeTruthy();
		expect(styles.heroCopy?.marginBottom).toBeTruthy();
		expect(styles.heroCopy?.maxWidth).toBeTruthy();
		expect(styles.heroCopy?.paddingLeft).toBeTruthy();
		expect(styles.heroCopy?.paddingRight).toBeTruthy();
		expect(styles.connectSocialColumns?.width).toBeTruthy();
		expect(styles.connectSocialColumns?.maxWidth).toBeTruthy();
		expect(styles.footerBottomBar?.width).toBeTruthy();
		expect(styles.footerBottomBar?.maxWidth).toBeTruthy();
		if (viewportWidth >= 960) {
			expect(styles.connectSocialImage?.width).toBeTruthy();
		}
		expect(styles.button?.paddingLeft).toBeTruthy();
		expect(styles.button?.paddingRight).toBeTruthy();
		expect(styles.heading?.fontFamily).toContain('Rubik');
		expect(styles.heading?.fontWeight).toBe('800');
		expect(styles.heading?.textTransform).toBe('uppercase');
		expect(styles.slideshow?.marginBottom).toBeTruthy();
		expect(styles.slideshowChild?.marginBottom).toBeTruthy();
		expect(styles.slideshowImage?.display).toBe('block');
		expect(styles.slideshowImage?.lineHeight).toBe('0px');
		expect(styles.slideshowImage?.maxWidth).toBe('none');
		expect(styles.slideshowImage?.maxHeight).toBe('none');
		expect(styles.slideshowImage?.objectFit).toBe('cover');
		expect(styles.slideshowPrev?.display).toBe('block');
		expect(styles.slideshowPrev?.left).toBeTruthy();
		expect(styles.slideshowPrev?.transform).not.toBe('none');
		expect(styles.slideshowNext?.display).toBe('block');
		expect(styles.slideshowNext?.right).toBeTruthy();
		expect(styles.slideshowNext?.transform).not.toBe('none');
		if (
			styles.slideshowContainer &&
			styles.slideshowPrev &&
			styles.slideshowNext
		) {
			const slideCenter =
				styles.slideshowContainer.boundingTop +
				styles.slideshowContainer.boundingHeight / 2;
			const previousCenter =
				styles.slideshowPrev.boundingTop +
				styles.slideshowPrev.boundingHeight / 2;
			const nextCenter =
				styles.slideshowNext.boundingTop +
				styles.slideshowNext.boundingHeight / 2;

			expect(Math.abs(previousCenter - slideCenter)).toBeLessThanOrEqual(
				2
			);
			expect(Math.abs(nextCenter - slideCenter)).toBeLessThanOrEqual(2);
		}
		expect(styles.slideshowPagination?.display).toBe('none');
		if (styles.markup.slideshowPaginationBulletsCount > 0) {
			expect(styles.slideshowPaginationBullets?.position).toBeTruthy();
		}
		if (viewportWidth >= 782) {
			expect(styles.slideshowFigure?.display).toBe('block');
			expect(styles.slideshowFigure?.height).toBeTruthy();
			expect(styles.slideshowFigure?.lineHeight).toBeTruthy();
			expect(styles.slideshowFigure?.minHeight).toBeTruthy();
		}
		expect(styles.socialLinks?.paddingLeft).toBeTruthy();
		expect(styles.socialLinks?.gap).toBeTruthy();
		expect(styles.socialLinks?.marginTop).toBeTruthy();
		expect(styles.socialAnchor?.paddingTop).toBeTruthy();
		expect(styles.socialAnchor?.paddingRight).toBeTruthy();
		expect(styles.socialAnchor?.paddingBottom).toBeTruthy();
		expect(styles.socialAnchor?.paddingLeft).toBeTruthy();
		expect(styles.socialIcon?.height).toBeTruthy();
		expect(styles.socialIcon?.width).toBeTruthy();
		expect(styles.markup.footerSocialListCount).toBe(1);
		expect(styles.markup.socialServices).toEqual([
			'patreon',
			'linkedin',
			'facebook',
			'instagram',
			'youtube',
			'bluesky',
			'threads',
		]);
	}
);

test(
	taggedTitle(
		'homepage custom Ecwid product grid contracts',
		'smoke',
		'mobile-smoke',
		'fast',
		'mobile-fast',
		'mobile-full',
		'shop',
		'ecwid'
	),
	async ({ page }) => {
		await page.goto('/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		const state = await page.evaluate(() => {
			const grid = document.querySelector<HTMLElement>(
				'.shop-intro .ran-ecwid-shop-teaser'
			);
			const frame =
				document.querySelector<HTMLElement>('.shop-intro > div');

			if (!grid || !frame) {
				return { found: false };
			}

			const gridRect = grid.getBoundingClientRect();
			const frameRect = frame.getBoundingClientRect();
			const cards = Array.from(
				grid.querySelectorAll<HTMLElement>(
					'.ran-ecwid-shop-teaser-card'
				)
			).map((card) => card.getBoundingClientRect());
			const computed = getComputedStyle(grid);

			return {
				cardCount: cards.length,
				display: computed.display,
				flexWrap: computed.flexWrap,
				found: true,
				frameLeft: Math.round(frameRect.left),
				frameRight: Math.round(frameRect.right),
				gridLeft: Math.round(gridRect.left),
				gridRight: Math.round(gridRect.right),
				hasDebugNotice: Boolean(
					grid.querySelector('.ran-ecwid-shop-teaser__notice')
				),
				justifyContent: computed.justifyContent,
				source: grid.getAttribute('data-source'),
				visibleRows: new Set(cards.map((card) => Math.round(card.top)))
					.size,
				viewportWidth: window.innerWidth,
			};
		});

		expect(state.found).toBe(true);
		expect(state.display).toBe('flex');
		expect(state.flexWrap).toBe('wrap');
		expect(['center', 'flex-start', 'flex-end']).toContain(
			state.justifyContent
		);
		expect(state.cardCount).toBeGreaterThanOrEqual(3);
		expect([
			'ecwid',
			'fresh-cache',
			'last-good-cache',
			'filter-fallback',
		]).toContain(state.source);
		expect(state.hasDebugNotice).toBe(false);
		expect(state.gridLeft).toBeGreaterThanOrEqual(state.frameLeft);
		expect(state.gridRight).toBeLessThanOrEqual(state.frameRight);
		expect(state.visibleRows).toBeGreaterThanOrEqual(1);
	}
);

test(
	taggedTitle(
		'manual spacing controls override theme defaults outside fixed layouts',
		'fast',
		'layout'
	),
	async ({ page }) => {
		await page.goto('/');
		await page.waitForLoadState('domcontentloaded');

		const styles = await page.evaluate(() => {
			const supportStyles = document.createElement('style');
			supportStyles.textContent = `
			.wp-container-pns-manual-flow-gap > * {
				margin-block-start: 23px;
				margin-block-end: 0;
			}
			.wp-container-pns-manual-flow-gap > :first-child {
				margin-block-start: 0;
			}
			.pns-manual-buttons-gap {
				gap: 29px;
				margin-block-start: 31px;
			}
			.pns-manual-columns-gap {
				gap: 43px;
			}
			.pns-manual-social-gap {
				gap: 11px;
				margin-block-start: 17px;
			}
		`;
			const themeStylesheet = document.querySelector(
				'link[href*="/styles/dist/frontend.min.css"]'
			);
			document.head.insertBefore(supportStyles, themeStylesheet);

			const fixture = document.createElement('div');
			fixture.id = 'pns-manual-spacing-control-fixture';
			fixture.className = 'entry-content';
			fixture.innerHTML = `
			<div class="is-layout-flow wp-container-pns-manual-flow-gap">
				<p>First flow child</p>
				<p>Second flow child</p>
			</div>
			<div class="wp-block-spacer" style="height: 1px; margin-top: 37px;"></div>
			<nav class="wp-block-query-pagination" style="margin-top: 41px;"></nav>
			<div class="wp-block-buttons is-layout-flex pns-manual-buttons-gap">
				<div class="wp-block-button"><a class="wp-block-button__link">One</a></div>
				<div class="wp-block-button"><a class="wp-block-button__link">Two</a></div>
			</div>
			<div class="wp-block-columns is-layout-flex pns-manual-columns-gap">
				<div class="wp-block-column"></div>
				<div class="wp-block-column"></div>
			</div>
			<ul class="wp-block-social-links is-layout-flex pns-manual-social-gap">
				<li class="wp-social-link"><a>One</a></li>
				<li class="wp-social-link"><a>Two</a></li>
			</ul>
		`;
			document.body.appendChild(fixture);

			const flowSecondChild = fixture.querySelector(
				'.is-layout-flow > p + p'
			);
			const spacer = fixture.querySelector('.wp-block-spacer');
			const pagination = fixture.querySelector(
				'.wp-block-query-pagination'
			);
			const buttons = fixture.querySelector('.wp-block-buttons');
			const columns = fixture.querySelector('.wp-block-columns');
			const socialLinks = fixture.querySelector('.wp-block-social-links');

			return {
				flowChildMarginTop: flowSecondChild
					? getComputedStyle(flowSecondChild).marginTop
					: null,
				spacerMarginTop: spacer
					? getComputedStyle(spacer).marginTop
					: null,
				paginationMarginTop: pagination
					? getComputedStyle(pagination).marginTop
					: null,
				buttonsGap: buttons ? getComputedStyle(buttons).gap : null,
				buttonsMarginTop: buttons
					? getComputedStyle(buttons).marginTop
					: null,
				columnsGap: columns ? getComputedStyle(columns).gap : null,
				socialLinksGap: socialLinks
					? getComputedStyle(socialLinks).gap
					: null,
				socialLinksMarginTop: socialLinks
					? getComputedStyle(socialLinks).marginTop
					: null,
			};
		});

		annotateAcceptedStateInventory('manual spacing control styles', styles);
		expect(styles.flowChildMarginTop).toBeTruthy();
		expect(styles.spacerMarginTop).toBeTruthy();
		expect(styles.paginationMarginTop).toBeTruthy();
		expect(styles.buttonsGap).toBeTruthy();
		expect(styles.buttonsMarginTop).toBeTruthy();
		expect(styles.columnsGap).toBeTruthy();
		expect(styles.socialLinksGap).toBeTruthy();
		expect(styles.socialLinksMarginTop).toBeTruthy();
	}
);

test(
	taggedTitle(
		'cross-site banner CTA contracts',
		'fast',
		'mobile-fast',
		'mobile-full',
		'layout',
		'mobile-layout',
		'navigation',
		'mobile-navigation'
	),
	async ({ page }) => {
		for (const path of ['/', '/edu-giveaway/', '/shop/']) {
			await page.goto(path);
			await page.waitForLoadState('domcontentloaded');
			await waitForContractReady(page);

			const banner = page.locator('header nav.pns-cross-site-banner-cta');

			await expect(banner).toHaveCount(1);
			await expect(
				banner.getByRole('link', { name: 'SHOP P&S' })
			).toHaveAttribute('href', '/shop/');
			await expect(
				banner.getByRole('link', { name: 'SUPPORT P&S' })
			).toHaveAttribute(
				'href',
				'https://www.patreon.com/cw/protestsandsuffragettes'
			);

			const styles = await banner.evaluate((element) => {
				const computed = getComputedStyle(element);
				const rect = element.getBoundingClientRect();
				const mainRect = document
					.querySelector('main')
					?.getBoundingClientRect();
				const link = element.querySelector(
					'.wp-block-navigation-item__content'
				);
				const linkComputed = link ? getComputedStyle(link) : null;
				const linkRect = link?.getBoundingClientRect();

				return {
					backgroundColor: computed.backgroundColor,
					clipPath: computed.clipPath,
					color: computed.color,
					linkRect: linkRect
						? {
								height: linkRect.height,
								width: linkRect.width,
								x: linkRect.x,
								y: linkRect.y,
							}
						: null,
					linkColor: linkComputed?.color,
					linkTransitionDuration: linkComputed?.transitionDuration,
					linkTransitionProperty: linkComputed?.transitionProperty,
					linkTransform: linkComputed?.transform,
					mainTop: mainRect?.top,
					marginBlockEnd: Number.parseFloat(computed.marginBlockEnd),
					rectBottom: rect.bottom,
					rectLeft: rect.left,
					rectRight: rect.right,
					rectHeight: rect.height,
					rectTop: rect.top,
					transform: computed.transform,
					scrollWidth: document.documentElement.scrollWidth,
					viewportWidth: window.innerWidth,
				};
			});

			expect(styles.backgroundColor).toBe('rgb(250, 208, 39)');
			expect(styles.linkColor).toBe('rgb(212, 0, 15)');
			expect(styles.clipPath).toContain('polygon');
			expect(styles.transform).toBe('none');
			expect(styles.linkTransform).toBe('none');
			expect(styles.linkTransitionProperty).not.toContain('all');
			expect(styles.linkTransitionProperty).toContain('color');
			expect(styles.linkTransitionProperty).toContain(
				'text-decoration-thickness'
			);
			expect(styles.linkTransitionDuration).not.toBe('0s');
			expect(styles.marginBlockEnd).toBeLessThan(-8);
			expect(styles.mainTop).toBeDefined();
			const contentOverlap = styles.rectBottom - (styles.mainTop ?? 0);
			expect(contentOverlap).toBeGreaterThan(8);
			expect(
				Math.abs(contentOverlap + styles.marginBlockEnd)
			).toBeLessThan(1);
			expect(styles.rectLeft).toBeGreaterThanOrEqual(-1);
			expect(styles.rectRight).toBeLessThanOrEqual(
				styles.viewportWidth + 1
			);
			expect(styles.scrollWidth).toBeLessThanOrEqual(
				styles.viewportWidth + 1
			);

			await banner
				.locator('.wp-block-navigation-item__content')
				.first()
				.hover();

			const hover = await banner.evaluate((element) => {
				const computed = getComputedStyle(element);
				const rect = element.getBoundingClientRect();
				const link = element.querySelector(
					'.wp-block-navigation-item__content'
				);
				const linkComputed = link ? getComputedStyle(link) : null;
				const linkRect = link?.getBoundingClientRect();

				return {
					clipPath: computed.clipPath,
					linkRect: linkRect
						? {
								height: linkRect.height,
								width: linkRect.width,
								x: linkRect.x,
								y: linkRect.y,
							}
						: null,
					linkTextDecorationLine: linkComputed?.textDecorationLine,
					rectHeight: rect.height,
					rectLeft: rect.left,
					rectRight: rect.right,
					rectTop: rect.top,
				};
			});

			expect(hover.clipPath).toBe(styles.clipPath);
			expect(hover.linkTextDecorationLine).toBe('underline');
			expect(Math.abs(hover.rectLeft - styles.rectLeft)).toBeLessThan(
				0.5
			);
			expect(Math.abs(hover.rectRight - styles.rectRight)).toBeLessThan(
				0.5
			);
			expect(Math.abs(hover.rectTop - styles.rectTop)).toBeLessThan(0.5);
			expect(Math.abs(hover.rectHeight - styles.rectHeight)).toBeLessThan(
				0.5
			);
			expect(hover.linkRect).not.toBeNull();
			expect(styles.linkRect).not.toBeNull();
			expect(
				Math.abs((hover.linkRect?.x ?? 0) - (styles.linkRect?.x ?? 0))
			).toBeLessThan(0.5);
			expect(
				Math.abs((hover.linkRect?.y ?? 0) - (styles.linkRect?.y ?? 0))
			).toBeLessThan(0.5);
			expect(
				Math.abs(
					(hover.linkRect?.width ?? 0) - (styles.linkRect?.width ?? 0)
				)
			).toBeLessThan(0.5);
			expect(
				Math.abs(
					(hover.linkRect?.height ?? 0) -
						(styles.linkRect?.height ?? 0)
				)
			).toBeLessThan(0.5);
		}
	}
);

test(
	taggedTitle(
		'light surface templates provide light CTA reveal backing',
		'fast',
		'mobile-full',
		'layout',
		'navigation',
		'template'
	),
	async ({ page }) => {
		for (const path of [
			'/shop/',
			'/search/suffrage/',
			'/news/work-with-us-past-deadlines/',
		]) {
			await page.goto(path);
			await page.waitForLoadState('domcontentloaded');

			await expect(
				page.locator('body > .wp-site-blocks > main.pns-light-surface')
			).toHaveCount(1);
			await expect(page.locator('body > .wp-site-blocks')).toHaveCSS(
				'background-color',
				'rgb(255, 255, 255)'
			);
		}

		await page.goto('/');
		await page.waitForLoadState('domcontentloaded');
		await expect(page.locator('body > .wp-site-blocks')).toHaveCSS(
			'background-color',
			'rgba(0, 0, 0, 0)'
		);
	}
);

test(
	taggedTitle(
		'header logo stays in the visual column',
		'fast',
		'mobile-fast',
		'mobile-full',
		'layout',
		'mobile-layout',
		'navigation',
		'mobile-navigation'
	),
	async ({ page }) => {
		for (const path of ['/', '/artworks/']) {
			await page.goto(path);
			await page.waitForLoadState('domcontentloaded');
			await waitForContractReady(page);

			const metrics = await page.evaluate(() => {
				const headerInner = document.querySelector<HTMLElement>(
					'.pns-header__surface > .pns-header__inner'
				);
				const logoBlock =
					document.querySelector<HTMLElement>('.pands-logo');
				const primaryNavigation = document.querySelector<HTMLElement>(
					'.pns-header__inner .pns-primary-navigation'
				);
				const logoImage = document.querySelector<HTMLElement>(
					'.pands-logo .custom-logo'
				);

				if (!headerInner || !logoBlock || !logoImage) {
					return null;
				}

				const headerInnerRect = headerInner.getBoundingClientRect();
				const logoBlockRect = logoBlock.getBoundingClientRect();
				const logoImageRect = logoImage.getBoundingClientRect();
				const primaryNavigationRect =
					primaryNavigation?.getBoundingClientRect();
				const headerInnerStyles = getComputedStyle(headerInner);
				const logoImageStyles = getComputedStyle(logoImage);
				const visualColumnLeft =
					headerInnerRect.left +
					Number.parseFloat(headerInnerStyles.paddingLeft || '0');

				return {
					headerHeight:
						document
							.querySelector('.pns-header__surface')
							?.getBoundingClientRect().height ?? 0,
					logoBlockWidth: logoBlockRect.width,
					logoImageLeft: logoImageRect.left,
					logoImagePosition: logoImageStyles.position,
					primaryNavigationRight:
						primaryNavigationRect?.right ?? null,
					scrollWidth: document.documentElement.scrollWidth,
					visualColumnRight: headerInnerRect.right,
					viewportWidth: window.innerWidth,
					visualColumnLeft,
				};
			});

			expect(metrics).not.toBeNull();

			if (!metrics) {
				return;
			}

			expect(metrics.logoImagePosition).toBe('static');
			expect(metrics.logoBlockWidth).toBeGreaterThan(0);
			expect(
				Math.abs(metrics.logoImageLeft - metrics.visualColumnLeft)
			).toBeLessThanOrEqual(1);
			if (metrics.primaryNavigationRight) {
				expect(metrics.primaryNavigationRight).toBeLessThanOrEqual(
					metrics.visualColumnRight + 1
				);
			}
			expect(metrics.headerHeight).toBeGreaterThan(0);
			expect(metrics.scrollWidth).toBeLessThanOrEqual(
				metrics.viewportWidth + 1
			);
		}
	}
);

test(
	taggedTitle(
		'site identity renders logo-only in template chrome',
		'fast',
		'template'
	),
	async ({ page }) => {
		for (const path of ['/', '/herstories/mary-barbour/']) {
			await page.goto(path);
			await page.waitForLoadState('domcontentloaded');
			await waitForContractReady(page);

			await expect(
				page.locator('header .wp-block-site-logo')
			).toHaveCount(1);
			await expect(
				page.locator('footer .wp-block-site-logo')
			).toHaveCount(1);
			await expect(
				page.locator(
					'header .wp-block-site-title, header .wp-block-site-tagline, footer .wp-block-site-title, footer .wp-block-site-tagline'
				)
			).toHaveCount(0);

			const documentTitle = await page.title();

			expect(documentTitle).toContain('Protests');
			expect(documentTitle).toContain('Suffragettes');
		}
	}
);

test(
	taggedTitle(
		'header search drawer keeps native search and keyboard disclosure contracts',
		'fast',
		'navigation',
		'desktop-only'
	),
	async ({ page }) => {
		await page.setViewportSize({ width: 1440, height: 900 });
		await page.goto('/', { waitUntil: 'domcontentloaded' });
		await waitForContractReady(page);

		const trigger = page.locator(
			'.pns-primary-navigation a.wp-block-navigation-item__content[href$="/search/"]'
		);
		const drawer = page.locator('.pns-header-search__drawer');
		const input = drawer.locator('input[type="search"]');
		const submit = drawer.locator('button[type="submit"]');
		const cta = page.locator('header nav.pns-cross-site-banner-cta');
		const closedCtaTop = await cta.evaluate(
			(element) => element.getBoundingClientRect().top
		);

		await expect(trigger).toBeVisible();
		await expect(trigger).toHaveAccessibleName('Search');
		await expect(trigger).not.toHaveClass(/\bbutton\b/);
		await expect(trigger).toHaveAttribute('href', /\/search\/$/);
		await expect(trigger).toHaveAttribute('aria-expanded', 'false');
		await expect(drawer).toBeHidden();
		await expect(drawer).not.toHaveAttribute('role', 'dialog');
		await expect(input).toHaveAttribute('name', 's');
		await expect(input).toHaveAttribute('id', 'pns-site-search-input');
		await expect(input).toHaveAttribute(
			'placeholder',
			'know your herstory...'
		);
		await expect(drawer.locator('label')).toHaveAttribute(
			'for',
			'pns-site-search-input'
		);
		await expect(drawer.locator('label')).toHaveClass(
			/wp-block-search__label/
		);
		await expect(drawer.locator('label')).toHaveClass(/screen-reader-text/);
		await expect(submit).toHaveClass(/wp-block-search__button/);

		await trigger.focus();
		await page.keyboard.press('Enter');
		await expect(trigger).toHaveAttribute('aria-expanded', 'true');
		await expect(drawer).toBeVisible();
		await expect(input).toBeFocused();

		const geometry = await page.evaluate(() => {
			const triggerElement = document.querySelector<HTMLElement>(
				'.pns-primary-navigation a.wp-block-navigation-item__content[href$="/search/"]'
			);
			const drawerElement = document.querySelector<HTMLElement>(
				'.pns-header-search__drawer'
			);
			const navigationElement = document.querySelector<HTMLElement>(
				'.pns-primary-navigation'
			);
			const ctaElement = document.querySelector<HTMLElement>(
				'header nav.pns-cross-site-banner-cta'
			);
			const navigationWrapper = document.querySelector<HTMLElement>(
				'.pns-header__navigation-search'
			);

			if (
				!triggerElement ||
				!drawerElement ||
				!navigationElement ||
				!ctaElement ||
				!navigationWrapper
			) {
				return null;
			}

			const drawerRect = drawerElement.getBoundingClientRect();
			const navigationRect = navigationElement.getBoundingClientRect();
			const navigationWrapperRect =
				navigationWrapper.getBoundingClientRect();
			const ctaRect = ctaElement.getBoundingClientRect();
			const formElement = drawerElement.querySelector<HTMLElement>(
				'.pns-header-search__form'
			);

			return {
				ctaTop: ctaRect.top,
				drawerBottom: drawerRect.bottom,
				drawerHeight: drawerRect.height,
				drawerRight: drawerRect.right,
				drawerPosition: window.getComputedStyle(drawerElement).position,
				drawerShadow: window.getComputedStyle(drawerElement).boxShadow,
				drawerTransition:
					window.getComputedStyle(drawerElement).transitionDuration,
				drawerTop: drawerRect.top,
				navigationBottom: navigationRect.bottom,
				navigationWrapperRight: navigationWrapperRect.right,
				formRight: formElement?.getBoundingClientRect().right ?? 0,
				formWidth: formElement?.getBoundingClientRect().width ?? 0,
				triggerBottom: triggerElement.getBoundingClientRect().bottom,
				submitBackground: window.getComputedStyle(
					drawerElement.querySelector('button[type="submit"]')
				).backgroundColor,
			};
		});

		expect(geometry).not.toBeNull();
		expect(geometry?.drawerTop ?? 0).toBeGreaterThanOrEqual(
			Math.max(
				geometry?.triggerBottom ?? 0,
				geometry?.navigationBottom ?? 0
			) - 1
		);
		expect(geometry?.drawerBottom ?? 0).toBeLessThanOrEqual(
			(geometry?.ctaTop ?? 0) + 1
		);
		expect(geometry?.ctaTop ?? 0).toBeCloseTo(
			closedCtaTop + (geometry?.drawerHeight ?? 0),
			1
		);
		expect(geometry?.drawerPosition).toBe('relative');
		expect(geometry?.drawerShadow).toBe('none');
		expect(geometry?.drawerTransition).not.toBe('0s');
		expect(geometry?.drawerRight ?? 0).toBeCloseTo(
			geometry?.navigationWrapperRight ?? 0,
			1
		);
		expect(geometry?.formRight ?? 0).toBeCloseTo(
			geometry?.navigationWrapperRight ?? 0,
			1
		);
		expect(geometry?.formWidth ?? 0).toBeCloseTo(515, 0);
		expect(geometry?.submitBackground).not.toBe('rgba(0, 0, 0, 0)');
		await expect(submit).toBeVisible();

		await page.keyboard.press('Escape');
		await expect(drawer).toBeHidden();
		await expect(trigger).toHaveAttribute('aria-expanded', 'false');
		await expect(trigger).toBeFocused();

		await trigger.click();
		await expect(drawer).toBeVisible();
		await page.mouse.click(8, 500);
		await expect(drawer).toBeHidden();

		await trigger.click();
		await expect(input).toBeFocused();
		await input.press('Tab');
		await expect(submit).toBeFocused();
		await submit.press('Tab');
		await expect(drawer).toBeHidden();
		await expect(trigger).toHaveAttribute('aria-expanded', 'false');

		await trigger.click();
		await input.fill('Work');
		await Promise.all([
			page.waitForURL(/\/search\/Work\/$/),
			input.press('Enter'),
		]);
		await expect(page.locator('main.pns-template-search')).toBeVisible();
	}
);

test(
	taggedTitle(
		'header search drawer respects reduced-motion preferences',
		'fast',
		'navigation',
		'desktop-only'
	),
	async ({ page }) => {
		await page.emulateMedia({ reducedMotion: 'reduce' });
		await page.setViewportSize({ width: 1440, height: 900 });
		await page.goto('/', { waitUntil: 'domcontentloaded' });
		await waitForContractReady(page);

		const trigger = page.locator(
			'.pns-primary-navigation a.wp-block-navigation-item__content[href$="/search/"]'
		);
		const drawer = page.locator('.pns-header-search__drawer');
		const input = drawer.locator('input[type="search"]');

		await trigger.click();
		await expect(drawer).toBeVisible();
		await expect(input).toBeFocused();
		expect(
			await drawer.evaluate(
				(element) => window.getComputedStyle(element).transitionDuration
			)
		).toBe('0s');

		await page.keyboard.press('Escape');
		await expect(drawer).toBeHidden();
		await expect(trigger).toBeFocused();
	}
);

test(
	taggedTitle(
		'header navigation switches before it overlaps the logo',
		'fast',
		'navigation',
		'layout',
		'desktop-only'
	),
	async ({ page }) => {
		test.setTimeout(60_000);

		const headerSearchDesktopBreakpoint = 1152;

		for (const width of [
			950, 1000, 1035, 1036, 1151, 1152, 1200, 1240, 1282, 1285, 1290,
			1293, 1294,
		]) {
			await page.setViewportSize({ width, height: 600 });
			await page.goto('/artworks/', { waitUntil: 'domcontentloaded' });
			await waitForContractReady(page);

			const metrics = await page.evaluate(() => {
				const readRect = (selector: string) => {
					const element =
						document.querySelector<HTMLElement>(selector);

					if (!element) {
						return null;
					}

					const rect = element.getBoundingClientRect();
					const computed = getComputedStyle(element);

					return {
						display: computed.display,
						height: rect.height,
						left: rect.left,
						right: rect.right,
						width: rect.width,
						y: rect.y,
					};
				};

				const logo = readRect('.pands-logo .custom-logo');
				const desktopNavigation = readRect(
					'header .pns-primary-navigation .wp-block-navigation__responsive-container:not(.is-menu-open)'
				);
				const coreOpenButton = readRect(
					'header .pns-primary-navigation > .wp-block-navigation__responsive-container-open'
				);
				const desktopNavigationList =
					document.querySelector<HTMLElement>(
						'header .pns-primary-navigation .wp-block-navigation__container'
					);

				return {
					coreOpenButton,
					desktopNavigation,
					desktopNavigationGap: desktopNavigationList
						? getComputedStyle(desktopNavigationList).gap
						: null,
					logo,
					scrollWidth: document.documentElement.scrollWidth,
					viewportWidth: window.innerWidth,
				};
			});

			expect(metrics.logo).not.toBeNull();

			if (width < headerSearchDesktopBreakpoint) {
				expect(metrics.desktopNavigation?.display).toBe('none');
				expect(metrics.coreOpenButton?.display).not.toBe('none');
				expect(metrics.coreOpenButton?.width ?? 0).toBeGreaterThan(0);
			} else {
				expect(metrics.coreOpenButton?.display).toBe('none');
				expect(metrics.desktopNavigation?.display).not.toBe('none');
				expect(metrics.desktopNavigation?.width ?? 0).toBeGreaterThan(
					0
				);
				expect(metrics.logo).not.toBeNull();
				expect(
					metrics.desktopNavigation?.left ?? 0
				).toBeGreaterThanOrEqual(metrics.logo?.right ?? 0);
				expect(metrics.desktopNavigation?.y ?? 0).toBeLessThan(80);
				expect(metrics.desktopNavigationGap).toBe('13px');
				const joinUs = page.locator(
					'header .pns-primary-navigation a[href$="/membership/"]'
				);
				await expect(joinUs).toHaveText('Join Us');
				await expect(joinUs).toBeVisible();
			}

			expect(metrics.scrollWidth).toBeLessThanOrEqual(
				metrics.viewportWidth + 1
			);
		}
	}
);

test(
	taggedTitle(
		'footer logo keeps its top inset through the 782px spacing breakpoint',
		'fast',
		'layout',
		'desktop-only'
	),
	async ({ page }) => {
		for (const width of [780, 781, 782]) {
			await page.setViewportSize({ width, height: 900 });
			await page.goto('/', { waitUntil: 'domcontentloaded' });
			await waitForContractReady(page);

			const metrics = await page.evaluate(() => {
				const footer =
					document.querySelector<HTMLElement>('footer .pns-footer');
				const logo = document.querySelector<HTMLElement>(
					'footer .footer-logo img'
				);

				if (!footer || !logo) {
					return null;
				}

				return {
					logoInset:
						logo.getBoundingClientRect().top -
						footer.getBoundingClientRect().top,
				};
			});

			expect(metrics).not.toBeNull();
			expect(metrics?.logoInset ?? 0).toBeGreaterThanOrEqual(16);
		}
	}
);

test(
	taggedTitle(
		'non-edge Split Section slideshows retain a square media panel',
		'fast',
		'pattern',
		'layout'
	),
	async ({ page }) => {
		await page.setViewportSize({ width: 1440, height: 1000 });
		await page.goto('/pns-pattern-qa/');
		await page.waitForLoadState('domcontentloaded');
		await waitForStableAssets(page);

		const variants = await page.evaluate(() => {
			const section = document.querySelector<HTMLElement>(
				'.pns-split-section:has(.wp-block-jetpack-slideshow)'
			);

			if (!section) {
				return null;
			}

			const layoutClasses = [
				'is-style-pns-media-left',
				'is-style-pns-media-right',
				'is-style-pns-edge-media-left',
				'is-style-pns-edge-media-right',
			];
			const media = section.querySelector<HTMLElement>(
				'.pns-split-section__media-column'
			);
			const slideshow = section.querySelector<HTMLElement>(
				'.wp-block-jetpack-slideshow'
			);

			if (!media || !slideshow) {
				return null;
			}

			const measure = (variant: string) => {
				section.classList.remove(...layoutClasses);
				section.classList.add(`is-style-pns-${variant}`);

				const mediaBounds = media.getBoundingClientRect();
				const slideshowBounds = slideshow.getBoundingClientRect();

				return {
					media: {
						height: mediaBounds.height,
						width: mediaBounds.width,
					},
					slideshow: {
						height: slideshowBounds.height,
						width: slideshowBounds.width,
					},
				};
			};

			return {
				mediaLeft: measure('media-left'),
				mediaRight: measure('media-right'),
			};
		});

		expect(variants).not.toBeNull();

		if (!variants) {
			return;
		}

		for (const [name, variant] of Object.entries(variants)) {
			expect(
				variant.media.height,
				`${name} media column must remain square`
			).toBeCloseTo(variant.media.width, 0);
			expect(
				variant.slideshow.height,
				`${name} slideshow must fill the square media column`
			).toBeCloseTo(variant.slideshow.width, 0);
		}
	}
);

test(
	taggedTitle(
		'archive card grids step from three to two to one column at their responsive breakpoints',
		'fast',
		'archive',
		'layout',
		'desktop-only'
	),
	async ({ page }) => {
		for (const { width, expectedColumns, expectedRows } of [
			{ width: 1361, expectedColumns: 3, expectedRows: 1 },
			{ width: 1360, expectedColumns: 2, expectedRows: 2 },
			{ width: 1359, expectedColumns: 2, expectedRows: 2 },
			{ width: 801, expectedColumns: 2, expectedRows: 2 },
			{ width: 800, expectedColumns: 1, expectedRows: 3 },
		]) {
			await page.setViewportSize({ width, height: 900 });
			await page.goto('/news/', { waitUntil: 'domcontentloaded' });
			await waitForContractReady(page);

			const metrics = await page.evaluate(() => {
				const grid = document.querySelector<HTMLElement>(
					'.pns-news-more-section .wp-block-post-template.is-layout-grid'
				);

				if (!grid) {
					return null;
				}

				const cardTops = Array.from(grid.children)
					.slice(0, 3)
					.map((card) =>
						Math.round(card.getBoundingClientRect().top)
					);

				return {
					columnCount: getComputedStyle(grid)
						.gridTemplateColumns.trim()
						.split(/\s+/).length,
					firstCardRows: new Set(cardTops).size,
					scrollWidth: document.documentElement.scrollWidth,
					viewportWidth: window.innerWidth,
				};
			});

			expect(metrics).not.toBeNull();
			expect(metrics?.columnCount).toBe(expectedColumns);
			expect(metrics?.firstCardRows).toBe(expectedRows);
			expect(metrics?.scrollWidth ?? 0).toBeLessThanOrEqual(
				(metrics?.viewportWidth ?? 0) + 1
			);
		}
	}
);

test(
	taggedTitle(
		'code-backed PNS pattern QA page contracts',
		'fast',
		'pattern',
		'layout'
	),
	async ({ page }) => {
		await page.goto('/pns-pattern-qa/');
		await page.waitForLoadState('domcontentloaded');
		await waitForStableAssets(page);
		await page.waitForTimeout(3000);

		await expect(
			page.getByRole('heading', { name: 'PNS Pattern QA' })
		).toBeVisible();
		await expect(
			page.getByRole('heading', { name: 'pns/page-hero' })
		).toBeVisible();
		await expect(
			page.getByRole('heading', { name: 'pns/blockquote-cover' })
		).toBeVisible();
		await expect(
			page.getByRole('heading', { name: 'pns/blockquote-with-red-line' })
		).toBeVisible();
		await expect(
			page.getByRole('heading', { name: 'pns/basic-centred-content' })
		).toBeVisible();
		await expect(
			page.getByRole('heading', { name: 'pns/suffragette-stats' })
		).toBeVisible();
		await expect(
			page.getByRole('heading', { name: 'pns/entry-herstory-navigation' })
		).toBeVisible();
		await expect(
			page.getByRole('heading', { name: 'pns/two-columns' })
		).toBeVisible();
		await expect(
			page.getByRole('heading', { name: 'pns/split-section-image' })
		).toBeVisible();
		await expect(
			page.getByRole('heading', { name: 'pns/split-section-slideshow' })
		).toBeVisible();
		await expect(
			page.getByRole('heading', { name: 'pns/split-section-video' })
		).toBeVisible();
		await expect(
			page.getByRole('heading', { name: 'pns/text-only-section' })
		).toBeVisible();
		await expect(
			page.getByRole('heading', { name: 'pns/suffragette-hero' })
		).toBeVisible();
		await expect(
			page.getByRole('heading', { name: 'pns/suffragette-facts' })
		).toBeVisible();
		await expect(
			page.getByRole('heading', { name: 'pns/suffragette-image-strip' })
		).toBeVisible();
		await expect(
			page.getByRole('heading', { name: 'pns/image-strip' })
		).toBeVisible();

		const contracts = await page.evaluate(() => ({
			qaPageCount: document.querySelectorAll('.pns-pattern-qa').length,
			pageHeroCount: Array.from(
				document.querySelectorAll('.pns-page-hero img')
			).filter((image) =>
				image.getAttribute('src')?.includes('Artworks-Header-1.2.png')
			).length,
			suffragetteHeroCount: Array.from(
				document.querySelectorAll('.jumbo-header img')
			).filter((image) =>
				image.getAttribute('src')?.includes('Header_image@2x-1.png')
			).length,
			fullCoverCount: document.querySelectorAll(
				'.wp-block-cover.alignfull'
			).length,
			quoteCount: document.querySelectorAll('.wp-block-quote').length,
			redKeylineCount: Array.from(document.images).filter((image) =>
				image.currentSrc.includes('Red-Keyline.svg')
			).length,
			jetpackSlideshowCount: document.querySelectorAll(
				'.wp-block-jetpack-slideshow'
			).length,
			splitSectionCount:
				document.querySelectorAll('.pns-split-section').length,
			splitSectionBlockCount: document.querySelectorAll(
				'.wp-block-pns-split-section.pns-split-section'
			).length,
			youtubeEmbedCount: document.querySelectorAll(
				'.pns-split-section .wp-block-embed-youtube'
			).length,
			brokenImages: Array.from(document.images)
				.filter((image) => !image.complete || image.naturalWidth === 0)
				.map((image) => image.getAttribute('src')),
			horizontalOverflow:
				document.documentElement.scrollWidth > window.innerWidth + 1,
		}));

		expect(contracts.qaPageCount).toBe(1);
		expect(contracts.pageHeroCount).toBe(1);
		expect(contracts.suffragetteHeroCount).toBe(1);
		expect(contracts.fullCoverCount).toBeGreaterThanOrEqual(3);
		expect(contracts.quoteCount).toBe(2);
		expect(contracts.redKeylineCount).toBe(1);
		expect(contracts.jetpackSlideshowCount).toBeGreaterThanOrEqual(1);
		expect(contracts.splitSectionCount).toBeGreaterThanOrEqual(3);
		expect(contracts.splitSectionBlockCount).toBeGreaterThanOrEqual(3);
		expect(contracts.youtubeEmbedCount).toBe(1);
		expect(contracts.brokenImages).toEqual([]);
		expect(contracts.horizontalOverflow).toBe(false);
	}
);

test(
	taggedTitle('theme gradient and duotone token contracts', 'fast', 'layout'),
	async ({ page }) => {
		const expectedPaletteEntries = [
			['neutral-0', '#ffffff'],
			['neutral-50', '#F0F0F0'],
			['neutral-200', '#D3CDC3'],
			['neutral-700', '#4F4D49'],
			['neutral-800', '#2B2B2B'],
			['neutral-950', '#161616'],
			['brand-purple', '#3D207E'],
			['deep-purple', '#170145'],
			['brand-red', '#D4000F'],
			['accent-mint', '#7bdcb5'],
			['heritage-green', '#006B5F'],
			['brand-yellow', '#fad027'],
		];
		const expectedGradientSlugs = [
			'deep-purple-to-brand-purple',
			'brand-purple-to-brand-red',
			'brand-purple-to-accent-mint',
			'deep-purple-to-heritage-green',
			'brand-purple-to-heritage-green',
			'heritage-green-to-accent-mint',
			'heritage-green-to-brand-yellow',
			'brand-yellow-to-brand-red',
			'neutral-950-to-brand-purple',
			'neutral-50-to-neutral-0',
		];
		const expectedDuotoneSlugs = [
			'deep-purple-and-neutral-0',
			'brand-purple-and-neutral-0',
			'heritage-green-and-neutral-0',
			'brand-purple-and-accent-mint',
			'heritage-green-and-accent-mint',
			'neutral-800-and-neutral-50',
		];

		expect(themeJson.settings.color.defaultGradients).toBe(false);
		expect(themeJson.settings.color.defaultDuotone).toBe(false);
		expect(
			themeJson.settings.color.palette.map((color) => [
				color.slug,
				color.color,
			])
		).toEqual(expectedPaletteEntries);
		expect(
			themeJson.settings.color.gradients.map((gradient) => gradient.slug)
		).toEqual(expectedGradientSlugs);
		expect(
			themeJson.settings.color.duotone.map((duotone) => duotone.slug)
		).toEqual(expectedDuotoneSlugs);
		expect(
			themeJson.settings.color.duotone.map((duotone) => duotone.colors)
		).toEqual([
			['#170145', '#ffffff'],
			['#3D207E', '#ffffff'],
			['#006B5F', '#ffffff'],
			['#3D207E', '#7bdcb5'],
			['#006B5F', '#7bdcb5'],
			['#2B2B2B', '#F0F0F0'],
		]);

		await page.goto('/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		const contracts = await page.evaluate(() => {
			const rootStyles = getComputedStyle(document.documentElement);
			const gradientFixture = document.createElement('div');
			gradientFixture.className =
				'has-deep-purple-to-brand-purple-gradient-background has-background';
			gradientFixture.style.width = '10px';
			gradientFixture.style.height = '10px';
			document.body.appendChild(gradientFixture);

			const lightGradientFixture = document.createElement('div');
			lightGradientFixture.className =
				'has-neutral-50-to-neutral-0-gradient-background has-background';
			lightGradientFixture.style.width = '10px';
			lightGradientFixture.style.height = '10px';
			document.body.appendChild(lightGradientFixture);

			const unsetDuotoneImage = document.querySelector<HTMLElement>(
				'.wp-duotone-unset-2.wp-block-cover .wp-block-cover__image-background'
			);

			return {
				deepGradientVariable: rootStyles
					.getPropertyValue(
						'--wp--preset--gradient--deep-purple-to-brand-purple'
					)
					.trim(),
				lightGradientVariable: rootStyles
					.getPropertyValue(
						'--wp--preset--gradient--neutral-50-to-neutral-0'
					)
					.trim(),
				gradientBackground:
					getComputedStyle(gradientFixture).backgroundImage,
				lightGradientBackground:
					getComputedStyle(lightGradientFixture).backgroundImage,
				unsetDuotoneFilter: unsetDuotoneImage
					? getComputedStyle(unsetDuotoneImage).filter
					: null,
				unsetDuotoneCoverCount: document.querySelectorAll(
					'.wp-duotone-unset-2.wp-block-cover'
				).length,
			};
		});

		expect(contracts.deepGradientVariable).toBe(
			'linear-gradient(135deg, #170145 0%, #3D207E 100%)'
		);
		expect(contracts.lightGradientVariable).toBe(
			'linear-gradient(135deg, #F0F0F0 0%, #ffffff 100%)'
		);
		expect(contracts.gradientBackground).toContain('linear-gradient');
		expect(contracts.gradientBackground).toContain('rgb(23, 1, 69)');
		expect(contracts.gradientBackground).toContain('rgb(61, 32, 126)');
		expect(contracts.lightGradientBackground).toContain('linear-gradient');
		expect(contracts.lightGradientBackground).toContain(
			'rgb(240, 240, 240)'
		);
		expect(contracts.lightGradientBackground).toContain(
			'rgb(255, 255, 255)'
		);
		expect(contracts.unsetDuotoneCoverCount).toBeGreaterThan(0);
		expect(contracts.unsetDuotoneFilter).toBe('none');
	}
);

test(
	taggedTitle(
		'live adoption trial quote matches code-backed pattern',
		'audit',
		'snapshot',
		'pattern',
		'slow'
	),
	async ({ page }) => {
		await page.goto('/herstories/');
		await page.waitForLoadState('domcontentloaded');
		await waitForStableAssets(page);

		await expect(page.locator('body')).not.toContainText(
			/Warning:|Notice:|Deprecated:|Array to string conversion/
		);

		const quoteCover = page
			.locator(
				'.wp-block-cover.alignfull.pns-quotes.pns-blockquote-with-red-line'
			)
			.filter({
				has: page.locator('img[src*="Red-Keyline.svg"]'),
			})
			.first();

		await expect(quoteCover).toBeVisible();

		const contracts = await quoteCover.evaluate((element) => ({
			backgroundImage: element
				.querySelector<HTMLImageElement>(
					'.wp-block-cover__image-background'
				)
				?.getAttribute('src')
				?.includes('Quote_image_1.jpg'),
			redKeylineCount: Array.from(element.querySelectorAll('img')).filter(
				(image) =>
					(
						image.currentSrc ||
						image.getAttribute('src') ||
						''
					).includes('Red-Keyline.svg')
			).length,
			horizontalOverflow:
				document.documentElement.scrollWidth > window.innerWidth + 1,
			text: element.textContent
				?.replace(/\s+/g, ' ')
				.trim()
				.slice(0, 160),
		}));

		annotateAcceptedStateInventory('live adoption trial quote', contracts);
		expect(contracts.backgroundImage || contracts.redKeylineCount > 0).toBe(
			true
		);
		expect(contracts.horizontalOverflow).toBe(false);
		await expect(quoteCover).toHaveScreenshot(
			'herstories-live-adoption-quote.png',
			{
				animations: 'disabled',
				maxDiffPixelRatio: 0.02,
				timeout: 30000,
			}
		);
	}
);

test(
	taggedTitle(
		'mobile navigation drawer cascade contracts',
		'mobile-smoke',
		'mobile-fast',
		'mobile-full',
		'mobile-navigation'
	),
	async ({ page }) => {
		await page.goto('/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		const openButton = page.locator(
			'header .pns-primary-navigation > .wp-block-navigation__responsive-container-open'
		);
		const drawer = page.locator(
			'header .pns-primary-navigation > .wp-block-navigation__responsive-container'
		);
		const closeButton = drawer.locator(
			'.wp-block-navigation__responsive-container-close'
		);

		if (!(await openButton.isVisible())) {
			return;
		}

		await expect(page.locator('header .pns-mobile-navigation')).toHaveCount(
			0
		);

		const openIconBox = await openButton.locator('svg').boundingBox();

		await openButton.click();
		await expect(drawer).toHaveClass(/is-menu-open/, {
			timeout: 5000,
		});
		await expect(page.locator('html')).toHaveClass(/has-modal-open/);
		await expect(drawer).toContainText(/Herstories|Shop|About/i);
		const mobileSearchLink = drawer.getByRole('link', {
			exact: true,
			name: 'Search',
		});

		await expect(mobileSearchLink).toBeVisible();
		await expect(mobileSearchLink).toHaveAttribute('href', /\/search\/$/);
		await expect(mobileSearchLink).not.toHaveAttribute('aria-expanded');

		const mobileJoinUsLink = drawer.getByRole('link', {
			exact: true,
			name: 'Join Us',
		});

		await expect(mobileJoinUsLink).toBeVisible();
		await expect(mobileJoinUsLink).toHaveAttribute(
			'href',
			/\/membership\/$/
		);

		const focusIsInsideDrawer = await drawer.evaluate((element) =>
			element.contains(document.activeElement)
		);

		expect(focusIsInsideDrawer).toBe(true);
		await page.mouse.move(1, (page.viewportSize()?.height ?? 844) - 1);
		await closeButton.focus();

		const styles = await page.evaluate(() => {
			function read(selector: string) {
				const element = document.querySelector(selector);

				if (!element) {
					return null;
				}

				const computed = getComputedStyle(element);

				return {
					animationName: computed.animationName,
					backgroundColor: computed.backgroundColor,
					borderRightColor: computed.borderRightColor,
					borderRightWidth: computed.borderRightWidth,
					bottom: element.getBoundingClientRect().bottom,
					color: computed.color,
					display: computed.display,
					insetInlineEnd: computed.insetInlineEnd,
					rightFromViewport:
						window.innerWidth -
						element.getBoundingClientRect().right,
					marginRight: computed.marginRight,
					paddingBottom: computed.paddingBottom,
					paddingLeft: computed.paddingLeft,
					paddingRight: computed.paddingRight,
					paddingTop: computed.paddingTop,
					textDecorationLine: computed.textDecorationLine,
					top: element.getBoundingClientRect().top,
					transform: computed.transform,
				};
			}

			return {
				closeButton: read(
					'header .pns-primary-navigation > .wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation__responsive-container-close'
				),
				closeIcon: read(
					'header .pns-primary-navigation > .wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation__responsive-container-close svg'
				),
				drawer: read(
					'header .pns-primary-navigation > .wp-block-navigation__responsive-container.is-menu-open'
				),
				drawerItem: read(
					'header .pns-primary-navigation > .wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation-item'
				),
				drawerContent: read(
					'header .pns-primary-navigation > .wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation__responsive-container-content'
				),
				nestedDrawerItem: read(
					'header .pns-primary-navigation > .wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation__submenu-container .wp-block-navigation-item'
				),
				submenuContainer: read(
					'header .pns-primary-navigation > .wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation__submenu-container'
				),
				firstDrawerItemContent: read(
					'header .pns-primary-navigation > .wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation-item > .wp-block-navigation-item__content'
				),
			};
		});

		expect(
			Number.parseFloat(styles.drawer?.paddingTop ?? '0')
		).toBeGreaterThan(0);
		expect(
			Number.parseFloat(styles.drawer?.paddingRight ?? '0')
		).toBeGreaterThan(0);
		expect(
			Number.parseFloat(styles.drawer?.paddingBottom ?? '0')
		).toBeGreaterThan(0);
		expect(
			Number.parseFloat(styles.drawer?.paddingLeft ?? '0')
		).toBeGreaterThan(0);
		expect(styles.closeIcon?.top).toBeCloseTo(openIconBox?.y ?? 0, 1);
		expect(styles.closeIcon?.rightFromViewport).toBeCloseTo(
			(page.viewportSize()?.width ?? 0) -
				((openIconBox?.x ?? 0) + (openIconBox?.width ?? 0)),
			1
		);
		expect(styles.closeButton?.animationName).toBe('none');
		expect(styles.closeButton?.transform).toBe('none');
		expect(styles.drawerContent?.animationName).toBe(
			'pns-navigation-drawer-content-reveal'
		);
		expect(styles.drawerItem?.top).toBeGreaterThan(
			(styles.closeButton?.bottom ?? 0) + 8
		);
		expect(styles.submenuContainer?.paddingTop).toBe('0px');
		expect(styles.submenuContainer?.paddingRight).toBe('0px');
		expect(styles.submenuContainer?.paddingBottom).toBe('0px');
		expect(styles.submenuContainer?.paddingLeft).toBe('16px');
		expect(styles.submenuContainer?.display).toBe('none');
		expect(styles.drawerItem?.borderRightWidth).toBe('6px');
		expect(styles.drawerItem?.borderRightColor).toBe('rgba(0, 0, 0, 0)');
		expect(styles.drawerItem?.paddingRight).toBe('10px');
		expect(styles.drawerItem?.marginRight).toBe('10px');
		expect(styles.firstDrawerItemContent?.color).toBe('rgb(0, 0, 0)');
		expect(styles.firstDrawerItemContent?.backgroundColor).toBe(
			'rgba(0, 0, 0, 0)'
		);

		await page
			.locator(
				'header .pns-primary-navigation > .wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation-item'
			)
			.first()
			.hover({ force: true });

		const hoveredDrawerItem = await page.evaluate(() => {
			const element = document.querySelector(
				'header .pns-primary-navigation > .wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation-item'
			);
			const content = element?.querySelector(
				':scope > .wp-block-navigation-item__content'
			);

			if (!element || !content) {
				return null;
			}

			const itemComputed = getComputedStyle(element);
			const contentComputed = getComputedStyle(content);

			return {
				backgroundColor: contentComputed.backgroundColor,
				borderRightColor: itemComputed.borderRightColor,
				borderRightWidth: itemComputed.borderRightWidth,
				color: contentComputed.color,
				textDecorationLine: contentComputed.textDecorationLine,
			};
		});

		expect(hoveredDrawerItem?.borderRightWidth).toBe('6px');
		expect(hoveredDrawerItem?.borderRightColor).toBe('rgba(0, 0, 0, 0)');
		expect(hoveredDrawerItem?.backgroundColor).toBe('rgb(212, 0, 15)');
		expect(hoveredDrawerItem?.color).toBe('rgb(255, 255, 255)');
		expect(hoveredDrawerItem?.textDecorationLine).toBe('none');

		const firstSubmenuParent = page
			.locator(
				'header .pns-primary-navigation > .wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation-item.has-child'
			)
			.first();
		const firstSubmenuParentLabel = firstSubmenuParent.locator(
			':scope > .wp-block-navigation-item__content'
		);
		const firstSubmenuToggle = firstSubmenuParent.locator(
			':scope > .wp-block-navigation-submenu__toggle'
		);
		const firstSubmenuContainer = firstSubmenuParent.locator(
			':scope > .wp-block-navigation__submenu-container'
		);

		await expect(firstSubmenuToggle).toHaveAttribute(
			'aria-expanded',
			'false'
		);
		await expect(firstSubmenuContainer).not.toBeVisible();

		await firstSubmenuParentLabel.click();

		await expect(firstSubmenuToggle).toHaveAttribute(
			'aria-expanded',
			'true'
		);
		await expect(firstSubmenuContainer).toBeVisible();

		const expandedParentStyles = await firstSubmenuParentLabel.evaluate(
			(element) => {
				const computed = getComputedStyle(element);

				return {
					backgroundColor: computed.backgroundColor,
					color: computed.color,
					textDecorationLine: computed.textDecorationLine,
				};
			}
		);

		expect(expandedParentStyles.backgroundColor).toBe('rgb(212, 0, 15)');
		expect(expandedParentStyles.color).toBe('rgb(255, 255, 255)');
		expect(expandedParentStyles.textDecorationLine).toBe('none');

		const expandedNestedStyles = await firstSubmenuContainer
			.locator('.wp-block-navigation-item')
			.first()
			.evaluate((element) => {
				const computed = getComputedStyle(element);

				return {
					marginRight: computed.marginRight,
					paddingRight: computed.paddingRight,
				};
			});

		expect(expandedNestedStyles.paddingRight).toBe('25px');
		expect(expandedNestedStyles.marginRight).toBe('-12px');

		await page
			.locator(
				'header .pns-primary-navigation > .wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation__submenu-container a'
			)
			.first()
			.hover({ force: true });

		const hoveredSubLink = await page.evaluate(() => {
			const element = document.querySelector(
				'header .pns-primary-navigation > .wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation__submenu-container a'
			);

			if (!element) {
				return null;
			}

			const computed = getComputedStyle(element);

			return {
				backgroundColor: computed.backgroundColor,
				color: computed.color,
				textDecorationLine: computed.textDecorationLine,
			};
		});

		expect(hoveredSubLink?.backgroundColor).toBe('rgb(212, 0, 15)');
		expect(hoveredSubLink?.color).toBe('rgb(255, 255, 255)');
		expect(hoveredSubLink?.textDecorationLine).toBe('none');

		await closeButton.focus();
		await page.keyboard.press('Escape');
		await expect(drawer).not.toHaveClass(/is-menu-open/);
		await expect(page.locator('html')).not.toHaveClass(/has-modal-open/);
		await expect(openButton).toBeFocused();
	}
);

test(
	taggedTitle(
		'herstory cascade contracts',
		'fast',
		'mobile-fast',
		'mobile-full',
		'layout',
		'mobile-layout',
		'pattern'
	),
	async ({ page }) => {
		await page.goto('/herstories/mary-barbour/');
		await page.waitForLoadState('domcontentloaded');
		await waitForContractReady(page);

		const splitSection = page.locator('.pns-split-section').first();
		await splitSection.scrollIntoViewIfNeeded();
		await page.waitForFunction(() => {
			const copyColumn = document.querySelector<HTMLElement>(
				'.pns-split-section .pns-split-section__copy-column'
			);

			if (!copyColumn) {
				return false;
			}

			const transform = getComputedStyle(copyColumn).transform;

			return (
				transform === 'none' || transform === 'matrix(1, 0, 0, 1, 0, 0)'
			);
		});

		const styles = await page.evaluate(() => {
			const manualColorFixture = document.createElement('div');
			manualColorFixture.id = 'pns-entry-navigation-manual-color-fixture';
			manualColorFixture.className =
				'wp-block-group pns-section pns-layout pns-entry-navigation has-brand-red-color has-text-color';
			manualColorFixture.innerHTML = `
			<div class="wp-block-buttons is-layout-flex">
				<div class="wp-block-button pns-entry-navigation__action previous">
					<a class="wp-block-button__link wp-element-button" href="#">Previous</a>
				</div>
				<div class="wp-block-button">
					<a class="wp-block-button__link has-brand-red-color has-brand-yellow-background-color has-text-color has-background wp-element-button" href="#">Manual button</a>
				</div>
			</div>
		`;
			document.body.appendChild(manualColorFixture);

			const purpleBackgroundFixture = document.createElement('div');
			purpleBackgroundFixture.id =
				'pns-entry-navigation-purple-background-fixture';
			purpleBackgroundFixture.className =
				'wp-block-group pns-section pns-layout pns-entry-navigation has-brand-purple-background-color has-background';
			purpleBackgroundFixture.innerHTML = `
			<div class="wp-block-buttons is-layout-flex">
				<div class="wp-block-button pns-entry-navigation__action previous">
					<a class="wp-block-button__link wp-element-button" href="#">Previous</a>
				</div>
				<div class="wp-block-button">
					<a class="wp-block-button__link wp-element-button" href="#">Back to Herstories</a>
				</div>
			</div>
		`;
			document.body.appendChild(purpleBackgroundFixture);

			function read(selector: string, pseudo?: string) {
				const element = document.querySelector(selector);

				if (!element) {
					return null;
				}

				const computed = getComputedStyle(element, pseudo);

				return {
					backgroundColor: computed.backgroundColor,
					borderLeftWidth: computed.borderLeftWidth,
					boxShadow: computed.boxShadow,
					color: computed.color,
					content: computed.content,
					display: computed.display,
					fontFamily: computed.fontFamily,
					fontSize: computed.fontSize,
					fontWeight: computed.fontWeight,
					listStyleType: computed.listStyleType,
					marginBottom: computed.marginBottom,
					marginLeft: computed.marginLeft,
					maxWidth: computed.maxWidth,
					paddingBottom: computed.paddingBottom,
					paddingLeft: computed.paddingLeft,
					paddingRight: computed.paddingRight,
					paddingTop: computed.paddingTop,
					pnsSectionText: computed
						.getPropertyValue('--pns-section-text')
						.trim(),
					pnsSurfaceButtonBackground: computed
						.getPropertyValue('--pns-surface-button-background')
						.trim(),
					pnsSurfaceButtonColor: computed
						.getPropertyValue('--pns-surface-button-color')
						.trim(),
					pnsSurfaceText: computed
						.getPropertyValue('--pns-surface-text')
						.trim(),
					width: computed.width,
				};
			}

			return {
				activeDates: read('.active-dates'),
				activeDatesList: read('.active-dates ul'),
				activeDatesStrong: read('.active-dates strong'),
				entryNavigationAction: read(
					'.pns-template-herstory-single .pns-entry-navigation .pns-entry-navigation__action .wp-block-button__link, .pns-template-herstory-single .pns-entry-navigation .wp-block-post-navigation-link.pns-entry-navigation__action a'
				),
				entryNavigationActionAfter: read(
					'.pns-template-herstory-single .pns-entry-navigation .pns-entry-navigation__action .wp-block-button__link, .pns-template-herstory-single .pns-entry-navigation .wp-block-post-navigation-link.pns-entry-navigation__action a',
					'::after'
				),
				manualFixtureButton: read(
					'#pns-entry-navigation-manual-color-fixture .wp-block-button:not(.pns-entry-navigation__action) .wp-block-button__link'
				),
				manualFixtureEntryNavigationAction: read(
					'#pns-entry-navigation-manual-color-fixture .pns-entry-navigation__action .wp-block-button__link'
				),
				purpleBackgroundFixtureButton: read(
					'#pns-entry-navigation-purple-background-fixture .wp-block-button:not(.pns-entry-navigation__action) .wp-block-button__link'
				),
				purpleBackgroundFixtureEntryNavigationAction: read(
					'#pns-entry-navigation-purple-background-fixture .pns-entry-navigation__action .wp-block-button__link'
				),
				funFactsLi: read('.fun-facts li'),
				funFactsLiBefore: read('.fun-facts li', '::before'),
				markup: {
					funFactsLiCount:
						document.querySelectorAll('.fun-facts li').length,
					funFactsNestedFlowUlCount: document.querySelectorAll(
						'.fun-facts .is-layout-flow ul'
					).length,
					funFactsNestedUlCount:
						document.querySelectorAll('.fun-facts ul').length,
				},
				pageHeading: read('h1.wp-block-heading, h2.wp-block-heading'),
				quote: read('.wp-block-quote'),
				quoteText: read('.wp-block-quote p'),
				splitSectionLayout: (() => {
					const section =
						document.querySelector<HTMLElement>(
							'.pns-split-section'
						);
					const copy = section?.querySelector<HTMLElement>(
						'.pns-split-section__copy'
					);
					const heading =
						copy?.querySelector<HTMLElement>('.wp-block-heading');
					const media = section?.querySelector<HTMLElement>(
						'.pns-split-section__media-column'
					);
					const contentProbe = document.createElement('div');

					contentProbe.style.boxSizing = 'border-box';
					contentProbe.style.inlineSize =
						'var(--wp--style--global--content-size, 44rem)';
					contentProbe.style.position = 'absolute';
					contentProbe.style.visibility = 'hidden';
					document.body.appendChild(contentProbe);

					const contentWidth =
						contentProbe.getBoundingClientRect().width;
					contentProbe.remove();

					function rect(element?: HTMLElement | null) {
						if (!element) {
							return null;
						}

						const bounds = element.getBoundingClientRect();
						const computed = getComputedStyle(element);

						return {
							bottom: bounds.bottom,
							left: bounds.left,
							right: bounds.right,
							top: bounds.top,
							width: bounds.width,
							paddingBottom: parseFloat(computed.paddingBottom),
							paddingLeft: parseFloat(computed.paddingLeft),
							paddingRight: parseFloat(computed.paddingRight),
							paddingTop: parseFloat(computed.paddingTop),
						};
					}

					return {
						className: section?.className ?? null,
						contentWidth,
						copy: rect(copy),
						heading: rect(heading),
						media: rect(media),
						viewportWidth: window.innerWidth,
					};
				})(),
			};
		});

		expect(styles.activeDates?.maxWidth).toBe('320px');
		expect(styles.activeDatesStrong?.fontFamily).toContain('Rubik');
		expect(styles.activeDatesStrong?.fontWeight).toBe('800');
		expect(styles.activeDatesList?.paddingTop).toBe('0px');
		expect(styles.activeDatesList?.paddingRight).toBe('0px');
		expect(styles.activeDatesList?.paddingBottom).toBe('0px');
		expect(styles.activeDatesList?.paddingLeft).toBe('0px');
		if (styles.entryNavigationAction) {
			expect(styles.entryNavigationAction.color).toBe('rgb(61, 32, 126)');
			expect(styles.entryNavigationAction.backgroundColor).toBe(
				'rgba(0, 0, 0, 0)'
			);
			expect(styles.entryNavigationAction.boxShadow).toContain(
				'0px 0px 0px'
			);
			expect(styles.entryNavigationActionAfter?.backgroundColor).toBe(
				'rgba(0, 0, 0, 0)'
			);
		}
		expect(styles.manualFixtureEntryNavigationAction?.color).toBe(
			'rgb(212, 0, 15)'
		);
		expect(styles.manualFixtureEntryNavigationAction?.backgroundColor).toBe(
			'rgba(0, 0, 0, 0)'
		);
		expect(styles.manualFixtureButton?.color).toBe('rgb(212, 0, 15)');
		expect(styles.manualFixtureButton?.backgroundColor).toBe(
			'rgb(250, 208, 39)'
		);
		expect(styles.purpleBackgroundFixtureEntryNavigationAction?.color).toBe(
			'rgb(255, 255, 255)'
		);
		expect(
			styles.purpleBackgroundFixtureEntryNavigationAction?.backgroundColor
		).toBe('rgba(0, 0, 0, 0)');
		expect(styles.purpleBackgroundFixtureButton?.color).toBe(
			'rgb(61, 32, 126)'
		);
		expect(styles.purpleBackgroundFixtureButton?.backgroundColor).toBe(
			'rgb(255, 255, 255)'
		);
		expect([
			'#ffffff',
			'var(--wp--preset--color--neutral-0)',
			'var(--wp--custom--color--text--inverse)',
		]).toContain(styles.purpleBackgroundFixtureButton?.pnsSurfaceText);
		expect([
			'#ffffff',
			'var(--pns-surface-text)',
			'var(--wp--custom--color--text--inverse)',
		]).toContain(styles.purpleBackgroundFixtureButton?.pnsSectionText);
		expect([
			'#ffffff',
			'var(--wp--preset--color--neutral-0)',
			'var(--wp--custom--color--button--inverse-background)',
		]).toContain(
			styles.purpleBackgroundFixtureButton?.pnsSurfaceButtonBackground
		);
		expect([
			'#3d207e',
			'#3D207E',
			'var(--wp--preset--color--brand-purple)',
			'var(--wp--custom--color--button--inverse-text)',
		]).toContain(
			styles.purpleBackgroundFixtureButton?.pnsSurfaceButtonColor
		);
		expect(styles.markup.funFactsLiCount).toBe(5);
		expect(styles.markup.funFactsNestedFlowUlCount).toBe(0);
		expect(styles.markup.funFactsNestedUlCount).toBe(0);
		expect(styles.funFactsLi?.listStyleType).toBe('none');
		expect(styles.funFactsLi?.marginBottom).toBe('16px');
		expect(styles.funFactsLiBefore?.content).toBe('"•"');
		expect(styles.funFactsLiBefore?.color).toBe('rgb(123, 220, 181)');
		expect(styles.funFactsLiBefore?.display).toBe('inline-block');
		expect(styles.funFactsLiBefore?.fontWeight).toBe('700');
		expect(
			Number.parseFloat(styles.funFactsLiBefore?.width ?? '0')
		).toBeCloseTo(Number.parseFloat(styles.funFactsLi?.fontSize ?? '0'), 1);
		expect(
			Number.parseFloat(styles.funFactsLiBefore?.marginLeft ?? '0')
		).toBeCloseTo(
			-Number.parseFloat(styles.funFactsLi?.fontSize ?? '0'),
			1
		);
		expect(styles.pageHeading?.fontFamily).toContain('Rubik');
		expect(styles.pageHeading?.fontWeight).toBe('800');
		expect(styles.quote?.borderLeftWidth).toBe('0px');
		const viewportWidth = page.viewportSize()?.width ?? 0;
		const expectedQuotePadding = viewportWidth >= 600 ? '32px' : '16px';

		expect(styles.quote?.paddingLeft).toBe(expectedQuotePadding);
		expect(styles.quote?.paddingRight).toBe(expectedQuotePadding);
		expect(styles.quoteText?.fontFamily).toContain('Rubik');

		const splitLayout = styles.splitSectionLayout;
		expect(splitLayout.copy).not.toBeNull();
		expect(splitLayout.heading).not.toBeNull();
		expect(splitLayout.media).not.toBeNull();
		expect(splitLayout.className).toContain(
			'is-style-pns-edge-media-right'
		);
		expect(splitLayout.copy?.width ?? 0).toBeLessThanOrEqual(
			Math.min(splitLayout.contentWidth, splitLayout.viewportWidth) + 1
		);
		const expectedCopyPadding = viewportWidth >= 782 ? 32 : 16;
		expect(splitLayout.copy?.paddingTop).toBe(expectedCopyPadding);
		expect(splitLayout.copy?.paddingBottom).toBe(expectedCopyPadding);
		expect(splitLayout.heading?.top ?? 0).toBeGreaterThanOrEqual(
			(splitLayout.copy?.top ?? 0) + expectedCopyPadding - 1
		);
		expect(splitLayout.heading?.left ?? 0).toBeGreaterThanOrEqual(
			(splitLayout.copy?.left ?? 0) - 1
		);
		expect(splitLayout.heading?.right ?? 0).toBeLessThanOrEqual(
			(splitLayout.copy?.right ?? 0) + 1
		);
		if (viewportWidth >= 782) {
			expect(splitLayout.copy?.paddingLeft).toBe(0);
			expect(splitLayout.copy?.paddingRight).toBe(expectedCopyPadding);
			expect(splitLayout.media?.left ?? 0).toBeGreaterThanOrEqual(
				(splitLayout.copy?.right ?? 0) - 1
			);
			expect(splitLayout.media?.right ?? 0).toBeGreaterThanOrEqual(
				viewportWidth - 1
			);
			expect(splitLayout.heading?.right ?? 0).toBeLessThanOrEqual(
				(splitLayout.media?.left ?? 0) - expectedCopyPadding + 1
			);
		} else {
			expect(splitLayout.copy?.paddingLeft).toBe(expectedCopyPadding);
			expect(splitLayout.copy?.paddingRight).toBe(expectedCopyPadding);
			expect(splitLayout.media?.top ?? 0).toBeGreaterThanOrEqual(
				(splitLayout.copy?.bottom ?? 0) - 1
			);
			expect(splitLayout.media?.left ?? 0).toBeLessThanOrEqual(
				(splitLayout.copy?.left ?? 0) + 1
			);
			expect(splitLayout.media?.right ?? 0).toBeGreaterThanOrEqual(
				(splitLayout.copy?.right ?? 0) - 1
			);
		}
	}
);

test(
	taggedTitle(
		'giveaway form cascade contracts',
		'emailoctopus',
		'vendor',
		'slow'
	),
	async ({ page }) => {
		await page.goto('/edu-giveaway/');
		await page.waitForLoadState('domcontentloaded');
		await waitForStableAssets(page);
		await page
			.locator(
				'[data-form="5e60a222-ff72-11ef-8552-6b8c59d486cb"] .nurture-container'
			)
			.waitFor({ state: 'attached', timeout: 10000 });
		await expect(
			page.locator('[data-form="5e60a222-ff72-11ef-8552-6b8c59d486cb"]')
		).toHaveCount(1);
		await expect(
			page.locator('[data-form="5e60a222-ff72-11ef-8552-6b8c59d486cb"]')
		).toHaveAttribute('data-version', '2');
		await expect(
			page.locator('link[href*="emailoctopus"][href*="legacy-frontend"]')
		).toHaveCount(0);

		const styles = await page.evaluate(() => {
			const rootStyles = getComputedStyle(document.documentElement);

			for (const element of document.querySelectorAll<HTMLElement>(
				'.emailoctopus-form, .emailoctopus-form *'
			)) {
				element.style.visibility = 'visible';
			}

			function read(selector: string, focus = false) {
				const element = document.querySelector<HTMLElement>(selector);

				if (!element) {
					return null;
				}

				if (focus) {
					element.focus();
				}

				const computed = getComputedStyle(element);

				return {
					borderColor: computed.borderColor,
					borderRadius: computed.borderRadius,
					boxShadow: computed.boxShadow,
					display: computed.display,
					fontFamily: computed.fontFamily,
					gridTemplateColumns: computed.gridTemplateColumns,
					fontWeight: computed.fontWeight,
					paddingBottom: computed.paddingBottom,
					paddingLeft: computed.paddingLeft,
					paddingRight: computed.paddingRight,
					paddingTop: computed.paddingTop,
					visibility: computed.visibility,
				};
			}

			return {
				container: read(
					'[data-form="5e60a222-ff72-11ef-8552-6b8c59d486cb"] .nurture-container'
				),
				firstInput: read(
					'.emailoctopus-form input[type="email"], .emailoctopus-form input[type="text"]'
				),
				focusedInput: read('.emailoctopus-form .form-control', true),
				heading: read('h1, h2.wp-block-heading, .wp-block-heading'),
				label: read('.emailoctopus-form label'),
				paddingWrapper: read(
					'[data-form="5e60a222-ff72-11ef-8552-6b8c59d486cb"] .align-mid.pt-5.pb-5'
				),
				poweredBy: read('[eo-block="powered-by"]'),
				secondColumn: read(
					'[data-form="5e60a222-ff72-11ef-8552-6b8c59d486cb"] .nurture-container > div:nth-child(2)'
				),
				submit: read('.emailoctopus-form input[type="submit"]'),
				tokens: {
					buttonBorderRadius: rootStyles
						.getPropertyValue('--pns--button--border-radius')
						.trim(),
					fieldBorderColor: rootStyles
						.getPropertyValue('--pns--form-field--border-color')
						.trim(),
					fieldFocusBorderColor: rootStyles
						.getPropertyValue(
							'--pns--form-field--focus-border-color'
						)
						.trim(),
					fieldPadding: rootStyles
						.getPropertyValue('--pns--form-field--padding')
						.trim(),
				},
			};
		});

		expect(styles.container?.display).toBe('grid');
		expect(styles.container?.gridTemplateColumns.split(' ')).toHaveLength(
			1
		);
		expect(styles.secondColumn?.display).toBe('none');
		expect(styles.paddingWrapper?.paddingTop).toBe('0px');
		expect(styles.paddingWrapper?.paddingBottom).toBe('0px');
		expect(styles.poweredBy?.display).toBe('none');
		expect(styles.poweredBy?.visibility).toBe('hidden');
		expect(styles.tokens.buttonBorderRadius).toBe('0px');
		expect(styles.tokens.fieldBorderColor).toBe('#8c8f94');
		expect(styles.tokens.fieldFocusBorderColor).toBe('#000');
		expect(styles.tokens.fieldPadding).toBe('1.625rem');
		expect(styles.firstInput?.borderRadius).toBe('0px');
		expect(styles.firstInput?.paddingTop).toBe('26px');
		expect(styles.firstInput?.paddingRight).toBe('26px');
		expect(styles.firstInput?.paddingBottom).toBe('26px');
		expect(styles.firstInput?.paddingLeft).toBe('26px');
		expect(styles.focusedInput?.borderColor).toBe('rgb(0, 0, 0)');
		expect(styles.focusedInput?.boxShadow).toContain('rgb(0, 0, 0)');
		expect(styles.heading?.fontFamily).toContain('Rubik');
		expect(styles.heading?.fontWeight).toBe('800');
		expect(styles.label?.display).toBe('block');
		expect(styles.label?.fontWeight).toBe('700');
		expect(styles.submit?.borderRadius).toBe('0px');
		expect(styles.submit?.paddingTop).toBe('20px');
		expect(styles.submit?.paddingRight).toBe('30px');
		expect(styles.submit?.paddingBottom).toBe('20px');
		expect(styles.submit?.paddingLeft).toBe('30px');
	}
);

test(
	taggedTitle(
		'homepage EmailOctopus embed contracts',
		'emailoctopus',
		'vendor'
	),
	async ({ page }) => {
		await page.goto('/');
		await page.waitForLoadState('domcontentloaded');
		await waitForStableAssets(page);
		await expect(
			page.locator('[data-form="3637e2c8-ff87-11ef-8123-45a2d1a97169"]')
		).toHaveCount(1);
		await expect(
			page.locator('[data-form="3637e2c8-ff87-11ef-8123-45a2d1a97169"]')
		).toHaveAttribute('data-version', '2');
		await expect(
			page.locator('link[href*="emailoctopus"][href*="legacy-frontend"]')
		).toHaveCount(0);
		await page
			.locator(
				'[data-form="3637e2c8-ff87-11ef-8123-45a2d1a97169"] .emailoctopus-form'
			)
			.waitFor({ state: 'attached', timeout: 10000 });

		const styles = await page.evaluate(() => {
			function read(selector: string) {
				const element = document.querySelector<HTMLElement>(selector);

				if (!element) {
					return null;
				}

				const computed = getComputedStyle(element);

				return {
					borderRadius: computed.borderRadius,
					display: computed.display,
					fontWeight: computed.fontWeight,
					paddingLeft: computed.paddingLeft,
					paddingRight: computed.paddingRight,
				};
			}

			return {
				firstInput: read(
					'[data-form="3637e2c8-ff87-11ef-8123-45a2d1a97169"] .emailoctopus-form input[type="email"], [data-form="3637e2c8-ff87-11ef-8123-45a2d1a97169"] .emailoctopus-form input[type="text"]'
				),
				label: read(
					'[data-form="3637e2c8-ff87-11ef-8123-45a2d1a97169"] .emailoctopus-form label'
				),
				submit: read(
					'[data-form="3637e2c8-ff87-11ef-8123-45a2d1a97169"] .emailoctopus-form input[type="submit"]'
				),
			};
		});

		expect(styles.firstInput?.borderRadius).toBe('0px');
		expect(styles.firstInput?.paddingLeft).toBe('26px');
		expect(styles.firstInput?.paddingRight).toBe('26px');
		expect(styles.label?.display).toBe('block');
		expect(styles.label?.fontWeight).toBe('700');
		expect(styles.submit?.borderRadius).toBe('0px');
	}
);

test(
	taggedTitle(
		'shop Ecwid cascade contracts',
		'shop',
		'ecwid',
		'vendor',
		'slow'
	),
	async ({ page }) => {
		await page.goto('/shop/');
		await page.waitForLoadState('domcontentloaded');
		await waitForStableAssets(page);
		await page
			.locator('.grid-product__title-inner')
			.first()
			.waitFor({ state: 'attached', timeout: 10000 });

		const styles = await page.evaluate(() => {
			const mediumFontProbe = document.createElement('span');
			mediumFontProbe.style.fontSize =
				'var(--wp--preset--font-size--text-lead)';
			document.body.appendChild(mediumFontProbe);
			const mediumFontSize = getComputedStyle(mediumFontProbe).fontSize;
			mediumFontProbe.remove();

			function read(selector: string) {
				const element = document.querySelector(selector);

				if (!element) {
					return null;
				}

				const computed = getComputedStyle(element);

				return {
					color: computed.color,
					display: computed.display,
					fontSize: computed.fontSize,
					textFillColor: computed.webkitTextFillColor,
				};
			}

			return {
				categoryGrid: (() => {
					const frame = document.querySelector<HTMLElement>(
						'.pns-shop-storefront'
					);
					const grid = document.querySelector<HTMLElement>(
						'.pns-shop-storefront .ec-store__category-page .grid__products'
					);
					const container = grid?.closest<HTMLElement>(
						'[id="static-ec-store-container"], [id="dynamic-ec-store-container"]'
					);
					const products = Array.from(
						grid?.querySelectorAll<HTMLElement>(
							':scope > .grid-product'
						) ?? []
					);

					if (
						!frame ||
						!grid ||
						!container ||
						products.length === 0
					) {
						return null;
					}

					const firstProductRect =
						products[0].getBoundingClientRect();
					const rowProducts = products.filter((product) => {
						const rect = product.getBoundingClientRect();

						return Math.abs(rect.top - firstProductRect.top) <= 2;
					});
					const firstMedia =
						rowProducts[0]?.querySelector<HTMLElement>(
							'.grid-product__image'
						);
					const lastMedia = rowProducts[
						rowProducts.length - 1
					]?.querySelector<HTMLElement>('.grid-product__image');

					if (!firstMedia || !lastMedia) {
						return null;
					}

					const frameRect = frame.getBoundingClientRect();
					const containerRect = container.getBoundingClientRect();
					const firstMediaRect = firstMedia.getBoundingClientRect();
					const lastMediaRect = lastMedia.getBoundingClientRect();

					return {
						firstRowProductCount: rowProducts.length,
						leftMediaGutter: firstMediaRect.left - frameRect.left,
						rightMediaGutter: frameRect.right - lastMediaRect.right,
						lastMediaInsideContainer:
							containerRect.right - lastMediaRect.right,
					};
				})(),
				mediumFontSize,
				markup: {
					messengerCount: document.querySelectorAll(
						'.ec-fbmessenger-chat'
					).length,
					priceCount: document.querySelectorAll(
						'.grid-product__price'
					).length,
					titleCount: document.querySelectorAll(
						'.grid-product__title-inner'
					).length,
				},
				messenger: read('.ec-fbmessenger-chat'),
				price: read('.grid-product__price'),
				title: read('.grid-product__title-inner'),
			};
		});

		expect(styles.markup.titleCount).toBeGreaterThan(0);
		expect(styles.markup.priceCount).toBeGreaterThan(0);
		expect(styles.markup.messengerCount).toBeGreaterThan(0);
		expect(styles.categoryGrid).not.toBeNull();
		expect(styles.categoryGrid?.firstRowProductCount ?? 0).toBeGreaterThan(
			0
		);
		expect(
			Math.abs(
				(styles.categoryGrid?.leftMediaGutter ?? 0) -
					(styles.categoryGrid?.rightMediaGutter ?? 0)
			)
		).toBeLessThanOrEqual(2);
		expect(
			styles.categoryGrid?.lastMediaInsideContainer ?? 0
		).toBeGreaterThanOrEqual(-1);
		expect(styles.title?.fontSize).toBe(styles.mediumFontSize);
		expect(styles.price?.fontSize).toBe('16px');
		expect(styles.price?.color).toBe('rgb(0, 0, 0)');
		expect(styles.messenger?.display).toBe('none');
	}
);

test(
	taggedTitle(
		'Ecwid product detail text remains readable on white storefront surfaces',
		'shop',
		'ecwid',
		'vendor',
		'slow'
	),
	async ({ page }) => {
		await page.goto('/shop/Scottish-Suffragette-Posters-p612143977');
		await page.waitForLoadState('domcontentloaded');
		await waitForStableAssets(page);
		await page
			.locator('.product-details__product-title')
			.first()
			.waitFor({ state: 'attached', timeout: 10000 });

		const styles = await page.evaluate(() => {
			function read(selector: string) {
				const element = document.querySelector(selector);

				if (!element) {
					return null;
				}

				const computed = getComputedStyle(element);

				return {
					color: computed.color,
					fontFamily: computed.fontFamily,
					fontWeight: computed.fontWeight,
					stroke: computed.stroke,
					textFillColor: computed.webkitTextFillColor,
				};
			}

			return {
				minicart: read('.ec-minicart'),
				minicartIcon: read('.ec-minicart__icon svg'),
				minicartIconPath: read(
					'.ec-minicart__icon svg [stroke="currentColor"]'
				),
				optionText: read('.ec-store .form-control__text'),
				price: read('.ec-store .product-details__product-price-row'),
				select: read('.ec-store .form-control__select'),
				title: read('.ec-store .product-details__product-title'),
			};
		});

		expect(styles.title?.color).not.toBe('rgb(255, 255, 255)');
		expect(styles.title?.textFillColor).not.toBe('rgb(255, 255, 255)');
		expect(styles.title?.fontFamily).toContain('Rubik');
		expect(styles.title?.fontWeight).toBe('800');
		expect(styles.price?.color).toBe('rgb(0, 0, 0)');
		expect(styles.select?.color).toBe('rgb(0, 0, 0)');
		expect(styles.optionText?.color).toBe('rgb(0, 0, 0)');
		expect(styles.optionText?.textFillColor).toBe('rgb(0, 0, 0)');
		expect(styles.minicart?.color).toBe('rgb(0, 0, 0)');
		expect(styles.minicartIcon?.color).toBe('rgb(0, 0, 0)');
		expect(styles.minicartIconPath?.color).toBe('rgb(0, 0, 0)');
		expect(styles.minicartIconPath?.stroke).toBe('rgb(0, 0, 0)');
	}
);

const relatedProductRoutes = [
	{
		cardCount: 4,
		path: '/shop/Suffragette-Penny-A-Unique-Handmade-Replica-&-Tiny-act-of-rebellion-p814453695',
	},
	{
		cardCount: 3,
		path: '/shop/Scotlands-Suffrage-History-Education-Pack-p781515145',
	},
	{
		cardCount: 5,
		path: '/shop/Rent-Striker-and-Suffragette-Posters-1st-Edition-p398671992',
	},
];

for (const route of relatedProductRoutes) {
	test(
		taggedTitle(
			`Ecwid related product row respects max card width: ${route.cardCount} cards`,
			'shop',
			'ecwid',
			'vendor',
			'slow'
		),
		async ({ page }) => {
			await page.goto(route.path);
			await page.waitForLoadState('domcontentloaded');
			await waitForStableAssets(page);
			await page
				.locator('.ec-related-products .grid-product')
				.first()
				.waitFor({ state: 'attached', timeout: 10000 });

			const relatedProducts = await page.evaluate(() => {
				const grid = document.querySelector<HTMLElement>(
					'.ec-store .ec-related-products .grid__products'
				);
				const products = Array.from(
					document.querySelectorAll<HTMLElement>(
						'.ec-store .ec-related-products .grid__products > .grid-product'
					)
				);

				if (!grid || products.length === 0) {
					return null;
				}

				const gridRect = grid.getBoundingClientRect();
				const productRects = products.map((product) =>
					product.getBoundingClientRect()
				);
				const firstRowTop = productRects[0]?.top ?? 0;
				const firstRowRects = productRects.filter(
					(rect) => Math.abs(rect.top - firstRowTop) <= 2
				);
				const firstRowLeft = Math.min(
					...firstRowRects.map((rect) => rect.left)
				);
				const firstRowRight = Math.max(
					...firstRowRects.map((rect) => rect.right)
				);
				const maxColumns =
					window.innerWidth <= 600
						? 1
						: window.innerWidth <= 999
							? 2
							: 4;

				return {
					cardCount: products.length,
					expectedFirstRowCount: Math.min(
						products.length,
						maxColumns
					),
					expectedCardWidth: gridRect.width / maxColumns,
					firstCardWidth: productRects[0]?.width ?? 0,
					firstRowCount: firstRowRects.length,
					firstRowInlineStart: firstRowLeft - gridRect.left,
					gridWidth: gridRect.width,
					occupiedWidth: firstRowRight - firstRowLeft,
				};
			});

			expect(relatedProducts?.cardCount).toBe(route.cardCount);
			expect(relatedProducts?.firstRowCount).toBe(
				relatedProducts?.expectedFirstRowCount
			);
			expect(
				relatedProducts?.firstRowInlineStart ?? 0
			).toBeLessThanOrEqual(1);
			expect(relatedProducts?.firstCardWidth ?? 0).toBeCloseTo(
				relatedProducts?.expectedCardWidth ?? 0,
				0
			);
			expect(relatedProducts?.occupiedWidth ?? 0).toBeCloseTo(
				(relatedProducts?.expectedCardWidth ?? 0) *
					(relatedProducts?.expectedFirstRowCount ?? 0),
				0
			);
		}
	);
}

test(
	taggedTitle(
		'Ecwid product descriptions keep white-surface typography',
		'shop',
		'ecwid',
		'vendor',
		'slow'
	),
	async ({ page }) => {
		await page.goto(
			'/shop/Suffragette-Penny-A-Unique-Handmade-Replica-&-Tiny-act-of-rebellion-p814453695'
		);
		await page.waitForLoadState('domcontentloaded');
		await waitForStableAssets(page);
		await page
			.locator('.product-details__product-description h2')
			.first()
			.waitFor({ state: 'attached', timeout: 10000 });
		await page
			.locator('.ec-related-products .grid-product')
			.first()
			.waitFor({ state: 'attached', timeout: 10000 });

		const styles = await page.evaluate(() => {
			function read(selector: string) {
				const element = document.querySelector(selector);

				if (!element) {
					return null;
				}

				const computed = getComputedStyle(element);

				return {
					color: computed.color,
					fontFamily: computed.fontFamily,
					fontSize: computed.fontSize,
					fontWeight: computed.fontWeight,
					lineHeight: computed.lineHeight,
					textFillColor: computed.webkitTextFillColor,
				};
			}

			function readRelatedProducts() {
				const grid = document.querySelector<HTMLElement>(
					'.ec-store .ec-related-products .grid__products'
				);
				const products = Array.from(
					document.querySelectorAll<HTMLElement>(
						'.ec-store .ec-related-products .grid__products > .grid-product'
					)
				);

				if (!grid || products.length === 0) {
					return null;
				}

				const gridRect = grid.getBoundingClientRect();
				const productRects = products.map((product) =>
					product.getBoundingClientRect()
				);
				const firstLeft = Math.min(
					...productRects.map((rect) => rect.left)
				);
				const lastRight = Math.max(
					...productRects.map((rect) => rect.right)
				);

				return {
					cardCount: products.length,
					firstCardWidth: productRects[0]?.width ?? 0,
					gridWidth: gridRect.width,
					occupiedWidth: lastRight - firstLeft,
				};
			}

			return {
				body: read('.ec-store .product-details__product-description p'),
				heading: read(
					'.ec-store .product-details__product-description h2'
				),
				relatedProducts: readRelatedProducts(),
			};
		});

		expect(styles.heading?.color).toBe('rgb(0, 0, 0)');
		expect(styles.heading?.textFillColor).toBe('rgb(0, 0, 0)');
		expect(styles.heading?.fontFamily).toContain('Rubik');
		expect(styles.heading?.fontSize).toBe('28px');
		expect(styles.heading?.fontWeight).toBe('800');
		expect(styles.heading?.lineHeight).toBe('36.4px');
		expect(styles.body?.color).toBe('rgb(79, 77, 73)');
		expect(styles.body?.textFillColor).toBe('rgb(79, 77, 73)');
		expect(styles.relatedProducts?.cardCount).toBe(4);
		expect(
			styles.relatedProducts?.occupiedWidth ?? 0
		).toBeGreaterThanOrEqual((styles.relatedProducts?.gridWidth ?? 0) - 2);
		expect(
			styles.relatedProducts?.firstCardWidth ?? 0
		).toBeGreaterThanOrEqual(
			((styles.relatedProducts?.gridWidth ?? 0) / 4) * 0.95
		);
	}
);

test(
	taggedTitle(
		'Ecwid cart recommendations match the related-product card layout',
		'shop',
		'ecwid',
		'vendor',
		'slow'
	),
	async ({ page }) => {
		await page.goto('/shop/Scottish-Suffragette-Posters-p612143977');
		await page.waitForLoadState('domcontentloaded');
		await waitForStableAssets(page);
		await page
			.locator('.product-details__product-title')
			.first()
			.waitFor({ state: 'attached', timeout: 10000 });
		await page
			.getByRole('button', { name: /add to bag/i })
			.first()
			.click();
		await page.waitForTimeout(3000);
		await page.goto('/shop/cart');
		await page.waitForLoadState('domcontentloaded');
		await waitForStableAssets(page);
		await page
			.locator('.ec-related-products .grid-product__title-inner')
			.first()
			.waitFor({ state: 'attached', timeout: 15000 });

		const styles = await page.evaluate(() => {
			const mediumFontProbe = document.createElement('span');
			mediumFontProbe.style.fontSize =
				'var(--wp--preset--font-size--text-lead)';
			document.body.appendChild(mediumFontProbe);
			const mediumFontSize = getComputedStyle(mediumFontProbe).fontSize;
			mediumFontProbe.remove();

			function read(selector: string) {
				const element = document.querySelector(selector);

				if (!element) {
					return null;
				}

				const computed = getComputedStyle(element);

				return {
					color: computed.color,
					fontSize: computed.fontSize,
					textFillColor: computed.webkitTextFillColor,
				};
			}

			function readRelatedProducts() {
				const grid = document.querySelector<HTMLElement>(
					'.ec-store .ec-related-products .grid__products'
				);
				const products = Array.from(
					document.querySelectorAll<HTMLElement>(
						'.ec-store .ec-related-products .grid__products > .grid-product'
					)
				);

				if (!grid || products.length === 0) {
					return null;
				}

				const gridRect = grid.getBoundingClientRect();
				const productRects = products.map((product) =>
					product.getBoundingClientRect()
				);
				const firstRowTop = productRects[0]?.top ?? 0;
				const firstRowRects = productRects.filter(
					(rect) => Math.abs(rect.top - firstRowTop) <= 2
				);
				const firstRowLeft = Math.min(
					...firstRowRects.map((rect) => rect.left)
				);
				const maxColumns =
					window.innerWidth <= 600
						? 1
						: window.innerWidth <= 999
							? 2
							: 4;

				return {
					cardCount: products.length,
					expectedCardWidth: gridRect.width / maxColumns,
					expectedFirstRowCount: Math.min(
						products.length,
						maxColumns
					),
					firstCardWidth: productRects[0]?.width ?? 0,
					firstRowCount: firstRowRects.length,
					firstRowInlineStart: firstRowLeft - gridRect.left,
				};
			}

			return {
				mediumFontSize,
				price: read('.ec-related-products .grid-product__price'),
				relatedProducts: readRelatedProducts(),
				title: read('.ec-related-products .grid-product__title-inner'),
			};
		});

		expect(styles.title?.color).toBe('rgb(22, 22, 22)');
		expect(styles.title?.textFillColor).toBe('rgb(22, 22, 22)');
		expect(styles.title?.fontSize).toBe(styles.mediumFontSize);
		expect(styles.price?.color).toBe('rgb(0, 0, 0)');
		expect(styles.price?.textFillColor).toBe('rgb(0, 0, 0)');
		expect(styles.relatedProducts?.cardCount ?? 0).toBeGreaterThan(0);
		expect(styles.relatedProducts?.firstRowCount).toBe(
			styles.relatedProducts?.expectedFirstRowCount
		);
		expect(
			styles.relatedProducts?.firstRowInlineStart ?? 0
		).toBeLessThanOrEqual(1);
		expect(styles.relatedProducts?.firstCardWidth ?? 0).toBeCloseTo(
			styles.relatedProducts?.expectedCardWidth ?? 0,
			0
		);
	}
);
