# Render Filter Template Remediation Plan

Plan started on 2026-07-06.

All paths are relative to the project root.

## Purpose

Evaluate and remediate places where the standalone theme is using render-time
PHP filters to create structural or surface ownership that would be more honest
as template, pattern, block, or content-model ownership.

Creating new templates is explicitly allowed. The default preference is not a
bespoke template for every one-off route. Prefer a reusable template or block
contract when the need is generic.

## Problem Statement

The immediate trigger is `/shop/`.

The Shop page is supposed to render as a light readable commerce surface, but it
does not currently use a light-surface page template. It uses the default page
template, then a `render_block` filter adds `pns-light-surface` and
`pns-shop-surface` to the rendered `core/post-content` block.

That makes the runtime output look correct, but it hides the real page contract
from the page template assignment, Site Editor, template tests, and future
agents. It also leaves page-specific CSS tied to `page-id-565` instead of a
portable template or surface class.

The broader question is whether other render filters are also standing in for a
more appropriate source of truth.

## Current Evidence

### Pre-implementation Staleness Check - 2026-07-06

This plan is still current and should be treated as the next structural cleanup
job before layout-stability, typography, or motion polish.

Verified current state:

- The active theme is `protestsandsuffragettes-standalone`.
- `/shop/` is still page ID `565`, slug `shop`, status `publish`, with
  `_wp_page_template=default`.
- The Shop page content still contains the semantic `pns-shop-storefront`
  marker.
- `pns_standalone_add_shop_surface_classes()` is still present and still
  injects `pns-light-surface` and `pns-shop-surface` into the rendered
  `core/post-content` block.
- Shop CSS still targets `.page-id-565 .entry-content`.
- Visual tests still assert the old ownership model: default page template on
  `main`, no `pns-light-surface` on `main`, and injected surface classes on
  `.wp-block-post-content`.
- No matching saved `wp_template` overrides were found for `page`,
  `page-light-surface`, `page-light-surface-no-contact-form`, `page-shop`, or
  `shop` in the live DB check used for this evaluation.

The plan needed two updates:

- Add the current navigation spacing render filter,
  `pns_standalone_apply_navigation_block_support_variables()`, to the filter
  classification table.
- Do not make Herstories CTA or landing-page approval a blocker for this work.
  Herstories render filters should be classified here, but CTA/landing-page
  content decisions should stay with the Herstories queue.

### Dex Backing

This plan is now tracked by Dex:

- Parent: `3q6vu5ah` - Remediate render filters and Shop template ownership.
- Phase 0: `sunbv0tg` - refresh live ownership baseline.
- Phase 1: `1cca9eme` - decide Shop light-surface template shape.
- Phase 2: `ed2kh2mv` - migrate Shop surface ownership.
- Phase 3: `s8w4i05c` - review clear-button source ownership.
- Phase 4: `opx3a8gl` - classify Herstory dynamic filters without CTA cutover.
- Phase 5: `etthzse7` - classify navigation render surgery.
- Phase 6: `xq3esxii` - validate template and filter remediation.

Priority decision: run this parent before `ur561p95` layout stability,
`a7s4gtu0` typography polish, and `apu24hdi` motion polish. The reason is that
Shop's real template/surface contract should be settled before reserving Shop
hydration height, tuning typography, or adding motion over unstable structure.

### Implementation Status - 2026-07-06

Shop remediation is implemented:

- `/shop/` now uses the existing `page-light-surface` template.
- `theme.json` labels `page-light-surface` as
  `PNS - Light Surface Content Only` so the Site Editor contract is explicit:
  header, `main.pns-light-surface`, page content, footer, and no synced
  "Our Shop", connect-social, or contact-form sections.
- The `pns_standalone_add_shop_surface_classes()` render filter was removed.
- Shop-specific CSS now targets the saved-content semantic
  `.pns-shop-storefront` wrapper instead of `.page-id-565 .entry-content`.
- The visual contract now asserts that Shop uses
  `main.pns-template-page-light-surface.pns-light-surface`, that post content
  does not receive injected `pns-light-surface` or `pns-shop-surface`, and that
  the light-surface template does not include `.pns-connect-social` or
  `.pns-contact-form` inside `main`.
- DB backup:
  `docs/jobs/render-filter-db-backups/20260706-before-render-filter-template-remediation.sql`.

Filter classification results:

- Retain the Ecwid `[]` cleanup, EmailOctopus shortcode bridge, slug-to-ref
  resolver, navigation spacing variable bridge, Herstories sparse archive
  hiding, body class, and search query/routing hooks as runtime bridges.
- Remove `pns_standalone_normalize_clear_button_block()`. A live DB check found
  no current non-revision `core/button.clear-button` blocks with generated
  background classes, and the preferred policy is to update source/DB content
  instead of carrying historical editor-output guards.
- Split primary navigation drawer markup, submenu overview injection, CTA
  navigation guardrails, and Herstory entry-navigation dynamic-block conversion
  out of this Shop remediation unless a later task explicitly scopes them.

Validation:

- `php -l app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/block-filters.php`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone exec prettier --check theme.json styles/page-types/shop.css tests/visual/frontend.spec.ts ../../../../../docs/jobs/2026-07-06-render-filter-template-remediation-plan.md ../../../../../docs/jobs/2026-07-06-layout-stability-plan.md`
- `php app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-block-templates.php`
- `git diff --check`
- Rendered `/shop/` probe confirmed `main.pns-template-page-light-surface.pns-light-surface`,
  no injected `pns-shop-surface`, no contact/social sections inside `main`, and
  no `page-id-565` stylesheet contract.
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:shop`
  passed 24/24 after accepted Shop snapshot refresh.
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates`
  passed 22/22.
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual`
  passed the lean landing gate: desktop 35/35 and mobile 10/10.
- Clear-button guard removal validation:
  - current non-revision DB content has no `core/button.clear-button` blocks
    with generated background classes,
  - `php -l` passed for touched PHP includes,
  - focused `herstory cascade contracts` Playwright test passed 3/3 after the
    documented elevated rerun for Chromium sandbox permissions.

- Active theme:
  `protestsandsuffragettes-standalone`.
- `/shop/` page:
  - page ID `565`,
  - slug `shop`,
  - `_wp_page_template=default`,
  - rendered `body` includes `page-template-default`,
  - rendered `<main>` uses `pns-template pns-template-page`,
  - rendered `.wp-block-post-content` receives
    `pns-light-surface pns-shop-surface` from PHP.
- The render filter is
  `pns_standalone_add_shop_surface_classes()` in
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/block-filters.php`.
- The current visual tests intentionally assert that Shop's template is not a
  light-surface template and that only post content receives the light-surface
  classes.
- Existing selectable light-surface templates are registered in
  `theme.json`:
  - `page-light-surface`
  - `page-light-surface-no-contact-form`
- Existing light-surface template files are content-only; they do not include
  the default page template's synced `connect-social` or `contact-form` blocks,
  and they do not include the synced `shop-intro` / "Our Shop" section.
- There is no `templates/page-shop.html`, `templates/shop.html`, or
  shop-specific PHP pattern.
- Shop-specific CSS currently lives in `styles/page-types/shop.css` and targets
  `.page-id-565 .entry-content`.

## Desired Ownership Model

| Concern                          | Preferred owner                                                                                            | Notes                                                                                                                    |
| -------------------------------- | ---------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------ |
| Generic readable page surface    | Generic light-surface page template                                                                        | Should not include contact, connect-social, or shop-intro synced blocks by default.                                      |
| Shop commerce surface            | Generic light-surface template plus Shop content classes, unless evidence proves a shop template is needed | The saved Shop page content already has `pns-shop-storefront`; prefer using that before adding another bespoke template. |
| Ecwid runtime quirks             | Ecwid vendor adapter CSS / plugin integration                                                              | Templates cannot fix third-party runtime text-fill or generated form controls.                                           |
| Page-specific structural classes | Template or saved block markup                                                                             | Avoid `page-id-*` CSS when a semantic template/block class exists.                                                       |
| Conditional data-driven output   | Dynamic block or narrow PHP render callback                                                                | Static templates cannot express post counts or adjacent-post queries honestly.                                           |
| DB-backed refs in templates      | Runtime slug-to-ref resolver                                                                               | Templates should own stable slug intent; PHP may resolve local numeric IDs.                                              |

## In Scope

- Re-evaluate all active render filters in:
  - `inc/block-filters.php`
  - `inc/navigation.php`
  - `inc/template-tags.php`
  - `inc/search.php`
- Decide which filters should remain runtime bridges and which should migrate
  to templates, patterns, blocks, content migration, or plugin ownership.
- For `/shop/`, evaluate these remediation options in order:
  1. Assign the existing `page-light-surface` template if it already satisfies
     the content-only light surface contract.
  2. Create or rename a clearer generic content-only light template, such as
     `page-light-surface-content-only`, if the current template naming or
     markup is ambiguous.
  3. Create a shop-specific template only if Shop needs structural chrome that
     a generic light template plus saved `pns-shop-storefront` content cannot
     provide.
- Remove render-time surface class injection only after template/content
  ownership and tests prove the replacement.
- Replace `.page-id-565` CSS with template, surface, or `pns-shop-storefront`
  selectors.
- Update visual/editor tests so they assert the intended ownership model, not
  the old render-filter behavior.
- Back up any DB-backed page or template mutation before applying it.

## Out Of Scope

- Redesigning the Shop page or Ecwid storefront.
- Rewriting Ecwid, EmailOctopus, Jetpack, or WordPress core block rendering.
- Removing runtime filters that are fixing real vendor/core bugs without a
  replacement.
- Combining this work with unrelated CSS token, typography, footer, navigation,
  or utility cleanup.
- Refreshing visual snapshots before diffs are classified and accepted.

## Guardrails

- Do not replace a narrow runtime bridge with duplicated template content. If a
  synced block is the source of truth, keep the synced block and fix its render
  path.
- Do not make a bespoke page template when a generic light-surface content-only
  template would express the same contract.
- Do not use page IDs as the long-term styling contract unless no semantic
  owner exists.
- Keep vendor adapters isolated. Generic light-surface CSS should expose
  variables; Ecwid CSS can consume those variables with vendor selectors.
- DB mutations require a timestamped backup, a rollback note, and post-apply
  verification.
- Update tests at the same time as ownership changes. A test that encodes the
  old filter behavior must be changed or removed in the same remediation batch.

## Filter Classification

| Filter / hook                                              | Current role                                                                  | Initial classification                        | Preferred remediation                                                                                            |
| ---------------------------------------------------------- | ----------------------------------------------------------------------------- | --------------------------------------------- | ---------------------------------------------------------------------------------------------------------------- |
| `render_block` / `pns_standalone_add_shop_surface_classes` | Adds `pns-light-surface` and `pns-shop-surface` to Shop post content.         | Template/content ownership gap.               | Move to generic light-surface template assignment plus semantic Shop content selectors; then remove the filter.  |
| `render_block` / `pns_standalone_clean_ecwid_store_block`  | Removes stray `[]` from Ecwid store output.                                   | Vendor/runtime bridge.                        | Keep unless Ecwid/plugin-side fix is available. Template cannot solve this.                                      |
| `render_block_core/shortcode` / EmailOctopus bridge        | Executes EmailOctopus shortcode inside Shortcode blocks.                      | Core/plugin render bridge.                    | Keep unless plugin/core behavior changes; do not duplicate contact form markup in templates.                     |
| `render_block` / clear-button normalization                | Removed generated background classes from `clear-button` links.               | Historical editor-output guard.               | Removed after current DB/template check; source/DB content should own `clear-button` attributes.                 |
| `render_block_data` / slug-to-ref resolver                 | Resolves `pnsRefSlug` to local numeric refs for navigation and synced blocks. | Appropriate DB-backed template glue.          | Keep; templates already own stable slug intent.                                                                  |
| Navigation overlay attr stripping                          | Removes unsupported core navigation overlay/icon attrs.                       | Editor drift guard.                           | Keep until unsupported controls are hidden at the editor/block-support layer.                                    |
| CTA navigation normalization                               | Forces banner CTA navigation inline-only.                                     | Template/block variation plus guard.          | Prefer template/variation ownership, keep guard if editor can still save unsupported attrs.                      |
| Herstories sparse section hiding                           | Hides secondary archive grid when there are fewer than two entries.           | Conditional data behavior.                    | Keep as PHP or convert to a dynamic block; static template is not enough.                                        |
| Herstory entry navigation group replacement                | Replaces a marked Group with model-driven previous/back/next controls.        | Dynamic-block candidate.                      | Convert to a dedicated dynamic block or Herstories plugin block when practical.                                  |
| Navigation block support variables                         | Maps Navigation block spacing attrs to theme-owned CSS variables.             | Runtime bridge for supported editor controls. | Keep unless the navigation component gains a cleaner block/control owner; do not remove during Shop remediation. |
| Primary navigation render filter                           | Adds stable class and mobile drawer markup to core Navigation.                | Component/block candidate.                    | Plan a separate navigation component decision; not part of the Shop template fix.                                |
| Navigation submenu overview injection                      | Injects parent overview links into submenu markup.                            | Markup surgery.                               | Prefer navigation content/model ownership if editors can represent the overview item.                            |
| `body_class` Herstories family class                       | Adds cross-template family class.                                             | Acceptable body-level route context.          | Keep unless all affected routes move to explicit templates/classes.                                              |
| Search query and route hooks                               | Limits search content and disables routes via option.                         | Query/routing behavior.                       | Keep; templates do not own query policy.                                                                         |

## Execution Plan

### Phase 0 - Confirm Live Ownership

Goal: refresh the evidence immediately before edits.

- Confirm active theme with WP-CLI:
  - `wp theme list --status=active`
  - `wp option get stylesheet`
  - `wp option get template`
- Confirm `/shop/` page ID, template slug, and saved content.
- Confirm current saved `wp_template` records for:
  - `page`
  - `page-light-surface`
  - `page-light-surface-no-contact-form`
  - any existing Shop-related template if one appears later.
- Render `/shop/`, `/shop/cart`, and a product detail route to verify the
  current class placement and Ecwid variable inheritance.

Acceptance:

- Current source of truth is documented before mutation.
- Any saved template override that could affect the chosen template is known.

### Phase 1 - Decide Generic Light Template Shape

Goal: choose the least bespoke template that accurately expresses the desired
surface.

Evaluate:

- Whether `page-light-surface` is already the correct generic content-only
  template.
- Whether `page-light-surface-no-contact-form` should be retained, renamed, or
  narrowed to Contact-style content-frame needs.
- Whether a new generic template is needed for readable pages that should have:
  - header,
  - `main.pns-light-surface`,
  - post content,
  - no contact form,
  - no connect-social block,
  - no shop-intro / "Our Shop" synced block.
- Whether Shop needs `pns-shop-surface` at all once `main.pns-light-surface`
  owns the surface and saved content keeps `pns-shop-storefront`.

Acceptance:

- A written decision states whether Shop will use an existing generic template,
  a new generic template, or a bespoke Shop template.
- The decision explains why it does or does not need `pns-shop-surface`.

### Phase 2 - Implement Shop Ownership Migration

Goal: move Shop's light-surface contract out of `render_block`.

Likely implementation path, pending Phase 1:

1. Back up page `565` and any affected `wp_template` records.
2. Assign Shop to the chosen light-surface content-only template.
3. Update source template markup if a new or adjusted generic template is
   required.
4. Update saved template rows only if filesystem template changes are not the
   live source.
5. Replace `.page-id-565 .entry-content` rules with semantic selectors such as:
   - `.pns-template-page-light-surface .pns-shop-storefront`
   - `.pns-shop-storefront`
   - `.pns-light-surface .pns-shop-storefront`
6. Remove `pns_standalone_add_shop_surface_classes()`.
7. Update tests that currently assert post-content-only light-surface adoption.

Acceptance:

- `/shop/` renders the chosen light-surface template class on `main`.
- Shop content no longer needs render-time class injection.
- Ecwid storefront, cart, and product detail pages still inherit the surface
  variables needed by `styles/vendor-overrides/ecwid.css`.
- No `page-id-565` selector remains unless a specific unavoidable reason is
  documented.

### Phase 3 - Clear-Button Filter Review

Goal: decide whether `clear-button` normalization belongs in source markup.

- Inventory every rendered and source-authored `clear-button` use.
- Determine whether generated background classes come from:
  - pattern markup,
  - saved block content,
  - block style defaults,
  - editor user choices.
- Migrate controlled pattern/template cases to correct source attributes.
- Remove the filter if current content does not need it; future drift should be
  fixed in source/DB content rather than hidden by a render guard.

Acceptance:

- Controlled source patterns no longer rely on render-time class cleanup.
- Current DB/template evidence is recorded, and no clear-button normalization
  filter remains.

### Phase 4 - Herstory Entry Navigation Dynamic Block Decision

Goal: replace Group-block render replacement with a more honest component if it
is worth the cost.

Do not use this phase to decide unapproved Herstories CTA or landing-page
content. Those are content/product approval blockers and should stay in the
Herstories queue. This phase only classifies whether the render filters are
defensible runtime bridges, dynamic-block candidates, or follow-up work.

- Compare current `pns-herstory-entry-navigation` pattern usage against the
  Herstories plugin/content-model boundary.
- Decide whether the right owner is:
  - a theme dynamic block,
  - a Herstories plugin dynamic block,
  - retained PHP render callback on marked Group until the Herstories plugin
    surface is ready.
- If implementing now, add a dedicated block and migrate templates/patterns to
  that block.

Acceptance:

- Either the Group replacement is removed, or a named follow-up documents why
  the dynamic block migration is deferred.

### Phase 5 - Navigation Render Surgery Decision

Goal: avoid mixing Shop remediation with broader navigation architecture while
still recording the risk.

- Revisit:
  - primary nav mobile drawer appended by render filter,
  - submenu overview link injection,
  - CTA navigation attr normalization.
- Decide whether the next step is a dedicated navigation component/block
  project or a narrower editor-control hardening task.

Acceptance:

- Navigation render filters are classified as retained, narrowed, or split into
  a dedicated plan.
- No navigation behavior changes are bundled into the Shop template migration.

## Test Plan

Run from:

```text
app/public/wp-content/themes/protestsandsuffragettes-standalone
```

Core validation:

- `pnpm compile:css`
- `pnpm lint:css`
- `pnpm format:check` for touched source files
- `php scripts/validate-block-templates.php`
- `git diff --check`

Focused browser coverage:

- `/shop/`
- `/shop/cart`
- representative product detail route
- `/contact-us/`
- `/search/`
- `/`
- `/herstories/mary-barbour/`

Specific assertions to update/add:

- Shop uses the chosen light-surface template class on `main`.
- Shop no longer depends on `pns-light-surface` being injected into
  `.wp-block-post-content`.
- Ecwid text, product titles, prices, minicart, cart recommendations, and
  product descriptions remain readable.
- Existing Contact pages still use their intended no-contact/light template and
  do not gain Shop or synced "Our Shop" content.
- Generic light-surface templates do not render contact, connect-social, or
  shop-intro synced blocks unless page content explicitly includes them.

Run the full Playwright visual suite before closing the remediation parent if
Shop template ownership, light-surface template markup, navigation filters, or
Herstory navigation rendering changes.

## Done When

- Every active render filter has a current classification.
- Shop light-surface ownership lives in a template/content contract, not a
  render filter.
- The chosen generic or bespoke template decision is documented with evidence.
- Any new template is registered in `theme.json`, has filesystem markup, and is
  verified against saved-template overrides.
- Render-filter tests are updated to assert the new source of truth.
- Page-ID Shop CSS is removed or explicitly justified.
- DB backups and rollback notes exist for any page/template assignment changes.
- Focused Shop/Ecwid/Contact/Search validation passes.
- Remaining render filters are either retained for a defensible runtime reason
  or split into named follow-up work.
