# Core Block Selector Classification Plan

Created: 2026-07-07.

All paths are relative to the project root.

## Purpose

Classify broad CSS selectors that currently shape WordPress core blocks so the
theme can distinguish real defaults from PNS-specific component, page, pattern,
and compatibility behavior.

This is an architecture cleanup plan, not a polish pass. It must not be read as
approval to delete selectors. The first landing cut is evidence and
classification only.

## Related Work

- Parent backlog evidence:
  `docs/jobs/2026-07-06-theme-css-control-health-remediation-plan.md`
- Typography and rhythm split:
  `docs/jobs/2026-07-06-typography-rhythm-polish-plan.md`
- Navigation drawer migration commit:
  `3b7aa2260 refactor(nav): use core responsive drawer`

## Current Evidence

The parent control-health plan identifies these candidate files for broad
selector review:

- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/base/elements.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/layout/index.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/page-types/content-rhythm.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/components/section-theme.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/components/light-surface.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/blocks/core-navigation.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/blocks/core-navigation-base.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/blocks/core-navigation-desktop.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/blocks/core-navigation-core-drawer.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/blocks/core-cover.css`

The intended owner categories are:

- real core block default;
- page-type rhythm;
- component or pattern surface;
- saved-content compatibility;
- vendor or control exclusion.

## Non-Goals

- Do not remove selectors during classification.
- Do not migrate saved classes, spacing presets, palette slugs, or serialized
  block-support output in this task.
- Do not use this task to reopen the accepted core Navigation drawer migration.
- Do not classify Herstories migration work as active; `h3rs0t00` is awaiting
  client approval.
- Do not edit parent themes or third-party plugins.
- Do not alter duplicate block CSS load paths without checking
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/css-assets.json`
  and `app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/assets.php`.

## Guardrails

- Treat completed plans as historical evidence. Refresh live filesystem and
  DB-backed state before any implementation task uses this plan.
- Keep the first pass scan-only and reviewable as a table or markdown inventory.
- Prefer comments and tests over selector deletion when the selector is a
  legitimate compatibility bridge.
- If a selector is PNS-specific, the preferred future target is an explicit
  pattern class, block style, or surface class rather than another broad core
  selector.

## Dex Tracking

This plan is queued in the standalone tracker:

- Parent: `mv15xqi8` - Classify core-block selector ownership before CSS
  hardening
- Cut 0: `h7fkwbp6` - Selector classification Cut 0 - refresh evidence and
  inventory broad selectors

Use the standalone tracker directory:

```bash
dex --storage-path app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex list --all
```

Do not pre-create implementation children until the scan cut has produced a
reviewed classification.

## Execution Plan

### Cut 0 - Refresh Evidence

Objective: confirm current source, active theme, and DB-backed content before
classifying selectors.

Steps:

- Run `git status --short`.
- Reconfirm active theme and DB-backed owner rows with WP-CLI.
- Run a source scan for broad selectors in the candidate files.
- Record whether each selector targets core block defaults, PNS surfaces, saved
  compatibility, or vendor/control exclusions.
- Record whether each candidate file is bundled, block-registered, or both.

Acceptance:

- A classification table exists before edits.
- Each candidate file has at least one owner decision or a recorded no-op.
- Any DB-backed risk is named before implementation is considered.

### Cut 1 - Add Owner Comments And Tests Only Where Needed

Objective: make retained broad selectors understandable without changing
behavior.

Steps:

- Add short comments only for broad selectors that cannot be narrowed safely.
- Add or update computed-style tests for high-risk retained selectors.
- Avoid snapshot baseline refresh unless drift is classified and accepted.

Acceptance:

- Retained compatibility selectors have a reason and deletion gate.
- Ordinary editor-created core blocks are not accidentally documented as PNS
  components.

### Cut 2 - Queue Narrow Replacement Work

Objective: split implementation into small follow-up tasks only after the
classification has been reviewed.

Possible follow-ups:

- Move PNS surface behavior to explicit component or pattern classes.
- Replace saved-content compatibility with a DB-backed migration.
- Leave true core defaults in base/block CSS.
- Leave vendor/control exclusions scoped and documented.

## Validation

Scan and compile:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
```

Targeted visual lanes if behavior changes:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
```

Editor validation if controls or editor CSS are touched:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
```

## Done When

- The candidate selector set is classified.
- Any retained broad selector has a documented reason or is split to a specific
  follow-up.
- No implementation task proceeds without current evidence and a scoped
  validation plan.

## Cut 0 Evidence - 2026-07-07

Cut 0 produced a scan-only classification inventory:

`docs/jobs/2026-07-07-core-block-selector-classification-inventory.md`

No CSS behavior changes were made.
