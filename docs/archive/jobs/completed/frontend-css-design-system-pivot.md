# Frontend CSS Design-System Pivot

## Summary

The inherited CSS should no longer be treated as an ordering puzzle to preserve
exactly. The current site visuals are the reference. Future cleanup should
rebuild the authored CSS around modern WordPress theming and a small, explicit
design system.

## Direction

- Put WordPress-supported defaults in `theme.json`: spacing presets, layout
  widths, color palette, typography defaults, block gap, and button defaults.
- Use `styles/shared/` for fonts, tokens, and cascade layers.
- Use `styles/base/` for plain element defaults only.
- Use `styles/layout/` for site layout primitives and explicit content-width
  rules.
- Use `styles/blocks/` for true WordPress block defaults. Prefer
  `wp_enqueue_block_style()` for block-owned styles so frontend and editor
  styling follow WordPress' native block asset path.
- Use `styles/components/` for real theme components such as buttons, logos,
  and footer sections.
- Use `styles/page-types/` for page families such as Herstories biographies,
  shop, and education landing pages.
- Use `styles/vendor-overrides/` only for plugin styles after checking plugin
  settings or design APIs first.

## Architecture Decisions

1. `theme.json` is the first-choice home for supported design defaults:
   layout widths, spacing presets, block gap, palette, typography, and supported
   block defaults.
2. `wp_enqueue_block_style()` is the preferred path for true block-owned CSS.
   It should be used when a rule describes how a core block should look by
   default in this theme, not when a rule is compensating for page composition,
   old markup, or plugin output.
3. Bundled global CSS is still valid for shared tokens, plain elements, explicit
   site layout primitives, page types, real theme components, utilities, and
   documented vendor overrides.
4. Do not load the same block rule through both the bundled frontend stylesheet
   and `wp_enqueue_block_style()` unless the duplication is intentional and
   documented during a migration batch.
5. Editor parity is an explicit acceptance criterion. Moving a block default to
   the native block asset path should include a decision about whether the
   editor receives the same rule through `wp_enqueue_block_style()`, the editor
   entrypoint, or a deliberately separate editor-specific rule.
6. A block rule that only works because it loads late in the global cascade is
   a diagnostic signal. Before preserving that order, check whether an earlier
   base, layout, component, or legacy rule is overreaching.
7. Contextual composition rules do not belong in block defaults. For example, a
   Group block inside a Herstories biography layout belongs in
   `styles/page-types/herstories-bios.css` or a named component, not in a
   general `core-group` stylesheet.
8. `!important` remains temporary debt unless the rule is a documented exception
   for WordPress inline block-support styles, plugin output, or another
   demonstrable cascade boundary that cannot be solved by `theme.json`, block
   asset ownership, or narrower selectors.

## Working Rules

- Do not preserve accidental legacy cascade order unless a visual test proves
  it represents an intentional design.
- Delete or quarantine selectors that do not map to current rendered markup.
- Prefer documented, scoped exceptions over broad `!important` rules.
- Compare proposed changes against the committed visual references in
  `docs/visual-reference/2026-06-23-current-design/`.
- Treat subtle drift as acceptable when it is deliberate, reviewed, and
  documented in the cleanup note for that batch.

## Implementation Log

### Phase 1 And 2 Bookkeeping Closed

- Closed on 2026-06-23 after later gated work established the intended design
  contracts and moved supported defaults.
- `docs/css/README.md` records the current CSS ownership strategy, regression
  gates, block-gap owner, `!important` policy, and legacy holding-area removal.
- `theme.json` owns supported layout widths, block gap, spacing controls,
  palette, typography defaults, and supported core block defaults.
- `tests/visual/frontend.spec.ts` contains computed-style contracts for the
  high-risk primitives that drove the pivot: navigation, alignwide widths,
  buttons, headings, quotes, slideshow images, social icons, forms, Ecwid, and
  EmailOctopus.
- Later Phase 3-7 gates provide the compile/editor/visual/Wallace evidence for
  closing the setup phases without a separate code batch.

### Phase 3 Pilot Started: Quote And Separator

- `core/quote` and `core/separator` are true block defaults and are now the
  first pilot candidates for native `wp_enqueue_block_style()` ownership.
- Their authored CSS files should not also be imported through the global
  frontend block bundle during this pilot.
- Existing Playwright contracts cover quote padding/border/font behavior and
  separator margins.
- Dex tracking:
  - Parent pivot task: `7ggy4ipm`
  - Phase 3 task: `ig0rnw94`
  - Completed quote/separator pilot: `dzhff19s`
  - Open visual gate: `socg6l88`
- Initial verification on 2026-06-23:
  - Local Lightning CSS frontend/editor compile passed through
    `node_modules/.bin/lightningcss`.
  - `pnpm compile:css` could not run because pnpm's version switch to
    `10.14.0` failed registry signature verification in this environment.
  - PHP syntax check passed for `functions.php`.
  - Prettier check passed for touched docs/CSS.
  - Elevated Playwright run: 25 passed, 5 full-page screenshot failures.
    Homepage separator and Herstories quote computed-style contracts passed.
  - This was a pre-heading/AOS-stabilization checkpoint. The failures matched
    the checkpoint's known unstable/drift patterns: mobile home height,
    desktop/mobile Mary Barbour, and tablet/mobile edu-giveaway.

### Phase 3 Follow-Up: Heading Weight And AOS Stabilization

- Visual review found the current desktop Mary Barbour render was correct, but
  the old local baseline had captured a stalled animation frame.
- Production sanity checks against `https://protestsandsuffragettes.com/`
  confirmed the intended heading style on `/`, `/herstories/mary-barbour/`,
  and `/edu-giveaway/`: Rubik at `800` weight.
- `theme.json` now owns that heading weight through the core heading block
  default, and Playwright computed-style contracts assert it on the covered
  Home, Herstory, and Edu Giveaway routes.
- The visual test helper now primes AOS/scroll-driven content before freezing
  animations so full-page screenshots include content that is hidden until
  scroll reveal runs.
- Only the accepted desktop Mary Barbour local baseline was refreshed.
- Dex tracking:
  - Completed heading/AOS follow-up: `njpy3rgv`
  - Completed Phase 3 visual gate: `socg6l88`
- Verification on 2026-06-23:
  - Targeted heading/form contracts passed.
  - Edu Giveaway visual snapshots passed across desktop, tablet, and mobile.
  - Full frontend Playwright visual suite passed: 30/30.

### Phase 4 Complete: Harder Core Block Ownership

- Covered non-navigation block defaults were moved to native
  `wp_enqueue_block_style()` ownership for `core/columns`, `core/cover`, and
  `core/image`.
- Social Links frontend link/icon rules were folded into
  `core-social-links.css`, and the duplicate global Social Links imports were
  removed.
- The duplicate global import for `core-navigation.css` was removed after
  navigation contracts passed. `core-navigation-frontend.css` remains global for
  now because those rules are contextual header/frontend composition rather than
  clean block defaults.
- Button styling was deliberately left in `styles/components/buttons.css`
  because the current rules style a shared theme button system across
  `.wp-block-button__link`, `.btn`, form inputs, and plugin-style controls. A
  later component/theme.json pass should decide which subset belongs to
  `core/button`.
- Dex tracking:
  - Completed non-navigation block migration: `heteh5yg`
  - Completed navigation duplicate-import removal: `3e1zyydq`
  - Completed Phase 4: `8w11u7r0`
- Verification on 2026-06-23:
  - Local Lightning CSS frontend/editor compile passed through
    `node_modules/.bin/lightningcss`.
  - PHP syntax check passed for `functions.php`.
  - Non-navigation block contracts passed, and the full Playwright visual suite
    passed: 30/30.
  - Navigation contracts passed: 6/6.
  - Final full frontend Playwright visual suite passed: 30/30.

### Phase 5 Complete: Legacy Frontend Quarantine

- `styles/blocks/legacy-frontend.css` now contains no active declarations.
- Remaining historical selectors were audited against child-theme source,
  WordPress content, and covered rendered routes.
- The release-watch comments now distinguish:
  - selectors with no rendered/content hits, such as `.m-auto-wide`,
    `.wide-img`, `.pt15`, and `.is-50vw`;
  - selectors with content or rendered hits whose old declarations are
    superseded, such as `.jumbo-header`, `.alignwide`, `.shop-intro`, and the
    broad `alignfull` column pattern;
  - selectors with only draft/custom-css or reusable-block evidence, such as
    `.ml0`, `.mw-584`, and legacy `.contact-form` fields.
- The file remained imported during the initial release-watch window until the
  Phase 7 owner override accepted removal on 2026-06-23.
- Dex tracking:
  - Completed release-watch verification: `89nlcxtn`
  - Completed Phase 5: `05fe8tb3`
- Verification on 2026-06-23:
  - Local rendered route probe confirmed zero matches for the no-hit legacy
    classes on `/`, `/herstories/mary-barbour/`, `/edu-giveaway/`, and `/shop/`.
  - WP-CLI content queries documented the remaining non-rendered content hits.
  - `.jumbo-header` still appears in published pages/patterns, and the broad
    `alignfull` column pattern still renders on covered routes; only the old
    declarations are on release-watch.
  - Full Playwright visual suite reached 29/30 twice when run with three
    workers, both times on the desktop Mary Barbour full-page screenshot.
    The same snapshot passed on isolated retry.
  - Playwright visual tests now run with one worker to avoid cross-project
    screenshot instability.
  - Final full frontend Playwright visual suite passed: 30/30.

### Phase 6 Complete: Vendor Override Rationalization

- Vendor/plugin priorities were isolated and documented rather than removed
  where runtime or hosted plugin CSS still wins.
- Messenger hiding now lives in `styles/vendor-overrides/messenger.css`, not in
  the Ecwid storefront typography file.
- Jetpack slideshow priority is limited to the block-owned pagination-hide rule
  in `styles/blocks/jetpack-slideshow.css`; the unused
  `swiper-pagination-bullets` positioning rule is disabled under
  release-watch.
- Ecwid storefront typography/title priorities remain in
  `styles/vendor-overrides/ecwid.css`; `/shop/` covers product title, product
  price, and Messenger hiding, but not currently the retained Ecwid heading
  selectors because that route renders no matching heading/product-detail title
  elements.
- EmailOctopus priorities remain in
  `styles/vendor-overrides/emailoctopus.css` for hosted hydrated form CSS; the
  focus styling carries a TODO to check the hosted EmailOctopus form editor.
- Dex tracking:
  - Completed vendor override documentation/isolation: `0jykuvxk`
  - Completed Phase 6: `hdnzh8el`
- Verification on 2026-06-23:
  - Direct Lightning CSS frontend/editor compile passed through
    `node_modules/.bin/lightningcss`.
  - Targeted vendor computed-style contracts passed.
  - Full frontend Playwright visual suite passed.

### Phase 7 Complete: Legacy Frontend Holding Area Removed

- Completed on 2026-06-23 by explicit owner override of the release-watch
  window.
- Removed the `blocks/legacy-frontend.css` import from `styles/frontend.css`.
- Deleted the comment-only `styles/blocks/legacy-frontend.css` quarantine file.
- Removed the deleted file from Stylelint override ignores.
- Rebuilt the frontend bundle with the local Lightning CSS binary after
  `pnpm compile:css` hung without output in this environment.
- Verification on 2026-06-23:
  - Prettier check passed for touched JSON, CSS entrypoints, and docs.
  - Direct Lightning CSS frontend/editor compile passed.
  - Wallace frontend metrics after removal: 13.2KB, 119 rules, 174 selectors,
    350 declarations, 19 `!important` declarations.
  - Full editor harness passed: 5/5.
  - Full frontend visual suite reached 29/30 twice with the same desktop Home
    screenshot stability timeout; the isolated desktop Home snapshot passed
    between full runs. `frontend.min.css` had no content diff, only the source
    map changed after deleting the comment-only import source.
  - `git diff --check` passed.
  - Full Stylelint still reports pre-existing issues in `buttons.css`,
    `core-navigation.css`, and `settings.css`; no new selector lint failures
    were introduced by the Phase 7 deletion.

## Phased Implementation Plan

### Phase 1: Design Tokens And Contracts

- Record the intended site primitives: content width, wide width, block gap,
  section spacing, type scale, palette, button shape, link treatment, quote
  rhythm, form field rhythm, and social-link sizing.
- Compare those primitives against the committed reference screenshots before
  changing behavior.
- Add or tighten computed-style assertions for any primitive that will be used
  as a regression contract.

Verification:

- `pnpm compile:css`
- `pnpm test:visual`
- `pnpm analyze:css` when the slice should change compiled metrics

### Phase 2: Move Supported Defaults Into `theme.json`

- Move WordPress-supported defaults out of ad hoc CSS and into `theme.json`.
- Start with layout content width, wide width, spacing presets, block gap,
  palette, typography defaults, and supported button defaults.
- Treat visual drift as a design decision, not a casual baseline refresh.

Verification:

- Compile CSS.
- Run Playwright visual tests.
- Compare affected routes against
  `docs/visual-reference/2026-06-23-current-design/`.
- Update Playwright baselines only after documenting the intentional drift.

### Phase 3: Pilot Native Block Styles

- Pilot `wp_enqueue_block_style()` with low-to-medium-risk core blocks before
  migrating harder block families.
- Initial candidates:
  - `core/quote`
  - `core/social-links`
  - `core/separator`
- For each block, move only true block defaults into the block-owned stylesheet.
- Resolve current duplication between block files bundled through
  `styles/blocks/index.css` and files registered through
  `wp_enqueue_block_style()` before treating the migration as complete.
- If moving a rule changes output because of load order, inspect the upstream
  rule that forced the dependency before adding specificity or `!important`.

Verification:

- Compile CSS.
- Run Playwright visual tests.
- Confirm the affected block renders on at least one covered frontend route.
- Add or retain computed-style assertions for the moved behavior.
- Check frontend screenshots and, when practical, editor rendering.

### Phase 4: Harder Core Block Families

- Apply the same native-block-style approach to higher-risk blocks only after
  the pilot is stable.
- Likely candidates:
  - `core/navigation`
  - `core/buttons`
  - `core/group`
  - `core/columns`
  - `core/image`
  - `core/cover`
- Keep page-context and component-context rules out of these files.
- Prefer deleting or narrowing overreaching legacy rules instead of making block
  selectors increasingly specific.

Verification:

- Compile CSS.
- Run the full visual suite.
- Review both desktop and mobile navigation states when navigation is touched.

### Phase 5: Quarantine Or Delete Legacy Selectors

- For each remaining legacy selector family, verify whether it appears in:
  child-theme source, WordPress content, templates/patterns, or rendered covered
  routes.
- If a selector is not found but deletion confidence is not high enough, move it
  to a release-watch comment with the evidence and date.
- Delete release-watch selectors after one or two releases if no regression is
  observed.

Verification:

- Compile CSS.
- Run Playwright visual tests for affected routes.
- Use Wallace metrics to track reductions, but do not treat Wallace as
  unused-selector proof.

### Phase 6: Vendor And Plugin Overrides

- Keep Jetpack, Ecwid, EmailOctopus, and Messenger rules isolated under
  `styles/vendor-overrides/`.
- Before keeping a vendor override, check for plugin settings, plugin design
  APIs, block-owned CSS, or documented WordPress hooks.
- Keep `!important` only when the plugin or WordPress output creates a real
  cascade boundary, and document the reason next to the rule.

Verification:

- Test only routes that render the plugin markup.
- Add computed-style assertions for retained high-risk overrides.
- Run full visual tests before moving to the next vendor cluster.

### Phase 7: Remove The Legacy Holding Area

- Once active rules have moved into the correct architecture, reduce
  `legacy-frontend.css` to release-watch comments only.
- Delete the file when no active or release-watch selectors remain.
- Remove imports and build references as part of the same regression-tested
  batch.

Verification:

- Compile CSS.
- Run the full visual suite.
- Run Wallace and record the metric change in the relevant cleanup note.
