# Surface Color Role Token Normalization Plan

Created: 2026-07-09.

All paths are relative to the project root.

## Purpose

Normalize reusable surface and color roles after the grayscale ladder lands.

This plan covers the style-guide `Public Token Candidates` item:

> Surfaces and color roles: about 19 properties for surface background, text,
> heading, muted text, links, buttons, shadows, and dark-surface variants.
> Reusable color roles should not be reinvented per component.

The goal is not to add more magic. The goal is to make the surface model honest:
raw palette colors live in the palette, reusable semantic roles live in one
documented token layer, and component CSS derives from those roles instead of
redefining them independently.

## Depends On

- `docs/jobs/2026-07-09-public-token-candidates-normalization-plan.md`
- Grayscale ladder migration and DB refresh from Dex parent `k8yd3l0x`.
- Completed light/dark surface history:
  - `docs/jobs/__completed/2026-07-04-light-surface-theme-extraction-plan.md`
  - `docs/jobs/__completed/2026-07-05-dark-surface-mitigation-plan.md`

## Related Work

- Completed palette cleanup:
  `docs/jobs/__completed/2026-07-06-theme-json-color-token-cleanup-plan.md`
- Cascade layer migration guardrails:
  `docs/jobs/2026-07-07-css-cascade-layer-migration-plan.md`
- Core block selector ownership guardrails:
  `docs/jobs/2026-07-07-core-block-selector-classification-plan.md`
- DB compatibility guardrails:
  `docs/jobs/2026-07-07-priority-db-compatibility-debt-plan.md`

## Dex Tracking

- Parent: `k8yd3l0x` - Normalize public token candidates and grayscale ladder
- Workstream: `01j3oq9u` - Token normalization Cut 5 - normalize remaining
  public candidates
- Task: `us5nnntn` - Token normalization Cut 5a - surface and color role tokens

## Current Evidence

Current surface/color role ownership is split across:

- `theme.json` palette colors and hidden `settings.custom.color` values;
- `styles/shared/settings.css` private brand aliases;
- `styles/components/section-theme.css` surface variables:
  - `--pns-surface-text`
  - `--pns-surface-heading`
  - `--pns-surface-button-background`
  - `--pns-surface-button-color`
  - `--pns-surface-button-shadow`
  - `--pns-surface-text-action-color`
  - `--pns-section-*` aliases
- `styles/components/light-surface.css`;
- `styles/components/buttons.css`;
- vendor adapters such as Ecwid and EmailOctopus.

The existing surface model is directionally useful, but its roles are not cleanly
separated from palette values, component variables, and vendor compatibility.

Follow-up evidence from the surface-role implementation:

- The old private `--pns--color--brand-purple`,
  `--pns--color--deep-purple`, `--pns--color--red`,
  `--pns--color--accent-mint`, and `--pns--color--banner-yellow` aliases deserve
  a later audit pass. Some remain useful component contracts, but generic
  component CSS should increasingly consume semantic roles or public palette
  tokens directly instead of relying on private brand aliases.
- The semantic role audit did not find another fake role equivalent to
  `color.surface.inverse`; narrow roles such as inverse text and inverse button
  colors have real dark-surface consumers.

## Ownership Model

Use three layers:

1. **Palette tokens**
   - Raw author-facing color choices.
   - Examples: brand colors and the neutral ladder.
   - Stored in `settings.color.palette`.

2. **Semantic role tokens**
   - Reusable site-wide roles derived from palette tokens.
   - Examples: default surface background, default text, muted text, link text,
     separator border, inverse surface text, default button background, default
     button shadow.
   - Stored in a documented token layer, preferably `theme.json`
     `settings.custom.color` only when the role is truly reusable.

3. **Component-local aliases**
   - CSS variables that express a component API or state.
   - Examples: section current text, section current button background, vendor
     storefront bridge variables.
   - Stored near the owning CSS and derived from palette or semantic roles.

## Proposed Role Candidates

Audit before final names, but likely reusable roles are:

- `color.text.default`
- `color.text.muted`
- `color.text.inverse`
- `color.link.default`
- `color.surface.default`
- `color.surface.muted`
- `color.surface.inverse`
- `color.border.subtle`
- `color.button.background`
- `color.button.text`
- `color.button.shadow`
- `color.button.inverse-background`
- `color.button.inverse-text`

Do not expose every role in the editor color picker. Editor-facing choices
remain the palette. Role tokens exist so theme CSS can share one source of truth.

Accepted direction:

- Use the hybrid model: palette tokens remain editor-facing; a small semantic
  role layer can exist for reusable theme CSS roles; component and vendor aliases
  stay CSS-local.
- Keep the reusable role set small. Start with text, muted text, inverse text,
  link, default/muted/inverse surfaces, subtle border, and default/inverse button
  color roles.
- Keep `--pns-section-*`, vendor bridge variables, and component state aliases
  out of the public role layer.
- Do not promote vendor-only values into public roles.

Implementation decision:

- Landed role names:
  - `color.text.default`
  - `color.text.heading`
  - `color.text.muted`
  - `color.text.inverse`
  - `color.link.default`
  - `color.link.hover`
  - `color.surface.default`
  - `color.surface.muted`
  - `color.border.subtle`
  - `color.button.background`
  - `color.button.text`
  - `color.button.shadow`
  - `color.button.inverse-background`
  - `color.button.inverse-text`
- `color.surface.inverse` was deliberately not added in this pass because no
  source CSS consumed it. Inverse surface behavior currently means explicit dark
  background classes set inverse text/button roles; they do not need a fake
  background role.

## Execution Plan

### 1. Refresh Surface Inventory

- Scan source and DB-backed content for current palette and surface classes.
- Inventory all `--pns-surface-*`, `--pns-section-*`, and role-like color
  variables.
- Separate public roles from component-local and vendor bridge variables.
- Reconfirm which dark backgrounds trigger inverse surface behavior after the
  neutral ladder rename.

### 2. Define Role Token Names

- Choose final role names after the inventory.
- Keep names semantic and value-agnostic.
- Avoid `primary` / `secondary` unless they describe a stable role, not a vague
  shade.
- Do not recreate old aliases under new names without deleting the old source of
  truth.

### 3. Rewire Surface CSS

- Update `section-theme.css` so section variables derive from semantic role
  tokens.
- Update light-surface and dark-surface defaults to consume the same role layer.
- Update button color defaults only where they are surface-role behavior.
- Keep explicit editor-selected text/background colors winning over defaults.

### 4. Keep Vendor Adapters Separate

- Leave Ecwid, Jetpack, and EmailOctopus quirks in vendor/component files.
- Allow vendor adapters to consume semantic roles.
- Do not promote vendor-only values into public roles.

### 5. Verify Editor Color Control Surfaces

- Ensure the full PNS palette is the editor-facing palette for text,
  background, link, border, and overlay/background color controls.
- Disable WordPress default palette/gradient/duotone presets unless explicitly
  replaced with PNS-owned values.
- Avoid exposing semantic role tokens in editor color pickers; roles are theme
  CSS contracts, while palette tokens are editor choices.
- Confirm no block-specific `settings.blocks.*.color.palette` override narrows
  or replaces the global PNS palette.

### 6. Future Gradient And Duotone Token Slice

Implementation status, 2026-07-09:

- Dex `qsdj95m0` promotes this future slice into active work.
- `theme.json` now exposes six PNS-owned gradient presets and five PNS-owned
  duotone presets based on the public palette.
- Current source and DB audit found no saved gradient preset use; saved
  duotone references are `duotone: "unset"` image/cover safeguards and are not
  migrated.
- The private PNS Style Guide page now includes `Gradient And Duotone Tokens`
  and expanded `Form Control Primitives` sections, with rollback exports under
  `docs/jobs/gradient-duotone-db-backups/`.
- The editor validation status is now explicit: `test:editor` passes the live
  editor checks and skips only the optional private `pns-editor-css-fixture`
  tests when that fixture page is absent from the local database.

Current decision:

- Keep WordPress default gradient and duotone presets disabled; expose only
  PNS-owned presets.
- Base all future presets on the current public palette:
  - `neutral-0`
  - `neutral-50`
  - `neutral-200`
  - `neutral-700`
  - `neutral-800`
  - `neutral-950`
  - `brand-purple`
  - `deep-purple`
  - `red`
  - `accent-mint`
  - `brand-yellow`
- Do not expose semantic role tokens as editor gradient/duotone choices; the
  editor should expose palette-based visual choices.

Initial gradient candidates to audit visually:

- `deep-purple-to-brand-purple`
- `brand-purple-to-red`
- `brand-purple-to-accent-mint`
- `brand-yellow-to-red`
- `neutral-950-to-brand-purple`
- `neutral-50-to-neutral-0`

Initial duotone candidates to audit visually:

- `deep-purple-and-neutral-0`
- `neutral-950-and-neutral-0`
- `brand-purple-and-accent-mint`
- `red-and-brand-yellow`
- `neutral-800-and-neutral-50`

Landing requirements:

- Add `settings.color.gradients` and `settings.color.duotone` only after the
  candidate set is visually checked against cover/image use cases.
- Keep `settings.color.defaultGradients: false` and
  `settings.color.defaultDuotone: false`; do not re-enable WordPress defaults.
- Update the private style guide to show gradient and duotone presets separately
  from the flat color palette.
- Run editor and visual checks that cover cover overlays, image duotone, and any
  saved content already using gradient/duotone attributes.

### 7. Update Style Guide

- Expand the Color Tokens section:
  - brand palette;
  - neutral ladder;
  - semantic surface/color roles;
  - gradient and duotone presets once they exist;
  - vendor adapter notes.
- The style guide should make clear which tokens are editor-selectable palette
  values and which are theme role tokens.

## Validation

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:fast
```

Conditional:

- `test:visual:shop` / `test:visual:ecwid` if Ecwid roles change.
- `test:visual:emailoctopus` if form/vendor roles change.
- `test:visual:navigation` if Navigation color roles change.

## Done When

- Reusable surface/color roles have one owner.
- Editor color controls use the PNS palette rather than WordPress default color
  presets.
- Gradient and duotone controls remain disabled until PNS-owned presets are
  defined from the current palette and validated.
- Section and light/dark surface CSS derive from role tokens instead of
  reinventing values.
- Vendor patches remain isolated.
- The private style guide distinguishes palette, neutral ladder, semantic roles,
  and vendor adapters.
- No old grayscale/custom color aliases are needed for current non-revision DB
  content.
