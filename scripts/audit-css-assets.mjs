import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const themeRoot = process.cwd();
const manifestPath = path.join(themeRoot, 'styles/css-assets.json');
const blockStylesPhpPath = path.join(themeRoot, 'inc/block-styles.php');

const toPosix = (value) => value.split(path.sep).join('/');
const fromThemeRoot = (value) => toPosix(path.relative(themeRoot, value));
const unique = (values) => [...new Set(values)].sort();

function fail(message, details = []) {
	console.error(message);

	for (const detail of details) {
		console.error(`- ${detail}`);
	}

	process.exitCode = 1;
}

function readJson(filePath) {
	return JSON.parse(fs.readFileSync(filePath, 'utf8'));
}

function walkFiles(dirPath, matcher, files = []) {
	for (const entry of fs.readdirSync(dirPath, { withFileTypes: true })) {
		const entryPath = path.join(dirPath, entry.name);

		if (entry.isDirectory()) {
			walkFiles(entryPath, matcher, files);
			continue;
		}

		if (matcher(entryPath)) {
			files.push(fromThemeRoot(entryPath));
		}
	}

	return files;
}

function resolveCssImport(importPath, importer) {
	if (
		importPath.startsWith('http://') ||
		importPath.startsWith('https://') ||
		importPath.startsWith('url(')
	) {
		return null;
	}

	const importerDir = path.dirname(importer);
	const withExtension = importPath.endsWith('.css')
		? importPath
		: `${importPath}.css`;

	return toPosix(path.normalize(path.join(importerDir, withExtension)));
}

function readImports(relativePath) {
	const absolutePath = path.join(themeRoot, relativePath);
	const source = fs.readFileSync(absolutePath, 'utf8');
	const imports = [];
	const importPattern = /@import\s+(?:url\()?["']([^"')]+)["']\)?/g;
	let match;

	while ((match = importPattern.exec(source))) {
		const resolved = resolveCssImport(match[1], relativePath);

		if (resolved) {
			imports.push(resolved);
		}
	}

	return imports;
}

function collectReachable(entrypoint) {
	const reachable = new Set();
	const importers = new Map();
	const missing = [];

	function visit(relativePath, importer = null) {
		const absolutePath = path.join(themeRoot, relativePath);

		if (!fs.existsSync(absolutePath)) {
			missing.push(
				`${relativePath} imported by ${importer ?? '<entrypoint>'}`
			);
			return;
		}

		if (importer) {
			const current = importers.get(relativePath) ?? [];
			current.push(importer);
			importers.set(relativePath, current);
		}

		if (reachable.has(relativePath)) {
			return;
		}

		reachable.add(relativePath);

		for (const importedPath of readImports(relativePath)) {
			visit(importedPath, relativePath);
		}
	}

	visit(entrypoint);

	return { importers, missing, reachable };
}

function mergeImporters(graphs) {
	const merged = new Map();

	for (const graph of graphs) {
		for (const [file, importers] of graph.importers.entries()) {
			merged.set(
				file,
				unique([...(merged.get(file) ?? []), ...importers])
			);
		}
	}

	return merged;
}

function parsePhpBlockStyles() {
	const source = fs.readFileSync(blockStylesPhpPath, 'utf8');
	const blockStyles = {};
	const blockStylePattern =
		/'([^']+\/[^']+)'\s*=>\s*(?:array\(\s*'path'\s*=>\s*)?'(styles\/blocks\/[^']+\.css)'/g;
	let match;

	while ((match = blockStylePattern.exec(source))) {
		blockStyles[match[1]] = match[2];
	}

	return blockStyles;
}

function mapEntries(objectValue = {}) {
	return Object.entries(objectValue).sort(([a], [b]) => a.localeCompare(b));
}

function diffObject(left, right) {
	const messages = [];
	const keys = unique([...Object.keys(left), ...Object.keys(right)]);

	for (const key of keys) {
		if (!(key in left)) {
			messages.push(
				`${key} missing from manifest; PHP registers ${right[key]}`
			);
			continue;
		}

		if (!(key in right)) {
			messages.push(
				`${key} missing from PHP registration; manifest declares ${left[key]}`
			);
			continue;
		}

		if (left[key] !== right[key]) {
			messages.push(`${key}: manifest=${left[key]} php=${right[key]}`);
		}
	}

	return messages;
}

const manifest = readJson(manifestPath);
const entrypoints = manifest.entrypoints ?? [];
const graphs = entrypoints.map(collectReachable);
const allReachable = new Set(graphs.flatMap((graph) => [...graph.reachable]));
const frontendReachable = graphs[0]?.reachable ?? new Set();
const editorReachable = graphs[1]?.reachable ?? new Set();
const importers = mergeImporters(graphs);
const missingImports = graphs.flatMap((graph) => graph.missing);
const stylesRoot = path.join(themeRoot, 'styles');
const allCssFiles = walkFiles(
	stylesRoot,
	(filePath) =>
		filePath.endsWith('.css') &&
		!filePath.includes(`${path.sep}dist${path.sep}`)
);
const blockRegistered = manifest.blockRegistered ?? {};
const blockRegisteredFiles = new Set(Object.values(blockRegistered));
const frontendOnly = manifest.frontendOnly ?? {};
const allowedDuplicateLoadPaths = manifest.allowedDuplicateLoadPaths ?? {};
const dormant = manifest.dormant ?? {};
const phpBlockStyles = parsePhpBlockStyles();

const failures = [];
const warnings = [];

for (const missingImport of missingImports) {
	failures.push(`Missing CSS import target: ${missingImport}`);
}

for (const file of allCssFiles) {
	const isLoaded = allReachable.has(file);
	const isBlockRegistered = blockRegisteredFiles.has(file);
	const isDormant = file in dormant;

	if (!isLoaded && !isBlockRegistered && !isDormant) {
		failures.push(`Unclassified CSS file: ${file}`);
	}

	if (isDormant && (isLoaded || isBlockRegistered)) {
		failures.push(`Dormant CSS file is still loaded: ${file}`);
	}
}

for (const [file, reason] of mapEntries(frontendOnly)) {
	if (!fs.existsSync(path.join(themeRoot, file))) {
		failures.push(`Frontend-only file does not exist: ${file}`);
		continue;
	}

	if (!frontendReachable.has(file)) {
		failures.push(
			`Frontend-only file is not imported by frontend.css: ${file}`
		);
	}

	if (editorReachable.has(file)) {
		failures.push(`Frontend-only file is imported by editor.css: ${file}`);
	}

	if (!reason || typeof reason !== 'string') {
		failures.push(`Frontend-only file needs a reason: ${file}`);
	}
}

for (const [file, reason] of mapEntries(dormant)) {
	if (!fs.existsSync(path.join(themeRoot, file))) {
		failures.push(`Dormant file does not exist: ${file}`);
	}

	if (!reason || typeof reason !== 'string') {
		failures.push(`Dormant file needs a reason: ${file}`);
	}
}

for (const [blockName, file] of mapEntries(blockRegistered)) {
	if (!fs.existsSync(path.join(themeRoot, file))) {
		failures.push(
			`Block-registered file for ${blockName} does not exist: ${file}`
		);
	}
}

for (const file of [...blockRegisteredFiles]
	.filter((file) => allReachable.has(file))
	.sort()) {
	if (!(file in allowedDuplicateLoadPaths)) {
		failures.push(
			`CSS file is both bundled and block-registered without allowlist: ${file}`
		);
	}
}

for (const [file, reason] of mapEntries(allowedDuplicateLoadPaths)) {
	const isDuplicate =
		blockRegisteredFiles.has(file) && allReachable.has(file);

	if (!isDuplicate) {
		failures.push(`Duplicate-load allowlist entry is stale: ${file}`);
	}

	if (!reason || typeof reason !== 'string') {
		failures.push(`Duplicate-load allowlist entry needs a reason: ${file}`);
	}
}

for (const message of diffObject(blockRegistered, phpBlockStyles)) {
	failures.push(`Block style registration drift: ${message}`);
}

for (const file of [
	'styles/dist/frontend.min.css',
	'styles/dist/editor.min.css',
]) {
	const filePath = path.join(themeRoot, file);

	if (!fs.existsSync(filePath)) {
		continue;
	}

	if (fs.readFileSync(filePath, 'utf8').includes(':where(){')) {
		failures.push(`Compiled CSS contains empty :where() selector: ${file}`);
	}
}

const importedOnly = allCssFiles.filter(
	(file) => allReachable.has(file) && !blockRegisteredFiles.has(file)
);
const blockOnly = allCssFiles.filter(
	(file) => !allReachable.has(file) && blockRegisteredFiles.has(file)
);
const duplicates = allCssFiles.filter(
	(file) => allReachable.has(file) && blockRegisteredFiles.has(file)
);
const dormantFiles = allCssFiles.filter((file) => file in dormant);

for (const file of duplicates.sort()) {
	warnings.push(`Duplicate load path allowed: ${file}`);
}

if (failures.length) {
	fail('CSS asset audit failed:', failures);
} else {
	console.log('CSS asset audit passed.');
}

console.log(`Entrypoints: ${entrypoints.join(', ')}`);
console.log(`Imported-only CSS files: ${importedOnly.length}`);
console.log(`Block-registered-only CSS files: ${blockOnly.length}`);
console.log(`Allowed duplicate load paths: ${duplicates.length}`);
console.log(`Dormant CSS files: ${dormantFiles.length}`);

if (warnings.length) {
	console.warn('CSS asset audit warnings:');

	for (const warning of warnings) {
		console.warn(`- ${warning}`);
	}
}

if (process.env.PNS_CSS_ASSET_AUDIT_VERBOSE === '1') {
	console.log('Importers:');

	for (const [file, importerList] of [...importers.entries()].sort()) {
		console.log(`- ${file}: ${importerList.join(', ')}`);
	}
}
