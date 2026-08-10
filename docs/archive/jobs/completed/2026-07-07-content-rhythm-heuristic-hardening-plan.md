# Content Rhythm Heuristic Hardening Plan

Created: 2026-07-07.

All paths are relative to the project root.

## Purpose

Reduce the fragility of `content-rhythm.css` without losing the practical
baseline it currently gives normal WordPress block composition.

This plan starts from the current decision that
`styles/page-types/content-rhythm.css` remains the central rhythm owner. The
goal is to harden the heuristic layer only where replacements are proven, not
to chase pure selector elegance.

## Related Work

- Parent backlog evidence:
  `docs/jobs/2026-07-06-theme-css-control-health-remediation-plan.md`
- Typography and rhythm polish:
  `docs/jobs/2026-07-06-typography-rhythm-polish-plan.md`
- Core selector classification plan:
  `docs/jobs/2026-07-07-core-block-selector-classification-plan.md`

## Current Evidence

`content-rhythm.css` currently owns:

- top-level content stack spacing for `.wp-block-post-content` and
  `.entry-content`;
- editor root stack spacing;
- exclusions for nested flow containers without explicit block gap;
- PNS section and template-part rhythm resets;
- list, marker, and quote rhythm;
- exclusions for Navigation, Social Links, Query Pagination, buttons, Jetpack,
  Search, EmailOctopus, and Ecwid.
- generated-layout safeguards around `.is-layout-flow` and `wp-container-*`
  output.

Those exclusions are intentional until fresh tests prove otherwise.

Recent local/prod comparison screenshots also surfaced a paragraph baseline
rhythm risk: the global paragraph default in `styles/base/elements.css` sets a
bottom rhythm, but `content-rhythm.css` resets direct content-root paragraphs
and generated-flow sibling spacing. This hardening pass must identify the exact
rendered override and restore ordinary prose paragraph separation without
loosening vendor/control exclusions.

## Non-Goals

- Do not remove exclusions because they look ugly.
- Do not delete generated-layout heuristics until replacement selectors and
  editor/frontend tests exist.
- Do not make components depend on "magic classes" that editors cannot apply
  reliably in the block editor.
- Do not migrate saved content in this task unless a separately approved
  implementation cut includes affected-record scans and rollback criteria.
- Do not retune the whole typography scale.
- Do not touch Herstories migration work while `h3rs0t00` is awaiting client
  approval.

## Guardrails

- Prefer a boring, composable block foundation over exact recreation of old
  screenshots.
- Treat vendor and control surfaces as no-go zones unless a focused test proves
  they are unaffected.
- Preserve editor/frontend parity as a first-class acceptance criterion.
- If narrowing a heuristic requires new surface hooks, prove those hooks are
  available from normal block composition or existing pattern/block output.

## Dex Tracking

This plan is not queued yet. If accepted, create one parent task first:

`Harden content-rhythm heuristics without breaking block composition`

Do not pre-create implementation children until the audit identifies exact
replacement candidates.

## Execution Plan

### Cut 0 - Inventory Current Rhythm Responsibilities

Objective: produce a current map of what `content-rhythm.css` owns.

Steps:

- Run `git status --short`.
- Review `styles/page-types/content-rhythm.css` and the relevant tests in
  `tests/visual/frontend.spec.ts`.
- Classify each selector as baseline rhythm, component exclusion, vendor
  exclusion, editor parity, or compatibility debt.
- Identify selectors that depend on generated classes or broad
  `:where()` chains.
- Record the current exclusion list from lines around the Navigation, Social
  Links, Query Pagination, buttons, Jetpack, Search, EmailOctopus, and Ecwid
  rules before proposing changes.

Acceptance:

- Every selector in `content-rhythm.css` has a current responsibility.
- Current exclusions are either justified or named as candidates for later
  replacement.

### Cut 1 - Add Coverage Before Narrowing

Objective: protect the surfaces most likely to regress.

Test candidates:

- ordinary article/page content;
- ordinary paragraph baseline bottom rhythm, including paragraphs inside normal
  editor-composed groups;
- editor-created paragraph, heading, list, quote, and nested group content;
- Navigation and drawer content;
- Social Links;
- Query Pagination;
- Search;
- EmailOctopus;
- Ecwid.

Acceptance:

- Tests assert behavior that matters, not incidental selector strings.
- Editor and frontend rhythm are both covered where parity matters.

### Cut 2 - Replace Only Proven Heuristics

Objective: make small behavior-preserving replacements.

Allowed replacements:

- move component-owned spacing to component CSS;
- use explicit pattern or section classes where existing content already has
  them;
- add short comments for retained compatibility selectors;
- leave broad defaults in place when removing them would require risky saved
  content migration.

Acceptance:

- No vendor/control surface changes without targeted evidence.
- No unexplained generated-class exception remains if it can be replaced safely.

## Validation

Compile and lint:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
```

Targeted visual lanes:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:shop
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:emailoctopus
```

Editor validation if editor rhythm changes:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
```

## Done When

- The rhythm layer has fewer unexplained heuristics or a documented reason for
  keeping them.
- Components and vendor surfaces still opt out cleanly.
- Editor and frontend rhythm remain aligned on representative pages.
- Any saved-content migration risk is split to its own DB-backed task.
