# Light Surface Theme Extraction Plan

Plan started on 2026-07-04.

All paths are relative to the project root.

## Purpose

Lift the current white-background text fixes out of one-off Ecwid vendor
overrides into a reusable light-surface theme contract.

The class and token language should be `light surface`, not `white surface`.
White may be the current background color for several surfaces, but the design
contract is readability on a light page or component surface: dark heading text,
secondary body text, intentional link color, and predictable embedded/vendor
text behavior.

This is adjacent to the existing `theme.json`/CSS rationalization and
Site/style-controls mop-up work. It exists because the recent Ecwid cart/product
fixes exposed a broader theme health issue: several generic templates still
inherit the site's purple body surface even though their content should read as
dark text on a light editorial surface.

## Problem Statement

The standalone theme currently has two competing models:

- Brand and campaign sections can intentionally use the purple site background
  with light foreground text.
- Store, blog, search, 404, archive, and other long-form or utility templates
  often need a light readable surface with dark/secondary text.

Recent Ecwid fixes had to patch individual runtime selectors:

- Store/product detail text on white storefront panels.
- Ecwid product descriptions on white product pages.
- Cart `You may also like` product-card titles that had dark parent links but
  retained white `-webkit-text-fill-color`.

Those fixes are valid as scoped vendor shims, but they are not generic enough
as the permanent model. The theme needs a reusable surface contract that normal
WordPress templates can adopt and Ecwid can consume through a small adapter.
The first implementation slice proves the light-surface variant. After that is
stable, the existing purple/brand section model should converge onto the same
underlying surface contract instead of continuing as a separate inferred model.

## In Scope

- Define a reusable light-surface CSS/token contract.
- Apply the contract to generic readable templates that should not inherit the
  purple body surface by default:
  - `404`
  - `search`
  - blog home/index
  - post archives
  - single blog posts
  - fallback/index templates where appropriate
- Refactor Ecwid overrides so they consume light-surface variables where
  practical.
- Converge the existing purple/brand section theme onto the same shared surface
  model after the light variant is proven, preserving current visuals as the
  compatibility baseline.
- Keep Ecwid-specific selectors only for real vendor/runtime quirks, especially
  `-webkit-text-fill-color`, form controls, minicart icons, product
  descriptions, and related/recommended product cards.
- Verify frontend and editor/template preview behavior.

## Out Of Scope

- Redesigning the visual language of existing bespoke PNS sections.
- Renaming established `pns-section` wrappers without a separate migration.
- Removing Ecwid vendor overrides that are still required by Ecwid runtime
  markup.
- Changing product/content copy.
- Refreshing visual references before changes are classified and accepted.
- Combining this work with generic legacy utility deletion.

## Guardrails

- Do not name the reusable class or token `white`. Use neutral light-surface
  naming so future off-white or tinted surfaces can use the same contract.
- Preserve existing accepted visuals unless a difference is explicitly
  classified and accepted.
- Audit rendered templates before editing: filesystem templates may not be the
  only live source if saved template or global-style records exist.
- Keep vendor quirks isolated. Ecwid can adapt to the surface contract, but the
  surface contract should not become an Ecwid-specific abstraction.
- Do not rewrite the purple section theme before the light-surface contract has
  been proven on generic templates and Ecwid adapter values.
- Do not broaden `!important` rules. Keep priority only where WordPress inline
  styles, core CSS, or third-party runtime CSS requires it.
- Make editor controls honest: if a template adopts the light surface, editor
  previews and relevant color/text controls should not imply a different
  unsupported behavior.
- DB mutations require a timestamped backup and rollback note.

## Proposed Ownership Model

| Surface                             | Preferred owner                                            | Notes                                                                     |
| ----------------------------------- | ---------------------------------------------------------- | ------------------------------------------------------------------------- |
| Shared surface contract             | Theme CSS/token contract                                   | Underlies light and purple/brand variants once convergence is proven.     |
| Light readable template surface     | Theme CSS/token contract                                   | Use neutral `light surface` naming.                                       |
| Purple/brand section surface        | Shared surface variables plus `pns-section` compatibility  | Preserve current visuals while removing inferred/duplicated ownership.    |
| Generic templates                   | Filesystem templates plus scoped template CSS              | Adopt the light-surface contract explicitly.                              |
| Long-form body text                 | Theme surface variables plus base/content rhythm           | Body text should default to secondary gray on light surfaces.             |
| Headings on light surfaces          | Theme surface variables plus existing Rubik heading system | Avoid inheriting white text from purple body defaults.                    |
| Links on light surfaces             | Theme surface variables and existing link affordance rules | Must remain visible and accessible.                                       |
| Ecwid storefront/cart/product pages | Ecwid adapter in `styles/vendor-overrides/ecwid.css`       | Consume light-surface variables, keep vendor selectors where unavoidable. |
| Editor previews                     | Editor CSS plus template markup/classes                    | Match frontend surface behavior.                                          |

## Dex Tracking

Dex task state is stored under the standalone theme root:

```text
app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex/tasks.jsonl
```

Use the tracker with the storage path explicitly:

```bash
dex --storage-path /Users/anachronistic/Local\ Sites/pns-stageing/app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex list --all
```

Parent task:

```text
ff1v95jm - Lift light surface theme out of Ecwid white-page overrides
```

Phase tasks:

| Phase | Dex ID     | Task                                     |
| ----- | ---------- | ---------------------------------------- |
| 0     | `irgcfez2` | Audit light-template and Ecwid surfaces  |
| 1     | `6p0yhuvb` | Define reusable light-surface contract   |
| 2     | `0ol6s4ba` | Adopt generic templates                  |
| 3     | `962lxi6s` | Refactor Ecwid overrides as adapter      |
| 4     | `f7epvec9` | Align editor controls and block previews |
| 5     | `raqquj84` | Converge light and purple surface models |
| 6     | `g8pdxigb` | Validate and retire duplicated overrides |

Related Dex tasks:

- `511irrlz` - parent rationalization rollout for standalone block controls and
  CSS authority.
- `d72fw0le` - Site Identity and Site Editor style-controls mop-up.
- `to9qfw83` - legacy utility class removal before designer handback.
- `hfux2nkg` - completed root overflow and vendor override debt phase that
  exposed the Ecwid compatibility work.
- `fyuxgot2` / `5e6hco3p` - Ecwid product grid block stabilization and final
  validation.

## Phase 0 - Audit Light-Template And Ecwid Surfaces

Goal: prove current ownership and contrast before implementation.

Audit targets:

- `/404` or a forced 404 route.
- `/search/` and an actual query route.
- Blog home/index template.
- Blog archive/category/tag templates.
- Single blog posts.
- Fallback `index.html` behavior.
- `/shop/`, product detail routes, `/shop/cart`, and cart recommendations.

Checks:

- Confirm active theme and template source.
- Check whether any saved `wp_template`, `wp_template_part`, or
  `wp_global_styles` row overrides the filesystem templates.
- Capture computed background/text/link/heading colors for each route.
- Classify each surface as:
  - already correct light surface,
  - should adopt light surface,
  - intentionally purple/dark campaign surface,
  - vendor adapter surface,
  - blocked by missing content/fixture state.
- Record which CSS or template file currently owns the surface.

Acceptance checks:

- No source behavior changes in this phase.
- Every target template has an ownership classification.
- Ecwid vendor quirks are separated from generic light-surface needs.

Phase 0 result:

- Active Local theme options are
  `stylesheet=protestsandsuffragettes-standalone` and
  `template=protestsandsuffragettes-standalone`.
- Active published `wp_global_styles` row for the standalone theme is `5256`;
  its content is still minimal:
  `{"version": 3, "isGlobalStylesUserThemeJSON": true }`.
- Published saved template rows exist for `404` (`3164`), `home` (`1029`),
  `single` (`5990`), `page` (`1028`), and `page-no-contact-form` (`4689`).
  Saved header and footer template-part rows also exist (`5936`, `5980`).
  Phase 2 must confirm whether each rendered route is using filesystem markup,
  saved markup, or a merged/overridden template before editing source or DB
  content.
- Runtime computed-style audit at desktop width found:
  - 404 renders on the purple body surface with white heading/body copy. Its
    saved template paragraph/button color classes are strongly tied to the
    current purple/white palette.
  - Search results render on the purple body surface. Query headings are white,
    while excerpts and links compute as dark text, creating mixed readability.
  - Blog home/news already renders a light surface through
    `has-white-background-color` and `has-black-color`; it should later migrate
    to the reusable light-surface contract rather than remain a one-off core
    color pair.
  - Category/archive renders on the purple body surface. Query headings are
    white, while excerpts and links compute as dark text.
  - Single blog posts render on the purple body surface. Post title/comment
    headings are white, while post body paragraphs and links compute dark.
  - Shop/Ecwid surfaces are vendor adapter candidates: the surrounding page
    still inherits the purple body, but current Ecwid store output has explicit
    dark text patches for storefront/product/cart content.
- Filesystem audit found all requested generic templates exist:
  `404.html`, `search.html`, `home.html`, `archive.html`, `single.html`, and
  `index.html`.
- Natural Phase 2 attachment points are the main template wrappers:
  `.pns-template-404`, `.pns-template-search`, `.pns-template-home`,
  `.pns-template-archive`, `.pns-template-single`, and `.pns-template-index`.
  The 404 spacers are siblings outside `main`, so a continuous light band may
  need a small template/rhythm cleanup when adopted.

## Phase 1 - Define Reusable Light-Surface Contract

Goal: introduce the reusable contract without broad adoption.

Expected shape:

- A semantic class or wrapper role using `light surface` language.
- CSS variables for:
  - background,
  - text,
  - heading,
  - muted text,
  - link,
  - border/separator,
  - button foreground/background where needed.
- Rules for common descendants:
  - headings,
  - paragraphs,
  - lists,
  - links,
  - captions where supported,
  - post meta,
  - query/post navigation.

Implementation rules:

- Keep the contract in the appropriate theme layer, not in
  `vendor-overrides`.
- Do not make this an Ecwid-only abstraction.
- Avoid changing the body default surface globally until template adoption has
  been audited.
- Keep existing PNS section theme behavior intact.

Acceptance checks:

- CSS compiles.
- Existing visual targets are unchanged unless a controlled fixture route is
  intentionally used to prove the new class.
- Stylelint and formatting pass for touched files.

Phase 1 contract:

- Add `styles/components/light-surface.css`.
- Import it through both `styles/components/frontend.css` and
  `styles/components/index.css` so frontend and editor bundles share the same
  contract.
- Use `.pns-light-surface` as the neutral adoption class.
- Define these variables on the surface root:
  - `--pns-surface-background`
  - `--pns-surface-text`
  - `--pns-surface-heading`
  - `--pns-surface-muted`
  - `--pns-surface-link`
  - `--pns-surface-link-hover`
  - `--pns-surface-border`
  - `--pns-surface-button-background`
  - `--pns-surface-button-color`
  - `--pns-surface-button-shadow`
- Apply default descendant behavior only when blocks have not been explicitly
  given text/background colors:
  - paragraphs, list items, and captions use `--pns-surface-text`;
  - headings and post titles use `--pns-surface-heading`;
  - post meta, excerpts, query pagination, and comment meta use
    `--pns-surface-muted`;
  - ordinary links use `--pns-surface-link` with red hover/focus;
  - uncolored buttons use the surface button variables.
- Bridge `.pns-section.pns-light-surface` into the existing section-theme
  variables so future section adoption can share the same contract without
  duplicating section CSS.

## Phase 2 - Adopt Generic Templates

Goal: make long-form and utility templates readable by applying the
light-surface contract.

Initial adoption candidates:

- `templates/404.html`
- `templates/search.html`
- `templates/home.html`
- `templates/archive.html`
- `templates/single.html`
- `templates/index.html`

Rules:

- Apply the contract at the template surface/wrapper level, not by scattering
  individual text-color classes through content.
- Preserve current layout width, rhythm, and template structure unless a
  specific issue is found and accepted.
- Keep archive cards and post excerpts readable without adding nested card
  styling for its own sake.
- If a template is missing or DB-owned, record the source before changing it.

Acceptance checks:

- Template validation passes.
- Focused browser checks confirm readable text on all adopted templates.
- No horizontal overflow or obvious spacing shift.
- Playwright coverage is added or extended for generic template surfaces.

Phase 2 result:

- Applied `.pns-light-surface` at the `main` wrapper level for
  `templates/search.html`, `templates/home.html`, `templates/archive.html`,
  `templates/single.html`, and `templates/index.html`.
- Modernized `templates/404.html` rather than only adding the class: removed
  spacer siblings, made the `main` wrapper the full-width light surface owner,
  moved spacing into wrapper padding, and removed the old explicit foreground
  paragraph/button text color classes so the surface contract owns readability.
- Synced saved template rows after DB backup
  `docs/jobs/light-surface-db-backups/20260704-before-light-surface-template-sync.sql`:
  404 row `3164`, home row `1029`, and single row `5990`. The single update
  changed only the outer wrapper so its saved post-date binding stayed intact.
- Added `generic template light surface contract` Playwright coverage for:
  404, search results, search no-results, blog home, archive, and single post
  across desktop, tablet, and mobile. `templates/index.html` remains covered by
  static block-template validation because no current route resolves to that
  fallback template.
- Added a selectable custom page template, `templates/page-light-surface.html`,
  registered in `theme.json` as `PNS - Light Surface Page`, so editors can
  choose the light surface from the page template selector instead of manually
  typing the CSS class onto a wrapper group.
- Verification passed: CSS compile, CSS lint, Prettier check for touched
  templates/tests, block-template validation for the six template files, DB
  verification that saved 404/home/single contain `pns-light-surface`, and
  focused Playwright light-surface coverage `18 passed`.
- Pre-change elevated full visual suite was otherwise healthy but had stale
  `/shop/` snapshots in desktop/tablet/mobile (`178 passed`, `2 skipped`,
  `3 failed`). The Ecwid computed contracts passed in that same run, so the
  shop snapshot refresh remains separate from this generic-template phase.

## Phase 3 - Refactor Ecwid Overrides As Adapter

Goal: keep the necessary Ecwid fixes while making their values come from the
light-surface contract.

Scope:

- `/shop/`
- Ecwid product detail routes.
- Product descriptions.
- Cart.
- Cart related/recommended products.
- Minicart.
- Form/select/radio controls.

Rules:

- Keep Ecwid selectors in `styles/vendor-overrides/ecwid.css`.
- Replace hard-coded white-surface assumptions with light-surface variables
  where possible.
- Keep `-webkit-text-fill-color` rules only where the rendered Ecwid DOM proves
  `color` is insufficient.
- Do not merge editorial search and Ecwid/store search.

Acceptance checks:

- Existing Ecwid regression tests remain green.
- Cart recommendation titles and product descriptions stay readable.
- Product grid title/price sizing remains responsive.
- No visible product/store drift unless accepted.

## Phase 4 - Align Editor Controls And Block Previews

Goal: avoid a frontend-only fix that leaves the Site Editor misleading.

Checks:

- Template previews show readable text on light surfaces.
- Post editor previews for single/blog content are readable.
- Color and style controls do not claim unsupported behavior for fixed
  template surfaces.
- Any unsupported or CSS-owned controls are documented in the style-controls
  mop-up plan rather than silently ignored.

Acceptance checks:

- Editor and frontend computed colors agree for target surfaces.
- Any control limitations are documented or split to `d72fw0le`.
- No unrelated editor canvas regressions.

Phase 4 result:

- Confirmed the editor bundle imports the same light-surface contract through
  `styles/editor.css` -> `styles/components/index.css` ->
  `styles/components/light-surface.css`.
- Added an editor-canvas computed-style probe that appends temporary
  `.pns-light-surface` markup inside the authenticated editor canvas and
  verifies the same core defaults as the frontend contract: white surface
  background, secondary body text, primary heading/link text, readable default
  button colours, and mint button shadow.
- The probe does not mutate saved content. It proves the Site Editor/editor
  canvas CSS path applies the contract when light-surface markup is present.
- Remaining control-support decisions stay with the Site/style-controls mop-up
  parent `d72fw0le`; this phase records parity for the light-surface contract,
  not a new promise that fixed template surfaces expose every Global Styles
  control.
- Verification passed: CSS compile, Prettier check for the touched editor test
  and plan doc, `git diff --check`, and focused elevated editor Playwright
  coverage (`1 passed`, `2 skipped` by the existing desktop-only editor harness
  rule).

## Phase 5 - Converge Light And Purple Surface Models

Goal: move toward one surface system without changing the accepted purple
section visuals.

Sequence:

1. Keep the current `pns-section` purple/brand behavior working as
   compatibility.
2. Confirm the light-surface model is proven on generic templates and Ecwid
   adapter values before touching the purple section model.
3. Introduce or formalize a shared surface contract underneath both variants.
4. Make purple/brand section styles consume the same surface variables used by
   the light model.
5. Retire duplicate or inferred purple rules only where the visual gate proves
   no apparent change.

Rules:

- Do not rename established `pns-section` wrappers in this phase.
- Do not infer light or purple behavior from arbitrary background classes where
  an explicit surface class or compatibility bridge can own it.
- Preserve existing computed heading, body, link, button, separator, and
  section-background behavior for current purple/brand sections.
- Record any surfaces that cannot yet adopt the shared contract and explain why.

Acceptance checks:

- Existing purple/brand sections compute the same visible colors and button
  styling as before.
- Light-surface templates continue to compute the same readable colors.
- Any duplicated rules removed in this phase have before/after evidence.
- Visual coverage for high-risk campaign, template, and shop routes remains
  green or differences are explicitly accepted.

Phase 5 result:

- Converged the PNS section theme onto the shared surface vocabulary without
  changing public section class names or rendered section colours.
- `pns-section` now seeds `--pns-surface-*` values first, then exposes the
  existing `--pns-section-*` names as compatibility aliases. Purple/brand/dark
  section inversions use the same bridge.
- No section selectors were retired in this phase. The safe cleanup decision is
  deferred to Phase 6 because the existing rules still own compatibility for
  clear buttons, manual text colours, and legacy section wrappers.
- Updated visual coverage so the Herstories fixture checks both the visible
  purple-section button colours and the underlying shared surface-token bridge.
- The active saved 404 template is the newer search/image version, so 404
  light-surface coverage now asserts readable search text and uses a temporary
  button fixture for default button hover/readability.
- Verification passed: CSS compile, CSS lint, Prettier check, `git diff
--check`, focused Herstories Playwright coverage (`3 passed`), and combined
  focused surface/Ecwid Playwright coverage (`33 passed` across
  desktop/tablet/mobile).

## Phase 6 - Validate And Retire Duplicated Overrides

Goal: close only after the generic contract has replaced one-off ownership.

Validation:

- CSS compile.
- CSS lint.
- Formatting checks.
- Block-template validation.
- Focused Playwright tests for:
  - search,
  - 404,
  - blog index/home,
  - archive,
  - single post,
  - shop,
  - product detail,
  - cart recommendations.
- Manual or scripted browser checks at desktop, tablet, and mobile widths.

Cleanup:

- Remove duplicated one-off white/light surface overrides only after the new
  contract owns the behavior.
- Remove duplicated or inferred purple/brand surface rules only after Phase 5
  proves the shared contract preserves current visuals.
- Keep Ecwid-specific adapter rules where runtime CSS still requires them.
- Update this plan with decisions, accepted visual changes, and any follow-up
  Dex IDs.

Closeout criteria:

- Generic readable templates no longer rely on purple body defaults.
- Ecwid uses the light-surface contract for common values and only retains
  vendor-specific selector shims.
- Purple/brand section styling and light-surface styling are governed by the
  same underlying surface contract, with compatibility hooks retained only where
  they still have a named owner.
- Full relevant visual gate is green or any blocker has a named external owner.
- The plan records final evidence and related commits.

Phase 6 result:

- Validated the completed light-surface extraction after generic template
  adoption, Ecwid adapter work, editor parity, and shared surface-token
  convergence.
- No additional selectors were retired. The only apparent duplicate,
  `.pns-section.pns-light-surface`, remains as a compatibility hook from Phase
  1 and still owns the light-surface clear-button mapping. Removing the whole
  selector would drop that named behavior; removing only part of it would create
  a less obvious partial bridge for little practical reduction.
- Ecwid overrides remain vendor-scoped because Ecwid runtime markup still
  requires targeted `color`/`-webkit-text-fill-color` shims for product detail,
  description, minicart, and cart recommendation text.
- Verification passed: `pnpm check`, block-template validation for 15 files,
  focused editor light-surface contract (`1 passed`, `2 skipped` by desktop-only
  harness), and focused frontend surface/Ecwid Playwright coverage (`33 passed`
  across desktop/tablet/mobile).
