# Legacy Utility Class Removal Plan Before Designer Handback

## Summary

Remove generic legacy utility classes from the standalone theme before designer
handoff, but do it through explicit migration batches rather than deleting live
saved-content classes. The current goal is to replace generic layout/spacing
classes with semantic PNS hooks, block styles, template-part selectors, or
plugin-owned CSS, while preserving the accepted visuals.

This plan is tracked in Dex under parent task `to9qfw83`.

Status: completed on 2026-07-06.

## Scope

In scope:

- Generic utility selectors in
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/utilities/index.css`.
- Filesystem templates, parts, patterns, and synced patterns.
- DB-backed `page`, `post`, `herstory`, `wp_block`, `wp_navigation`,
  `wp_template`, and `wp_template_part` content.
- Frontend/editor tests that currently assert generic utility behavior.

Out of scope:

- Removing `pns-*` hooks by default. These are semantic/theme-owned hooks unless
  a specific hook is proven redundant and migrated.
- Refreshing visual references while concurrent Our Shop/Ecwid shared-block work
  is still moving snapshots.
- Combining utility deletion with broader `theme.json` spacing/control changes.

## Current Inventory

Refreshed on 2026-07-05 with exact class-token DB checks plus source search.

| Class             | Current owner                                | Exact DB records | Disposition                           |
| ----------------- | -------------------------------------------- | ---------------: | ------------------------------------- |
| `no-gap`          | No current DB owner found                    |                0 | Remove after fresh source/DB recheck. |
| `mt0`             | No current DB owner found                    |                0 | Remove after fresh source/DB recheck. |
| `mb05`            | No current DB owner found                    |                0 | Remove after fresh source/DB recheck. |
| `pl1`             | No current DB owner found                    |                0 | Remove after fresh source/DB recheck. |
| `pr1`             | No current DB owner found                    |                0 | Remove after fresh source/DB recheck. |
| `max-inline-size` | CSS-only/source cleanup candidate            |                0 | Remove after source recheck.          |
| `p1`              | Migrated from footer template part           |                0 | Completed in `pnl710sq`.              |
| `p2-m`            | Migrated from footer template part           |                0 | Completed in `pnl710sq`.              |
| `w-100`           | Migrated from footer plus editor fixture     |                0 | Completed in `pnl710sq`.              |
| `vw-100`          | Migrated from synced/draft/test fixtures     |                0 | Completed in `ppwc51ds`.              |
| `w-50-m`          | Migrated from Connect Social/editor fixture  |                0 | Completed in `ppwc51ds`.              |
| `lh0`             | Migrated from Connect Social/Wikipedia draft |                0 | Completed in `ppwc51ds`.              |
| `mw-intro-text`   | Migrated from pages plus editor fixture      |                0 | Completed in `k1jojqjj`.              |
| `pns-intro-copy`  | Intro paragraph measure semantic hook        |                6 | Keep as semantic PNS hook.            |
| `grid`            | Migrated from hero/herstory class tokens     |                0 | Completed in `umgti392`.              |
| `m-auto`          | Migrated from hero/herstory class tokens     |                0 | Completed in `umgti392`.              |
| `pr0`             | Migrated from hero/herstory class tokens     |                0 | Completed in `umgti392`.              |
| `rubik`           | Migrated from Herstory active-date classes   |                0 | Completed in `umgti392`.              |

Keep and normalize, not removal targets:

- `pns-section-inner`
- `pns-section-frame`
- `pns-content-frame`
- `pns-copy-column`
- `pns-hero-copy`

Review separately:

- `.is-content-justification-right`, because it is a WordPress core block-state
  class override, not a PNS legacy utility.

## Dex Work Plan

| Order | Dex        | Task                                 | Gate                                                                                                                     |
| ----: | ---------- | ------------------------------------ | ------------------------------------------------------------------------------------------------------------------------ |
|     0 | `37jm3n5h` | Freeze visual and content baseline   | Our Shop/Ecwid shared-block work no longer moves snapshot output, or remaining failures have a named external owner.     |
|     1 | `tz63v9wl` | Remove zero-hit utilities            | Fresh exact DB/source checks still show no live owners for `no-gap`, `mt0`, `mb05`, `pl1`, `pr1`, and `max-inline-size`. |
|     2 | `pnl710sq` | Replace footer-only utilities        | Footer `p1`, `p2-m`, and `w-100` behavior moved to footer semantic selectors/template part.                              |
|     3 | `ppwc51ds` | Replace synced full-bleed utilities  | `vw-100`, `w-50-m`, `lh0`, `grid`, and `m-auto` migrated out of Connect Social and related fixtures.                     |
|     4 | `umgti392` | Replace hero and Herstory utilities  | `grid`, `m-auto`, `pr0`, and `rubik` replaced in high-volume hero/herstory content.                                      |
|     5 | `k1jojqjj` | Decide measure and embed utilities   | `mw-intro-text` and `inline-container` classified as semantic replacement, vendor owner, or documented keep.             |
|     6 | `aedypetx` | Remove CSS and tests after migration | Removed utilities have zero exact DB hits and no source/test dependencies.                                               |

## Implementation Notes

### Phase 1 - `tz63v9wl`

Status: completed on 2026-07-05.

Fresh exact DB token checks returned zero current non-trash/non-revision records
for:

- `no-gap`
- `mt0`
- `mb05`
- `pl1`
- `pr1`
- `max-inline-size`

`mt0`, `no-gap`, and `mb05` had already been removed from active CSS by the
earlier spacing-utility migration. This phase removed the remaining source-only
CSS definitions for `pl1`, `pr1`, and `.max-inline-size` from
`styles/utilities/index.css`.

### Phase 2 - `pnl710sq`

Status: completed on 2026-07-05.

DB backup:
`docs/jobs/legacy-utility-db-backups/20260705-before-pnl710sq-footer-utility-cleanup.sql`
(local-only, not committed).

Fresh exact DB token checks before migration found:

- `wp_template_part` `5980`, `PNS - Footer`: `p1`, `p2-m`, and `w-100`
- private editor fixture page `5216`, `PNS Editor CSS Fixture`: `w-100`

The footer filesystem part and saved template part now use footer-owned
semantic classes instead:

- `pns-footer__inner`
- `pns-footer-bottom-bar`
- `pns-footer-bottom-bar__inner`

The previous `p1`/`p2-m` spacing behavior is preserved in
`styles/components/footer-layout.css`. The previous footer `w-100` full-width
behavior is covered by the existing footer bottom-bar full-bleed selectors. The
editor fixture no longer exercises deleted `w-100` utility behavior.

Post-migration exact DB and authored-source scans returned zero current
non-trash/non-revision records or active source/test hits for `p1`, `p2-m`, and
`w-100`.

### Phase 3 - `ppwc51ds`

Status: completed on 2026-07-05.

DB backup:
`docs/jobs/legacy-utility-db-backups/20260705-before-ppwc51ds-synced-utility-cleanup.sql`
(local-only, not committed).

Fresh exact DB token checks before migration confirmed the `ppwc51ds` owners:

- `wp_block` `1494`, `PNS - Connect Social`: `vw-100`, `w-50-m`, `lh0`,
  `grid`, and `m-auto`
- `wp_block` `1493`, `PNS - Contact Form`: `grid`
- draft page `1797`, `Our work with Wikipedia`: `vw-100`, `lh0`, `grid`, and
  `m-auto`
- private page `1861`, `[ Test Page 2 - FEEL FREE TO PLAY ABOUT WITH EDITING
THIS ONE :) ]`: `vw-100`
- private editor fixture page `5216`, `PNS Editor CSS Fixture`: `vw-100`,
  `w-50-m`, and `m-auto`

The reusable-block and fixture utility classes were migrated to semantic
section/layout classes:

- `pns-connect-social__columns`
- `pns-connect-social__copy-column`
- `pns-connect-social__copy`
- `pns-connect-social__media-column`
- `pns-connect-social__image`
- `pns-contact-form__copy-column`
- existing split-section and `pns-content-frame` primitives where applicable

The full-bleed, half-width image, media-column line-height, and synced copy
spacing behavior now lives in `styles/components/synced-sections.css` and the
existing split-section CSS. The global `vw-100`, `w-50-m`, and `lh0` utility CSS
was removed after post-migration DB and authored-source scans returned zero
hits.

`grid` and `m-auto` were removed from the synced blocks and fixtures covered by
this phase, but remain in hero/herstory/high-volume saved content for
`umgti392`.

### Phase 4 - `umgti392`

Status: completed on 2026-07-05.

DB backup:
`docs/jobs/legacy-utility-db-backups/20260705-before-umgti392-hero-herstory-utility-cleanup.sql`
(local-only, not committed).

The hero and Herstory utility classes were migrated to semantic owners:

- `pns-hero__inner`
- `pns-hero-copy`
- `pns-suffragette-facts__copy-column`
- `pns-suffragette-facts__copy`
- existing `pns-section-inner` and `pns-copy-column` primitives

The previous `grid`, `m-auto`, `pr0`, and `rubik` behavior now lives in
component/page-type CSS instead of global utilities:

- hero wrappers use `styles/components/hero.css`
- active-date typography and facts copy layout use
  `styles/page-types/herstories-bios.css`

Saved-content migration covered published Herstory CPTs, Herstory-like draft
pages, Pattern QA/editor fixtures, and affected page-hero content. A follow-up
repair split merged saved Herstory hero wrappers back into the same two-level
shape as the code-backed hero patterns: outer `pns-hero__inner
pns-section-inner`, inner `pns-hero-copy pns-copy-column`. This preserved the
front-page hero alignment model for Herstory heroes.

Post-migration class-aware DB scans returned zero current non-trash/non-revision
records with `grid`, `m-auto`, `pr0`, or `rubik` as saved class tokens. Remaining
plain-text `grid` matches are false positives from editorial prose, Ecwid
`grid-product` names, and the Herstory archive Query block's `layout.type=grid`.
The broad exact text scan is therefore not the acceptance gate for this phase;
the class-aware scan is.

### Phase 5 - `k1jojqjj`

Status: completed on 2026-07-05.

DB backup:
`docs/jobs/legacy-utility-db-backups/20260705-before-k1jojqjj-intro-copy-utility-cleanup.sql`
(local-only, not committed).

Fresh exact DB token checks before migration found `mw-intro-text` on paragraph
blocks in:

- page `1786`, `Educational Resources`
- page `1789`, `About`
- page `2363`, `Shenanigans`
- page `4629`, `Education Pack Giveaway`
- private page `5216`, `PNS Editor CSS Fixture`
- draft page `6118`, `Educational Resources`

`mw-intro-text` was first migrated to `pns-intro-copy`, then the structure was
corrected so the measure is owned by block settings rather than CSS. The
semantic `pns-intro-copy` hook now belongs to a `core/group` wrapper with
`layout.type=constrained` and `layout.contentSize=40rem`; the paragraph inside
keeps its typography/color attrs and no longer carries an intro-copy utility
class.

`inline-container` had no current saved-content class-token owner. It is an
EmailOctopus runtime wrapper, so its centering rule moved out of generic
utilities and into `styles/vendor-overrides/emailoctopus.css`, scoped to the
hosted form surface that emits the class.

Post-migration exact DB checks returned zero current non-trash/non-revision
records for `mw-intro-text` and `inline-container`, six current `core/group`
owners for the semantic `pns-intro-copy` hook, and zero paragraph owners for
`pns-intro-copy` in block attrs or serialized paragraph HTML.

Follow-up editor-gate correction on 2026-07-05: elevated Playwright proved a
CSS compatibility selector was the wrong owner because WordPress editor
root-container layout overrode the paragraph max-width. The backup-backed
`aedypetx` structure migration moved all six intro-copy paragraphs into
constrained Group wrappers, repaired six stale serialized paragraph class
tokens, and removed the compatibility CSS path.

## Migration Rules

- Run a DB backup before any saved-content mutation.
- Use a dry-run replacement report before applying DB changes.
- Apply one utility family or one ownership surface per commit.
- Do not delete the CSS for a utility until DB and source checks both return no
  live owners, unless an alias is deliberately retained for compatibility.
- Keep `pns-*` hooks stable unless a task explicitly says to migrate that hook.
- Keep visual changes named. If a batch should be visually neutral, treat
  visual drift as a blocker unless it is classified as concurrent shared-block
  drift.

## Test Plan

For every batch:

- Fresh exact class-token DB inventory.
- CSS compile.
- CSS lint and formatting checks.
- Focused browser checks for touched routes.
- Relevant frontend/editor Playwright contracts.
- Full visual suite only after the shared-block baseline is stable.

Required route focus:

- `/`
- `/herstories/mary-barbour/`
- `/herstories/`
- `/artworks/`
- `/educational-resources/`
- `/shenanigans/`
- `/shenanigans/glasgow-herstory-workshops/`
- `/shenanigans/workshop-unleashing-the-suffragette-spirit/`
- `/edu-giveaway/`
- `/shop/`
- `/pns-pattern-qa/`

## Handback Criteria

- No generic legacy utility remains in saved content unless explicitly
  documented as retained with an owner.
- Designer-facing patterns use semantic names or block styles rather than
  `grid`, `m-auto`, `vw-100`, `pr0`, `rubik`, `lh0`, and similar generic
  utilities.
- Tests assert semantic behavior, not the legacy utility implementation.
- The visual baseline is green or blocked only by a named external owner.

## Closeout

Closed on 2026-07-06.

Final closeout evidence:

- All seven Dex child tasks under `to9qfw83` are complete.
- Exact parsed-block DB scan across current non-revision saved content returned
  `[]` for the retired utility class-token set:
  `no-gap`, `mt0`, `mb05`, `pl1`, `pr1`, `max-inline-size`, `p1`, `p2-m`,
  `w-100`, `vw-100`, `w-50-m`, `lh0`, `grid`, `m-auto`, `pr0`, `rubik`, and
  `mw-intro-text`.
- Authored-source scan returned no active template, part, pattern,
  synced-pattern, style, or test hits for the retired non-semantic utility
  names.
- CSS compile passed.
- Block-template validation passed for 15 files.

Visual closeout note: the full visual suite was not rerun as the final parent
gate because concurrent Search/Search Results template work and independent
video-cover/plugin work are dirty in the worktree. The utility-specific removal
evidence is clean; any remaining visual-gate work belongs to those active
external owners or later parent validation under `511irrlz`.
