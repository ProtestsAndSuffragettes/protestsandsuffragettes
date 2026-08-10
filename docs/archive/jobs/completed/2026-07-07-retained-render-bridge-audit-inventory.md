# Retained Render Bridge Audit Inventory

Created: 2026-07-07.

Related plan: `docs/jobs/2026-07-07-retained-render-bridge-audit-plan.md`

Dex:

- Parent: `4kcr7xve` - Audit retained render bridges after Navigation and
  template cleanup
- Cut 0: `4wk2glck` - Render bridge audit Cut 0 - inventory retained runtime
  bridges

## Scope

This is a scan-only audit. No PHP behavior, templates, saved content, or CSS
were changed.

Files audited:

- `app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/block-filters.php`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/navigation.php`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/search.php`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/template-tags.php`
- `docs/jobs/2026-07-06-render-filter-template-remediation-plan.md`

Excluded:

- Completed Shop/template remediation is treated as baseline evidence and is
  not reopened.
- `h3rs0t00` / Herstories migration remains client-approval pending.
- Herstories bridges are classified here only so they stop appearing as generic
  next work.

## Refreshed Evidence

Active WordPress state:

- Active theme: `protestsandsuffragettes-standalone`
- `stylesheet`: `protestsandsuffragettes-standalone`
- `template`: `protestsandsuffragettes-standalone`
- Active global styles row:
  `wp-global-styles-protestsandsuffragettes-standalone` (`ID 5256`)
- Active navigation rows include:
  - `pns-primary-navigation` (`ID 1035`)
  - `pns-footer-navigation` (`ID 1032`)
  - `pns-banner-cta-navigation` (`ID 5259`)
- DB-backed template, template-part, navigation, synced block, and global-style
  rows are present. Any replacement work that touches template refs, synced
  patterns, or saved content remains DB-backed migration work.

Syntax checks passed for the audited PHP files:

- `inc/block-filters.php`
- `inc/navigation.php`
- `inc/search.php`
- `inc/template-tags.php`

## Summary

| Category                                | Count | Recommendation                                            |
| --------------------------------------- | ----: | --------------------------------------------------------- |
| Third-party adapter                     |     2 | Keep; test through vendor lanes.                          |
| WordPress core output gap               |     5 | Keep; add focused tests where markup surgery matters.     |
| DB-backed ref resolver                  |     3 | Keep; test slug/ref resolution as one contract.           |
| Route/query policy                      |     4 | Keep; test search/body route behavior.                    |
| Editor/frontend honesty risk            |     4 | Keep short term; consider editor-control follow-ups.      |
| Temporary compatibility bridge          |     1 | Keep temporarily; plan DB/content cleanup before removal. |
| Client-approval-blocked Herstories item |     2 | Keep parked; do not advance until client approval.        |

Overall outcome: most retained bridges are legitimate runtime ownership rather
than hidden template mistakes. The highest-value follow-ups are not broad PHP
removal; they are targeted tests, comments, and a few separately scoped
ownership decisions.

## Classification Table

| Function / hook                                                                                             | Owner category                          | Behavior                                                                                                                        | CSS/control health impact                                                 | Risk       | Disposition                                                                          |
| ----------------------------------------------------------------------------------------------------------- | --------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------- | ---------- | ------------------------------------------------------------------------------------ |
| `pns_standalone_clean_ecwid_store_block()` / `render_block`                                                 | Third-party adapter                     | Removes stray Ecwid `[]` artifact from `ecwid/store-block`.                                                                     | Yes, keeps storefront output clean without CSS masking.                   | Low        | Keep; comment is adequate; test through Shop/Ecwid render lane.                      |
| `pns_standalone_ecwid_product_grid_fallback_products()` / `pns_ecwid_product_grid_static_fallback_products` | Third-party adapter                     | Supplies PNS static fallback products when plugin data is empty.                                                                | Indirect; preserves product-card layout when Ecwid/plugin data is absent. | Medium     | Keep; follow up only if product source moves into plugin/admin data.                 |
| `pns_standalone_render_emailoctopus_shortcode_block()` / `render_block_core/shortcode`                      | WordPress core output gap               | Executes EmailOctopus shortcodes saved in core Shortcode blocks.                                                                | Yes, prevents broken synced contact sections and duplicate template work. | Medium     | Keep; existing comment is strong; regression-test contact/synced block rendering.    |
| `pns_standalone_remove_legacy_single_post_back_to_news_button_block()` / `render_block_core/post-content`   | Temporary compatibility bridge          | Removes legacy inline "Back to news" buttons from single post content.                                                          | Yes, avoids duplicate navigation controls.                                | Medium     | Keep temporarily; follow up with DB/content cleanup before removal.                  |
| `pns_standalone_render_archive_featured_image_fallback_block()` / `render_block_core/post-featured-image`   | WordPress core output gap               | Renders site logo for archive/search cards missing featured images.                                                             | Yes, stabilizes card rhythm/media columns.                                | Medium     | Keep; test archive/search no-thumbnail cases.                                        |
| `pns_standalone_render_search_result_post_date_block()` / `render_block_core/post-date`                     | Route/query policy                      | Hides dates for non-post search results.                                                                                        | Minor; prevents misleading page metadata in search UI.                    | Low        | Keep; test mixed post/page search results.                                           |
| `pns_standalone_resolve_template_ref_block_data()` / `render_block_data`                                    | DB-backed ref resolver                  | Resolves `pnsRefSlug` to local numeric `ref` for navigation/reusable blocks.                                                    | Yes, prevents environment-local IDs from becoming template contracts.     | Medium     | Keep; add or retain tests around slug resolution and missing refs.                   |
| `pns_standalone_strip_navigation_overlay_template_block_data()` / `render_block_data`                       | Editor/frontend honesty risk            | Removes unsupported core Navigation overlay/icon attrs before render.                                                           | Yes, prevents controls from implying behavior the theme ignores.          | Medium     | Keep; follow up with editor-control hiding if separately scoped.                     |
| `pns_standalone_normalize_cta_navigation_block_data()` / `render_block_data`                                | Editor/frontend honesty risk            | Forces banner CTA navigation inline-only and strips overlay color attrs.                                                        | Yes, avoids CTA/drawer mismatch.                                          | Medium     | Keep; test CTA nav render; later move to variation/control ownership if needed.      |
| `pns_standalone_register_navigation_metadata_attributes()` / `register_block_type_args`                     | DB-backed ref resolver                  | Registers `pnsRefSlug` metadata for `core/navigation` and `core/block`.                                                         | Yes, supports slug-owned template refs.                                   | Low        | Keep with resolver; test as one contract.                                            |
| `pns_standalone_hide_sparse_herstories_more_section()` / `render_block`                                     | Client-approval-blocked Herstories item | Hides secondary Herstories archive section when fewer than two entries exist.                                                   | Yes, avoids empty archive layout.                                         | Low-medium | Keep; do not advance `h3rs0t00` or client-approval work.                             |
| `pns_standalone_render_herstory_entry_navigation_block()` / `render_block_core/group`                       | Client-approval-blocked Herstories item | Replaces marked Group with model-driven previous/back/next controls.                                                            | Yes, renders navigation markup/classes the CSS expects.                   | High       | Keep as bridge; no client-approval work should be advanced from this audit.          |
| `pns_standalone_render_stable_query_pagination_block()` / `render_block_core/query-pagination`              | WordPress core output gap               | Adds disabled Previous/Next boundary labels and removes unwanted background classes for marked pagination.                      | Yes, stabilizes pagination layout and removes block-style drift.          | Medium     | Keep; test archive pagination first/middle/last pages.                               |
| `pns_standalone_blacklist_blocks()` / `allowed_block_types_all`                                             | Editor/frontend honesty risk            | Removes unavailable core Archives/Calendar blocks from editor inserter.                                                         | Yes, reduces unsupported controls/content surfaces.                       | Low-medium | Keep; test editor block availability if editor QA is in scope.                       |
| `pns_standalone_apply_navigation_block_support_variables()` / `render_block_core/navigation`                | WordPress core output gap               | Maps Navigation `blockGap` attrs into theme CSS variables, including CTA gap var.                                               | Yes, keeps editor spacing controls reflected in frontend CSS.             | Low-medium | Keep; test spacing var output for primary/footer/banner nav.                         |
| `pns_standalone_add_primary_navigation_class()` / `render_block_core/navigation`                            | DB-backed ref resolver                  | Resolves primary nav by `pnsRefSlug` or local `wp_navigation` ID and adds `pns-primary-navigation`.                             | Yes, key CSS hook for nav behavior.                                       | Medium     | Keep; add or retain render smoke around slug/ID resolution.                          |
| Former submenu overview PHP bridge / `render_block_core/navigation-submenu`                                 | Resolved WordPress core output gap      | Parent overview links are now saved `wp_navigation` content instead of render-time injected submenu markup.                     | Yes, behavior remains visible but is now content-owned.                   | Medium     | Removed after saved Navigation content proved stable; validate fixture/DB ownership. |
| `pns_standalone_seed_navigation_refs_on_activation()` / `after_switch_theme`                                | DB-backed ref resolver                  | Seeds missing `wp_navigation` records from theme fixtures while preserving existing editor-owned nav unless explicitly updated. | Indirect; keeps nav refs available for CSS/render hooks.                  | Low-medium | Keep; test manifest parsing and missing-record seed behavior.                        |
| `pns_standalone_apply_editorial_search_post_types()` / `pre_get_posts`                                      | Route/query policy                      | Limits native search to editorial post types when search is enabled.                                                            | No direct CSS impact.                                                     | Low        | Keep; test native search excludes store/product surfaces unless opted in.            |
| `pns_standalone_get_editorial_search_post_types()` / `pns_standalone_editorial_search_post_types`           | Route/query policy                      | Provides a sanitized opt-in list for future editorial CPTs.                                                                     | No direct CSS impact.                                                     | Low        | Keep; existing comment is enough.                                                    |
| `pns_standalone_maybe_disable_search_routes()` / `template_redirect`                                        | Route/query policy                      | Turns `/search/` and `?s=` requests into real 404s when the theme option is off.                                                | No direct CSS impact.                                                     | Medium     | Keep; test enabled/disabled search routes.                                           |
| `pns_standalone_body_classes()` / `body_class` Herstories family                                            | Route/query policy                      | Adds `pns-page-family-herstories` across Herstories landing, child, and single routes.                                          | Yes, provides stable CSS hook across mixed route types.                   | Low-medium | Keep; test representative Herstories page and single entry.                          |
| `pns_standalone_body_classes()` plus `pns_standalone_enable_template_reveal`                                | Editor/frontend honesty risk            | Adds `pns-template-reveal-enabled`, with constant/filter rollback for experimental page-level reveal behavior.                  | Yes, can affect visual behavior globally.                                 | Medium     | Keep with rollback path; add or keep test that filter disables class.                |

## Follow-Up Candidates

These are not authorized implementation tasks yet.

1. Legacy single-post Back-to-News cleanup:
   `pns_standalone_remove_legacy_single_post_back_to_news_button_block()` should
   eventually be retired after a DB/content scan proves legacy inline buttons
   are gone.
2. Navigation editor-control honesty:
   overlay stripping and CTA normalization are acceptable guards, but the editor
   should hide or constrain unsupported controls where possible.
3. Slug/ref resolver tests:
   `pnsRefSlug` is legitimate DB-backed glue, but it should have regression
   coverage for missing refs, primary navigation, banner CTA, footer navigation,
   and synced blocks.
4. Pagination and no-thumbnail tests:
   stable query pagination and archive image fallbacks should be covered through
   first/middle/last page and missing-media cases.
5. Search route tests:
   search enabled/disabled behavior and mixed post/page result metadata should
   be tested without merging Ecwid/store search into editorial search.
6. Template reveal rollback test:
   the `pns_standalone_enable_template_reveal` filter/constant path should be
   covered before the reveal trial is treated as permanent.

## Non-Follow-Ups

- Do not remove retained bridges just to reduce hook count.
- Do not reopen Shop surface ownership; that remediation is already complete.
- Do not advance Herstories validation, namespace cutover, or dynamic-block
  conversion until client approval is received for `h3rs0t00`.
- Do not replace vendor adapters with duplicated template markup.

## Validation Notes

No visual tests were run because this cut made no behavior changes.

Current scan validation:

```bash
php -l app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/block-filters.php
php -l app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/navigation.php
php -l app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/search.php
php -l app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/template-tags.php
```

Future behavior-changing follow-ups should use the narrowest relevant gate:

```bash
php app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-block-templates.php
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:shop
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:ecwid
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:emailoctopus
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
```
