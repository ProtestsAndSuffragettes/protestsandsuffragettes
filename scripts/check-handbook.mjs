import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';
import { fileURLToPath } from 'node:url';

const themeRoot = path.resolve(
	path.dirname(fileURLToPath(import.meta.url)),
	'..'
);
const ignoredDirectories = new Set(['.git', 'node_modules', 'vendor']);

function usage() {
	console.log(`Usage: node scripts/check-handbook.mjs

Checks local Markdown links in the theme handbook and reports the current
filesystem inventory. Broken local links cause a non-zero exit code; inventory
counts are informational and do not set a baseline or fail the command.`);
}

function toThemePath(filePath) {
	return path.relative(themeRoot, filePath).split(path.sep).join('/');
}

function walkFiles(directoryPath, matcher, files = []) {
	if (!fs.existsSync(directoryPath)) {
		return files;
	}

	for (const entry of fs.readdirSync(directoryPath, {
		withFileTypes: true,
	})) {
		if (ignoredDirectories.has(entry.name)) {
			continue;
		}

		const entryPath = path.join(directoryPath, entry.name);

		if (entry.isDirectory()) {
			walkFiles(entryPath, matcher, files);
			continue;
		}

		if (matcher(entry, entryPath)) {
			files.push(entryPath);
		}
	}

	return files;
}

function countFiles(directory, extension) {
	return walkFiles(path.join(themeRoot, directory), (entry) =>
		entry.name.endsWith(extension)
	).length;
}

function countDirectoriesWithFile(directory, fileName) {
	const directoryPath = path.join(themeRoot, directory);

	if (!fs.existsSync(directoryPath)) {
		return 0;
	}

	return fs
		.readdirSync(directoryPath, { withFileTypes: true })
		.filter(
			(entry) =>
				entry.isDirectory() &&
				fs.existsSync(path.join(directoryPath, entry.name, fileName))
		).length;
}

function handbookFiles() {
	const files = walkFiles(themeRoot, (entry, entryPath) => {
		if (entry.name !== 'README.md' && !entryPath.endsWith('.md')) {
			return false;
		}

		return (
			entry.name === 'README.md' ||
			toThemePath(entryPath).startsWith('docs/')
		);
	});

	return files.sort((left, right) => left.localeCompare(right));
}

function linkDestinations(source) {
	const links = [];
	const inlineLink =
		/(?<!!)(?:^|[^\\])\[[^\]]*\]\(\s*(?:<([^>]+)>|([^\s)]+))(?:\s+["'][^)]*["'])?\s*\)/gm;
	let match;

	while ((match = inlineLink.exec(source))) {
		links.push(match[1] ?? match[2]);
	}

	return links;
}

function isExternalDestination(destination) {
	return (
		destination.startsWith('#') ||
		destination.startsWith('//') ||
		/^[a-z][a-z\d+.-]*:/i.test(destination)
	);
}

function localDestination(destination, sourcePath) {
	const withoutFragment = destination.split('#', 1)[0].split('?', 1)[0];

	if (!withoutFragment || isExternalDestination(destination)) {
		return null;
	}

	if (path.isAbsolute(withoutFragment)) {
		const resolved = path.resolve(withoutFragment);
		return resolved.startsWith(`${themeRoot}${path.sep}`) ||
			resolved === themeRoot
			? resolved
			: null;
	}

	return path.resolve(path.dirname(sourcePath), withoutFragment);
}

function checkLinks(files) {
	const broken = [];
	let checked = 0;

	for (const filePath of files) {
		const source = fs.readFileSync(filePath, 'utf8');

		for (const destination of linkDestinations(source)) {
			const target = localDestination(destination, filePath);

			if (!target) {
				continue;
			}

			checked += 1;

			if (!fs.existsSync(target)) {
				broken.push({
					destination,
					source: toThemePath(filePath),
					target: toThemePath(target),
				});
			}
		}
	}

	return { broken, checked };
}

function reportInventory() {
	const navigationDirectory = path.join(themeRoot, 'navigation');
	const syncedPatternsDirectory = path.join(themeRoot, 'synced-patterns');
	const inventory = [
		['Templates (.html)', countFiles('templates', '.html')],
		['Template parts (.html)', countFiles('parts', '.html')],
		['Patterns (.php)', countFiles('patterns', '.php')],
		['Navigation fixtures (.html)', countFiles('navigation', '.html')],
		[
			'Navigation manifest',
			fs.existsSync(path.join(navigationDirectory, 'manifest.json'))
				? 1
				: 0,
		],
		[
			'Synced-pattern fixtures (.html)',
			countFiles('synced-patterns', '.html'),
		],
		[
			'Synced-pattern manifest',
			fs.existsSync(path.join(syncedPatternsDirectory, 'manifest.json'))
				? 1
				: 0,
		],
		['Theme modules (inc/*.php)', countFiles('inc', '.php')],
		[
			'Theme blocks (block.json)',
			countDirectoriesWithFile('blocks', 'block.json'),
		],
	];

	console.log('\nHandbook inventory (informational)');

	for (const [label, count] of inventory) {
		console.log(`- ${label}: ${count}`);
	}
}

if (process.argv.includes('--help') || process.argv.includes('-h')) {
	usage();
	process.exit(0);
}

const files = handbookFiles();
const { broken, checked } = checkLinks(files);

console.log(
	`Checked ${checked} local Markdown link(s) in ${files.length} handbook file(s).`
);

if (broken.length > 0) {
	console.error(`\nBroken local Markdown link(s): ${broken.length}`);

	for (const link of broken) {
		console.error(
			`- ${link.source}: ${link.destination} (missing ${link.target})`
		);
	}

	process.exitCode = 1;
} else {
	console.log('All local Markdown links resolve.');
}

reportInventory();
