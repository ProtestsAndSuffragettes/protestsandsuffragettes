<?php
/**
 * Move template-owned full-width news heroes into saved post content.
 *
 * Usage:
 *   wp eval-file scripts/migrate-editorial-news-hero.php
 *   wp eval-file scripts/migrate-editorial-news-hero.php apply
 *   wp eval-file scripts/migrate-editorial-news-hero.php rollback <backup-path>
 *
 * The default dry run is read-only. Apply first writes one rollback manifest,
 * then either prepends a RAN Enhanced Cover to each approved pre-migration
 * post or upgrades the locked dynamic details block in an already-saved hero.
 * Rollback restores only the exact content and meta values recorded in that
 * manifest.
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( 1 );
}

const PNS_NEWS_HERO_TEMPLATE             = 'single-full-width-news';
const PNS_NEWS_HERO_EXPECTED_POST_COUNT  = 17;
const PNS_NEWS_HERO_PRIMARY_META          = 'pns_editorial_strapline_primary';
const PNS_NEWS_HERO_SECONDARY_META        = 'pns_editorial_strapline_secondary';
const PNS_NEWS_HERO_FOCUS_X_META          = '_pns_featured_image_focus_x';
const PNS_NEWS_HERO_FOCUS_Y_META          = '_pns_featured_image_focus_y';
const PNS_NEWS_HERO_BLOCK                 = 'ran/enhanced-cover';
const PNS_NEWS_HERO_DETAILS_BLOCK         = 'pns/post-details';
const PNS_NEWS_HERO_METADATA_BLOCK        = 'pns/post-metadata';
const PNS_NEWS_HERO_BACKUP_SCHEMA_VERSION = 2;

$pns_news_hero_arguments = isset( $args ) && is_array( $args ) ? array_values( $args ) : array();
$pns_news_hero_mode      = $pns_news_hero_arguments[0] ?? 'dry-run';

if ( 'rollback' === $pns_news_hero_mode ) {
	pns_news_hero_rollback( $pns_news_hero_arguments[1] ?? '' );
	return;
}

if ( ! in_array( $pns_news_hero_mode, array( 'dry-run', 'apply' ), true ) ) {
	WP_CLI::error( 'Use no argument for a dry run, apply, or rollback <backup-path>.' );
}

$pns_news_hero_posts = pns_news_hero_find_targets();

if ( PNS_NEWS_HERO_EXPECTED_POST_COUNT !== count( $pns_news_hero_posts ) ) {
	WP_CLI::error(
		sprintf(
			'Expected exactly %d published posts assigned %s; found %d. Refusing to migrate an unreviewed target set.',
			PNS_NEWS_HERO_EXPECTED_POST_COUNT,
			PNS_NEWS_HERO_TEMPLATE,
			count( $pns_news_hero_posts )
		)
	);
}

$pns_news_hero_rows = array_map( 'pns_news_hero_build_row', $pns_news_hero_posts );

foreach ( $pns_news_hero_rows as $pns_news_hero_row ) {
	WP_CLI::log(
		sprintf(
			'#%d %s: %s',
			$pns_news_hero_row['post_id'],
			$pns_news_hero_row['post_name'],
			$pns_news_hero_row['state']
		)
	);

	foreach ( $pns_news_hero_row['changes'] as $pns_news_hero_change ) {
		WP_CLI::log( '  - ' . $pns_news_hero_change );
	}
}

$pns_news_hero_invalid_rows = array_filter(
	$pns_news_hero_rows,
	static function ( $row ) {
		return 'invalid-contract' === $row['state'];
	}
);

if ( ! empty( $pns_news_hero_invalid_rows ) ) {
	WP_CLI::error( 'Migration stopped because at least one target does not satisfy the strict news-hero contract.' );
}

$pns_news_hero_pending_rows = array_values(
	array_filter(
		$pns_news_hero_rows,
		static function ( $row ) {
			return in_array( $row['state'], array( 'would-migrate', 'would-upgrade' ), true );
		}
	)
);

if ( 'dry-run' === $pns_news_hero_mode ) {
	WP_CLI::success(
		sprintf(
			'Dry run complete. %d post(s) would change and %d already satisfy the saved-hero contract.',
			count( $pns_news_hero_pending_rows ),
			count( $pns_news_hero_rows ) - count( $pns_news_hero_pending_rows )
		)
	);
	return;
}

if ( empty( $pns_news_hero_pending_rows ) ) {
	WP_CLI::success( 'No posts require migration.' );
	return;
}

$pns_news_hero_backup_path = pns_news_hero_write_backup( $pns_news_hero_pending_rows );

foreach ( $pns_news_hero_pending_rows as $pns_news_hero_row ) {
	$result = wp_update_post(
		array(
			'ID'           => $pns_news_hero_row['post_id'],
			'post_content' => $pns_news_hero_row['target']['post_content'],
		),
		true
	);

	if ( is_wp_error( $result ) ) {
		WP_CLI::error( sprintf( 'Could not update post %d: %s', $pns_news_hero_row['post_id'], $result->get_error_message() ) );
	}

	if ( ! empty( $pns_news_hero_row['target']['delete_strapline_meta'] ) ) {
		foreach ( array( PNS_NEWS_HERO_PRIMARY_META, PNS_NEWS_HERO_SECONDARY_META ) as $meta_key ) {
			if ( ! delete_post_meta( $pns_news_hero_row['post_id'], $meta_key ) ) {
				WP_CLI::error( sprintf( 'Could not delete transferred %s from post %d.', $meta_key, $pns_news_hero_row['post_id'] ) );
			}
		}
	}

	clean_post_cache( $pns_news_hero_row['post_id'] );
}

WP_CLI::success( sprintf( 'Migrated %d post(s). Rollback manifest: %s', count( $pns_news_hero_pending_rows ), $pns_news_hero_backup_path ) );

/**
 * Return published posts using the exact news template in stable ID order.
 *
 * @return WP_Post[]
 */
function pns_news_hero_find_targets() {
	return get_posts(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'          => -1,
			'orderby'                 => 'ID',
			'order'                   => 'ASC',
			'no_found_rows'           => true,
			'ignore_sticky_posts'     => true,
			'update_post_meta_cache'  => true,
			'update_post_term_cache'  => false,
			'meta_query'              => array(
				array(
					'key'   => '_wp_page_template',
					'value' => PNS_NEWS_HERO_TEMPLATE,
				),
			),
		)
	);
}

/**
 * Validate one post and build its content replacement without writing it.
 *
 * @param WP_Post $post Target post.
 * @return array<string,mixed>
 */
function pns_news_hero_build_row( $post ) {
	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type || 'publish' !== $post->post_status || PNS_NEWS_HERO_TEMPLATE !== get_page_template_slug( $post->ID ) ) {
		return pns_news_hero_invalid_row( $post, 'Post type, status, or template assignment changed while building the migration set.' );
	}

	$blocks = parse_blocks( $post->post_content );

	if ( ! empty( $blocks ) && PNS_NEWS_HERO_BLOCK === ( $blocks[0]['blockName'] ?? '' ) ) {
		return pns_news_hero_existing_or_inconsistent_row( $post, $blocks[0] );
	}

	$featured_image = pns_news_hero_featured_image( $post->ID );

	if ( is_wp_error( $featured_image ) ) {
		return pns_news_hero_invalid_row( $post, $featured_image->get_error_message() );
	}

	$focal_point = pns_news_hero_focal_point( $post->ID );

	if ( is_wp_error( $focal_point ) ) {
		return pns_news_hero_invalid_row( $post, $focal_point->get_error_message() );
	}

	$straplines = pns_news_hero_straplines( $post->ID );

	if ( is_wp_error( $straplines ) ) {
		return pns_news_hero_invalid_row( $post, $straplines->get_error_message() );
	}

	$hero_block     = pns_news_hero_build_block( $featured_image, $focal_point, $straplines );
	$target_content = serialize_block( $hero_block ) . "\n\n" . $post->post_content;

	return array(
		'post_id'   => $post->ID,
		'post_name' => $post->post_name,
		'state'     => 'would-migrate',
		'changes'   => array(
			'Prepend one saved ran/enhanced-cover hero using Featured Image #' . $featured_image['id'] . ' and its saved focal point.',
			'Insert dynamic Post Title and locked pns/post-details.',
			sprintf( 'Transfer %d non-empty editorial strapline(s) into ordinary editable paragraph block(s).', count( $straplines ) ),
			'Delete only the transferred editorial strapline meta after the saved content update succeeds.',
		),
		'before'    => pns_news_hero_snapshot( $post ),
		'target'    => array(
			'post_content'          => $target_content,
			'delete_strapline_meta' => true,
		),
	);
}

/**
 * Validate a prior migration without assuming its editable straplines stayed unchanged.
 *
 * @param WP_Post $post Target post.
 * @param array   $hero First parsed block.
 * @return array<string,mixed>
 */
function pns_news_hero_existing_or_inconsistent_row( $post, $hero ) {
	$featured_image = pns_news_hero_featured_image( $post->ID );

	if ( is_wp_error( $featured_image ) ) {
		return pns_news_hero_invalid_row( $post, $featured_image->get_error_message() );
	}

	$focal_point = pns_news_hero_focal_point( $post->ID );

	if ( is_wp_error( $focal_point ) ) {
		return pns_news_hero_invalid_row( $post, $focal_point->get_error_message() );
	}

	$attrs = isset( $hero['attrs'] ) && is_array( $hero['attrs'] ) ? $hero['attrs'] : array();

	if (
		absint( $attrs['posterId'] ?? 0 ) !== $featured_image['id'] ||
		(string) ( $attrs['posterUrl'] ?? '' ) !== $featured_image['url'] ||
		! pns_news_hero_focal_points_match( $attrs['focalPoint'] ?? null, $focal_point )
	) {
		return pns_news_hero_invalid_row( $post, 'First content block is ran/enhanced-cover but does not match the required saved-hero contract.' );
	}

	foreach ( array( PNS_NEWS_HERO_PRIMARY_META, PNS_NEWS_HERO_SECONDARY_META ) as $meta_key ) {
		if ( metadata_exists( 'post', $post->ID, $meta_key ) ) {
			return pns_news_hero_invalid_row( $post, 'Saved hero exists but legacy editorial strapline meta has not been removed.' );
		}
	}

	if ( pns_news_hero_contains_required_blocks( $hero ) ) {
		return array(
			'post_id'   => $post->ID,
			'post_name' => $post->post_name,
			'state'     => 'already-migrated',
			'changes'   => array( 'No mutation required; saved hero, Featured Image, focal point, dynamic details block, and legacy meta cleanup are all present.' ),
			'before'    => pns_news_hero_snapshot( $post ),
			'target'    => array(),
		);
	}

	$upgraded_content = pns_news_hero_upgrade_legacy_metadata_block( $post->post_content, $hero );

	if ( is_wp_error( $upgraded_content ) ) {
		return pns_news_hero_invalid_row( $post, $upgraded_content->get_error_message() );
	}

	return array(
		'post_id'   => $post->ID,
		'post_name' => $post->post_name,
		'state'     => 'would-upgrade',
		'changes'   => array( 'Replace exactly one locked pns/post-metadata hero block with locked pns/post-details; preserve the saved cover, title, straplines, and body blocks.' ),
		'before'    => pns_news_hero_snapshot( $post ),
		'target'    => array(
			'post_content'          => $upgraded_content,
			'delete_strapline_meta' => false,
		),
	);
}

/**
 * Get one valid Featured Image and its canonical attachment URL.
 *
 * @param int $post_id Post ID.
 * @return array<string,mixed>|WP_Error
 */
function pns_news_hero_featured_image( $post_id ) {
	$attachment_id = get_post_thumbnail_id( $post_id );

	if ( $attachment_id <= 0 || 'attachment' !== get_post_type( $attachment_id ) ) {
		return new WP_Error( 'missing-featured-image', 'A published full-width news post must have a valid Featured Image attachment.' );
	}

	$url = wp_get_attachment_url( $attachment_id );

	if ( ! is_string( $url ) || '' === $url ) {
		return new WP_Error( 'missing-featured-image-url', 'The Featured Image attachment has no usable canonical URL.' );
	}

	return array(
		'id'  => $attachment_id,
		'url' => $url,
	);
}

/**
 * Get one complete, normalized focal point pair.
 *
 * @param int $post_id Post ID.
 * @return array<string,float>|WP_Error
 */
function pns_news_hero_focal_point( $post_id ) {
	if ( ! metadata_exists( 'post', $post_id, PNS_NEWS_HERO_FOCUS_X_META ) || ! metadata_exists( 'post', $post_id, PNS_NEWS_HERO_FOCUS_Y_META ) ) {
		return new WP_Error( 'missing-focal-point', 'Featured Image focal point metadata must contain both X and Y values.' );
	}

	$x = get_post_meta( $post_id, PNS_NEWS_HERO_FOCUS_X_META, true );
	$y = get_post_meta( $post_id, PNS_NEWS_HERO_FOCUS_Y_META, true );

	if ( ! is_numeric( $x ) || ! is_numeric( $y ) || (float) $x < 0 || (float) $x > 1 || (float) $y < 0 || (float) $y > 1 ) {
		return new WP_Error( 'invalid-focal-point', 'Featured Image focal point metadata must be numeric values between 0 and 1.' );
	}

	return array(
		'x' => (float) $x,
		'y' => (float) $y,
	);
}

/**
 * Validate and return the non-empty existing editorial straplines in order.
 *
 * @param int $post_id Post ID.
 * @return string[]|WP_Error
 */
function pns_news_hero_straplines( $post_id ) {
	$values = array();

	foreach ( array( PNS_NEWS_HERO_PRIMARY_META, PNS_NEWS_HERO_SECONDARY_META ) as $meta_key ) {
		if ( ! metadata_exists( 'post', $post_id, $meta_key ) ) {
			return new WP_Error( 'missing-strapline-meta', sprintf( 'Expected editorial strapline meta key %s is missing.', $meta_key ) );
		}

		$value = get_post_meta( $post_id, $meta_key, true );

		if ( ! is_string( $value ) ) {
			return new WP_Error( 'invalid-strapline-meta', sprintf( 'Editorial strapline meta key %s must be a single string.', $meta_key ) );
		}

		$value = trim( wp_kses_post( $value ) );

		if ( '' !== $value ) {
			$values[] = $value;
		}
	}

	return $values;
}

/**
 * Build the serialized block tree for one saved news hero.
 *
 * @param array<string,mixed> $featured_image Featured image data.
 * @param array<string,float> $focal_point Featured image focal point.
 * @param string[]            $straplines Sanitized strapline markup.
 * @return array<string,mixed>
 */
function pns_news_hero_build_block( $featured_image, $focal_point, $straplines ) {
	$copy_blocks = array(
		pns_news_hero_dynamic_block(
			'core/post-title',
			array(
				'level'     => 1,
				'textColor' => 'neutral-0',
				'fontSize'  => 'title-large',
			)
		),
	);

	foreach ( $straplines as $index => $strapline ) {
		$modifier      = 0 === $index ? 'primary' : 'secondary';
		$copy_blocks[] = pns_news_hero_paragraph_block( $strapline, $modifier );
	}

	$copy_blocks[] = pns_news_hero_dynamic_block(
		PNS_NEWS_HERO_DETAILS_BLOCK,
		array(
			'lock'         => array(
				'move'   => true,
				'remove' => true,
			),
		)
	);

	return pns_news_hero_container_block(
		PNS_NEWS_HERO_BLOCK,
		array(
			'posterId'           => $featured_image['id'],
			'posterUrl'          => $featured_image['url'],
			'focalPoint'         => $focal_point,
			'minHeight'          => 80,
			'minHeightUnit'      => 'vh',
			'contentPosition'    => 'center left',
			'customBackgroundColor' => '#908d76',
			'customOverlayColor' => '#908d76',
			'overlayOpacity'     => 0,
			'align'              => 'full',
			'className'          => 'pns-section pns-layout pns-page-hero pns-site-frame-panel',
			'textColor'          => 'neutral-0',
		),
		array(
			pns_news_hero_container_block(
				'core/group',
				array( 'className' => 'pns-hero__inner pns-section-inner' ),
				array(
					pns_news_hero_container_block(
						'core/group',
						array(
							'className' => 'pns-copy-column pns-hero-copy',
							'style'     => array(
								'spacing' => array( 'blockGap' => 'var:preset|spacing|tight' ),
							),
						),
						$copy_blocks
					),
				)
			)
		)
	);
}

/**
 * Build a generic container block with valid saved group markup where needed.
 *
 * @param string              $name Block name.
 * @param array<string,mixed> $attrs Block attributes.
 * @param array<int,array>    $inner_blocks Child blocks.
 * @return array<string,mixed>
 */
function pns_news_hero_container_block( $name, $attrs, $inner_blocks ) {
	if ( 'core/group' === $name ) {
		$class_name = 'wp-block-group';

		if ( ! empty( $attrs['className'] ) ) {
			$class_name .= ' ' . $attrs['className'];
		}

		$inner_content = array( '<div class="' . esc_attr( $class_name ) . '">' );

		foreach ( $inner_blocks as $unused_inner_block ) {
			$inner_content[] = null;
		}

		$inner_content[] = '</div>';

		return array(
			'blockName'    => $name,
			'attrs'        => $attrs,
			'innerBlocks'  => $inner_blocks,
			'innerHTML'    => '',
			'innerContent' => $inner_content,
		);
	}

	return array(
		'blockName'    => $name,
		'attrs'        => $attrs,
		'innerBlocks'  => $inner_blocks,
		'innerHTML'    => '',
		'innerContent' => array( null ),
	);
}

/**
 * Build a self-closing dynamic block.
 *
 * @param string              $name Block name.
 * @param array<string,mixed> $attrs Block attributes.
 * @return array<string,mixed>
 */
function pns_news_hero_dynamic_block( $name, $attrs ) {
	return array(
		'blockName'    => $name,
		'attrs'        => $attrs,
		'innerBlocks'  => array(),
		'innerHTML'    => '',
		'innerContent' => array(),
	);
}

/**
 * Build one saved, editor-owned strapline paragraph.
 *
 * @param string $content Safe inline markup.
 * @param string $modifier primary or secondary.
 * @return array<string,mixed>
 */
function pns_news_hero_paragraph_block( $content, $modifier ) {
	$class_name = 'pns-editorial-strapline pns-editorial-strapline--' . $modifier . ' has-neutral-0-color has-text-color has-text-lead-font-size';

	return array(
		'blockName'    => 'core/paragraph',
		'attrs'        => array(
			'className' => 'pns-editorial-strapline pns-editorial-strapline--' . $modifier,
			'textColor' => 'neutral-0',
			'fontSize'  => 'text-lead',
		),
		'innerBlocks'  => array(),
		'innerHTML'    => '<p class="' . esc_attr( $class_name ) . '">' . $content . '</p>',
		'innerContent' => array( '<p class="' . esc_attr( $class_name ) . '">' . $content . '</p>' ),
	);
}

/**
 * Confirm the dynamic title and locked hero-details block are present below the hero.
 *
 * @param array $block Parsed block.
 * @return bool
 */
function pns_news_hero_contains_required_blocks( $block ) {
	$found_title           = false;
	$details_count         = 0;
	$legacy_metadata_count = 0;

	pns_news_hero_walk_blocks(
		$block,
		static function ( $candidate ) use ( &$found_title, &$details_count, &$legacy_metadata_count ) {
			if ( 'core/post-title' === ( $candidate['blockName'] ?? '' ) ) {
				$found_title = true;
			}

			if ( PNS_NEWS_HERO_METADATA_BLOCK === ( $candidate['blockName'] ?? '' ) ) {
				++$legacy_metadata_count;
			}

			if ( PNS_NEWS_HERO_DETAILS_BLOCK === ( $candidate['blockName'] ?? '' ) ) {
				$attrs          = isset( $candidate['attrs'] ) && is_array( $candidate['attrs'] ) ? $candidate['attrs'] : array();
				$lock           = isset( $attrs['lock'] ) && is_array( $attrs['lock'] ) ? $attrs['lock'] : array();

				if ( ! empty( $lock['move'] ) && ! empty( $lock['remove'] ) ) {
					++$details_count;
				}
			}
		}
	);

	return $found_title && 1 === $details_count && 0 === $legacy_metadata_count;
}

/**
 * Swap the former locked metadata block in the already-migrated hero only.
 *
 * The replacement operates on the serialized first block rather than
 * reserializing the tree, so editor-owned title, strapline, and wrapper markup
 * remain byte-for-byte unchanged.
 *
 * @param string $post_content Full saved post content.
 * @param array  $hero First parsed block.
 * @return string|WP_Error
 */
function pns_news_hero_upgrade_legacy_metadata_block( $post_content, $hero ) {
	$legacy_details = array();
	$details_count  = 0;
	$metadata_count = 0;

	pns_news_hero_walk_blocks(
		$hero,
		static function ( $candidate ) use ( &$legacy_details, &$details_count, &$metadata_count ) {
			if ( PNS_NEWS_HERO_DETAILS_BLOCK === ( $candidate['blockName'] ?? '' ) ) {
				++$details_count;
			}

			if ( PNS_NEWS_HERO_METADATA_BLOCK === ( $candidate['blockName'] ?? '' ) ) {
				++$metadata_count;
				$attrs = isset( $candidate['attrs'] ) && is_array( $candidate['attrs'] ) ? $candidate['attrs'] : array();
				$lock  = isset( $attrs['lock'] ) && is_array( $attrs['lock'] ) ? $attrs['lock'] : array();

				if ( 'hero' === ( $attrs['presentation'] ?? '' ) && ! empty( $lock['move'] ) && ! empty( $lock['remove'] ) ) {
					$legacy_details[] = $candidate;
				}
			}
		}
	);

	if ( 1 !== count( $legacy_details ) || 1 !== $metadata_count || 0 !== $details_count ) {
		return new WP_Error( 'invalid-legacy-details', 'Saved hero must contain exactly one locked pns/post-metadata block with presentation "hero" before it can be upgraded.' );
	}

	$serialized_hero = serialize_block( $hero );
	$hero_offset     = strpos( $post_content, $serialized_hero );

	if ( false === $hero_offset ) {
		return new WP_Error( 'unlocatable-saved-hero', 'Could not locate the original serialized first hero block without changing its editor-owned content.' );
	}

	$replacement = serialize_block(
		pns_news_hero_dynamic_block(
			PNS_NEWS_HERO_DETAILS_BLOCK,
			array(
				'lock' => array(
					'move'   => true,
					'remove' => true,
				),
			)
		)
	);
	$upgraded_hero = preg_replace(
		'/<!-- wp:pns\\/post-metadata\\b.*?\\/-->/s',
		$replacement,
		$serialized_hero,
		-1,
		$replacement_count
	);

	if ( ! is_string( $upgraded_hero ) || 1 !== $replacement_count ) {
		return new WP_Error( 'ambiguous-legacy-details', 'Could not replace exactly one serialized pns/post-metadata hero block.' );
	}

	return substr_replace( $post_content, $upgraded_hero, $hero_offset, strlen( $serialized_hero ) );
}

/**
 * Walk one parsed block tree.
 *
 * @param array    $block Parsed block.
 * @param callable $callback Visitor.
 * @return void
 */
function pns_news_hero_walk_blocks( $block, $callback ) {
	$callback( $block );

	foreach ( $block['innerBlocks'] ?? array() as $child ) {
		pns_news_hero_walk_blocks( $child, $callback );
	}
}

/**
 * Compare stored block focal data to the canonical saved point.
 *
 * @param mixed               $candidate Block attribute.
 * @param array<string,float> $expected Saved point.
 * @return bool
 */
function pns_news_hero_focal_points_match( $candidate, $expected ) {
	return is_array( $candidate ) && isset( $candidate['x'], $candidate['y'] ) && abs( (float) $candidate['x'] - $expected['x'] ) < 0.000001 && abs( (float) $candidate['y'] - $expected['y'] ) < 0.000001;
}

/**
 * Capture the exact project-owned data needed for one rollback and audit.
 *
 * @param WP_Post $post Target post.
 * @return array<string,mixed>
 */
function pns_news_hero_snapshot( $post ) {
	$meta = array();

	foreach ( array( '_wp_page_template', '_thumbnail_id', PNS_NEWS_HERO_FOCUS_X_META, PNS_NEWS_HERO_FOCUS_Y_META, PNS_NEWS_HERO_PRIMARY_META, PNS_NEWS_HERO_SECONDARY_META ) as $meta_key ) {
		$meta[ $meta_key ] = array(
			'exists' => metadata_exists( 'post', $post->ID, $meta_key ),
			'value'  => get_post_meta( $post->ID, $meta_key, true ),
		);
	}

	return array(
		'post_content' => $post->post_content,
		'post_excerpt' => $post->post_excerpt,
		'template'     => get_page_template_slug( $post->ID ),
		'featured_image' => array(
			'id'  => get_post_thumbnail_id( $post->ID ),
			'url' => wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) ),
		),
		'meta'          => $meta,
	);
}

/**
 * Return a stable invalid row.
 *
 * @param WP_Post|null $post Target post.
 * @param string       $reason Validation error.
 * @return array<string,mixed>
 */
function pns_news_hero_invalid_row( $post, $reason ) {
	return array(
		'post_id'   => $post instanceof WP_Post ? $post->ID : 0,
		'post_name' => $post instanceof WP_Post ? $post->post_name : '',
		'state'     => 'invalid-contract',
		'changes'   => array( $reason ),
		'before'    => $post instanceof WP_Post ? pns_news_hero_snapshot( $post ) : array(),
		'target'    => array(),
	);
}

/**
 * Write a JSON rollback manifest atomically before the first mutation.
 *
 * @param array<int,array<string,mixed>> $rows Pending rows.
 * @return string
 */
function pns_news_hero_write_backup( $rows ) {
	$project_root = dirname( get_stylesheet_directory(), 5 );
	$backup_dir   = $project_root . '/docs/jobs/news-hero-content-db-backups';

	if ( ! is_dir( $backup_dir ) && ! wp_mkdir_p( $backup_dir ) ) {
		WP_CLI::error( sprintf( 'Could not create backup directory: %s', $backup_dir ) );
	}

	$backup = array(
		'schema_version' => PNS_NEWS_HERO_BACKUP_SCHEMA_VERSION,
		'created_at'     => gmdate( 'c' ),
		'operation'      => 'template-owned-news-hero-to-saved-ran-enhanced-cover',
		'rows'           => $rows,
	);
	$json   = wp_json_encode( $backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

	if ( ! is_string( $json ) ) {
		WP_CLI::error( 'Could not encode news hero rollback data.' );
	}

	$backup_path = sprintf( '%s/%s-before-saved-news-hero.json', $backup_dir, gmdate( 'Ymd\\THis\\Z' ) );
	$temp_path   = $backup_path . '.tmp';

	if ( false === file_put_contents( $temp_path, $json . "\n" ) || ! rename( $temp_path, $backup_path ) ) {
		WP_CLI::error( sprintf( 'Could not write news hero rollback data at %s.', $backup_path ) );
	}

	return $backup_path;
}

/**
 * Restore an approved manifest exactly.
 *
 * @param string $backup_path Manifest path.
 * @return void
 */
function pns_news_hero_rollback( $backup_path ) {
	if ( '' === $backup_path || ! is_readable( $backup_path ) ) {
		WP_CLI::error( 'Rollback requires a readable backup path.' );
	}

	$backup = json_decode( (string) file_get_contents( $backup_path ), true );

	if ( ! is_array( $backup ) || PNS_NEWS_HERO_BACKUP_SCHEMA_VERSION !== (int) ( $backup['schema_version'] ?? 0 ) || ! isset( $backup['rows'] ) || ! is_array( $backup['rows'] ) ) {
		WP_CLI::error( 'Rollback manifest has an unsupported schema.' );
	}

	foreach ( $backup['rows'] as $row ) {
		$post_id = absint( $row['post_id'] ?? 0 );
		$before  = $row['before'] ?? null;

		if ( 0 >= $post_id || ! is_array( $before ) || PNS_NEWS_HERO_TEMPLATE !== get_page_template_slug( $post_id ) || 'post' !== get_post_type( $post_id ) ) {
			WP_CLI::error( 'Rollback manifest contains an unapproved or malformed target.' );
		}

		$result = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => (string) ( $before['post_content'] ?? '' ),
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( sprintf( 'Could not restore post %d: %s', $post_id, $result->get_error_message() ) );
		}

		if ( ! isset( $before['meta'] ) || ! is_array( $before['meta'] ) ) {
			WP_CLI::error( sprintf( 'Rollback manifest has no metadata snapshot for post %d.', $post_id ) );
		}

		foreach ( $before['meta'] as $meta_key => $meta ) {
			if ( ! in_array( $meta_key, array( '_wp_page_template', '_thumbnail_id', PNS_NEWS_HERO_FOCUS_X_META, PNS_NEWS_HERO_FOCUS_Y_META, PNS_NEWS_HERO_PRIMARY_META, PNS_NEWS_HERO_SECONDARY_META ), true ) || ! is_array( $meta ) ) {
				WP_CLI::error( sprintf( 'Rollback manifest contains an unapproved metadata entry for post %d.', $post_id ) );
			}

			if ( ! empty( $meta['exists'] ) ) {
				update_post_meta( $post_id, $meta_key, $meta['value'] ?? '' );
			} else {
				delete_post_meta( $post_id, $meta_key );
			}
		}

		clean_post_cache( $post_id );
	}

	WP_CLI::success( sprintf( 'Rollback restored %d post(s) from %s.', count( $backup['rows'] ), $backup_path ) );
}
