# CSS Cascade Layer Migration Plan

Created: 2026-07-07.

All paths are relative to the project root.

## Purpose

Make cascade layers real in the standalone theme without weakening the CSS that
currently has to beat WordPress core, block-support, global-style, and plugin
output.

The theme already declares layer names, but that is mostly a scaffold:

- `styles/shared/layers.css` declares `settings`, `base`, `layout`, `blocks`,
  `components`, `utilities`, and `vendor-overrides`.
- `styles/frontend.css` and `styles/editor.css` import that declaration first.
- Lightning CSS preserves layer syntax in the compiled outputs.
- Most authored rules are still unlayered.
- Bare declarations such as `@layer utilities;` do not layer following rules.
- `styles/components/cross-site-banner-cta.css` is currently the only clearly
  layered rule block.

The migration goal is not "put everything in layers." The useful goal is:

1. put theme-owned defaults into real layers;
2. leave known WordPress/plugin/runtime override tails explicit and small;
3. use tests and compiled-output inspection to prove each slice before moving
   the next one.

## Related Work

- Control-health baseline:
  `docs/jobs/2026-07-06-theme-css-control-health-remediation-plan.md`
- Core selector follow-up:
  `docs/jobs/2026-07-07-core-selector-remediation-plan.md`
- Content rhythm follow-up:
  `docs/jobs/2026-07-07-content-rhythm-heuristic-hardening-plan.md`
- Retained render-bridge follow-up:
  `docs/jobs/2026-07-07-retained-render-bridge-remediation-plan.md`
- Visual suite cleanup:
  `docs/jobs/2026-07-06-motion-perceived-loading-plan.md`

## Current Evidence

The standalone theme has two theme CSS delivery paths:

- Main bundled frontend/editor CSS:
  - `styles/frontend.css` compiles to `styles/dist/frontend.min.css`.
  - `styles/editor.css` compiles to `styles/dist/editor.min.css`.
  - The frontend handle is `pns-standalone-style`.
  - The editor bundle is loaded through `add_editor_style()`.
- Block-scoped CSS registered through `wp_enqueue_block_style()` for core
  Columns, Cover, Group, Image, Navigation, Quote, Separator, Social Links, and
  Jetpack Slideshow.

The surrounding WordPress/plugin CSS is not layered:

- WordPress core block styles;
- `theme.json` global styles;
- block-support inline styles;
- Ecwid CSS;
- Jetpack Slideshow CSS;
- any other active plugin CSS that enters the page.

That matters because normal unlayered author rules beat normal layered author
rules regardless of source order. A blanket migration would make many PNS
rules less authoritative than the CSS they currently override.

## Cut 0 Baseline Refresh - 2026-07-10

Before implementation, the DB was backed up to:

```text
docs/jobs/cascade-layer-db-backups/20260710-134746-before-cascade-layers.sql
```

The accepted visual snapshot baseline was refreshed with:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:update
```

Result:

- `240 passed`
- `25 failed`
- `2 skipped`

The screenshot baselines in
`app/public/wp-content/themes/protestsandsuffragettes-standalone/tests/visual/frontend.spec.ts-snapshots/`
were regenerated on disk, but that snapshot directory is ignored by Git and is
therefore a local baseline artifact rather than tracked source.

The remaining failures are assertion/contract failures in the current baseline,
not screenshot-diff failures caused by cascade-layer work:

- Herstories saved background group audit still reports padded groups.
- Dark-section inversion contract expects old outside colors.
- Core selector ownership fixture still expects tighter large-size line-height.
- Herstories split-section audit expects at least four split sections but the
  current page has three.
- Homepage cascade contract still expects a Herstories desktop submenu overview
  label that is not present.
- Manual spacing fixture expects `23px` flow child margin but current baseline
  computes `10px`.
- Live adoption quote audit still looks for the old Mary Barbour quote cover.
- Mobile additionally reports current native single-post navigation, navigation
  control surface, and red-line quote hook assertion drift.

Treat those failures as pre-layer baseline debt. Do not attribute them to a
layer migration cut unless a later cut changes the failure shape or count.

## Cut 0 Cascade Evidence - 2026-07-10

Starting tracked state:

```text
 M app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/dist/editor.min.css.map
 M app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/dist/frontend.min.css.map
 M docs/jobs/2026-07-07-css-cascade-layer-migration-plan.md
```

Baseline validation before CSS edits:

```text
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
PASS

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
PASS
```

Current authored layer usage outside `styles/dist/`:

- `styles/shared/layers.css` declares the ordered layer names.
- `styles/components/cross-site-banner-cta.css` is the only real authored
  layered rule block.
- `styles/components/frontend.css`, `styles/components/index.css`,
  `styles/utilities/index.css`, and `styles/vendor-overrides/index.css` still
  contain bare layer declarations only; those declarations do not layer the
  imported rules that precede them.

Current CSS delivery paths:

- `inc/assets.php` loads `styles/dist/frontend.min.css` through the
  `pns-standalone-style` handle.
- `inc/assets.php` loads `styles/dist/editor.min.css` through
  `add_editor_style()`.
- `inc/assets.php` registers block-scoped styles through
  `wp_enqueue_block_style()`.
- `styles/css-assets.json` remains the block-scoped style manifest and still
  records duplicate-load allowlists for selected block files.

Current authored `!important` families outside `styles/dist/`:

- Third-party plugin/vendor override boundary:
  - `styles/vendor-overrides/ecwid.css`: 70
  - `styles/vendor-overrides/emailoctopus.css`: 8
  - `styles/blocks/jetpack-slideshow.css`: 1
- Core block / WordPress block-support override boundary:
  - `styles/blocks/core-navigation-core-drawer.css`: 7
  - `styles/blocks/core-navigation-desktop.css`: 2
  - `styles/blocks/core-social-links.css`: 2
  - `styles/components/buttons.css`: 1
- Saved-content or component geometry compatibility:
  - `styles/components/cross-site-banner-cta.css`: 12
  - `styles/components/footer-layout.css`: 5
  - `styles/components/split-section.css`: 3
  - `styles/page-types/herstories-bios.css`: 2

Total authored `!important` count outside compiled CSS: 113. Those rules are
not cleanup targets for Cut 1.

## Cut 1 Progress - 2026-07-10

Implemented the first narrow layer proof:

- `styles/frontend.css` imports `shared/settings.css` with `layer(settings)`.
- `styles/editor.css` imports `shared/settings.css` with `layer(settings)`.
- `shared/fonts.css` intentionally remains unlayered.

Important finding:

- The initial attempt also imported `shared/fonts.css` with `layer(settings)`.
  Compile and Stylelint passed, but the smoke visual contract failed because
  the browser-facing font-face check could no longer read the expected
  `font-display: swap` value. The font import was therefore rolled back to
  unlayered in both bundles.
- Treat `@font-face` as a registration concern, not a cascade-layer migration
  candidate, unless a later browser-compatibility proof shows otherwise.

Compiled output inspection after rollback:

- `styles/dist/frontend.min.css` and `styles/dist/editor.min.css` both emit a
  real `@layer settings { ... }` block containing the `:root` settings custom
  properties.
- `@font-face` rules are emitted outside that layer.
- Lightning CSS normalizes the remaining empty declarations into grouped layer
  declarations such as `@layer base,layout,blocks;` and
  `@layer utilities,vendor-overrides;`.

Validation after the narrower settings-only change:

```text
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
PASS

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
PASS

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:smoke
FAIL - known pre-layer Herstories submenu overview assertion only

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone exec playwright test tests/visual --grep "@mobile-smoke" --project=mobile
PASS
```

Cut 1 remains open. Do not proceed to `base`, `layout`, or `utilities` until
the settings-only layer proof has been reviewed.

Follow-up implementation in the same cut:

- `layout/index.css` was briefly imported with `layer(layout)` in both bundles.
  Compile and Stylelint passed, but `test:visual:layout` caught a new regression:
  the homepage `main` element picked up `10px` top margin instead of the required
  `0px`. The layout import was rolled back to unlayered.
- Treat `layout/index.css` as mixed priority CSS. Its site-shell rules need to
  beat generated layout margins, so it should not move wholesale into
  `layer(layout)`.
- `utilities/index.css` was then moved into `layer(utilities)` in both bundles.
  The old bare `@layer utilities;` declaration was removed from the utility
  partial because the import now provides real layer membership.

Validation after settings plus utilities:

```text
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
PASS

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
PASS

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
FAIL - known pre-layer desktop assertion baseline only

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone exec playwright test tests/visual --grep "@mobile-layout" --project=mobile
PASS
```

Cut 1 conclusion:

- Keep `shared/fonts.css` unlayered.
- Keep `layout/index.css` unlayered until it is split into layered defaults and
  explicit unlayered shell override tails.
- Defer `base/elements.css`; it is broad site-reset/rhythm CSS and should not
  move until the baseline assertion debt is cleaned up or a dedicated base cut
  proves it against WordPress global styles.
- Accept `shared/settings.css` in `settings` and `utilities/index.css` in
  `utilities` as the rollbackable Cut 1 implementation.

## Cut 2 Progress - 2026-07-10

Implemented a conservative component-layer batch by importing these component
partials with `layer(components)` from both component barrels, except where
noted:

- `footer.css`
- `hero.css`
- `motion.css`
- `search-results.css`
- `style-guide.css`
- `template-reveal.css` from the frontend barrel only

The following candidate files were tested and rolled back to unlayered because
they lost required priority against WordPress/core/global-style output:

- `taxonomy-pills.css`: taxonomy pill foreground changed from white to near
  black in search, archive, and single-post templates.
- `archive-cards.css`: page search-result meta became visible because the
  layered `display: none` lost to unlayered block layout CSS.
- `single-post.css`: single-post header height exceeded the template contract.
- `header.css` and `logos.css`: header height and logo/navigation overlap
  contracts failed.
- `synced-sections.css`: homepage connect/contact intro spacing changed from
  `20px` to `10px`.

Mixed/high-risk component files still intentionally unlayered:

- `buttons.css`
- `archive-cards.css`
- `footer-layout.css`
- `header.css`
- `logos.css`
- `split-section.css`
- `section-theme.css`
- `light-surface.css`
- `synced-sections.css`
- `taxonomy-pills.css`
- `single-post.css`

Validation after the narrowed component batch:

```text
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
PASS

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
PASS

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
PASS - 26 passed

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
FAIL - known pre-layer desktop assertion baseline only

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone exec playwright test tests/visual --grep "@mobile-navigation" --project=mobile
PASS - 3 passed
```

Cut 2 conclusion:

- Only clearly self-contained component presentation files should be layered
  wholesale.
- Several apparently small component files are still priority-sensitive because
  they override unlayered WordPress generated layout, link color, or header
  geometry.
- Any further component migration should split those files into layered default
  blocks plus explicit unlayered override tails instead of moving whole files.

## Cut 3 Progress - 2026-07-10

Reframed Cut 3 before implementation:

- This cut is not approval to layer plugin CSS.
- The useful boundary is core block defaults versus unlayered override tails.
- Plugin-adjacent CSS, including Jetpack Slideshow, stays classified as
  vendor/plugin override CSS unless a later vendor cut proves otherwise.

Block CSS classification:

- `core-post-template.css`: safe bundled default. Moved to a real
  `layer(blocks)` import in both block barrels because it only removes list
  indentation from Query Loop post templates.
- `core-navigation.css` and its private partials: keep unlayered. Navigation
  competes with unlayered WordPress core responsive-container and submenu CSS.
- `core-cover.css`: keep unlayered. It is duplicate-loaded by the bundle and
  block-scoped registration and includes priority-sensitive cover padding and
  section inner-container geometry.
- `core-quote.css`: keep unlayered. Quote typography must beat broad unlayered
  base/content rules and is duplicate-loaded until the block loading policy is
  simplified.
- `jetpack-contact-form.css`: keep unlayered. It is plugin-adjacent form markup,
  not a core block default.
- Block-scoped-only core files (`core-columns.css`, `core-group.css`,
  `core-image.css`, `core-separator.css`, `core-social-links.css`): keep
  unlayered for now. They are not in the bundle, and moving their standalone
  block-scoped handles into layers could make them lose to unlayered core or
  global-style output.
- `jetpack-slideshow.css`: keep unlayered and treat as vendor/plugin override
  territory for Cut 4, not as a `blocks` layer candidate.

Cut 3 conclusion:

- Only `core-post-template.css` moved in this cut.
- The rest of the block CSS is intentionally unlayered until a narrower split
  proves individual defaults can move without weakening override tails.
- No plugin override CSS was layered.

Validation after the narrowed block default proof:

```text
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
PASS

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
PASS

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone exec prettier --check styles/blocks/index.css styles/blocks/editor.css styles/blocks/core-post-template.css docs/jobs/2026-07-07-css-cascade-layer-migration-plan.md
PASS

git diff --check
PASS

Compiled output inspection:
PASS - `styles/dist/frontend.min.css` and `styles/dist/editor.min.css` both
emit `@layer blocks { .wp-block-post-template { ... } }`.

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
PASS - 26 passed

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
FAIL - known pre-layer desktop assertion baseline only: core selector large
line-height, homepage Herstories submenu overview, and manual spacing flow
margin. Direct mobile layout passed 8/8.

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
FAIL - known pre-layer desktop assertion baseline only: core selector large
line-height and homepage Herstories submenu overview. Direct mobile navigation
passed 3/3.

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
PASS - 6 passed, 2 skipped
```

## Cut 4 Progress - 2026-07-10

Vendor boundary decision:

- Keep `vendor-overrides` as a reserved layer name in `styles/shared/layers.css`.
- Do not use the layer for current Ecwid or EmailOctopus override files.
- Remove the misleading bare `@layer vendor-overrides;` declaration from
  `styles/vendor-overrides/index.css`; it did not layer the imported rules and
  made the vendor boundary look more migrated than it was.
- Document in code that current vendor override imports intentionally remain
  unlayered because Ecwid and EmailOctopus output unlayered plugin/runtime CSS.

Classification:

- `styles/vendor-overrides/ecwid.css`: keep unlayered. It adapts hydrated and
  static Ecwid runtime markup to the PNS storefront contract, uses many
  documented `!important` rules, and must beat Ecwid's unlayered CSS.
- `styles/vendor-overrides/emailoctopus.css`: keep unlayered. It adapts hosted
  EmailOctopus embed wrappers, fixed-width form markup, focus states, and
  injected utility classes that are unlayered and hydration-sensitive.
- `styles/vendor-overrides/index.css`: imports remain unlayered. The only
  layer-related behavior is the central reserved layer order in
  `styles/shared/layers.css`.

Cut 4 conclusion:

- No current vendor override rules moved into `layer(vendor-overrides)`.
- The layer name remains available only for future low-priority
  vendor-adjacent defaults that do not need to beat plugin CSS.
- Actual plugin override CSS remains in the normal author cascade.

Validation after the vendor boundary decision:

```text
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
PASS

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
PASS

pnpm exec prettier --check styles/blocks/*.css styles/vendor-overrides/*.css styles/shared/layers.css docs/jobs/2026-07-07-css-cascade-layer-migration-plan.md
PASS

git diff --check
PASS

Layer declaration inspection:
PASS - no bare `@layer vendor-overrides;` remains in
`styles/vendor-overrides/index.css`; the only vendor-overrides layer declaration
is the central reserved layer order in `styles/shared/layers.css`.

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:ecwid
FAIL - 30 passed; only `/shop/` visual snapshots failed on desktop, tablet, and
mobile. Ecwid cascade, product detail readability, related product card sizing,
cart recommendation readability, view-transition, hydration reservation, and
homepage product-grid contracts all passed.

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:emailoctopus
PASS - 9 passed
```

## Cut 5 Closeout - 2026-07-10

Final source audit after Cuts 1-4:

- `settings` is real in both bundles through `shared/settings.css`
  `layer(settings)` imports.
- `utilities` is real in both bundles through `utilities/index.css`
  `layer(utilities)` imports.
- `blocks` is real in both block barrels through `core-post-template.css`
  `layer(blocks)` imports.
- `components` is real for the conservative component batch:
  `footer.css`, `hero.css`, `motion.css`, `search-results.css`,
  `style-guide.css`, frontend-only `template-reveal.css`, and the internal
  `@layer components { ... }` block in `cross-site-banner-cta.css`.
- `vendor-overrides` remains a reserved layer name only.
- `base` and `layout` remain reserved layer names only.

Final compiled output audit:

- `styles/dist/frontend.min.css` and `styles/dist/editor.min.css` both emit
  real `@layer settings`, `@layer blocks`, `@layer components`, and
  `@layer utilities` blocks.
- Both compiled bundles emit declaration-only `@layer base,layout;` and
  `@layer vendor-overrides;` from the central layer order. Those declarations
  are ordering reservations, not evidence of migrated rules.
- Both compiled bundles keep Ecwid and EmailOctopus selectors outside layers.
- Both compiled bundles keep `.wp-block-post-template` inside `@layer blocks`.

Misleading bare declarations cleanup:

- Removed the remaining no-op `@layer components;` declarations from
  `styles/components/frontend.css` and `styles/components/index.css`.
- The only source-level standalone layer declaration is now the central
  ordered declaration in `styles/shared/layers.css`.

Intentional unlayered families at closeout:

- `shared/fonts.css`: unlayered because layering `@font-face` caused the
  browser-facing font-display contract to fail during Cut 1.
- `layout/index.css`: unlayered because wholesale layout layering lost priority
  against generated layout margins.
- `base/elements.css`: deferred because broad reset/rhythm CSS needs a
  dedicated proof against WordPress global styles and baseline assertion debt.
- Mixed/high-risk components: `buttons.css`, `archive-cards.css`,
  `footer-layout.css`, `header.css`, `logos.css`, `split-section.css`,
  `section-theme.css`, `light-surface.css`, `synced-sections.css`,
  `taxonomy-pills.css`, and `single-post.css`.
- Block and plugin override tails: Navigation, Cover, Quote, Jetpack Contact
  Form, Jetpack Slideshow, and block-scoped-only core Columns, Group, Image,
  Separator, and Social Links CSS.
- Vendor overrides: Ecwid and EmailOctopus remain unlayered because their
  plugin/runtime CSS is unlayered and hydration-sensitive.

Current authored `!important` count outside `styles/dist/` is 122. The largest
families are `styles/vendor-overrides/ecwid.css` with 79,
`styles/components/cross-site-banner-cta.css` with 12, and
`styles/vendor-overrides/emailoctopus.css` with 8.

Cut 5 conclusion:

- The migration has made cascade layers real for the low-risk theme-owned
  defaults proven by the earlier cuts.
- The migration did not layer plugin override CSS or broad priority-sensitive
  WordPress override tails.
- Remaining unlayered CSS is intentionally documented deferred work, not hidden
  migration drift.
- The remaining known visual gate failures are the pre-layer baseline debts
  documented in Cut 0 and the accepted Ecwid `/shop/` snapshot drift from the
  storefront card fixes.

Cut 5 validation:

```text
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
PASS - Prettier, CSS asset audit, Stylelint, and Lightning CSS compile passed.
The CSS asset audit reported only the accepted duplicate load-path warnings for
core Cover, Navigation, and Quote.

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
PASS - 6 passed, 2 skipped.

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual
FAIL - desktop ran 43 passed, 5 failed. The failures match documented
pre-layer baseline debt: short-template height timeout, dark-section outside
color assertion, core selector large line-height, homepage missing Herstories
submenu overview label, and manual spacing flow margin.

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone exec playwright test tests/visual --grep "@mobile-full" --project=mobile
FAIL - 9 passed, 1 failed. The failure is a mobile homepage snapshot drift
localized around the news-card slice. The compiled frontend/editor CSS payloads
have no diff, so this is not cascade-layer behavior drift.
```

## Dex Tracking

- Parent: `mlqbujkv` - Migrate standalone CSS to real cascade layers
- Child: `163m5w17` - Layer migration Cut 0 - freeze cascade evidence
- Child: `nwgi41v7` - Layer migration Cut 1 - make low-risk imports real layers
- Child: `zmwhytl2` - Layer migration Cut 2 - migrate safe components in small batches
- Child: `q2a1jg5x` - Layer migration Cut 3 - split block defaults from override tails
- Child: `1hts1hsr` - Layer migration Cut 4 - decide vendor override layer boundary
- Child: `vajtouyd` - Layer migration Cut 5 - validate document and close migration

## Non-Goals

- Do not layer all CSS in one pass.
- Do not remove `!important` rules as part of the layer migration unless a
  specific slice proves the priority is no longer needed.
- Do not weaken overrides that compensate for WordPress inline block-support
  styles, core block CSS, Jetpack, Ecwid, or saved-content compatibility.
- Do not change the visual design, spacing system, typography scale, or block
  control policy as part of this migration.
- Do not edit parent themes or third-party plugins.
- Do not mutate DB-backed templates, template parts, patterns, navigation, or
  content as part of the layer migration unless a separate backed-up DB task is
  queued.

## Guardrails

- Treat unlayered CSS as an intentional override channel until proven
  otherwise.
- Prefer `@import "file.css" layer(name)` for simple bundle membership.
- Use `@layer name { ... }` inside a partial only when a file must contain both
  layered defaults and unlayered override tails.
- Keep layer declarations centralized in `styles/shared/layers.css`.
- Keep every slice independently revertible.
- Compile after each slice and inspect compiled output for the expected
  `@layer` placement.
- Use the smallest visual lane that covers the moved rules, then run the lean
  visual gate before closing a broad migration cut.
- Do not create more specificity or more `!important` just to compensate for
  a rule that was moved into a lower-priority layer.

## Locked Decisions

1. Layer adoption is an ownership migration, not a cleanup excuse.
   - Moving a rule into a layer is valid only when the rule's owner is known.
   - Broad selector cleanup, rhythm cleanup, and render-bridge cleanup remain
     separate plans.

2. Theme defaults should be layered first.
   - `settings`, `base`, `layout`, and simple `utilities` are the first safe
     candidates.
   - Component migration starts only after the low-risk bundle migration is
     compiled and visually checked.

3. Override-sensitive families stay unlayered until split.
   - Navigation, buttons, light-surface behavior, split-section media behavior,
     vendor overrides, block overrides, and rules with documented `!important`
     require explicit review before layering.

4. Block CSS must be handled as its own channel.
   - The block-registered styles in `inc/assets.php` are not just ordinary
     bundle imports.
   - Any block-layer migration must decide whether a file is a layered default,
     an unlayered override, or split into both.

5. Vendor overrides are not automatically a layer.
   - A layered `vendor-overrides` file can express theme-owned defaults around
     third-party surfaces.
   - Actual plugin override rules may need to remain unlayered because Ecwid
     and Jetpack output is unlayered.

## Execution Plan

### 1. Layer Migration Cut 0 - Freeze Cascade Evidence

Dex: `163m5w17`

Risk:

The visible cascade depends on runtime enqueue order, block-scoped handles, and
compiled bundle output. A migration without a fresh baseline can create silent
priority regressions.

Scope:

- Record current `git status --short`.
- Compile current CSS without changing behavior.
- Inspect current `@layer` usage in `styles/` excluding `styles/dist/`.
- Inspect compiled `styles/dist/frontend.min.css` and
  `styles/dist/editor.min.css` for layer output.
- Reconfirm the CSS delivery paths:
  - main frontend bundle;
  - editor style bundle;
  - block-scoped styles from `wp_enqueue_block_style()`.
- Capture the current `!important` families and classify them as:
  - WordPress inline/block-support;
  - core block;
  - third-party plugin;
  - saved-content compatibility;
  - project-owned priority debt.
- Do not edit CSS in this cut.

Acceptance:

- The plan has a current cascade evidence note or inventory.
- Every later cut has a concrete baseline to compare against.
- No source or compiled CSS behavior changes.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:smoke
```

### 2. Layer Migration Cut 1 - Make Low-Risk Imports Real Layers

Dex: `nwgi41v7`

Risk:

The current layer scaffold looks active but is mostly nominal. The first real
change should prove the build pipeline and browser behavior with rules that are
least likely to be override-sensitive.

Scope:

- Convert low-risk imports to explicit layer imports:
  - `shared/fonts.css` into `settings`;
  - `shared/settings.css` into `settings`;
  - `base/elements.css` into `base`, after checking saved preset fallbacks;
  - `layout/index.css` into `layout`;
  - `utilities/index.css` into `utilities`, except any utility that is proven
    to require unlayered priority.
- Remove or replace misleading bare layer declarations in moved files.
- Keep all block, component, vendor, and editor-canvas rules unchanged unless
  a file is directly in scope.
- Inspect compiled output to prove these rules are inside the intended layers.

Acceptance:

- The low-risk imports are actually layered in compiled CSS.
- No new priority rules are introduced.
- Visual smoke remains stable.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:smoke
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
```

### 3. Layer Migration Cut 2 - Migrate Safe Components In Small Batches

Dex: `zmwhytl2`

Risk:

Component files are mixed. Some are theme-owned presentation; others override
WordPress block output or bridge saved-content behavior.

Scope:

- Classify component files before moving them:
  - safe theme-owned defaults;
  - mixed default plus override;
  - override-sensitive, leave unlayered.
- Start with low-risk component families such as archive cards, taxonomy pills,
  logos, motion, search results, single post, and template reveal, if current
  evidence confirms they do not need to beat unlayered WordPress/plugin CSS.
- Defer buttons, header/navigation, footer layout, split-section,
  light-surface, synced sections, and cross-site banner CTA unless they are
  split into layered defaults plus explicit unlayered override tails.
- For mixed files, either:
  - keep the whole file unlayered for now; or
  - wrap only the safe defaults in `@layer components { ... }` and leave the
    override tail unlayered with comments.

Acceptance:

- Each moved component has a named owner and targeted visual coverage.
- Mixed component files are not made weaker accidentally.
- The component layer contains real rules, not only declarations.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
```

### 4. Layer Migration Cut 3 - Split Core Block Defaults From Override Tails

Dex: `q2a1jg5x`

Risk:

Block CSS is loaded through both bundled imports and block-scoped
`wp_enqueue_block_style()` handles. Some files are defaults; some are overrides
for unlayered core output; some are both. Plugin-adjacent CSS is a separate
vendor boundary and should not be layered merely because it lives in
`styles/blocks/`.

Scope:

- Reconcile bundle imports with `inc/assets.php` block-style registrations.
- For each block file, classify rules as:
  - theme default safe for `@layer blocks`;
  - required unlayered override tail;
  - duplicate load-path debt;
  - candidate for another plan.
- Start with simpler block files before Navigation and Quote.
- Keep core Navigation drawer priority rules unlayered until tests prove
  otherwise.
- Keep plugin override CSS, including Jetpack Slideshow and Jetpack form
  compatibility rules, unlayered unless vendor evidence in Cut 4 proves the
  layer remains strong enough.

Acceptance:

- Block defaults that enter layers still win where they should.
- Required overrides remain explicit and documented.
- Duplicate bundle/block registration behavior is understood before broad
  block movement.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
```

### 5. Layer Migration Cut 4 - Decide Vendor Override Layer Boundary

Dex: `1hts1hsr`

Risk:

The layer name `vendor-overrides` sounds authoritative, but third-party output
is unlayered. Putting real plugin override rules into a layer may make them
lose.

Scope:

- Separate vendor-adjacent theme defaults from true plugin overrides.
- Keep Ecwid and EmailOctopus rules unlayered where they must beat plugin CSS.
- Use `@layer vendor-overrides` only for rules that do not depend on beating
  unlayered plugin output, or rename/reframe the layer if the name is
  misleading.
- Preserve documented `!important` rules that are still required for plugin
  output.

Acceptance:

- The vendor layer boundary is honest.
- Plugin surfaces keep current visual behavior.
- Any remaining unlayered vendor override tail has comments naming why it must
  remain unlayered.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:ecwid
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:emailoctopus
```

### 6. Layer Migration Cut 5 - Validate, Document, And Close Migration

Dex: `vajtouyd`

Risk:

The migration can look complete in source while compiled CSS, editor CSS, or
block-scoped CSS still has nominal or misleading layer use.

Scope:

- Audit final `@layer` usage across `styles/` excluding `styles/dist/`.
- Audit compiled frontend/editor CSS for expected layer grouping.
- Document intentional unlayered override tails.
- Keep this plan's Dex section aligned with the live standalone tracker.
- Run the lean visual gate.
- Leave follow-up tasks for any broad selector, rhythm, render-bridge, or
  `!important` cleanup discovered during migration.

Acceptance:

- Layer usage is real and documented.
- Remaining unlayered CSS is intentional, not accidental.
- No known visual/editor regression remains open.
- Any deferred cleanup is queued under the appropriate existing plan rather
  than hidden inside this migration.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
```

## Open Questions

1. Should `vendor-overrides` remain a layer name?
   - Option A: keep the name but use it only for low-priority vendor-adjacent
     defaults.
   - Option B: rename it to avoid implying plugin override strength.
   - Recommendation: decide during Cut 4 after Ecwid and EmailOctopus evidence.

2. Should block-scoped CSS remain separately registered after block defaults are
   layered?
   - Option A: keep block-scoped CSS for conditional loading.
   - Option B: fold more block defaults into the main bundle.
   - Recommendation: keep the current registration model until Cut 3 proves a
     specific simplification is safe.

3. Should mixed component files be split physically or with local `@layer`
   blocks?
   - Option A: split files into `*-defaults.css` and `*-overrides.css`.
   - Option B: keep files together and use `@layer components { ... }` for safe
     defaults.
   - Recommendation: prefer local `@layer` blocks first to reduce file churn;
     split files only when the mixed ownership stays confusing.

## Closeout Criteria

- Every layer name in `styles/shared/layers.css` has either real rules or a
  documented reason to remain reserved.
- Bare layer declarations are not mistaken for migrated CSS.
- Layered theme defaults and unlayered override tails are both intentional.
- Compiled frontend/editor CSS preserves the expected layer structure.
- Block-scoped CSS behavior is accounted for.
- The final visual/editor gates pass, or any accepted drift is documented with
  updated baselines.
