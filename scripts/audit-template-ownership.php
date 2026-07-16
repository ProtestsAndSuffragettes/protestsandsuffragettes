<?php
/**
 * Report whether saved standalone templates or template parts shadow code.
 *
 * Run with WordPress loaded:
 * wp eval-file scripts/audit-template-ownership.php [warn|strict] [table|json]
 *
 * This is intentionally read-only. It reports DB ownership drift but never
 * exports, synchronizes, deletes, restores, or updates WordPress records.
 *
 * @package protestsandsuffragettes
 */

$pns_template_ownership_args = isset( $args ) && is_array( $args ) ? $args : array();
$pns_template_ownership_mode = pns_template_ownership_parse_mode( $pns_template_ownership_args );
$pns_template_ownership_report = pns_template_ownership_build_report();

if ( 'json' === $pns_template_ownership_mode['format'] ) {
	echo wp_json_encode( $pns_template_ownership_report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";
} else {
	pns_template_ownership_render_table( $pns_template_ownership_report );
}

if ( ! empty( $pns_template_ownership_report['issues'] ) ) {
	$message = sprintf(
		'Template ownership audit found %d actionable issue(s). No WordPress data was changed.',
		count( $pns_template_ownership_report['issues'] )
	);

	if ( 'strict' === $pns_template_ownership_mode['policy'] ) {
		WP_CLI::error( $message );
	}

	WP_CLI::warning( $message );
} elseif ( 'json' !== $pns_template_ownership_mode['format'] ) {
	WP_CLI::success( 'Template ownership audit passed. No WordPress data was changed.' );
}

/**
 * @param string[] $script_args Positional arguments supplied to wp eval-file.
 * @return array{policy:string,format:string}
 */
function pns_template_ownership_parse_mode( array $script_args ) {
	$policy = 'warn';
	$format = 'table';

	foreach ( $script_args as $argument ) {
		switch ( $argument ) {
			case 'warn':
				$policy = 'warn';
				break;
			case 'strict':
			case 'fail':
				$policy = 'strict';
				break;
			case 'table':
				$format = 'table';
				break;
			case 'json':
				$format = 'json';
				break;
			default:
				WP_CLI::error(
					sprintf(
						'Unknown argument "%s". Use warn or strict, optionally followed by table or json.',
						$argument
					)
				);
		}
	}

	return array(
		'policy' => $policy,
		'format' => $format,
	);
}

/**
 * @return array<string,mixed>
 */
function pns_template_ownership_build_report() {
	$theme_slug = get_stylesheet();

	if ( 'protestsandsuffragettes' !== $theme_slug ) {
		WP_CLI::error(
			sprintf(
				'Active stylesheet is %s; this guard only applies to protestsandsuffragettes.',
				$theme_slug
			)
		);
	}

	$source_records = pns_template_ownership_collect_source_records( get_stylesheet_directory() );
	$saved_records = pns_template_ownership_collect_saved_template_records( $theme_slug );
	$saved_by_key  = array();
	$issues        = array();
	$code_rows     = array();

	foreach ( $saved_records as $saved_record ) {
		$key = $saved_record['post_type'] . ':' . $saved_record['slug'];

		if ( ! isset( $saved_by_key[ $key ] ) ) {
			$saved_by_key[ $key ] = array();
		}

		$saved_by_key[ $key ][] = $saved_record;
	}

	foreach ( $source_records as $key => $source_record ) {
		$runtime_template = get_block_template(
			$theme_slug . '//' . $source_record['slug'],
			$source_record['post_type']
		);
		$runtime_source = $runtime_template ? $runtime_template->source : null;
		$has_theme_file = $runtime_template ? (bool) $runtime_template->has_theme_file : false;
		$saved          = $saved_by_key[ $key ] ?? array();

		$code_rows[] = array(
			'post_type'       => $source_record['post_type'],
			'slug'            => $source_record['slug'],
			'source_path'     => $source_record['source_path'],
			'file_sha256'     => $source_record['file_sha256'],
			'saved_record_ids' => wp_list_pluck( $saved, 'id' ),
			'runtime_source'  => $runtime_source,
			'has_theme_file'  => $has_theme_file,
			'status'          => empty( $saved ) && 'theme' === $runtime_source && $has_theme_file ? 'file_authoritative' : 'review',
		);

		if ( ! empty( $saved ) ) {
			foreach ( $saved as $saved_record ) {
				$issues[] = array(
					'kind'        => 'code_owned_db_override',
					'post_type'   => $saved_record['post_type'],
					'id'          => $saved_record['id'],
					'slug'        => $saved_record['slug'],
					'post_status' => $saved_record['post_status'],
					'source_path' => $source_record['source_path'],
					'file_sha256' => $source_record['file_sha256'],
					'db_sha256'   => $saved_record['db_sha256'],
					'relation'    => $source_record['file_sha256'] === $saved_record['db_sha256'] ? 'matching_shadow' : 'diverged_override',
					'message'     => sprintf(
						'%s #%d (%s) shadows code-owned %s.',
						$saved_record['post_type'],
						$saved_record['id'],
						$saved_record['slug'],
						$source_record['source_path']
					),
				);
			}
		}

		if ( empty( $saved ) && ( 'theme' !== $runtime_source || ! $has_theme_file ) ) {
			$issues[] = array(
				'kind'        => 'runtime_template_resolution',
				'post_type'   => $source_record['post_type'],
				'id'          => null,
				'slug'        => $source_record['slug'],
				'post_status' => null,
				'source_path' => $source_record['source_path'],
				'file_sha256' => $source_record['file_sha256'],
				'db_sha256'   => null,
				'relation'    => 'unexpected_runtime_source',
				'message'     => sprintf(
					'%s did not resolve from its theme file.',
					$source_record['source_path']
				),
			);
		}
	}

	foreach ( $saved_records as $saved_record ) {
		$key = $saved_record['post_type'] . ':' . $saved_record['slug'];

		if ( isset( $source_records[ $key ] ) ) {
			continue;
		}

		$issues[] = array(
			'kind'        => 'unclassified_structural_record',
			'post_type'   => $saved_record['post_type'],
			'id'          => $saved_record['id'],
			'slug'        => $saved_record['slug'],
			'post_status' => $saved_record['post_status'],
			'source_path' => null,
			'file_sha256' => null,
			'db_sha256'   => $saved_record['db_sha256'],
			'relation'    => 'no_matching_theme_file',
			'message'     => sprintf(
				'%s #%d (%s) has no matching standalone source file.',
				$saved_record['post_type'],
				$saved_record['id'],
				$saved_record['slug']
			),
		);
	}

	$administrator_data = pns_template_ownership_administrator_data();

	foreach ( $administrator_data['navigation'] as $navigation ) {
		if ( 'missing' === $navigation['state'] ) {
			$issues[] = array(
				'kind'        => 'missing_administrator_navigation',
				'post_type'   => 'wp_navigation',
				'id'          => null,
				'slug'        => $navigation['slug'],
				'post_status' => null,
				'source_path' => $navigation['recovery_fixture'],
				'file_sha256' => null,
				'db_sha256'   => null,
				'relation'    => 'missing_required_data',
				'message'     => sprintf( 'Administrator navigation %s is missing.', $navigation['slug'] ),
			);
		}
	}

	$global_styles = pns_template_ownership_global_styles( $theme_slug );

	if ( 'user_payload' === $global_styles['state'] ) {
		$issues[] = array(
			'kind'        => 'global_styles_user_payload',
			'post_type'   => 'wp_global_styles',
			'id'          => $global_styles['id'],
			'slug'        => $global_styles['slug'],
			'post_status' => $global_styles['post_status'],
			'source_path' => 'theme.json',
			'file_sha256' => null,
			'db_sha256'   => $global_styles['db_sha256'],
			'relation'    => 'review_required',
			'message'     => 'Active global styles contain a user settings/styles payload and require review.',
		);
	}

	return array(
		'schema_version' => 1,
		'generated_at_gmt' => gmdate( 'c' ),
		'active_theme' => $theme_slug,
		'summary' => array(
			'code_source_files'       => count( $source_records ),
			'saved_template_records'  => count( $saved_records ),
			'actionable_issue_count'   => count( $issues ),
			'file_authoritative_count' => count(
				array_filter(
					$code_rows,
					static function ( $row ) {
						return 'file_authoritative' === $row['status'];
					}
				)
			),
		),
		'code_templates_and_parts' => $code_rows,
		'issues' => $issues,
		'administrator_data' => $administrator_data,
		'managed_fixtures' => pns_template_ownership_managed_fixtures(),
		'global_styles' => $global_styles,
		'editor_data' => array(
			'state' => 'excluded',
			'description' => 'Post, Page, Herstory, campaign, and other ordinary editor content is not scanned or mutated by this guard.',
		),
		'safety' => array(
			'read_only' => true,
			'does_not_sync' => true,
			'does_not_write_or_delete' => true,
		),
	);
}

/**
 * @param string $theme_directory
 * @return array<string,array<string,string>>
 */
function pns_template_ownership_collect_source_records( $theme_directory ) {
	$directories = array(
		'wp_template' => 'templates',
		'wp_template_part' => 'parts',
	);
	$records = array();

	foreach ( $directories as $post_type => $directory ) {
		foreach ( glob( $theme_directory . '/' . $directory . '/*.html' ) ?: array() as $file_path ) {
			$content = file_get_contents( $file_path );

			if ( false === $content ) {
				WP_CLI::error( sprintf( 'Could not read source file %s.', $file_path ) );
			}

			$slug = basename( $file_path, '.html' );
			$key = $post_type . ':' . $slug;
			$records[ $key ] = array(
				'post_type' => $post_type,
				'slug' => $slug,
				'source_path' => $directory . '/' . $slug . '.html',
				'file_sha256' => hash( 'sha256', rtrim( $content ) ),
			);
		}
	}

	ksort( $records );

	return $records;
}

/**
 * @param string $theme_slug
 * @return array<int,array<string,mixed>>
 */
function pns_template_ownership_collect_saved_template_records( $theme_slug ) {
	$records = array();
	$statuses = get_post_stati( array(), 'names' );

	foreach ( array( 'wp_template', 'wp_template_part' ) as $post_type ) {
		$posts = get_posts(
			array(
				'post_type' => $post_type,
				'post_status' => $statuses,
				'posts_per_page' => -1,
				'orderby' => 'ID',
				'order' => 'ASC',
				'tax_query' => array(
					array(
						'taxonomy' => 'wp_theme',
						'field' => 'slug',
						'terms' => array( $theme_slug ),
					),
				),
			)
		);

		foreach ( $posts as $post ) {
			$records[] = array(
				'id' => (int) $post->ID,
				'post_type' => $post->post_type,
				'slug' => $post->post_name,
				'post_status' => $post->post_status,
				'db_sha256' => hash( 'sha256', rtrim( $post->post_content ) ),
			);
		}
	}

	return $records;
}

/**
 * @return array<string,mixed>
 */
function pns_template_ownership_administrator_data() {
	$navigation = array(
		array(
			'slug' => 'pns-primary-navigation',
			'recovery_fixture' => 'navigation/primary.html',
		),
		array(
			'slug' => 'pns-banner-cta-navigation',
			'recovery_fixture' => 'navigation/banner-cta.html',
		),
		array(
			'slug' => 'pns-footer-navigation',
			'recovery_fixture' => 'navigation/footer.html',
		),
	);

	foreach ( $navigation as &$entry ) {
		$post = pns_template_ownership_find_post_by_slug( $entry['slug'], 'wp_navigation' );
		$entry['state'] = $post ? 'saved' : 'missing';
		$entry['id'] = $post ? (int) $post->ID : null;
		$entry['post_status'] = $post ? $post->post_status : null;
		$entry['db_sha256'] = $post ? hash( 'sha256', rtrim( $post->post_content ) ) : null;
	}
	unset( $entry );

	$footer_social_links = get_option( 'pns_footer_social_links', null );

	return array(
		'navigation' => $navigation,
		'footer_social_links' => array(
			'state' => null === $footer_social_links ? 'defaults' : ( empty( $footer_social_links ) ? 'empty' : 'saved' ),
			'value_sha256' => hash( 'sha256', serialize( $footer_social_links ) ),
			'control_path' => 'Appearance > Footer Social Links',
		),
	);
}

/**
 * @return array<int,array<string,mixed>>
 */
function pns_template_ownership_managed_fixtures() {
	$fixtures = array(
		'contact-form',
		'connect-social',
		'read-all-about-it',
		'read-all-about-it-workshops',
		'shop-intro',
	);
	$results = array();

	foreach ( $fixtures as $slug ) {
		$post = pns_template_ownership_find_post_by_slug( $slug, 'wp_block' );
		$results[] = array(
			'slug' => $slug,
			'state' => $post ? 'saved_review_only' : 'missing_review_only',
			'id' => $post ? (int) $post->ID : null,
			'post_status' => $post ? $post->post_status : null,
			'db_sha256' => $post ? hash( 'sha256', rtrim( $post->post_content ) ) : null,
		);
	}

	return $results;
}

/**
 * @param string $theme_slug
 * @return array<string,mixed>
 */
function pns_template_ownership_global_styles( $theme_slug ) {
	$posts = get_posts(
		array(
			'post_type' => 'wp_global_styles',
			'post_status' => get_post_stati( array(), 'names' ),
			'posts_per_page' => -1,
			'tax_query' => array(
				array(
					'taxonomy' => 'wp_theme',
					'field' => 'slug',
					'terms' => array( $theme_slug ),
				),
			),
		)
	);
	$post = $posts[0] ?? null;

	if ( ! $post ) {
		return array(
			'state' => 'absent',
			'id' => null,
			'slug' => null,
			'post_status' => null,
			'db_sha256' => null,
		);
	}

	$decoded = json_decode( $post->post_content, true );
	$is_neutral = is_array( $decoded )
		&& 2 === count( $decoded )
		&& 3 === (int) ( $decoded['version'] ?? 0 )
		&& true === ( $decoded['isGlobalStylesUserThemeJSON'] ?? false );

	return array(
		'state' => $is_neutral ? 'neutral_generated_record' : 'user_payload',
		'id' => (int) $post->ID,
		'slug' => $post->post_name,
		'post_status' => $post->post_status,
		'db_sha256' => hash( 'sha256', rtrim( $post->post_content ) ),
	);
}

/**
 * @param string $slug
 * @param string $post_type
 * @return WP_Post|null
 */
function pns_template_ownership_find_post_by_slug( $slug, $post_type ) {
	$posts = get_posts(
		array(
			'post_type' => $post_type,
			'post_status' => get_post_stati( array(), 'names' ),
			'name' => $slug,
			'posts_per_page' => 1,
		)
	);

	return $posts[0] ?? null;
}

/**
 * @param array<string,mixed> $report
 * @return void
 */
function pns_template_ownership_render_table( $report ) {
	echo "PNS standalone template ownership audit\n";
	echo 'Theme: ' . $report['active_theme'] . "\n";
	echo sprintf(
		"Code source files: %d | Saved template/part records: %d | Actionable issues: %d\n\n",
		$report['summary']['code_source_files'],
		$report['summary']['saved_template_records'],
		$report['summary']['actionable_issue_count']
	);

	echo "CODE TEMPLATES AND PARTS\n";
	foreach ( $report['code_templates_and_parts'] as $row ) {
		echo sprintf(
			'[%s] %s %s — %s%s' . "\n",
			'file_authoritative' === $row['status'] ? 'OK' : 'REVIEW',
			$row['post_type'],
			$row['slug'],
			$row['source_path'],
			empty( $row['saved_record_ids'] ) ? '' : ' (saved IDs: ' . implode( ',', $row['saved_record_ids'] ) . ')'
		);
	}

	echo "\nADMINISTRATOR DATA (informational; never compared or overwritten)\n";
	foreach ( $report['administrator_data']['navigation'] as $navigation ) {
		echo sprintf(
			'[%s] wp_navigation %s%s' . "\n",
			'saved' === $navigation['state'] ? 'OK' : 'REVIEW',
			$navigation['slug'],
			null === $navigation['id'] ? '' : ' (#' . $navigation['id'] . ')'
		);
	}
	echo sprintf(
		'[INFO] Footer social links: %s (%s)' . "\n",
		$report['administrator_data']['footer_social_links']['state'],
		$report['administrator_data']['footer_social_links']['control_path']
	);

	echo "\nMANAGED FIXTURES (review-only; never promoted, deleted, or overwritten)\n";
	foreach ( $report['managed_fixtures'] as $fixture ) {
		echo sprintf(
			'[INFO] wp_block %s: %s%s' . "\n",
			$fixture['slug'],
			$fixture['state'],
			null === $fixture['id'] ? '' : ' (#' . $fixture['id'] . ')'
		);
	}

	echo sprintf(
		"\nGLOBAL STYLES (review-only unless a user payload exists): %s\n",
		$report['global_styles']['state']
	);
	echo "EDITOR DATA: excluded — ordinary post/page/Herstory/campaign content is never scanned or mutated.\n";

	if ( empty( $report['issues'] ) ) {
		return;
	}

	echo "\nACTION REQUIRED\n";
	foreach ( $report['issues'] as $issue ) {
		echo '[WARN] ' . $issue['message'] . "\n";
	}
}
