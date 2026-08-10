# Legacy Spacing, Saved Content, And Rhythm Cleanup

Created: 2026-07-08.

All paths are relative to the project root.

## Summary

Run a scan-first cleanup for three compatibility debts: legacy `theme.json`
spacing sizes, saved font preset fallback CSS, and fragile content rhythm
heuristics. Legacy spacing remains the priority: audit every use of numeric
spacing slugs, migrate source and approved DB-backed content to semantic slugs,
then remove the `Legacy *` spacing sizes from `theme.json`.

This plan is tracked by Dex parent `ypq381w9`.

## Current Status

- Completed and archived. Dex parent `ypq381w9` and its child tasks are closed.
- Source and approved DB-backed legacy spacing references were migrated to
  semantic slugs, and `Legacy 20` through `Legacy 80` were removed from
  `theme.json`.
- Saved font preset compatibility cleanup is complete. The title-size paragraph
  line-height dependency was migrated into source/current DB block data with
  rollback exports, and `styles/base/font-preset-compat.css` was removed.
- The paragraph baseline rhythm sub-issue is fixed under Dex child `cccts8n4`;
  retained content-rhythm exclusions are documented as intentional compatibility
  boundaries.

## Paragraph Baseline Rhythm Issue

Recent local/prod comparison screenshots showed ordinary paragraphs can lose
the expected baseline bottom rhythm in composed page content. This belongs in
the content-rhythm hardening slice, not as a separate visual-polish job, because
the likely overrides live in the same heuristic layer:

- `styles/base/elements.css` defines the intended baseline paragraph bottom
  rhythm with `p { margin-block-end: var(--pns--content-rhythm--paragraph-margin-block-end); }`.
- `styles/page-types/content-rhythm.css` currently resets direct content-root
  children and direct root paragraphs to `margin-block-end: 0`.
- The same file also resets sibling top margins in PNS sections and generated
  `.is-layout-flow` containers, which can remove visible paragraph separation
  when saved block composition does not provide a stable explicit block gap.

Acceptance for the rhythm slice now includes:

- identify the rendered route(s) and exact computed-style owner suppressing the
  paragraph gap;
- restore a boring baseline bottom rhythm for ordinary prose paragraphs in
  frontend and editor content;
- preserve root stack, generated-layout, vendor, navigation, buttons, search,
  EmailOctopus, Ecwid, and other control-surface exclusions unless targeted
  tests prove they can safely change;
- add or update focused visual/editor assertions so ordinary paragraph rhythm
  cannot silently collapse again.

Implemented evidence for the paragraph sub-issue:

- `/about/` Who We Are paragraphs now compute `margin-bottom: 10px` and an
  actual 10px gap to the next paragraph or button group.
- `tests/visual/frontend.spec.ts` includes `/about/` in the content rhythm
  coverage and asserts the baseline paragraph bottom rhythm.
- Validation passed: touched-file Prettier, `lint:css`, `compile:css`, and
  `test:visual:layout` with 23 desktop and 8 mobile tests.

## Key Changes

- Added `scripts/migrate-saved-content-compat.php` for the completed DB
  migration, then retired it during the post-migration cleanup.
- The migration used dry-run by default and required `--apply` for DB mutation
  after explicit user approval.
- Rollback exports were written to `docs/jobs/saved-content-compat-db-backups/`.
- Use WordPress block parsing/serialization for block content changes; do not
  raw-string replace serialized block comments.
- Audit and migrate numeric spacing slugs:
  - `20` -> `extra-small`
  - `30` -> `compact`
  - `40` -> `small`
  - `50` -> `medium`
  - `60` -> `x-large`
  - `70` -> `2-x-large`
  - `80` -> `3-x-large`
- Remove `Legacy 20` through `Legacy 80` from `theme.json` only after source and
  current non-revision DB usage is clean.
- Add semantic `x-large`, `2-x-large`, and `3-x-large` spacing presets only when
  audit evidence shows live content still needs those values.
- Audit saved `.has-large-font-size`, `.has-x-large-font-size`, and
  `.has-medium-font-size` usage before removing `font-preset-compat.css`.
- Harden `content-rhythm.css` after spacing cleanup, with the paragraph baseline
  issue as an explicit requirement.

## Implementation Order

1. Maintain Dex parent and children for legacy spacing audit, source migration,
   DB dry-run/apply, `theme.json` removal, font preset compatibility audit, and
   content rhythm hardening.
2. Add the migration script in dry-run mode first.
3. Update source templates, parts, patterns, and synced patterns before DB work.
4. Run DB dry-run and stop for approval before `--apply`.
5. After approved apply, rerun the audit and require zero current non-revision
   refs to numeric legacy spacing slugs before removing them from `theme.json`.
6. Remove legacy spacing entries from `theme.json`; add semantic large spacing
   replacements only if audit evidence requires them.
7. Reconcile paragraph baseline rhythm and retained content-rhythm heuristics.
8. Rebuild CSS and run validation.

## Validation

Source/template validation:

```bash
php app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-block-templates.php
```

Standard validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
```

Conditional validation:

- If shop/Ecwid spacing refs are touched: run `test:visual:shop` or
  `test:visual:ecwid`.
- If EmailOctopus/contact refs are touched: run `test:visual:emailoctopus`.
- If broad rhythm behavior changes: finish with the lean `test:visual` gate.

## Assumptions

- Legacy numeric spacing slugs should be removed from `theme.json`, not renamed
  in place.
- DB mutation requires explicit approval after dry-run.
- Revisions are counted but not migrated in this pass.
- Exact visual preservation is preferred for `40` and `50`; near-match semantic
  cleanup is acceptable for `20` and `30`.
- Large semantic replacements are added only if live content still needs those
  values.
- `h3rs0t00` remains client-approval pending and out of scope.
