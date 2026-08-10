# Standalone Theme Adversarial Review Remediation Plan

Created: 2026-07-11.

All paths are relative to the project root.

Last updated: 2026-07-11.

Status: Completed. Implementation landed in `5643160`
(`fix: remediate standalone theme visual contracts`); this completed-plan copy
records the final closeout.

## Purpose

Remediate the actionable findings from the 2026-07-11 adversarial review of
the active `protestsandsuffragettes-standalone` theme, with emphasis on CSS
architecture, surface styles, cascade layers, `theme.json`, WordPress-generated
CSS variables, runtime render bridges, and selector compatibility debt.

This plan is not a broad redesign. It is a staged cleanup that starts with
broken or misleading contracts, then moves into ownership simplification only
after each claim has current evidence.

## Related Work

- Completed control-health baseline:
  `docs/jobs/__completed/2026-07-06-theme-css-control-health-remediation-plan.md`
- Completed cascade-layer migration:
  `docs/jobs/__completed/2026-07-07-css-cascade-layer-migration-plan.md`
- Completed retained render-bridge work:
  `docs/jobs/__completed/2026-07-07-retained-render-bridge-remediation-plan.md`
- Completed light-surface extraction:
  `docs/jobs/__completed/2026-07-04-light-surface-theme-extraction-plan.md`
- Completed public token normalization:
  `docs/jobs/__completed/2026-07-09-public-token-candidates-normalization-plan.md`
- Completed hard-coded layout/rhythm/type normalization:
  `docs/jobs/__completed/2026-07-09-layout-rhythm-type-hardcoded-value-normalization-plan.md`

## Current Evidence

The review found several confirmed defects or high-confidence risks:

- `theme.json` disables default spacing sizes and does not define a `0` spacing
  preset, while templates use `var(--wp--preset--spacing--0)`.
- `styles/components/section-theme.css` includes `::after` selectors inside
  `:where(...)`; Lightning CSS compiles those rules into empty
  `:where(){...}` selectors in both frontend and editor bundles.
- Cascade layers are partially adopted, but major CSS barrels remain unlayered,
  and block-scoped styles loaded through `wp_enqueue_block_style()` sit outside
  the bundled layer model.
- `styles/css-assets.json` passes, but it intentionally allowlists duplicate
  load paths for `core-cover`, `core-navigation`, and `core-quote`.
- Surface behavior is split across `light-surface.css` and `section-theme.css`,
  with duplicated button treatment and finite class lists.
- Several CSS surfaces are PHP-mutated at render time, especially Herstory
  entry navigation and query pagination.
- Static selector review found compatibility candidates that need DB/rendered
  verification before deletion: legacy button aliases, old Herstories
  `.pns-suffragette-text-media`, `.pns-contact-form-octopus`, and
  style-guide-only helpers.

Read-only checks already run:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone audit:css-assets
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
```

Both passed. `audit:css-assets` reported the known three duplicate load-path
warnings.

## Implementation Log

### 2026-07-11 Final Closeout

This plan is now closed. The original review findings were either fixed in
this plan, superseded by related completed plan work, or retained with explicit
current ownership evidence.

Final validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone exec playwright test tests/visual
```

Results:

- `pnpm check` passed.
- Full Playwright visual suite passed: 274 passed, 2 skipped, 0 failed.
- `git diff --cached --check` passed before commit.
- Stale `store-block-test-live-adoption-quote-*` ignored snapshots were
  deleted after the quote test moved to the durable `/herstories/` route.
- Ignored generated Playwright output directories were cleared:
  `playwright-report/` and `test-results/`.

Subagents were used during the final assertion and cleanup pass:

- Style-guide verifier confirmed the DB-backed private style guide page
  (`/pns-style-guide/`, post ID 6340) and button contracts.
- Quote verifier confirmed `/store-block-test/` was removed and `/herstories/`
  has durable red-line quote coverage.
- Test-artifact verifier confirmed no remaining
  `store-block-test-live-adoption-quote` files or references after cleanup.

Cut status:

- Cut 0: completed. Baseline claims were rechecked against source, compiled
  CSS, live rendered output, DB-backed content, and current Dex state.
- Cut 1: completed. Undefined spacing-zero references were removed from
  file-backed templates, malformed compiled `:where(){}` selectors were fixed,
  and the CSS asset audit now guards against empty compiled `:where(){}`.
- Cut 2: completed or superseded by the completed cascade-layer migration and
  this plan's asset-ownership guard work. The remaining duplicate block load
  paths are documented allowed exceptions in `styles/css-assets.json`.
- Cut 3: completed or superseded by the public token, rhythm/type, and final
  visual-contract work. The final pass fixed title preset line-height ownership
  and footer copyright color ownership.
- Cut 4: completed or superseded by light-surface extraction, surface-role
  token work, and the final section-theme button assertions. Default and
  inverse button roles now have concrete Playwright coverage.
- Cut 5: completed. Retained render bridges have owner comments, removal gates,
  and focused visual coverage; block-style registration ownership was moved out
  of `inc/assets.php` into `inc/block-styles.php`.
- Cut 6: completed. Compatibility selectors were classified through file,
  DB-backed, rendered, and fixture evidence. Retained selectors are documented
  as current compatibility or style-guide support rather than unexamined dead
  code.

Residual concerns from the original audit:

- No unaddressed original audit concern remains inside this plan's scope.
- Future work should be opened as new, narrower plans only if new evidence
  appears, such as a plugin markup change, WordPress core output change, or a
  decision to make the private style guide directly testable through an
  authenticated Playwright lane.

### 2026-07-11 Baseline Refresh And Landing Cut

The Playwright reference update was run against the current Local WP site:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:update
```

The run refreshed current snapshot references where Playwright detected changed
images, then exited non-zero because the broader suite still has pre-existing
assertion/audit failures. Those failures are not snapshot-diff failures from
this landing cut.

Subagents were used for the landing pass:

- Cut 1 verifier: confirmed the undefined spacing preset references and the
  malformed `:where(...::after)` selector source.
- Cut 6 verifier: classified compatibility-selector deletion candidates and
  retained selectors that still have file-backed or fixture-backed evidence.
- Cut 5 verifier: confirmed the safe render-bridge and block-style ownership
  work and identified editor-parity automation as a later owner decision.
- Final verifier: rechecked the implemented landing cut after source changes.

Implemented in this cut:

- Replaced file-backed template references to the undefined
  `var:preset|spacing|0` / `--wp--preset--spacing--0` with literal zero
  values.
- Fixed the malformed entry-navigation pseudo-element selectors in
  `styles/components/section-theme.css` so Lightning CSS no longer emits empty
  `:where(){...}` selectors.
- Added an `audit:css-assets` guard for compiled empty `:where(){...}`
  selectors.
- Moved `pns_standalone_enqueue_block_styles()` from `inc/assets.php` into the
  already-included `inc/block-styles.php`, and updated the CSS asset audit to
  parse the new owner file.
- Added removal-gate comments to the retained Herstory navigation and query
  pagination render bridges.

Validation after implementation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone audit:css-assets
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
php -l app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/assets.php
php -l app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/block-styles.php
php -l app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/block-filters.php
node --check app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/audit-css-assets.mjs
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
```

All of the commands above passed. `audit:css-assets` still reports the known
three duplicate load-path warnings for `core-cover`, `core-navigation`, and
`core-quote`.

Current direct scans after compilation:

- No file-backed template reference to `var:preset|spacing|0` remains in the
  standalone theme templates.
- No compiled dist CSS contains `:where(){...}`.
- The only remaining literal `:where(){` match is the audit guard itself.

## Honest Evaluation

The highest-risk findings are not theoretical architecture preferences. The
undefined `spacing--0` references and compiled empty `:where(){}` selectors are
concrete broken contracts.

The layer and token findings are real but should not be remediated with blanket
migrations. This theme intentionally has override tails that must compete with
unlayered WordPress, block-support, and plugin CSS. The useful target is an
honest ownership map: layered defaults where layers are safe, explicit unlayered
override tails where runtime output requires them, and comments/tests for every
retained exception.

The static dead-code findings are cleanup candidates, not deletion approval.
This WordPress site has DB-backed templates, synced patterns, saved block
markup, plugin output, and editor-only DOM surfaces. Static file absence is not
enough evidence to delete compatibility selectors.

## Non-Goals

- Do not redesign the visual system.
- Do not delete compatibility CSS from static grep alone.
- Do not collapse all private `--pns-*` variables into public `theme.json`
  tokens.
- Do not force vendor/plugin overrides into cascade layers while Ecwid,
  EmailOctopus, Jetpack, or WordPress core still emit unlayered runtime CSS.
- Do not remove render bridges that encode legitimate runtime behavior, such as
  data-dependent Herstory ordering or archive-boundary pagination.
- Do not mutate DB-backed templates, parts, navigation, synced patterns, or
  global styles without a backup and post-apply verification.

## Guardrails

- Reconfirm the dirty tree before each implementation cut. This checkout
  currently has active standalone-theme changes unrelated to this plan.
- Keep each cut independently revertible.
- Prefer source/template fixes over runtime repair when the behavior is static.
- Preserve runtime bridges when the source cannot honestly own the behavior.
- Run a DB/rendered-content scan before removing any compatibility selector.
- Use targeted visual lanes during implementation and the lean visual gate
  before closing broad CSS behavior changes.
- Compile CSS after every CSS batch.
- Inspect compiled CSS for empty selectors and unexpected layer movement after
  every layer or selector change.

## Locked Decisions

1. Start with a landing cut.
   - Queue and execute Cut 0 first.
   - Do not pre-authorize the full backlog as implementation work.
   - Later cuts can run in tandem only after Cut 0 confirms their claims and
     disjoint write scopes.

2. Use subagents as part of the work model.
   - Every implementation cut should include verifier subagents for claims that
     can be checked independently.
   - Worker subagents may implement in tandem only when file ownership is
     explicit and non-overlapping.
   - The main agent remains responsible for integration and validation.

3. Fix broken contracts before architecture polish.
   - Undefined preset variables and malformed compiled selectors outrank layer
     cleanup, token normalization, and selector deletion.
   - Build/test guards should be added where the current tooling failed to catch
     a broken contract.

4. Treat DB-backed content as a first-class source of truth.
   - Any task touching serialized classes, templates, patterns, navigation, or
     token references must include DB-backed scans and backup/rollback criteria.
   - File-backed fixtures alone are not deletion proof.

5. Keep override tails honest.
   - Vendor, core, and runtime overrides may remain unlayered when current
     evidence proves they need normal author cascade priority.
   - Retained unlayered CSS should be documented as an exception, not mistaken
     for completed cascade-layer architecture.

## Dex Backing

This plan should be queued conservatively.

Recommended initial Dex state:

- Parent: `Remediate standalone adversarial CSS/theme review findings`
- First child only: `Cut 0 - refresh baseline and prove claims`

Do not create child tasks for Cuts 1-6 until Cut 0 has classified current
claims and the active dirty tree. When later children are created, each child
should name:

- verifier subagent scope;
- worker ownership boundaries;
- files or DB records in scope;
- validation commands;
- rollback criteria.

## Subagent Execution Model

Use subagents deliberately. The main agent owns coordination, final judgment,
and integration. Subagents should be assigned bounded, independently useful
work with explicit acceptance criteria.

For each cut:

- Use at least one verifier subagent for evidence gathering when the claim can
  be checked independently.
- Use worker subagents for implementation only when write scopes are disjoint.
- Tell workers they are not alone in the codebase and must not revert unrelated
  local edits.
- Give each worker exact file ownership.
- Do not run two workers against the same CSS barrel, generated CSS output, or
  serialized template file at the same time.
- Have the main agent review worker changes before validation.

Preferred tandem pattern:

1. Main agent starts the cut and identifies the immediate critical path.
2. Verifier subagent checks live/file/DB evidence for the claim.
3. Worker A implements the narrow source fix.
4. Worker B, only if disjoint, adds or updates the relevant validation guard.
5. Main agent integrates, compiles, runs targeted tests, and updates this plan
   with evidence.

## Landing Cut / Cut 0 - Refresh Baseline And Prove Claims

Goal: convert the review findings into current, reproducible evidence before
mutating source.

Subagents:

- Verifier A: inspect `theme.json`, templates, parts, patterns, and synced
  patterns for undefined preset variables, especially
  `--wp--preset--spacing--0`.
- Verifier B: inspect source and compiled CSS for malformed selectors such as
  `:where(){...}` and identify the exact source selector that produced each
  compiled result.
- Verifier C: inspect DB-backed/rendered content for compatibility candidates:
  `.btn`, `.outline-btn`, `.pushbutton-wide`, `.simplefavorite-button`,
  `.wpcf7-submit`, `.pns-suffragette-text-media`,
  `.pns-contact-form-octopus`, and `.pns-style-guide-*`.
- Verifier D: inspect render bridges for Herstory navigation, query pagination,
  light-surface editor parity, and block-scoped style registration.

Commands and checks:

```bash
git status --short
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone audit:css-assets
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
```

DB-backed WP-CLI commands require elevated local-service access in this
workspace. Use WP-CLI for saved content and active theme/template state rather
than assuming filesystem fixtures are current.

Acceptance criteria:

- Each review claim is classified as confirmed, superseded, or needs live
  browser/DB evidence.
- Current dirty tree is recorded.
- Compatibility candidates are not deleted in this cut.
- The next implementation cuts are updated if evidence contradicts the review.

## Cut 1 - Fix Broken Token And Selector Contracts

Goal: land the confirmed defects first.

Lane A - spacing zero:

- Decide whether the durable fix is to add a public `0` spacing preset or to
  replace template references with literal `0` / `0rem`.
- Prefer the option that best matches editor control behavior and avoids
  reintroducing a confusing public spacing token.
- Update affected filesystem templates and any synced DB content only after a
  DB-vs-file freshness check.

Owned files likely include:

- `app/public/wp-content/themes/protestsandsuffragettes-standalone/theme.json`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/templates/*.html`

Lane B - malformed compiled selectors:

- Replace `:where(.pns-entry-navigation__action .wp-block-button__link::after)`
  selectors with direct pseudo-element selectors that Lightning CSS preserves.
- Add a build guard that fails if compiled CSS contains `:where(){`.

Owned files likely include:

- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/components/section-theme.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/audit-css-assets.mjs`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/dist/*.css`

Subagent use:

- Worker A can own spacing/template changes.
- Worker B can own selector/build-guard changes.
- Verifier subagent should inspect the compiled output after both land.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone audit:css-assets
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
```

## Cut 2 - Clarify Cascade Layer And Load-Path Ownership

Goal: make the layer model honest without weakening CSS that must beat
WordPress or plugin output.

Tasks:

- Re-run the import/layer matrix for `styles/frontend.css`, `styles/editor.css`,
  component barrels, block barrels, utilities, and vendor overrides.
- Keep unlayered override tails explicit with comments and proof.
- Decide whether the three duplicate block load paths are still necessary:
  `core-cover`, `core-navigation`, and `core-quote`.
- If any duplicate is removable, remove one at a time and prove no frontend or
  editor regression.
- Update `styles/css-assets.json` when ownership changes.

Subagent use:

- Verifier A maps compiled/bundled layer membership.
- Verifier B maps `wp_enqueue_block_style()` output and duplicate handles.
- Worker subagents may remove duplicate paths only one file family at a time.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone audit:css-assets
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
```

## Cut 3 - Normalize Remaining Token Mismatches

Goal: remove obvious token drift without promoting implementation-only geometry.

Candidates:

- `theme.json` caption `#555555`.
- `styles/components/footer.css` `#fff`.
- `styles/blocks/core-image.css` `#222`.
- heading fallback sizes `46px` / `34px`.
- `core/social-links` `1.375rem`.
- `core/separator` `20px` margins.
- duplicated `2.4375rem` button bottom margins.
- `3rem` search/archive pagination padding.
- unused private custom properties such as
  `--pns--content-rhythm--heading-follow-gap` and
  `--pns-ecwid-category-grid-gutter`, if still unused after Cut 0.

Rules:

- Promote only true cross-surface tokens.
- Keep vendor/runtime reservations private.
- Do not normalize values when the token would be less honest than the current
  component-local value.

Subagent use:

- Verifier A classifies hard-coded values as public-token candidate,
  component-private, vendor/runtime, fallback, or dead.
- Worker A handles `theme.json` and template-safe token replacements.
- Worker B handles component-private CSS simplifications in disjoint files.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:fast
```

## Cut 4 - Simplify Surface Style Ownership

Goal: reduce duplicated light-surface and section-surface behavior while keeping
editor and frontend output aligned.

Tasks:

- Compare `light-surface.css` and `section-theme.css` variable bridges.
- Decide which file owns generic surface aliases and which owns section class
  translation.
- Remove duplicated button rules only after proving equivalent rendered output.
- Expand or document the finite dark-surface class list if strong palette
  colors outside `brand-purple`, `deep-purple`, and `neutral-800` are meant to
  behave as dark surfaces.
- Keep `.pns-light-surface` as the page/template surface contract unless a
  broader template migration is explicitly queued.

Subagent use:

- Verifier A compares computed styles for light-surface pages, section blocks,
  and dark sections.
- Worker A edits `light-surface.css`.
- Worker B edits `section-theme.css` only after Worker A has landed or if the
  main agent splits exact non-overlapping ranges.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
```

## Cut 5 - Make Runtime CSS Ownership Honest

Goal: document, reduce, or replace render bridges that obscure CSS ownership.

Targets:

- Herstory entry navigation: pattern markup is currently a trigger while PHP
  renders the actual frontend output.
- Query pagination: templates declare background classes, then PHP removes
  classes and injects boundary spans.
- Light-surface editor parity: frontend class ownership is template-driven, but
  editor canvas parity depends on a hard-coded JS template-slug set.
- `inc/block-styles.php`: included by `functions.php` but effectively empty,
  while real block CSS registration lives in `inc/assets.php`.

Decision rules:

- Keep PHP when behavior is data-dependent or cannot be expressed by static
  block markup.
- Replace PHP with source/template/block ownership when behavior is static.
- If a render bridge remains, add a clear owner comment, tests, and a removal
  gate.
- Any new light-surface template must update editor parity automatically or
  update the JS slug list in the same cut.

Subagent use:

- Verifier A traces rendered output versus pattern/template source.
- Worker A handles PHP bridge comments or simplification.
- Worker B handles editor parity tests or JS guardrails if file ownership is
  disjoint.

Validation:

```bash
php -l app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/block-filters.php
node --check app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/editor-blocks.js
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
```

## Cut 6 - Remove Or Document Compatibility CSS

Goal: retire dead compatibility selectors only after DB/rendered proof.

Candidates:

- Legacy button aliases:
  `.btn`, `.outline-btn`, `.pushbutton-wide`, `.simplefavorite-button`,
  `.wpcf7-submit`.
- `.pns-suffragette-text-media`.
- `.pns-contact-form-octopus`.
- `.pns-style-guide-*` helpers.

Required evidence before deletion:

- Filesystem search across templates, parts, patterns, synced patterns, PHP,
  JS, tests, and docs.
- DB-backed saved-content search.
- Rendered route/class inventory for known affected pages.
- Plugin-active check for plugin-specific classes such as Contact Form 7 or
  Simple Favorites.

Subagent use:

- Verifier A owns DB-backed selector inventory.
- Verifier B owns rendered-page inventory.
- Worker A removes one compatibility family at a time after verifier approval.
- Worker B updates tests or audit fixtures for that family.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:fast
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual
```

## Closeout

Close the plan only when:

- all confirmed broken contracts are fixed;
- compiled CSS no longer contains empty `:where(){}` selectors;
- undefined preset references are gone or intentionally defined;
- duplicate block load paths are either removed or documented with current
  proof;
- retained render bridges have comments, tests, and removal gates;
- compatibility selectors are deleted or documented with DB/rendered evidence;
- `pnpm check` passes, or any failure is documented as unrelated pre-existing
  debt;
- the lean visual gate passes for touched surfaces, or approved baseline updates
  are recorded.
