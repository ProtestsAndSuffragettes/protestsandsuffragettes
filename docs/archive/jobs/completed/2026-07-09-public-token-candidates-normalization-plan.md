# Public Token Candidates Normalization Plan

Created: 2026-07-09.

All paths are relative to the project root.

## Purpose

Normalize the public token candidates identified by the private style guide's
`Token-System Debt` section without turning every CSS variable into a
`theme.json` token.

The first landing slice is the grayscale/color-token cleanup. The current
palette has author-facing names that do not describe the values honestly:
`foreground` is white, `dark-grey` and `light-grey` are only two points in a
neutral scale, and `settings.custom.color.primary` / `secondary` hide near-black
and medium-grey text values outside the palette. This plan should replace that
with a real neutral ladder and DB refresh rather than long-term aliases.

## Current Evidence

Fresh local evidence from 2026-07-09:

- Active work is in
  `app/public/wp-content/themes/protestsandsuffragettes-standalone`.
- Current public palette includes `foreground`, `brand-purple`, `deep-purple`,
  `red`, `accent-mint`, `dark-grey`, `light-grey`, and `brand-yellow`.
- Current hidden custom color values:
  - `primary`: `#161616`
  - `secondary`: `#4F4D49`
  - `separator-border`: `#D3CDC3`
- Current scoped DB hit counts:
  - `foreground`: 43 rows
  - `dark-grey`: 13 rows
  - `light-grey`: 8 rows
  - `primary`: 6 rows
  - `secondary`: 5 rows
  - `separator-border`: 1 row
- The existing color migration script only handles the old
  `background -> brand-purple` and `tertiary -> accent-mint` migration.
- `black` and `white` may exist in saved content through core/default palette
  usage even when they are not first-class PNS palette decisions.
- Generated `styles/dist/*` must be rebuilt, not edited manually.

## Dex Tracking

- Parent: `k8yd3l0x` - Normalize public token candidates and grayscale ladder
- Child: `mwlyku57` - Token normalization Cut 0 - audit and classify public
  candidates
- Child: `19hwxb22` - Token normalization Cut 1 - build grayscale migration
  tooling
- Child: `tfp99jr3` - Token normalization Cut 2 - migrate source grayscale
  references
- Child: `jjwju2pw` - Token normalization Cut 3 - DB dry-run apply and rescan
- Child: `ds6fbs7b` - Token normalization Cut 4 - remove retired color tokens
  and update style guide
- Child: `01j3oq9u` - Token normalization Cut 5 - normalize remaining public
  candidates
  - Child: `us5nnntn` - Token normalization Cut 5a - surface and color role
    tokens
  - Child: `g9xnbr4c` - Token normalization Cut 5b - button and form control
    primitives
  - Child: `03f65m72` - Token normalization Cut 5c - layout rhythm type and
    hard-coded values
- Child: `rulhtw76` - Token normalization Cut 6 - validate token migration
- Reopened closeout parent: `hc1pvy3k` - Token normalization closeout - fully
  address remaining candidate gaps
  - Child: `jy4q42t7` - Closeout Cut 0 - reconcile style-guide criteria and
    plan
  - Child: `3t2i1xip` - Closeout Cut 1 - retire stale private brand color
    aliases
  - Child: `3a4k16s3` - Closeout Cut 2 - prove button form and surface-role
    completion
  - Child: `xioyvl9m` - Closeout Cut 3 - resolve saved font preset
    compatibility
  - Child: `79rypzwf` - Closeout Cut 4 - classify residual hard-coded
    component values
  - Child: `0ig3x4lj` - Closeout Cut 5 - update style guide and validate fully
    addressed state
- Remaining cleanup parent: `whyzz3dq` - Remediate remaining token cleanup
  candidates
  - Child: `wjdhd1ol` - Remaining cleanup - reconcile Education Pack legacy
    spacing row
  - Child: `3qj0mprd` - Remaining cleanup - decide font preset fallback
    deletion
  - Child: `71ct59vx` - Remaining cleanup - simplify navigation private
    metrics
  - Child: `b0c8w0lu` - Remaining cleanup - harden content rhythm exclusions

## Related Remainder Plans

The grayscale ladder is the first landing slice. The remaining
`Public Token Candidates` work is split into focused sibling plans:

- Surface/color roles:
  `docs/jobs/2026-07-09-surface-color-role-token-normalization-plan.md`
- Control primitives for buttons and forms:
  `docs/jobs/2026-07-09-control-primitive-token-normalization-plan.md`
- Layout, rhythm, type, motion ownership, and residual hard-coded values:
  `docs/jobs/2026-07-09-layout-rhythm-type-hardcoded-value-normalization-plan.md`
- Gradient and duotone presets:
  define PNS-owned `theme.json` gradient and duotone tokens from the current
  palette before enabling those editor controls.

This split is deliberate:

- color and surface roles should follow the neutral ladder because their values
  depend on the corrected palette;
- buttons and forms need their own control-surface guardrails and vendor
  boundaries;
- layout, rhythm, type, and hard-coded spacing are coupled enough that a
  scan-first pass should classify them together before small implementation
  cuts;
- gradient and duotone controls should not inherit WordPress defaults; they
  need a small PNS-owned preset set based on the accepted palette and validated
  against real cover/image usage;
- motion is referenced through the existing motion plan unless the audit proves
  a public motion-token pass is useful.

## Fully Addressed Closeout Reopen

The first token cleanup was closed too optimistically. The style guide refresh
still labeled several areas as `Mostly Addressed`, which is useful progress
language but not a completion gate. This closeout pass treats those remaining
items as open until they are either fixed, moved into an explicit retained
private contract, or listed as genuine future work in `Remaining Cleanup
Candidates`.

### Current Closeout Gaps

- **Private brand aliases:** old private variables such as
  `--pns--color--accent-mint`, `--pns--color--red`, and
  `--pns--color--banner-yellow` still have authored CSS consumers even though
  equivalent public palette tokens now exist. This is not fully addressed until
  authored CSS outside `styles/dist` has zero stale private brand alias refs.
- **Buttons, forms, and surface roles:** the CSS and style guide are much
  closer, but this is not fully addressed until native buttons, block buttons,
  disabled states, secondary outline states, entry-navigation text actions, and
  basic form controls all share the same base contracts or have documented
  vendor-specific exceptions.
- **Saved font preset compatibility:** this is now closed. A follow-up
  migration moved compact line-height intent onto the title-size paragraph
  blocks that needed it, then deleted `styles/base/font-preset-compat.css`.
- **Residual hard-coded values:** the old raw counts are stale after token work,
  but remaining hard-coded type, spacing, rhythm, layout, motion, navigation,
  and vendor values still need a closeout classification. A value is complete
  only when it is either tokenized as a repeated cross-surface contract or
  documented as private component/vendor geometry.
- **Style-guide wording:** `Mostly Addressed` must disappear as a completion
  bucket. The final style guide should distinguish `Fully Addressed`,
  `Intentionally CSS-Private`, and `Remaining Cleanup Candidates`.

### Closeout Implementation Order

1. Update this plan and the Dex ledger so the reopen is visible.
2. Retire stale private brand color aliases from authored CSS and rebuild.
3. Audit buttons/forms/surface roles against the current style-guide examples
   and fix any remaining base-contract gaps.
4. Re-run the saved font preset compatibility audit against source and current
   non-revision DB content, then migrate/delete or explicitly retain with
   evidence.
5. Refresh the hard-coded/private-value scan and classify only the remaining
   meaningful values.
6. Update the private style-guide CSS-only section so complete areas are called
   fully addressed and remaining work is not hidden in a broad bucket.

### Closeout Acceptance Gates

- No authored CSS outside `styles/dist` references retired private
  `--pns--color--*` brand aliases.
- Button and form primitives have one coherent base behavior across block links
  and native controls, including disabled and outline variants.
- Surface role consumers use public custom color roles or local surface
  variables, not stale private brand aliases, except where a component-specific
  comment explains the ownership.
- Saved font preset compatibility has a fresh source/current-DB decision and no
  untracked dependency on `font-preset-compat.css`.
- Residual hard-coded values are either promoted, documented as private, or
  listed in `Remaining Cleanup Candidates` with a concrete owner.
- The style guide no longer uses `Mostly Addressed` for open cleanup work.

### Cut 3 Saved Font Preset Decision

2026-07-09 dry-run evidence keeps `styles/base/font-preset-compat.css` as a
documented retained compatibility file for now:

- source refs remain in 14 files across `parts/`, `patterns/`,
  `synced-patterns/`, `templates/`, and targeted CSS/tests;
- current non-revision DB refs remain in 38 rows across pages, herstories,
  templates, and the footer template part;
- revision refs are counted only and remain intentionally untouched;
- the dry-run also reported one unrelated remaining spacing row on the
  Education Pack Giveaway page. That should be handled by the legacy spacing
  cleanup path, not hidden inside this font-preset closeout slice.

This means the font preset fallback is not dead CSS. It is fully addressed for
this token closeout only as a named, evidenced compatibility contract. Deletion
requires a later source/current-DB normalization pass that removes saved
`has-medium-font-size`, `has-large-font-size`, and `has-x-large-font-size`
dependencies or proves WordPress generated preset CSS covers the compact
line-height behavior without visual regressions.

Follow-up implementation, 2026-07-09:

- Added `scripts/migrate-font-preset-line-height.php` as a dry-run/apply
  migration for title-size paragraph line-height.
- Updated source quote patterns/templates so non-heading
  `title-large`/`title-display` paragraphs serialize explicit line-height.
- Applied the DB migration to 30 current non-revision rows; revisions were
  counted but not mutated.
- Wrote rollback export
  `docs/jobs/font-preset-line-height-db-backups/20260709-210339-font-preset-line-height.json`.
- Re-ran the dry-run after the style-guide edits and confirmed zero current
  non-revision rows still require migration.
- Removed the `font-preset-compat.css` import from frontend/editor CSS and
  deleted the file.
- Updated the DB-backed style guide status with rollback export
  `docs/jobs/style-guide-db-backups/20260709-211221-font-compat-status.json`.

## Remaining Cleanup Candidate Remediation Queue

These items are no longer hidden inside the completed token/style-guide
closeout. They were queued as concrete follow-up work under Dex parent
`whyzz3dq` and completed on 2026-07-09.

Current status:

- no open token-remediation candidates remain in this plan;
- font preset compatibility is retained with a deletion trigger, not treated as
  dead CSS;
- navigation and content-rhythm work is documented as retained private
  contracts until rendered evidence proves a narrower deletion path;
- the next open CSS architecture work is the separate cascade-layer migration
  plan, `mlqbujkv`.

### A. Legacy Spacing DB Reconciliation

Dex: `wjdhd1ol`

Problem:

- The saved-content dry-run still reports one current non-revision legacy
  spacing row on the Education Pack Giveaway page.
- This is legacy spacing debt, not token CSS debt.

Remediation steps:

Accepted decision:

- Rerun the dry-run first even though the previous run identified the Education
  Pack Giveaway page. Do not directly apply stale audit output.

1. Re-run the saved-content compatibility dry-run with the legacy spacing
   scanner/migrator.
2. Confirm the exact Education Pack Giveaway row, post type, status, ID, title,
   old spacing slug/value, and proposed semantic replacement.
3. Export rollback data before any mutation.
4. Apply only after the row is confirmed as current non-revision content.
5. Re-run the source/current-DB spacing audit and require zero current
   non-revision legacy spacing refs, or document any retained row with a
   separate owner.
6. Do not mutate revisions in this pass.

Implementation result, 2026-07-09:

- Fresh dry-run now reports `0 spacing row(s) would change`.
- Direct inspection of page `4629`, Education Pack Giveaway, found saved font
  preset classes but no legacy spacing token or serialized numeric spacing
  slug.
- The previous `spacing-change` report was caused by the migration script
  parsing/serializing a font-only audit row. The script now rewrites block
  content only when spacing hits are present.
- No DB mutation or rollback export was needed for this slice.

Validation:

```bash
php app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-block-templates.php
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
```

The migration script was removed after the migration completed. Future
saved-content changes should recreate a narrow dry-run/apply script with a
rollback export path; do not use raw string replacement against serialized block
comments.

### B. Font Preset Fallback Deletion

Dex: `3qj0mprd`

Resolved problem:

- `styles/base/font-preset-compat.css` was retained because title-size
  paragraph blocks needed compact line-height that WordPress preset CSS does
  not generate.
- The compact line-height dependency has now been migrated into source/current
  DB block data, so the compatibility stylesheet has been deleted.

Remediation steps:

Accepted decision:

- Start with a rollbackable proof patch that removes the fallback from the
  build path and measures visual behavior. Do not begin with DB migration.

1. Re-run a fresh source/current non-revision DB audit for
   `.has-medium-font-size`, `.has-large-font-size`, and
   `.has-x-large-font-size`.
2. Separate true content intent from old saved block artifacts.
3. Test WordPress generated preset CSS without the fallback in a rollbackable
   branch or temporary patch.
4. Compare representative pages, templates, footer content, and style-guide
   samples for compact display line-height regressions.
5. If visual behavior is preserved, migrate source/current DB content as needed,
   delete `font-preset-compat.css`, remove its import, rebuild CSS, and update
   the style guide.
6. If visual behavior is not preserved, keep the fallback and update its header
   comment with the fresh audit counts and next deletion trigger.

Implementation result, 2026-07-09:

- The rollbackable proof patch removed the fallback import from
  `styles/frontend.css` and `styles/editor.css`, then rebuilt CSS.
- Computed font-size values stayed aligned with the current type scale, but
  compact line-height did not: `.has-large-font-size` changed from `42.4px` to
  `57.6px`, and `.has-x-large-font-size` changed from `45.573px` to `70.4px`
  on a mobile viewport.
- Follow-up migration applied explicit line-height to the saved title-size
  paragraphs that needed it, then removed the fallback import and deleted
  `font-preset-compat.css`.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
```

Run editor coverage if source or DB normalization changes saved block font-size
attributes.

### C. Navigation Simplification

Dex: `71ct59vx`

Problem:

- Navigation now uses the WordPress core responsive drawer, and the migration
  has settled visually.
- The private navigation property set is still large and should be reviewed for
  grouping, naming, and reduction without weakening the drawer/desktop
  breakpoint contract.

Remediation steps:

Accepted decision:

- Optimize for grouped ownership and clearer names before count reduction.
  Delete only proven dead metrics.

1. Inventory current navigation custom properties and selectors across
   `core-navigation-base.css`, `core-navigation-desktop.css`, and
   `core-navigation-core-drawer.css`.
2. Classify each value as desktop, drawer, submenu, open/close control,
   responsive breakpoint, motion, or obsolete.
3. Group or rename related private metrics where it improves readability.
4. Delete only proven unused metrics; do not reduce explicit values merely to
   make the count smaller.
5. Preserve the current contract:
   - core drawer at `999px` and below;
   - desktop nav from `1000px` upward;
   - open/close controls aligned without transition jank;
   - submenu toggles and hover/active states remain intact.
6. Document retained private navigation metrics near their owning CSS file.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
```

Use focused browser checks at representative widths around `950px`, `999px`,
and `1000px`.

Implementation result, 2026-07-09:

- The 31 private Navigation properties remain explicit because none were proven
  dead in this pass.
- `styles/shared/settings.css` now groups them by ownership:
  - shared shell metrics;
  - private type aliases;
  - desktop spacing and submenu chrome;
  - core responsive drawer layout and nested submenu geometry;
  - drawer chrome and local motion;
  - header open button and desktop hover marker metrics.
- No values, selectors, breakpoints, or open/close behavior changed. Further
  deletion requires focused rendered evidence that a property has no consumer
  or can be folded without weakening the settled drawer/desktop contract.

### D. Content Rhythm Hardening

Dex: `b0c8w0lu`

Problem:

- `content-rhythm.css` still contains generated-layout and vendor/control
  exclusions.
- Those exclusions should stay until targeted evidence proves a safer owner,
  but the plan needs an active hardening path rather than leaving them as vague
  debt.

Remediation steps:

Accepted decision:

- Keep baseline prose rhythm centralized. Move rhythm only when a stable
  component hook already exists; otherwise retain explicit exclusions with
  comments and evidence.

1. Re-audit `content-rhythm.css` exclusions against current rendered pages,
   generated layouts, forms, Navigation, Ecwid, Jetpack, and style-guide
   examples.
2. For each exclusion, classify it as:
   - generated-layout protection;
   - vendor/control protection;
   - component rhythm that can move to a stable hook;
   - obsolete.
3. Move rhythm only where a stable component hook already exists.
4. Add or refine comments for retained generated-flow and vendor/control
   exclusions.
5. Keep baseline paragraph/list/heading rhythm centralized; do not create
   component-local rhythm just to satisfy a theoretical purity goal.
6. Add focused coverage for any moved rhythm owner before deleting the old
   heuristic.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
```

Run vendor lanes when touched exclusions affect Ecwid, EmailOctopus, Jetpack,
or other plugin surfaces.

Implementation result, 2026-07-09:

- `content-rhythm.css` keeps baseline prose rhythm centralized.
- Retained paragraph and list exclusions are now documented by ownership:
  generated layout protections, WordPress control blocks, component/page hooks
  coupled to saved markup, and vendor adapter surfaces.
- No rhythm selectors were moved in this pass because each candidate either
  still depends on saved generated markup or would require focused visual
  coverage before deletion from the central heuristic.
- Future movement should target one stable owner at a time, such as
  `active-dates`, split-section CTA rhythm, or vendor adapter list resets, and
  should include a narrow visual lane for that owner.

## Token Ownership Rules

Promote a value to `theme.json` only when one of these is true:

- editors should intentionally select it;
- WordPress block supports can honestly own it;
- it is a reusable site-wide design value rather than a component detail.

Keep a value CSS-private when it compensates for:

- one component or template;
- vendor/plugin output;
- responsive geometry;
- z-index or overlay mechanics;
- hover markers, drawer spacing, logo sizing, or other implementation details;
- legacy saved-content quirks that should be migrated away.

## Recommended Grayscale Ladder

Use a numeric neutral ladder and preserve current values:

| New slug      | Value     | Replaces                               |
| ------------- | --------- | -------------------------------------- |
| `neutral-0`   | `#ffffff` | `foreground` and PNS-owned white usage |
| `neutral-50`  | `#F0F0F0` | `light-grey`                           |
| `neutral-200` | `#D3CDC3` | `separator-border`                     |
| `neutral-700` | `#4F4D49` | `custom.color.secondary`               |
| `neutral-800` | `#2B2B2B` | `dark-grey`                            |
| `neutral-950` | `#161616` | `custom.color.primary`                 |

Decision gate:

- If current non-revision content has deliberate `black` usage, decide whether
  to migrate it to `neutral-950` or add `neutral-1000: #000000`.
- If current non-revision content has raw `#f1f1f1`, decide whether it can
  normalize to `neutral-50` or whether `neutral-75` is required temporarily.

Recommended default:

- migrate `black` to `neutral-950` only where visual comparison proves the
  difference is acceptable;
- normalize `#f1f1f1` to `neutral-50` if it is incidental saved content;
- do not add legacy aliases unless a dry-run proves a rollback bridge is needed.

Accepted direction:

- Do not add `neutral-1000` unless the dry-run proves deliberate current content
  needs exact black preservation.
- Do not add `neutral-75` unless the dry-run proves `#f1f1f1` is deliberate,
  repeated, and visually distinct from `neutral-50`.
- Do not keep long-term compatibility aliases for retired grayscale slugs.
  Source and current DB content should be migrated, then old slugs removed.

## Out Of Scope

- Redesigning the brand palette.
- Renaming `brand-purple`, `deep-purple`, `red`, `accent-mint`, or
  `brand-yellow`.
- Promoting Navigation drawer geometry, hover markers, z-index values, Ecwid
  bridge variables, split-section frame math, or logo sizing into public tokens.
- Hiding old grayscale slugs as long-term compatibility aliases.
- Mutating revisions in this pass.
- Advancing `h3rs0t00`; it remains client-approval pending and out of scope.

## Execution Plan

### 1. Audit Public Token Candidates

Create a scan-first inventory of public-token candidates across:

- `theme.json`;
- `styles/shared/settings.css`;
- all authored CSS outside `styles/dist`;
- templates, parts, patterns, synced patterns, and navigation fixtures;
- current non-revision DB content for `page`, `post`, `herstory`,
  `wp_template`, `wp_template_part`, `wp_block`, `wp_navigation`, and
  `wp_global_styles`.

Classify each finding as:

- public token;
- documented private component contract;
- vendor/runtime patch;
- compatibility debt to migrate away.

### 2. Build Grayscale Migration Tooling

The completed grayscale migration used a dry-run-first script that has since
been retired with the other one-off migration tooling.

Required behavior:

- dry-run by default;
- `--apply` required for DB writes;
- export rollback data before mutation;
- list affected rows by old slug/value, new slug, post type, status, ID, and
  title;
- count revisions separately but do not mutate them;
- scan both serialized block attributes and generated classes:
  - `textColor`;
  - `backgroundColor`;
  - `has-*-color`;
  - `has-*-background-color`;
  - `var:preset|color|*`;
  - `var(--wp--preset--color--*)`;
  - `var(--wp--custom--color--*)`;
  - relevant raw hex values.

### 3. Migrate Source-Owned Color References

Update source before DB content:

- `theme.json` palette and custom color definitions;
- CSS references to `--wp--custom--color--primary`;
- CSS references to `--wp--custom--color--secondary`;
- CSS references to `--wp--custom--color--separator-border`;
- serialized template, part, pattern, synced-pattern, and navigation source
  slugs/classes;
- tests that assert old token names or colors.

Expected source map:

- `foreground` -> `neutral-0`
- `light-grey` -> `neutral-50`
- `separator-border` -> `neutral-200`
- `secondary` -> `neutral-700`
- `dark-grey` -> `neutral-800`
- `primary` -> `neutral-950`

### 4. Run DB Dry-Run And Approval Gate

Run the grayscale DB dry-run and stop before apply.

The dry-run must answer:

- Which rows use old grayscale slugs?
- Which rows use hidden `custom.color` variables?
- Which rows use core `white` or `black`?
- Which rows use raw `#f1f1f1` or related off-ladder greys?
- Are any hits revisions only?
- Are any hits in private/draft style-guide or QA fixtures that should be
  migrated with the rest of current content?

Do not remove old slugs from `theme.json` until current non-revision DB refs are
clean or explicitly accepted as retained compatibility.

### 5. Apply DB Migration After Approval

After explicit approval:

- export rollback data;
- apply current non-revision DB migration;
- rerun source and DB scans;
- require zero current non-revision refs to retired grayscale slugs before
  removing them from `theme.json`;
- do not mutate revisions.

### 6. Remove Old Public/Custom Tokens

After source and DB usage are clean:

- remove `foreground`, `dark-grey`, and `light-grey` from the public palette;
- remove `settings.custom.color.primary`;
- remove `settings.custom.color.secondary`;
- remove `settings.custom.color.separator-border`;
- add the neutral ladder to the public palette;
- update the private style guide Color Tokens section to show the neutral ladder
  separately from brand colors;
- avoid compatibility aliases unless the rollback strategy explicitly requires a
  short-lived bridge.

### 7. Normalize Remaining Public Token Candidates

After grayscale cleanup lands, work through the remaining public candidates via
the sibling plans:

- surface/color roles;
- PNS-owned gradient and duotone presets based on the current color palette;
- control primitives for buttons and forms;
- layout, rhythm, type, and residual hard-coded values.

For each batch:

- classify public/private/vendor first;
- update source and DB only where needed;
- document retained private contracts near their owning CSS file;
- avoid promoting component geometry just because it appears more than once.

## Validation

Before DB apply:

```bash
wp theme list --status=active --fields=name,status
wp option get stylesheet
wp option get template
wp post list --post_type=wp_global_styles --post_status=publish --fields=ID,post_title,post_name
```

Source validation:

```bash
php app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-block-templates.php
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
```

Visual validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:fast
```

Conditional validation:

- If shop/Ecwid references change: run `test:visual:shop` or
  `test:visual:ecwid`.
- If EmailOctopus/contact references change: run `test:visual:emailoctopus`.
- If navigation colors change: run `test:visual:navigation`.
- For final broad token landing: run the lean `test:visual` gate.

## Done When

- The public palette has a clear neutral ladder.
- Hidden `custom.color.primary`, `secondary`, and `separator-border` are gone or
  retained only with a documented short-term removal gate.
- Current non-revision DB content no longer depends on retired grayscale slugs.
- The private style guide presents grayscale as a ladder, not as unrelated
  color names.
- Public token candidates are classified before promotion.
- Vendor compatibility patches remain isolated and are not mislabeled as theme
  tokens.
- Reopened closeout tasks `hc1pvy3k` and children are complete, with each
  previous `Mostly Addressed` item either fully fixed, explicitly retained as a
  private contract, or carried as a named remaining cleanup candidate.
- Authored CSS has zero stale private brand alias refs outside compiled
  `styles/dist` output.
- The private style guide labels completed token areas as fully addressed only
  after validation evidence exists.
- Source scans, DB scans, CSS compile/lint, and focused visual gates pass.
