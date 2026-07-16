# Ecwid Product Grid Genericization Plan

Created: 2026-07-04

Related plan: `docs/jobs/2026-06-24-pns-custom-blocks-plugin-plan.md`

## Goal

Genericize `pns/ecwid-product-grid` while it remains inside
`app/public/wp-content/plugins/pns-blocks/`, so a later move into a standalone
plugin is mostly packaging work rather than a behavioral rewrite.

The intended end state is a block that:

- Works without the PNS theme.
- Ships its own baseline frontend and editor styles.
- Keeps the serialized block name `pns/ecwid-product-grid` for content
  compatibility.
- Contains no PNS product names, image URLs, or site copy in the generic runtime.
- Uses filters or documented extension points for site-specific fallback
  products and visual tuning.
- Can later move to a different Git organization without changing saved block
  markup.

## Non-Goals

- Do not extract the block into a new plugin directory in this phase.
- Do not rename the saved block from `pns/ecwid-product-grid`.
- Do not build new admin settings pages.
- Do not make the block dependent on a specific theme, Ecwid widget block, or
  synced pattern.
- Do not migrate production pages beyond using page `3228` as a comparison and
  verification fixture.

## Current Local State

- Runtime block registration already prefers compiled metadata under
  `app/public/wp-content/plugins/pns-blocks/build/blocks`.
- `blocks/commerce/ecwid-product-grid/block.json` already declares:
  `editorScript`, `editorStyle`, `style`, `render`, `example`, align support,
  and spacing support.
- `EcwidProductGrid::render()` already uses `get_block_wrapper_attributes()`.
- The block already has a baseline `style.css` and compiled
  `style-index.css`.
- Theme CSS still contains `.pns-ecwid-product-grid` rules in
  `styles/components/shop-intro.css`; those are the main remaining theme
  coupling risk.
- The generic runtime still needs an audit for PNS-specific fallback products,
  product images, copy, defaults, and documentation.

## External Standards To Align With

WordPress Block Plugin guidelines:

- Block plugins should be single-purpose and contain a minimum of supporting
  code.
- Block plugins must include a valid `block.json`.
- A Block Directory block plugin must work independently and should work
  seamlessly without requiring a theme, another plugin, manual connection step,
  login, or activation key.
- Server-side code should be kept to a minimum.

Reference:
<https://developer.wordpress.org/plugins/wordpress-org/block-specific-plugin-guidelines/>

WordPress block metadata guidance:

- `block.json` is the canonical metadata source for server and client block
  registration.
- Asset fields such as `style`, `editorStyle`, and render metadata allow
  WordPress to register and enqueue block assets consistently.
- Assets declared through block metadata can be optimized and loaded only when
  needed.

Reference:
<https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/>

WordPress block supports guidance:

- Prefer native block supports where they cover the control model because they
  create a consistent editor experience and integrate with the style engine.

Reference:
<https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/>

## Dex Tracking

Dex task state for this rollout is stored under the standalone theme root:

```text
app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex/tasks.jsonl
```

Use the tracker with the storage path explicitly:

```bash
dex --storage-path /Users/anachronistic/Local\ Sites/pns-stageing/app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex list --all
```

Parent task:

```text
fyuxgot2 - Genericize Ecwid product grid block before extraction
```

Implementation tasks:

| Phase | Dex ID     | Task                                               |
| ----- | ---------- | -------------------------------------------------- |
| 1     | `ezuq6yck` | Genericize Ecwid block identity and language       |
| 2     | `dlo9j2p4` | Decouple runtime fallback products                 |
| 3     | `js7d1qab` | Make Ecwid grid CSS plugin-owned                   |
| 4     | `9k8rh54f` | Reconcile native supports and editor controls      |
| 5     | `kqriq7ee` | Add filterable Ecwid store and credential boundary |
| 6     | `nkpjxx0j` | Add public-plugin hygiene docs                     |
| 7     | `5e6hco3p` | Validate generic Ecwid block behavior              |

## Phase 1: Generic Identity And Language

Objective: remove PNS-specific user-facing assumptions without changing saved
content.

Steps:

1. Audit `block.json`, editor JS, PHP messages, REST responses, README text, and
   comments for user-facing PNS branding.
2. Change editor-facing title/description/copy from "PNS Ecwid Product Grid" to
   a generic label such as "Ecwid Product Grid" or "Product Grid for Ecwid".
3. Keep `name: "pns/ecwid-product-grid"` unchanged and document that this is the
   stable serialization namespace.
4. Decide whether the future standalone plugin textdomain should become
   `pns-ecwid-product-grid`. While still inside `pns-blocks`, avoid churn unless
   translation tooling is updated at the same time.
5. Add a short developer note explaining that the block is being genericized in
   place before extraction.

Acceptance criteria:

- No editor UI text says "PNS" unless it is explaining the stable namespace.
- Existing page `3228` and any saved `pns/ecwid-product-grid` blocks still
  render.

## Phase 2: Runtime Fallback Decoupling

Objective: remove PNS product fallback data from generic block behavior.

Decision:

The hardcoded PNS static fallback must be removed from the generic plugin
runtime. It is acceptable for the PNS site to reintroduce those three cards via
a site-owned filter, but the plugin default must not contain PNS product names,
image URLs, prices, or `/shop/` links.

Steps:

1. Locate all hardcoded PNS product names, prices, images, and `/shop/` fallback
   URLs in the Ecwid repository and editor preview code.
2. Replace frontend static fallback behavior with one of these generic states:
   no products, last-good cache only, or filter-provided fallback products.
3. Add documented filters:
    - `pns_ecwid_product_grid_static_fallback_products`
    - `pns_ecwid_product_grid_query_args`
    - `pns_ecwid_product_grid_product_card`
    - `pns_ecwid_product_grid_product_url`
4. Keep neutral editor preview cards if they are clearly marked as preview data
   and do not leak into frontend rendering.
5. Move PNS-specific fallback product data into the PNS theme or a site-specific
   adapter only after the filter contract exists.

Recommendation:

Use "last-good cache, then filter-provided static fallback" as the generic
runtime default. If no filter supplies products, render an editor-only message
and no public product grid rather than showing PNS content.

Acceptance criteria:

- Grepping the plugin for current PNS product names returns no runtime fallback
  usages.
- The PNS site can restore the three-card static fallback through a filter.
- Public visitors never see diagnostic copy or placeholder product data.

## Phase 3: CSS Ownership And Theme Decoupling

Objective: make the plugin stylesheet sufficient on its own.

Steps:

1. Treat `blocks/commerce/ecwid-product-grid/style.css` as the complete baseline
   card-grid contract.
2. Add CSS custom properties for all visual extension points needed by PNS:
    - max inline size
    - inline padding/gutter
    - card min width
    - grid gap
    - image aspect ratio
    - image object fit
    - title font family, size, weight, line height, transform
    - price size, weight, line height, color
3. Remove structural `.pns-ecwid-product-grid` rules from
   `styles/components/shop-intro.css`.
4. Keep only site-specific overrides in theme CSS, scoped to a site container
   and expressed as variables where possible.
5. Verify the grid remains usable when all theme Ecwid-grid overrides are
   temporarily disabled.

Acceptance criteria:

- The block looks like a complete product grid with only plugin CSS loaded.
- PNS theme overrides are optional enhancements, not required for basic layout.
- Page `3228` still matches the static comparison row after any PNS override
  cleanup.

## Phase 4: Native Supports And Editor Controls

Objective: make the block feel like a normal WordPress block instead of a
bespoke theme panel.

Steps:

1. Keep native `spacing` support for margin, padding, and block gap.
2. Re-evaluate custom typography/color controls and replace them with native
   supports where WordPress can target the correct wrapper or CSS variables.
3. If custom controls remain necessary for card internals, document why native
   supports are insufficient.
4. Ensure editor preview uses the same CSS variable contract as frontend render.
5. Confirm `example` metadata renders an inserter preview without requiring an
   Ecwid connection.

Acceptance criteria:

- Core spacing support works through `get_block_wrapper_attributes()`.
- Editor preview and frontend use the same card/layout defaults.
- Custom controls exist only where the block needs card-internal styling that
  core supports cannot express cleanly.

## Phase 5: Ecwid Integration Boundary

Objective: make Ecwid access optional, explainable, and replaceable.

Decision:

No block or plugin settings UI is required for v1. The installed Ecwid
WordPress plugin may remain the default source for the current shop/store ID and
API token. A supplied category ID is interpreted as belonging to that resolved
Ecwid store. Because Ecwid category IDs are only meaningful inside a specific
store, the effective lookup key is `store_id + category_id`, not `category_id`
alone.

For generic/plugin-directory readiness, the Ecwid plugin adapter must be the
default credential provider rather than the only possible provider. Add filters
so another plugin or theme can provide the store ID and token without adding a
settings UI.

Steps:

1. Keep the installed Ecwid WordPress plugin adapter as the default credential
   provider for store ID and token resolution.
2. Keep direct REST client fallback optional and credential access filterable.
3. Add and document credential filters for store ID and token resolution:
    - `pns_ecwid_product_grid_store_id`
    - `pns_ecwid_product_grid_token`
4. Document that `categoryId` is scoped to the resolved Ecwid store ID.
5. Avoid exposing token, store credential details, or raw API errors in public
   markup.
6. Keep debug details in `WP_DEBUG_LOG` and editor-only notices for users with
   `edit_posts`.
7. Confirm the block degrades cleanly when:
    - Ecwid plugin is inactive.
    - store ID is missing.
    - token is missing.
    - category is invalid.
    - Ecwid returns no products.

Acceptance criteria:

- Block remains installable and usable enough to configure without the Ecwid
  plugin active.
- On this site, the block can still use the active Ecwid plugin to infer the
  store ID for a category ID.
- A non-Ecwid-plugin credential provider can supply store ID and token through
  filters without adding UI.
- Failure modes are visible to editors, not public visitors.
- Future plugins or themes can override credential/product behavior with
  documented filters.

## Phase 6: Public Plugin Hygiene In The Current Shell

Objective: make the Ecwid block subtree ready for later extraction.

Steps:

1. Draft a future `readme.txt` section for the block:
    - description
    - installation
    - FAQ
    - privacy note
    - external service note for Ecwid API use
    - screenshots list
    - changelog
2. Add `Requires at least`, `Tested up to`, `Requires PHP`, and license targets
   to the future extraction notes.
3. Document all public filters with parameters and return shapes.
4. Confirm no admin menus, dashboard notices, or unrelated UX are required.
5. Keep server-side code bounded to registration, rendering, Ecwid catalog
   access, cache handling, and editor REST helpers.

Acceptance criteria:

- A future standalone plugin README can be assembled without rediscovering the
  behavior.
- Public filters and privacy/external-service behavior are documented.
- The block-specific code can be copied out without taking split-section code or
  unrelated PNS block infrastructure.

## Phase 7: Verification Matrix

Run these checks before marking the genericization complete:

- `pnpm --dir app/public/wp-content/plugins/pns-blocks build`
- `pnpm --dir app/public/wp-content/plugins/pns-blocks lint:js`
- `pnpm --dir app/public/wp-content/plugins/pns-blocks lint:css`
- PHP lint for:
    - `includes/Commerce/Ecwid/*.php`
    - `includes/Commerce/Rendering/EcwidProductGrid.php`
    - block `render.php`
- WP-CLI block registration check for `pns/ecwid-product-grid`.
- REST preview endpoint check as an editor-capable user.
- Frontend render checks:
    - no category selected
    - invalid category
    - Ecwid unavailable
    - last-good cache
    - filter-provided static fallback
    - live Ecwid product data
- Visual checks:
    - plugin CSS only, no PNS grid overrides
    - PNS page `3228` comparison against the static grid
    - mobile, tablet, desktop

## Implementation Order

1. Add filter contracts and decouple static fallback products.
2. Move PNS fallback data behind a PNS-owned filter.
3. Make plugin CSS independently sufficient.
4. Remove or reduce theme structural overrides.
5. Genericize block title, copy, docs, and comments.
6. Reconcile native supports versus custom controls.
7. Add public-plugin hygiene docs and extraction notes.
8. Run verification matrix.
9. Commit as one focused genericization change, or split into:
    - fallback/data decoupling
    - CSS/theme decoupling
    - docs/public-plugin hygiene

## Risks And Mitigations

- Risk: removing PNS fallback products changes the live local comparison.
  Mitigation: add the filter first, then move PNS fallback into the site layer in
  the same change.
- Risk: theme CSS removal changes the front-page shop row.
  Mitigation: compare page `3228` dynamic and static rows before and after.
- Risk: custom controls duplicate WordPress style engine behavior.
  Mitigation: keep native supports where possible and document remaining custom
  controls.
- Risk: Block Directory eligibility is limited by Ecwid account/API
  requirements.
  Mitigation: target generic Plugin Directory quality first; document any
  remaining Block Directory blockers separately.

## Done Definition

The work is complete when `pns/ecwid-product-grid` can be evaluated as a
standalone generic Ecwid product-grid block even though it still lives in
`pns-blocks`: no PNS runtime assumptions, no required PNS theme CSS, documented
extension points, clear Ecwid failure behavior, and verified rendering in both
theme-decoupled and PNS-styled contexts.

## Phase 7 Evidence - 2026-07-06

Dex: `5e6hco3p`.

Validation commands:

- `pnpm --dir app/public/wp-content/plugins/pns-blocks run build` passed.
- `pnpm --dir app/public/wp-content/plugins/pns-blocks run lint:css` passed.
- `pnpm --dir app/public/wp-content/plugins/pns-blocks run lint:js` passed.
- `pnpm --dir app/public/wp-content/plugins/pns-blocks run lint:pkg-json`
  passed.
- PHP syntax passed for project PHP under `app/public/wp-content/plugins/pns-blocks`,
  excluding `node_modules`.
- `wp eval` confirmed `WP_Block_Type_Registry` has
  `pns/ecwid-product-grid` registered.
- `wp post get 3228` confirmed page `3228` is still published at
  `/store-block-test/` and `has_block( 'pns/ecwid-product-grid' )` is true.

REST preview and cache behavior was validated through `rest_do_request()` as an
editor-capable user:

| Case                | Result                                                                  |
| ------------------- | ----------------------------------------------------------------------- |
| Missing category    | `source=missing-category`, `count=0`                                    |
| Live preview        | `source=ecwid`, `count=3` for category `203060063`                      |
| Live refresh        | `source=ecwid`, `count=3` for category `203060063`                      |
| Invalid/unavailable | `source=fallback-disabled`, `count=0` with fallback disabled            |
| Filter fallback     | `source=filter-fallback`, `count=1` with an isolated validation filter  |
| Last-good cache     | `source=last-good-cache`, `count=1` with isolated validation transients |

Important contract note: there is no separate `invalid-category` source. A
category that returns no Ecwid products follows the same unavailable/fallback
path as other fetch failures: negative cache, then last-good cache, then
filter-provided fallback, then disabled/empty fallback.

Frontend checks:

- Plugin-CSS-only Playwright probe passed without PNS theme CSS:
  `display=flex`, `flex-wrap=wrap`, `gap=24px`, card flex basis `256px`,
  title/price readable black, image aspect ratio `3 / 2`, and no horizontal
  overflow.
- Focused PNS-themed Playwright contract
  `homepage custom Ecwid product grid contracts` passed on desktop, tablet, and
  mobile.
- Focused rendered probes passed:
    - `/` renders the grid from `data-source=fresh-cache`,
      `data-category-id=203060063`, with three cards, black title/price text, and
      no horizontal overflow.
    - `/store-block-test/` renders the grid from `data-source=filter-fallback`,
      `data-category-id=999999`, with three cards, white title/price text on its
      current dark surface, and no horizontal overflow.

Outcome: the generic block contract is verified in both theme-decoupled and
PNS-styled contexts. PNS-specific polish remains in the standalone theme layer;
the plugin runtime does not require PNS fallback data or PNS theme CSS.
