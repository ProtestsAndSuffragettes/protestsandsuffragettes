<?php
/**
 * Add deterministic Query Pagination specimens to the private PNS Style Guide.
 *
 * Dry run:
 *   wp eval-file scripts/add-style-guide-pagination-examples.php
 *
 * Apply:
 *   wp eval-file scripts/add-style-guide-pagination-examples.php -- apply
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( 1 );
}

$pns_style_guide_id       = 6340;
$pns_style_guide_marker   = 'pns-style-guide-pagination-examples';
$pns_style_guide_template = 'page-light-surface-wide-content';
$pns_style_guide_apply    = in_array( 'apply', $args, true );
$pns_style_guide_page     = get_post( $pns_style_guide_id );

if ( ! $pns_style_guide_page instanceof WP_Post || 'page' !== $pns_style_guide_page->post_type ) {
	WP_CLI::error( 'Page 6340 is no longer the expected Style Guide page.' );
}

if ( 'pns-style-guide' !== $pns_style_guide_page->post_name ) {
	WP_CLI::error( 'Page 6340 no longer uses the pns-style-guide slug.' );
}

if ( $pns_style_guide_template !== get_post_meta( $pns_style_guide_id, '_wp_page_template', true ) ) {
	WP_CLI::error( 'The Style Guide page no longer uses the expected wide-content template.' );
}

$pns_style_guide_content = $pns_style_guide_page->post_content;

if ( str_contains( $pns_style_guide_content, $pns_style_guide_marker ) ) {
	WP_CLI::success( 'The Style Guide already contains the Query Pagination specimens.' );
	return;
}

$pns_style_guide_needle = <<<'HTML'
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Form Controls</h3>
<!-- /wp:heading -->
HTML;

if ( 1 !== substr_count( $pns_style_guide_content, $pns_style_guide_needle ) ) {
	WP_CLI::error( 'Expected exactly one Form Controls heading insertion point.' );
}

if ( ! str_contains( $pns_style_guide_content, '<h3 class="wp-block-heading">Entry Navigation Actions</h3>' ) ) {
	WP_CLI::error( 'The existing Entry Navigation Actions specimen could not be verified.' );
}

$pns_style_guide_examples = <<<'HTML'
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Query Pagination</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Paged archives use Previous, numbered pages, and Next. On small displays the controls stack in source order, while longer page lists wrap within the numbered row.</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
<div class="pns-style-guide-pagination-examples">
	<section class="pns-style-guide-sample pns-style-guide-pagination-example pns-style-guide-pagination-example--few" aria-labelledby="pns-pagination-few-title">
		<h4 id="pns-pagination-few-title">Few pages</h4>
		<nav aria-label="Few-page pagination example" class="pns-section pns-query-pagination wp-block-query-pagination is-layout-flex wp-block-query-pagination-is-layout-flex" style="padding-top:var(--wp--preset--spacing--generous);padding-bottom:var(--wp--preset--spacing--generous)">
			<span class="wp-block-query-pagination-previous pns-query-pagination__boundary is-disabled" aria-disabled="true">Previous</span>
			<div class="wp-block-query-pagination-numbers">
				<span aria-current="page" class="page-numbers current">1</span>
				<a class="page-numbers" href="#pns-pagination-few-page-2">2</a>
				<a class="page-numbers" href="#pns-pagination-few-page-3">3</a>
			</div>
			<a class="wp-block-query-pagination-next" href="#pns-pagination-few-page-2">Next <span class="wp-block-query-pagination-next-arrow is-arrow-arrow" aria-hidden="true">→</span></a>
		</nav>
	</section>

	<section class="pns-style-guide-sample pns-style-guide-pagination-example pns-style-guide-pagination-example--many" aria-labelledby="pns-pagination-many-title">
		<h4 id="pns-pagination-many-title">Many pages</h4>
		<nav aria-label="Many-page pagination example" class="pns-section pns-query-pagination wp-block-query-pagination is-layout-flex wp-block-query-pagination-is-layout-flex" style="padding-top:var(--wp--preset--spacing--generous);padding-bottom:var(--wp--preset--spacing--generous)">
			<a class="wp-block-query-pagination-previous" href="#pns-pagination-many-page-3"><span class="wp-block-query-pagination-previous-arrow is-arrow-arrow" aria-hidden="true">←</span> Previous</a>
			<div class="wp-block-query-pagination-numbers">
				<a class="page-numbers" href="#pns-pagination-many-page-1">1</a>
				<a class="page-numbers" href="#pns-pagination-many-page-2">2</a>
				<a class="page-numbers" href="#pns-pagination-many-page-3">3</a>
				<span aria-current="page" class="page-numbers current">4</span>
				<a class="page-numbers" href="#pns-pagination-many-page-5">5</a>
				<span class="page-numbers dots" aria-hidden="true">…</span>
				<a class="page-numbers" href="#pns-pagination-many-page-10">10</a>
			</div>
			<a class="wp-block-query-pagination-next" href="#pns-pagination-many-page-5">Next <span class="wp-block-query-pagination-next-arrow is-arrow-arrow" aria-hidden="true">→</span></a>
		</nav>
	</section>
</div>
<!-- /wp:html -->

HTML;

$pns_style_guide_updated_content = str_replace(
	$pns_style_guide_needle,
	$pns_style_guide_examples . $pns_style_guide_needle,
	$pns_style_guide_content
);

if ( 1 !== substr_count( $pns_style_guide_updated_content, $pns_style_guide_marker ) ) {
	WP_CLI::error( 'The pagination specimen marker was not inserted exactly once.' );
}

$pns_style_guide_blocks = parse_blocks( $pns_style_guide_updated_content );

if ( empty( $pns_style_guide_blocks ) ) {
	WP_CLI::error( 'The updated Style Guide content did not parse into blocks.' );
}

$pns_style_guide_rendered = do_blocks( $pns_style_guide_updated_content );

foreach ( array( $pns_style_guide_marker, 'pns-style-guide-pagination-example--few', 'pns-style-guide-pagination-example--many' ) as $pns_style_guide_required_marker ) {
	if ( ! str_contains( $pns_style_guide_rendered, $pns_style_guide_required_marker ) ) {
		WP_CLI::error( sprintf( 'Rendered output is missing %s.', $pns_style_guide_required_marker ) );
	}
}

WP_CLI::log( sprintf( 'Page: %d (%s)', $pns_style_guide_id, $pns_style_guide_page->post_title ) );
WP_CLI::log( sprintf( 'Current SHA-256: %s', hash( 'sha256', $pns_style_guide_content ) ) );
WP_CLI::log( sprintf( 'Updated SHA-256: %s', hash( 'sha256', $pns_style_guide_updated_content ) ) );
WP_CLI::log( 'Validated few-page and many-page Query Pagination specimens.' );

if ( ! $pns_style_guide_apply ) {
	WP_CLI::success( 'Dry run passed. Re-run with -- apply to update the private Style Guide.' );
	return;
}

$pns_style_guide_backup_dir = dirname( get_stylesheet_directory(), 5 ) . '/docs/jobs/style-guide-pagination-db-backups';

if ( ! wp_mkdir_p( $pns_style_guide_backup_dir ) ) {
	WP_CLI::error( 'Could not create the Style Guide pagination backup directory.' );
}

$pns_style_guide_backup_path = sprintf(
	'%s/%s-post-%d-before-pagination-examples.json',
	$pns_style_guide_backup_dir,
	gmdate( 'Ymd-His' ),
	$pns_style_guide_id
);
$pns_style_guide_backup       = array(
	'post_id'       => $pns_style_guide_id,
	'post_name'     => $pns_style_guide_page->post_name,
	'post_status'   => $pns_style_guide_page->post_status,
	'post_title'    => $pns_style_guide_page->post_title,
	'post_content'  => $pns_style_guide_content,
	'content_sha256' => hash( 'sha256', $pns_style_guide_content ),
	'created_at_utc' => gmdate( 'c' ),
);
$pns_style_guide_backup_json  = wp_json_encode( $pns_style_guide_backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

if ( false === $pns_style_guide_backup_json || false === file_put_contents( $pns_style_guide_backup_path, $pns_style_guide_backup_json . "\n" ) ) {
	WP_CLI::error( 'Could not write the Style Guide pagination backup.' );
}

$pns_style_guide_update_result = wp_update_post(
	wp_slash(
		array(
			'ID'           => $pns_style_guide_id,
			'post_content' => $pns_style_guide_updated_content,
		)
	),
	true
);

if ( is_wp_error( $pns_style_guide_update_result ) ) {
	WP_CLI::error( $pns_style_guide_update_result->get_error_message() );
}

$pns_style_guide_saved_content = get_post_field( 'post_content', $pns_style_guide_id, 'raw' );

if ( 1 !== substr_count( $pns_style_guide_saved_content, $pns_style_guide_marker ) ) {
	WP_CLI::error( 'The saved Style Guide content did not retain the pagination marker exactly once.' );
}

WP_CLI::log( sprintf( 'Backup: %s', $pns_style_guide_backup_path ) );
WP_CLI::success( 'Added responsive Query Pagination specimens to the private Style Guide.' );
