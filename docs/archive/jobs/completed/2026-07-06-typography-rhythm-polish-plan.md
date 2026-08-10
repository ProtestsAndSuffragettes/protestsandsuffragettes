# Typography Rhythm And Font Loading Polish Plan

Created: 2026-07-06

Related plans:

- `docs/jobs/2026-07-06-theme-json-rhythm-type-scale-cleanup-plan.md`
- `docs/jobs/2026-07-06-layout-stability-plan.md`
- `docs/jobs/2026-07-06-motion-perceived-loading-plan.md`
- `docs/jobs/2026-06-28-theme-json-css-structure-rationalization-plan.md`

## Goal

Build on the completed rhythm and type-scale canonicalization work with a
focused polish pass for readable typography, list rhythm, font loading, and
editor parity.

The end state is a more refined reading experience with fewer font-swap layout
surprises, better heading and paragraph wrapping, useful list defaults, and a
WordPress block editor canvas that reflects the frontend contract closely
enough for safe content editing.

This plan covers:

- Heading and paragraph wrapping polish.
- Fluid display and heading type where it improves real pages.
- `lh` / `rlh` rhythm usage in private CSS tokens.
- List item typography, marker alignment, nested list rhythm, and editor
  parity.
- Explicit fallback font stacks.
- `font-display` and selective font preloading/resource hints.
- Fallback font metric matching, including `size-adjust` and guarded metric
  overrides after measurement.
- Block editor support for the same frontend typography and list contracts.

## Non-Goals

- Do not redesign the whole site or replace the approved public type scale.
- Do not expose every modern CSS unit as an editor-facing spacing control.
- Do not use container queries for typography until the global type scale and
  fallback metrics are settled.
- Do not use `leading-trim` or `text-box-trim` as production foundations.
- Do not mutate DB-backed templates, pages, posts, or synced blocks without a
  rollback export and a dry-run.
- Do not refresh visual baselines until each visible typography change is named
  and accepted.

## Current Evidence

The completed type-scale/rhythm work under Dex task `uzku688y` already:

- Promoted the public spacing scale into `settings.spacing.spacingSizes`.
- Promoted real font-size values into public `theme.json` font-size presets.
- Added named line-height roles under `settings.custom.typography.line-height`.
- Removed the old `settings.custom.spacing` mirror layer.
- Migrated source and DB-owned rhythm tokens with rollback exports.

Current typography control surfaces:

- `theme.json` owns public spacing, font-size, font-family, and line-height
  choices.
- `styles/shared/settings.css` maps public values to private component and
  rhythm aliases.
- `styles/base/elements.css` owns base heading, paragraph, and list resets.
- `styles/layout/index.css` currently owns ordinary flow-list indentation
  through `.is-layout-flow ul`.
- `styles/page-types/content-rhythm.css` owns editorial flow spacing.
- `styles/editor.css` imports the same shared, layout, base, block, page-type,
  component, utility, and editor-canvas CSS layers for the block editor.
- `styles/editor-canvas.css` contains editor-only layout parity fixes, but no
  list-specific alignment contract.
- `styles/page-types/herstories-bios.css` owns Herstories-specific list
  exceptions for `.active-dates` and `.fun-facts`.

Current gaps:

- `@font-face` declarations for Libre Franklin and Rubik do not define
  `font-display`, `size-adjust`, `ascent-override`, `descent-override`, or
  `line-gap-override`.
- The Libre Franklin and Rubik family presets are bare single-family values,
  not explicit fallback stacks.
- No standalone-theme preload or resource-hint hook for local WOFF2 fonts was
  found.
- `Cubano-Regular.woff2` is bundled but appears unused in authored standalone
  theme sources.
- Base list rules only reset `ul` and `ol` block margins; list item rhythm,
  marker spacing, marker styling, nested lists, and editor parity need a real
  contract.
- Ordinary `ul` indentation is currently applied through `.is-layout-flow ul`;
  `ol` does not appear to have an equivalent explicit indentation owner.
- The `pns/suffragette-facts` Herstory pattern uses a `fun-facts` list and
  shows bullets on the frontend, but editor alignment is currently a known
  cleanup target.
- `.has-large-font-size` and `.has-x-large-font-size` currently carry tight
  heading/display line-height globally, so using those presets on long body
  copy can make paragraphs too compressed.

## Guardrails

- Treat this as a polish layer after the canonical scale, not as a second scale.
- Keep ordinary copy stable; apply fluid type mostly to headings, display text,
  and intentional intro text.
- Prefer private CSS aliases for `lh` / `rlh` rhythm before adding those units
  to editor spacing controls.
- Establish a global baseline for ordinary `ul`, `ol`, and `li` styling, then
  explicitly exclude UI/control lists such as Navigation, Social Links,
  pagination, and vendor/plugin controls.
- Keep editor parity explicit. If frontend list styling needs editor-specific
  selectors because of block-editor wrappers, add them deliberately in the
  editor layer.
- Measure fallback font metrics before adding metric overrides.
- Treat `ascent-override`, `descent-override`, and `line-gap-override` as
  progressive fallback-matching tools, not unconditional cross-browser
  guarantees.
- Validate font-loading changes with cold-cache and throttled checks before
  deciding whether preload improves perceived loading.

## Deferred

### Container Query Typography

Container queries are useful for component-responsive type in Search cards,
archive cards, CTA blocks, split sections, and future card components.

Defer this work until:

- The global type scale is settled after this polish pass.
- Fallback stacks and font metrics are known.
- List and editor parity cleanup has landed.
- The components needing independent type behavior are named and measured.

When revisited, container query work should be its own Dex-tracked plan or a
child task under a component-specific rollout.

## Implementation Closeout - 2026-07-06

Dex parent `a7s4gtu0` was implemented as a theme-owned polish pass, with no DB
template/content mutation.

Refreshed evidence:

- Active theme: `protestsandsuffragettes-standalone`.
- Active saved global styles row: `wp-global-styles-protestsandsuffragettes-standalone`
  (`ID 5256`).
- Saved-content risk scan found typography/list/font-family/line-height related
  serialization in `page` (31), `wp_template` (6), `herstory` (6), `wp_block`
  (3), `post` (1), `wp_template_part` (1), and `wp_global_styles` (1), so the
  rollout avoided DB rewrites and kept behavior in the theme contract.
- Cubano remains unused in authored standalone-theme sources and DB content.

Landed behavior:

- Added `font-display: swap` for the bundled Libre Franklin and Rubik variable
  fonts.
- Added explicit fallback stacks to the Libre Franklin and Rubik
  `theme.json` font-family presets.
- Reworked medium, large, display, and extra-large presets to use
  `clamp(... calc(...vw) ...)` formulas instead of fixed or viewport-only
  jumps.
- Moved ordinary list rhythm into `styles/page-types/content-rhythm.css`,
  covering `ul`, `ol`, nested lists, `li + li`, and marker styling across
  frontend and editor canvas.
- Removed the old `.is-layout-flow ul` indentation owner from
  `styles/layout/index.css`.
- Added `text-wrap: balance` for content/editor headings and `text-wrap:
pretty` for prose-bearing paragraphs, excerpts, and list items.
- Explicitly excluded Navigation, Social Links, pagination, buttons, Search,
  Jetpack slideshow, EmailOctopus, Ecwid, and related vendor/control lists from
  editorial wrapping and list rhythm.
- Preserved the Herstories `.fun-facts` custom pseudo-bullet contract with
  explicit list padding, line-height, and no native marker stacking.

Metric override decision:

- `fontTools`, `ttx`, `fontkit`, `opentype.js`, and other usable WOFF2 metric
  tooling were not available locally, and `fc-scan` / `hb-info` did not produce
  usable metrics for these WOFF2 files. `size-adjust`, `ascent-override`,
  `descent-override`, and `line-gap-override` were therefore intentionally not
  added. A future metric pass should install or vendor a real WOFF2 parser
  before adding fallback metric overrides.

Validation:

- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css`
  passed.
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css`
  passed.
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check`
  passed.
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout`
  passed.
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation`
  passed.
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:fast`
  passed.
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor`
  passed.
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:shop`
  passed all computed contract tests, but the slow audit snapshots changed on
  tablet/mobile after typography rhythm changes. Baselines were not refreshed.
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:emailoctopus`
  passed all computed vendor contracts, but the slow audit snapshots changed on
  desktop/tablet after typography rhythm changes. Baselines were not refreshed.

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
a7s4gtu0 - Plan typography rhythm and font loading polish
```

Phase tasks:

| Phase | Dex ID     | Task                                                     |
| ----- | ---------- | -------------------------------------------------------- |
| 0     | `bhi24bgc` | Audit live typography, lists, fonts, and editor parity   |
| 1     | `cz56v7u9` | Add heading and paragraph wrapping polish                |
| 2     | `e19jba8a` | Refine fluid type and rhythm units                       |
| 3     | `958hdggm` | Clean up list item typography and editor alignment       |
| 4     | `o4wsoeq7` | Improve font discovery and fallback stacks               |
| 5     | `qmtemlqa` | Add measured fallback font metrics                       |
| 6     | `902lqgan` | Validate frontend, editor, performance, and visual gates |

## Phase 0: Audit Live Typography, Lists, Fonts, And Editor Parity

Objective: refresh evidence immediately before implementation.

Steps:

1. Confirm the active theme and active saved `wp_global_styles` row.
2. Confirm saved `wp_template`, `wp_template_part`, `wp_block`, page, post, and
   Herstory content that may serialize font-size, line-height, list, or spacing
   choices.
3. Inventory heading, paragraph, list, quote, CTA, Search, News, Herstories,
   footer, and navigation typography on frontend routes.
4. Inventory editor rendering for the same representative blocks and patterns,
   including the `pns/suffragette-facts` pattern.
5. Inspect bundled WOFF2 font tables with proper tooling, such as `fontTools`,
   before metric override work.
6. Run a cold-cache browser check for font discovery, fetched font files, and
   layout shift attribution.

Acceptance criteria:

- Current frontend and editor typography/list gaps are recorded.
- Font binary metrics and axes are known before fallback-metric implementation.
- Saved content risks are named before any DB mutation.
- Navigation, Social Links, and other list-backed UI components are marked as
  no-regression surfaces.

## Phase 1: Heading And Paragraph Wrapping Polish

Objective: improve line breaks without changing content or layout ownership.

Recommended implementation direction:

- Add `text-wrap: balance` to short headings:
  - `h1`, `h2`, and `h3` in content surfaces.
  - `.wp-block-post-title`.
  - hero/display headings.
  - banner CTA and Herstories CTA headings.
- Add `text-wrap: pretty` as the default for prose-bearing text blocks and
  content text surfaces. Treat this as a global prose enhancement, not a
  one-off editorial-page exception.
- Identify prose at the block/surface level through ordinary WordPress content
  hooks such as `core/paragraph`, `core/list`, `core/post-excerpt`,
  `.wp-block-post-content`, `.entry-content`, and `.editor-styles-wrapper`
  equivalents.
- Exclude UI/control text from the global prose wrapping rule, including
  navigation labels, buttons, badges, pagination, form controls, compact
  metadata, and vendor/plugin controls.
- Include editor parity through the existing editor CSS import path.

Acceptance criteria:

- Headings wrap more intentionally on desktop, tablet, and mobile.
- Paragraph wrapping does not create obvious performance or layout issues on
  long result lists.
- The editor canvas reflects heading/list wrapping closely enough for authoring.
- Visual checks cover at least Home, a Herstory single, Search, News, and a
  representative page.

## Phase 2: Fluid Type And Rhythm Units

Objective: add fluid typography and modern rhythm units where they remove real
rough edges.

Recommended implementation direction:

- Keep body, UI, caption, navigation, badge, and metadata sizes stable.
- Review existing `clamp()` presets and adjust only display/heading/intro
  values that currently jump too much between breakpoints.
- Prefer formulas such as `clamp(min, calc(rem + vw), max)` over viewport-only
  scaling.
- Use `rlh` for root-rhythm spacing tokens where consistent vertical rhythm is
  the goal.
- Use `lh` for component-local text rhythm only when the spacing should follow
  that component's computed line-height.
- Use `cap` only for targeted marker/icon alignment experiments, not as a broad
  spacing scale.
- Keep `ch` for measure constraints where useful, not for general layout width.

Acceptance criteria:

- Fluid changes are limited to named display/heading/intro surfaces.
- Text remains legible and predictable at mobile and wide desktop widths.
- `lh` / `rlh` usage improves rhythm without adding editor control confusion.
- Existing line-height roles stay documented and still map to meaningful use
  cases.

## Phase 3: List Item Typography And Editor Alignment

Objective: close the list styling gap across editorial content, Herstory facts,
and editor rendering.

Recommended implementation direction:

- Define a global baseline list contract for ordinary `ul`, `ol`, and `li`.
- Prefer block/content selectors such as `core/list`, `.wp-block-list`,
  `.wp-block-post-content`, `.entry-content`, and editor equivalents before
  reaching for bare element selectors.
- Decide whether the current `.is-layout-flow ul` indentation owner should be
  replaced by or folded into the baseline list contract, and add matching `ol`
  handling.
- Set list line-height, marker offset, item spacing, nested-list spacing, and
  marker color intentionally.
- Use logical properties such as `padding-inline-start`, `margin-block`, and
  `margin-inline` rather than physical left/right values.
- Preserve bullets/numbers for ordinary lists and Herstory facts.
- Do not apply the baseline list treatment to:
  - Navigation lists.
  - Social Links lists.
  - Query pagination.
  - Vendor/plugin control lists.
- Add or adjust editor-specific selectors only where block-editor wrappers
  cause alignment differences.
- Treat `.fun-facts` as a named acceptance surface, not as the only list style.
- Preserve or deliberately replace the existing Herstories `.fun-facts`
  pseudo-marker treatment rather than accidentally stacking it with new generic
  marker rules.
- Verify light/dark section `li` color rules still respect explicit editor text
  choices.

Candidate prose/list surfaces:

- `.entry-content :where(ul, ol)`.
- `.wp-block-post-content :where(ul, ol)`.
- `.wp-block-list`.
- `.pns-copy-column :where(ul, ol)`.
- `.fun-facts`.
- `.editor-styles-wrapper` equivalents for the same surfaces.

Explicit exclusions:

- `.wp-block-navigation`.
- `.wp-block-social-links`.
- `.pns-query-pagination`.
- Form controls and button groups.
- Vendor/plugin UI regions.

Acceptance criteria:

- Editorial bullets and ordered lists have consistent indentation, line-height,
  and item rhythm.
- Nested lists remain readable and do not collapse into parent item rhythm.
- The `pns/suffragette-facts` pattern shows aligned bullets in both frontend
  and editor.
- Navigation and Social Links retain their markerless UI behavior.
- Tests or screenshots cover the frontend and editor version of the fun-facts
  pattern.

## Phase 4: Font Discovery And Explicit Fallback Stacks

Objective: reduce avoidable font-loading jank before metric overrides.

Recommended implementation direction:

- Add `font-display` to the Libre Franklin and Rubik `@font-face`
  declarations.
- Add explicit fallback stacks to the Libre Franklin and Rubik public font
  families in `theme.json`.
- Decide whether to preload both local WOFF2 files, only the most impactful
  one, or neither after cold-cache testing.
- Use WordPress resource-hint/preload APIs from the standalone theme if preload
  is beneficial.
- Remove, document, or defer the unused `Cubano-Regular.woff2` file after
  confirming no source, DB, or plugin content uses it.

Acceptance criteria:

- A delayed or blocked webfont falls back to an intentional sans stack.
- Font loading does not block content unnecessarily.
- Preload decisions are based on measured cold-cache behavior, not assumption.
- Editor and frontend use consistent family declarations.

## Phase 5: Measured Fallback Font Metrics

Objective: tune fallback font metrics to reduce reflow when Libre Franklin or
Rubik arrive late.

Recommended implementation direction:

1. Extract actual Libre Franklin and Rubik metrics and variation axes from the
   bundled WOFF2 files.
2. Choose fallback system fonts that are available and visually acceptable on
   common platforms.
3. Create named fallback faces only if measurement supports the approach.
4. Use `size-adjust` first.
5. Add `ascent-override`, `descent-override`, and `line-gap-override` only as
   guarded progressive enhancement after compatibility and visual testing.
6. Compare fallback text width, heading wraps, line boxes, and CLS before and
   after the change.

Acceptance criteria:

- Metric override values are derived from measured font data.
- Fallback heading and paragraph widths are materially closer to the webfont.
- Cold-cache layout shift improves or the task records why overrides were not
  worth landing.
- Unsupported browsers still get a reasonable explicit fallback stack.

## Phase 6: Validation

Objective: prove polish improvements without hiding regressions.

Frontend routes:

- `/`
- `/herstories/`
- a Herstory single using the fun-facts pattern
- `/news/`
- a standard post
- `/search/`
- `/?s=protest`
- `/contact-us/`
- `/contact-success/`

Editor checks:

- Insert or preview the `pns/suffragette-facts` pattern.
- Verify ordinary paragraph, heading, unordered list, ordered list, nested list,
  quote, and CTA text blocks.
- Verify Site Editor template previews do not regress because of editor-only
  selectors.

Commands:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual -- --grep "typography|editor|herstory|search|news"
```

Run the full visual suite before landing broad typography, font-loading, or
editor-parity behavior:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual
```

Acceptance criteria:

- Frontend typography changes are visible only where intended.
- Editor rendering supports the same content decisions authors see on the
  frontend.
- Font-loading changes do not regress CLS, LCP, or first render in cold-cache
  checks.
- Visual regression results are recorded in Dex with accepted drift named.

## Locked Decisions

### 1. How aggressive should `text-wrap: pretty` be?

Options:

- A. Editorial paragraphs and excerpts only.
- B. All paragraphs.
- C. Headings only; skip paragraph pretty wrapping.

Locked decision: use `text-wrap: pretty` as the default for prose-bearing text
surfaces, not only hand-picked editorial pages. This is broader than the
original option A, but still narrower than a bare global `p {}` rule because it
excludes UI/control text.

Implementation guidance:

- Include `core/paragraph`, `core/post-excerpt`, intro copy, content excerpts,
  `.wp-block-post-content`, `.entry-content`, and editor equivalents.
- Exclude navigation labels, buttons, badges, pagination, form controls,
  compact metadata, and vendor/plugin controls.

### 2. Should `lh` / `rlh` become editor-facing spacing units?

Options:

- A. Use them privately in CSS tokens first.
- B. Add them to `theme.json` spacing units immediately.
- C. Avoid them entirely.

Locked decision: A. Use `lh` / `rlh` privately in CSS tokens first. Revisit
editor-facing units after authors have a stable named preset language.

### 3. How should list marker styling be scoped?

Options:

- A. Editorial content surfaces plus named pattern surfaces.
- B. Every `ul` and `ol` globally.
- C. Only `.fun-facts`.

Locked decision: establish a global baseline for ordinary lists, with explicit
UI/control exclusions. This is broader than the original option A, but it should
still avoid Navigation, Social Links, pagination, form controls, and vendor UI.

Implementation guidance:

- Treat `core/list`, `.wp-block-list`, `.wp-block-post-content`, and
  `.entry-content` as normal list/prose surfaces.
- Keep `.fun-facts` as a named acceptance surface.
- Preserve or replace its custom pseudo-marker intentionally.
- Do not use an unqualified bare `ul, ol, li` rule unless tests prove it does
  not leak into UI lists.

### 4. Which font loading strategy should land first?

Options:

- A. `font-display` plus explicit fallback stacks.
- B. Add preload at the same time.
- C. Add metric overrides first.

Locked decision: A. Start with `font-display` plus explicit fallback stacks.
Preload and metric overrides should follow measurement.

### 5. Should metric overrides be required even if CLS is not font-attributed?

Options:

- A. Require measured font-attributed shift before landing overrides.
- B. Land overrides because fallback width deltas are visibly large.
- C. Create fallback stacks only and skip metrics.

Locked decision: A with a bias toward doing the measurement. The width deltas
are large enough to investigate, but override math should not be guessed.

### 6. When should container query typography resume?

Options:

- A. After this polish plan completes and the global scale is stable.
- B. In parallel with font metrics.
- C. Only when a specific component redesign requires it.

Locked decision: A. Resume container-query typography after this polish plan
completes and the global scale is stable. Container queries should not compete
with the global type scale while that scale is still being polished.
