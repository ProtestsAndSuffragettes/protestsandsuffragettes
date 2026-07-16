<?php
/**
 * Audit page sections against the standalone pattern library.
 *
 * Run from the project root:
 * wp eval-file app/public/wp-content/themes/protestsandsuffragettes/scripts/audit-published-page-patterns.php
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( 1 );
}

$pages = get_posts(
	array(
		'post_type'      => 'page',
		'post_status'    => pns_pattern_audit_page_statuses(),
		'posts_per_page' => -1,
		'orderby'        => 'ID',
		'order'          => 'ASC',
	)
);

$rows = array();

foreach ( $pages as $page ) {
	if ( pns_pattern_audit_should_skip_page( $page ) ) {
		continue;
	}

	$blocks = parse_blocks( $page->post_content );

	foreach ( $blocks as $index => $block ) {
		if ( ! pns_pattern_audit_is_section_candidate( $block ) ) {
			continue;
		}

		$classification = pns_pattern_audit_classify_block( $block, $page );

		$rows[] = array(
			'ID'         => (int) $page->ID,
			'slug'       => $page->post_name,
			'status'     => $page->post_status,
			'title'      => $page->post_title,
			'index'      => $index,
			'blockName'  => $block['blockName'] ?? '',
			'classes'    => $block['attrs']['className'] ?? '',
			'mapping'    => $classification['mapping'],
			'auditState' => $classification['status'],
			'evidence'   => $classification['evidence'],
			'heading'    => pns_pattern_audit_first_heading( $block ),
		);
	}
}

WP_CLI::log( '| Page | Page Status | Index | Block | Mapping | Audit State | Evidence |' );
WP_CLI::log( '| ---- | ----------- | ----- | ----- | ------- | ----------- | -------- |' );

foreach ( $rows as $row ) {
	WP_CLI::log(
		sprintf(
			'| `%s` | `%s` | `%d` | `%s` | %s | %s | %s |',
			esc_html( pns_pattern_audit_page_label( $row ) ),
			esc_html( $row['status'] ),
			$row['index'],
			esc_html( $row['blockName'] ),
			esc_html( $row['mapping'] ),
			esc_html( $row['auditState'] ),
			esc_html( pns_pattern_audit_compact_evidence( $row ) )
		)
	);
}

$needs_review = array_filter(
	$rows,
	static function ( $row ) {
		return 'needs-review' === $row['auditState'];
	}
);

WP_CLI::success(
	sprintf(
		'Page pattern audit checked %d section candidate(s); %d need review.',
		count( $rows ),
		count( $needs_review )
	)
);

/**
 * @return string[]
 */
function pns_pattern_audit_page_statuses() {
	return array( 'publish', 'private', 'draft' );
}

/**
 * @return string[]
 */
function pns_pattern_audit_excluded_page_slugs() {
	return array( 'news' );
}

/**
 * @param WP_Post $page Page object.
 */
function pns_pattern_audit_should_skip_page( $page ) {
	return in_array( $page->post_name, pns_pattern_audit_excluded_page_slugs(), true );
}

/**
 * @param array<string,mixed> $block Parsed block.
 */
function pns_pattern_audit_is_section_candidate( $block ) {
	$block_name = $block['blockName'] ?? '';

	if ( 'core/block' === $block_name ) {
		return true;
	}

	$align = $block['attrs']['align'] ?? '';

	if ( 'full' === $align ) {
		return true;
	}

	$class_name = $block['attrs']['className'] ?? '';

	return is_string( $class_name ) && false !== strpos( $class_name, 'pns-' );
}

/**
 * @param array<string,mixed> $block Parsed block.
 * @param WP_Post             $page Page object.
 * @return array{mapping:string,status:string,evidence:string}
 */
function pns_pattern_audit_classify_block( $block, $page ) {
	$block_name = $block['blockName'] ?? '';
	$class_name = isset( $block['attrs']['className'] ) && is_string( $block['attrs']['className'] ) ? $block['attrs']['className'] : '';
	$serialized = serialize_block( $block );

	if ( 'core/block' === $block_name ) {
		$ref = isset( $block['attrs']['ref'] ) ? (int) $block['attrs']['ref'] : 0;

		if ( $ref > 0 ) {
			$referenced_block = get_post( $ref );

			if ( $referenced_block instanceof WP_Post ) {
				return array(
					'mapping'  => 'synced-patterns/' . $referenced_block->post_name,
					'status'   => 'mapped',
					'evidence' => 'core/block ref=' . $ref,
				);
			}
		}

		if ( pns_pattern_audit_is_legacy_draft_or_private( $page ) ) {
			return array(
				'mapping'  => 'intentional legacy draft/private unresolved synced ref',
				'status'   => 'intentional-one-off',
				'evidence' => 'core/block without resolvable ref',
			);
		}

		return array(
			'mapping'  => 'unknown synced pattern',
			'status'   => 'needs-review',
			'evidence' => 'core/block without resolvable ref',
		);
	}

	$class_mappings = array(
		'pns-welcome-header'           => 'legacy homepage welcome section; use patterns/page-hero.php for new page headers',
		'pns-page-hero'                => 'patterns/page-hero.php',
		'pns-blockquote-cover'         => 'patterns/blockquote-cover.php',
		'pns-blockquote-with-red-line' => 'patterns/blockquote-with-red-line.php',
		'pns-basic-centred-content'    => 'patterns/basic-centred-content.php',
		'pns-suffragette-stats'        => 'patterns/suffragette-stats.php',
			'pns-entry-navigation'         => 'patterns/entry-herstory-navigation.php or patterns/entry-post-navigation.php',
		'pns-two-columns'              => 'patterns/two-columns.php',
		'pns-split-section'            => 'pns/split-section block via split-section image, slideshow, or video patterns',
		'pns-text-only-section'        => 'patterns/text-only-section.php',
		'pns-suffragette-hero'         => 'patterns/suffragette-hero.php',
		'pns-suffragette-text-media'   => 'legacy Herstories text/media section; use split-section or text-only patterns for new content',
		'pns-suffragette-facts'        => 'patterns/suffragette-facts.php',
		'pns-suffragette-image-strip'  => 'patterns/suffragette-image-strip.php',
		'pns-contact-form'             => 'synced-patterns/contact-form.html',
		'pns-connect-social'           => 'synced-patterns/connect-social.html',
		'pns-read-all-about-it-workshops' => 'synced-patterns/read-all-about-it-workshops.html',
		'pns-read-all-about-it'        => 'synced-patterns/read-all-about-it.html',
		'pns-shop-intro'               => 'synced-patterns/shop-intro.html',
	);

	foreach ( $class_mappings as $class => $mapping ) {
		if ( pns_pattern_audit_has_class( $class_name, $class ) ) {
			return array(
				'mapping'  => $mapping,
				'status'   => 'mapped',
				'evidence' => '.' . $class,
			);
		}
	}

	if ( false !== strpos( $serialized, 'pns-blockquote-with-red-line' ) ) {
		return array(
			'mapping'  => 'patterns/blockquote-with-red-line.php wrapper',
			'status'   => 'mapped',
			'evidence' => 'nested .pns-blockquote-with-red-line',
		);
	}

	if ( pns_pattern_audit_has_class( $class_name, 'pns-shop-storefront' ) ) {
		return array(
			'mapping'  => 'intentional one-off: Ecwid storefront',
			'status'   => 'intentional-one-off',
			'evidence' => $class_name,
		);
	}

	if ( pns_pattern_audit_has_class( $class_name, 'pns-pattern-qa' ) ) {
		return array(
			'mapping'  => 'intentional fixture: Pattern QA',
			'status'   => 'intentional-one-off',
			'evidence' => $class_name,
		);
	}

	if ( pns_pattern_audit_is_private_fixture( $page ) ) {
		return array(
			'mapping'  => 'intentional fixture: private test/editor content',
			'status'   => 'intentional-one-off',
			'evidence' => trim( $class_name ),
		);
	}

	if ( pns_pattern_audit_has_class( $class_name, 'pns-saved-section' ) ) {
		return array(
			'mapping'  => 'saved section compatibility hook',
			'status'   => 'needs-review',
			'evidence' => $class_name,
		);
	}

	if ( pns_pattern_audit_is_legacy_draft_or_private( $page ) ) {
		return array(
			'mapping'  => 'intentional legacy draft/private section',
			'status'   => 'intentional-one-off',
			'evidence' => trim( $class_name ),
		);
	}

	return array(
		'mapping'  => 'unclassified saved content',
		'status'   => 'needs-review',
		'evidence' => $class_name,
	);
}

function pns_pattern_audit_has_class( $class_name, $needle ) {
	return is_string( $class_name ) && preg_match( '/(^|\s)' . preg_quote( $needle, '/' ) . '(\s|$)/', $class_name );
}

/**
 * @param WP_Post $page Page object.
 */
function pns_pattern_audit_is_private_fixture( $page ) {
	return 'private' === $page->post_status && in_array( $page->post_name, array( 'pns-editor-css-fixture', 'test-page-2' ), true );
}

/**
 * @param WP_Post $page Page object.
 */
function pns_pattern_audit_is_legacy_draft_or_private( $page ) {
	return in_array( $page->post_status, array( 'draft', 'private' ), true );
}

/**
 * @param array<string,mixed> $block Parsed block.
 */
function pns_pattern_audit_first_heading( $block ) {
	if ( 'core/heading' === ( $block['blockName'] ?? '' ) ) {
		return trim( wp_strip_all_tags( $block['innerHTML'] ?? '' ) );
	}

	foreach ( $block['innerBlocks'] ?? array() as $inner_block ) {
		$heading = pns_pattern_audit_first_heading( $inner_block );

		if ( '' !== $heading ) {
			return $heading;
		}
	}

	return '';
}

/**
 * @param array<string,mixed> $row Audit row.
 */
function pns_pattern_audit_compact_evidence( $row ) {
	$parts = array_filter(
		array(
			$row['evidence'],
			$row['heading'] ? 'heading=' . $row['heading'] : '',
		)
	);

	return implode( '; ', $parts );
}

/**
 * @param array<string,mixed> $row Audit row.
 */
function pns_pattern_audit_page_label( $row ) {
	$slug = isset( $row['slug'] ) && is_string( $row['slug'] ) ? $row['slug'] : '';

	if ( '' !== $slug ) {
		return $slug;
	}

	return sprintf( '#%d %s', $row['ID'], $row['title'] );
}
