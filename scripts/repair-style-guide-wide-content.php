<?php
/**
 * Bring the private PNS Style Guide page into line with its wide-content contract.
 *
 * Usage:
 *   wp eval-file scripts/repair-style-guide-wide-content.php
 *   wp eval-file scripts/repair-style-guide-wide-content.php -- apply
 *   wp eval-file scripts/repair-style-guide-wide-content.php -- <autosave-id> apply
 *
 * This is intentionally scoped to post #6340 and its current autosaves. It
 * replaces only the eight direct text-section content frames with section
 * frames and repairs the three known malformed paragraph samples. It refuses
 * mixed or unexpected saved content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_style_guide_repair_args    = isset( $args ) && is_array( $args ) ? array_values( $args ) : array();
$pns_style_guide_repair_apply   = in_array( 'apply', $pns_style_guide_repair_args, true ) || in_array( '--apply', $pns_style_guide_repair_args, true );
$pns_style_guide_repair_post_id = 6340;

foreach ( $pns_style_guide_repair_args as $pns_style_guide_repair_arg ) {
	if ( ctype_digit( (string) $pns_style_guide_repair_arg ) ) {
		$pns_style_guide_repair_post_id = absint( $pns_style_guide_repair_arg );
	}
}

$pns_style_guide_repair_post    = get_post( $pns_style_guide_repair_post_id );

if ( ! $pns_style_guide_repair_post ) {
	WP_CLI::error( 'Style Guide repair requires page #6340 or one of its current autosaves.' );
}

$pns_style_guide_parent_id = 6340;

if ( 'page' === $pns_style_guide_repair_post->post_type && 6340 === $pns_style_guide_repair_post_id ) {
	$pns_style_guide_parent_id = $pns_style_guide_repair_post_id;
} elseif ( 'revision' === $pns_style_guide_repair_post->post_type && wp_is_post_autosave( $pns_style_guide_repair_post ) && 6340 === (int) $pns_style_guide_repair_post->post_parent ) {
	$pns_style_guide_parent_id = (int) $pns_style_guide_repair_post->post_parent;
} else {
	WP_CLI::error( 'Style Guide repair accepts only page #6340 or an autosave belonging to it.' );
}

if ( 'page-light-surface-wide-content' !== get_page_template_slug( $pns_style_guide_parent_id ) ) {
	WP_CLI::error( 'Style Guide repair stopped because the parent page no longer uses the light-surface wide-content template.' );
}

/**
 * Test whether a class list includes an exact class token.
 *
 * @param mixed  $class_name Class name attribute.
 * @param string $token Required token.
 * @return bool
 */
function pns_style_guide_has_class( $class_name, $token ) {
	return is_string( $class_name ) && in_array( $token, preg_split( '/\s+/', trim( $class_name ) ), true );
}

/**
 * Replace one class token without changing any other saved class names.
 *
 * @param string $class_name Existing class list.
 * @param string $from Class to replace.
 * @param string $to Replacement class.
 * @return string
 */
function pns_style_guide_replace_class( $class_name, $from, $to ) {
	return preg_replace( '/(^|\s)' . preg_quote( $from, '/' ) . '(?=\s|$)/', '$1' . $to, $class_name, 1 );
}

/**
 * Synchronise a parsed block's static markup after its class attribute changes.
 *
 * @param array<string,mixed> $block Parsed block.
 * @param string              $from Class to replace.
 * @param string              $to Replacement class.
 * @return void
 */
function pns_style_guide_replace_block_markup_class( &$block, $from, $to ) {
	$block['innerHTML'] = str_replace( $from, $to, $block['innerHTML'] );

	foreach ( $block['innerContent'] as $index => $content ) {
		if ( is_string( $content ) ) {
			$block['innerContent'][ $index ] = str_replace( $from, $to, $content );
		}
	}
}

/**
 * Repair the exact malformed paragraph samples in the private style guide.
 *
 * @param array<string,mixed> $block Parsed block.
 * @param array<string,int>   $changes Change counters.
 * @return void
 */
function pns_style_guide_repair_paragraph( &$block, &$changes ) {
	if ( 'core/paragraph' !== $block['blockName'] || ! is_string( $block['innerHTML'] ) || false === strpos( $block['innerHTML'], 'var(u002d' ) ) {
		return;
	}

	$paragraphs = array(
		'Title Display sample text' => array(
			'font_size'   => 'title-display',
			'line_height' => 'display-tight',
			'font_family' => 'rubik',
			'html'        => '<p class="has-rubik-font-family has-title-display-font-size" style="font-weight:800;line-height:var(--wp--custom--typography--line-height--display-tight);text-transform:uppercase">Title Display sample text</p>',
		),
		'Title Large sample text'   => array(
			'font_size'   => 'title-large',
			'line_height' => 'heading-compact',
			'font_family' => 'rubik',
			'html'        => '<p class="has-rubik-font-family has-title-large-font-size" style="font-weight:800;line-height:var(--wp--custom--typography--line-height--heading-compact);text-transform:uppercase">Title Large sample text</p>',
		),
		'Quote text using the title-large scale.' => array(
			'font_size'   => 'title-large',
			'line_height' => 'heading-compact',
			'font_family' => '',
			'html'        => '<p class="has-title-large-font-size" style="line-height:var(--wp--custom--typography--line-height--heading-compact)">Quote text using the title-large scale.</p>',
		),
	);

	$sample = null;
	foreach ( $paragraphs as $text => $settings ) {
		if ( false !== strpos( $block['innerHTML'], $text ) ) {
			$sample = $settings;
			break;
		}
	}

	if ( null === $sample ) {
		WP_CLI::error( 'Style Guide repair found malformed paragraph markup outside the three approved samples.' );
	}

	$block['attrs']['fontSize']                         = $sample['font_size'];
	$block['attrs']['style']['typography']['lineHeight'] = 'var(--wp--custom--typography--line-height--' . $sample['line_height'] . ')';

	if ( $sample['font_family'] ) {
		$block['attrs']['fontFamily']                         = $sample['font_family'];
		$block['attrs']['style']['typography']['fontWeight']  = '800';
		$block['attrs']['style']['typography']['textTransform'] = 'uppercase';
	}

	$block['innerHTML']     = $sample['html'];
	$block['innerContent']  = array( $sample['html'] );
	$changes['paragraphs']++;
}

/**
 * Recursively repair the three explicitly approved malformed paragraph blocks.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed blocks.
 * @param array<string,int>              $changes Change counters.
 * @return void
 */
function pns_style_guide_repair_paragraphs( &$blocks, &$changes ) {
	foreach ( $blocks as &$block ) {
		pns_style_guide_repair_paragraph( $block, $changes );

		if ( ! empty( $block['innerBlocks'] ) ) {
			pns_style_guide_repair_paragraphs( $block['innerBlocks'], $changes );
		}
	}
	unset( $block );
}

$pns_style_guide_malformed_comments = preg_match_all( '/<!-- wp:paragraph (?P<attributes>\{.*?\}) -->/s', $pns_style_guide_repair_post->post_content, $pns_style_guide_comment_matches );
$pns_style_guide_mangled_comments   = 0;

foreach ( $pns_style_guide_comment_matches['attributes'] as $pns_style_guide_comment_attributes ) {
	if ( false !== strpos( $pns_style_guide_comment_attributes, 'var(u002d' ) ) {
		$pns_style_guide_mangled_comments++;
	}
}

if ( 0 !== $pns_style_guide_mangled_comments && 3 !== $pns_style_guide_mangled_comments ) {
	WP_CLI::error( sprintf( 'Style Guide repair expected three malformed paragraph comments, found %d.', $pns_style_guide_mangled_comments ) );
}

$pns_style_guide_normalized_content = preg_replace_callback(
	'/<!-- wp:paragraph (?P<attributes>\{.*?\}) -->/s',
	static function ( $match ) {
		return '<!-- wp:paragraph ' . str_replace( 'u002d', '\\u002d', $match['attributes'] ) . ' -->';
	},
	$pns_style_guide_repair_post->post_content
);

if ( null === $pns_style_guide_normalized_content ) {
	WP_CLI::error( 'Style Guide repair could not normalise paragraph block comments.' );
}

$pns_style_guide_blocks = parse_blocks( $pns_style_guide_normalized_content );
$pns_style_guide_frames = array();

foreach ( $pns_style_guide_blocks as $pns_style_guide_block_index => &$pns_style_guide_block ) {
	if (
		'core/group' !== $pns_style_guide_block['blockName']
		|| ! pns_style_guide_has_class( $pns_style_guide_block['attrs']['className'] ?? '', 'pns-text-only-section' )
		|| pns_style_guide_has_class( $pns_style_guide_block['attrs']['className'] ?? '', 'pns-style-guide-component-examples' )
	) {
		continue;
	}

	$pns_style_guide_direct_frames = array();
	foreach ( $pns_style_guide_block['innerBlocks'] as $pns_style_guide_child_index => $pns_style_guide_child ) {
		if ( 'core/group' !== $pns_style_guide_child['blockName'] ) {
			continue;
		}

		$pns_style_guide_class_name = $pns_style_guide_child['attrs']['className'] ?? '';
		if ( pns_style_guide_has_class( $pns_style_guide_class_name, 'pns-content-frame' ) || pns_style_guide_has_class( $pns_style_guide_class_name, 'pns-section-frame' ) ) {
			$pns_style_guide_direct_frames[] = array(
				'block_index' => $pns_style_guide_block_index,
				'child_index' => $pns_style_guide_child_index,
				'class_name'  => $pns_style_guide_class_name,
			);
		}
	}

	if ( 1 !== count( $pns_style_guide_direct_frames ) ) {
		WP_CLI::error( 'Style Guide repair expected exactly one direct frame in every text-only section.' );
	}

	$pns_style_guide_frames[] = $pns_style_guide_direct_frames[0];
}
unset( $pns_style_guide_block );

if ( 8 !== count( $pns_style_guide_frames ) ) {
	WP_CLI::error( sprintf( 'Style Guide repair expected eight top-level text-only section frames, found %d.', count( $pns_style_guide_frames ) ) );
}

$pns_style_guide_content_frames = array_filter(
	$pns_style_guide_frames,
	static function ( $frame ) {
		return pns_style_guide_has_class( $frame['class_name'], 'pns-content-frame' );
	}
);
$pns_style_guide_section_frames = array_filter(
	$pns_style_guide_frames,
	static function ( $frame ) {
		return pns_style_guide_has_class( $frame['class_name'], 'pns-section-frame' );
	}
);

if ( count( $pns_style_guide_content_frames ) && count( $pns_style_guide_section_frames ) ) {
	WP_CLI::error( 'Style Guide repair found a mixed direct-frame state and will not guess which sections to change.' );
}

if ( 0 === count( $pns_style_guide_content_frames ) && 8 !== count( $pns_style_guide_section_frames ) ) {
	WP_CLI::error( 'Style Guide repair found unexpected direct-frame classes.' );
}

$pns_style_guide_changes = array(
	'frames'     => 0,
	'paragraphs' => 0,
);

foreach ( $pns_style_guide_content_frames as $pns_style_guide_frame ) {
	$pns_style_guide_frame_block =& $pns_style_guide_blocks[ $pns_style_guide_frame['block_index'] ]['innerBlocks'][ $pns_style_guide_frame['child_index'] ];
	$pns_style_guide_frame_block['attrs']['className'] = pns_style_guide_replace_class( $pns_style_guide_frame_block['attrs']['className'], 'pns-content-frame', 'pns-section-frame' );
	pns_style_guide_replace_block_markup_class( $pns_style_guide_frame_block, 'pns-content-frame', 'pns-section-frame' );
	$pns_style_guide_changes['frames']++;
	unset( $pns_style_guide_frame_block );
}

pns_style_guide_repair_paragraphs( $pns_style_guide_blocks, $pns_style_guide_changes );

if ( 0 === $pns_style_guide_mangled_comments && 0 !== $pns_style_guide_changes['paragraphs'] ) {
	WP_CLI::error( 'Style Guide repair found malformed paragraph markup without its expected malformed block comments.' );
}

if ( 3 === $pns_style_guide_mangled_comments && 3 !== $pns_style_guide_changes['paragraphs'] ) {
	WP_CLI::error( sprintf( 'Style Guide repair expected to repair three paragraph samples, repaired %d.', $pns_style_guide_changes['paragraphs'] ) );
}

if ( 0 === $pns_style_guide_changes['frames'] && 0 === $pns_style_guide_changes['paragraphs'] ) {
	WP_CLI::success( 'Style Guide is already repaired; no database changes were needed.' );
	return;
}

$pns_style_guide_repaired_content = serialize_blocks( $pns_style_guide_blocks );

if ( false !== strpos( $pns_style_guide_repaired_content, 'var(u002d' ) || preg_match( '#<p[^>]*>\s*<p[^>]*>#s', $pns_style_guide_repaired_content ) ) {
	WP_CLI::error( 'Style Guide repair produced invalid paragraph markup and was not applied.' );
}

WP_CLI::log(
	sprintf(
		'Post #%d: %d direct content frame(s) changed to section frames; %d malformed paragraph sample(s) repaired.',
		$pns_style_guide_repair_post_id,
		$pns_style_guide_changes['frames'],
		$pns_style_guide_changes['paragraphs']
	)
);

if ( ! $pns_style_guide_repair_apply ) {
	WP_CLI::success( 'Dry-run complete. Re-run with apply to write a rollback export and update this page.' );
	return;
}

$pns_style_guide_backup_dir = dirname( get_stylesheet_directory(), 5 ) . '/docs/jobs/style-guide-wide-content-db-backups';

if ( ! is_dir( $pns_style_guide_backup_dir ) && ! wp_mkdir_p( $pns_style_guide_backup_dir ) ) {
	WP_CLI::error( sprintf( 'Could not create rollback directory: %s', $pns_style_guide_backup_dir ) );
}

$pns_style_guide_backup_path = sprintf(
	'%s/%s-post-%d-before-wide-content-repair.json',
	$pns_style_guide_backup_dir,
	gmdate( 'Ymd\\THis\\Z' ),
	$pns_style_guide_repair_post_id
);
$pns_style_guide_backup      = wp_json_encode(
	array(
		'schema_version' => 1,
		'created_at_gmt' => gmdate( 'c' ),
		'post_id'        => $pns_style_guide_repair_post_id,
		'post_title'     => $pns_style_guide_repair_post->post_title,
		'post_status'    => $pns_style_guide_repair_post->post_status,
		'parent_post_id' => $pns_style_guide_parent_id,
		'template'       => get_page_template_slug( $pns_style_guide_parent_id ),
		'content_sha256' => hash( 'sha256', $pns_style_guide_repair_post->post_content ),
		'post_content'   => $pns_style_guide_repair_post->post_content,
	),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);

if ( false === file_put_contents( $pns_style_guide_backup_path, $pns_style_guide_backup . "\n" ) ) {
	WP_CLI::error( sprintf( 'Could not write rollback export: %s', $pns_style_guide_backup_path ) );
}

$pns_style_guide_update = wp_update_post(
	wp_slash(
		array(
			'ID'           => $pns_style_guide_repair_post_id,
			'post_content' => $pns_style_guide_repaired_content,
		)
	),
	true
);

if ( is_wp_error( $pns_style_guide_update ) ) {
	WP_CLI::error( $pns_style_guide_update->get_error_message() );
}

clean_post_cache( $pns_style_guide_repair_post_id );

WP_CLI::success( sprintf( 'Repaired Style Guide post #%d. Rollback export: %s', $pns_style_guide_repair_post_id, $pns_style_guide_backup_path ) );
