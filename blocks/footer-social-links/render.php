<?php
/**
 * Render callback for pns/footer-social-links.
 *
 * @package protestsandsuffragettes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$links = pns_theme_get_footer_social_links();

if ( empty( $links ) ) {
	return;
}

$social_link_blocks = array();
$inner_content      = array( '<ul class="wp-block-social-links has-icon-color is-style-logos-only large-social-icons">' );

foreach ( $links as $service => $url ) {
	$social_link_blocks[] = array(
		'blockName'    => 'core/social-link',
		'attrs'        => array(
			'url'     => $url,
			'service' => $service,
		),
		'innerBlocks'  => array(),
		'innerHTML'    => '',
		'innerContent' => array(),
	);
	$inner_content[] = null;
}

// Core Social Links is a static block, so provide its fixed wrapper markup and
// let WordPress render the Core Social Link children from structured block data.
$inner_content[] = '</ul>';

echo do_blocks( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress serializes and renders the Core block tree.
	serialize_block(
		array(
			'blockName'    => 'core/social-links',
			'attrs'        => array(
				'iconColor'      => 'light-green-cyan',
				'iconColorValue' => '#7bdcb5',
				'openInNewTab'   => true,
				'className'      => 'is-style-logos-only large-social-icons',
				'style'          => array(
					'spacing' => array(
						'blockGap' => 'var:preset|spacing|tight',
					),
				),
				'layout'         => array(
					'type'           => 'flex',
					'justifyContent' => 'left',
					'flexWrap'       => 'wrap',
				),
			),
			'innerBlocks'  => $social_link_blocks,
			'innerHTML'    => '<ul class="wp-block-social-links has-icon-color is-style-logos-only large-social-icons"></ul>',
			'innerContent' => $inner_content,
		)
	)
);
