=== Protests and Suffragettes ===
Contributors: Benjamin Rush, Neil Scott
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Block theme for the Protests and Suffragettes site.

== Source Of Truth ==

This theme ships file-backed templates, parts, navigation fixtures, and synced
pattern fixtures, but Local WordPress can also contain fresher DB-backed
records for `wp_template`, `wp_template_part`, `wp_navigation`, and `wp_block`.
Before editing or syncing any of those surfaces, audit the live DB records with
WP-CLI and compare `post_name` plus `post_modified_gmt` against the matching
file fixture.

Stable template references should resolve by `post_name` first. Numeric fallback
IDs are only a local convenience and must fail closed unless the fallback record
still has the expected post type and slug.

== CSS Cascade Policy ==

CSS architecture is documented in `docs/css/README.md`. The short version:
cascade layers are for low-conflict theme defaults, not for every stylesheet.
WordPress core, Global Styles, block supports, editor runtime CSS, and plugins
often output unlayered or inline styles. When the theme must intentionally beat
that output, the owner stylesheet should be imported unlayered with a comment
explaining the pressure.

Avoid solving layer conflicts by duplicating rules in
`components/priority-overrides.css` or by adding `!important`. Prefer moving
the rule to the real owner: the block plugin for project-owned block structure,
the block conflict tail for core/plugin block overrides, the layout owner for
site-shell spacing, or the vendor adapter for third-party runtime CSS.

== Retained Bridges ==

Retained theme bridges must be narrow and source-adjacent. A future developer
should be able to answer three questions beside the rule or filter: what
external pressure exists, why the theme is the current owner, and when the
bridge can be removed.

Current accepted bridge categories are:

* Core block adapters for theme chrome, such as Navigation and Social Links,
  where WordPress emits unlayered or inline block CSS and no project-owned block
  exists.
* Template-part bridges, such as footer Core Columns widths, where saved block
  attributes cannot express the responsive layout contract.
* Vendor adapters for Ecwid, Jetpack, and hosted EmailOctopus output after
  plugin or hosted-service settings have been checked.
* Legacy compatibility bridges, such as the EmailOctopus shortcode render
  filter for old synced-pattern content. `ran-octopus-forms` owns the modern
  contact/newsletter/Turnstile flow; this theme bridge should disappear when
  the legacy hosted embed fixture is migrated or retired.

Project-owned block structure belongs in a sidecar block plugin. For example,
`pns-blocks` owns the split-section layout and `ran-ecwid-shop-teaser` owns the
Ecwid shop teaser runtime; the theme supplies tokens, surfaces, and site
content only.

== Changelog ==

= 0.1.0 =
* Initial standalone migration scaffold

== Copyright ==

Protests and Suffragettes WordPress Theme, (C) 2026 Neil Scott
