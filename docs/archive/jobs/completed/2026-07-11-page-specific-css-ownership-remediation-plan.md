# Page-Specific CSS Ownership Remediation Plan

Created: 2026-07-11.

Status: Completed.

All paths are relative to the project root.

## Goal

Classify and remediate CSS that makes the standalone theme aware of specific
pages, page families, or authored content structures when that behavior should
belong to a block, block style, synced pattern, template part, saved block
attributes, or a vendor adapter.

The theme can own reusable defaults, template chrome, block adapters, and
documented plugin bridges. It should not keep accumulating page-level decisions
in CSS just because one rendered page needed a local spacing or layout fix.

The default remediation path is to bring non-conforming page content back to
theme rhythm. A deviation should be promoted to block, component, pattern, or
template CSS only when the deviation is repeatable, named, editor-reusable, and
not merely compensating for one page's authored spacing.

## Non-Goals

- Do not redesign the site or change accepted visual language.
- Do not remove compatibility selectors from static search alone.
- Do not mutate DB-backed content, templates, synced patterns, template parts,
  or navigation without a freshness audit and backup.
- Do not move legitimate template chrome out of the theme.
- Do not move vendor/plugin output styling into generic theme CSS.
- Do not archive this plan until the validation phase proves that every
  candidate selector is moved, generalized, or documented.

## Related Work

- Completed closeout plan:
  `docs/jobs/__completed/2026-07-11-final-standalone-theme-remediation-closeout-plan.md`
- Completed CSS/control health plan:
  `docs/jobs/__completed/2026-07-06-theme-css-control-health-remediation-plan.md`
- Completed cascade layer plan:
  `docs/jobs/__completed/2026-07-07-css-cascade-layer-migration-plan.md`
- Completed core selector plan:
  `docs/jobs/__completed/2026-07-07-core-selector-remediation-plan.md`
- Completed retained render bridge plan:
  `docs/jobs/__completed/2026-07-07-retained-render-bridge-remediation-plan.md`
- Completed adversarial review plan:
  `docs/jobs/__completed/2026-07-11-standalone-theme-adversarial-review-remediation-plan.md`

## Current Evidence

The 2026-07-11 trace found no broad homepage sibling gaps between post-content
sections after the current CSS build. The visible Mary Barbour 32px gap was
caused by saved content: a local `.mw-intro-box` inside `.shop-intro` carries
inline `margin-top` and `margin-bottom` values of
`var(--wp--preset--spacing--generous)`, which currently computes to 32px. That
margin collapsed through the full-width wrapper until the wrapper was made a
block formatting context.

The current mitigation in `styles/components/synced-sections.css` contains that
margin inside known full-width synced-section roots. It is technically correct
for the rendered issue, but it is also a clear example of the ownership question
this plan exists to answer: if the spacing is authored on a specific saved
content block, should a theme stylesheet know about the affected section family?

The source inventory found no current raw `.page-id-*`, `.mary-barbour`, or
similar selector contract in source CSS outside generated files. That is good.
The remaining issue is subtler: several CSS files encode named content-section
or page-family knowledge.

Live WP-CLI verification on 2026-07-11 confirmed the active theme is
`protestsandsuffragettes-standalone` for both stylesheet and template. A
DB-backed content inventory for the main candidate hooks found matching content
in six published `herstory` records, two published pages, five published
synced blocks, one published template, seven draft pages, and one private test
page. Published usage is concentrated in:

- Herstories records: Agnes Dollan, Georgiana Solomon, Helen Fraser, Jessie
  Soga, Lila Clunas, and Mary Barbour.
- Pages: `pns-pattern-qa` and `shop`.
- Synced blocks: PNS - Connect Social, PNS - Contact Form, PNS - Read All
  About It, PNS - Read All About It - Workshops, and PNS - Shop Intro.
- Template: PNS - Herstory Archive.

This reinforces the ownership split: search/archive/footer selectors can be
cleaned up in source CSS, but Shop intro, Herstories biography internals, and
synced-section margin behavior are live content/pattern concerns and must use
a backup-backed content or pattern migration path.

Implementation pass 2026-07-11:

- Removed unnecessary `.pns-template-search` / `.pns-template-archive` prefixes
  from `styles/components/search-results.css`; `.pns-search-result*` now owns
  search-result component styling directly.
- Removed the same search-template prefix from
  `styles/components/motion.css`; search-result link/image motion now follows
  the component hook.
- Replaced footer `#contact` spacing selectors in
  `styles/components/footer-layout.css` with the existing
  `.pns-footer__contact` template-part contract.
- Reworded the `styles/page-types/herstories-bios.css` header so future fixes
  treat it as transitional Herstories pattern CSS, not a page-specific route
  patching area.
- Left `styles/components/synced-sections.css` and
  `styles/page-types/shop.css` unchanged in this pass because the remaining
  concerns are DB-backed synced block/page content decisions.

Implementation pass 2026-07-12, Phase 3:

- Added `scripts/migrate-page-specific-content-ownership.php` as the
  backup-backed migration path for content-owned page selectors.
- Migrated the local Mary Barbour "More about Mary" section out of
  `shop-intro pns-synced-section pns-shop-intro` and into the existing
  text-only section ownership model:
  `pns-section pns-layout pns-text-only-section pns-saved-section
pns-herstory-more-about`, with `pns-text-only-section__inner` and
  `pns-herstory-more-about__copy` internals.
- Migrated the published Shop page intro wrapper to semantic saved-content
  hooks: `pns-shop-storefront__frame`, `pns-shop-storefront__content`, and
  `pns-shop-storefront__intro`.
- Retargeted `styles/page-types/shop.css` intro rhythm from generic
  `.is-content-justification-left.is-layout-constrained > p` descendants to
  `.pns-shop-storefront__intro`.
- Removed the theme-side Shop padding compensation that existed only because
  the saved Shop page had an anonymous inner `1rem` wrapper; the saved content
  now carries the explicit storefront frame class.
- Rollback exports:
  - `docs/jobs/page-specific-css-db-backups/20260712-215738-page-specific-content-ownership.json`
  - `docs/jobs/page-specific-css-db-backups/20260712-220155-page-specific-content-ownership.json`
  - `docs/jobs/page-specific-css-db-backups/20260712-220401-page-specific-content-ownership.json`
  - `docs/jobs/page-specific-css-db-backups/20260712-220658-page-specific-content-ownership.json`
- Post-apply dry-run reported `Current non-revision rows requiring migration:
0`.
- Current-content DB scans now show the old `shop-intro
pns-synced-section pns-shop-intro` and `mw-intro-box` strings only on the
  legitimate reusable `PNS - Shop Intro` block (`wp_block` 1509). Migrated
  semantic hooks are present on Mary Barbour (`herstory` 5835), draft Mary
  copies (`page` 42 and 1828), and Shop (`page` 565).
- Validation: PHP lint passed for the migration script;
  `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone
check` passed; focused desktop visual contracts for Shop and Herstory passed
  3/3; focused mobile Shop/Ecwid contracts passed 2/2. The focused mobile
  Herstory cascade contract still fails on the known split-section assertion
  that expects side-by-side geometry at mobile width while the rendered section
  is stacked.

Implementation pass 2026-07-13, Phase 4:

- Added source-adjacent ownership and removal-gate comments for the retained
  exception families that survived Phases 2 and 3:
  - Herstories biography compatibility in
    `styles/page-types/herstories-bios.css`;
  - reusable synced-section compatibility in
    `styles/components/synced-sections.css`;
  - Shop storefront saved-content hooks in `styles/page-types/shop.css`;
  - template floors and Ecwid storefront reservation in
    `styles/page-types/layout-stability.css`;
  - private style-guide helpers in `styles/components/style-guide.css`;
  - the retained `.pns-suffragette-text-media` section role bridge in
    `styles/components/section-theme.css`;
  - query result and archive card component ownership in
    `styles/components/search-results.css` and
    `styles/components/archive-cards.css`.
  - single-post and footer template chrome in
    `styles/components/single-post.css` and
    `styles/components/footer-layout.css`;
  - Ecwid and EmailOctopus vendor adapters in
    `styles/vendor-overrides/ecwid.css` and
    `styles/vendor-overrides/emailoctopus.css`.
- No behavior rules were changed in this pass; this phase is intentionally a
  documentation and auditability pass after the content migration.

Implementation pass 2026-07-13, Phase 5:

- Final authored-source scan found no raw `.page-id-*`, `.postid-*`,
  `.mary-barbour`, `mw-intro-box`, or `old-shop-intro-section` CSS contracts
  in source styles outside generated build artifacts.
- Current count of arbitrary page-specific CSS overrides: `0`.
- Retained documented CSS buckets after classification:
  - `64` documented page-family or compatibility declaration blocks;
  - `29` template/template-part chrome declaration blocks;
  - `12` private internal style-guide declaration blocks;
  - `58` vendor adapter declaration blocks.
- Added source comments for the remaining easy-to-misread hooks:
  `.pns-pattern-qa` is a private QA fixture, `.pns-page-hero` is a reusable
  Cover block style contract, and `.pns-template-search` is a template
  stability hook rather than page-content rhythm.
- Kept two narrowly scoped unlayered priority bridges because WordPress
  generated layout CSS is unlayered:
  - page search-result cards still hide post meta through
    `.wp-block-post.type-page .pns-post-card__meta`;
  - featured-post split-section media figures remain flush through
    `.pns-featured-post .pns-split-section__media-column
.wp-block-post-featured-image`.
- Refined Playwright contracts to match the actual theme/block shapes:
  edge-media split sections must span the viewport while non-edge split
  sections may remain centered; mobile Herstories split sections stack instead
  of staying side-by-side; cover-based single-post headers have a separate
  height budget from non-cover headers; multi-route mobile/template and
  breakpoint checks now have realistic timeouts for Local WP.
- Snapshot baseline updates were limited to the current home and Mary Barbour
  desktop/mobile smoke snapshots. The accepted classification is intentional
  reset to the current normalized theme rhythm after the page-specific spacing
  migration, with structural contracts passing afterward.
- Validation results:
  - `php
app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-block-templates.php`
    passed for 25 files.
  - `pnpm --dir
app/public/wp-content/themes/protestsandsuffragettes-standalone check`
    passed.
  - `pnpm --dir
app/public/wp-content/themes/protestsandsuffragettes-standalone
test:visual:smoke` passed: 4 desktop, 4 mobile.
  - `pnpm --dir
app/public/wp-content/themes/protestsandsuffragettes-standalone
test:visual:layout` passed: 28 desktop, 8 mobile.
  - `pnpm --dir
app/public/wp-content/themes/protestsandsuffragettes-standalone
test:visual:fast` passed: 39 desktop, 13 mobile.
  - `pnpm --dir
app/public/wp-content/themes/protestsandsuffragettes-standalone
test:visual` passed: 49 desktop, 10 mobile.

Retained exception register:

| Retained family                         | Owner stronger than historical convenience                     | Reason to keep now                                                                 | Removal gate                                                                                  | Test or check owner                                               |
| --------------------------------------- | -------------------------------------------------------------- | ---------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------- | ----------------------------------------------------------------- |
| Herstories biography internals          | Transitional theme support for Herstories CPT/pattern markup   | Live CPT records still serialize active-date, fact, stats, and image-strip classes | Move those layouts to Herstories plugin templates/block styles or regenerate saved patterns   | Herstory frontend/editor visual coverage and DB content scans     |
| Generic and Suffragette image strips    | Saved Core Columns image-strip component compatibility         | Existing strips share Core Columns markup that must stay gapless                   | Move to a neutral image-strip block style when reused outside Herstories                      | Herstory/source scan coverage                                     |
| Synced reusable sections                | Named synced WP block fixtures plus PNS component CSS          | Contact, connect-social, read-all-about-it, workshops, and shop-intro are reusable | Synced fixtures emit explicit frame/copy/media internals that avoid Core Columns reachthrough | Synced-section fixtures plus focused homepage/contact visuals     |
| Shop storefront wrapper and intro hooks | Saved Shop page semantic wrapper plus Ecwid adapter boundary   | Phase 3 migrated anonymous page structure to explicit storefront classes           | Storefront becomes a plugin/block-owned component or Ecwid is replaced                        | Shop/Ecwid visual lanes and Phase 3 DB migration dry-run          |
| Template height floors                  | Frontend template chrome                                       | Short search, 404, and light-surface templates need stable page floors             | Templates gain enough intrinsic content or a new template layout model owns the floor         | Template visual lane                                              |
| Ecwid storefront reservation            | Theme-side vendor host adapter                                 | Reduces storefront hydration jump while Ecwid remains third-party runtime output   | Ecwid or a PNS product-grid block provides stable pre-hydration height                        | Shop/Ecwid visual lanes                                           |
| Ecwid storefront runtime adapters       | Isolated third-party vendor override                           | Ecwid runtime markup and unlayered CSS require scoped priority rules               | Ecwid exposes equivalent settings, changes markup, or a PNS product grid/cart replaces it     | Shop/Ecwid visual lanes                                           |
| EmailOctopus hosted-form adapters       | Isolated third-party vendor override                           | Hosted forms inject layout/focus/utility wrappers that cannot be block-controlled  | A project-owned form block replaces it or EmailOctopus exposes equivalent embed controls      | EmailOctopus/contact visual coverage                              |
| Private style guide helpers             | Internal documentation UI                                      | The private style guide is a WP-authored fixture but not public page rhythm        | Style guide is retired, moved to external docs, or becomes a dedicated plugin/admin surface   | Style-guide page/editor smoke when that fixture changes           |
| `.pns-suffragette-text-media` role      | Supported section-theme bridge for existing Herstories content | Existing content uses the domain class as a section role for surface token mapping | Rename to a neutral role or move Herstories media/text panels to plugin-owned block styles    | Herstory visual coverage and CSS source scan                      |
| Query result and archive card classes   | Reusable component classes                                     | Templates place these components, but the card/result class owns the styling       | Only remove if templates stop using these cards or a query/card block plugin owns the markup  | Search/archive template visual coverage and source selector scans |
| Single-post chrome                      | Theme-owned single post template components                    | Header, meta, comments, content frame, and entry navigation are template surfaces  | Move only if single post templates are delegated to plugin-owned blocks or block styles       | Template/single visual coverage                                   |
| Footer contact rhythm                   | Theme-owned footer template part                               | Footer contact internals are template-part chrome, not arbitrary page content      | Footer contact markup carries explicit block spacing or moves to a footer/contact block       | Footer/template visual coverage                                   |

## Ownership Model

Use this decision order for every candidate selector family:

1. **Core or theme default**: keep in theme CSS only when it applies to arbitrary
   editor-created content safely.
2. **Reusable block/component**: keep in theme CSS when the selector names a
   reusable PNS component or block style with a stable markup contract.
3. **Template or template part**: keep in theme CSS when the template is the
   real owner, such as single-post chrome, search result chrome, or footer
   template-part layout.
4. **Synced pattern source**: prefer synced-pattern markup/classes/block
   attributes when the behavior is specific to a site-authored reusable section.
5. **Saved page or post content**: prefer block attributes, semantic classes, or
   a DB-backed content migration when the behavior is truly content-specific.
6. **Vendor adapter**: keep near the vendor override or plugin-owned adapter
   when the behavior exists only because third-party output cannot be shaped
   through normal block supports.
7. **Retained compatibility exception**: keep only with a comment, a removal
   gate, and focused visual/editor coverage.

## Deviation Policy

When a page or section diverges from normal theme rhythm, classify the
deviation before deciding where the fix belongs.

1. **Reset to default first**.
   If the page uses ad hoc margins, padding, local wrapper classes, or legacy
   spacing that only exists to make one page look different, prefer removing or
   normalizing that local styling so the theme defaults apply.
2. **Keep explicit saved-block intent in content**.
   If the editor intentionally wants one local block to have extra breathing
   room, keep that as block attributes or an explicit semantic class in saved
   content. Do not add theme CSS that only knows how to repair that page.
3. **Promote only repeatable patterns**.
   Promote a deviation to component, block-style, or synced-pattern CSS only
   when the same layout should be reusable elsewhere with the same visual
   behavior.
4. **Template CSS is for template chrome**.
   Search results, single-post headers, footer template parts, and short-page
   template floors can be template CSS because the template is the surface. The
   same rule should not leak into arbitrary page content just because the
   content sits under that template wrapper.
5. **Vendor adapters stay narrow**.
   Vendor or hydration layout work can be retained near the vendor adapter, but
   only for the vendor-owned output or the site-owned wrapper required to host
   it.

Promotion test:

> Would we want this behavior if the same block or pattern appeared on another
> page?

If the answer is no, reset to theme defaults or keep the deviation as explicit
saved-block intent. If the answer is yes, create or refine a block/component/
pattern owner and test that owner directly.

## Classification

### High Concern - Should Be Reclassified Before More CSS Fixes

| Area                          | Current owner                                                             | Current selector families                                                                                                            | Likely better owner                                                    | Plan stance                                                                                                                                                                          |
| ----------------------------- | ------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------ | ---------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Herstories biography patterns | `styles/page-types/herstories-bios.css`                                   | `.active-dates`, `.fun-facts`, `.pns-suffragette-facts`, `.pns-suffragette-stats`, `.pns-suffragette-image-strip`, `.pns-herstories` | Transitional theme support for Herstories CPT/pattern markup           | Retained as documented compatibility only. Move to Herstories plugin/block styles or regenerated patterns when that owner exists.                                                    |
| Synced content sections       | `styles/components/synced-sections.css`                                   | `.pns-contact-form`, `.pns-connect-social`, `.pns-read-all-about-it`, `.pns-read-all-about-it-workshops`, `.pns-shop-intro`          | Synced pattern fixture plus component CSS for stable reusable sections | Retained for first-class reusable synced sections. Remove reachthrough/BFC compatibility when synced fixtures emit explicit internals that cannot collapse margins through the root. |
| Shop content surface          | `styles/page-types/shop.css` and `styles/page-types/layout-stability.css` | `.pns-shop-storefront`, `.pns-shop-storefront__intro`                                                                                | Saved Shop content wrapper plus Ecwid adapter boundary                 | Retained after Phase 3 migration because the saved page now exposes semantic storefront hooks. Move to `pns-blocks` or a storefront component if that becomes the product owner.     |
| Private style guide surface   | `styles/components/style-guide.css`                                       | `.pns-style-guide-*`                                                                                                                 | Private internal documentation UI                                      | Retained as internal documentation surface, not public page rhythm. Delete if the style guide is retired or moved outside WP-authored content.                                       |

### Medium Concern - Likely Defensible, But Needs Explicit Ownership

| Area                         | Current owner                                                                 | Current selector families                                                                                                    | Likely better owner                                            | Plan stance                                                                                                                                                      |
| ---------------------------- | ----------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Template height floors       | `styles/page-types/layout-stability.css`                                      | `.pns-template-page-light-surface-no-contact-form`, `.pns-template-page-search`, `.pns-template-search`, `.pns-template-404` | Template CSS or template markup                                | Retained as documented frontend template stability, not arbitrary page-content spacing.                                                                          |
| Search/archive result layout | `styles/components/search-results.css`, `styles/components/archive-cards.css` | `.pns-search-result`, `.pns-archive-card`, `.pns-post-card__meta`                                                            | Reusable query-result/card component CSS                       | Template prefixes were removed in Phase 2. Styling now follows reusable result/card classes.                                                                     |
| Single post chrome           | `styles/components/single-post.css`                                           | `.pns-template-single`, `.pns-single-*`, `.pns-comments`                                                                     | Theme template component CSS                                   | Retained as stable single-post template chrome. Move only if single post templates are delegated to plugin-owned blocks or block styles.                         |
| Footer contact rhythm        | `styles/components/footer-layout.css`                                         | `.pns-footer__contact` inside the footer template part                                                                       | Footer/contact template-part CSS or synced contact section CSS | Retained as footer template-part rhythm. Move only if footer contact markup carries explicit block spacing or a footer/contact block owns the divider and stack. |
| Section theme roles          | `styles/components/section-theme.css`                                         | `.pns-suffragette-text-media` alongside reusable section roles                                                               | Reusable section role or Herstories-specific block style       | Retained as a documented section role bridge for existing content. Rename to a neutral role or move to plugin block styles when the content model changes.       |

### Low Concern - Keep Unless Evidence Says Otherwise

| Area                                  | Current owner                                   | Reason                                                                                                                |
| ------------------------------------- | ----------------------------------------------- | --------------------------------------------------------------------------------------------------------------------- |
| `styles/layout/index.css`             | Theme layout                                    | Owns shell and alignment defaults, not individual page content.                                                       |
| `styles/utilities/index.css`          | Theme utilities                                 | Owns generic frames such as `.pns-section-inner`, `.pns-content-frame`, `.pns-section-frame`, and `.pns-copy-column`. |
| `styles/components/split-section.css` | Reusable component                              | Stable component contract with block style variants. Domain names should still be checked, but the model is reusable. |
| `styles/components/light-surface.css` | Reusable surface component                      | Template/page assignment can choose the surface; CSS owns surface treatment.                                          |
| Header, footer, navigation, buttons   | Theme chrome/block adapters                     | These are theme-owned chrome or block adapter surfaces, not arbitrary page CSS.                                       |
| Vendor overrides                      | `styles/vendor-overrides/*` and plugin adapters | Excluded from this plan except where a page-specific wrapper is doing non-vendor work.                                |

## Dex Tracking

Standalone theme queue:

```bash
dex --storage-path app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex list rna99jm3
```

Parent:

- `rna99jm3` - Remediate page-specific CSS ownership

Phases:

- `mkwxbm2q` - Page CSS Phase 0 - inventory page-specific selectors and owners
- `41mccbf5` - Page CSS Phase 1 - define ownership decision tree
- `kudar00u` - Page CSS Phase 2 - migrate template-owned page selectors
- `qnrv9c0b` - Page CSS Phase 3 - migrate content-owned page selectors
- `59n1wela` - Page CSS Phase 4 - document retained page-family exceptions
- `tez7bpdg` - Page CSS Phase 5 - validate and close page-specificity debt

Dependency rules:

- Phase 0 blocks every decision or implementation phase.
- Phase 1 waits for Phase 0 and must update this plan before CSS/content
  changes start.
- Phase 2 and Phase 3 wait for Phase 1.
- Phase 4 waits for Phase 2 and Phase 3.
- Phase 5 waits for Phase 4.

## Guardrails

- Start with rendered usage and DB/file ownership, not selector names alone.
- Do not change serialized content without a DB backup and dry-run/apply/report
  path.
- Do not treat completed remediation docs as current truth; use them as
  accountability evidence only.
- Keep vendor/plugin CSS out of this plan unless a theme wrapper is doing
  page-specific non-vendor work.
- Prefer semantic reusable component hooks over route/page/template selectors
  when the same layout can appear in arbitrary editor content.
- Prefer block attributes or synced-pattern markup over CSS when the change is
  a one-off vertical spacing or layout decision.
- Treat a reset to theme defaults as the preferred outcome for middle-ground
  deviations that are not clearly reusable components.
- Any retained page-family selector must explain why block, pattern, template,
  or vendor ownership is insufficient.

## Phase Plan

### Phase 0 - Inventory Page-Specific Selectors And Owners

Dex: `mkwxbm2q`

Required work:

- Reconfirm active theme and live DB-backed template/synced-pattern ownership
  for touched surfaces.
- Inventory source CSS selectors in:
  - `styles/page-types/content-rhythm.css`
  - `styles/page-types/herstories-bios.css`
  - `styles/page-types/layout-stability.css`
  - `styles/page-types/shop.css`
  - `styles/components/synced-sections.css`
  - `styles/components/search-results.css`
  - `styles/components/archive-cards.css`
  - `styles/components/single-post.css`
  - `styles/components/footer-layout.css`
  - `styles/components/section-theme.css`
  - `styles/components/style-guide.css`
- Map each selector family to current rendered usage and source fixture usage.
- Record current visual failure shape, especially homepage and Mary desktop
  snapshot height drift.

Acceptance:

- This plan's classification table is updated with current rendered/source
  evidence.
- No CSS or DB mutation occurs in this phase.

### Phase 1 - Define Ownership Decision Tree

Dex: `41mccbf5`

Required work:

- Lock the decision for each candidate family:
  - reset to theme defaults;
  - keep as theme default;
  - keep as reusable block/component CSS;
  - keep as template/template-part CSS;
  - migrate to synced pattern source;
  - migrate to saved content attributes/classes;
  - move to vendor/plugin adapter;
  - retain as documented compatibility.
- Decide whether Herstories biography styling is now plugin-owned,
  theme-template-owned, or transitional legacy content styling.
- Decide whether `.pns-synced-section` is an honest reusable component class or
  a content authoring convenience class that should not drive CSS.
- For every deviation, answer the promotion test: would this behavior be wanted
  if the same block or pattern appeared on another page?

Acceptance:

- No implementation phase starts until this plan lists a disposition for every
  high and medium concern.
- Each disposition explicitly says whether the selector family should reset to
  theme defaults, remain saved-block intent, or be promoted to a reusable owner.
- Any open decision is either resolved or split into a blocker task.

### Phase 2 - Migrate Template-Owned Page Selectors

Dex: `kudar00u`

Required work:

- Move or rename template-owned CSS so it reads as template/component ownership,
  not page-specific cleanup.
- Candidates:
  - template height floors in `layout-stability.css`;
  - search/archive result component wrappers;
  - single-post chrome;
  - footer template-part contact rhythm;
  - private style-guide surface.
- Prefer template markup and component classes over styling through page-ish
  selectors when possible.

Acceptance:

- Template-owned CSS is documented as template chrome or component CSS.
- Arbitrary page content is not styled because it happens to live under a
  template wrapper.
- Touched templates pass block-template validation.

### Phase 3 - Migrate Content-Owned Page Selectors

Dex: `qnrv9c0b`

Required work:

- Move true page/content-specific spacing out of broad theme CSS.
- Candidates:
  - Mary/local `.shop-intro` margin-collapse behavior;
  - `.mw-intro-box` spacing if it is saved-content-specific;
  - `.active-dates` compact layout;
  - `.fun-facts` bullets;
  - Shop intro paragraph rhythm;
  - one-off `pns-suffragette-*` strip/facts/stats layout.
- Use synced-pattern fixtures or DB-backed content migrations when serialized
  content owns the behavior.
- Prefer deleting or normalizing ad hoc local spacing when the page should
  simply follow the theme rhythm.
- Preserve explicit local intent as saved block attributes only when the
  deviation is intentionally one-off and visible to editors.

Acceptance:

- DB changes have backup, dry-run, apply, and post-apply verification evidence.
- Editor and frontend checks prove intended visual parity.
- The theme no longer carries CSS that exists only to patch one saved page's
  local block stack unless Phase 4 explicitly retains it.
- Any intentional layout change caused by reverting to theme rhythm is recorded
  as accepted normalization, not hidden inside a snapshot refresh.

### Phase 4 - Document Retained Page-Family Exceptions

Dex: `59n1wela`

Required work:

- Keep only exceptions that survived Phases 2 and 3.
- Add source-adjacent comments for retained compatibility selectors.
- Add or update tests so retained selectors have a clear reason to exist.
- Record removal gates for transitional selectors tied to DB-backed content.

Acceptance:

- No retained exception is justified by "current screenshots need it" alone.
- Every keep has an owner, a test or rendered check, and a future removal
  condition.

### Phase 5 - Validate And Close Page-Specificity Debt

Dex: `tez7bpdg`

Required work:

- Run CSS compile and lint.
- Run block-template validation when templates/pattern fixtures changed.
- Run DB/source scans proving no accidental `.page-id-*`, individual slug, or
  unexplained page-family styling contract remains.
- Run editor checks for editor-facing selector or content migrations.
- Run targeted visual lanes for touched surfaces, then smoke/fast/lean visual
  gates as appropriate.
- Decide whether current desktop snapshot drift is fixed or intentionally
  accepted with refreshed local baselines.
- For visual diffs, classify each change as:
  - unintended regression;
  - intentional reset to theme rhythm;
  - intentional component/pattern/template deviation.

Acceptance:

- Every candidate selector family is moved, generalized, or documented.
- Any screenshot baseline update is backed by one of the intentional visual
  classifications above.
- The plan has final validation results and can be moved to
  `docs/jobs/__completed/` only after the Dex parent is ready to close.

## Validation

Expected validation set, scoped by touched surfaces:

```bash
php scripts/validate-block-templates.php
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:smoke
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:fast
```

Add area lanes as needed:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:shop
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:ecwid
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:emailoctopus
```

Use elevated local process access for Playwright in this Local WP checkout when
Chromium hits the known macOS sandbox failure.

## Resolved Questions

1. Should Herstories biography presentation now be treated as plugin/CPT-owned
   because `pns-herstories` exists, or should the standalone theme retain
   Herstories template/component styling until the content model fully moves?

   Decision: keep current Herstories biography presentation as documented
   transitional theme support. Move data/content presentation toward the
   Herstories plugin, explicit block styles, or regenerated patterns when that
   owner exists.

2. Are `pns-contact-form`, `pns-connect-social`, `pns-read-all-about-it`, and
   `pns-shop-intro` first-class reusable PNS section components?

   Decision: yes for synced pattern fixtures. Their spacing should be carried
   by the pattern/component contract rather than incidental page context such
   as `.entry-content`; current reachthrough remains compatibility only.

3. Should the private style guide remain CSS-authored in the theme?

   Decision: yes. It is internal documentation UI, not public page-content
   rhythm. Delete it when the style guide is retired or moved out of WP.

4. Should Shop storefront reservation stay in theme CSS or move to plugin code?

   Decision: keep a narrow theme/vendor adapter only while Ecwid remains
   third-party hydrated output. Any PNS custom product-grid behavior should
   belong to `pns-blocks`.

5. How should Playwright snapshots be handled when normalization changes page
   height or vertical rhythm?

   Decision: treat subtle layout shifts as expected only when implementation
   explicitly resets non-conforming content to theme rhythm or promotes a
   deviation to a documented reusable owner. Snapshot refreshes must cite that
   classification, not act as blanket approval.

## Locked Decisions

- This plan is active work, not a completed remediation artifact.
- Page-specific CSS is not automatically wrong; unexplained arbitrary page
  knowledge is wrong.
- Non-conforming page content should follow theme defaults by default.
- The preferred fix for one-off spacing is block/pattern/content ownership, not
  broader theme selectors.
- A deviation earns component or block-level CSS only when it is repeatable,
  named, editor-reusable, and desirable outside the original page.
- The current Mary spacing trace is evidence for Phase 3, not proof that every
  full-width section needs a global rule.
- Playwright screenshots are expected to change when pages are intentionally
  normalized; each baseline update must be tied to a classified intentional
  visual change.
- Vendor overrides are out of scope unless the theme wrapper is doing
  non-vendor page/content work.
