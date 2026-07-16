<?php
/**
 * Title: PNS - Split Section Video
 * Slug: pns/split-section-video
 * Categories: pns-layout
 * Description: Two-column section with editable copy and a framed YouTube video embed.
 * Inserter: true
 *
 * @package protestsandsuffragettes
 */

?>

<!-- wp:pns/split-section {"align":"full","backgroundColor":"neutral-0","layoutVariant":"media-right"} -->
<!-- wp:columns {"align":"full","className":"pns-split-section__columns"} -->
<div class="wp-block-columns alignfull pns-split-section__columns"><!-- wp:column {"className":"pns-split-section__copy-column"} -->
<div class="wp-block-column pns-split-section__copy-column"><!-- wp:group {"className":"pns-split-section__copy"} -->
<div class="wp-block-group pns-split-section__copy"><!-- wp:heading -->
<h2 class="wp-block-heading">Section Heading</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Use this split section for focused copy with a related video. Replace this starter text before publishing.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"pns-split-section__cta"} -->
<p class="pns-split-section__cta"><a class="wp-block-button__link wp-element-button" href="#">Find out more</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"pns-split-section__media-column pns-split-section__media-column--video"} -->
<div class="wp-block-column pns-split-section__media-column pns-split-section__media-column--video"><!-- wp:embed {"url":"https://www.youtube.com/watch?v=AAdwlHwQxws","type":"video","providerNameSlug":"youtube","responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->
<figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
https://www.youtube.com/watch?v=AAdwlHwQxws
</div></figure>
<!-- /wp:embed --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
<!-- /wp:pns/split-section -->
