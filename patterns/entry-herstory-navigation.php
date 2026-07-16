<?php
/**
 * Title: PNS - Entry Herstory Navigation
 * Slug: pns/entry-herstory-navigation
 * Categories: pns-herstories
 * Description: Herstory previous/back/next navigation using the Herstories editorial order.
 * Inserter: true
 *
 * @package protestsandsuffragettes
 */

?>

<!-- wp:group {"align":"full","backgroundColor":"neutral-50","className":"pns-section pns-layout pns-entry-navigation pns-herstory-entry-navigation pns-site-frame-panel","style":{"spacing":{"padding":{"top":"var(--wp--preset--spacing--generous)","bottom":"var(--wp--preset--spacing--generous)"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull pns-section pns-layout pns-entry-navigation pns-herstory-entry-navigation pns-site-frame-panel has-neutral-50-background-color has-background" style="padding-top:var(--wp--preset--spacing--generous);padding-bottom:var(--wp--preset--spacing--generous)"><!-- wp:group {"className":"pns-content-frame pns-entry-navigation__controls","layout":{"type":"default"}} -->
<div class="wp-block-group pns-content-frame pns-entry-navigation__controls"><!-- wp:post-navigation-link {"type":"previous","label":"Previous","showTitle":false,"linkLabel":true,"arrow":"arrow","className":"pns-entry-navigation__action previous"} /-->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/herstories/">Back to Herstories</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:post-navigation-link {"type":"next","label":"Next","showTitle":false,"linkLabel":true,"arrow":"arrow","className":"pns-entry-navigation__action next"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
