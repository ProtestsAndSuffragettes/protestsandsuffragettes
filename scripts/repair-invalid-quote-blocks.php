<?php
/**
 * Repair malformed saved paragraph markup in PNS quote-cover pattern copies.
 *
 * Usage:
 *   wp eval-file scripts/repair-invalid-quote-blocks.php -- <post-id>
 *   wp eval-file scripts/repair-invalid-quote-blocks.php -- <post-id> apply
 *
 * The command is deliberately scoped to one current post at a time. It only
 * touches quote covers carrying a PNS quote-pattern identity class, writes a
 * rollback export before an apply, and leaves every other block untouched.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_quote_repair_args    = isset( $args ) && is_array( $args ) ? array_values( $args ) : array();
$pns_quote_repair_post_id = 0;
$pns_quote_repair_apply   = false;

foreach ( $pns_quote_repair_args as $pns_quote_repair_arg ) {
	if ( 'apply' === $pns_quote_repair_arg || '--apply' === $pns_quote_repair_arg ) {
		$pns_quote_repair_apply = true;
		continue;
	}

	if ( ctype_digit( (string) $pns_quote_repair_arg ) ) {
		$pns_quote_repair_post_id = absint( $pns_quote_repair_arg );
	}
}

if ( 0 === $pns_quote_repair_post_id ) {
	WP_CLI::error( 'Provide one post ID. Example: wp eval-file scripts/repair-invalid-quote-blocks.php -- 4501 apply' );
}

$pns_quote_repair_post = get_post( $pns_quote_repair_post_id );

if ( ! $pns_quote_repair_post || 'revision' === $pns_quote_repair_post->post_type ) {
	WP_CLI::error( 'The requested post must be a current, non-revision row.' );
}

/**
 * Repair only a PNS code-backed quote-cover block copy.
 *
 * Stored damage has two coupled forms: JSON-escaped hyphens lost their
 * backslashes in the paragraph block comment, and a later save placed a valid
 * paragraph inside that malformed outer paragraph. Restoring the escaping and
 * retaining the valid inner paragraph produces current Core block markup
 * without changing quote copy, citations, images, or cover settings.
 *
 * @param string $cover Serialized cover block.
 * @param array<string,int> $changes Change counter reference.
 * @return string
 */
function pns_repair_invalid_quote_cover( $cover, &$changes ) {
	$cover = preg_replace_callback(
		'/<!-- wp:paragraph (?P<attributes>\{.*?\}) -->/s',
		static function ( $match ) use ( &$changes ) {
			if ( false === strpos( $match['attributes'], 'var(u002d' ) ) {
				return $match[0];
			}

			$changes['comment_attributes']++;

			return '<!-- wp:paragraph ' . str_replace( 'u002d', '\\u002d', $match['attributes'] ) . ' -->';
		},
		$cover
	);

	$cover = preg_replace_callback(
		'#<p(?P<outer_attributes>[^>]*)>\s*(?P<inner><p(?P<inner_attributes>[^>]*)>.*?</p>)\s*</p>#s',
		static function ( $match ) use ( &$changes ) {
			if (
				false === strpos( $match['outer_attributes'], 'line-height:var(u002d' )
				|| false === strpos( $match['inner_attributes'], 'line-height:var(--wp--custom--typography--line-height--' )
			) {
				return $match[0];
			}

			$changes['nested_paragraphs']++;

			return $match['inner'];
		},
		$cover
	);

	return $cover;
}

$pns_quote_repair_changes = array(
	'covers'             => 0,
	'comment_attributes' => 0,
	'nested_paragraphs'  => 0,
);

$pns_quote_repair_content = preg_replace_callback(
	'#<!-- wp:cover (?=[^>]*"className":"[^"]*pns-(?:blockquote-with-red-line|blockquote-cover)[^"]*")[\s\S]*?<!-- /wp:cover -->#',
	static function ( $match ) use ( &$pns_quote_repair_changes ) {
		$repaired = pns_repair_invalid_quote_cover( $match[0], $pns_quote_repair_changes );

		if ( $repaired !== $match[0] ) {
			$pns_quote_repair_changes['covers']++;
		}

		return $repaired;
	},
	$pns_quote_repair_post->post_content
);

if ( null === $pns_quote_repair_content ) {
	WP_CLI::error( 'The stored content could not be scanned for PNS quote covers.' );
}

WP_CLI::log(
	sprintf(
		'Post #%d: %d changed quote cover(s), %d malformed block comment(s), %d nested paragraph wrapper(s).',
		$pns_quote_repair_post_id,
		$pns_quote_repair_changes['covers'],
		$pns_quote_repair_changes['comment_attributes'],
		$pns_quote_repair_changes['nested_paragraphs']
	)
);

if ( $pns_quote_repair_content === $pns_quote_repair_post->post_content ) {
	WP_CLI::success( 'No invalid PNS quote markup found in this post.' );
	return;
}

if ( ! $pns_quote_repair_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with apply to write a rollback export and update this post.' );
	return;
}

$pns_quote_repair_backup_dir = dirname( get_stylesheet_directory(), 5 ) . '/docs/jobs/invalid-quote-block-db-backups';

if ( ! is_dir( $pns_quote_repair_backup_dir ) && ! wp_mkdir_p( $pns_quote_repair_backup_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create rollback directory: %s', $pns_quote_repair_backup_dir ) );
}

$pns_quote_repair_backup_path = sprintf(
	'%s/%s-post-%d-before-invalid-quote-repair.json',
	$pns_quote_repair_backup_dir,
	gmdate( 'Ymd\\THis\\Z' ),
	$pns_quote_repair_post_id
);
$pns_quote_repair_backup      = wp_json_encode(
	array(
		'schema_version' => 1,
		'created_at_gmt' => gmdate( 'c' ),
		'post_id'        => $pns_quote_repair_post_id,
		'post_content'   => $pns_quote_repair_post->post_content,
	),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);

if ( false === file_put_contents( $pns_quote_repair_backup_path, $pns_quote_repair_backup . "\n" ) ) {
	WP_CLI::error( sprintf( 'Could not write rollback export: %s', $pns_quote_repair_backup_path ) );
}

$pns_quote_repair_update = wp_update_post(
	wp_slash(
		array(
			'ID'           => $pns_quote_repair_post_id,
			'post_content' => $pns_quote_repair_content,
		)
	),
	true
);

if ( is_wp_error( $pns_quote_repair_update ) ) {
	WP_CLI::error( $pns_quote_repair_update->get_error_message() );
}

clean_post_cache( $pns_quote_repair_post_id );

WP_CLI::success( sprintf( 'Repaired post #%d. Rollback export: %s', $pns_quote_repair_post_id, $pns_quote_repair_backup_path ) );
