#!/usr/bin/env php
<?php
/**
 * Validate WordPress block template serialization.
 *
 * This intentionally validates the Gutenberg block comment grammar instead of
 * formatting the HTML. Generic HTML formatters do not understand serialized
 * WordPress block delimiters.
 */

declare(strict_types=1);

$theme_dir        = dirname(__DIR__);
$wp_parser_class  = dirname($theme_dir, 3) . '/wp-includes/class-wp-block-parser.php';
$valid_directories = array(
	$theme_dir . '/templates',
	$theme_dir . '/parts',
	$theme_dir . '/patterns',
	$theme_dir . '/navigation',
	$theme_dir . '/synced-patterns',
);

if (! file_exists($wp_parser_class)) {
	fwrite(STDERR, "WordPress block parser was not found at {$wp_parser_class}\n");
	exit(1);
}

require_once $wp_parser_class;

$files = pns_collect_block_template_files(array_slice($argv, 1), $valid_directories, $theme_dir);

if (empty($files)) {
	echo "No block template files found.\n";
	exit(0);
}

$failures = 0;

foreach ($files as $file) {
	$errors = pns_validate_block_template_file($file);

	if (! empty($errors)) {
		++$failures;
		fwrite(STDERR, "{$file}\n");

		foreach ($errors as $error) {
			fwrite(STDERR, "  - {$error}\n");
		}
	}
}

if (0 < $failures) {
	fwrite(STDERR, "\nBlock template validation failed for {$failures} file(s).\n");
	exit(1);
}

echo 'Block template validation passed for ' . count($files) . " file(s).\n";

/**
 * @param string[] $args
 * @param string[] $valid_directories
 * @return string[]
 */
function pns_collect_block_template_files(array $args, array $valid_directories, string $theme_dir): array {
	$files = array();

	if (empty($args)) {
		foreach ($valid_directories as $directory) {
			if (! is_dir($directory)) {
				continue;
			}

			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
			);

			foreach ($iterator as $file_info) {
				if ($file_info->isFile() && 'html' === strtolower($file_info->getExtension())) {
					$files[] = $file_info->getPathname();
				}
			}
		}

		return pns_unique_files($files);
	}

	for ($index = 0; $index < count($args); ++$index) {
		if ('--file-list' === $args[$index]) {
			$file_list = $args[$index + 1] ?? '';

			if (! is_readable($file_list)) {
				fwrite(STDERR, "File list is not readable: {$file_list}\n");
				exit(1);
			}

			$listed_files = file($file_list, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

			foreach (false === $listed_files ? array() : $listed_files as $listed_file) {
				$files[] = pns_resolve_file($listed_file, $theme_dir);
			}

			++$index;
			continue;
		}

		$files[] = pns_resolve_file($args[$index], $theme_dir);
	}

	return pns_unique_files(
		array_filter(
			$files,
			static fn (string $file): bool => is_file($file) && 'html' === strtolower(pathinfo($file, PATHINFO_EXTENSION))
		)
	);
}

function pns_resolve_file(string $file, string $theme_dir): string {
	if (file_exists($file)) {
		return $file;
	}

	$theme_relative = $theme_dir . '/' . ltrim($file, '/');

	if (file_exists($theme_relative)) {
		return $theme_relative;
	}

	return $file;
}

/**
 * @param string[] $files
 * @return string[]
 */
function pns_unique_files(array $files): array {
	$unique = array();

	foreach ($files as $file) {
		$key            = realpath($file) ?: $file;
		$unique[$key] = $file;
	}

	sort($unique);

	return array_values($unique);
}

/**
 * @return string[]
 */
function pns_validate_block_template_file(string $file): array {
	$content = file_get_contents($file);

	if (false === $content) {
		return array('File could not be read.');
	}

	$errors = pns_validate_block_comment_tokens($content);

	if (empty($errors)) {
		$parser = new WP_Block_Parser();
		$parser->parse($content);
	}

	return $errors;
}

/**
 * @return string[]
 */
function pns_validate_block_comment_tokens(string $content): array {
	$errors      = array();
	$stack       = array();
	$valid_starts = array();
	$token_regex = '/<!--\s+(?P<closer>\/)?wp:(?P<namespace>[a-z][a-z0-9_-]*\/)?(?P<name>[a-z][a-z0-9_-]*)\s+(?P<attrs>{(?:(?:[^}]+|}+(?=})|(?!}\s+\/?-->).)*+)?}\s+)?(?P<void>\/)?-->/s';

	preg_match_all($token_regex, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

	foreach ($matches as $match) {
		$offset                 = $match[0][1];
		$valid_starts[$offset] = true;
		$line                   = pns_line_number_for_offset($content, $offset);
		$is_closer              = isset($match['closer']) && -1 !== $match['closer'][1];
		$is_void                = isset($match['void']) && -1 !== $match['void'][1];
		$has_attrs              = isset($match['attrs']) && -1 !== $match['attrs'][1];
		$namespace              = isset($match['namespace']) && -1 !== $match['namespace'][1] ? $match['namespace'][0] : 'core/';
		$block_name             = $namespace . $match['name'][0];

		if ($has_attrs) {
			json_decode($match['attrs'][0], true);

			if (JSON_ERROR_NONE !== json_last_error()) {
				$errors[] = 'Line ' . $line . ': invalid JSON attributes for ' . $block_name . ' (' . json_last_error_msg() . ').';
			}
		}

		if ($is_closer && ($is_void || $has_attrs)) {
			$errors[] = 'Line ' . $line . ': closing block delimiter for ' . $block_name . ' must not include attributes or a self-closing slash.';
			continue;
		}

		if ($is_void) {
			continue;
		}

		if (! $is_closer) {
			$stack[] = array(
				'name' => $block_name,
				'line' => $line,
			);
			continue;
		}

		if (empty($stack)) {
			$errors[] = 'Line ' . $line . ': unexpected closing delimiter for ' . $block_name . '.';
			continue;
		}

		$opener = array_pop($stack);

		if ($opener['name'] !== $block_name) {
			$errors[] = 'Line ' . $line . ': closing delimiter for ' . $block_name . ' does not match opener for ' . $opener['name'] . ' on line ' . $opener['line'] . '.';
		}
	}

	preg_match_all('/<!--\s*\/?\s*wp:/i', $content, $wp_comment_starts, PREG_OFFSET_CAPTURE);

	foreach ($wp_comment_starts[0] as $wp_comment_start) {
		$offset = $wp_comment_start[1];

		if (! isset($valid_starts[$offset])) {
			$errors[] = 'Line ' . pns_line_number_for_offset($content, $offset) . ': malformed WordPress block delimiter.';
		}
	}

	foreach (array_reverse($stack) as $opener) {
		$errors[] = 'Line ' . $opener['line'] . ': missing closing delimiter for ' . $opener['name'] . '.';
	}

	return $errors;
}

function pns_line_number_for_offset(string $content, int $offset): int {
	return substr_count(substr($content, 0, $offset), "\n") + 1;
}
