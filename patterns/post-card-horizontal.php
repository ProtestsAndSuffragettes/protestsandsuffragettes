<?php
/**
 * Title: PNS - Post Card Horizontal
 * Slug: pns/post-card-horizontal
 * Categories: pns-layout
 * Description: Shared horizontal card for native search result loops.
 * Inserter: false
 *
 * @package protestsandsuffragettes
 */

?>

<!-- wp:group {"className":"pns-search-result pns-post-card pns-post-card--horizontal","layout":{"type":"default"}} -->
<div class="wp-block-group pns-search-result pns-post-card pns-post-card--horizontal"><!-- wp:post-featured-image {"isLink":true,"aspectRatio":"1","scale":"cover","sizeSlug":"square","className":"pns-search-result__thumbnail"} /-->

<!-- wp:group {"className":"pns-search-result__body","layout":{"type":"default"}} -->
<div class="wp-block-group pns-search-result__body"><!-- wp:post-title {"isLink":true,"level":2} /-->

<!-- wp:group {"className":"pns-post-card__meta pns-search-result__meta","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group pns-post-card__meta pns-search-result__meta pns-post-meta"><!-- wp:pns/post-metadata /--></div>
<!-- /wp:group -->

<!-- wp:post-excerpt {"moreText":"Read more"} /-->

<!-- wp:group {"className":"pns-search-result__footer pns-search-result__terms pns-taxonomy-pills","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group pns-search-result__footer pns-search-result__terms pns-taxonomy-pills"><!-- wp:post-terms {"term":"category","separator":"","className":"pns-search-result__term-list pns-taxonomy-pills__list"} /-->

<!-- wp:post-terms {"term":"post_tag","separator":"","className":"pns-search-result__term-list pns-taxonomy-pills__list"} /-->

<!-- wp:post-terms {"term":"herstory_tag","separator":"","className":"pns-search-result__term-list pns-taxonomy-pills__list"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
