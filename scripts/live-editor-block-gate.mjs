import playwrightTest from '@playwright/test';
import fs from 'node:fs';

const { chromium } = playwrightTest;

const args = process.argv.slice(2);
const cookiesIndex = args.indexOf('--cookies');
const baseUrlIndex = args.indexOf('--base-url');
const outputIndex = args.indexOf('--output');

if (cookiesIndex === -1 || !args[cookiesIndex + 1]) {
	console.error(
		'Usage: node scripts/live-editor-block-gate.mjs --cookies <cookies.json> [--base-url <url>] <post-id>...'
	);
	process.exit(1);
}

const cookiesPath = args[cookiesIndex + 1];
const baseUrl =
	baseUrlIndex === -1
		? process.env.PNS_LOCAL_URL || 'http://localhost:10008'
		: args[baseUrlIndex + 1];
const outputPath = outputIndex === -1 ? '' : args[outputIndex + 1];
const postIds = args.filter((arg, index) => {
	if (
		arg === '--cookies' ||
		arg === '--base-url' ||
		arg === '--output' ||
		index === cookiesIndex + 1 ||
		index === baseUrlIndex + 1 ||
		index === outputIndex + 1
	) {
		return false;
	}

	return !arg.startsWith('--');
});

if (postIds.length === 0) {
	console.error('Provide at least one post ID.');
	process.exit(1);
}

const cookies = JSON.parse(fs.readFileSync(cookiesPath, 'utf8')).map(
	(cookie) => ({
		...cookie,
		sameSite: cookie.sameSite || 'Lax',
	})
);

const browser = await chromium.launch();
const context = await browser.newContext();
await context.addCookies(cookies);

const collectEditorReport = async (postId) => {
	const page = await context.newPage();
	const editorUrl = `${baseUrl}/wp-admin/post.php?post=${postId}&action=edit`;
	const consoleMessages = [];

	page.on('console', (message) => {
		const text = message.text();
		if (/invalid|warning|block validation|error/i.test(text)) {
			consoleMessages.push({
				type: message.type(),
				text: text.slice(0, 500),
			});
		}
	});

	try {
		await page.goto(editorUrl, {
			waitUntil: 'domcontentloaded',
			timeout: 60000,
		});

		const finalUrl = page.url();
		const loggedIn = !finalUrl.includes('wp-login.php');

		if (loggedIn) {
			await page.waitForFunction(
				() =>
					Boolean(
						window.wp?.data?.select?.('core/block-editor')
							?.getBlocks && window.wp?.blocks?.validateBlock
					),
				{ timeout: 60000 }
			);

			await page.waitForTimeout(1000);
		}

		const report = await page.evaluate(() => {
			const warningSelectors = [
				'.block-editor-warning',
				'[class*="block-editor-warning"]',
				'[class*="block-editor-block-list__block-warning"]',
			];
			const warningTextPattern =
				/unexpected or invalid content|attempt block recovery|block contains/i;
			const warningNodes = Array.from(
				document.querySelectorAll(warningSelectors.join(','))
			)
				.map((node) => ({
					selector: node.className?.toString?.() || node.tagName,
					text:
						node.textContent
							?.replace(/\s+/g, ' ')
							.trim()
							.slice(0, 500) || '',
				}))
				.filter((entry) => warningTextPattern.test(entry.text));

			const editor = window.wp?.data?.select?.('core/block-editor');
			const blocksApi = window.wp?.blocks;
			const blocks = editor?.getBlocks?.() || [];
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

			const walk = (block, path = []) => {
				if (!block?.name) {
					return;
				}

				checked += 1;
				const blockType = blocksApi?.getBlockType?.(block.name);
				const validation = blocksApi?.validateBlock?.(
					block,
					blockType || block.name
				);

				if (validation && !validation[0]) {
					invalid.push({
						clientId: block.clientId,
						path: path.join('/'),
						name: block.name,
						className: block.attributes?.className || '',
						issues: validation[1]
							.map((issue) => issueMessage(issue).slice(0, 500))
							.slice(0, 6),
						originalContent:
							block.originalContent
								?.replace(/\s+/g, ' ')
								.slice(0, 300) || '',
					});
				}

				block.innerBlocks?.forEach((inner, index) =>
					walk(inner, [...path, index])
				);
			};

			blocks.forEach((block, index) => walk(block, [index]));

			return {
				title: document.title,
				currentPostId: window.wp?.data
					?.select?.('core/editor')
					?.getCurrentPostId?.(),
				isEditedPostDirty: window.wp?.data
					?.select?.('core/editor')
					?.isEditedPostDirty?.(),
				topLevelBlocks: blocks.length,
				checked,
				invalid,
				warningNodes,
			};
		});

		await page.close();

		return {
			postId,
			editorUrl,
			finalUrl,
			loggedIn,
			loadError: '',
			consoleMessages: consoleMessages.filter((message) =>
				/block validation|unexpected|invalid/i.test(message.text)
			),
			...report,
		};
	} catch (error) {
		const finalUrl = page.url();
		await page.close();

		return {
			postId,
			editorUrl,
			finalUrl,
			loggedIn: !finalUrl.includes('wp-login.php'),
			loadError: `${error.name || 'Error'}: ${error.message || error}`,
			consoleMessages: consoleMessages.filter((message) =>
				/block validation|unexpected|invalid/i.test(message.text)
			),
			title: '',
			currentPostId: null,
			isEditedPostDirty: null,
			topLevelBlocks: 0,
			checked: 0,
			invalid: [],
			warningNodes: [],
		};
	}
};

const reports = [];

for (const postId of postIds) {
	reports.push(await collectEditorReport(postId));
}

await browser.close();

const summary = {
	checkedPosts: reports.length,
	invalidPosts: reports.filter(
		(report) => report.invalid.length || report.warningNodes.length
	).length,
	invalidBlocks: reports.reduce(
		(count, report) => count + report.invalid.length,
		0
	),
	warningNodes: reports.reduce(
		(count, report) => count + report.warningNodes.length,
		0
	),
};

const result = { summary, reports };

if (outputPath) {
	fs.writeFileSync(outputPath, `${JSON.stringify(result, null, 2)}\n`);
	console.log(JSON.stringify({ ...summary, output: outputPath }, null, 2));
} else {
	console.log(JSON.stringify(result, null, 2));
}

if (
	reports.some(
		(report) =>
			!report.loggedIn ||
			report.loadError ||
			report.invalid.length > 0 ||
			report.warningNodes.length > 0
	)
) {
	process.exitCode = 1;
}
