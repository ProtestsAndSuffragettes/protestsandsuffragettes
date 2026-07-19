import {
	expect,
	test,
	type BrowserContext,
	type Frame,
	type Page,
} from '@playwright/test';
import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

const authStatePath = path.resolve(
	process.cwd(),
	'.cache/playwright/editor-auth.json'
);
const fixtureSlug =
	process.env.PNS_EDITOR_FIXTURE_SLUG || 'pns-editor-css-fixture';
const fixtureTitle = 'PNS Editor CSS Fixture';
const themeJson = JSON.parse(
	fs.readFileSync(path.resolve(process.cwd(), 'theme.json'), 'utf8')
) as {
	settings: {
		color: {
			defaultDuotone: boolean;
			defaultGradients: boolean;
			duotone: Array<{ slug: string }>;
			gradients: Array<{ slug: string }>;
		};
	};
};
const realPages = [
	{
		name: 'home',
		env: 'PNS_EDITOR_HOME_ID',
		slug: 'about-2',
		title: 'Protests and Suffragettes',
		fallbackId: 49,
	},
	{
		name: 'mary-barbour',
		env: 'PNS_EDITOR_MARY_BARBOUR_ID',
		slug: 'mary-barbour',
		title: 'Mary Barbour',
		fallbackId: 42,
	},
	{
		name: 'edu-giveaway',
		env: 'PNS_EDITOR_EDU_GIVEAWAY_ID',
		slug: 'edu-giveaway',
		title: 'Education Pack Giveaway',
		fallbackId: 4629,
	},
	{
		name: 'shop',
		env: 'PNS_EDITOR_SHOP_ID',
		slug: 'shop',
		title: 'Shop',
		fallbackId: 565,
	},
	{
		name: 'contact',
		env: 'PNS_EDITOR_CONTACT_ID',
		slug: 'contact-us',
		title: 'Contact Us',
		fallbackId: 6236,
	},
	{
		name: 'pattern-qa',
		env: 'PNS_EDITOR_PATTERN_QA_ID',
		slug: 'pns-pattern-qa',
		title: 'PNS Pattern QA',
		fallbackId: 5265,
	},
	{
		name: 'style-guide',
		env: 'PNS_EDITOR_STYLE_GUIDE_ID',
		slug: 'pns-style-guide',
		title: 'PNS Style Guide: General',
		fallbackId: 6340,
	},
	{
		name: 'membership',
		env: 'PNS_EDITOR_MEMBERSHIP_ID',
		slug: 'membership',
		title: 'Membership',
		fallbackId: 7020,
	},
	{
		name: 'split-section-components',
		env: 'PNS_EDITOR_SPLIT_SECTION_COMPONENTS_ID',
		slug: 'pns-style-guide-split-section-components',
		title: 'PNS Style Guide: Split Section Components',
		fallbackId: 5654,
	},
];
const fullWidthNewsPost = {
	env: 'PNS_EDITOR_FULL_WIDTH_NEWS_ID',
	slug: 'glasgow-herstory-workshops',
	title: 'Glasgow Herstory Workshops',
	fallbackId: 3677,
};
const standardSinglePost = {
	env: 'PNS_EDITOR_STANDARD_SINGLE_ID',
	slug: 'work-with-us-argyll',
	title: 'Work with Us – Argyll',
	fallbackId: 5128,
};

class EditorAuthSkip extends Error {}

type EditorDocument = Page | Frame;

async function restoreAuthState(context: BrowserContext) {
	if (!fs.existsSync(authStatePath)) {
		return;
	}

	try {
		const state = JSON.parse(fs.readFileSync(authStatePath, 'utf8')) as {
			cookies?: Parameters<BrowserContext['addCookies']>[0];
		};

		if (state.cookies?.length) {
			await context.addCookies(state.cookies);
		}
	} catch {
		fs.rmSync(authStatePath, { force: true });
	}
}

async function isLoggedIn(page: Page) {
	await page.goto('/wp-admin/', { waitUntil: 'domcontentloaded' });

	if (page.url().includes('wp-login.php')) {
		return false;
	}

	return page
		.locator('#wpadminbar, body.wp-admin')
		.first()
		.isVisible()
		.catch(() => false);
}

function wpCliLoginUrl() {
	if (process.env.PNS_WP_LOGIN_URL) {
		return process.env.PNS_WP_LOGIN_URL;
	}

	if (!process.env.PNS_WP_LOGIN_URL_COMMAND) {
		return '';
	}

	try {
		return execFileSync(process.env.SHELL || 'sh', [
			'-lc',
			process.env.PNS_WP_LOGIN_URL_COMMAND,
		])
			.toString()
			.trim();
	} catch {
		return '';
	}
}

async function loginWithCredentials(page: Page) {
	const username = process.env.PNS_WP_USERNAME;
	const password = process.env.PNS_WP_PASSWORD;

	if (!username || !password) {
		return false;
	}

	await page.goto('/wp-login.php', { waitUntil: 'domcontentloaded' });
	await page.locator('#user_login').fill(username);
	await page.locator('#user_pass').fill(password);
	await page.locator('#wp-submit').click();
	await page.waitForLoadState('domcontentloaded');

	return isLoggedIn(page);
}

async function ensureAuthenticated(page: Page) {
	await restoreAuthState(page.context());

	if (await isLoggedIn(page)) {
		return;
	}

	const loginUrl = wpCliLoginUrl();

	if (loginUrl) {
		await page.goto(loginUrl, { waitUntil: 'domcontentloaded' });

		if (await isLoggedIn(page)) {
			await saveAuthState(page.context());
			return;
		}
	}

	if (await loginWithCredentials(page)) {
		await saveAuthState(page.context());
		return;
	}

	throw new EditorAuthSkip(
		[
			'Editor auth is not configured.',
			'Set PNS_WP_LOGIN_URL, PNS_WP_LOGIN_URL_COMMAND, or PNS_WP_USERNAME/PNS_WP_PASSWORD.',
			'Auth state will be cached locally at .cache/playwright/editor-auth.json.',
		].join(' ')
	);
}

async function saveAuthState(context: BrowserContext) {
	fs.mkdirSync(path.dirname(authStatePath), { recursive: true });
	await context.storageState({ path: authStatePath });
}

async function fixturePageId(page: Page) {
	if (process.env.PNS_EDITOR_FIXTURE_ID) {
		return Number(process.env.PNS_EDITOR_FIXTURE_ID);
	}

	const response = await page.request.get('/wp-json/wp/v2/pages', {
		params: {
			slug: fixtureSlug,
			context: 'edit',
		},
	});

	if (response.ok()) {
		const pages = (await response.json()) as Array<{ id?: number }>;
		const id = pages[0]?.id;

		if (id) {
			return id;
		}
	}

	await page.goto(
		`/wp-admin/edit.php?post_type=page&s=${encodeURIComponent(fixtureTitle)}`,
		{ waitUntil: 'domcontentloaded' }
	);

	const row = page
		.locator('#the-list tr[id^="post-"]')
		.filter({ hasText: fixtureTitle })
		.first();

	if ((await row.count()) > 0) {
		const rowId = await row.getAttribute('id');
		const postId = rowId?.match(/^post-(\d+)$/)?.[1];

		if (postId) {
			return Number(postId);
		}
	}

	throw new Error(
		`Editor fixture "${fixtureSlug}" was not found. Create or restore a page with the title "${fixtureTitle}" before running the editor suite.`
	);
}

async function optionalFixturePageId(page: Page) {
	try {
		return await fixturePageId(page);
	} catch (error) {
		if (
			error instanceof Error &&
			error.message.includes(
				`Editor fixture "${fixtureSlug}" was not found`
			)
		) {
			return null;
		}

		throw error;
	}
}

async function pageIdFromAdminList(page: Page, title: string) {
	await page.goto(
		`/wp-admin/edit.php?post_type=page&s=${encodeURIComponent(title)}`,
		{ waitUntil: 'domcontentloaded' }
	);

	const row = page
		.locator('#the-list tr[id^="post-"]')
		.filter({ hasText: title })
		.first();

	if ((await row.count()) === 0) {
		return 0;
	}

	const rowId = await row.getAttribute('id');
	const postId = rowId?.match(/^post-(\d+)$/)?.[1];

	return postId ? Number(postId) : 0;
}

async function postIdFromAdminList(page: Page, title: string) {
	await page.goto(`/wp-admin/edit.php?s=${encodeURIComponent(title)}`, {
		waitUntil: 'domcontentloaded',
	});

	const row = page
		.locator('#the-list tr[id^="post-"]')
		.filter({ hasText: title })
		.first();

	if ((await row.count()) === 0) {
		return 0;
	}

	const rowId = await row.getAttribute('id');
	const postId = rowId?.match(/^post-(\d+)$/)?.[1];

	return postId ? Number(postId) : 0;
}

async function realPageId(page: Page, realPage: (typeof realPages)[number]) {
	const envValue = process.env[realPage.env];

	if (envValue) {
		return Number(envValue);
	}

	const response = await page.request.get('/wp-json/wp/v2/pages', {
		params: {
			slug: realPage.slug,
		},
	});

	if (response.ok()) {
		const pages = (await response.json()) as Array<{ id?: number }>;
		const id = pages[0]?.id;

		if (id) {
			return id;
		}
	}

	const adminListId = await pageIdFromAdminList(page, realPage.title);

	return adminListId || realPage.fallbackId;
}

async function fullWidthNewsPostId(page: Page) {
	return postIdBySlugOrTitle(page, fullWidthNewsPost);
}

async function standardSinglePostId(page: Page) {
	return postIdBySlugOrTitle(page, standardSinglePost);
}

async function postIdBySlugOrTitle(
	page: Page,
	post: typeof fullWidthNewsPost | typeof standardSinglePost
) {
	const envValue = process.env[post.env];

	if (envValue) {
		return Number(envValue);
	}

	const response = await page.request.get('/wp-json/wp/v2/posts', {
		params: { slug: post.slug },
	});

	if (response.ok()) {
		const posts = (await response.json()) as Array<{ id?: number }>;
		const id = posts[0]?.id;

		if (id) {
			return id;
		}
	}

	const adminListId = await postIdFromAdminList(page, post.title);

	return adminListId || post.fallbackId;
}

async function openEditor(page: Page, postId: number) {
	await page.goto(`/wp-admin/post.php?post=${postId}&action=edit`, {
		waitUntil: 'domcontentloaded',
	});

	await page
		.locator('.editor-styles-wrapper, iframe[name="editor-canvas"]')
		.first()
		.waitFor({ state: 'attached', timeout: 20000 });

	await page
		.getByRole('button', { name: /close/i })
		.click({ timeout: 1500 })
		.catch(() => undefined);
}

async function editorDocument(page: Page): Promise<EditorDocument> {
	const iframe = page.locator('iframe[name="editor-canvas"]').first();

	if ((await iframe.count()) > 0) {
		const handle = await iframe.elementHandle();
		const frame = await handle?.contentFrame();

		if (frame) {
			await frame
				.locator('.editor-styles-wrapper')
				.first()
				.waitFor({ state: 'attached', timeout: 10000 });

			return frame;
		}
	}

	return page;
}

async function waitForEditorContent(
	editor: EditorDocument,
	selector = '.editor-styles-wrapper .wp-block-heading'
) {
	await editor.locator(selector).first().waitFor({
		state: 'attached',
		timeout: 15000,
	});
}

async function expectNoBlockRecoveryWarnings(
	editor: EditorDocument,
	rootSelector?: string
) {
	if (rootSelector) {
		const warningBlocks = await editor.evaluate((selector) => {
			return Array.from(document.querySelectorAll(selector)).filter(
				(element) =>
					/unexpected or invalid content|attempt block recovery/i.test(
						(element.textContent || '').replace(/\s+/g, ' ')
					)
			).length;
		}, rootSelector);

		expect(warningBlocks).toBe(0);

		return;
	}

	await expect(
		editor.getByText(/unexpected or invalid content/i)
	).toHaveCount(0);
	await expect(editor.getByText(/attempt block recovery/i)).toHaveCount(0);
}

async function readEditorStyles(editor: EditorDocument) {
	return editor.evaluate(() => {
		const rootStyles = getComputedStyle(document.documentElement);

		function read(selector: string, pseudo?: string) {
			const element = document.querySelector(selector);

			if (!element) {
				return null;
			}

			const computed = getComputedStyle(element, pseudo);

			return {
				backgroundColor: computed.backgroundColor,
				borderLeftWidth: computed.borderLeftWidth,
				borderRadius: computed.borderRadius,
				boxShadow: computed.boxShadow,
				color: computed.color,
				content: computed.content,
				display: computed.display,
				fontFamily: computed.fontFamily,
				fontSize: computed.fontSize,
				fontVariationSettings: computed.fontVariationSettings,
				fontWeight: computed.fontWeight,
				gap: computed.gap,
				height: computed.height,
				lineHeight: computed.lineHeight,
				listStyleType: computed.listStyleType,
				marginBottom: computed.marginBottom,
				marginLeft: computed.marginLeft,
				marginTop: computed.marginTop,
				maxWidth: computed.maxWidth,
				paddingBottom: computed.paddingBottom,
				paddingLeft: computed.paddingLeft,
				paddingRight: computed.paddingRight,
				paddingTop: computed.paddingTop,
				textTransform: computed.textTransform,
				textWrap: computed.getPropertyValue('text-wrap'),
				width: computed.width,
			};
		}

		return {
			activeDates: read('.active-dates'),
			button: read('.wp-block-button__link'),
			buttonAfter: read('.wp-block-button__link', '::after'),
			cover: read('.wp-block-cover, .wp-block-cover-image'),
			funFact: read('.fun-facts li'),
			funFactBefore: read('.fun-facts li', '::before'),
			groupWithBackground: read('.wp-block-group.has-background'),
			heading: read('.wp-block-heading'),
			largeText: read('.has-title-large-font-size'),
			mediumText: read('.has-text-lead-font-size'),
			activeDateLabel: read('.active-dates p:has(strong)'),
			pnsContentFrame: read('.pns-content-frame'),
			pnsIntroCopyText: read(
				'.pns-intro-copy :is(p, .wp-block-paragraph)'
			),
			semanticGapOwner: read(
				'.pns-image-strip, .pns-suffragette-image-strip, .pns-contact-form .wp-block-columns, .pns-connect-social .wp-block-columns'
			),
			paragraph: read('.pns-content-frame p'),
			pnsSection: read('.wp-block.alignfull.pns-section'),
			quote: read('.wp-block-quote'),
			quoteText: read('.wp-block-quote p'),
			separator: read('.wp-block-separator'),
			socialIcon: read(
				'.wp-block-social-links.is-style-logos-only .wp-social-link svg'
			),
			socialLinks: read('.wp-block-social-links'),
			wrapper: read('.editor-styles-wrapper'),
			tokens: {
				gradientDeepPurpleToBrandPurple: rootStyles
					.getPropertyValue(
						'--wp--preset--gradient--deep-purple-to-brand-purple'
					)
					.trim(),
				gradientNeutral50ToNeutral0: rootStyles
					.getPropertyValue(
						'--wp--preset--gradient--neutral-50-to-neutral-0'
					)
					.trim(),
			},
			markup: {
				buttons: document.querySelectorAll('.wp-block-button__link')
					.length,
				covers: document.querySelectorAll(
					'.wp-block-cover, .wp-block-cover-image'
				).length,
				funFacts: document.querySelectorAll('.fun-facts li').length,
				headings: document.querySelectorAll('.wp-block-heading').length,
				quotes: document.querySelectorAll('.wp-block-quote').length,
				pnsSections: document.querySelectorAll(
					'.wp-block.alignfull.pns-section'
				).length,
				separators: document.querySelectorAll('.wp-block-separator')
					.length,
				socialLinks: document.querySelectorAll('.wp-block-social-links')
					.length,
			},
		};
	});
}

function cssPixels(value: string | undefined) {
	return Number.parseFloat(value || 'NaN');
}

test.describe('editor CSS regression harness', () => {
	test.beforeEach(async ({ page }, testInfo) => {
		test.skip(
			testInfo.project.name !== 'desktop',
			'Editor regression harness runs once on the desktop project.'
		);

		try {
			await ensureAuthenticated(page);
		} catch (error) {
			if (error instanceof EditorAuthSkip) {
				test.skip(true, error.message);
			}

			throw error;
		}
	});

	test('private fixture exposes representative editor canvas styles', async ({
		page,
	}) => {
		const postId = await optionalFixturePageId(page);

		test.skip(
			postId === null,
			`Optional editor fixture "${fixtureSlug}" is not present in this database.`
		);

		if (postId === null) {
			return;
		}

		await openEditor(page, postId);

		const editor = await editorDocument(page);
		await waitForEditorContent(editor, '.editor-styles-wrapper .fun-facts');
		await expectNoBlockRecoveryWarnings(editor);
		const styles = await readEditorStyles(editor);

		expect(styles.wrapper).not.toBeNull();
		expect(themeJson.settings.color.defaultGradients).toBe(false);
		expect(themeJson.settings.color.defaultDuotone).toBe(false);
		expect(
			themeJson.settings.color.gradients.map((gradient) => gradient.slug)
		).toContain('deep-purple-to-brand-purple');
		expect(
			themeJson.settings.color.duotone.map((duotone) => duotone.slug)
		).toContain('deep-purple-and-neutral-0');
		expect(styles.tokens.gradientDeepPurpleToBrandPurple).toBe(
			'linear-gradient(135deg, #170145 0%, #3D207E 100%)'
		);
		expect(styles.tokens.gradientNeutral50ToNeutral0).toBe(
			'linear-gradient(135deg, #F0F0F0 0%, #ffffff 100%)'
		);
		expect(styles.markup.headings).toBeGreaterThan(0);
		expect(styles.markup.buttons).toBeGreaterThan(0);
		expect(styles.markup.quotes).toBeGreaterThan(0);
		expect(styles.markup.separators).toBeGreaterThan(0);
		expect(styles.markup.covers).toBeGreaterThan(0);
		expect(styles.markup.funFacts).toBeGreaterThan(0);
		expect(styles.markup.socialLinks).toBeGreaterThan(0);

		expect(styles.heading?.fontFamily).toContain('Rubik');
		expect(styles.heading?.fontWeight).toBe('800');
		expect(styles.heading?.textTransform).toBe('uppercase');
		expect(styles.heading?.marginBottom).toBe('0px');
		expect(styles.heading?.textWrap).toBe('balance');
		expect(
			Number.parseFloat(styles.paragraph?.marginBottom ?? '0')
		).toBeCloseTo(10, 0);
		expect(styles.paragraph?.textWrap).toBe('pretty');
		expect(styles.largeText?.fontSize).toBeTruthy();
		expect(styles.mediumText?.fontSize).toBeTruthy();
		expect(styles.activeDateLabel?.marginBottom).toBe('8px');
		expect(styles.pnsIntroCopyText?.maxWidth).toBe('640px');

		expect(styles.button?.borderRadius).toBe('0px');
		expect(styles.button?.boxShadow).toContain('rgb(123, 220, 181)');
		expect(styles.button?.fontVariationSettings).toContain('"wght" 600');
		expect(cssPixels(styles.button?.paddingTop)).toBeGreaterThan(10);
		expect(cssPixels(styles.button?.paddingRight)).toBeGreaterThan(20);
		expect(cssPixels(styles.button?.paddingBottom)).toBeGreaterThan(10);
		expect(cssPixels(styles.button?.paddingLeft)).toBeGreaterThan(20);
		expect(styles.buttonAfter?.backgroundColor).toBe('rgb(123, 220, 181)');
		expect(styles.quote?.borderLeftWidth).toBe('0px');
		expect(styles.quoteText?.fontFamily).toContain('Rubik');
		expect(styles.separator?.height).toBe('3px');
		expect(styles.separator?.backgroundColor).toBe('rgb(212, 0, 15)');
		expect(styles.cover?.paddingTop).toBe('0px');
		expect(styles.groupWithBackground?.paddingTop).toBe('0px');
		if (styles.semanticGapOwner) {
			expect(styles.semanticGapOwner.gap).toBe('0px');
			expect(styles.semanticGapOwner.marginTop).toBe('0px');
		}
		expect(styles.pnsContentFrame?.maxWidth).toBe('704px');
		expect(styles.activeDates?.maxWidth).toBe('320px');
		expect(styles.funFact?.listStyleType).toBe('none');
		expect(styles.funFact?.lineHeight).toBeTruthy();
		expect(styles.funFact?.marginBottom).toBe('16px');
		expect(styles.funFact?.textWrap).toBe('pretty');
		expect(styles.funFactBefore?.color).toBe('rgb(123, 220, 181)');
		expect(styles.funFactBefore?.content).toBe('"•"');
		expect(styles.funFactBefore?.display).toBe('inline-block');
		expect(styles.funFactBefore?.fontWeight).toBe('700');
		expect(styles.funFactBefore?.width).toBe(styles.funFact?.fontSize);
		expect(styles.funFactBefore?.marginLeft).toBe(
			`-${styles.funFact?.fontSize}`
		);
		expect(styles.socialLinks?.paddingLeft).toBe('0px');
		expect(styles.socialIcon?.height).toBe('48px');
		expect(styles.socialIcon?.width).toBe('48px');
	});

	test('editor canvas applies the light surface contract', async ({
		page,
	}) => {
		const postId = await optionalFixturePageId(page);

		test.skip(
			postId === null,
			`Optional editor fixture "${fixtureSlug}" is not present in this database.`
		);

		if (postId === null) {
			return;
		}

		await openEditor(page, postId);

		const editor = await editorDocument(page);
		await waitForEditorContent(editor);

		const styles = await editor.evaluate(() => {
			const wrapper = document.querySelector('.editor-styles-wrapper');

			if (!wrapper) {
				return null;
			}

			const surface = document.createElement('section');
			surface.className = 'pns-light-surface';
			surface.innerHTML = [
				'<h2 class="wp-block-heading">Light Surface Heading</h2>',
				'<p>Light surface paragraph.</p>',
				'<a href="#light-surface-link">Light surface link</a>',
				'<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#light-surface-button">Light surface button</a></div>',
			].join('');
			wrapper.append(surface);

			const darkSurface = document.createElement('section');
			darkSurface.id = 'pns-editor-dark-surface-fixture';
			darkSurface.className =
				'pns-section pns-dark-surface has-brand-purple-background-color has-background';
			darkSurface.innerHTML = [
				'<h2 id="pns-editor-dark-surface-heading" class="wp-block-heading">Dark Surface Heading</h2>',
				'<p id="pns-editor-dark-surface-copy">Dark surface paragraph.</p>',
				'<p id="pns-editor-dark-surface-explicit" class="has-brand-red-color has-text-color">Explicit editor colour.</p>',
			].join('');
			surface.append(darkSurface);

			function read(selector: string, pseudo?: string) {
				const element = surface.querySelector(selector);

				if (!element) {
					return null;
				}

				const computed = getComputedStyle(element, pseudo);

				return {
					backgroundColor: computed.backgroundColor,
					boxShadow: computed.boxShadow,
					color: computed.color,
				};
			}

			const surfaceStyles = getComputedStyle(surface);
			const hadLightSurfaceTemplateClass = wrapper.classList.contains(
				'is-pns-light-surface-template'
			);
			wrapper.classList.add('is-pns-light-surface-template');

			const pageEditorWrapperStyles = getComputedStyle(wrapper);
			const templateRoot = document.createElement('div');
			templateRoot.className = 'is-root-container is-preview-mode';
			templateRoot.innerHTML =
				'<main class="wp-block-group alignfull pns-template pns-template-page-light-surface pns-light-surface"></main>';
			wrapper.append(templateRoot);

			const templateRootStyles = getComputedStyle(templateRoot);
			const wrapperStyles = getComputedStyle(wrapper);

			const result = {
				button: read('.wp-block-button__link'),
				buttonAfter: read('.wp-block-button__link', '::after'),
				darkSurface: read('#pns-editor-dark-surface-fixture'),
				darkSurfaceCopy: read('#pns-editor-dark-surface-copy'),
				darkSurfaceExplicit: read('#pns-editor-dark-surface-explicit'),
				darkSurfaceHeading: read('#pns-editor-dark-surface-heading'),
				heading: read('.wp-block-heading'),
				link: read('a:not(.wp-block-button__link)'),
				pageEditorWrapper: {
					backgroundColor: pageEditorWrapperStyles.backgroundColor,
					color: pageEditorWrapperStyles.color,
				},
				surface: {
					backgroundColor: surfaceStyles.backgroundColor,
					color: surfaceStyles.color,
				},
				templateRoot: {
					backgroundColor: templateRootStyles.backgroundColor,
					color: templateRootStyles.color,
				},
				text: read('p'),
				wrapper: {
					backgroundColor: wrapperStyles.backgroundColor,
					color: wrapperStyles.color,
				},
			};

			templateRoot.remove();
			darkSurface.remove();
			if (!hadLightSurfaceTemplateClass) {
				wrapper.classList.remove('is-pns-light-surface-template');
			}
			surface.remove();

			return result;
		});

		expect(styles).not.toBeNull();

		if (!styles) {
			return;
		}

		expect(styles.surface.backgroundColor).toBe('rgb(255, 255, 255)');
		expect(styles.surface.color).toBe('rgb(79, 77, 73)');
		expect(styles.pageEditorWrapper.backgroundColor).toBe(
			'rgb(255, 255, 255)'
		);
		expect(styles.pageEditorWrapper.color).toBe('rgb(79, 77, 73)');
		expect(styles.templateRoot.backgroundColor).toBe('rgb(255, 255, 255)');
		expect(styles.templateRoot.color).toBe('rgb(79, 77, 73)');
		expect(styles.wrapper.backgroundColor).toBe('rgb(255, 255, 255)');
		expect(styles.wrapper.color).toBe('rgb(79, 77, 73)');
		expect(styles.heading?.color).toBe('rgb(22, 22, 22)');
		expect(styles.text?.color).toBe('rgb(79, 77, 73)');
		expect(styles.darkSurface?.color).toBe('rgb(255, 255, 255)');
		expect(styles.darkSurfaceHeading?.color).toBe('rgb(255, 255, 255)');
		expect(styles.darkSurfaceCopy?.color).toBe('rgb(255, 255, 255)');
		expect(styles.darkSurfaceExplicit?.color).toBe('rgb(212, 0, 15)');
		expect(styles.link?.color).toBe('rgb(22, 22, 22)');
		expect(styles.button?.backgroundColor).toBe('rgb(61, 32, 126)');
		expect(styles.button?.color).toBe('rgb(255, 255, 255)');
		expect(styles.button?.boxShadow).toContain('rgb(123, 220, 181)');
		expect(styles.buttonAfter?.backgroundColor).toBe('rgb(123, 220, 181)');
	});

	for (const realPage of realPages) {
		test(`real page editor smoke: ${realPage.name}`, async ({ page }) => {
			await openEditor(page, await realPageId(page, realPage));

			const editor = await editorDocument(page);
			await waitForEditorContent(editor);
			const styles = await readEditorStyles(editor);

			expect(styles.wrapper).not.toBeNull();
			expect(themeJson.settings.color.defaultGradients).toBe(false);
			expect(themeJson.settings.color.defaultDuotone).toBe(false);
			expect(styles.tokens.gradientDeepPurpleToBrandPurple).toBe(
				'linear-gradient(135deg, #170145 0%, #3D207E 100%)'
			);
			expect(styles.tokens.gradientNeutral50ToNeutral0).toBe(
				'linear-gradient(135deg, #F0F0F0 0%, #ffffff 100%)'
			);
			expect(styles.markup.headings).toBeGreaterThan(0);
			expect(styles.heading?.fontFamily).toContain('Rubik');
			expect(styles.heading?.fontWeight).toBe('800');

			if (realPage.name === 'pattern-qa') {
				expect(styles.markup.pnsSections).toBeGreaterThan(0);
				expect(cssPixels(styles.pnsSection?.width)).toBeGreaterThan(
					cssPixels(styles.heading?.width) + 100
				);
			}
		});
	}

	test('Membership exposes two editable Text | Text Split Sections without recovery warnings', async ({
		page,
	}) => {
		const membership = realPages.find(
			(realPage) => realPage.name === 'membership'
		);

		expect(membership).toBeDefined();
		await openEditor(page, await realPageId(page, membership!));

		const editor = await editorDocument(page);
		await waitForEditorContent(editor, '[data-type="pns/split-section"]');
		await expectNoBlockRecoveryWarnings(editor);
		await expect(
			editor.locator('[data-type="pns/split-section"]')
		).toHaveCount(2);

		const blockState = await page.evaluate(() => {
			const blocks = wp.data.select('core/block-editor').getBlocks();
			const splitSections = blocks.filter(
				(block: Record<string, unknown>) =>
					block.name === 'pns/split-section'
			);
			const textVariation = wp.blocks
				.getBlockVariations('pns/split-section')
				.find(
					(variation: Record<string, unknown>) =>
						variation.name === 'text-text'
				);

			return {
				columnCounts: splitSections.map(
					(block: Record<string, unknown>) =>
						(
							(
								block.innerBlocks as Array<
									Record<string, unknown>
								>
							)[0]?.innerBlocks as Array<Record<string, unknown>>
						)?.length ?? 0
				),
				layoutVariants: splitSections.map(
					(block: Record<string, unknown>) =>
						(block.attributes as Record<string, unknown>)
							.layoutVariant
				),
				variationRegistered: Boolean(textVariation),
			};
		});

		expect(blockState.layoutVariants).toEqual(['text-text', 'text-text']);
		expect(blockState.columnCounts).toEqual([2, 2]);
		expect(blockState.variationRegistered).toBe(true);
	});

	test('Split Section registers a YouTube embed variation with the shared video column', async ({
		page,
	}) => {
		const membership = realPages.find(
			(realPage) => realPage.name === 'membership'
		);

		expect(membership).toBeDefined();
		await openEditor(page, await realPageId(page, membership!));

		const variation = await page.evaluate(() => {
			const youtubeVariation = wp.blocks
				.getBlockVariations('pns/split-section')
				.find(
					(registeredVariation: Record<string, unknown>) =>
						registeredVariation.name === 'youtube'
				);
			const columns = youtubeVariation?.innerBlocks?.[0];
			const mediaColumn = columns?.[2]?.[1];
			const mediaBlock = mediaColumn?.[2]?.[0];

			return {
				mediaBlockName: mediaBlock?.[0],
				mediaColumnClassName: mediaColumn?.[1]?.className,
				mediaType: youtubeVariation?.attributes?.mediaType,
				providerNameSlug: mediaBlock?.[1]?.providerNameSlug,
			};
		});

		expect(variation).toEqual({
			mediaBlockName: 'core/embed',
			mediaColumnClassName:
				'pns-split-section__media-column pns-split-section__media-column--video',
			mediaType: 'youtube',
			providerNameSlug: 'youtube',
		});
	});

	test('Split Section component guide exposes the complete Text | Text matrix', async ({
		page,
	}) => {
		const componentGuide = realPages.find(
			(realPage) => realPage.name === 'split-section-components'
		);

		expect(componentGuide).toBeDefined();
		await openEditor(page, await realPageId(page, componentGuide!));

		const editor = await editorDocument(page);
		await waitForEditorContent(
			editor,
			'[data-type="pns/split-section"].is-pns-text-text'
		);
		await expectNoBlockRecoveryWarnings(editor);

		const matrixState = await page.evaluate(() => {
			const blocks = wp.data.select('core/block-editor').getBlocks();
			const splitSections = blocks.filter(
				(block: Record<string, unknown>) =>
					block.name === 'pns/split-section'
			);
			const textSections = blocks.filter(
				(block: Record<string, unknown>) =>
					block.name === 'pns/split-section' &&
					(block.attributes as Record<string, unknown>).mediaType ===
						'text'
			);

			const sections = textSections.map(
				(block: Record<string, unknown>) => {
					const columnsBlock = (
						block.innerBlocks as Array<Record<string, unknown>>
					)[0];
					const columns =
						(columnsBlock?.innerBlocks as Array<
							Record<string, unknown>
						>) || [];

					return {
						anchor: (block.attributes as Record<string, unknown>)
							.anchor,
						clientId: block.clientId,
						columnColours: columns.map((column) => ({
							backgroundColor: (
								column.attributes as Record<string, unknown>
							).backgroundColor,
							lock: (column.attributes as Record<string, unknown>)
								.lock,
							textColor: (
								column.attributes as Record<string, unknown>
							).textColor,
						})),
						columnCount: columns.length,
						layoutVariant: (
							block.attributes as Record<string, unknown>
						).layoutVariant,
						mediaType: (block.attributes as Record<string, unknown>)
							.mediaType,
						secondaryTextVerticalAlignment: (
							block.attributes as Record<string, unknown>
						).secondaryTextVerticalAlignment,
						textVerticalAlignment: (
							block.attributes as Record<string, unknown>
						).textVerticalAlignment,
					};
				}
			);
			const copyGroups = splitSections.flatMap(
				(block: Record<string, unknown>) => {
					const anchor = (block.attributes as Record<string, unknown>)
						.anchor;
					const columnsBlock = (
						block.innerBlocks as Array<Record<string, unknown>>
					)[0];
					const columns =
						(columnsBlock?.innerBlocks as Array<
							Record<string, unknown>
						>) || [];

					return columns.flatMap((column, panelIndex) => {
						const copy = (
							(column.innerBlocks as Array<
								Record<string, unknown>
							>) || []
						).find(
							(innerBlock) =>
								innerBlock.name === 'core/group' &&
								String(
									(
										innerBlock.attributes as Record<
											string,
											unknown
										>
									).className
								).includes('pns-split-section__copy')
						);

						if (!copy) {
							return [];
						}

						return [
							{
								anchor,
								clientId: copy.clientId,
								layout: (
									copy.attributes as Record<string, unknown>
								).layout as Record<string, unknown>,
								panelIndex,
							},
						];
					});
				}
			);

			return {
				copyGroups,
				mediaSectionClientId: splitSections.find(
					(block: Record<string, unknown>) =>
						(block.attributes as Record<string, unknown>)
							.mediaType === 'video'
				)?.clientId,
				sections,
				topSectionClientId: sections.find(
					(section) =>
						section.anchor ===
						'pns-text-text-demo-text-text-long-short'
				)?.clientId,
			};
		});

		expect(matrixState.sections).toHaveLength(8);
		expect(
			matrixState.sections.filter(
				(section) =>
					section.layoutVariant === 'edge-media-left' ||
					section.layoutVariant === 'edge-media-right'
			)
		).toHaveLength(4);
		expect(
			matrixState.sections.filter(
				(section) => section.layoutVariant === 'media-right'
			)
		).toHaveLength(4);
		expect(
			matrixState.sections.every(
				(section) => section.mediaType === 'text'
			)
		).toBe(true);
		expect(
			matrixState.sections.every((section) => section.columnCount === 2)
		).toBe(true);
		expect(
			matrixState.sections.every(
				(section) =>
					section.columnColours[0].backgroundColor ===
						'brand-purple' &&
					section.columnColours[1].backgroundColor ===
						'heritage-green' &&
					section.columnColours.every(
						(column) =>
							column.textColor === 'neutral-0' && !column.lock
					)
			)
		).toBe(true);
		expect(
			matrixState.sections.every((section) =>
				String(section.anchor).startsWith('pns-text-text-demo-')
			)
		).toBe(true);
		expect(
			matrixState.sections.filter(
				(section) => section.layoutVariant === 'edge-media-left'
			)
		).toEqual([
			expect.objectContaining({
				anchor: 'pns-text-text-demo-text-text-long-short',
				mediaType: 'text',
			}),
		]);
		expect(matrixState.copyGroups).toHaveLength(28);
		expect(
			matrixState.copyGroups.every((copy) => copy.layout?.type !== 'flex')
		).toBe(true);
		expect(
			matrixState.sections.filter(
				(section) => section.textVerticalAlignment === 'top'
			)
		).toHaveLength(2);
		expect(
			matrixState.sections.filter(
				(section) => section.secondaryTextVerticalAlignment === 'top'
			)
		).toHaveLength(2);
		expect(matrixState.topSectionClientId).toBeTruthy();
		expect(matrixState.mediaSectionClientId).toBeTruthy();

		await page.evaluate(() => {
			const reversedBlock = wp.data
				.select('core/block-editor')
				.getBlocks()
				.find(
					(block: Record<string, unknown>) =>
						(block.attributes as Record<string, unknown>).anchor ===
						'pns-text-text-demo-text-text-long-short'
				);

			if (!reversedBlock) {
				throw new Error(
					'Could not find the reversed Text | Text demo.'
				);
			}

			wp.data
				.dispatch('core/block-editor')
				.selectBlock(reversedBlock.clientId);
		});

		const settingsButton = page.locator('button[aria-label="Settings"]');

		if ((await settingsButton.getAttribute('aria-pressed')) !== 'true') {
			await settingsButton.click();
		}

		const blockInspector = page.locator('.interface-complementary-area');
		await expect(
			blockInspector.getByRole('combobox', { name: 'Layout' })
		).toHaveValue('edge-media-left');
		await expect(
			blockInspector.getByRole('radio', { name: 'Text' })
		).toHaveAttribute('aria-checked', 'true');
		await blockInspector.getByRole('tab', { name: 'Settings' }).click();

		await expect(
			blockInspector.getByRole('combobox', {
				name: 'First text panel vertical alignment',
			})
		).toHaveValue('center');
		await expect(
			blockInspector.getByRole('combobox', {
				name: 'Second text panel vertical alignment',
			})
		).toHaveValue('top');

		await page.evaluate((clientId) => {
			wp.data.dispatch('core/block-editor').selectBlock(clientId);
		}, matrixState.mediaSectionClientId);
		await blockInspector.getByRole('tab', { name: 'Settings' }).click();

		await expect(
			blockInspector.getByRole('combobox', {
				name: 'Text vertical alignment',
			})
		).toHaveValue('center');
		await expect(
			blockInspector.getByRole('combobox', {
				name: 'Second text panel vertical alignment',
			})
		).toHaveCount(0);
		await expect(
			editor.locator(
				'[data-type="pns/split-section"]:not(.is-pns-text-text)[class*="is-pns-secondary-text-align-"]'
			)
		).toHaveCount(0);
	});

	test('full-width news posts expose locked PNS Post Details inside the content-owned hero', async ({
		page,
	}) => {
		await openEditor(page, await fullWidthNewsPostId(page));

		const editor = await editorDocument(page);
		await waitForEditorContent(
			editor,
			'.is-root-container > .wp-block-ran-enhanced-cover.pns-page-hero:first-child'
		);

		const hero = editor.locator(
			'.is-root-container > .wp-block-ran-enhanced-cover.pns-page-hero:first-child'
		);
		const detailsBlock = hero.locator('[data-type="pns/post-details"]');

		await expect(hero).toHaveCount(1);
		await expect(detailsBlock).toHaveCount(1);
		await expect(detailsBlock).toContainText('Protests and Suffragettes');
		await expect(
			detailsBlock.locator('.wp-block-pns-post-metadata')
		).toHaveCount(1);
		await expect(
			detailsBlock.locator('[data-type="pns/post-metadata"]')
		).toHaveCount(0);
		await expect(detailsBlock.locator('.pns-single-terms')).toHaveCount(1);
		await expect(page.locator('.pns-editorial-header-panel')).toHaveCount(
			0
		);

		const editorLayout = await editor.evaluate(() => {
			const hero = document.querySelector(
				'.is-root-container > .wp-block-ran-enhanced-cover.pns-page-hero:first-child'
			);
			const firstBodyBlock = hero?.nextElementSibling;

			return {
				bodyMarginBlockStart: firstBodyBlock
					? getComputedStyle(firstBodyBlock).marginBlockStart
					: null,
			};
		});

		expect(editorLayout.bodyMarginBlockStart).toBe('32px');

		const blockState = await page.evaluate(() => {
			const blocks = wp.data.select('core/block-editor').getBlocks();
			const findBlock = (items: Array<Record<string, unknown>>) => {
				for (const item of items) {
					if (item.name === 'pns/post-details') {
						return item;
					}
					const match = findBlock(
						(item.innerBlocks as Array<Record<string, unknown>>) ||
							[]
					);

					if (match) {
						return match;
					}
				}

				return null;
			};
			const metadata = findBlock(
				blocks as Array<Record<string, unknown>>
			);

			return {
				firstBlock: blocks[0]?.name,
				detailsLock: metadata?.attributes?.lock,
			};
		});

		expect(blockState.firstBlock).toBe('ran/enhanced-cover');
		expect(blockState.detailsLock).toEqual({ move: true, remove: true });

		await detailsBlock.click();
		const settingsButton = page.locator('button[aria-label="Settings"]');

		if ((await settingsButton.getAttribute('aria-pressed')) !== 'true') {
			await settingsButton.click();
		}

		const detailsInspector = page
			.locator('.components-panel__body')
			.filter({ hasText: 'PNS Post Details' });

		await expect(detailsInspector).toBeVisible();
		await expect(
			detailsInspector.locator(
				'input, select, textarea, [role="combobox"]'
			)
		).toHaveCount(0);
	});

	test('standard single posts use the light editor surface', async ({
		page,
	}) => {
		await openEditor(page, await standardSinglePostId(page));

		const editor = await editorDocument(page);
		await waitForEditorContent(editor, '.editor-styles-wrapper');
		await editor.waitForFunction(() =>
			document.body.classList.contains('is-pns-light-surface-template')
		);

		const surface = await editor.evaluate(() => {
			const wrapper = document.querySelector<HTMLElement>(
				'.editor-styles-wrapper'
			);

			if (!wrapper) {
				return null;
			}

			const styles = getComputedStyle(wrapper);

			return {
				backgroundColor: styles.backgroundColor,
				color: styles.color,
			};
		});

		expect(surface).toEqual({
			backgroundColor: 'rgb(255, 255, 255)',
			color: 'rgb(79, 77, 73)',
		});
	});

	test('the Style Guide uses wide section frames without block recovery', async ({
		page,
	}) => {
		const styleGuide = realPages.find(
			(realPage) => realPage.name === 'style-guide'
		);

		if (!styleGuide) {
			throw new Error('Style Guide editor config is missing.');
		}

		await openEditor(page, await realPageId(page, styleGuide));

		if (
			await page
				.getByText('This post is already being edited')
				.isVisible()
				.catch(() => false)
		) {
			test.skip(
				true,
				'Style Guide is actively being edited; do not take over a user session during a regression run.'
			);
		}

		const editor = await editorDocument(page);
		await waitForEditorContent(
			editor,
			'.is-root-container > .pns-style-guide-component-examples'
		);
		await editor.waitForFunction(() =>
			document.body.classList.contains('is-pns-light-surface-template')
		);
		await expectNoBlockRecoveryWarnings(editor);

		const result = await editor.evaluate(() => {
			const wrapper = document.querySelector<HTMLElement>(
				'.editor-styles-wrapper'
			);
			const root =
				document.querySelector<HTMLElement>('.is-root-container');

			if (!wrapper || !root) {
				return null;
			}

			const rootRect = root.getBoundingClientRect();
			const wideSize = Number.parseFloat(
				getComputedStyle(root).getPropertyValue(
					'--wp--style--global--wide-size'
				)
			);
			const sections = Array.from(
				root.querySelectorAll<HTMLElement>(
					':scope > .pns-section.pns-text-only-section'
				)
			);
			const frames = sections
				.map((section) =>
					section.querySelector<HTMLElement>('.pns-section-frame')
				)
				.filter((frame): frame is HTMLElement => Boolean(frame));

			return {
				surface: {
					backgroundColor: getComputedStyle(wrapper).backgroundColor,
					color: getComputedStyle(wrapper).color,
				},
				root: {
					left: rootRect.left,
					width: rootRect.width,
				},
				wideSize,
				sections: sections.length,
				frames: frames.map((frame) => {
					const rect = frame.getBoundingClientRect();
					const section = frame.closest<HTMLElement>(
						'.pns-text-only-section'
					);
					const sectionRect = section?.getBoundingClientRect();
					const sectionStyles = section
						? getComputedStyle(section)
						: null;

					return {
						width: rect.width,
						left: rect.left,
						paddingInlineStart:
							getComputedStyle(frame).paddingInlineStart,
						sectionLeft: sectionRect?.left,
						sectionWidth: sectionRect?.width,
						sectionPaddingInlineStart:
							sectionStyles?.paddingInlineStart,
						sectionPaddingInlineEnd:
							sectionStyles?.paddingInlineEnd,
					};
				}),
			};
		});
		const savedContent = await page.evaluate(() =>
			wp.data.select('core/editor').getEditedPostAttribute('content')
		);

		expect(result).not.toBeNull();

		if (!result) {
			return;
		}

		expect(result.surface).toEqual({
			backgroundColor: 'rgb(255, 255, 255)',
			color: 'rgb(79, 77, 73)',
		});
		expect(result.sections).toBe(9);
		expect(result.frames).toHaveLength(9);
		expect(savedContent).not.toContain('var(u002d');
		expect(savedContent).not.toMatch(/<p[^>]*>\s*<p[^>]*>/);
		expect(savedContent).toContain('pns-style-guide-component-examples');
		expect(savedContent).toContain('pns/post-card');
		expect(savedContent).toContain('pns/post-card-horizontal');
		expect(savedContent).not.toContain('Community card example');
		expect(savedContent).not.toContain('pns-style-guide-card-grid');
		expect(savedContent).not.toContain('pns-style-guide-pill-examples');

		for (const frame of result.frames) {
			const sectionWidth = frame.sectionWidth || 0;
			const sectionLeft = frame.sectionLeft || 0;
			const sectionPaddingStart = Number.parseFloat(
				frame.sectionPaddingInlineStart || '0'
			);
			const sectionPaddingEnd = Number.parseFloat(
				frame.sectionPaddingInlineEnd || '0'
			);
			const availableWidth =
				sectionWidth - sectionPaddingStart - sectionPaddingEnd;
			const expectedWidth = Math.min(availableWidth, result.wideSize);
			const expectedLeft =
				sectionLeft +
				sectionPaddingStart +
				(availableWidth - expectedWidth) / 2;

			expect(Math.abs(frame.width - expectedWidth)).toBeLessThanOrEqual(
				2
			);
			expect(Math.abs(frame.left - expectedLeft)).toBeLessThanOrEqual(1);
			expect(Number.parseFloat(frame.paddingInlineStart)).toBeGreaterThan(
				0
			);
		}
	});

	test('opening Covers use the editor canvas while Video Cover content keeps the shared rail', async ({
		page,
	}) => {
		const contact = realPages.find(
			(realPage) => realPage.name === 'contact'
		);

		if (!contact) {
			throw new Error('Contact editor config is missing.');
		}

		await openEditor(page, await realPageId(page, contact));

		const editor = await editorDocument(page);
		await waitForEditorContent(
			editor,
			'.is-root-container > :is(.wp-block-cover, .wp-block-ran-enhanced-cover):first-child'
		);

		const geometry = await editor.evaluate(() => {
			const root =
				document.querySelector<HTMLElement>('.is-root-container');
			const hero = root?.querySelector<HTMLElement>(
				':scope > :is(.wp-block-cover, .wp-block-ran-enhanced-cover):first-child'
			);

			if (!root || !hero) {
				return null;
			}

			const rootRect = root.getBoundingClientRect();
			const heroRect = hero.getBoundingClientRect();
			const videoContent = hero.querySelector<HTMLElement>(
				':scope > .ran-video-cover__content'
			);
			const videoContentRect = videoContent?.getBoundingClientRect();
			const videoContentStyles = videoContent
				? getComputedStyle(videoContent)
				: null;

			return {
				heroLeft: heroRect.left,
				heroWidth: heroRect.width,
				isVideoCover: hero.classList.contains(
					'wp-block-ran-enhanced-cover'
				),
				rootLeft: rootRect.left,
				rootWidth: rootRect.width,
				videoContentLeft: videoContentRect?.left,
				videoContentPaddingStart:
					videoContentStyles?.paddingInlineStart,
				videoContentWidth: videoContentRect?.width,
			};
		});

		expect(geometry).not.toBeNull();

		if (!geometry) {
			return;
		}

		expect(geometry.heroWidth).toBeGreaterThanOrEqual(
			geometry.rootWidth - 2
		);
		expect(
			Math.abs(geometry.heroLeft - geometry.rootLeft)
		).toBeLessThanOrEqual(1);

		if (geometry.isVideoCover) {
			expect(geometry.videoContentWidth).toBeGreaterThanOrEqual(
				geometry.rootWidth - 2
			);
			expect(
				cssPixels(geometry.videoContentPaddingStart)
			).toBeGreaterThan(0);
		}
	});

	test('Shop opening Core Cover uses the editor canvas', async ({ page }) => {
		const shop = realPages.find((realPage) => realPage.name === 'shop');

		if (!shop) {
			throw new Error('Shop editor config is missing.');
		}

		await openEditor(page, await realPageId(page, shop));

		const editor = await editorDocument(page);
		await waitForEditorContent(
			editor,
			'.is-root-container > .wp-block-cover:first-child'
		);

		const geometry = await editor.evaluate(() => {
			const root =
				document.querySelector<HTMLElement>('.is-root-container');
			const hero = root?.querySelector<HTMLElement>(
				':scope > .wp-block-cover:first-child'
			);
			const storefront = root?.querySelector<HTMLElement>(
				':scope > .pns-shop-storefront.pns-site-frame-panel'
			);

			if (!root || !hero || !storefront) {
				return null;
			}

			const rootRect = root.getBoundingClientRect();
			const heroRect = hero.getBoundingClientRect();
			const storefrontRect = storefront.getBoundingClientRect();

			return {
				heroLeft: heroRect.left,
				heroWidth: heroRect.width,
				rootLeft: rootRect.left,
				rootWidth: rootRect.width,
				storefrontLeft: storefrontRect.left,
				storefrontWidth: storefrontRect.width,
				wideSize: parseFloat(
					getComputedStyle(root).getPropertyValue(
						'--pns--layout--wide-size'
					)
				),
			};
		});

		expect(geometry).not.toBeNull();

		if (!geometry) {
			return;
		}

		expect(geometry.heroWidth).toBeGreaterThanOrEqual(
			geometry.rootWidth - 2
		);
		expect(
			Math.abs(geometry.heroLeft - geometry.rootLeft)
		).toBeLessThanOrEqual(1);
		const storefrontExpectedWidth = Math.min(
			geometry.rootWidth,
			geometry.wideSize
		);

		expect(
			Math.abs(geometry.storefrontWidth - storefrontExpectedWidth)
		).toBeLessThanOrEqual(2);
		expect(
			Math.abs(
				geometry.storefrontLeft -
					geometry.rootLeft +
					(storefrontExpectedWidth - geometry.rootWidth) / 2
			)
		).toBeLessThanOrEqual(1);
	});

	test('reusable site-frame panels use the editor canvas', async ({
		page,
	}) => {
		const giveaway = realPages.find(
			(realPage) => realPage.name === 'edu-giveaway'
		);

		if (!giveaway) {
			throw new Error(
				'Education Pack Giveaway editor config is missing.'
			);
		}

		await openEditor(page, await realPageId(page, giveaway));

		const editor = await editorDocument(page);
		await waitForEditorContent(
			editor,
			'.is-root-container > .wp-block-block.is-reusable > .pns-shop-intro.pns-site-frame-panel'
		);

		const geometry = await editor.evaluate(() => {
			const root =
				document.querySelector<HTMLElement>('.is-root-container');
			const shopIntro = root?.querySelector<HTMLElement>(
				':scope > .wp-block-block.is-reusable > .pns-shop-intro.pns-site-frame-panel'
			);
			const reusable = shopIntro?.closest<HTMLElement>(
				'.wp-block-block.is-reusable'
			);

			if (!root || !reusable || !shopIntro) {
				return null;
			}

			const rootRect = root.getBoundingClientRect();
			const reusableRect = reusable.getBoundingClientRect();
			const shopIntroRect = shopIntro.getBoundingClientRect();

			return {
				rootLeft: rootRect.left,
				rootWidth: rootRect.width,
				reusableLeft: reusableRect.left,
				reusableWidth: reusableRect.width,
				shopIntroLeft: shopIntroRect.left,
				shopIntroWidth: shopIntroRect.width,
				wideSize: parseFloat(
					getComputedStyle(root).getPropertyValue(
						'--pns--layout--wide-size'
					)
				),
			};
		});

		expect(geometry).not.toBeNull();

		if (!geometry) {
			return;
		}

		expect(geometry.reusableWidth).toBeGreaterThanOrEqual(
			geometry.rootWidth - 2
		);
		expect(
			Math.abs(geometry.reusableLeft - geometry.rootLeft)
		).toBeLessThanOrEqual(1);

		const expectedPanelWidth = Math.min(
			geometry.rootWidth,
			geometry.wideSize
		);
		expect(
			Math.abs(geometry.shopIntroWidth - expectedPanelWidth)
		).toBeLessThanOrEqual(2);
		expect(
			Math.abs(
				geometry.shopIntroLeft -
					geometry.rootLeft +
					(expectedPanelWidth - geometry.rootWidth) / 2
			)
		).toBeLessThanOrEqual(1);
	});

	test('Home Split Sections and quote panels share the editor site frame', async ({
		page,
	}) => {
		const home = realPages.find((realPage) => realPage.name === 'home');

		if (!home) {
			throw new Error('Home editor config is missing.');
		}

		await openEditor(page, await realPageId(page, home));

		const editor = await editorDocument(page);
		await waitForEditorContent(
			editor,
			'.is-root-container > .pns-split-section'
		);

		const geometry = await editor.evaluate(() => {
			const root =
				document.querySelector<HTMLElement>('.is-root-container');
			const split = root?.querySelector<HTMLElement>(
				':scope > .pns-split-section'
			);
			const quote = root?.querySelector<HTMLElement>(
				':scope > .pns-quotes.pns-site-frame-panel'
			);

			if (!root || !split || !quote) {
				return null;
			}

			const toEdges = (element: HTMLElement) => {
				const rect = element.getBoundingClientRect();

				return {
					left: rect.left,
					right: rect.right,
					width: rect.width,
				};
			};

			return {
				quote: toEdges(quote),
				root: toEdges(root),
				split: toEdges(split),
			};
		});

		expect(geometry).not.toBeNull();

		if (!geometry) {
			return;
		}

		for (const panel of [geometry.split, geometry.quote]) {
			expect(panel.width).toBeGreaterThanOrEqual(geometry.root.width - 2);
			expect(
				Math.abs(panel.left - geometry.root.left)
			).toBeLessThanOrEqual(1);
			expect(
				Math.abs(panel.right - geometry.root.right)
			).toBeLessThanOrEqual(1);
		}
	});

	test('synced PNS sections do not inherit editor root block gaps', async ({
		page,
	}) => {
		const home = realPages.find((realPage) => realPage.name === 'home');

		if (!home) {
			throw new Error('Home editor config is missing.');
		}

		await openEditor(page, await realPageId(page, home));

		const editor = await editorDocument(page);
		await waitForEditorContent(
			editor,
			'.editor-styles-wrapper .wp-block.is-reusable.wp-block-block'
		);

		const syncedSections = await editor.evaluate(() =>
			Array.from(
				document.querySelectorAll<HTMLElement>(
					'.is-root-container > .wp-block.is-reusable.wp-block-block'
				)
			).map((section) => {
				const computed = getComputedStyle(section);

				return {
					marginTop: computed.marginTop,
					paddingTop: computed.paddingTop,
				};
			})
		);

		expect(syncedSections.length).toBeGreaterThanOrEqual(2);

		for (const section of syncedSections) {
			expect(section.marginTop).toBe('0px');
			expect(section.paddingTop).toBe('0px');
		}
	});

	test('PNS quote covers retain the frontend quote treatment in the editor', async ({
		page,
	}) => {
		const patternQa = realPages.find(
			(realPage) => realPage.name === 'pattern-qa'
		);

		if (!patternQa) {
			throw new Error('Pattern QA page config is missing.');
		}

		await openEditor(page, await realPageId(page, patternQa));

		const editor = await editorDocument(page);
		const selector =
			'.wp-block-cover.pns-quotes.pns-blockquote-with-red-line .wp-block-quote';
		await waitForEditorContent(editor, selector);

		const quote = await editor.evaluate((quoteSelector) => {
			const quoteElement =
				document.querySelector<HTMLElement>(quoteSelector);
			const textElement =
				quoteElement?.querySelector<HTMLElement>(':scope > p');

			if (!quoteElement || !textElement) {
				return null;
			}

			const quoteStyle = getComputedStyle(quoteElement);
			const textStyle = getComputedStyle(textElement);

			return {
				borderInlineStartWidth: quoteStyle.borderInlineStartWidth,
				borderLeftWidth: quoteStyle.borderLeftWidth,
				fontFamily: textStyle.fontFamily,
				fontVariationSettings: textStyle.fontVariationSettings,
				fontWeight: textStyle.fontWeight,
			};
		}, selector);

		expect(quote).not.toBeNull();

		if (!quote) {
			return;
		}

		expect(quote.borderInlineStartWidth).toBe('0px');
		expect(quote.borderLeftWidth).toBe('0px');
		expect(quote.fontFamily).toContain('Rubik');
		expect(quote.fontWeight).toBe('800');
		expect(quote.fontVariationSettings).toContain('"wght" 800');
	});

	test('dark PNS surfaces keep empty-block guidance legible in the editor', async ({
		page,
	}) => {
		const patternQa = realPages.find(
			(realPage) => realPage.name === 'pattern-qa'
		);

		if (!patternQa) {
			throw new Error('Pattern QA page config is missing.');
		}

		await openEditor(page, await realPageId(page, patternQa));

		const editor = await editorDocument(page);
		await waitForEditorContent(editor, '.editor-styles-wrapper');

		const placeholder = await editor.evaluate(() => {
			const root = document.querySelector('.is-root-container');
			const appender = document.createElement('div');
			const fixture = document.createElement('div');
			const heroFixture = document.createElement('section');
			const rootEmptyParagraph = document.createElement('p');

			appender.className = 'block-list-appender';
			appender.innerHTML =
				'<p class="block-editor-default-block-appender__content">Type / to choose a block</p>';
			fixture.className =
				'wp-block-group pns-section has-brand-purple-background-color';
			fixture.innerHTML =
				'<p class="block-editor-rich-text__editable wp-block-paragraph" data-empty="true"><span data-rich-text-placeholder="Type / to choose a block"></span></p>';
			heroFixture.className =
				'wp-block-ran-enhanced-cover pns-section pns-page-hero';
			heroFixture.innerHTML =
				'<div class="ran-video-cover__content"><div class="block-editor-inner-blocks"><div class="block-editor-block-list__layout"><div class="block-list-appender"><div class="block-editor-default-block-appender"><p class="block-editor-default-block-appender__content">Type / to choose a block</p></div></div></div></div></div>';
			rootEmptyParagraph.className =
				'block-editor-rich-text__editable wp-block-paragraph';
			rootEmptyParagraph.dataset.empty = 'true';
			rootEmptyParagraph.innerHTML =
				'<span data-rich-text-placeholder="Type / to choose a block"></span>';
			root?.append(fixture);
			root?.append(heroFixture);
			root?.append(appender);
			root?.append(rootEmptyParagraph);

			try {
				const editable = fixture.querySelector<HTMLElement>(
					'.block-editor-rich-text__editable[data-empty="true"]'
				);
				const text = editable?.querySelector<HTMLElement>(
					':scope > [data-rich-text-placeholder]'
				);

				if (!editable || !text) {
					return null;
				}
				const appenderText = appender.querySelector<HTMLElement>(
					'.block-editor-default-block-appender__content'
				);

				if (!appenderText) {
					return null;
				}
				const heroAppenderText = heroFixture.querySelector<HTMLElement>(
					'.block-editor-default-block-appender__content'
				);

				if (!heroAppenderText) {
					return null;
				}
				const rootPlaceholder =
					rootEmptyParagraph.querySelector<HTMLElement>(
						':scope > [data-rich-text-placeholder]'
					);

				if (!rootPlaceholder) {
					return null;
				}

				return {
					appenderColor: getComputedStyle(appenderText).color,
					appenderOpacity: getComputedStyle(appenderText).opacity,
					editableColor: getComputedStyle(editable).color,
					heroAppenderColor: getComputedStyle(heroAppenderText).color,
					heroAppenderOpacity:
						getComputedStyle(heroAppenderText).opacity,
					rootEditableColor:
						getComputedStyle(rootEmptyParagraph).color,
					rootTextColor: getComputedStyle(rootPlaceholder).color,
					text: text.getAttribute('data-rich-text-placeholder'),
					textColor: getComputedStyle(text).color,
				};
			} finally {
				rootEmptyParagraph.remove();
				appender.remove();
				heroFixture.remove();
				fixture.remove();
			}
		});

		expect(placeholder).not.toBeNull();
		expect(placeholder?.appenderColor).toBe('rgb(255, 255, 255)');
		expect(placeholder?.appenderOpacity).toBe('1');
		expect(placeholder?.heroAppenderColor).toBe('rgb(255, 255, 255)');
		expect(placeholder?.heroAppenderOpacity).toBe('1');
		expect(placeholder?.text).toBe('Type / to choose a block');
		expect(placeholder?.editableColor).toBe('rgb(255, 255, 255)');
		expect(placeholder?.textColor).toBe('rgb(255, 255, 255)');
		expect(placeholder?.rootEditableColor).toBe('rgb(255, 255, 255)');
		expect(placeholder?.rootTextColor).toBe('rgb(255, 255, 255)');
	});

	test('split-section layout controls move copy and media in the editor canvas', async ({
		page,
	}) => {
		const patternQa = realPages.find(
			(realPage) => realPage.name === 'pattern-qa'
		);

		if (!patternQa) {
			throw new Error('Pattern QA page config is missing.');
		}

		await openEditor(page, await realPageId(page, patternQa));

		const editor = await editorDocument(page);
		await waitForEditorContent(
			editor,
			'.editor-styles-wrapper .pns-split-section'
		);
		await expectNoBlockRecoveryWarnings(
			editor,
			'[data-type="pns/split-section"]'
		);
		await editor.waitForFunction(() =>
			Array.from(
				document.querySelectorAll<HTMLElement>(
					'.pns-split-section .pns-split-section__media-column .wp-block-jetpack-slideshow_image'
				)
			).some((image) => image.getBoundingClientRect().height > 300)
		);

		const variants = await editor.evaluate(() => {
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

			return variantNames.map((variant) => {
				section.classList.remove(...variantClasses);
				section.classList.add(`is-style-pns-${variant}`);

				const copy = section.querySelector<HTMLElement>(
					'.pns-split-section__copy-column'
				);
				const media = section.querySelector<HTMLElement>(
					'.pns-split-section__media-column'
				);
				const columns = section.querySelector<HTMLElement>(
					'.pns-split-section__columns'
				);

				if (!copy || !media || !columns) {
					return null;
				}

				const layout =
					columns.querySelector<HTMLElement>(
						':scope > .block-editor-inner-blocks > .block-editor-block-list__layout'
					) ?? columns;
				const copyRect = copy.getBoundingClientRect();
				const mediaRect = media.getBoundingClientRect();
				const layoutRect = layout.getBoundingClientRect();

				return {
					copyBeforeMedia:
						Math.round(copyRect.left) === Math.round(mediaRect.left)
							? copyRect.top < mediaRect.top
							: copyRect.left < mediaRect.left,
					display: getComputedStyle(layout).display,
					layoutLeft: Math.round(layoutRect.left),
					layoutRight: Math.round(layoutRect.right),
					variant,
				};
			});
		});

		expect(variants).not.toBeNull();

		if (!variants) {
			return;
		}

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
					copyBeforeMedia: false,
					display: 'grid',
					variant: 'edge-media-left',
				}),
				expect.objectContaining({
					copyBeforeMedia: true,
					display: 'grid',
					variant: 'edge-media-right',
				}),
			])
		);

		const mediaStates = await editor.evaluate(() => {
			function read(element: Element | null) {
				if (!(element instanceof HTMLElement)) {
					return null;
				}

				const computed = getComputedStyle(element);
				const rect = element.getBoundingClientRect();

				return {
					height: computed.height,
					objectFit: computed.objectFit,
					overflow: computed.overflow,
					rectHeight: rect.height,
					rectWidth: rect.width,
					width: computed.width,
				};
			}

			return Array.from(
				document.querySelectorAll<HTMLElement>('.pns-split-section')
			).map((section) => {
				const mediaColumn = section.querySelector<HTMLElement>(
					'.pns-split-section__media-column'
				);
				const image = section.querySelector<HTMLElement>(
					'.pns-split-section__media-column .wp-block-image img'
				);
				const slideshowImage = section.querySelector<HTMLElement>(
					'.pns-split-section__media-column .wp-block-jetpack-slideshow_image'
				);
				const videoEmbed = section.querySelector<HTMLElement>(
					'.pns-split-section__media-column--video > .wp-block-embed.wp-has-aspect-ratio'
				);

				return {
					image: read(image),
					mediaColumn: read(mediaColumn),
					slideshowImage: read(slideshowImage),
					type: videoEmbed
						? 'video'
						: slideshowImage
							? 'slideshow'
							: image
								? 'image'
								: 'unknown',
					videoEmbed: read(videoEmbed),
				};
			});
		});

		expect(mediaStates.map((state) => state.type)).toEqual(
			expect.arrayContaining(['image', 'slideshow', 'video'])
		);

		for (const state of mediaStates) {
			expect(state.mediaColumn?.rectWidth).toBeGreaterThan(300);

			if (state.type === 'image') {
				expect(state.mediaColumn?.overflow).toBe('hidden');
				expect(state.mediaColumn?.rectHeight).toBeGreaterThan(300);
				expect(state.image?.objectFit).toBe('cover');
				expect(state.image?.rectHeight).toBeGreaterThan(300);
			}

			if (state.type === 'slideshow') {
				expect(state.mediaColumn?.overflow).toBe('hidden');
				expect(state.mediaColumn?.rectHeight).toBeGreaterThan(300);
				expect(state.slideshowImage?.objectFit).toBe('cover');
				expect(state.slideshowImage?.rectHeight).toBeGreaterThan(300);
			}

			if (state.type === 'video') {
				expect(state.videoEmbed).not.toBeNull();
				expect(state.videoEmbed?.rectHeight).toBeGreaterThan(
					(state.videoEmbed?.rectWidth ?? 0) / 2
				);
				expect(cssPixels(state.videoEmbed?.height)).toBeGreaterThan(
					300
				);
			}
		}

		const desktopMediaGeometry = await editor.evaluate(() => {
			const variantClasses = [
				'is-style-pns-media-left',
				'is-style-pns-media-right',
				'is-style-pns-edge-media-left',
				'is-style-pns-edge-media-right',
			];
			const sections = Array.from(
				document.querySelectorAll<HTMLElement>('.pns-split-section')
			);
			const imageSection = sections.find((section) =>
				section.querySelector(
					'.pns-split-section__media-column .wp-block-image img'
				)
			);
			const slideshowSection = sections.find((section) =>
				section.querySelector(
					'.pns-split-section__media-column .wp-block-jetpack-slideshow_image'
				)
			);

			if (!imageSection || !slideshowSection) {
				return null;
			}

			const originalState = [imageSection, slideshowSection].map(
				(section) => ({
					className: section.className,
					copyMinBlockSize:
						section.querySelector<HTMLElement>(
							'.pns-split-section__copy-column'
						)?.style.minBlockSize ?? '',
				})
			);

			const read = (section: HTMLElement) => {
				const columns = section.querySelector<HTMLElement>(
					'.pns-split-section__columns'
				);
				const copy = section.querySelector<HTMLElement>(
					'.pns-split-section__copy-column'
				);
				const copyPanel = section.querySelector<HTMLElement>(
					'.pns-split-section__copy'
				);
				const media = section.querySelector<HTMLElement>(
					'.pns-split-section__media-column'
				);
				const image = section.querySelector<HTMLElement>(
					'.pns-split-section__media-column :is(img, .wp-block-jetpack-slideshow_image)'
				);

				if (!columns || !copy || !copyPanel || !media || !image) {
					return null;
				}

				const columnsRect = columns.getBoundingClientRect();
				const copyRect = copy.getBoundingClientRect();
				const mediaRect = media.getBoundingClientRect();
				const imageRect = image.getBoundingClientRect();

				return {
					columnGap: getComputedStyle(columns).columnGap,
					columnsBottom: columnsRect.bottom,
					columnsTop: columnsRect.top,
					copyBottom: copyRect.bottom,
					copyHeight: copyRect.height,
					copyPaddingLeft: parseFloat(
						getComputedStyle(copyPanel).paddingLeft
					),
					copyPaddingRight: parseFloat(
						getComputedStyle(copyPanel).paddingRight
					),
					imageBottom: imageRect.bottom,
					imageTop: imageRect.top,
					mediaBottom: mediaRect.bottom,
					mediaHeight: mediaRect.height,
					mediaLeft: mediaRect.left,
					mediaRight: mediaRect.right,
					mediaTop: mediaRect.top,
					mediaWidth: mediaRect.width,
					objectFit: getComputedStyle(image).objectFit,
				};
			};

			const measurePair = (left: string, right: string) => {
				imageSection.classList.remove(...variantClasses);
				slideshowSection.classList.remove(...variantClasses);
				imageSection.classList.add(`is-style-pns-${left}`);
				slideshowSection.classList.add(`is-style-pns-${right}`);

				return {
					left: read(imageSection),
					right: read(slideshowSection),
				};
			};

			try {
				for (const section of [imageSection, slideshowSection]) {
					const copy = section.querySelector<HTMLElement>(
						'.pns-split-section__copy-column'
					);

					if (copy) {
						copy.style.minBlockSize = '900px';
					}
				}

				return {
					edge: measurePair('edge-media-left', 'edge-media-right'),
					standard: measurePair('media-left', 'media-right'),
				};
			} finally {
				[imageSection, slideshowSection].forEach((section, index) => {
					section.className = originalState[index].className;
					const copy = section.querySelector<HTMLElement>(
						'.pns-split-section__copy-column'
					);

					if (copy) {
						copy.style.minBlockSize =
							originalState[index].copyMinBlockSize;
					}
				});
			}
		});

		expect(desktopMediaGeometry).not.toBeNull();

		if (desktopMediaGeometry) {
			for (const pair of [
				desktopMediaGeometry.edge,
				desktopMediaGeometry.standard,
			]) {
				expect(pair.left).not.toBeNull();
				expect(pair.right).not.toBeNull();

				if (!pair.left || !pair.right) {
					continue;
				}

				expect(pair.left.columnGap).toBe('0px');
				expect(pair.right.columnGap).toBe('0px');
				expect(pair.left.mediaRight).toBeCloseTo(
					pair.right.mediaLeft,
					0
				);
			}

			for (const media of [
				desktopMediaGeometry.edge.left,
				desktopMediaGeometry.edge.right,
			]) {
				if (!media) {
					continue;
				}

				expect(media.mediaHeight).toBeGreaterThanOrEqual(
					media.copyHeight - 1
				);
				expect(media.mediaTop).toBeCloseTo(media.columnsTop, 0);
				expect(media.mediaBottom).toBeCloseTo(media.columnsBottom, 0);
				expect(media.imageTop).toBeCloseTo(media.mediaTop, 0);
				expect(media.imageBottom).toBeCloseTo(media.mediaBottom, 0);
				expect(media.objectFit).toBe('cover');
				expect(media.copyPaddingLeft).toBeGreaterThan(0);
				expect(media.copyPaddingRight).toBeGreaterThan(0);
			}

			const standardSlideshow = desktopMediaGeometry.standard.right;
			expect(standardSlideshow).not.toBeNull();
			if (standardSlideshow) {
				expect(
					standardSlideshow.mediaHeight,
					'Regular slideshow media must remain square in the editor'
				).toBeCloseTo(standardSlideshow.mediaWidth, 0);
			}
		}

		const editorParity = await editor.evaluate(() => {
			const read = (element: Element | null) => {
				if (!(element instanceof HTMLElement)) {
					return null;
				}

				const computed = getComputedStyle(element);

				return {
					borderRadius: computed.borderRadius,
					marginBottom: computed.marginBottom,
					marginTop: computed.marginTop,
				};
			};

			const section =
				document.querySelector<HTMLElement>('.pns-split-section');

			return {
				button: read(
					section?.querySelector(
						'.pns-split-section__cta .wp-block-button__link'
					) ?? null
				),
				innerBlocks: Array.from(
					section?.querySelectorAll<HTMLElement>(
						'.block-editor-block-list__layout > .wp-block'
					) ?? []
				).map(read),
				section: read(section),
			};
		});

		expect(editorParity.section?.marginTop).toBe('0px');
		expect(editorParity.section?.marginBottom).toBe('0px');
		expect(editorParity.button?.borderRadius).toBe('0px');
		expect(editorParity.innerBlocks.length).toBeGreaterThan(0);

		for (const block of editorParity.innerBlocks) {
			expect(block?.marginTop).toBe('0px');
		}
	});
});
