import playwrightTest from '@playwright/test';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const { chromium } = playwrightTest;

const args = process.argv.slice(2);
const json = args.includes('--json');
const paths = args.filter((arg) => arg !== '--json' && arg !== '--');

if (paths.length === 0) {
	console.error(
		'Usage: node scripts/validate-editor-block-content.mjs [--json] <post-content.html>...'
	);
	process.exit(1);
}

const themeDir = path.dirname(fileURLToPath(import.meta.url));
const wpRoot = path.resolve(themeDir, '../../../../');
const localUrl = process.env.PNS_LOCAL_URL || 'http://localhost:10008/';

const coreScripts = [
	'wp-includes/js/dist/vendor/react.js',
	'wp-includes/js/dist/vendor/react-jsx-runtime.js',
	'wp-includes/js/dist/dom-ready.js',
	'wp-includes/js/dist/hooks.js',
	'wp-includes/js/dist/i18n.js',
	'wp-includes/js/dist/a11y.js',
	'wp-includes/js/dist/url.js',
	'wp-includes/js/dist/api-fetch.js',
	'wp-includes/js/dist/autop.js',
	'wp-includes/js/dist/blob.js',
	'wp-includes/js/dist/vendor/react-dom.js',
	'wp-includes/js/dist/block-serialization-spec-parser.js',
	'wp-includes/js/dist/block-serialization-default-parser.js',
	'wp-includes/js/dist/deprecated.js',
	'wp-includes/js/dist/dom.js',
	'wp-includes/js/dist/escape-html.js',
	'wp-includes/js/dist/element.js',
	'wp-includes/js/dist/is-shallow-equal.js',
	'wp-includes/js/dist/keycodes.js',
	'wp-includes/js/dist/priority-queue.js',
	'wp-includes/js/dist/undo-manager.js',
	'wp-includes/js/dist/compose.js',
	'wp-includes/js/dist/private-apis.js',
	'wp-includes/js/dist/redux-routine.js',
	'wp-includes/js/dist/data.js',
	'wp-includes/js/dist/html-entities.js',
	'wp-includes/js/dist/rich-text.js',
	'wp-includes/js/dist/shortcode.js',
	'wp-includes/js/dist/warning.js',
	'wp-includes/js/dist/blocks.js',
	'wp-includes/js/dist/vendor/moment.js',
	'wp-includes/js/dist/date.js',
	'wp-includes/js/dist/primitives.js',
	'wp-includes/js/dist/components.js',
	'wp-includes/js/dist/keyboard-shortcuts.js',
	'wp-includes/js/dist/commands.js',
	'wp-includes/js/dist/notices.js',
	'wp-includes/js/dist/preferences-persistence.js',
	'wp-includes/js/dist/preferences.js',
	'wp-includes/js/dist/style-engine.js',
	'wp-includes/js/dist/theme.js',
	'wp-includes/js/dist/token-list.js',
	'wp-includes/js/dist/upload-media.js',
	'wp-includes/js/dist/block-editor.js',
	'wp-includes/js/dist/core-data.js',
	'wp-includes/js/dist/patterns.js',
	'wp-includes/js/dist/server-side-render.js',
	'wp-includes/js/dist/wordcount.js',
	'wp-includes/js/utils.js',
	'wp-includes/js/jquery/jquery.js',
	'wp-includes/js/jquery/jquery-migrate.js',
	'wp-admin/js/editor.js',
	'wp-includes/js/dist/block-library.js',
];

const browser = await chromium.launch();
const page = await browser.newPage();

await page.goto(localUrl, { waitUntil: 'domcontentloaded' });
await page.evaluate((url) => {
	window.wp = window.wp || {};
	window.userSettings = { uid: '1' };
	window._wpLoadBlockEditor = Promise.resolve({});
	window.wpApiSettings = {
		root: new URL('/wp-json/', url).toString(),
		nonce: '',
	};
}, localUrl);

for (const relative of coreScripts) {
	const scriptPath = path.join(wpRoot, relative);
	if (!fs.existsSync(scriptPath)) {
		throw new Error(`Missing WordPress core script: ${relative}`);
	}

	await page.addScriptTag({ path: scriptPath });
}

await page.evaluate(() => {
	window.wp?.blockLibrary?.registerCoreBlocks?.();
});

const reports = [];

for (const inputPath of paths) {
	const postContent = fs.readFileSync(inputPath, 'utf8');
	const report = await page.evaluate((content) => {
		const specBlocks =
			window.wp.blockSerializationSpecParser.parse(content);
		const blocks = window.wp.blocks
			.parse(content)
			.filter((block) => block.name);
		const invalid = [];
		let checked = 0;
		const issueMessage = (issue) => {
			if (typeof issue === 'string') {
				return issue;
			}

			if (issue?.message) {
				return typeof issue.message === 'string'
					? issue.message
					: JSON.stringify(issue.message);
			}

			return JSON.stringify(issue);
		};

		const walk = (block, blockPath = []) => {
			const blockType = window.wp.blocks.getBlockType(block.name);
			const validation = window.wp.blocks.validateBlock(
				block,
				blockType || block.name
			);
			checked += 1;

			if (!validation[0]) {
				invalid.push({
					path: blockPath.join('/'),
					name: block.name,
					classes: block.attributes?.className || '',
					issues: validation[1].map(issueMessage),
					html:
						block.originalContent
							?.replace(/\s+/g, ' ')
							.slice(0, 160) || '',
				});
			}

			block.innerBlocks.forEach((inner, index) =>
				walk(inner, [...blockPath, index])
			);
		};

		blocks.forEach((block, index) => walk(block, [index]));

		return {
			bytes: content.length,
			topLevelBlocks: blocks.length,
			specNodes: specBlocks.length,
			checked,
			invalid,
		};
	}, postContent);

	reports.push({
		file: inputPath,
		...report,
	});
}

await browser.close();

if (json) {
	console.log(JSON.stringify(reports, null, 2));
} else {
	for (const report of reports) {
		console.log(
			`${report.file}: ${report.invalid.length} invalid / ${report.checked} checked`
		);
		for (const row of report.invalid.slice(0, 12)) {
			console.log(
				`  ${row.path} ${row.name} classes="${row.classes}" issues="${row.issues.join('; ')}" html="${row.html}"`
			);
		}
		if (report.invalid.length > 12) {
			console.log(`  ... ${report.invalid.length - 12} more`);
		}
	}
}

if (reports.some((report) => report.invalid.length > 0)) {
	process.exitCode = 1;
}
