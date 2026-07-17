import { readFileSync } from 'node:fs';
import { join } from 'node:path';

import { expect, test, type Page } from '@playwright/test';

const themeJson = JSON.parse(
	readFileSync(join(process.cwd(), 'theme.json'), 'utf8')
) as { settings: { layout: { wideSize: string } } };
const siteFrameSize = Number.parseFloat(themeJson.settings.layout.wideSize);
const wideViewport = { width: 2560, height: 1200 };
const enabled = process.env.PNS_ENABLE_SITE_FRAME_NEXT === '1';

type Rect = {
	left: number;
	right: number;
	width: number;
};

type SiteFramePanel = Rect;

type SiteFramePanelAudit = {
	documentWidth: number;
	panels: SiteFramePanel[];
	viewportWidth: number;
};

function expectCentredFrame(
	frame: Rect,
	viewportWidth: number,
	label: string
): void {
	expect(
		frame.width,
		`${label} must not exceed the shared site frame`
	).toBeLessThanOrEqual(siteFrameSize + 1);
	expect(
		frame.left,
		`${label} must be centred inside the viewport`
	).toBeCloseTo((viewportWidth - frame.width) / 2, 0);
}

async function setWideViewport(page: Page): Promise<void> {
	await page.setViewportSize(wideViewport);
}

async function readSiteFramePanels(page: Page): Promise<SiteFramePanelAudit> {
	return page.evaluate(() => ({
		documentWidth: document.documentElement.scrollWidth,
		panels: Array.from(
			document.querySelectorAll<HTMLElement>('.pns-site-frame-panel')
		)
			.filter((panel) => !panel.classList.contains('pns-page-hero'))
			.map((panel) => {
				const bounds = panel.getBoundingClientRect();
				return {
					left: bounds.left,
					right: bounds.right,
					width: bounds.width,
				};
			}),
		viewportWidth: window.innerWidth,
	}));
}

function expectSiteFramePanels(
	audit: SiteFramePanelAudit,
	expectedCount: number,
	label: string
): void {
	expect(
		audit.panels,
		`${label} must expose every intended panel root`
	).toHaveLength(expectedCount);
	expect(
		audit.documentWidth,
		`${label} must not overflow`
	).toBeLessThanOrEqual(audit.viewportWidth + 1);

	for (const [index, panel] of audit.panels.entries()) {
		expectCentredFrame(
			panel,
			audit.viewportWidth,
			`${label} panel ${index + 1}`
		);
	}
}

test.describe('@site-frame-next future site-frame contracts', () => {
	test.skip(
		!enabled,
		'Future site-frame assertions are intentionally opt-in until their owning phase passes.'
	);

	test('@site-frame-next native wideSize and configurable content rail cap at the configured shared frame', async ({
		page,
	}) => {
		await setWideViewport(page);
		await page.goto('/pns-pattern-qa/');
		await page.waitForLoadState('domcontentloaded');

		const widths = await page.evaluate(() => {
			const fixture = document.createElement('div');
			fixture.style.cssText =
				'position:absolute;visibility:hidden;inline-size:3000px;block-size:1px;';

			const probe = (inlineSize: string) => {
				const element = document.createElement('div');
				element.style.inlineSize = inlineSize;
				element.style.blockSize = '1px';
				fixture.append(element);
				return element;
			};

			const content = probe(
				'var(--wp--style--global--content-size, 0px)'
			);
			const wide = probe('var(--wp--style--global--wide-size, 0px)');
			const pnsSectionFrame = probe(
				'var(--pns--layout--section-frame-size, 0px)'
			);
			const contentRail = probe('var(--pns--layout--content-rail, 0px)');

			document.body.append(fixture);

			const width = (element: HTMLElement) =>
				element.getBoundingClientRect().width;
			const rootStyles = getComputedStyle(document.documentElement);
			const values = {
				content: width(content),
				contentRail: width(contentRail),
				pnsSectionFrame: width(pnsSectionFrame),
				wideToken: rootStyles
					.getPropertyValue('--wp--style--global--wide-size')
					.trim(),
				wide: width(wide),
			};

			fixture.remove();
			return values;
		});

		expect(widths.content).toBeCloseTo(704, 0);
		expect(widths.wideToken).toBeTruthy();
		expect(widths.wide).toBeCloseTo(siteFrameSize, 0);
		expect(widths.pnsSectionFrame).toBeCloseTo(siteFrameSize, 0);
		expect(widths.contentRail).toBeGreaterThan(0);

		await page.setViewportSize({ width: 390, height: 844 });

		const narrowRails = await page.evaluate(() => {
			const probe = (inlineSize: string) => {
				const element = document.createElement('div');
				element.style.cssText = `position:absolute;visibility:hidden;inline-size:${inlineSize};block-size:1px;`;
				document.body.append(element);
				const width = element.getBoundingClientRect().width;
				element.remove();
				return width;
			};

			return {
				contentRail: probe('var(--pns--layout--content-rail, 0px)'),
				section: probe('var(--wp--preset--spacing--section, 0px)'),
			};
		});

		expect(narrowRails.section).toBeGreaterThan(0);
		expect(narrowRails.contentRail).toBeCloseTo(narrowRails.section, 0);
	});

	test('@site-frame-next header and footer retain viewport surfaces around centred inners', async ({
		page,
	}) => {
		await setWideViewport(page);
		await page.goto('/');
		await page.waitForLoadState('domcontentloaded');

		const layout = await page.evaluate(() => {
			const rect = (selector: string): Rect | null => {
				const element = document.querySelector<HTMLElement>(selector);

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
			return {
				footerInner: rect('.pns-footer > .pns-footer__inner'),
				footerSurface: rect('.pns-footer'),
				headerInner: rect('.pns-header__surface > .pns-header__inner'),
				headerSurface: rect('.pns-header__surface'),
				viewportWidth: window.innerWidth,
			};
		});

		expect(layout.headerSurface).not.toBeNull();
		expect(layout.footerSurface).not.toBeNull();
		expect(layout.headerInner).not.toBeNull();
		expect(layout.footerInner).not.toBeNull();

		if (
			!layout.headerSurface ||
			!layout.footerSurface ||
			!layout.headerInner ||
			!layout.footerInner
		) {
			return;
		}

		expect(layout.headerSurface.width).toBeCloseTo(layout.viewportWidth, 0);
		expect(layout.footerSurface.width).toBeCloseTo(layout.viewportWidth, 0);
		expectCentredFrame(
			layout.headerInner,
			layout.viewportWidth,
			'Header inner'
		);
		expectCentredFrame(
			layout.footerInner,
			layout.viewportWidth,
			'Footer inner'
		);
	});

	test('@site-frame-next opening page heroes use viewport art with a capped inner rail', async ({
		page,
	}) => {
		await setWideViewport(page);
		await page.goto('/artworks/');
		await page.waitForLoadState('domcontentloaded');

		const hero = await page.evaluate(() => {
			const root = document.querySelector<HTMLElement>(
				'.pns-template .wp-block-post-content > .wp-block-cover.pns-page-hero:first-child'
			);
			const inner =
				root?.querySelector<HTMLElement>('.pns-section-inner');
			const heading = root?.querySelector<HTMLElement>(
				'.pns-hero-copy .wp-block-heading'
			);

			if (!root || !inner || !heading) {
				return null;
			}

			const rect = (element: HTMLElement): Rect => {
				const bounds = element.getBoundingClientRect();
				return {
					left: bounds.left,
					right: bounds.right,
					width: bounds.width,
				};
			};
			const railProbe = document.createElement('div');
			railProbe.style.cssText =
				'position:absolute;visibility:hidden;inline-size:var(--pns--layout--content-rail);block-size:1px;';
			document.body.append(railProbe);

			const hero = {
				contentRail: railProbe.getBoundingClientRect().width,
				heading: rect(heading),
				inner: rect(inner),
				root: rect(root),
				viewportWidth: window.innerWidth,
			};

			railProbe.remove();

			return hero;
		});

		expect(
			hero,
			'Artworks must expose an opening page hero'
		).not.toBeNull();

		if (!hero) {
			return;
		}

		expect(hero.root.left).toBeCloseTo(0, 0);
		expect(hero.root.width).toBeCloseTo(hero.viewportWidth, 0);
		expectCentredFrame(
			hero.inner,
			hero.viewportWidth,
			'Opening hero inner'
		);
		expect(hero.heading.left).toBeCloseTo(
			hero.inner.left + hero.contentRail,
			0
		);
	});

	test('@site-frame-next opening news heroes use the shared cover inner and rail', async ({
		page,
	}) => {
		await page.setViewportSize({ width: 1504, height: 1000 });
		await page.goto('/news/work-with-us-argyll/');
		await page.waitForLoadState('domcontentloaded');

		const hero = await page.evaluate(() => {
			const root = document.querySelector<HTMLElement>(
				'.pns-template-single-full-width-news .wp-block-cover.pns-page-hero'
			);
			const inner =
				root?.querySelector<HTMLElement>('.pns-section-inner');
			const copy = root?.querySelector<HTMLElement>('.pns-hero-copy');
			const heading = root?.querySelector<HTMLElement>(
				'.pns-hero-copy .wp-block-post-title'
			);
			const strapline = root?.querySelector<HTMLElement>(
				'.pns-editorial-strapline'
			);

			if (!root || !inner || !copy || !heading || !strapline) {
				return null;
			}

			const rect = (element: HTMLElement): Rect => {
				const bounds = element.getBoundingClientRect();
				return {
					left: bounds.left,
					right: bounds.right,
					width: bounds.width,
				};
			};
			const fixture = document.createElement('div');
			fixture.style.cssText = 'position:absolute;visibility:hidden;';
			const probe = (inlineSize: string) => {
				const element = document.createElement('div');
				element.style.inlineSize = inlineSize;
				element.style.blockSize = '1px';
				fixture.append(element);
				return element;
			};
			const contentRail = probe('var(--pns--layout--content-rail)');
			const contentSize = probe('var(--pns--layout--content-size)');
			document.body.append(fixture);
			const hero = {
				contentRail: contentRail.getBoundingClientRect().width,
				contentSize: contentSize.getBoundingClientRect().width,
				copy: rect(copy),
				heading: rect(heading),
				inner: rect(inner),
				root: rect(root),
				straplineMaxInlineSize: Number.parseFloat(
					getComputedStyle(strapline).maxInlineSize
				),
				viewportWidth: window.innerWidth,
			};
			fixture.remove();
			return hero;
		});

		expect(hero).not.toBeNull();

		if (!hero) {
			return;
		}

		expect(hero.root.left).toBeCloseTo(0, 0);
		expect(hero.root.width).toBeCloseTo(hero.viewportWidth, 0);
		expect(hero.heading.left).toBeCloseTo(
			hero.inner.left + hero.contentRail,
			0
		);
		expect(hero.straplineMaxInlineSize).toBeCloseTo(hero.contentSize, 0);
	});

	test('@site-frame-next desktop header keeps both its logo and navigation inside the frame', async ({
		page,
	}) => {
		await page.setViewportSize({ width: 1504, height: 1000 });
		await page.goto('/news/');
		await page.waitForLoadState('domcontentloaded');

		const layout = await page.evaluate(() => {
			const rect = (selector: string): Rect | null => {
				const element = document.querySelector<HTMLElement>(selector);

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

			const railProbe = document.createElement('div');
			railProbe.style.cssText =
				'position:absolute;visibility:hidden;inline-size:var(--pns--layout--content-rail);block-size:1px;';
			document.body.append(railProbe);

			const layout = {
				headerInner: rect('.pns-header__surface > .pns-header__inner'),
				logo: rect('.pns-header .pands-logo'),
				navigation: rect('.pns-header .pns-primary-navigation'),
				navigationMarginEnd: Number.parseFloat(
					getComputedStyle(
						document.querySelector<HTMLElement>(
							'.pns-header .pns-primary-navigation'
						) as HTMLElement
					).marginInlineEnd
				),
				contentRail: railProbe.getBoundingClientRect().width,
			};

			railProbe.remove();

			return layout;
		});

		expect(layout.headerInner).not.toBeNull();
		expect(layout.logo).not.toBeNull();
		expect(layout.navigation).not.toBeNull();

		if (!layout.headerInner || !layout.logo || !layout.navigation) {
			return;
		}

		expect(
			layout.logo.left,
			'Desktop logo must begin at the site frame'
		).toBeCloseTo(layout.headerInner.left, 0);
		expect(
			layout.navigationMarginEnd,
			'Desktop navigation must use the configurable content rail, not a fixed spacing preset'
		).toBeCloseTo(layout.contentRail, 0);
		expect(
			layout.navigation.right,
			'Desktop navigation must align with the site-frame inner gutter'
		).toBeCloseTo(layout.headerInner.right - layout.navigationMarginEnd, 0);
	});

	test('@site-frame-next cross-site CTA keeps its links inside the content rails', async ({
		page,
	}) => {
		await page.setViewportSize({ width: 1504, height: 1000 });
		await page.goto('/');
		await page.waitForLoadState('domcontentloaded');

		const cta = await page.evaluate(() => {
			const banner = document.querySelector<HTMLElement>(
				'header nav.pns-cross-site-banner-cta'
			);
			const inner = banner?.querySelector<HTMLElement>(
				'.wp-block-navigation__container'
			);

			if (!banner || !inner) {
				return null;
			}

			const rect = (element: HTMLElement): Rect => {
				const bounds = element.getBoundingClientRect();
				return {
					left: bounds.left,
					right: bounds.right,
					width: bounds.width,
				};
			};
			const railProbe = document.createElement('div');
			railProbe.style.cssText =
				'position:absolute;visibility:hidden;inline-size:var(--pns--layout--content-rail);block-size:1px;';
			document.body.append(railProbe);
			const cta = {
				banner: rect(banner),
				contentRail: railProbe.getBoundingClientRect().width,
				inner: rect(inner),
				viewportWidth: window.innerWidth,
			};
			railProbe.remove();

			return cta;
		});

		expect(cta).not.toBeNull();

		if (!cta) {
			return;
		}

		expect(cta.banner.left).toBeCloseTo(0, 0);
		expect(cta.banner.width).toBeCloseTo(cta.viewportWidth, 0);
		expect(cta.inner.left).toBeCloseTo(cta.contentRail, 0);
		expect(cta.inner.right).toBeCloseTo(
			cta.viewportWidth - cta.contentRail,
			0
		);
	});

	test('@site-frame-next header and footer logos share the same vertical inner-frame inset', async ({
		page,
	}) => {
		await setWideViewport(page);
		await page.goto('/');
		await page.waitForLoadState('domcontentloaded');

		const layout = await page.evaluate(() => {
			const top = (selector: string): number | null => {
				const element = document.querySelector<HTMLElement>(selector);

				return element ? element.getBoundingClientRect().top : null;
			};

			return {
				footerInnerTop: top('.pns-footer > .pns-footer__inner'),
				footerLogoTop: top('.pns-footer .footer-logo img'),
				headerInnerTop: top(
					'.pns-header__surface > .pns-header__inner'
				),
				headerLogoTop: top('.pns-header .pands-logo .custom-logo'),
			};
		});

		expect(layout.footerInnerTop).not.toBeNull();
		expect(layout.footerLogoTop).not.toBeNull();
		expect(layout.headerInnerTop).not.toBeNull();
		expect(layout.headerLogoTop).not.toBeNull();

		if (
			layout.footerInnerTop === null ||
			layout.footerLogoTop === null ||
			layout.headerInnerTop === null ||
			layout.headerLogoTop === null
		) {
			return;
		}

		expect(
			layout.footerLogoTop - layout.footerInnerTop,
			'Footer logo inset must match the header logo inset'
		).toBeCloseTo(layout.headerLogoTop - layout.headerInnerTop, 0);
	});

	test('@site-frame-next edge Split media ends at the centred frame edge above the cap', async ({
		page,
	}) => {
		await setWideViewport(page);
		await page.goto('/pns-pattern-qa/');
		await page.waitForLoadState('domcontentloaded');

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
			const rect = (element: HTMLElement | null): Rect | null => {
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
					columns: rect(
						section.querySelector<HTMLElement>(
							'.pns-split-section__columns'
						)
					),
					media: rect(
						section.querySelector<HTMLElement>(
							'.pns-split-section__media-column'
						)
					),
					section: rect(section),
				};
			};

			return {
				edgeMediaLeft: measure('edge-media-left'),
				edgeMediaRight: measure('edge-media-right'),
				viewportWidth: window.innerWidth,
			};
		});

		expect(variants).not.toBeNull();

		if (!variants) {
			return;
		}

		for (const [name, variant] of Object.entries({
			edgeMediaLeft: variants.edgeMediaLeft,
			edgeMediaRight: variants.edgeMediaRight,
		})) {
			expect(
				variant.columns,
				`${name} columns must exist`
			).not.toBeNull();
			expect(variant.media, `${name} media must exist`).not.toBeNull();

			if (!variant.columns || !variant.media) {
				continue;
			}

			expect(variant.section, `${name} root must exist`).not.toBeNull();

			if (!variant.section) {
				continue;
			}

			expectCentredFrame(
				variant.section,
				variants.viewportWidth,
				`${name} root surface`
			);
			expectCentredFrame(
				variant.columns,
				variants.viewportWidth,
				`${name} columns`
			);
			expect(
				variant.media.width,
				`${name} media must remain within the capped panel`
			).toBeLessThanOrEqual(variant.section.width + 1);
			expect(
				name === 'edgeMediaLeft'
					? variant.media.left
					: variant.media.right,
				`${name} media must meet its panel edge without a viewport seam`
			).toBeCloseTo(
				name === 'edgeMediaLeft'
					? variant.columns.left
					: variant.columns.right,
				0
			);
		}
	});

	test('@site-frame-next edge Split preserves the configured frame seam and never overflows below it', async ({
		page,
	}) => {
		await page.goto('/pns-pattern-qa/');
		await page.waitForLoadState('domcontentloaded');

		const measurements = [];

		for (const viewport of [
			{ width: 390, height: 844 },
			{ width: siteFrameSize, height: 1000 },
			{ width: siteFrameSize + 1, height: 1000 },
		]) {
			await page.setViewportSize(viewport);

			const measurement = await page.evaluate(() => {
				const section =
					document.querySelector<HTMLElement>('.pns-split-section');

				if (!section) {
					return null;
				}

				section.classList.remove(
					'is-style-pns-media-left',
					'is-style-pns-media-right',
					'is-style-pns-edge-media-right'
				);
				section.classList.add('is-style-pns-edge-media-left');

				const columns = section.querySelector<HTMLElement>(
					'.pns-split-section__columns'
				);
				const media = section.querySelector<HTMLElement>(
					'.pns-split-section__media-column'
				);
				const copy = section.querySelector<HTMLElement>(
					'.pns-split-section__copy-column'
				);

				if (!columns || !copy || !media) {
					return null;
				}

				const rect = (element: HTMLElement): Rect => {
					const bounds = element.getBoundingClientRect();

					return {
						left: bounds.left,
						right: bounds.right,
						width: bounds.width,
					};
				};

				return {
					columns: rect(columns),
					copy: rect(copy),
					documentWidth: document.documentElement.scrollWidth,
					media: rect(media),
					section: rect(section),
					viewportWidth: window.innerWidth,
				};
			});

			expect(measurement).not.toBeNull();

			if (!measurement) {
				continue;
			}

			expect(measurement.documentWidth).toBeLessThanOrEqual(
				measurement.viewportWidth + 1
			);
			expect(measurement.columns.width).toBeLessThanOrEqual(
				measurement.viewportWidth + 1
			);
			expect(measurement.media.width).toBeLessThanOrEqual(
				measurement.viewportWidth + 1
			);
			expect(measurement.section.width).toBeLessThanOrEqual(
				measurement.viewportWidth + 1
			);
			measurements.push(measurement);
		}

		const [mobile, atCap, aboveCap] = measurements;

		expect(mobile.columns.width).toBeCloseTo(mobile.viewportWidth, 0);
		expect(atCap.columns.width).toBeCloseTo(siteFrameSize, 0);
		expect(atCap.copy.width).toBeCloseTo(atCap.media.width, 0);
		expect(atCap.media.width).toBeLessThanOrEqual(atCap.section.width + 1);
		expect(atCap.media.left).toBeCloseTo(atCap.columns.left, 0);
		expectCentredFrame(
			atCap.section,
			atCap.viewportWidth,
			'At-cap edge Split root'
		);
		expectCentredFrame(
			aboveCap.columns,
			aboveCap.viewportWidth,
			'Above-cap edge Split columns'
		);
		expect(aboveCap.media.width).toBeLessThanOrEqual(
			aboveCap.section.width + 1
		);
		expect(aboveCap.copy.width).toBeCloseTo(aboveCap.media.width, 0);
		expect(aboveCap.media.left).toBeCloseTo(aboveCap.columns.left, 0);
		expectCentredFrame(
			aboveCap.section,
			aboveCap.viewportWidth,
			'Above-cap edge Split root'
		);
	});

	test('@site-frame-next contains Mary quote, image-strip, and Facts panels inside the site frame', async ({
		page,
	}) => {
		await page.emulateMedia({ reducedMotion: 'reduce' });
		await setWideViewport(page);
		await page.goto('/herstories/mary-barbour/');
		await page.waitForLoadState('domcontentloaded');
		const maryPanels = await readSiteFramePanels(page);
		expectSiteFramePanels(maryPanels, 14, 'Mary Herstory');

		const mary = await page.evaluate(() => {
			const rect = (selector: string): Rect | null => {
				const element = document.querySelector<HTMLElement>(selector);

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
			const quote = document.querySelector<HTMLElement>(
				'.wp-block-cover.alignfull.pns-quotes .wp-block-quote'
			);
			const railProbe = document.createElement('div');
			railProbe.style.cssText =
				'position:absolute;visibility:hidden;inline-size:var(--pns--layout--content-rail);block-size:1px;';
			document.body.append(railProbe);

			const mary = {
				contentRail: railProbe.getBoundingClientRect().width,
				documentWidth: document.documentElement.scrollWidth,
				facts: rect('.pns-suffragette-facts'),
				factsColumns: rect(
					'.pns-suffragette-facts > .wp-block-columns'
				),
				factsCopyColumn: rect('.pns-suffragette-facts__copy-column'),
				imageStrip: rect('.pns-image-strip'),
				quote: rect(
					'.wp-block-cover.alignfull.pns-quotes .wp-block-quote'
				),
				quoteCover: rect('.wp-block-cover.alignfull.pns-quotes'),
				quoteKeyline: rect(
					'.wp-block-cover.alignfull.pns-quotes .wp-block-quote img[src*="Red-Keyline.svg"]'
				),
				quotePaddingInlineStart: quote
					? Number.parseFloat(
							getComputedStyle(quote).paddingInlineStart
						)
					: Number.NaN,
				quoteParagraph: rect(
					'.wp-block-cover.alignfull.pns-quotes .wp-block-quote p'
				),
				splitColumns: rect(
					'.pns-split-section .pns-split-section__columns'
				),
				viewportWidth: window.innerWidth,
			};

			railProbe.remove();

			return mary;
		});

		expect(mary.facts).not.toBeNull();
		expect(mary.factsColumns).not.toBeNull();
		expect(mary.factsCopyColumn).not.toBeNull();
		expect(mary.imageStrip).not.toBeNull();
		expect(mary.quote).not.toBeNull();
		expect(mary.quoteCover).not.toBeNull();
		expect(mary.quoteKeyline).not.toBeNull();
		expect(mary.quoteParagraph).not.toBeNull();
		expect(mary.splitColumns).not.toBeNull();

		if (
			!mary.facts ||
			!mary.factsColumns ||
			!mary.factsCopyColumn ||
			!mary.imageStrip ||
			!mary.quote ||
			!mary.quoteCover ||
			!mary.quoteKeyline ||
			!mary.quoteParagraph ||
			!mary.splitColumns
		) {
			return;
		}

		expect(mary.documentWidth).toBeLessThanOrEqual(mary.viewportWidth + 1);
		expectCentredFrame(mary.facts, mary.viewportWidth, 'Mary Facts panel');
		expectCentredFrame(
			mary.factsColumns,
			mary.viewportWidth,
			'Mary Facts columns'
		);
		expect(mary.factsCopyColumn.left).toBeGreaterThanOrEqual(
			mary.factsColumns.left - 1
		);
		expect(mary.factsCopyColumn.right).toBeLessThanOrEqual(
			mary.factsColumns.right + 1
		);
		expectCentredFrame(
			mary.imageStrip,
			mary.viewportWidth,
			'Mary image strip'
		);
		expectCentredFrame(
			mary.quoteCover,
			mary.viewportWidth,
			'Mary quote cover'
		);
		expect(mary.quotePaddingInlineStart).toBeCloseTo(mary.contentRail, 0);
		expect(mary.quoteParagraph.left).toBeCloseTo(
			mary.quoteCover.left + mary.quotePaddingInlineStart,
			0
		);
		expect(mary.quoteParagraph.right).toBeCloseTo(
			mary.quoteCover.right - mary.quotePaddingInlineStart,
			0
		);
		expect(mary.quoteKeyline.left).toBeCloseTo(mary.quoteParagraph.left, 0);
		expectCentredFrame(
			mary.splitColumns,
			mary.viewportWidth,
			'Mary Barbour structural Split frame'
		);

		await page.goto('/pns-pattern-qa/');
		await page.waitForLoadState('domcontentloaded');

		const mediaControls = await page.evaluate(() => {
			const measure = (selector: string) => {
				const media = document.querySelector<HTMLElement>(selector);
				const section =
					media?.closest<HTMLElement>('.pns-split-section');
				const columns = section?.querySelector<HTMLElement>(
					'.pns-split-section__columns'
				);

				if (!media || !columns) {
					return null;
				}

				const bounds = media.getBoundingClientRect();
				const frame = columns.getBoundingClientRect();

				return {
					mediaLeft: bounds.left,
					mediaRight: bounds.right,
					frameLeft: frame.left,
					frameRight: frame.right,
				};
			};

			return {
				documentWidth: document.documentElement.scrollWidth,
				slideshow: measure(
					'.pns-split-section .wp-block-jetpack-slideshow'
				),
				video: measure('.pns-split-section .wp-block-embed-youtube'),
				viewportWidth: window.innerWidth,
			};
		});

		expect(mediaControls.slideshow).not.toBeNull();
		expect(mediaControls.video).not.toBeNull();

		for (const [name, control] of Object.entries({
			slideshow: mediaControls.slideshow,
			video: mediaControls.video,
		})) {
			expect(control, `${name} control must render`).not.toBeNull();

			if (!control) {
				continue;
			}

			expect(control.mediaLeft).toBeGreaterThanOrEqual(
				control.frameLeft - 1
			);
			expect(control.mediaRight).toBeLessThanOrEqual(
				control.frameRight + 1
			);
		}

		expect(mediaControls.documentWidth).toBeLessThanOrEqual(
			mediaControls.viewportWidth + 1
		);
	});

	test('@site-frame-next caps Herstories archive panels and their spacing at the site frame', async ({
		page,
	}) => {
		await setWideViewport(page);
		await page.goto('/herstories/');
		await page.waitForLoadState('domcontentloaded');
		const archivePanels = await readSiteFramePanels(page);
		expectSiteFramePanels(archivePanels, 6, 'Herstories archive');

		const herstories = await page.evaluate(() => {
			const main = document.querySelector<HTMLElement>(
				'.pns-template-herstories-archive'
			);
			const more = document.querySelector<HTMLElement>(
				'.pns-herstories-more-section'
			);
			const hero = document.querySelector<HTMLElement>(
				'.pns-template-herstories-archive > .wp-block-cover.pns-page-hero'
			);

			if (!main || !hero || !more) {
				return null;
			}

			const rect = (element: HTMLElement): Rect => {
				const bounds = element.getBoundingClientRect();
				return {
					left: bounds.left,
					right: bounds.right,
					width: bounds.width,
				};
			};

			return {
				bodyBackgroundColor: getComputedStyle(document.body)
					.backgroundColor,
				mainBackgroundColor: getComputedStyle(main).backgroundColor,
				hero: rect(hero),
				more: rect(more),
			};
		});

		expect(herstories).not.toBeNull();

		if (!herstories) {
			return;
		}

		expect(herstories.hero.left).toBeCloseTo(0, 0);
		expect(herstories.hero.width).toBeCloseTo(wideViewport.width, 0);
		expectCentredFrame(
			herstories.more,
			wideViewport.width,
			'More Herstories panel and spacing'
		);
		expect(
			herstories.mainBackgroundColor,
			'Herstories archive gutters must not leak the global purple canvas'
		).not.toBe(herstories.bodyBackgroundColor);
	});

	test('@site-frame-next related-post panels use the configured content rail, not Core background padding', async ({
		page,
	}) => {
		const routes = [
			{ path: '/news/', selector: '.pns-news-more-section' },
			{ path: '/herstories/', selector: '.pns-herstories-more-section' },
		];

		for (const route of routes) {
			await setWideViewport(page);
			await page.goto(route.path);
			await page.waitForLoadState('domcontentloaded');

			const panel = await page.evaluate((selector) => {
				const root = document.querySelector<HTMLElement>(selector);
				const query =
					root?.querySelector<HTMLElement>('.wp-block-query');

				if (!root || !query) {
					return null;
				}

				const rootBounds = root.getBoundingClientRect();
				const queryBounds = query.getBoundingClientRect();

				const railProbe = document.createElement('div');
				railProbe.style.cssText =
					'position:absolute;visibility:hidden;inline-size:var(--pns--layout--content-rail);block-size:1px;';
				document.body.append(railProbe);
				const panel = {
					contentRail: railProbe.getBoundingClientRect().width,
					inset: queryBounds.left - rootBounds.left,
					paddingInlineStart: Number.parseFloat(
						getComputedStyle(root).paddingInlineStart
					),
				};

				railProbe.remove();

				return panel;
			}, route.selector);

			expect(
				panel,
				`${route.path} must expose its related-post panel`
			).not.toBeNull();

			if (!panel) {
				continue;
			}

			expect(panel.paddingInlineStart).toBeCloseTo(panel.contentRail, 0);
			expect(panel.inset).toBeCloseTo(panel.contentRail, 0);
		}
	});

	test('@site-frame-next default editorial post prose widens to the scoped 920px measure only', async ({
		page,
	}) => {
		await setWideViewport(page);
		await page.goto('/news/work-with-us-argyll/');
		await page.waitForLoadState('domcontentloaded');

		const prose = await page.evaluate(() => {
			const element = document.querySelector<HTMLElement>(
				'.pns-template-single .pns-single-content > p'
			);

			if (!element) {
				return null;
			}

			const bounds = element.getBoundingClientRect();
			return {
				left: bounds.left,
				right: bounds.right,
				width: bounds.width,
			};
		});

		expect(prose, 'Default news fixture must expose prose').not.toBeNull();

		if (!prose) {
			return;
		}

		expect(prose.width).toBeCloseTo(920, 0);
		expectCentredFrame(
			prose,
			wideViewport.width,
			'Default editorial prose'
		);
	});

	test('@site-frame-next Connect Social and Stay in Touch use centred bounded surfaces and internals', async ({
		page,
	}) => {
		const routes = [
			{
				name: 'Connect Social',
				path: '/',
				selectors: [
					'.pns-connect-social',
					'.pns-connect-social__columns',
				],
			},
			{
				name: 'Stay in Touch',
				path: '/',
				selectors: [
					'.pns-contact-form',
					'.pns-contact-form > .pns-section-frame',
				],
			},
		];

		for (const route of routes) {
			await setWideViewport(page);
			await page.goto(route.path);
			await page.waitForLoadState('domcontentloaded');

			for (const selector of route.selectors) {
				const measurement = await page.evaluate((target) => {
					const element = document.querySelector<HTMLElement>(target);

					if (!element) {
						return null;
					}

					const bounds = element.getBoundingClientRect();
					return {
						left: bounds.left,
						right: bounds.right,
						width: bounds.width,
					};
				}, selector);

				expect(
					measurement,
					`${route.name} fixture must render ${selector}`
				).not.toBeNull();

				if (!measurement) {
					continue;
				}

				expectCentredFrame(
					measurement,
					wideViewport.width,
					`${route.name} ${selector}`
				);
			}
		}
	});

	test('@site-frame-next Connect Social copy starts at the capped panel gutter', async ({
		page,
	}) => {
		await setWideViewport(page);
		await page.goto('/');
		await page.waitForLoadState('domcontentloaded');

		const layout = await page.evaluate(() => {
			const rect = (selector: string): Rect | null => {
				const element = document.querySelector<HTMLElement>(selector);

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
			const copy = document.querySelector<HTMLElement>(
				'.pns-connect-social__copy'
			);

			return {
				copy: rect('.pns-connect-social__copy'),
				copyColumn: rect('.pns-connect-social__copy-column'),
				heading: rect('.pns-connect-social__copy .wp-block-heading'),
				paddingInlineStart: copy
					? Number.parseFloat(
							getComputedStyle(copy).paddingInlineStart
						)
					: Number.NaN,
			};
		});

		expect(layout.copy).not.toBeNull();
		expect(layout.copyColumn).not.toBeNull();
		expect(layout.heading).not.toBeNull();

		if (!layout.copy || !layout.copyColumn || !layout.heading) {
			return;
		}

		expect(layout.copy.left).toBeCloseTo(layout.copyColumn.left, 0);
		expect(layout.heading.left).toBeCloseTo(
			layout.copyColumn.left + layout.paddingInlineStart,
			0
		);
	});
});
