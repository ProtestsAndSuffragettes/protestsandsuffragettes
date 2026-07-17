<?php
/**
 * Title: PNS - Entry Post Navigation
 * Slug: pns/entry-post-navigation
 * Categories: pns-layout
 * Description: Native previous/back/next navigation for standard posts.
 * Inserter: true
 *
 * @package protestsandsuffragettes
 */

?>

<!-- wp:group {"align":"full","backgroundColor":"neutral-50","className":"pns-section pns-layout pns-entry-navigation","style":{"spacing":{"padding":{"top":"var(--wp--preset--spacing--generous)","bottom":"var(--wp--preset--spacing--generous)"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull pns-section pns-layout pns-entry-navigation has-neutral-50-background-color has-background" style="padding-top:var(--wp--preset--spacing--generous);padding-bottom:var(--wp--preset--spacing--generous)"><!-- wp:group {"className":"pns-content-frame pns-entry-navigation__controls","layout":{"type":"default"}} -->
<div class="wp-block-group pns-content-frame pns-entry-navigation__controls"><!-- wp:post-navigation-link {"type":"previous","label":"Previous","showTitle":false,"linkLabel":true,"arrow":"arrow","className":"pns-entry-navigation__action previous"} /-->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/news/">Back to News</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:post-navigation-link {"type":"next","label":"Next","showTitle":false,"linkLabel":true,"arrow":"arrow","className":"pns-entry-navigation__action next"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
