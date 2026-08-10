# Theme JSON Rhythm And Type Scale Cleanup Plan

Plan started on 2026-07-06.

All paths are relative to the project root.

## Purpose

Canonicalize spacing, typography, and line-height ownership across `theme.json`,
authored CSS, filesystem templates and patterns, synced patterns, tests, and
saved DB content.

The end state is a coherent rhythm system: one approved author-facing spacing
scale, one approved type scale, explicit line-height roles, and private CSS
aliases only where they protect real component mechanics.

This is a radical cleanup plan. Compatibility aliases or migration bridges are
allowed only when direct removal would strand live serialized content or make
rollback impractical.

## Parent Work

Parent plan:
`docs/jobs/2026-06-28-theme-json-css-structure-rationalization-plan.md`

Dex: `uzku688y` - Spacing typography and rhythm scale canonicalization.

## Problem Statement

The standalone theme has a disjointed soup of values:

- Global body type is `0.875rem / 1.6`, while paragraph styling uses
  `1rem / 1.6`.
- Public font presets are sparse and display-oriented, while raw heading,
  caption, navigation, social, button, and body sizes live elsewhere.
- Navigation typography is defined in block settings, block styles, custom
  typography values, and private CSS aliases.
- The spacing ladder lives under `settings.custom.spacing`, not as a normal
  author-facing spacing preset scale.
- Global zero margins and scoped content rhythm compete for ownership.
- Buttons, separators, spacers, and query pagination use a mix of raw
  `theme.json` values and component CSS.

The cleanup needs one source-of-truth rule before values are removed:

- `settings.spacing.spacingSizes` should expose author-facing spacing choices.
- `settings.typography.fontSizes` should expose author-facing type choices.
- `settings.custom.spacing` and `settings.custom.typography` are only for
  semantic generated `--wp--custom-*` values that are not public preset mirrors.
- Private `--pns-*` variables are component and pattern aliases, not an
  independent rhythm/type source.
- Serialized templates, patterns, pages, and posts should use approved presets
  or documented component exceptions, not stale one-off values.

## In Scope

- Audit and classify spacing, font-size, and line-height values in:
  - `app/public/wp-content/themes/protestsandsuffragettes-standalone/theme.json`
  - `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/`
  - `app/public/wp-content/themes/protestsandsuffragettes-standalone/templates/`
  - `app/public/wp-content/themes/protestsandsuffragettes-standalone/parts/`
  - `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/`
  - synced-pattern source files, if present.
- Define author-facing spacing and type scales.
- Define line-height roles.
- Decide which one-off values are real component/vendor geometry.
- Reconfirm saved DB content before implementation.
- Migrate DB-backed serialized values only when required.
- Update tests that assert old raw values.

## Out Of Scope

- Broad redesign of page layouts.
- Removing legacy utility classes for their own sake.
- Normalizing third-party plugin geometry without checking runtime/plugin
  constraints.
- Editing parent themes or third-party plugins.
- Mutating revisions unless restore/history normalization is separately
  accepted.

## Guardrails

- Reconfirm the active theme and active `wp_global_styles` row immediately
  before implementation.
- Back up DB rows before any mutation.
- Dry-run DB replacements where practical, then verify post-apply.
- Migrate source-owned templates, patterns, CSS, and tests before changing or
  removing public preset names.
- Classify fixed values before changing them. Navigation marker sizes, form
  widths, avatar sizes, media thresholds, and vendor output may be real
  geometry rather than rhythm debt.
- Do not refresh visual baselines until each visible rhythm/type change is
  named and accepted.

## Current Evidence

The current 2026-07-06 audit found:

- `theme.json` exposes a partial spacing ladder through custom values:
  `0.25rem`, `0.5rem`, `0.625rem`, `1rem`, `1.25rem`, `1.5rem`, and `2rem`.
- Global `styles.spacing.blockGap` maps to
  `var(--wp--custom--spacing--compact)`.
- Public font presets are `medium`, `large`, and `x-large`.
- Raw element/block font sizes include body `0.875rem`, caption `13px`,
  `h1` `3.6875rem`, `h4` `1.6875rem`, `h5` `1.3125rem`, social links
  `1.375rem`, navigation `1rem`, and several custom/navigation values around
  `1.125rem`.
- Line-height roles are implicit rather than named: paragraph `1.6`, heading
  `1.2`, heading compact `1.17777778`, display tight `1.03575`, compact
  paragraph `1.26666667`, quote paragraph `1.1`, and product-grid fallback
  values such as `0.8`.
- Source markup and CSS still carry one-off spacing values such as `4rem`,
  `6rem`, `2.5rem`, `1.875em`, `1.875rem`, `2.4375rem`, `64px`, `56px`,
  `20px`, `26px`, navigation drawer values like `25px` / `28px`, and embed
  widths such as `32.5rem`.
- Tests currently assert raw values including `20px`, `13px`, `26px`,
  `45px` / `56px`, and `320px`.

These findings must be refreshed before mutation because templates and saved
content are DB-backed.

## Competing Rules To Resolve

| Area | Conflict | Target |
| --- | --- | --- |
| Body vs paragraph | Body is `0.875rem`; paragraphs are `1rem`. | Decide whether body text is ordinary copy or a smaller fallback/UI default. |
| Font presets vs raw sizes | Public presets do not cover real element sizes. | Promote author-facing values; keep private component defaults out of the picker. |
| Navigation type | Block settings, block styles, custom typography, and CSS aliases all define it. | Keep one author-facing navigation scale; CSS owns mechanics. |
| Custom spacing | Rhythm ladder lives in `settings.custom.spacing`. | Move author-facing rhythm to real spacing presets; keep custom only for semantic generated values. |
| Global margins | `theme.json` and base CSS zero margins, then components recreate rhythm. | Decide the default vertical rhythm owner and document zero-gap exceptions. |
| Buttons | `theme.json` defaults and component CSS both style the button surface. | Keep honest defaults in `theme.json`; component CSS owns PNS treatment and states. |
| Separators | `core/separator` margin uses raw `20px`; CSS also paints separator behavior. | Move rhythm to a token or document a fixed exception. |
| Spacers | Saved spacer heights may encode page-specific layout. | Migrate ordinary rhythm to tokens/block gaps; retain intentional visual gaps. |
| Query pagination | `theme.json` sets raw `4em`; CSS hook adds full component treatment. | Keep generic core default minimal; explicit PNS hook owns component layout. |

## Execution Phases

1. Inventory source values.
   - Inventory all spacing, font-size, and line-height values in `theme.json`,
     styles, templates, parts, patterns, and synced-pattern sources.
   - Classify each value as public preset, element default, component token,
     serialized block value, vendor/runtime exception, or deletion candidate.

2. Inventory DB values.
   - Reconfirm active theme and active standalone `wp_global_styles`.
   - Scan current `wp_template`, `wp_template_part`, `wp_block`,
     `wp_navigation`, published pages, posts, and Herstories for serialized
     spacing, font-size, and line-height values.

3. Decide canonical scales.
   - Define public spacing presets for ordinary rhythm, section padding,
     template padding, and block gaps.
   - Define public type presets for caption/small, body, navigation, medium
     copy, heading, and display.
   - Define line-height roles for body copy, compact copy, heading, compact
     heading, display, quote, and component exceptions.

4. Migrate source-owned values.
   - Update `theme.json`, `styles/shared/settings.css`, base typography,
     block CSS, component CSS, templates, parts, patterns, synced-pattern
     sources, and tests.
   - Do not remove old preset/custom names until source scans are clean.

5. Migrate DB only when required.
   - If retired values remain serialized in live DB content, create
     timestamped rollback exports.
   - Dry-run, apply, and run post-apply verification.
   - Migrate sitewide/template/reusable records before pages and Herstories.

6. Validate.
   - Run `pnpm compile:css`.
   - Run the relevant CSS lint or focused CSS gate.
   - Run block-template validation.
   - Run focused visual/computed checks for paragraphs, headings, navigation,
     buttons, separators, spacers, and query pagination.
   - Run the full visual suite before closing `uzku688y`.

## Done When

- `theme.json` exposes only approved public spacing, type, and line-height
  choices.
- `settings.custom.spacing` and `settings.custom.typography` contain no
  unexplained mirrors of public presets.
- Body and paragraph type defaults have a single documented contract.
- Navigation typography has one clear author-facing surface plus private CSS
  only for component mechanics.
- Raw spacing/type values are migrated to tokens or documented as fixed
  component/vendor geometry.
- Any DB mutation has backup, dry-run/apply evidence, post-apply verification,
  and zero stale live hits for retired values.
- Compile, CSS, DB, and visual gates pass.

## Implementation Evidence

Completed on 2026-07-06 under Dex task `uzku688y`.

### Canonical Scale Changes

- Added an explicit public spacing scale in `theme.json` and disabled default
  core spacing sizes.
- Promoted the previous author-facing custom spacing ladder into public
  spacing presets:
  - `2-x-small`: `0.25rem`
  - `extra-small`: `0.5rem`
  - `compact`: `0.625rem`
  - `small`: `1rem`
  - `medium-small`: `1.25rem`
  - `medium`: `1.5rem`
  - `large`: `2rem`
- Kept legacy numeric spacing presets `20` through `80` so existing serialized
  `var:preset|spacing|*` content remains valid while the custom spacing alias
  layer is removed.
- Added public font-size presets for caption, small/UI/body copy, body-large,
  heading, display, site-title, and social-icon use cases.
- Replaced custom typography font-size mirrors with public font-size presets.
- Added line-height roles under `settings.custom.typography.line-height` for
  paragraph, compact paragraph, heading, compact heading, display-tight, and
  quote rhythm.
- Removed `settings.custom.spacing` from `theme.json`.

### Source And DB Migration

- Rewired standalone theme CSS, templates, parts, synced patterns, and tests
  from retired `--wp--custom--spacing-*` and custom font-size mirror values to
  public spacing/font-size presets or private component aliases.
- Added
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/migrate-rhythm-scale-tokens.php`
  for dry-run/apply DB migration with rollback exports.
- Exported rollback files under
  `docs/jobs/rhythm-scale-db-backups/`.
- Applied the DB migration to the active rendered owners that still contained
  retired custom spacing tokens:
  - `1029` Home
  - `5936` PNS - Header
  - `6138` PNS - Herstory Archive
  - `6186` Blog Home
  - `6221` Search Results
- Applied a follow-up escaped-token pass for Home where serialized content used
  escaped CSS custom-property names.
- Verified remaining DB hits for `wp--custom--spacing` are revision history
  only: `revision / inherit = 136` rows. Active rendered content has no stale
  custom spacing token rows.

### Ecwid Product Grid Follow-Up

The first focused home snapshot exposed an 8px page-height drift from the
project-owned `pns-blocks` Ecwid product-grid block. Its generated frontend CSS
still referenced `--wp--custom--spacing--2-x-small` with a `0.5rem` fallback,
while the retired custom token had previously resolved to `0.25rem`.

Fix:

- Updated
  `app/public/wp-content/plugins/pns-blocks/blocks/commerce/ecwid-product-grid/style.css`
  and `editor.css` to use public spacing presets:
  - `var(--wp--preset--spacing--2-x-small, 0.25rem)`
  - `var(--wp--preset--spacing--compact, 0.625rem)`
- Regenerated tracked build outputs under
  `app/public/wp-content/plugins/pns-blocks/build/blocks/commerce/ecwid-product-grid/`.

### Validation

Passed:

- `php -l app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/migrate-rhythm-scale-tokens.php`
- JSON parse for
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/theme.json`
- `pnpm check` in
  `app/public/wp-content/themes/protestsandsuffragettes-standalone`
- Block-template validation for standalone `templates/*.html`,
  `parts/*.html`, and `synced-patterns/*.html`
- `pnpm --dir app/public/wp-content/plugins/pns-blocks run build`
- `pnpm --dir app/public/wp-content/plugins/pns-blocks run lint:css`
- `pnpm exec prettier --check blocks/commerce/ecwid-product-grid/style.css blocks/commerce/ecwid-product-grid/editor.css`
  in `app/public/wp-content/plugins/pns-blocks`
- Targeted Playwright home snapshot:
  `pnpm exec playwright test tests/visual/frontend.spec.ts --project=desktop --grep "visual snapshot: home"`
- Focused Playwright rhythm/layout/search gate:
  `pnpm exec playwright test tests/visual --grep "content typography rhythm|navigation controls expose|homepage cascade contracts|native search|header logo|layout width contract"`
  (`30 passed`)
- Full visual suite:
  `pnpm test:visual` (`235 passed`, `2 skipped`)

Known unrelated validation noise:

- `pnpm --dir app/public/wp-content/plugins/pns-blocks run check` still fails
  on pre-existing Prettier issues in
  `blocks/media/video-banner/editor.css` and
  `blocks/media/video-banner/style.css`. The touched Ecwid product-grid CSS
  files pass targeted Prettier, CSS lint, and build checks.
