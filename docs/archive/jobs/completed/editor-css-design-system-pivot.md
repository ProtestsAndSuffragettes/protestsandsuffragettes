# Editor CSS Design-System Pivot

## Summary

The editor stylesheet should follow the same ownership model established by the
frontend CSS design-system pivot, but it should not be cleaned up by blindly
copying frontend decisions. The block editor has different markup, admin chrome,
iframe/editor wrappers, block controls, plugin previews, and inline block
support styles. Cleanup must therefore start with repeatable editor regression
coverage, then move rules only when the editor proves they are either duplicated,
editor-specific, or no longer needed.

The goal was to remove `styles/blocks/legacy-editor.css` after moving active
rules into documented owners, while keeping editor rendering close enough to the
frontend that content authors can trust the editing canvas.

## Current State

- `styles/editor.css` imports shared, layout, base, block, page-type,
  component, and utility owners directly; the legacy editor holding area has
  been removed.
- `styles/blocks/legacy-editor.css` was deleted in Phase 7 after active rules
  were moved to `theme.json`, shared settings, registered block styles,
  utilities, components, or page-type owners.
- `functions.php` registers block-owned styles through
  `wp_enqueue_block_style()` for core block families and Jetpack slideshow.
  Those registered block styles are the preferred home for true block defaults
  when they work in both frontend and editor contexts.
- `theme.json` already owns supported design defaults such as layout widths,
  font families, font sizes, heading defaults, button defaults, separator
  defaults, navigation typography, and other core block defaults.

## Direction

- Prefer `theme.json` for supported editor-visible design defaults.
- Prefer `wp_enqueue_block_style()` for true block defaults that should follow
  WordPress native block asset loading.
- Use `styles/shared/` for fonts, tokens, and cascade layers shared by frontend
  and editor.
- Use editor-specific CSS only for editor canvas affordances, block preview
  mismatches, admin/editor wrapper behavior, or plugin previews that cannot be
  expressed through `theme.json` or block-owned styles.
- Do not import frontend-only vendor overrides into the editor bundle unless a
  matching editor-rendered plugin surface proves the rule is needed.
- Keep every retained editor `!important` tied to a documented cascade boundary:
  WordPress inline block-support styles, editor/admin styles, or plugin/editor
  output.

## Dex Tracking

All implementation must be tracked through the child-theme Dex store:

```bash
cd app/public/wp-content/themes/protestsandsuffragettes
dex show sihwwt4w --full
```

If local Dex config is stale, use the explicit storage path:

```bash
dex --storage-path /Users/anachronistic/Local\ Sites/pns-stageing/app/public/wp-content/themes/protestsandsuffragettes/.dex show sihwwt4w --full
```

Parent task:

- `sihwwt4w` - Editor CSS design-system pivot

Phase gates:

- `og5ex7oy` - Phase 1: Establish editor regression harness
- `yjf3zv3s` - Phase 2: Inventory editor CSS ownership
- `y1xujih7` - Phase 3: Remove shared and native block duplication
- `xnomud70` - Phase 4: Rationalize editor components and utilities
- `8055xkjq` - Phase 5: Isolate editor vendor and plugin overrides
- `2xja02f0` - Phase 6: Quarantine or delete editor legacy selectors
- `dodihavw` - Phase 7: Remove editor legacy holding area

Do not mark a phase complete until its verification notes are recorded in Dex.
If a phase cannot complete because Local, WP-CLI, or Playwright access is
blocked, update the Dex task with the blocker instead of silently continuing.

## Editor Regression Strategy

Backend/editor verification needs an authenticated browser state. Use one of
these approaches, in order:

1. Prefer the installed `wp-cli-login-server` flow if the matching WP-CLI
   command is available and can produce a one-time login URL.
2. Otherwise create a temporary local admin/editor user with WP-CLI, log in
   through Playwright, save local-only auth state, and delete or rotate the user
   after the cleanup work.
3. If WP-CLI cannot reach the Local database from the sandbox, rerun the exact
   WP-CLI command with elevated local-service access before changing project
   files.

Store Playwright auth state and editor screenshots outside tracked files unless
the project policy changes. The editor tests should prefer computed-style
assertions inside `.editor-styles-wrapper` over broad full-admin screenshots.
Use screenshots as review artifacts for the block canvas, not as the only gate.

## Reference Baselines And Quality Bar

Do not assume the current local editor is the correct baseline. Recent frontend
cleanup, compiled CSS changes, or vendor override work may already have changed
editor rendering. Phase 1 must establish reference sources before judging drift:

- Current local editor rendering is diagnostic only until checked against at
  least one reference source.
- Previous committed theme state should be used as the first editor reference
  when practical. Use a temporary worktree, `git show`, or Playwright route
  interception to compare the same editor fixture against the previous
  `styles/editor.css` / `styles/dist/editor.min.css`.
- Production public pages should be used as the design-language reference for
  typography, rhythm, widths, buttons, quotes, and block appearance. Production
  admin/editor access is not required and should not be assumed.
- Existing frontend reference screenshots in
  `docs/visual-reference/2026-06-23-current-design/` remain useful for public
  design intent, but they are not editor baselines.
- If a previous editor style was weak, inconsistent, or misleading for authors,
  the cleanup may intentionally improve it. Those changes should be recorded as
  design improvements, not hidden as snapshot drift.

Use three labels when reviewing editor differences:

- Regression: the editor became less representative, less usable, or less
  consistent with the accepted public design.
- Parity correction: the editor changed because it now follows `theme.json`,
  registered block styles, or the accepted frontend design more closely.
- Intentional improvement: the editor differs from both current local and the
  previous committed editor because the inherited styling was below the desired
  authoring standard.

An intentional improvement is acceptable only when it has a short rationale,
before/after evidence, editor fixture coverage, and no public frontend
regression. Prefer improvements that make editing clearer and more faithful to
the public site without styling WordPress admin controls as page content.

## Resolved Defaults Before Implementation

Use these decisions unless new evidence shows they are wrong:

- Reference baseline: use previous committed editor CSS as the regression
  baseline where practical. Use production public pages as the design-language
  reference. Treat current local editor rendering as diagnostic only.
- Improvement policy: allow targeted editor improvements when inherited styling
  is weak, but classify them as intentional improvements with rationale,
  before/after evidence, editor fixture coverage, and clean frontend regression
  gates.
- Fixture and real-page checks: create a private editor fixture for repeatable
  coverage, then spot-check important real pages when relevant: Home, Mary
  Barbour, Edu Giveaway, and Shop.
- Authentication: avoid manual login. Try `wp-cli-login-server` first, then
  fall back to a temporary local user created by WP-CLI.
- Editor surfaces: start with the post/page block editor. Add site/template
  editor coverage only when touching navigation, header, footer, templates, or
  global styles.
- Plugin previews: include only plugin blocks that actually render in the
  editor fixture or inspected editor page. Do not keep frontend vendor
  overrides in editor CSS without evidence.
- Baseline artifacts: keep editor screenshots and auth state local-only unless
  project policy changes. Commit tests and documentation, not transient review
  screenshots.
- Quality rubric: optimize for frontend-faithful authoring. The editor should
  represent content accurately without styling WordPress admin controls as page
  content.

Initial editor fixture coverage should include:

- headings and paragraphs;
- button/buttons;
- quote and separator;
- cover, group, columns, image;
- social links;
- navigation if the editable surface is stable enough to test;
- Jetpack slideshow and contact form if they render in the editor;
- content utility classes such as `.m-auto`, `.w-100`, `.w-50-m`, `.mb05`,
  `.active-dates`, `.fun-facts`, and `.mw-intro-text`;
- vendor/plugin blocks only when they are actually editor-rendered.

## Architecture Decisions

1. Editor cleanup starts with a test harness, not selector deletion.
2. A rule duplicated in `legacy-editor.css` and in `theme.json` should usually
   be removed from `legacy-editor.css` only after the editor harness proves
   parity.
3. A rule duplicated in `legacy-editor.css` and a registered block stylesheet
   should usually stay in the registered block stylesheet, unless the editor
   needs a deliberately different rule.
4. The editor bundle should not be a dumping ground for frontend vendor fixes.
   Use editor-specific vendor override files only for proven editor surfaces.
5. Contextual page composition should not become a global block default just
   because a class appears in editor content. Put content-family rules in a
   scoped editor content file or keep them as utilities when authors apply the
   class intentionally.
6. The frontend visual suite remains a required safety net whenever shared
   files, block styles, components, utilities, or `theme.json` change. It does
   not replace editor verification.

## Working Rules

- Change one editor ownership concern at a time.
- Keep `styles/dist/editor.min.css` compiled with the authored source changes.
- Do not remove frontend coverage or frontend assertions while making editor
  cleanup easier.
- Do not update Playwright baselines or screenshots casually. Document intended
  visual drift in the relevant Dex task.
- When uncertain, move a selector to a dated release-watch comment with the
  evidence instead of deleting it immediately.
- If editor rendering differs from frontend rendering by design, document why
  the editor needs a separate rule.

## Implementation Log

### Phase 1 Harness Scaffolded

- Added `scripts/seed-editor-fixture.php` to create an idempotent private page
  fixture for representative editor block/style coverage.
- Added `tests/editor/editor.spec.ts` and `pnpm test:editor` for authenticated
  block-editor checks.
- The harness uses local-only auth state under `.cache/playwright/`, supports
  explicit login URL/command inputs, and falls back to environment-provided
  WordPress credentials.
- The initial fixture checks headings, buttons, quote, separator, cover, group,
  columns, social links, selected utility classes, and block recovery warnings.
- Real-page smoke checks cover Home, Mary Barbour, Edu Giveaway, and Shop.
- Local verification on 2026-06-23:
  - seeded private fixture page `pns-editor-css-fixture`;
  - created local-only editor harness user for Playwright authentication;
  - `php -l scripts/seed-editor-fixture.php` passed;
  - Prettier check for touched package/test files passed;
  - direct Lightning CSS editor compile passed;
  - editor harness passed: 5/5.

### Phase 2 Ownership Inventory

- Added `docs/editor-css-ownership-inventory.md` with the current editor loading
  path, canonical owners for legacy selector families, duplicate/drift hotspots,
  release-watch candidates, current harness coverage gaps, and the recommended
  Phase 3 first cleanup batch.
- Phase 2 made no CSS behavior changes.
- Phase 3 should start by making shared fonts/settings explicit in
  `styles/editor.css`, extending assertions for `.mb05`, `.mw-intro-text`, and
  `.w-50-m`, then deleting covered native-block duplication from
  `legacy-editor.css`.

### Phase 3 First Native Duplication Batch

- Made shared font and token ownership explicit in `styles/editor.css` by
  importing `shared/fonts.css` and `shared/settings.css` before the legacy
  editor holding file.
- Removed duplicate active rules from `styles/blocks/legacy-editor.css` for
  shared fonts/tokens and covered native block rules now owned by registered
  block styles: Image, Group, Quote, Separator, Cover, and Social Links.
- Left navigation, buttons, vendor/plugin rules, footer/logo rules,
  page-specific composition, and unknown helper classes for later phases.
- Extended the editor harness assertions for `.mb05`, `.mw-intro-text`, and
  `.w-50-m`.
- Local verification on 2026-06-23:
  - direct Lightning CSS editor compile passed;
  - Prettier check for touched docs, CSS, and editor test files passed;
  - editor harness passed with elevated local browser access: 5/5;
  - whole CSS Stylelint still fails on inherited selector-pattern, duplicate,
    and ordering issues in legacy/core/vendor CSS, so it remains a separate
    cleanup concern.

### Phase 4 Components And Utilities Batch

- Imported `components/buttons.css` through `styles/components/index.css` so
  editor buttons use the same canonical component owner as the frontend.
- Removed duplicated active button clusters from `styles/blocks/legacy-editor.css`.
- Removed duplicated active utility rules already owned by
  `styles/utilities/index.css`, including `.grid`, `.m-auto`, `.pr0`, `.p1`,
  `.p2-m`, `.w-50-m`, `.w-100`, `.mb05`, `.no-gap`, `.vw-100`, `.lh0`, and
  `.mw-intro-text`.
- Left risky or unresolved helpers in `legacy-editor.css`: navigation-adjacent
  button text, page/content composition, `.active-dates`, `.fun-facts`,
  `.pt15`, `.ml0`, `.mw-584`, contact form fields, and the later `.mt0`
  padding drift.
- Extended the editor harness to assert button pseudo-element color,
  variable-font weight settings, and non-compact button padding so the old
  compact legacy button cluster does not return silently.
- Local verification on 2026-06-23:
  - direct Lightning CSS editor compile passed;
  - Prettier check for touched CSS/test files passed;
  - editor harness passed with elevated local browser access: 5/5.

### Phase 5 Vendor Import Isolation

- Removed `vendor-overrides/index.css` from `styles/editor.css`; Ecwid,
  EmailOctopus, and Messenger overrides remain frontend/runtime-owned.
- Kept Jetpack slideshow and contact form behavior block-owned through their
  block styles instead of the generic vendor override layer.
- Live editor-canvas probe on 2026-06-23:
  - Shop editor page had zero `.ec-store`, `.grid-product__title-inner`,
    `.ec-header-*`, or `.ec-fbmessenger-chat` matches.
  - Edu Giveaway editor page had zero `.emailoctopus-form`, `[data-form]`,
    `[eo-block]`, or `.wp-block-emailoctopus-form` matches.
  - Edu Giveaway editor page still rendered Jetpack slideshow blocks, so those
    remain covered by block ownership.

### Phase 6 Legacy Editor Quarantine

- Added canonical editor imports for `layout/index.css`, `base/elements.css`,
  `blocks/jetpack-contact-form.css`, and `page-types/index.css`.
- Added footer layout/logo imports to `styles/components/index.css` so editor
  component ownership matches the remaining footer/logo selectors.
- Replaced active `styles/blocks/legacy-editor.css` declarations with
  release-watch comments only.
- The release-watch comments cover generated navigation drift, frontend-only
  vendor selectors, unknown helper selectors such as `.pt15`, `.ml0`,
  `.mw-584`, `.is-50vw`, and the inherited `.mt0` padding drift.
- Local verification on 2026-06-23:
  - direct Lightning CSS editor compile passed;
  - Prettier check for touched CSS files passed;
  - editor harness passed with elevated local browser access: 5/5.

### Phase 7 Complete: Legacy Editor Holding Area Removed

- Completed on 2026-06-23 by explicit owner override of the release-watch
  window.
- Removed the `blocks/legacy-editor.css` import from `styles/editor.css`.
- Deleted the comment-only `styles/blocks/legacy-editor.css` quarantine file.
- Removed the deleted file from Stylelint override ignores.
- Rebuilt the editor bundle with the local Lightning CSS binary after
  `pnpm compile:css` hung without output in this environment.
- Verification on 2026-06-23:
  - Prettier check passed for touched JSON, CSS entrypoints, and docs.
  - Direct Lightning CSS frontend/editor compile passed.
  - Full editor harness passed: 5/5.
  - Full frontend visual suite reached 29/30 twice with the same desktop Home
    screenshot stability timeout; the isolated desktop Home snapshot passed
    between full runs. `editor.min.css` had no content diff, only the source map
    changed after deleting the comment-only import source.
  - `git diff --check` passed.
  - Full Stylelint still reports pre-existing issues in `buttons.css`,
    `core-navigation.css`, and `settings.css`; no new selector lint failures
    were introduced by the Phase 7 deletion.

## Phased Implementation Plan

### Phase 1: Establish Editor Regression Harness

Dex task: `og5ex7oy`

- Add a repeatable way for Playwright to authenticate into the local WordPress
  admin/editor.
- Save local-only auth state for future editor tests.
- Create or identify editor fixture content that exercises the high-risk blocks
  and content classes.
- Establish reference sources for the fixture: current local, previous
  committed theme CSS where practical, production/public design language, and
  existing frontend visual references.
- Spot-check real editor pages when relevant, starting with Home, Mary Barbour,
  Edu Giveaway, and Shop.
- Add initial computed-style assertions for representative editor canvas rules.
- Capture the current editor state and at least one reference comparison before
  cleanup so later changes have a meaningful before/after review.
- Define which differences will be treated as regressions, parity corrections,
  or intentional improvements.

Verification:

- WP-CLI can create or access the authenticated editor session, or the blocker
  is documented.
- Playwright can open a block editor URL without manual login.
- The current editor CSS state and selected reference source are both captured
  or the missing reference is documented.
- Initial assertions distinguish preservation checks from quality improvements.
- Existing frontend visual tests still pass or any pre-existing failure is
  recorded.

### Phase 2: Inventory Editor CSS Ownership

Dex task: `yjf3zv3s`

- Classify active `legacy-editor.css` rules by owner:
  - `theme.json`
  - registered block stylesheet
  - shared fonts/settings
  - editor canvas/base
  - component
  - utility
  - content/page composition
  - vendor/editor override
  - release-watch or deletion candidate
- Note which rules are true editor-only affordances.
- Identify duplicated imports such as frontend vendor overrides in the editor
  entrypoint.
- Choose the first low-risk cleanup batch.

Verification:

- Inventory is recorded in this document, a follow-up doc, or the Dex task
  result.
- No CSS behavior changes are made in this phase except test harness support.
- Phase 3 candidates have explicit before/after assertions.

### Phase 3: Remove Shared And Native Block Duplication

Dex task: `y1xujih7`

- Remove `legacy-editor.css` rules already owned by shared fonts/settings,
  `theme.json`, or registered block styles.
- Start with lower-risk duplicated defaults:
  - heading typography;
  - quote;
  - separator;
  - image;
  - cover;
  - group;
  - columns;
  - social links;
  - Jetpack slideshow rules already covered by block-owned styles.
- Keep block-specific editor differences only when the editor harness proves
  they are needed.

Verification:

- `pnpm compile:css:editor`
- Editor Playwright harness for affected blocks
- Existing frontend visual suite when shared files, block styles, or
  `theme.json` changed
- Dex result records deleted, moved, or retained selectors

### Phase 4: Rationalize Editor Components And Utilities

Dex task: `xnomud70`

- Decide which button, footer, logo, spacing, width, and helper-class rules
  should be shared with frontend, editor-scoped, or deleted.
- Keep frontend-only composition out of the editor bundle unless fixture content
  proves the editor needs it.
- Keep author-applied utility classes available when they are part of current
  content editing, but remove duplicate definitions from `legacy-editor.css`.
- Decide whether button styling should remain a shared component, gain an
  editor-specific companion rule, or move partially into `theme.json` or
  `core/button` ownership.

Verification:

- Editor fixture checks cover affected utilities/components.
- Frontend visual suite passes if shared utilities/components changed.
- Any retained editor-specific component rule explains why it is not shared.

### Phase 5: Isolate Editor Vendor And Plugin Overrides

Dex task: `8055xkjq`

- Audit whether Ecwid, EmailOctopus, Messenger, Jetpack, BlockMeister, or other
  plugin output actually renders inside the block editor.
- Remove `vendor-overrides/index.css` from `styles/editor.css` if the current
  import only brings frontend runtime fixes into the editor.
- Create editor-specific vendor override files only for proven editor surfaces.
- Keep `!important` only for plugin/editor cascade boundaries that are visible
  in the editor and covered by assertions or screenshots.

Verification:

- Editor fixture or inspected editor route proves each retained plugin rule is
  needed.
- Frontend vendor regression checks still pass when shared vendor files change.
- Dex result lists removed frontend-only vendor imports and retained editor
  exceptions.

### Phase 6: Quarantine Or Delete Editor Legacy Selectors

Dex task: `2xja02f0`

- Audit remaining `legacy-editor.css` selectors against:
  - child-theme source;
  - WordPress content;
  - templates and patterns;
  - editor fixture rendering;
  - frontend rendered coverage where shared behavior is possible.
- Delete rules with no owner and no rendered/editor evidence.
- Move uncertain rules to dated release-watch comments with the evidence and a
  removal condition.

Verification:

- `legacy-editor.css` has no active unowned declarations.
- Release-watch comments cite the date, evidence, and removal condition.
- Editor and frontend regression gates pass.

### Phase 7: Remove Editor Legacy Holding Area

Dex task: `dodihavw`

- Start only after the release-watch window leaves `legacy-editor.css` empty or
  comment-only.
- Remove the `blocks/legacy-editor.css` import from `styles/editor.css`.
- Delete `legacy-editor.css` when no active or release-watch selectors remain.
- Record final editor CSS ownership and any follow-up debt in Dex.

Verification:

- `pnpm compile:css:editor`
- Full editor Playwright harness
- Full frontend visual suite
- Stylelint/format checks for touched CSS/docs
- Dex parent task `sihwwt4w` records final outcome or remaining open subtasks
