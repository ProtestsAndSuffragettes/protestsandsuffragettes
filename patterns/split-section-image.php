<?php
/**
 * Title: PNS - Split Section Image
 * Slug: pns/split-section-image
 * Categories: pns-layout
 * Description: Two-column section with editable copy and a replaceable framed image.
 * Inserter: false
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
<p>Use this split section for focused copy with a related image. Replace this starter text before publishing.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"pns-split-section__cta"} -->
<p class="pns-split-section__cta"><a class="wp-block-button__link wp-element-button" href="#">Find out more</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"pns-split-section__media-column"} -->
<div class="wp-block-column pns-split-section__media-column"><!-- wp:image {"id":1122,"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="/wp-content/uploads/2022/08/Our_work_with_Wikipedia_Image@2x-1024x912.jpeg" alt="" class="wp-image-1122"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
<!-- /wp:pns/split-section -->
