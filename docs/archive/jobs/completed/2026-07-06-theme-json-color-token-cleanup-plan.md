# Theme JSON Color Token Cleanup Plan

Plan started on 2026-07-06.

All paths are relative to the project root.

## Purpose

Canonicalize standalone theme color ownership across `theme.json`, private CSS
aliases, filesystem block markup, saved DB content, and visual tests.

The end state is one public palette slug per author-facing color, no duplicate
`settings.custom.color` mirrors, and private `--pns-*` aliases only where they
express a real component or surface role.

This is a radical cleanup plan. A temporary migration bridge is allowed only
when direct removal would break live serialized content or make rollback
impractical.

## Parent Work

Parent plan:
`docs/jobs/2026-06-28-theme-json-css-structure-rationalization-plan.md`

Dex: `sodx2jk4` - Palette token canonicalization and DB migration.

## Problem Statement

The standalone theme currently has competing color owners:

- `background` and `brand-purple` are both purple surface concepts.
- `tertiary` and `accent-mint` are both mint accent concepts.
- `brand-purple`, `deep-purple`, and `accent-mint` are mirrored between the
  public palette and `settings.custom.color`.
- `foreground` is white, while source markup and CSS may also rely on a core or
  default `white` preset.
- Button, separator, navigation, section, and light-surface colors are split
  between `theme.json`, authored CSS, private aliases, and serialized block
  classes.

The cleanup needs one source-of-truth rule before values are removed:

- `settings.color.palette` is the author-facing color picker surface.
- `settings.custom.color` is only for semantic values that should generate
  `--wp--custom--color-*` variables and are not palette mirrors.
- Private `--pns-*` variables are component and pattern aliases, not an
  independent design-token source.
- Saved templates, patterns, pages, and posts should use canonical palette
  slugs or approved custom variables, not retired slugs or raw hex values.

## In Scope

- Audit and migrate color references in:
  - `app/public/wp-content/themes/protestsandsuffragettes-standalone/theme.json`
  - `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/`
  - `app/public/wp-content/themes/protestsandsuffragettes-standalone/templates/`
  - `app/public/wp-content/themes/protestsandsuffragettes-standalone/parts/`
  - `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/`
  - synced-pattern source files, if present.
- Reconfirm saved DB content before implementation.
- Migrate live/current DB records only when source cleanup would otherwise
  strand serialized retired slugs.
- Update visual/computed-style tests that assert old color values.
- Remove compatibility aliases before closeout unless the user explicitly
  accepts them as long-term debt.

## Out Of Scope

- Broad redesign of the PNS palette.
- Inferring arbitrary custom colors as light or dark.
- Reworking the dark/light surface model beyond making it consume canonical
  tokens.
- Retiring unrelated utility classes.
- Editing parent themes or third-party plugins.
- Mutating revisions unless restore/history normalization is separately
  accepted.

## Guardrails

- Reconfirm the active theme and active `wp_global_styles` row immediately
  before implementation.
- Back up DB rows before any mutation.
- Dry-run DB replacements where practical, then verify post-apply.
- Migrate source-owned templates, patterns, CSS, and tests before removing any
  palette slug from `theme.json`.
- Do not retain a misleading public slug only to support body background or a
  component implementation detail.
- Explicit editor choices win. Surface CSS should fill defaults, not override
  deliberate block-level text/background selections.

## Current Evidence

The current 2026-07-06 audit found:

- Active Local theme is `protestsandsuffragettes-standalone`.
- `brand-color` is not an active standalone palette slug and has no scoped
  filesystem or DB content hits.
- `background` is `#3D207E`; `brand-purple` is `#392279`.
- `tertiary` is `#7FBFA5`; `accent-mint` is `#7bdcb5`.
- `brand-purple`, `deep-purple`, and `accent-mint` are duplicated between the
  public palette and `settings.custom.color`.
- Filesystem code uses `background` as a palette slug for surfaces, while
  `brand-purple` and `accent-mint` are mostly consumed through private
  `--pns-*` aliases.
- `tertiary` is still serialized in source-owned stats pattern markup and is
  consumed by a navigation CSS alias.
- Active standalone `wp_global_styles` row `5256` was minimal at audit time:
  `{"version":3,"isGlobalStylesUserThemeJSON":true}`.
- `background` appeared in live/current `wp_template`, `wp_template_part`,
  reusable blocks, published pages, published Herstories, and draft/private
  working pages.
- `tertiary` appeared in published Herstories, the Pattern QA page, and draft
  duplicate pages.
- `brand-purple`, `accent-mint`, and `brand-color` had no scoped DB
  `post_content` hits from the audit.

These findings must be refreshed before mutation because templates and saved
content are DB-backed.

## Canonical Decisions To Make

Record these decisions before editing source:

| Decision | Expected direction |
| --- | --- |
| Main purple | Prefer `brand-purple`; migrate/remove `background` if fresh scans confirm safety. |
| Mint accent | Prefer `accent-mint`; migrate/remove `tertiary`. |
| White/light text | Decide whether `foreground` or `white` is the author-facing slug; avoid duplicate names for the same author intent. |
| Body background | Use the canonical brand surface token directly; do not keep a misleading generic `background` slug just for body color. |
| Separator color | Either keep a real semantic custom token or point separator CSS to a canonical palette/private alias. |
| Buttons | `theme.json` exposes honest defaults; component CSS owns the PNS button treatment and states. |
| Navigation | `theme.json` owns basic nav text defaults; component CSS owns drawer, submenu, hover, marker, and responsive behavior. |
| Sections/surfaces | Section and light-surface CSS can own derived roles, but all roles must point to canonical tokens. |

## Execution Phases

1. Reconfirm live state.
   - Confirm active theme is `protestsandsuffragettes-standalone`.
   - Reconfirm active standalone `wp_global_styles`.
   - Scan filesystem and scoped DB rows for `background`, `tertiary`,
     `brand-purple`, `accent-mint`, `foreground`, and `white`.
   - Export DB rollback files before mutation.

2. Lock the canonical map.
   - Create a slug migration table with old slug, new slug, token type,
     source hits, DB hits, and removal gate.
   - Decide `foreground` versus `white`.
   - Decide whether any existing custom color such as `primary`, `secondary`,
     or separator-related values remains a real semantic custom token.

3. Migrate source-owned color references.
   - Update `theme.json` palette/custom color blocks.
   - Update private aliases in `styles/shared/settings.css`.
   - Update templates, parts, patterns, synced-pattern source, and tests.
   - Update button, separator, navigation, section, light-surface, and vendor
     override references.

4. Migrate DB-owned serialized content only if required.
   - Dry-run replacements for current `wp_template`, `wp_template_part`,
     `wp_block`, and `wp_navigation` records.
   - Apply sitewide/template/reusable records before ordinary content.
   - Migrate published pages and Herstories after sitewide records.
   - Migrate drafts only if they remain useful fixtures or editorial sources.

5. Remove compatibility bridges.
   - Remove retired palette slugs from `theme.json`.
   - Remove compatibility CSS for retired classes such as
     `has-background-background-color` or `has-tertiary-color` after live scans
     are clean.
   - Remove private aliases that only existed to bridge old slugs.

6. Validate.
   - Run `pnpm compile:css`.
   - Run the relevant CSS lint or focused CSS gate.
   - Run source scans proving removed slugs are gone.
   - Run WP-CLI DB scans proving removed slugs have no live/current hits.
   - Run focused visual/computed checks for home, footer, light surfaces,
     Herstories stats, navigation, buttons, and separators.
   - Run the full visual suite before closing `sodx2jk4`.

## Done When

- `theme.json` has no palette/custom color mirrors without semantic
  justification.
- `background` and `tertiary` are fully migrated away, or retained with an
  explicit documented reason and removal gate.
- Body background, section surfaces, buttons, separators, and navigation each
  have one documented color owner.
- No live filesystem or DB content references removed slugs.
- Rollback exports exist for every DB mutation.
- Compile, CSS, DB, and visual gates pass.

## 2026-07-06 Implementation Evidence

Implemented `sodx2jk4` as a hard cut with DB rewrites rather than compatibility
bridges.

- Confirmed active theme: `protestsandsuffragettes-standalone`.
- Exported DB backup before mutation:
  `docs/jobs/color-token-db-backups/20260706-before-palette-token-canonicalization.sql`.
- Canonical map:
  - `background` -> `brand-purple`
  - `tertiary` -> `accent-mint`
- Removed the public `background` and `tertiary` palette slugs from
  `theme.json`.
- Kept canonical `brand-purple` at the accepted rendered purple `#3D207E` so
  button and surface visuals did not drift.
- Removed palette mirrors for `brand-purple`, `accent-mint`, and `deep-purple`
  from `settings.custom.color`.
- Pointed private CSS aliases at public palette tokens:
  `--pns--color--brand-purple`, `--pns--color--accent-mint`, and
  `--pns--color--deep-purple`.
- Removed retired `.has-background-background-color` dark-surface compatibility
  selectors from section CSS.
- Migrated source-owned footer, synced-pattern, stats pattern, light-surface,
  Ecwid, navigation, and visual-test references.
- Added `scripts/migrate-color-token-slugs.php`.
- Dry-run DB migration found 20 current rows.
- Applied DB migration to 20 current rows across `herstory`, `page`,
  `wp_block`, and `wp_template_part`.
- Verified DB scan returned no current rows using retired `background` or
  `tertiary` tokens/classes.
- Verified authored source scan returned no retired token/class references
  outside the migration script and generated dist outputs.

Validation:

- `php -l scripts/migrate-color-token-slugs.php` passed.
- `php scripts/validate-block-templates.php` passed for 16 files.
- `pnpm compile:css` passed.
- `pnpm lint:css` passed.
- Targeted Playwright surface/Ecwid gate passed: 18 passed.
- Targeted typography rhythm rerun passed: 9 passed.
- Full Playwright visual suite passed after rerun: 235 passed, 2 expected
  skips.

Notes:

- The first full visual run exposed a brittle typography-rhythm selector on
  `/shenanigans/`; the test now excludes `.pns-hero-copy` so it measures
  editorial rhythm instead of the hero intro, which intentionally has no bottom
  paragraph margin.
- The first full visual run also briefly showed a `pns-forms` early translation
  notice in desktop snapshots while the active form plugin worktree was in
  flux. That notice was not caused by the palette migration and was gone on
  rerun.
