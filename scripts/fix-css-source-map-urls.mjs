import fs from 'node:fs';
import path from 'node:path';

const distDir = path.resolve('styles/dist');
const files = ['frontend.min.css', 'editor.min.css'];

for (const file of files) {
	const filePath = path.join(distDir, file);
	const mapName = `${file}.map`;
	const expectedComment = `/*# sourceMappingURL=${mapName} */`;

	if (!fs.existsSync(filePath)) {
		continue;
	}

	const content = fs.readFileSync(filePath, 'utf8');
	const updated = content.replace(
		/\/\*# sourceMappingURL=.*? \*\//,
		expectedComment
	);

	if (updated !== content) {
		fs.writeFileSync(filePath, updated);
	}
}
