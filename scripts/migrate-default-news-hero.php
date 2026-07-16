<?php
/**
 * Replace the two remaining default-template news headers with saved heroes.
 *
 * Usage:
 *   wp eval-file scripts/migrate-default-news-hero.php
 *   wp eval-file scripts/migrate-default-news-hero.php apply
 *   wp eval-file scripts/migrate-default-news-hero.php rollback <manifest-path>
 *
 * The no-argument mode is read-only. Apply writes a content-exact manifest
 * before changing either post. This deliberately targets no other posts.
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

const PNS_DEFAULT_NEWS_HERO_SCHEMA = 1;
const PNS_DEFAULT_NEWS_HERO_BLOCK  = 'ran/enhanced-cover';
const PNS_DEFAULT_NEWS_HERO_TARGETS = array(
	5128 => array(
		'slug' => 'work-with-us-argyll',
		'thumbnail' => 1951,
		'focus_meta' => true,
		'straplines' => array(
			'Community Research and Partnerships Assistant',
			'Admin and Marketing Assistant',
		),
	),
	5028 => array(
		'slug' => 'work-with-us-past-deadlines',
		'thumbnail' => 2333,
		'focus_meta' => false,
		'straplines' => array( 'Business Development Lead' ),
	),
);

$pns_default_news_hero_args = isset( $args ) && is_array( $args ) ? array_values( $args ) : array();
$pns_default_news_hero_mode = $pns_default_news_hero_args[0] ?? 'dry-run';

if ( 'rollback' === $pns_default_news_hero_mode ) {
	pns_default_news_hero_rollback( $pns_default_news_hero_args[1] ?? '' );
	return;
}

if ( ! in_array( $pns_default_news_hero_mode, array( 'dry-run', 'apply' ), true ) ) {
	WP_CLI::error( 'Use no argument for a dry run, apply, or rollback <manifest-path>.' );
}

$pns_default_news_hero_rows = array_map( 'pns_default_news_hero_row', array_keys( PNS_DEFAULT_NEWS_HERO_TARGETS ) );

foreach ( $pns_default_news_hero_rows as $pns_default_news_hero_row ) {
	WP_CLI::log( sprintf( '#%d %s: %s', $pns_default_news_hero_row['id'], $pns_default_news_hero_row['slug'], $pns_default_news_hero_row['state'] ) );
	foreach ( $pns_default_news_hero_row['notes'] as $pns_default_news_hero_note ) {
		WP_CLI::log( '  - ' . $pns_default_news_hero_note );
	}
}

if ( array_filter( $pns_default_news_hero_rows, static fn( $row ) => 'invalid' === $row['state'] ) ) {
	WP_CLI::error( 'Migration stopped because a target no longer matches its reviewed source contract.' );
}

$pns_default_news_hero_pending = array_values( array_filter( $pns_default_news_hero_rows, static fn( $row ) => 'would-migrate' === $row['state'] ) );

if ( 'dry-run' === $pns_default_news_hero_mode ) {
	WP_CLI::success( sprintf( 'Dry run complete. %d post(s) would change; %d already match.', count( $pns_default_news_hero_pending ), count( $pns_default_news_hero_rows ) - count( $pns_default_news_hero_pending ) ) );
	return;
}

if ( empty( $pns_default_news_hero_pending ) ) {
	WP_CLI::success( 'No posts require migration.' );
	return;
}

$pns_default_news_hero_manifest = pns_default_news_hero_write_manifest( $pns_default_news_hero_pending );
foreach ( $pns_default_news_hero_pending as $pns_default_news_hero_row ) {
	$result = wp_update_post( array( 'ID' => $pns_default_news_hero_row['id'], 'post_content' => $pns_default_news_hero_row['after'] ), true );
	if ( is_wp_error( $result ) ) {
		WP_CLI::error( sprintf( 'Could not update post %d: %s', $pns_default_news_hero_row['id'], $result->get_error_message() ) );
	}
	clean_post_cache( $pns_default_news_hero_row['id'] );
}

WP_CLI::success( sprintf( 'Migrated %d post(s). Rollback manifest: %s', count( $pns_default_news_hero_pending ), $pns_default_news_hero_manifest ) );

/** @return array<string,mixed> */
function pns_default_news_hero_row( $id ) {
	$contract = PNS_DEFAULT_NEWS_HERO_TARGETS[ $id ];
	$post     = get_post( $id );

	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type || 'publish' !== $post->post_status || $contract['slug'] !== $post->post_name || '' !== get_page_template_slug( $id ) || $contract['thumbnail'] !== get_post_thumbnail_id( $id ) ) {
		return pns_default_news_hero_invalid( $id, $contract['slug'], 'Post identity, publication state, default template, or Featured Image changed.' );
	}

	$poster_url = wp_get_attachment_url( $contract['thumbnail'] );
	if ( ! is_string( $poster_url ) || '' === $poster_url ) {
		return pns_default_news_hero_invalid( $id, $contract['slug'], 'Featured Image has no usable attachment URL.' );
	}

	$blocks = parse_blocks( $post->post_content );
	if ( empty( $blocks ) ) {
		return pns_default_news_hero_invalid( $id, $contract['slug'], 'Post has no serializable content blocks.' );
	}

	if ( pns_default_news_hero_is_complete( $blocks[0], $contract['thumbnail'], $poster_url ) ) {
		return array( 'id' => $id, 'slug' => $contract['slug'], 'state' => 'already-migrated', 'notes' => array( 'The first saved block already matches the shared content-owned news hero contract.' ), 'before' => pns_default_news_hero_snapshot( $post ), 'after' => $post->post_content );
	}

	$focal = array( 'x' => 0.5, 'y' => 0.5 );
	if ( $contract['focus_meta'] ) {
		$x = get_post_meta( $id, '_pns_featured_image_focus_x', true );
		$y = get_post_meta( $id, '_pns_featured_image_focus_y', true );
		if ( ! is_numeric( $x ) || ! is_numeric( $y ) || (float) $x < 0 || (float) $x > 1 || (float) $y < 0 || (float) $y > 1 ) {
			return pns_default_news_hero_invalid( $id, $contract['slug'], 'Argyll needs valid saved Featured Image focal-point metadata.' );
		}
		$focal = array( 'x' => (float) $x, 'y' => (float) $y );
	}

	if ( 5128 === $id ) {
		if ( PNS_DEFAULT_NEWS_HERO_BLOCK !== ( $blocks[0]['blockName'] ?? '' ) ) {
			return pns_default_news_hero_invalid( $id, $contract['slug'], 'Argyll no longer begins with the reviewed legacy Enhanced Cover.' );
		}
		$straplines = pns_default_news_hero_legacy_cover_straplines( $blocks[0] );
		if ( is_wp_error( $straplines ) ) {
			return pns_default_news_hero_invalid( $id, $contract['slug'], $straplines->get_error_message() );
		}
		if ( ! pns_default_news_hero_has_reviewed_straplines( $straplines, $contract['straplines'] ) ) {
			return pns_default_news_hero_invalid( $id, $contract['slug'], 'Argyll straplines changed after the reviewed migration snapshot.' );
		}
		$straplines = $contract['straplines'];
		array_shift( $blocks );
	} else {
		if ( 'core/heading' !== ( $blocks[0]['blockName'] ?? '' ) || 'Business Development Lead' !== trim( wp_strip_all_tags( $blocks[0]['innerHTML'] ?? '' ) ) ) {
			return pns_default_news_hero_invalid( $id, $contract['slug'], 'Past Deadlines no longer begins with its reviewed Business Development Lead heading.' );
		}
		$straplines = array( esc_html( trim( wp_strip_all_tags( $blocks[0]['innerHTML'] ) ) ) );
		if ( $straplines !== $contract['straplines'] ) {
			return pns_default_news_hero_invalid( $id, $contract['slug'], 'Past Deadlines lead heading changed after the reviewed migration snapshot.' );
		}
		array_shift( $blocks );
	}

	$hero = pns_default_news_hero_build( $contract['thumbnail'], $poster_url, $focal, $straplines );
	$after = serialize_block( $hero );
	if ( ! empty( $blocks ) ) {
		$after .= "\n\n" . serialize_blocks( $blocks );
	}

	return array(
		'id' => $id, 'slug' => $contract['slug'], 'state' => 'would-migrate',
		'notes' => array( 'Replace only the reviewed leading legacy header with a 80vh brand-purple 60% saved hero.', sprintf( 'Move %d existing lead text item(s) into editable strapline paragraph(s); preserve all remaining blocks.', count( $straplines ) ) ),
		'before' => pns_default_news_hero_snapshot( $post ), 'after' => $after,
	);
}

/**
 * Allow the reviewed source order and the order emitted by the earlier draft
 * migration, then normalize to the preserved editorial order on apply.
 *
 * @param string[] $actual   Parsed source straplines.
 * @param string[] $expected Reviewed editorial order.
 * @return bool
 */
function pns_default_news_hero_has_reviewed_straplines( $actual, $expected ) {
	if ( $actual === $expected ) {
		return true;
	}

	return array_reverse( $expected ) === $actual;
}

/** @return string[]|WP_Error */
function pns_default_news_hero_legacy_cover_straplines( $hero ) {
	$paragraphs = array();
	pns_default_news_hero_walk( $hero, static function( $block ) use ( &$paragraphs ) {
		if ( 'core/paragraph' === ( $block['blockName'] ?? '' ) ) {
			$text = trim( wp_strip_all_tags( $block['innerHTML'] ?? '' ) );
			if ( '' !== $text ) { $paragraphs[] = $text; }
		}
	} );
	return 2 === count( $paragraphs ) ? $paragraphs : new WP_Error( 'unexpected-argyll-straplines', 'Argyll legacy cover must contain exactly two non-empty strapline paragraphs.' );
}

/** @return array<string,mixed> */
function pns_default_news_hero_build( $poster_id, $poster_url, $focal, $straplines ) {
	$copy = array( pns_default_news_hero_dynamic( 'core/post-title', array( 'level' => 1, 'textColor' => 'neutral-0', 'fontSize' => 'title-large' ) ) );
	foreach ( $straplines as $index => $strapline ) { $copy[] = pns_default_news_hero_paragraph( $strapline, 0 === $index ? 'primary' : 'secondary' ); }
	$copy[] = pns_default_news_hero_dynamic( 'pns/post-details', array( 'lock' => array( 'move' => true, 'remove' => true ) ) );
	return pns_default_news_hero_container( PNS_DEFAULT_NEWS_HERO_BLOCK, array(
		'posterId' => $poster_id, 'posterUrl' => $poster_url, 'focalPoint' => $focal, 'minHeight' => 80, 'minHeightUnit' => 'vh', 'contentPosition' => 'center left', 'overlayColor' => 'brand-purple', 'customOverlayColor' => '', 'overlayOpacity' => 60, 'align' => 'full', 'className' => 'pns-section pns-layout pns-page-hero pns-site-frame-panel', 'textColor' => 'neutral-0',
	), array( pns_default_news_hero_group( 'pns-hero__inner pns-section-inner', array( pns_default_news_hero_group( 'pns-copy-column pns-hero-copy', $copy ) ) ) ) );
}

/** @return array<string,mixed> */
function pns_default_news_hero_group( $class, $children ) { return pns_default_news_hero_container( 'core/group', array( 'className' => $class ), $children ); }
/** @return array<string,mixed> */
function pns_default_news_hero_container( $name, $attrs, $children ) {
	if ( 'core/group' === $name ) {
		$inner = array( '<div class="wp-block-group ' . esc_attr( $attrs['className'] ?? '' ) . '">' ); foreach ( $children as $_child ) { $inner[] = null; } $inner[] = '</div>';
		return array( 'blockName' => $name, 'attrs' => $attrs, 'innerBlocks' => $children, 'innerHTML' => '', 'innerContent' => $inner );
	}
	return array( 'blockName' => $name, 'attrs' => $attrs, 'innerBlocks' => $children, 'innerHTML' => '', 'innerContent' => array( null ) );
}
/** @return array<string,mixed> */
function pns_default_news_hero_dynamic( $name, $attrs ) { return array( 'blockName' => $name, 'attrs' => $attrs, 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array() ); }
/** @return array<string,mixed> */
function pns_default_news_hero_paragraph( $content, $modifier ) {
	$class = 'pns-editorial-strapline pns-editorial-strapline--' . $modifier . ' has-neutral-0-color has-text-color has-text-lead-font-size';
	return array( 'blockName' => 'core/paragraph', 'attrs' => array( 'className' => 'pns-editorial-strapline pns-editorial-strapline--' . $modifier, 'textColor' => 'neutral-0', 'fontSize' => 'text-lead' ), 'innerBlocks' => array(), 'innerHTML' => '<p class="' . esc_attr( $class ) . '">' . $content . '</p>', 'innerContent' => array( '<p class="' . esc_attr( $class ) . '">' . $content . '</p>' ) );
}
function pns_default_news_hero_walk( $block, $callback ) { $callback( $block ); foreach ( $block['innerBlocks'] ?? array() as $child ) { pns_default_news_hero_walk( $child, $callback ); } }
function pns_default_news_hero_is_complete( $hero, $poster_id, $poster_url ) {
	if ( PNS_DEFAULT_NEWS_HERO_BLOCK !== ( $hero['blockName'] ?? '' ) ) { return false; }
	$attrs = $hero['attrs'] ?? array();
	if ( $poster_id !== absint( $attrs['posterId'] ?? 0 ) || $poster_url !== ( $attrs['posterUrl'] ?? '' ) || 80 !== absint( $attrs['minHeight'] ?? 0 ) || 'vh' !== ( $attrs['minHeightUnit'] ?? '' ) || 'brand-purple' !== ( $attrs['overlayColor'] ?? '' ) || 60 !== absint( $attrs['overlayOpacity'] ?? 0 ) ) { return false; }
	$title = 0; $details = 0; pns_default_news_hero_walk( $hero, static function( $block ) use ( &$title, &$details ) { if ( 'core/post-title' === ( $block['blockName'] ?? '' ) ) { ++$title; } if ( 'pns/post-details' === ( $block['blockName'] ?? '' ) && ! empty( $block['attrs']['lock']['move'] ) && ! empty( $block['attrs']['lock']['remove'] ) ) { ++$details; } } ); return 1 === $title && 1 === $details;
}
/** @return array<string,mixed> */
function pns_default_news_hero_snapshot( $post ) { return array( 'post_content' => $post->post_content, 'template' => get_page_template_slug( $post->ID ), 'featured_image' => get_post_thumbnail_id( $post->ID ), 'focal_point' => array( 'x' => get_post_meta( $post->ID, '_pns_featured_image_focus_x', true ), 'y' => get_post_meta( $post->ID, '_pns_featured_image_focus_y', true ) ) ); }
/** @return array<string,mixed> */
function pns_default_news_hero_invalid( $id, $slug, $note ) { return array( 'id' => $id, 'slug' => $slug, 'state' => 'invalid', 'notes' => array( $note ), 'before' => array(), 'after' => '' ); }
function pns_default_news_hero_write_manifest( $rows ) {
	$root = dirname( get_stylesheet_directory(), 5 ); $dir = $root . '/docs/jobs/default-news-hero-db-backups';
	if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) { WP_CLI::error( 'Could not create rollback directory.' ); }
	$rows = array_map( static fn( $row ) => array( 'post_id' => $row['id'], 'post_name' => $row['slug'], 'before' => $row['before'] ), $rows );
	$json = wp_json_encode( array( 'schema_version' => PNS_DEFAULT_NEWS_HERO_SCHEMA, 'created_at' => gmdate( 'c' ), 'operation' => 'default-news-headers-to-content-owned-ran-enhanced-cover', 'rows' => $rows ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	$path = $dir . '/' . gmdate( 'Ymd\\THis\\Z' ) . '-before-default-news-hero.json'; $tmp = $path . '.tmp';
	if ( ! is_string( $json ) || false === file_put_contents( $tmp, $json . "\n" ) || ! rename( $tmp, $path ) ) { WP_CLI::error( 'Could not write rollback manifest.' ); }
	return $path;
}
function pns_default_news_hero_rollback( $path ) {
	if ( '' === $path || ! is_readable( $path ) ) { WP_CLI::error( 'Rollback requires a readable manifest path.' ); }
	$manifest = json_decode( (string) file_get_contents( $path ), true );
	if ( ! is_array( $manifest ) || PNS_DEFAULT_NEWS_HERO_SCHEMA !== (int) ( $manifest['schema_version'] ?? 0 ) || ! is_array( $manifest['rows'] ?? null ) ) { WP_CLI::error( 'Unsupported rollback manifest.' ); }
	foreach ( $manifest['rows'] as $row ) { $id = absint( $row['post_id'] ?? 0 ); $content = $row['before']['post_content'] ?? null; if ( ! isset( PNS_DEFAULT_NEWS_HERO_TARGETS[ $id ] ) || ! is_string( $content ) ) { WP_CLI::error( 'Manifest target is invalid.' ); } $result = wp_update_post( array( 'ID' => $id, 'post_content' => $content ), true ); if ( is_wp_error( $result ) ) { WP_CLI::error( $result->get_error_message() ); } clean_post_cache( $id ); }
	WP_CLI::success( sprintf( 'Restored %d post(s) from %s.', count( $manifest['rows'] ), $path ) );
}
