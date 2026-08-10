# Dark Surface Mitigation Plan

Plan started on 2026-07-05.

All paths are relative to the project root.

## Purpose

Turn the inherited purple/default page surface into an explicit, supported
dark-surface contract without hidden color magic.

The front page and brand-led campaign sections should be able to use purple or
other known dark theme colors with inverted default text, headings, links, and
buttons. Readable editorial and utility templates can remain light. Nested cards
can use the light surface inside a dark section.

This plan is adjacent to:

- `docs/jobs/__completed/2026-07-04-light-surface-theme-extraction-plan.md`
- `docs/jobs/2026-06-28-theme-json-css-structure-rationalization-plan.md`
- `docs/jobs/2026-07-03-site-identity-style-controls-mop-up-plan.md`

## Problem Statement

Before the light-surface migration, the theme largely relied on global
`theme.json` colors:

- `background` is purple.
- `foreground` is white.
- generic page text often inherited the purple-page model.

That broad default made some brand pages look right, but it also made ordinary
readable templates, Shop/Ecwid surfaces, posts, and utility pages fragile. The
light-surface migration began correcting that by making readable surfaces
explicit. The remaining risk is that the brand/purple surface still behaves like
an inherited global assumption rather than a deliberate, composable surface.

We need the old front page visual language back where it is intentional, but not
by making every page globally purple again.

## Recommendation

Use composable surfaces:

- Light surface is the normal readable content/template surface.
- Dark/brand surface is an explicit opt-in surface for known supported
  containers and known dark theme palette backgrounds.
- White/light cards nested inside dark sections are light surfaces.
- Cover/hero blocks that assume transparent artwork and white text should opt
  into the dark/brand surface contract, or use a cover-specific extension of it.

The WordPress block background color picker can be the editor-facing UI for
choosing a dark surface, but only for known supported palette colors. Do not try
to infer arbitrary custom colors as "dark" in CSS.

## In Scope

- Audit the current dark-surface trigger model in:
  - `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/components/section-theme.css`
  - `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/components/light-surface.css`
  - `app/public/wp-content/themes/protestsandsuffragettes-standalone/theme.json`
  - rendered saved page/template content.
- Preserve the pre-light-surface front page visual reference as the pass/fail
  gate for this mitigation, if Phase 0 confirms it is still usable.
- Define a dark/brand surface contract parallel to `pns-light-surface`.
- Trigger dark-surface defaults from known WordPress palette background classes,
  not arbitrary custom color values.
- Respect explicit editor text choices such as `has-text-color`, explicit color
  classes, and inline color styles.
- Remove or narrow fragile `!important` surface rules wherever the cascade can
  be made honest.
- Add tests that prove:
  - default text/headings/buttons invert on supported dark surfaces,
  - explicit user-selected text colors are not overwritten,
  - the old front-page visual reference is preserved or every diff is named and
    accepted before snapshot refresh.
- Apply explicit dark background classes or surface classes to only the blocks,
  templates, or saved content that should visually remain purple/brand-led.

## Out Of Scope

- Inferring arbitrary custom colors as light or dark.
- Reverting the light-surface model for readable templates.
- Refreshing home visual snapshots before the mitigation is classified and
  accepted.
- Broad redesign of the front page, campaign sections, cards, footer, or cover
  blocks.
- Combining this work with generic legacy utility removal.
- Treating Ecwid vendor quirks as the generic surface model.

## Guardrails

- Explicit user choices win. A block or child element with `has-text-color`,
  an explicit theme color class, or inline `style="color: ..."` should not be
  overridden by the surface system except for a named legacy/vendor bug.
- Surface CSS fills missing defaults only.
- The background color picker is allowed to imply a surface only for supported
  containers and known palette classes.
- Known dark palette triggers are currently expected to include:
  - `has-brand-purple-background-color`
  - `has-deep-purple-background-color`
  - `has-dark-grey-background-color`
- Known light palette/background choices should keep light-surface readable
  defaults.
- Remaining `!important` clauses must be treated as debt unless they are needed
  for WordPress inline styles, core block CSS, vendor/runtime CSS, or a
  documented compatibility shim.
- DB mutations require a timestamped backup, dry-run where practical, and
  post-apply verification.
- Do not refresh the local home Playwright snapshots until the front-page diff
  is accepted.

## Current Evidence

- Local Playwright home snapshots exist under:

```text
app/public/wp-content/themes/protestsandsuffragettes-standalone/tests/visual/frontend.spec.ts-snapshots/
```

- The home snapshots were last modified on 2026-07-04 around 17:13-17:14.
- The light-surface work started later on 2026-07-04 (`16b949ffb` at 21:08 and
  `bcf7c5432` at 22:05).
- The snapshot PNG files are ignored by git, so they are local evidence, not
  durable commit history. Phase 0 must confirm they are still present and usable
  before relying on them.
- The current CSS only detects specific known dark background classes on
  supported `.pns-section` wrappers. It does not detect arbitrary dark custom
  colors.

## Implementation Note - 2026-07-05

The first mitigation pass found the immediate live regression in the published
standalone `wp_global_styles` row:

```json
{
  "styles": {
    "color": { "background": "var:preset|color|light-grey" },
    "elements": {
      "link": { "color": { "text": "var:preset|color|deep-purple" } },
      "heading": { "color": { "text": "var:preset|color|dark-grey" } },
      "button": { "color": { "text": "var:preset|color|foreground" } }
    }
  },
  "isGlobalStylesUserThemeJSON": true,
  "version": 3
}
```

That row made the broad site surface light grey while ordinary page content
still expected the inherited purple/dark surface model. After a DB export, row
`5256` was reset to:

```json
{ "version": 3, "isGlobalStylesUserThemeJSON": true }
```

Backup:

```text
docs/jobs/dark-surface-db-backups/20260705-before-dark-surface-global-styles-reset.sql
```

CSS changes removed the dark-section `!important` rules that overrode explicit
text colors. The current dark-surface trigger still uses only known palette
background classes on supported PNS section wrappers; it does not infer
arbitrary custom colors.

Validation passed:

- CSS compile.
- CSS lint.
- Targeted Prettier check for `section-theme.css` and `frontend.spec.ts`.
- WP-CLI verification of row `5256`.
- Focused surface/Ecwid Playwright coverage: 36/36.
- Old home visual snapshot gate: 3/3 across desktop, tablet, and mobile.

Remaining follow-up:

- Phase 4 is not fully closed by this pass. The immediate regression was fixed
  by removing the user-level Global Styles override, but any future content
  migration should still make intentionally purple/brand sections explicit at
  the block/template level where practical.

## Ownership Model

| Surface or control                    | Preferred owner                                  | Notes                                                             |
| ------------------------------------- | ------------------------------------------------ | ----------------------------------------------------------------- |
| Light readable surface                | `pns-light-surface` contract                     | Normal content/template readability.                              |
| Dark/brand surface                    | New explicit dark-surface contract               | Parallel to light surface; known dark palette triggers only.      |
| Background color picker               | WordPress editor UI plus theme CSS contract      | Valid trigger for supported containers and known palette classes. |
| Explicit text color                   | Editor/user choice                               | Must win over surface defaults.                                   |
| Cover/hero transparent artwork blocks | Dark-surface or cover-specific surface extension | Do not depend on global page purple.                              |
| Nested white/light cards              | Light surface inside dark parent                 | Card text should be dark by default.                              |
| PNS sections                          | Surface contract plus section compatibility CSS  | First implementation target.                                      |
| Vendor/runtime blocks                 | Vendor adapter consuming surface variables       | Keep Ecwid/Jetpack quirks isolated.                               |
| `!important` clauses                  | Temporary compatibility only                     | Remove or narrow unless a named exception applies.                |

## Dex Tracking

Dex task state is stored under the standalone theme root:

```text
app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex/tasks.jsonl
```

Use the tracker with the storage path explicitly:

```bash
dex --storage-path /Users/anachronistic/Local\ Sites/pns-stageing/app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex list --all
```

Parent task:

```text
c3ebi01g - Mitigate dark surface behavior without hidden color magic
```

Phase tasks:

| Phase | Dex ID     | Task                                                     |
| ----- | ---------- | -------------------------------------------------------- |
| 0     | `81i1dg5n` | Freeze old visual reference and audit current triggers   |
| 1     | `zn5tn4j8` | Define explicit supported surface contract               |
| 2     | `n9wnk9x2` | Remove fragile important surface overrides               |
| 3     | `asf8ceom` | Implement known dark palette inversion                   |
| 4     | `anc67n6s` | Align blocks and templates to explicit surfaces          |
| 5     | `c8v6j1yt` | Gate against old home snapshots and explicit-color tests |
| 6     | `k6wz741x` | Document editor contract and closeout                    |

Related Dex tasks:

- `ff1v95jm` - light surface theme extraction.
- `511irrlz` - larger standalone theme block controls and CSS authority work.
- `d72fw0le` - Site Identity and Site Editor style-controls mop-up.
- `to9qfw83` - legacy utility class removal before designer handback.

## Phase 0 - Freeze Old Visual Reference And Audit Current Triggers

Goal: prove the mitigation gate before any behavior changes.

Tasks:

- Confirm local home snapshots still exist and have not been refreshed after the
  light-surface migration.
- Record the exact snapshot paths and timestamps.
- Run the current home route against the existing snapshots without updating
  them.
- Audit current dark-surface triggers in CSS and `theme.json`.
- Audit rendered front page blocks to identify which sections/cards/cover blocks
  should be dark, light, or nested light-inside-dark.
- Audit current `!important` rules in surface/button/section CSS and classify
  each as remove, narrow, keep with reason, or defer.

Acceptance:

- No source behavior changes in this phase.
- The old home snapshots are confirmed usable, or a named blocker explains why
  they cannot be the gate.
- The plan records current dark triggers and target surfaces.

## Phase 1 - Define Explicit Supported Surface Contract

Goal: define the contract before implementation.

Tasks:

- Name the dark/brand surface contract.
- Define variables for:
  - background,
  - text,
  - heading,
  - muted text,
  - link,
  - link hover,
  - separator,
  - button background,
  - button text,
  - button shadow/accent.
- Define supported trigger classes.
- Define supported wrappers/containers.
- Define nested surface behavior.
- Define the explicit-color escape hatch.

Acceptance:

- The contract does not infer arbitrary custom colors.
- The contract states that explicit editor text choices win.
- The implementation target list is small enough for a regression-gated batch.

## Phase 2 - Remove Fragile Important Surface Overrides

Goal: reduce cascade force before adding new behavior.

Tasks:

- Review `section-theme.css`, `buttons.css`, and related surface rules.
- Remove or narrow `!important` rules that override explicit text color.
- Keep `!important` only where required by:
  - WordPress inline block-support styles,
  - core block CSS that cannot be beaten safely by order/specificity,
  - third-party runtime CSS,
  - documented legacy compatibility.
- Add computed-style tests for explicit color winning on dark sections.

Acceptance:

- User-selected text colors win in supported dark surfaces.
- Every remaining `!important` in the touched surface rules has a named reason.
- Compile/lint pass before Phase 3 starts.

## Phase 3 - Implement Known Dark Palette Inversion

Goal: make supported dark backgrounds produce correct default readable output.

Tasks:

- Implement dark-surface defaults for known dark palette background classes.
- Wire text, headings, links, separators, buttons, and nested cards without
  overriding explicit colors.
- Keep existing PNS visuals aligned where blocks already use dark backgrounds.
- Avoid global page-level purple assumptions.

Acceptance:

- Known dark palette backgrounds invert defaults on supported containers.
- Custom arbitrary colors do not silently trigger dark mode.
- Explicit color tests pass.

## Phase 4 - Align Blocks And Templates To Explicit Surfaces

Goal: move intended purple visuals from global inheritance to explicit block or
template ownership.

Tasks:

- Update only the blocks/templates that should visually remain purple or
  brand-led.
- Prefer editor-visible background color classes or block styles over hidden
  template-only behavior.
- Use DB backups before saved-content mutations.
- Keep readable templates light where that is the intended editorial surface.

Acceptance:

- Front page dark areas are explicit.
- White/light cards inside dark areas remain readable.
- No broad template change makes ordinary pages purple by accident.

Result, 2026-07-06:

- No additional source-template or saved-content mutation was required for this
  phase. The current explicit ownership model is already present:
  - dark section defaults are limited to supported PNS section wrappers carrying
    known dark palette classes: `has-brand-purple-background-color`,
    `has-deep-purple-background-color`, or `has-dark-grey-background-color`;
  - footer and `PNS - Connect Social` carry explicit `brand-purple` /
    `foreground` ownership;
  - home/news and Herstories archive cover heroes carry explicit foreground copy
    over cover overlays;
  - readable templates and store/search surfaces carry `pns-light-surface`,
    `white`, `light-grey`, or explicit dark-grey text ownership;
  - shop/cart Ecwid surfaces remain light and scoped to the light-surface/store
    contract.
- The retired `has-background-background-color` trigger was removed from this
  plan after the color-token cleanup migrated `background` to `brand-purple`.
- Validation passed:
  - CSS compile.
  - CSS lint.
  - Block-template validation for 16 files.
  - Focused Playwright dark/light gate: `51/51` passed across desktop, tablet,
    and mobile for home snapshots, generic light-surface templates, light
    surface leak checks, shop/cart light surfaces, light-surface button
    readability, PNS dark section inversion, homepage cascade, and Herstory
    cascade.

## Phase 5 - Gate Against Old Home Snapshots And Explicit-Color Tests

Goal: prove the mitigation worked without refreshing away regressions.

Required checks:

- CSS compile.
- CSS lint.
- Block-template validation for touched templates.
- Focused computed-style tests for:
  - dark-surface defaults,
  - explicit text colors winning,
  - nested light cards,
  - buttons on dark and light surfaces.
- Focused browser checks for front page and any touched saved-content routes.
- Playwright visual run using existing home snapshots as the pass/fail gate.

Acceptance:

- Front page matches the accepted old local reference, or every difference is
  named and accepted before any snapshot refresh.
- No white-on-white or dark-on-dark text is introduced on tested surfaces.
- Any remaining blocker has a Dex owner.

## Phase 6 - Document Editor Contract And Closeout

Goal: make the behavior explainable before designer handback.

Documentation must state:

- The background color picker can select known light/dark palette surfaces on
  supported containers.
- Known dark palette backgrounds trigger dark-surface defaults.
- Custom arbitrary colors are not auto-classified.
- Explicit text colors win.
- Remaining unsupported controls or `!important` exceptions are named.

Acceptance:

- Plan results and Dex results contain implementation evidence.
- Designer-facing guidance is documented.
- Any deferred `!important` or unsupported-control debt has a follow-up owner.

Editor-facing contract, 2026-07-06:

- Use the block background color picker for explicit surface ownership on
  supported PNS section containers. The supported dark palette background
  classes are `brand-purple`, `deep-purple`, and `dark-grey`.
- Known dark palette backgrounds trigger default readable text, heading, link,
  and button colors only on supported PNS section wrappers. This is an explicit
  palette-class contract, not hidden color-math.
- Known light surfaces, generic templates, store/search surfaces, and long-form
  editorial templates should use `pns-light-surface`, `white`, `light-grey`, or
  explicit dark text ownership.
- Arbitrary custom colors are not auto-classified as dark or light. If an
  editor chooses a custom dark color, they must also choose readable text/link
  colors or use a supported palette color.
- Explicit text colors win. Blocks or child elements with `has-text-color`,
  explicit theme text-color classes, or inline text color styles are not
  overridden by the surface system.
- Cover/hero blocks remain explicit media-overlay compositions: their readable
  copy is controlled through foreground text choices on the cover content, not
  through a broad page-level dark mode.
- Nested light cards inside dark sections keep their own readable surface. Do
  not use parent dark-surface rules to recolor card internals that already have
  explicit light/background ownership.
- Remaining `!important` clauses are not part of the dark-surface contract.
  They are retained only where separately justified for WordPress inline styles,
  core block priority, vendor/runtime output, or named compatibility debt.

Closeout evidence:

- `anc67n6s` confirmed no additional block/template mutation was required after
  the current explicit surface ownership audit.
- Validation passed after the final alignment check:
  - CSS compile.
  - CSS lint.
  - Block-template validation for 16 files.
  - Focused dark/light Playwright gate: `51/51`.
  - Full Playwright visual suite from the parent closeout: `235 passed`,
    `2 skipped`.

## Test Plan

- Do not refresh local home snapshots before Phase 5 acceptance.
- Use the existing local home desktop/tablet/mobile snapshots as the mitigation
  gate if Phase 0 confirms they are usable.
- Run focused computed-style tests before broad visual checks.
- Run full visual coverage only after the front-page dark-surface contract is
  stable.
- DB-backed changes require timestamped backup plus verification.

## Open Questions

- Resolved for this closeout: do not add a broad `pns-dark-surface` or
  `pns-brand-surface` abstraction yet. The supported public contract is the
  explicit WordPress palette background class on supported PNS section wrappers.
- Resolved for this closeout: Cover blocks keep their explicit media-overlay
  model with foreground text, rather than joining the generic section surface
  rules.
- Resolved for this closeout: no saved front-page mutation was needed. Current
  front-page dark and light areas already use explicit block/template ownership
  and passed the existing home snapshots.
