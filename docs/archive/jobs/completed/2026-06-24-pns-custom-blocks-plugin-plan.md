# PNS Custom Blocks Plugin Plan

Created: 2026-06-24

Dex tracker root: `829hnr81` - `PNS custom blocks plugin`

## Goal

Create a project-owned WordPress plugin for portable PNS custom blocks. This
plan owns the Ecwid-backed Shop Intro product grid, while sibling plans can add
other blocks to the same plugin scaffold.

The first Ecwid deliverable is intentionally narrow:

- Plugin path: `app/public/wp-content/plugins/pns-blocks/`.
- First block name: `pns/ecwid-product-grid`.
- Rendering model: hybrid, PHP-rendered dynamic block.
- First data mode: a dedicated hidden Ecwid category, rendered as a flexible
  product grid that can accommodate `n` products.
- First placement target: native synced pattern `wp_block` `#1509`, `Shop Intro`.

Sibling block plan: `docs/jobs/__completed/2026-06-27-pns-video-banner-block-plan.md`
tracks `pns/video-banner` as a separate `VB*` Dex branch under the same
`829hnr81` root.

Whichever feature starts first must complete the shared plugin scaffold in
`CB1` / `45yn1av3` in a way that supports multiple blocks. Do not create a
second project-owned blocks plugin or duplicate plugin bootstrap, allowlist,
README, or activation work.

## Current Evidence

- This site currently has no project-owned custom block types. Existing block
  registration is plugin-owned or WordPress/database-owned; see
  `docs/2026-06-22-custom-blocks-patterns-audit.md`.
- `Shop Intro` is native synced pattern `wp_block #1509`; editing it updates all
  pages that reference the block. The audit recorded 11 references, including the
  home page, ArtWorks, Herstories, Educational Resources, Glasgow Herstory
  Workshops, Workshop, and Education Pack Giveaway.
- `Shop Intro (Using Ecwid Blocks)` is `wp_block #3816`, but it uses
  `ecwid/product-block` instances and was only observed on `Store BLOCK TEST`.
- `Store BLOCK TEST` is page `#3228` and is published locally at
  `/store-block-test/`. It embeds `wp_block #3816` and proves the Ecwid product
  blocks can populate when Ecwid's client script hydrates them.
- Local rendered testing showed the Ecwid product block outputs placeholder
  `ecwid-SingleProduct-v2` markup and depends on Ecwid JavaScript hydration. That
  is the wrong surface for matching the bespoke PNS three-card layout.
- The current local Ecwid store ID observed via WP-CLI is `62802007`. Local API
  status was `fail_token`, so API connectivity must be confirmed before feature
  implementation is considered complete.

## Block Type Decision

Use a hybrid PHP-rendered dynamic block.

That means:

- Static block attributes save editor choices: source mode, Ecwid category ID,
  limit/safety cap, fallback behavior, and CSS class.
- PHP renders the front-end markup from those attributes and fresh or cached
  Ecwid data.
- No Ecwid storefront/product-card widget markup is used for the card grid.
- The theme owns final visual styling through stable `.pns-*` classes.
- The plugin owns behavior, data access, caching, error handling, and block
  registration.

This is not a fully static block because prices/images/product URLs should update
from Ecwid. It is not a client-side Ecwid widget because the target layout needs
stable theme-owned markup.

## API References

WordPress APIs:

- `register_block_type()` registers block types and recommends `block.json`
  metadata as the canonical registration source:
  <https://developer.wordpress.org/reference/functions/register_block_type/>
- WordPress dynamic blocks render on demand and can save only attributes while a
  PHP `render_callback` controls front-end output:
  <https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/creating-dynamic-blocks/>
- `block.json` is the canonical metadata file for PHP and JavaScript block
  registration:
  <https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/>
- `wp_remote_get()` performs HTTP GET requests and returns a response or
  `WP_Error`:
  <https://developer.wordpress.org/reference/functions/wp_remote_get/>
- The Transients API provides expiring cache storage through `set_transient()`,
  `get_transient()`, and related functions:
  <https://developer.wordpress.org/apis/transients/>

Ecwid APIs:

- REST API overview: Ecwid uses OAuth 2.0, JSON, and rate limits. It accepts up
  to 600 requests per minute per token:
  <https://docs.ecwid.com/api-reference>
- Get product by ID: `GET https://app.ecwid.com/api/v3/{storeId}/products/{productId}`.
  Requires `Authorization: Bearer ...` and `read_catalog` access:
  <https://docs.ecwid.com/api-reference/rest-api/products/get-product>
- Search products: `GET https://app.ecwid.com/api/v3/{storeId}/products`.
  Supports `productId`, `category`, `categories`, `sortBy`, `enabled`,
  `inStock`, `visibleInStorefront`, `limit`, and `responseFields`:
  <https://docs.ecwid.com/api-reference/rest-api/products/search-products>

Local Ecwid plugin integration points, if reused through a narrow optional
adapter:

- `get_ecwid_store_id()` resolves the configured store ID in
  `app/public/wp-content/plugins/ecwid-shopping-cart/ecwid-shopping-cart.php`.
- `Ecwid_Api_V3::get_token()` resolves the plugin token in
  `app/public/wp-content/plugins/ecwid-shopping-cart/lib/ecwid_api_v3.php`.
- `Ecwid_Api_V3::build_request_headers()` uses an `Authorization: Bearer ...`
  header internally.
- `Ecwid_Api_V3::get_product()`, `search_products()`, and `get_products()` wrap
  product catalog reads in
  `app/public/wp-content/plugins/ecwid-shopping-cart/lib/ecwid_api_v3.php`.
- `Ecwid_Store_Page::get_store_url()`,
  `Ecwid_Store_Page::get_product_url_from_api()`, and
  `Ecwid_Store_Page::get_product_url_default_fallback()` build local storefront
  and product URLs in
  `app/public/wp-content/plugins/ecwid-shopping-cart/includes/class-ecwid-store-page.php`.

Do not treat those local plugin internals as the durable public contract. Prefer
official Ecwid REST semantics in the PNS plugin and keep any reuse of Ecwid's
WordPress plugin credentials behind a small adapter that can be replaced.

## Ecwid Plugin Primitives Assessment

The installed Ecwid plugin does offer useful primitives, but they are not all
equally suitable for this block.

Use these as guarded adapter inputs:

- `get_ecwid_store_id()` for the already-connected store ID, so editors do not
  need to configure the store twice.
- `Ecwid_Api_V3::get_token()` for the already-connected OAuth token, if the
  Ecwid plugin is active and the token is healthy. Never print, log, or expose
  this value.
- `Ecwid_Api_V3::get_product()`, `search_products()`, or `get_products()` for
  catalog reads if we choose to depend on the local plugin wrapper. These methods
  already use the plugin's bearer-token handling and product cache helpers.
- `Ecwid_Store_Page` URL helpers for local product links and fallback hashbang
  URLs, so product cards point back into the configured WordPress storefront.
- The optional `Ecwid_Products` local sync feature only as a later optimization.
  It can register an `ec-product` post type when `ecwid_local_base_enabled` is
  enabled, but it is not active by default and should not be required for v1.

Avoid these as the rendered card surface for the PNS grid:

- `ecwid/product-block`: it is a dynamic Gutenberg block, but its PHP render path
  delegates to `Ecwid_Shortcode_Product` and outputs Ecwid widget placeholder
  markup that depends on Ecwid JavaScript hydration.
- Ecwid product/store shortcodes such as `[ecwid_product]`,
  `[ecwid_productbrowser]`, `[ecwid]`, and `[ec_store]`: they are useful for
  full Ecwid widgets, but they produce Ecwid-owned runtime markup rather than
  stable PNS-owned product-card HTML.
- Styling `.ecwid-*`, `.ec-store`, or `.grid-product__*` runtime classes for the
  bespoke Shop Intro cards. Those selectors remain valid for storefront override
  work, but not for the custom product grid we want to control.

Implementation consequence: the PNS block should render its own HTML and use
Ecwid only as the catalog/source-of-truth layer. If the guarded plugin adapter is
missing or fails, the same normalized product card shape should be populated via
official Ecwid REST calls or a verified fallback cache.

## Ecwid Product Block Styling Assessment

Page `#3228` (`/store-block-test/`) is useful evidence but should not become the
production implementation path.

What it proves:

- `wp_block #3816`, `Shop Intro (Using Ecwid Blocks)`, can render three
  `ecwid/product-block` instances and populate product data after Ecwid's client
  script loads.
- The Ecwid plugin's block primitive handles the product selection and buy-button
  wiring for manually chosen product IDs.
- The existing standalone theme already has a narrow Ecwid override file at
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/vendor-overrides/ecwid.css`
  for full storefront styling.

Why it is not the recommended Shop Intro route:

- Server-rendered output is mostly empty Ecwid placeholders plus initialization
  scripts. Product title, image, and price depend on delayed JavaScript hydration
  from `https://app.ecwid.com/script.js?...`.
- The hydrated widget output is fixed around Ecwid's single-product card model,
  including inline `max-width` and image dimensions, Ecwid runtime classes, and a
  built-in buy button/price layout.
- `ecwid/product-block` exposes coarse toggles only: picture, title, price,
  options, quantity, buy button, border, center alignment, and price-on-button.
  It does not expose a stable card-markup contract, hidden-category product
  source, custom image aspect handling, or PNS-owned layout semantics.
- Styling the Ecwid product block would require scoped overrides against
  third-party runtime classes and inline styles. That is acceptable for
  storefront cleanup, but brittle for the curated front-page Shop Intro layout.
- The reusable block/template path around `wp_block #3816` has shown editor crash
  risk when applied elsewhere, so it should stay a reference/test artifact rather
  than the production synced-pattern surface.

Conclusion: keep `#3228` and `#3816` as a comparison fixture and proof that Ecwid
data can hydrate, but do not build the production Shop Intro by overriding Ecwid
product-block styles. Build the PNS block as planned: Ecwid remains the catalog
source, while `pns/ecwid-product-grid` owns the rendered product-card HTML.

## Target Architecture

```text
app/public/wp-content/plugins/pns-blocks/
  pns-blocks.php
  includes/
    Blocks.php
    Assets.php
    Commerce/
      Ecwid/
        Client.php
        ProductRepository.php
        Credentials.php
        EcwidPluginAdapter.php
      Rendering/
        EcwidProductGrid.php
  blocks/
    commerce/
      ecwid-product-grid/
        block.json
        index.js
        editor.css
        render.php
    media/
      video-banner/
        block.json
        index.js
        view.js
        render.php
        editor.css
        style.css
  README.md
```

The Ecwid-specific classes are optional until the Ecwid block starts. The shared
scaffold should load blocks from a predictable
`blocks/<family>/<block-name>/block.json` convention so the video-banner and
product-grid tracks can proceed independently after `CB1`. Commerce/Ecwid PHP
helpers should stay under commerce-specific include namespaces and must not be
imported by media blocks.

The plugin should not ship broad global CSS. Each custom block should be
self-contained and rely on WordPress/theme.json design artifacts such as preset
CSS variables and block supports. Themes may tune blocks through scoped
overrides, but block behavior and baseline presentation should not depend on
project-global theme CSS:

- Current child theme: `app/public/wp-content/themes/protestsandsuffragettes/`.
- Current local standalone theme: `app/public/wp-content/themes/protestsandsuffragettes-standalone/`.

For v1, add baseline front-end CSS to each block's plugin-owned block stylesheet
so custom blocks remain portable. Use theme CSS only for explicit, scoped theme
overrides if the rollout owner reopens that decision.

## Block Contract

Block name: `pns/ecwid-product-grid`

Initial attributes:

```json
{
  "sourceMode": "category",
  "categoryId": 0,
  "limit": 12,
  "showUnavailable": false,
  "cacheTtl": 3600,
  "fallbackMode": "last-good-cache-static"
}
```

Category mode is the v1 default. The implementation should use a dedicated
hidden Ecwid category as the editorial source of truth, with Ecwid product order
driving card order. `limit` is a safety cap, not a layout assumption; theme CSS
must handle any reasonable number of cards using grid or flex layout.

Manual IDs can be added later if editors need a one-off curated override, but
they are not the first-cut product source.

Normalize each product to this internal card shape:

```php
array(
	'id' => 612143977,
	'name' => 'Rent Strikers & Suffragettes Zine',
	'price' => '£7.00',
	'image_url' => 'https://...',
	'image_alt' => '...',
	'url' => 'https://...',
	'enabled' => true,
	'in_stock' => true,
)
```

Use `defaultDisplayedPriceFormatted` from Ecwid rather than formatting currency
locally when available.

## Phases

### CB0: Confirm Plugin Scope and API Access

Dex: `0v1taaj4`

1. Confirm active theme and plugin state:
   `wp theme list --status=active`, `wp plugin list --status=active`.
2. Confirm the current front-page and synced-pattern references:
   `wp option get page_on_front`, `wp post get 49 --field=post_content`,
   and a search for `ref":1509`.
3. Confirm Ecwid state:
   `wp option get ecwid_store_id`,
   `wp eval 'echo get_option("ecwid_api_status");'`,
   and whether `Ecwid_Api_V3::get_token()` returns a token without printing it.
4. Use the Ecwid plugin's configured store ID/token as the v1 credential source
   through a guarded adapter. Keep a direct REST client fallback internally, but
   do not add a second credential UI unless the adapter proves insufficient.
5. Confirm the hidden Ecwid category ID that will drive the first product grid.
6. Export current `wp_block #1509` content to a local rollback file under `/tmp`
   before any later DB mutation.

Acceptance:

- API/token status and chosen credential source are documented.
- Hidden category ID for v1 is confirmed.
- No code changes have been made before the source-of-truth check is complete.

### CB1: Scaffold Project-Owned Blocks Plugin

Dex: `45yn1av3`

1. Update `.gitignore` to allowlist only:
   `app/public/wp-content/plugins/pns-blocks/` and its children.
2. Create `pns-blocks.php` with a standard plugin header and strict direct-access
   guard.
3. Register plugin bootstrap on `init`.
4. Add a shared block registration/loading convention for multiple families and
   blocks, not only `pns/ecwid-product-grid`.
5. Add `README.md` with owner boundary:
   plugin owns block/data behavior; theme owns visual styling; synced patterns
   own page placement/copy.
6. Activate locally through WP-CLI and confirm no fatal errors.

Acceptance:

- `wp plugin list --status=active` can show `pns-blocks` active.
- The scaffold can register more than one block from
  `blocks/<family>/<block-name>/block.json`.
- No parent theme, WordPress core, uploads, cache, or third-party plugin code is
  tracked.
- `php -l` passes for all plugin PHP files.

### CB2: Define PHP-Rendered Block Contract

Dex: `xe6fncxw`

1. Add `blocks/ecwid-product-grid/block.json`.
2. Register the block with `register_block_type()`.
3. Use API version 3.
4. Use a PHP render path, either `render` in `block.json` or a PHP
   `render_callback` supplied during registration.
5. Keep saved content minimal. The block should save attributes, not a full copy
   of product-card markup.
6. Add editor controls for:
   - source mode,
   - Ecwid category ID,
   - limit/safety cap,
   - fallback mode.

Acceptance:

- The block appears in the editor inserter.
- The editor can display either a simple PHP server-rendered preview or a
  lightweight placeholder that clearly lists the selected category/source state.
- Front-end render works without Ecwid JavaScript widget hydration.

### CB3: Build Ecwid Catalog Data Layer

Dex: `3gnp9920`

1. Implement a `Credentials` class:
   - store ID from the guarded Ecwid plugin adapter,
   - token from the guarded Ecwid plugin adapter,
   - never log or render tokens.
2. Implement an `EcwidPluginAdapter` class that checks `function_exists()` and
   `class_exists()` before using local plugin primitives:
   `get_ecwid_store_id()`, `Ecwid_Api_V3::get_token()`, `Ecwid_Api_V3`
   product-read methods, and `Ecwid_Store_Page` URL helpers.
3. Implement a `Client` class using `wp_remote_get()` as the durable fallback
   path against official Ecwid REST endpoints.
4. For category mode, use Ecwid product search with the selected `category`,
   `enabled`, `inStock`, and configured `limit` values. Preserve Ecwid/category
   ordering where the API provides it.
5. Always request only needed response fields:
   `id,name,enabled,inStock,defaultDisplayedPriceFormatted,imageUrl,thumbnailUrl,media,url`.
6. Add transients:
   - fresh cache by attribute hash,
   - last-good cache by category ID and query attributes,
   - short negative cache for API failures.
7. Normalize and validate product data:
   - skip disabled products unless `showUnavailable` is true,
   - skip out-of-stock products unless explicitly allowed,
   - require URL, product name, and an image or fallback image.

Acceptance:

- API failures return stable fallback markup rather than a blank section.
- The block does not exceed Ecwid rate limits under normal cached rendering.
- Cache can be invalidated by changing block attributes or deleting transients.

### CB4: Render and Style Shop Product Grid

Dex: `kpbyqfka`

1. Render stable markup such as:

```html
<div class="pns-ecwid-product-grid" data-source="category">
  <article class="pns-ecwid-product-card">
    <a class="pns-ecwid-product-card__media" href="...">
      <img ...>
    </a>
    <h3 class="pns-ecwid-product-card__title">...</h3>
    <p class="pns-ecwid-product-card__price">...</p>
  </article>
</div>
```

2. Add theme CSS for:
   - flexible grid or flex layout that handles `n` product cards,
   - three-column behavior when the current three-product set is returned,
   - stacked or two-column responsive behavior where appropriate,
   - current image crop/aspect behavior,
   - Rubik uppercase title styling,
   - title/price rhythm matching the current screenshot as closely as pure Ecwid
     product fields allow.
3. Keep product cards clickable through the product image/title or whole-card
   link, but avoid nesting invalid anchors.
4. Add empty/error/fallback state markup that is visible to editors but not noisy
   to public users when last-good data exists.

Acceptance:

- Home/Shop Intro visually matches the current static section closely when the
  hidden category returns the current three products, and remains coherent when
  the category contains more or fewer products.
- Markup validates for basic accessibility: alt text, meaningful links, no empty
  headings.
- CSS selectors are stable PNS-owned classes, not Ecwid runtime classes.

### CB5: Migrate Shop Intro Synced Pattern

Dex: `jtjniova`

1. Export current `wp_block #1509` before editing:
   `wp post get 1509 --field=post_content > /tmp/pns-shop-intro-1509-before.html`.
2. Replace only the static product-card columns with
   `<!-- wp:pns/ecwid-product-grid ... /-->`.
3. Preserve:
   - heading,
   - intro paragraphs,
   - `View our shop` button,
   - `shop-intro` wrapper semantics,
   - synced-pattern update behavior.
4. Keep `wp_block #3816` as legacy/test until the new block is proven, then mark
   it in docs or retire it later.
5. Record rollback command:
   `wp post update 1509 --post_content="$(cat /tmp/pns-shop-intro-1509-before.html)"`.

Acceptance:

- All pages referencing `wp_block #1509` inherit the new product grid.
- No page-specific copies are edited accidentally.
- Rollback has been tested or at least dry-run validated.

### CB6: Verify, Document, and Release Gate

Dex: `xapc858h`

Run verification in this order:

1. PHP syntax:
   `find app/public/wp-content/plugins/pns-blocks -name '*.php' -print -exec php -l {} \;`
2. WP-CLI plugin smoke:
   `wp plugin activate pns-blocks`,
   `wp plugin list --status=active`,
   and `wp eval 'echo do_blocks("<!-- wp:pns/ecwid-product-grid ... /-->");'`.
3. Theme CSS compile for the active PNS theme.
4. Editor smoke if feasible: open a page using `Shop Intro` and confirm block
   controls do not produce validation errors.
5. Front-end Playwright coverage:
   - `/`,
   - `/herstories/mary-barbour/`,
   - `/shop/`,
   - `/educational-resources/`,
   - any page with a distinct `Shop Intro` placement.
6. API failure smoke:
   - simulate bad/missing token,
   - verify last-good or static fallback renders.
7. Docs update:
   - update `docs/2026-06-22-custom-blocks-patterns-audit.md` or add a
     follow-up note that `pns-blocks` now owns project block registration.
   - update this plan with final command evidence.
   - complete Dex tasks with results.

Acceptance:

- Visual checks pass or intentional deltas are reviewed.
- API failure does not blank the shop section.
- Dex has final evidence on each phase.

## Implementation Notes

- `CB1` is shared by the Ecwid product-grid and video-banner plans. If this
  Ecwid track starts first, scaffold `pns-blocks` as an extensible multi-block
  plugin rather than a one-off Ecwid plugin.
- Use direct Ecwid REST responses for product data rather than hydrated Ecwid
  widget markup.
- It is acceptable to use the installed Ecwid plugin's store ID, token, product
  fetch methods, and URL helpers behind a guarded adapter. Keep official Ecwid
  REST as the durable contract and fallback path.
- Do not use Ecwid's product block or shortcodes to render the Shop Intro cards;
  use them only as reference for plugin behavior or as separate storefront
  widgets elsewhere.
- Treat page `#3228` and `wp_block #3816` as evidence/reference fixtures for
  Ecwid product hydration, not as the production implementation path.
- Treat the Ecwid WordPress plugin as an installed dependency, not as a place to
  patch project behavior.
- Keep tokens out of markup, logs, docs, screenshots, and Dex results.
- Prefer `responseFields` on Ecwid requests to reduce payload size.
- Cache product data aggressively enough to avoid rate-limit pressure, but keep a
  manual cache-bust path for product updates.
- Do not remove the existing static `Shop Intro` content until the dynamic block
  has a verified fallback path.
- Do not make the plugin responsible for the whole visual system. Theme CSS owns
  final presentation.

## Implementation Progress

### 2026-06-29

Implemented the first `pns/ecwid-product-grid` slice without migrating
`wp_block #1509`.

Files added under `app/public/wp-content/plugins/pns-blocks/`:

- `blocks/commerce/ecwid-product-grid/block.json`
- `blocks/commerce/ecwid-product-grid/index.js`
- `blocks/commerce/ecwid-product-grid/index.asset.php`
- `blocks/commerce/ecwid-product-grid/editor.css`
- `blocks/commerce/ecwid-product-grid/render.php`
- `includes/Commerce/Ecwid/Client.php`
- `includes/Commerce/Ecwid/Credentials.php`
- `includes/Commerce/Ecwid/EcwidPluginAdapter.php`
- `includes/Commerce/Ecwid/ProductRepository.php`
- `includes/Commerce/Rendering/EcwidProductGrid.php`

Files updated:

- `app/public/wp-content/plugins/pns-blocks/pns-blocks.php` now loads the
  commerce data/render classes.
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/components/shop-intro.css`
  now styles the PNS-owned product-grid/card classes.

Verified:

- `pns-blocks` activates locally.
- `pns/ecwid-product-grid` registers through WordPress block metadata.
- The block renders via PHP without Ecwid widget hydration.
- With local Ecwid API status still `fail_token`, a dummy category ID renders the
  static Shop Intro fallback cards instead of blank output.
- PHP syntax passes for all plugin PHP files.
- Targeted JS syntax, Prettier, and Stylelint checks pass for touched block/CSS
  files.
- Direct Lightning CSS frontend/editor compiles pass for the standalone theme.

Still pending before migrating the synced pattern:

- Confirm/fix Ecwid API token health.
- Confirm the hidden Ecwid category ID for v1.
- Verify live category products flow through the adapter/REST path.
- Browser/editor smoke the block in Gutenberg before replacing `wp_block #1509`.

Migrated the safe test surface page `#3228` (`/store-block-test/`) after the
initial block implementation:

- Exported the pre-change page content to
  `docs/jobs/live-adoption-db-backups/2026-06-29-220014-page-3228-before-pns-ecwid-grid.json`.
- Replaced only the bottom `<!-- wp:block {"ref":3816} /-->` reference with
  `<!-- wp:pns/ecwid-product-grid {"categoryId":999999,"limit":3} /-->`.
- Verified the old Ecwid widget output is gone from the test surface.
- Verified the new grid renders three fallback cards on `/store-block-test/` with
  `data-source="static-fallback"` and `data-category-id="999999"`.

The `999999` category ID is a temporary test placeholder to exercise the
fallback render while local Ecwid API status remains `fail_token`. Replace it
with the real hidden category ID before treating the test page as live
category-source proof.

## Locked Decisions

These choices are locked for v1 implementation:

| Question | Decision |
| --- | --- |
| Shared plugin scaffold | Complete `CB1` as a neutral, reusable `pns-blocks` scaffold before any block-specific implementation depends on it. It must support both `pns/ecwid-product-grid` and `pns/video-banner`. |
| Ecwid credential source | Use a guarded adapter to the existing Ecwid plugin store ID/token. Keep direct REST code as a replaceable fallback path, but do not add a second credential UI in v1. |
| Product source | Use a dedicated hidden Ecwid category as the first-cut editorial source. Manual product IDs are not part of v1 unless later reopened. |
| Product display fields | Use pure Ecwid product fields for title/name, image, price, URL, availability, and stock state. Do not add per-card editorial title, subtitle, image, or alt overrides in v1. |
| Layout count | Do not hard-code a three-card layout. CSS must use grid or flex so the block handles `n` products while still matching the current three-product screenshot when the hidden category contains the current three products. |
| Ecwid product block styling | Do not pursue a CSS-only override of `ecwid/product-block` for the production Shop Intro. Keep `#3228` and `wp_block #3816` as reference fixtures only. |
| Active theme target | Add visual styling only to the active PNS rollout theme confirmed in `CB0`; do not duplicate styling into both child and standalone themes by default. |
| Fallback policy | Use fresh cache first, last-good cache second, and a static fallback copied from the current `wp_block #1509` when Ecwid/API data is unavailable. |
| Editor experience | Start with a stable editor placeholder or lightweight preview that shows selected category/source state. Do not build React-side Ecwid fetching in v1. |
| Plugin build tooling | Prefer no plugin build step and plain JavaScript unless implementation proves JSX/build tooling is necessary. Do not couple plugin builds to the theme tooling. |

Implementation order is intentionally not locked here. The rollout owner will
decide whether the video-banner or Ecwid product-grid stream goes first. The
only ordering dependency is that the shared `pns-blocks` scaffold must exist
before either block can be registered.
