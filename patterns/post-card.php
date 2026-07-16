<?php
/**
 * Title: PNS - Post Card
 * Slug: pns/post-card
 * Categories: pns-layout
 * Description: Shared vertical card for post and Herstory query grids.
 * Inserter: false
 *
 * @package protestsandsuffragettes
 */

?>

<!-- wp:group {"className":"pns-archive-card pns-post-card","style":{"spacing":{"padding":{"top":"var(\u002d\u002dwp\u002d\u002dpreset\u002d\u002dspacing\u002d\u002dspacious)","right":"0px","bottom":"var(\u002d\u002dwp\u002d\u002dpreset\u002d\u002dspacing\u002d\u002dspacious)","left":"0px"},"blockGap":"var(\u002d\u002dwp\u002d\u002dpreset\u002d\u002dspacing\u002d\u002dregular)"}},"backgroundColor":"neutral-0","textColor":"neutral-800","layout":{"type":"constrained"}} -->
<div class="wp-block-group pns-archive-card pns-post-card has-neutral-800-color has-neutral-0-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--spacious);padding-right:0px;padding-bottom:var(--wp--preset--spacing--spacious);padding-left:0px"><!-- wp:post-featured-image {"isLink":true,"aspectRatio":"auto","height":"200px","scale":"cover","sizeSlug":"card","style":{"spacing":{"margin":{"top":"-25px","bottom":"0px","left":"-25px","right":"-25px"}}}} /-->

<!-- wp:post-title {"level":3,"isLink":true,"style":{"spacing":{"padding":{"right":"25px","left":"25px"}}},"fontSize":"text-lead"} /-->

<!-- wp:group {"className":"pns-post-card__meta","style":{"spacing":{"padding":{"right":"25px","left":"25px"}}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group pns-post-card__meta pns-post-meta" style="padding-right:25px;padding-left:25px"><!-- wp:pns/post-metadata /--></div>
<!-- /wp:group -->

<!-- wp:post-excerpt {"moreText":"Read more","style":{"spacing":{"padding":{"right":"25px","left":"25px"}}}} /-->

<!-- wp:group {"className":"pns-post-card__footer pns-taxonomy-pills","style":{"spacing":{"padding":{"right":"25px","left":"25px"}}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group pns-post-card__footer pns-taxonomy-pills" style="padding-right:25px;padding-left:25px"><!-- wp:post-terms {"term":"category","separator":"","className":"pns-post-card__term-list pns-taxonomy-pills__list"} /-->

<!-- wp:post-terms {"term":"post_tag","separator":"","className":"pns-post-card__term-list pns-taxonomy-pills__list"} /-->

<!-- wp:post-terms {"term":"herstory_tag","separator":"","className":"pns-post-card__term-list pns-taxonomy-pills__list"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
