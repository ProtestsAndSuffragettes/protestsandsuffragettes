# Ownership and integration boundaries

This page identifies the responsible system for the functionality that makes
up the PNS site. It is an operational guide for maintainers: use it before
deciding where to make a change, not as a substitute for inspecting the live
WordPress state.

## Boundary map

| System              | Owns                                                                                                                                              | Does not own                                                                                          |
| ------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------- |
| This theme          | Visual system, block-theme templates and parts on disk, patterns, editor presentation, theme assets, and narrow presentation/integration bridges. | Content model registration or reusable application features that must survive a future theme change.  |
| PNS/RAN plugins     | Custom block registration and feature behaviour, including PNS layouts, Herstories content, enhanced covers, shop teasers, and form integrations. | The site-wide PNS visual composition supplied by the theme.                                           |
| Third-party plugins | Their own service integrations, editor blocks, and runtime behaviour.                                                                             | PNS-specific layout, styling, or content-model policy, except where their documented API provides it. |
| WordPress database  | Published and draft content, media, Site Editor records, navigation records, synced patterns, global styles, options, and plugin configuration.   | A versioned replacement for theme/plugin source files.                                                |

The theme is intentionally a presentation and composition layer. A future
theme should be able to keep the content, custom blocks, and service
integrations supplied by the plugins. Conversely, a plugin feature should not
assume this theme's markup, CSS classes, or template arrangement unless that
contract is documented at the integration point.

## Theme-owned surface

The theme owns the block-theme files in `templates/`, `parts/` where present,
`patterns/`, `theme.json`, and the frontend/editor asset pipeline under
`styles/` and `scripts/`. Its PHP modules in `inc/` add site-specific
composition and carefully scoped compatibility code.

Examples of theme-owned integration work include:

- loading Ecwid's browser storefront runtime only on store surfaces or where
  native Ecwid content needs it;
- a render cleanup for Ecwid's `ecwid/store-block` output;
- a temporary renderer for legacy EmailOctopus shortcodes held in theme-owned
  synced-pattern content;
- navigation presentation, the desktop search drawer, and seed fixtures for
  default native Navigation records;
- the Herstories editor scaffold and theme-specific archive/entry composition;
  and
- a Site Health test and administrator warning for the PNS dependency bundle.

These bridges are deliberately narrow. Before widening a filter or adding one,
check whether the change belongs in the owning plugin instead. Retire a bridge
when its upstream plugin or a portable block/template path can own the
behaviour cleanly.

## Project-owned plugin contracts

The runtime dependency contract is defined in
[`inc/dependencies.php`](../../inc/dependencies.php). It is enforced through
the WordPress Site Health screen and an administrator notice when a required
plugin is missing or inactive.

| Dependency                                                                                               | Required | Responsibility at this site                                                                                                           |
| -------------------------------------------------------------------------------------------------------- | -------- | ------------------------------------------------------------------------------------------------------------------------------------- |
| PNS Blocks (`pns-blocks/pns-blocks.php`)                                                                 | Yes      | PNS site block layouts, including split sections.                                                                                     |
| PNS Herstories (`pns-herstories/pns-herstories.php`)                                                     | Yes      | Herstories content and presentation. The plugin owns the content type; the theme supplies its site-specific scaffold and composition. |
| RAN Enhanced Cover (`ran-enhanced-cover/ran-enhanced-cover.php`)                                         | Yes      | Enhanced Cover blocks used in PNS content.                                                                                            |
| RAN Ecwid Shop Teaser (`ran-ecwid-shop-teaser/ran-ecwid-shop-teaser.php`)                                | Yes      | PNS shop teaser block.                                                                                                                |
| RAN EmailOctopus for Jetpack Forms (`ran-emailoctopus-jetpack-forms/ran-emailoctopus-jetpack-forms.php`) | Yes      | EmailOctopus subscriptions from selected Jetpack forms.                                                                               |
| RAN Turnstile for Jetpack Forms (`ran-turnstile-for-jetpack-forms/ran-turnstile-for-jetpack-forms.php`)  | Yes      | Cloudflare Turnstile protection for selected Jetpack forms.                                                                           |
| Jetpack (`jetpack/jetpack.php`)                                                                          | Yes      | Consent controls and slideshow content used by the site.                                                                              |
| Ecwid by Lightspeed Ecommerce Shopping Cart (`ecwid-shopping-cart/ecwid-shopping-cart.php`)              | Yes      | Native PNS shop integration.                                                                                                          |
| EmailOctopus (`emailoctopus/emailoctopus.php`)                                                           | Yes      | Legacy hosted newsletter embeds that remain in site content.                                                                          |
| Jetpack Boost (`jetpack-boost/jetpack-boost.php`)                                                        | No       | Optional performance tooling. The theme contains a route-specific Ecwid compatibility adjustment when it is active.                   |

“Required” here means required by the current PNS site bundle, not by every
installation of the named plugin. Check Site Health after any plugin update,
activation change, or migration. Do not remove a required plugin merely because
one page appears to work: saved content, templates, or another route can still
depend on its blocks or shortcodes.

## Third-party integration rule

Treat third-party plugin output as vendor-owned. The theme may use the public
block, shortcode, hook, or asset API to make the current site coherent, but it
must not become a fork of that plugin. Record each exceptional theme bridge
beside its callback, including its removal condition.

Current examples worth checking after upstream updates are Ecwid asset
scoping/cleanup, the legacy EmailOctopus shortcode renderer, Jetpack slideshow
styling, and the Jetpack Boost adjustment on native Ecwid routes. Validate the
affected route or editor surface after changing either side of these
boundaries.

## Files, database, and editor ownership

Block themes have two valid sources of state:

- Versioned files provide the theme's defaults: templates, patterns, navigation
  fixtures, synced-pattern fixtures, theme configuration, PHP, CSS, and
  scripts.
- The WordPress database stores the live editable state: posts and pages,
  media, `wp_template`, `wp_template_part`, `wp_navigation`, `wp_block`,
  `wp_global_styles`, options, plugin settings, and editor changes.

A saved Site Editor record can override a same-named file. A filesystem change
therefore does not prove that the rendered site has changed. Before editing or
deploying a template, part, navigation, or synced pattern, inspect both the
file-backed definition and the corresponding live database record. Preserve the
current source of truth; do not overwrite an editor-owned record merely to make
it resemble a file.

Theme activation seeds missing navigation and synced-pattern records from the
theme fixtures. The normal seed path keeps existing records rather than
overwriting them. The **Appearance → PNS Theme Setup** screen exposes the
lifecycle choices, including whether theme-owned seeded records are kept or
cleaned up on a theme switch. Those choices affect database content and must be
reviewed deliberately during a handoff or future-theme migration.

## Handoff and change-routing rules

Use this routing order when taking over work:

1. For a layout, typography, template, pattern, or editor-presentation change,
   start in this theme, then verify the database has not overridden the target.
2. For a custom block's controls, render behaviour, content model, or
   integration that should travel to a future theme, start in its owning PNS or
   RAN plugin.
3. For behaviour supplied by Ecwid, Jetpack, EmailOctopus, or another vendor,
   start with that plugin's supported configuration/API. Add a theme bridge only
   when the concern is genuinely PNS presentation and the bridge has a clear
   removal gate.
4. For content, navigation, global styles, synced patterns, template changes
   made in Site Editor, media, or options, treat the database as production
   state. Make a backup and capture the before/after state; source control alone
   cannot roll it back.

For every non-trivial handoff, provide the receiving developer with the active
plugin list, Site Health result, current template/part/navigation/synced-pattern
ownership audit, and the relevant route-level visual checks. This gives them a
safe starting point without treating a local database snapshot or a particular
hosting/deployment configuration as portable theme documentation.
