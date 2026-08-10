# Control Primitive Token Normalization Plan

Created: 2026-07-09.

All paths are relative to the project root.

## Purpose

Normalize reusable button and form primitive values without exposing component
or vendor quirks as public design tokens.

This plan covers the style-guide `Public Token Candidates` item:

> Buttons and forms: repeated padding, minimum size, field padding, borders, and
> focus rings should resolve into small shared contracts where they represent
> site UI rather than vendor markup.

## Depends On

- Public token umbrella:
  `docs/jobs/2026-07-09-public-token-candidates-normalization-plan.md`
- Surface/color role cleanup:
  `docs/jobs/2026-07-09-surface-color-role-token-normalization-plan.md`

## Related Work

- Completed design-token consolidation:
  `docs/jobs/__completed/2026-06-24-design-token-consolidation-plan.md`
- Theme CSS/control-surface health:
  `docs/jobs/2026-07-06-theme-css-control-health-remediation-plan.md`
- Vendor plugin override triage:
  `docs/jobs/__completed/vendor-plugin-css-override-triage-plan.md`

## Dex Tracking

- Parent: `k8yd3l0x` - Normalize public token candidates and grayscale ladder
- Workstream: `01j3oq9u` - Token normalization Cut 5 - normalize remaining
  public candidates
- Task: `g9xnbr4c` - Token normalization Cut 5b - button and form control
  primitives

## Current Evidence

Current primitive values are mostly CSS-owned:

- `styles/shared/settings.css`
  - `--pns--button--border-radius`
  - `--pns--button--padding-compact`
  - `--pns--button--padding-block`
  - `--pns--button--padding-inline`
  - `--pns--button--shadow-offset`
  - `--pns--button--min-inline-size`
  - `--pns--form-field--background`
  - `--pns--form-field--border-color`
  - `--pns--form-field--border-radius`
  - `--pns--form-field--border-width`
  - `--pns--form-field--padding`
  - `--pns--form-field--focus-border-color`
  - `--pns--form-field--focus-border-width`
  - `--pns--form-field--focus-ring-width`
- `theme.json`
  - `settings.custom.button_border-radius`
  - core button defaults
- `styles/components/buttons.css`
- `styles/vendor-overrides/emailoctopus.css`
- `styles/blocks/jetpack-contact-form.css`

Some values are site UI primitives. Others are component mechanics, vendor
adapter patches, or visual implementation details.

## Ownership Rules

Promote only values that are reusable across site UI:

- default button padding;
- default button minimum inline size;
- default button border radius;
- default field padding;
- default field border;
- default focus ring.

Keep these private:

- one-off button shadow mechanics;
- query pagination visual exceptions;
- vendor form quirks;
- EmailOctopus/Jetpack structural fixes;
- button hover/active state geometry that is tied to one component.

Accepted direction:

- Keep control primitives mostly in `styles/shared/settings.css` unless
  WordPress block support or editor controls directly benefit from `theme.json`
  ownership.
- Promote only stable shared primitives: default button padding, button minimum
  inline size, button border radius, field padding, field border color, and
  focus ring.
- Treat submit button as a separate variant only if the audit finds repeated
  non-vendor usage.
- Let query pagination consume shared primitives only when it keeps its own
  component ownership.
- Keep EmailOctopus, Jetpack, and other vendor structural fixes private and
  scoped.
- Keep field border/focus colors CSS-private for now. They are control defaults
  used by hosted/vendor form adapters, not reusable editor palette choices.
- Keep `button_border-radius` in `theme.json` as the WordPress-facing custom
  source for core Button support, and expose `--pns--button--border-radius` as
  the theme CSS primitive that component CSS consumes.

## Execution Plan

### 1. Audit Button And Form Values

- Scan authored CSS for button/form padding, borders, min sizes, focus styles,
  and raw colors.
- Separate WordPress core button defaults from PNS component button treatment.
- Separate native form controls from vendor forms.
- Confirm whether each repeated value is site UI, vendor markup adaptation, or
  local component styling.

### 2. Define Primitive Contract

Create a small naming map before editing:

- compact button padding: `--pns--button--padding-compact`;
- button block padding: `--pns--button--padding-block`;
- button inline padding: `--pns--button--padding-inline`;
- button min inline size: `--pns--button--min-inline-size`;
- button border radius: `--pns--button--border-radius`;
- field background: `--pns--form-field--background`;
- field padding: `--pns--form-field--padding`;
- field border: `--pns--form-field--border-width`,
  `--pns--form-field--border-color`, and
  `--pns--form-field--border-radius`;
- field focus ring: `--pns--form-field--focus-border-color`,
  `--pns--form-field--focus-border-width`, and
  `--pns--form-field--focus-ring-width`.

Decide whether each belongs in `theme.json` custom tokens or remains in
`styles/shared/settings.css` as documented CSS-private settings.

### 3. Rewire Theme UI

- Update core button defaults and PNS button CSS to consume the chosen
  primitives.
- Update query pagination only if it consumes a shared button primitive without
  losing its own component ownership.
- Update native contact/form styles separately from EmailOctopus and Jetpack.
- Keep query pagination layout and number geometry component-owned, but consume
  the shared compact padding, border radius, shadow offset, and min-size
  primitives where it already behaves like a theme button/control.

### 4. Isolate Vendor Forms

- Keep EmailOctopus and Jetpack overrides scoped.
- Allow them to consume shared field primitives only when the value is a real
  site default.
- Keep vendor structural corrections in vendor/block CSS.
- EmailOctopus may consume the field and submit-button primitives, but its
  wrapper containment, powered-by hiding, Bootstrap text color, and contact
  section alignment remain vendor adapter rules.
- Hosted-form-only values such as EmailOctopus field margins remain
  vendor-private, even when they happen to match a spacing preset.

### 5. Update Tests And Style Guide

- Add focused computed-style checks for representative buttons and fields.
- Add style-guide examples for default button, clear button, submit button, and
  text field if not already present.

## Validation

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
```

Conditional:

- `test:visual:emailoctopus` if EmailOctopus styles change.
- `test:visual:shop` or `test:visual:ecwid` if Ecwid buttons/forms change.

## Done When

- Shared button/form primitives have one owner.
- Vendor form patches remain isolated.
- Button and field examples in the style guide reflect the real contract.
- Source scans show repeated cross-surface primitive values are tokenized or
  explicitly documented as private component values.

## Implementation Note, 2026-07-09

- Base form controls now have a shared `styles/base/forms.css` owner.
- Generic submit/button text now uses the button text role instead of repeating
  the background role.
- The private style guide now covers default, disabled, inverse, and clear
  button states plus text, email, textarea, select, checkbox, and radio controls.
