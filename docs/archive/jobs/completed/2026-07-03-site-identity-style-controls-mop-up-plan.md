# Site Identity And Style Controls Mop-Up Plan

Plan started on 2026-07-03.

All paths are relative to the project root.

## Purpose

Make the active `protestsandsuffragettes-standalone` theme honest about which
Site Identity and Site Editor style controls it supports.

This is a no-visual-change mop-up plan. The goal is not to redesign the site or
make every WordPress control powerful. The goal is to preserve current rendered
defaults while removing confusing control surfaces, wiring small missing
defaults where practical, and documenting surfaces that are intentionally owned
by theme CSS.

## Survey Baseline

The 2026-07-03 control survey found:

- Active Local theme options are
  `stylesheet=protestsandsuffragettes-standalone` and
  `template=protestsandsuffragettes-standalone`.
- The active standalone `wp_global_styles` row is `5256`; its content remains
  minimal: `{"version": 3, "isGlobalStylesUserThemeJSON": true }`.
- Site Title and Tagline are not rendered by filesystem templates, parts, or
  patterns. They still feed core document title, metadata, and logo alt text.
- Site Logo is rendered in the header and footer via `core/site-logo`.
- `site_logo` and `site_icon` both currently point to attachment `1052`.
- The standalone theme seeds a default `site_logo` on activation if no valid
  Site Logo exists.
- `theme.json` styles `core/site-title` and `core/site-tagline`, but no source
  template places those blocks.
- Theme palette presets and custom color variables are actively used by CSS,
  section wrappers, template parts, and patterns.
- Core default palette and gradient presets are still exposed alongside the PNS
  palette; `black` and `white` are used in templates/CSS but are not declared in
  the theme palette.
- Button color controls partly work, but button shape, spacing, shadow, and
  hover affordances are CSS-owned.
- Caption styling is not meaningfully wired.
- Separator, navigation, and broad spacing controls are mostly presentation
  hints because authored CSS owns those surfaces.
- `custom.button_border-radius` and `custom.avatar_border-radius` are declared
  in `theme.json` but not consumed by source CSS.

## Non-Negotiable Guardrails

- No apparent visual change is allowed unless a later task explicitly records
  and accepts it.
- Do not remove a working WordPress or theme feature just to simplify the
  surface. First audit what the UI or `theme.json` claims, prove whether that
  control is wired in runtime/editor output, then choose one outcome: keep it,
  wire it, restrict/document its supported scope, or remove it only when it is
  genuinely dead or misleading.
- Do not remove Site Identity wholesale. Site Title, Tagline, Site Logo, and
  Site Icon remain core site metadata surfaces even when title/tagline are not
  visibly rendered.
- Do not add visible Site Title or Tagline blocks to header/footer unless a
  separate design decision asks for that.
- Do not hide or remove a Site Editor control until its runtime effect is proven
  misleading or unsupported.
- Treat `wp_global_styles` as a live override layer. Recheck it before changing
  `theme.json` defaults.
- Preserve the existing PNS section/color model. If a block control is meant to
  be ignored inside a fixed PNS component, document that scope rather than
  making the global control dishonest.
- Compile and visually verify after any `theme.json` or CSS behavior change.
- Any DB mutation requires a timestamped backup under
  `docs/jobs/live-adoption-db-backups/` and a rollback note.

## Control Ownership Model

| Surface                                 | Preferred owner                                                               | Expected action                                                   |
| --------------------------------------- | ----------------------------------------------------------------------------- | ----------------------------------------------------------------- |
| Site Logo and Site Icon values          | WordPress site options plus theme activation seeding                          | Keep controls and verify seeded defaults.                         |
| Visible header/footer brand mark        | `core/site-logo` blocks in template parts plus scoped logo CSS                | Keep; no visible change.                                          |
| Visible Site Title and Tagline text     | Not currently part of the design                                              | Keep metadata controls, do not render text by default.            |
| Brand palette                           | `theme.json` presets and PNS CSS variables                                    | Keep and complete missing explicit presets.                       |
| Core palette/gradients                  | WordPress defaults unless disabled by `theme.json`                            | Disable or narrow only after replacing relied-on `black`/`white`. |
| Body/text/link/heading defaults         | `theme.json` first, reinforced by base CSS where needed                       | Align values without changing computed output.                    |
| Buttons                                 | `theme.json` for honest supported defaults; CSS for PNS-specific shape/shadow | Wire only controls that should survive CSS ownership.             |
| Captions                                | `theme.json` element styles plus minimal CSS if needed                        | Wire if editors should see/use the control.                       |
| Separators/navigation/fixed PNS layouts | Scoped theme CSS                                                              | Document or disable misleading controls where possible.           |
| Broad spacing/block gap controls        | Existing Phase 4/5 spacing follow-ups                                         | Do not duplicate; link into existing Dex tasks.                   |

## Dex Tracking

Dex task state for this rollout is stored under the standalone theme root:

```text
app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex/tasks.jsonl
```

Use the tracker with the storage path explicitly:

```bash
dex --storage-path /Users/anachronistic/Local\ Sites/pns-stageing/app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex list --all
```

Parent task:

```text
d72fw0le - Mop up Site Identity and Site Editor style controls
```

Phase tasks:

| Phase | Dex ID     | Task                                               |
| ----- | ---------- | -------------------------------------------------- |
| 0     | `g2hpkzuf` | Confirm control baseline and visual gate           |
| 1     | `a7y6t3yb` | Stabilize Site Identity behavior and documentation |
| 2     | `gsvmsdwj` | Normalize palette exposure without visual drift    |
| 3     | `kspg2ven` | Wire or retire dead `theme.json` tokens            |
| 4     | `yrjdny2l` | Make element controls honest                       |
| 5     | `xk6x5x6l` | Decide unsupported or CSS-owned controls           |
| 6     | `di7ymp8j` | Validate editor/frontend parity and closeout       |

Related existing Dex tasks:

- `511irrlz` - parent rationalization rollout for standalone block controls and
  CSS authority.
- `yyqsfqf5` - manual spacing controls refinement. This plan should not
  duplicate broad blockGap/margin/padding work owned there.
- `yvdzp2xw` - full visual snapshot gate restoration. This plan should not
  close while the visual gate is knowingly red unless the blocker is explicitly
  recorded.
- `5koa559i` - final validation phase for the larger rationalization rollout.

## Phase 0 - Confirm Control Baseline And Visual Gate

Goal: refresh the survey evidence immediately before implementation.

Checks:

- Confirm active `stylesheet` and `template` options.
- Confirm active standalone `wp_global_styles` row and content.
- Confirm current `site_logo`, `site_icon`, `blogname`, and `blogdescription`
  values.
- Confirm header/footer rendered output still includes Site Logo and does not
  include Site Title/Site Tagline blocks.
- Inventory current global styles CSS for exposed core/theme palettes,
  gradients, font sizes, and element rules.
- Record whether full visual snapshots are currently green or blocked by the
  existing visual-gate follow-up.

Acceptance checks:

- No source files are changed in this phase.
- The plan records any drift from the 2026-07-03 survey.
- A clean implementation gate is known before `theme.json` or CSS changes
  begin.

## Phase 1 - Stabilize Site Identity Behavior And Documentation

Goal: make the Site Identity model explicit without adding visible title/tagline
text.

Scope:

- Keep Site Identity controls available.
- Verify the activation seeding path for the default Site Logo remains
  idempotent and does not overwrite a valid admin-selected logo.
- Decide whether `core/site-title` and `core/site-tagline` styles should stay in
  `theme.json` as future-safe defaults or be removed to avoid implying visible
  placement.
- Add project guidance only if needed, preferably in this plan or a narrow
  docs note rather than in user-facing UI.

Acceptance checks:

- Header and footer visual output is unchanged.
- Site title/tagline remain available for metadata and SEO/core behavior.
- No Customizer/Site Editor section is disabled wholesale.

Decision:

- Keep Site Identity controls available. Site Title and Tagline are core
  metadata inputs for document titles, SEO/plugin integrations, and any future
  deliberate `core/site-title` or `core/site-tagline` placement; they are not
  part of the current header/footer visual design.
- Keep the existing `core/site-title` and `core/site-tagline` `theme.json`
  defaults as future-safe block styles. Removing them would not make the current
  chrome clearer because the controls still exist in WordPress, but it would
  leave those blocks unstyled if an editor intentionally inserts them later.
- Do not add title/tagline blocks to header or footer by default. Header and
  footer identity output remains logo-only through `core/site-logo`.
- Keep the activation Site Logo seeding path as-is: it returns early for a
  valid admin-selected `site_logo`, reuses an existing theme-seeded attachment
  when needed, and only creates a default logo attachment when no valid Site
  Logo exists. The theme does not seed `site_icon`; the current matching
  `site_icon` value is a WordPress option, not a theme lifecycle contract.

## Phase 2 - Normalize Palette Exposure Without Visual Drift

Goal: make the color palette less confusing while preserving existing class and
variable behavior.

Scope:

- Add explicit theme palette entries for any core presets the theme relies on,
  especially `white` and `black`, or replace source usage with existing PNS
  palette slugs.
- Decide whether to disable `defaultPalette`, `defaultGradients`, and any other
  uncurated core color surfaces after relied-on presets are explicit.
- Preserve generated `.has-*-color` and `.has-*-background-color` classes used
  by templates, saved content, and patterns.
- Keep PNS section color remapping intact.

Acceptance checks:

- Existing templates, parts, patterns, and saved content still resolve their
  color classes.
- Generated global styles expose only intentional editor color choices.
- Frontend visual checks show no apparent color drift.

Decision:

- Keep WordPress default palette exposure enabled for now. Current source and
  saved content already rely on default palette slugs including `white`,
  `black`, `light-green-cyan`, and `vivid-purple`; disabling
  `defaultPalette` would remove a currently wired editor feature unless paired
  with a broader content/style migration.
- Do not add duplicate theme palette entries for default slugs while the default
  palette remains enabled. That would make the editor palette noisier without
  changing rendering behavior.
- Keep WordPress default gradients enabled for now. The live non-revision
  content scan found no gradient preset dependency, but default gradients are a
  working core editor feature and should not be removed as part of this
  no-visual-change mop-up.
- Treat this phase as a dependency classification rather than a control removal.
  A later migration may intentionally replace default slugs with PNS-owned
  palette slugs, but that should be a separate visual-gated change.

## Phase 3 - Wire Or Retire Dead `theme.json` Tokens

Goal: remove misleading design-token declarations or connect them to real CSS.

Scope:

- Decide whether `custom.button_border-radius` should be consumed by button CSS
  or removed in favor of the current fixed square button treatment.
- Decide whether `custom.avatar_border-radius` has a real consumer or should be
  removed until avatar styling exists.
- Check for any additional custom variables exposed by `theme.json` but not
  used by CSS, templates, or patterns.
- Keep any removal as a no-behavior source cleanup; do not change computed
  button/avatar output.

Acceptance checks:

- Dead token removals do not change generated CSS needed by templates or saved
  content.
- Consumed tokens compile to the same computed values as before.
- The plan records every token kept, wired, or removed.

Decision:

- Keep and wire `custom.button_border-radius`. The 2026-07-04 audit found the
  token generated `--wp--custom--button-border-radius` but button output still
  used literal `0px` values in `theme.json` and `styles/components/buttons.css`.
  The token now owns the existing square-button contract for core Button
  defaults, button links, and their shadow pseudo-element; the computed value
  remains `0px`.
- Remove `custom.avatar_border-radius`. The only checked-in avatar block is the
  comments template avatar in `templates/single.html`, and it already declares
  its own inline `20px` radius. A saved-content scan did not find published or
  draft content using `core/avatar`. Keeping a generated `185px` root variable
  would imply an avatar styling contract the theme does not actually support.
- Keep the remaining custom tokens. Spacing, color, navigation/category
  typography, and line-height tokens are consumed by `theme.json`, templates,
  `inc/` render helpers, or authored CSS.

## Phase 4 - Make Element Controls Honest

Goal: align Site Editor element controls with the parts of the design that are
actually author-editable.

Surfaces:

- Text/body defaults.
- Background defaults.
- Links.
- Headings.
- Buttons.
- Captions.

Work:

- Compare `theme.json` output, authored CSS, and rendered computed styles for
  representative content.
- Keep element defaults in `theme.json` where they match current computed
  output.
- Wire caption defaults if captions should be an editor-visible style surface.
- For buttons, preserve the PNS button treatment while making only supported
  color/text defaults editable.
- Avoid changes to broad spacing/margin controls owned by `yyqsfqf5`.

Acceptance checks:

- Representative paragraphs, headings, links, buttons, and captions keep their
  current visual defaults.
- Any intentionally unsupported element control is named and scoped.
- Editor canvas and frontend agree for the supported element defaults.

Decision:

- Keep body/text and background defaults in `theme.json`. The global body
  background/text defaults are active, while paragraph defaults are also
  reinforced by base CSS and existing editor/frontend tests.
- Keep link defaults in `theme.json`. Generic links use the PNS primary text
  color, while scoped components such as footer links and navigation links may
  intentionally override that with block attributes or component CSS.
- Keep heading defaults split between `theme.json` and base CSS. `theme.json`
  owns heading sizes and core Heading typography defaults; base CSS continues to
  preserve the inherited PNS heading rhythm and compact heading line-height.
- Keep button color, typography, and radius defaults in `theme.json`; keep PNS
  shadow, spacing, hover affordances, and clear-button variants in authored
  component CSS. Those component-level button surfaces are not generic Global
  Styles controls.
- Keep caption controls available, but do not invent a PNS caption design yet.
  Captions are currently core/default styled, so `theme.json` now records only
  the current core-compatible caption color and font size. Caption line-height,
  spacing, and any PNS-specific caption treatment remain undesigned and should
  be handled by a future visual-design task rather than this cleanup pass.
- Do not bundle-import or remove `styles/blocks/core-image.css` in this phase.
  That file is not imported by the frontend/editor CSS bundles, but it is
  registered as a block-scoped `core/image` style in PHP. Any future change to
  image block margins or dark-theme caption rules needs rendered asset
  verification rather than an import-graph assumption.

## Phase 5 - Decide Unsupported Or CSS-Owned Controls

Goal: stop pretending that fixed-layout or heavily scripted surfaces are generic
global style controls.

Surfaces to classify:

- Navigation typography, color, hover markers, drawer colors, and spacing.
- Separators.
- Broad blockGap/margin/padding controls outside the existing spacing follow-up.
- PNS fixed section layouts.
- Plugin/vendor output where account or plugin settings are the real owner.

Possible outcomes:

- Keep the control because it is genuinely supported.
- Scope CSS so the control works outside a named fixed-layout component.
- Disable or restrict the control if WordPress supports doing so cleanly.
- Document the unsupported surface if disabling is not practical.

Acceptance checks:

- Each unsupported surface has a reason tied to runtime CSS or WordPress block
  support behavior.
- No broad control is disabled if saved content or normal editorial blocks rely
  on it.
- Follow-up tasks are created for spacing or visual-gate work that belongs to
  existing Dex tasks.

Decision:

- Keep Navigation typography/color controls available where they are genuinely
  supported. `theme.json` exposes navigation font-size presets and default text
  color, and runtime CSS preserves inline font-size, small/large classes, and
  the PHP-mapped `--pns--navigation--gap` custom property. Treat hover markers,
  submenu padding, drawer geometry, drawer borders, breakpoints, and mobile
  drawer behavior as CSS/PHP/JS-owned PNS navigation treatment rather than
  generic editor controls.
- Keep existing Navigation restrictions. The cross-site CTA and primary/mobile
  navigation cleanup already strips unsupported overlay/icon surfaces in the
  editor; drawer colors/fonts/gaps are scoped implementation details unless a
  future design task adds a dedicated PNS control.
- Classify Separator styling as CSS-owned and not generally author-editable.
  `theme.json` provides a default, but the rendered separator contract is the
  red, borderless, 3px rule with 20px vertical margin from authored CSS. Do not
  add separator color/width/radius promises until the CSS owner is deliberately
  relaxed and covered by visual tests.
- Keep broad spacing controls enabled for normal editorial blocks. The
  `yyqsfqf5` work already narrowed gap/margin resets so generated blockGap and
  manual spacing controls remain meaningful outside named fixed-layout PNS
  surfaces. Do not disable global spacing support from this phase.
- Classify fixed PNS sections as scoped CSS-owned layouts. Split sections,
  text-only sections, two-column sections, Herstories facts/image strips,
  synced contact/social/read-all sections, previous/next, and section color
  remapping preserve the designed layout/rhythm through component CSS rather
  than generic Site Editor controls.
- Classify plugin/vendor output as scoped vendor override territory. Ecwid and
  EmailOctopus runtime markup is styled through `styles/vendor-overrides/`;
  those account/plugin/runtime surfaces are not reliable Global Styles controls.
- Treat `styles/blocks/core-image.css` as block-scoped registered CSS, not a
  dead file. It is absent from the bundle import graph because PHP registers it
  for `core/image`; any future image/caption change belongs with rendered block
  asset verification.

## Phase 6 - Validate Editor/Frontend Parity And Closeout

Goal: prove the mop-up preserved the current design while making controls more
honest.

Checks:

- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check`
- Block-template validation using the standalone validator.
- WP-CLI checks for active theme, `wp_global_styles`, Site Logo/Icon, and
  template source.
- Local frontend visual checks on covered routes.
- Editor canvas smoke checks for paragraphs, headings, buttons, captions, and
  palette choices.

Acceptance checks:

- Any command that cannot run is recorded with the exact blocker.
- No apparent visual drift is accepted silently.
- Dex completion results contain evidence for each phase.
- The parent task closes only after unsupported controls are either wired,
  restricted, documented, or split to a named follow-up.

Result, 2026-07-06:

- Live WP-CLI state confirmed:
  - `stylesheet` and `template` are both
    `protestsandsuffragettes-standalone`.
  - Site Logo and Site Icon both point at attachment `1052`.
  - Site Title is `Protests & Suffragettes`.
  - Tagline remains populated as site metadata:
    `A creative project & new social enterprise led by a team of artists,
activists, & local historians working to recover and re-voice the histories
of women activists in Scotland.`
  - Front page mode is `page`, with page `49` as the front page.
  - Standalone `wp_global_styles` row `5256` is minimal:
    `{"version":3,"isGlobalStylesUserThemeJSON":true}`.
  - Saved template/template-part rows were recorded; Blog Home `6186` and
    Search Results `6221` were already resynced during the `5koa559i` closeout
    after invalid saved serialization was found.
- Site Identity behavior remains intentional:
  - header/footer chrome renders logo-only;
  - Site Title and Tagline stay available as core metadata and for deliberate
    future `core/site-title` / `core/site-tagline` placement;
  - title/tagline are not added to header/footer by default.
- Supported style-control contract remains:
  - paragraphs, headings, links, buttons, navigation, spacing, palette choices,
    and light/dark surfaces are wired where the theme explicitly supports them;
  - captions stay core-compatible until a formal PNS caption design exists;
  - `core/image` styling remains registered as block-scoped CSS, not imported
    bundle CSS;
  - fixed PNS layout components and vendor/runtime output remain scoped CSS or
    vendor override territory rather than generic Global Styles controls.
- Validation passed:
  - `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone run check`
  - Block-template validation for 16 files.
  - Editor suite: `8/8` passed in `5koa559i`.
  - Focused frontend parity gate: `18/18` passed across desktop, tablet, and
    mobile for readable buttons, PNS dark section inversion, captions,
    navigation controls, header logo positioning, and logo-only Site Identity.
  - Full visual suite from the final parent closeout passed `235` with `2`
    expected skips.

No source edits or snapshot refresh were required for this phase.

## Current Next Step

This mop-up is ready for Dex parent closeout. Any future caption treatment
should be a new design task rather than an implicit Site Editor parity fix.
