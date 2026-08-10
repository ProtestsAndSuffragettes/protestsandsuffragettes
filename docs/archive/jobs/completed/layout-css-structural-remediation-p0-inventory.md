# Layout CSS Structural Remediation P0 Inventory

## Scope

This inventory supports Phase 0 of
`docs/jobs/layout-css-structural-remediation-plan.md`.

Dex task: `xfcs2uru`

Covered routes:

- `/`
- `/herstories/mary-barbour/`
- `/edu-giveaway/`
- `/shop/`

Covered viewports:

- `1440px`
- `900px`
- `390px`

## Baseline Commands

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes compile:css
```

Result: passed before Phase 1 implementation.

Browser probes used Playwright Chromium from `@playwright/test` with elevated
local access.

## Root Cause Summary

- `theme.json` currently sets global `styles.spacing.blockGap` to `1.5rem`.
- `styles/shared/settings.css` currently sets `--wp--style--block-gap: 0`.
- WordPress still emits generated layout CSS with explicit spacing, including
  navigation `gap: 1.6em`, social links `gap: 8px`, and flow margins from the
  global block gap.
- `styles/layout/index.css` compensates from below with broad `!important`
  suppressors for flex gap and flow margin.

## Selector Inventory

### Broad Flex Layouts

Selector:

```css
body .is-layout-flex
```

Rendered matches:

- Home: `19`
- Mary Barbour: `22`
- Edu Giveaway: `14` visible from `17`
- Shop: `11`

Representative current computed result:

- Header/wide group flex blocks compute `gap: 0px`.
- Removing the broad reset in earlier probes exposed generated gaps such as
  navigation `25.6px` and column `24px`.

Phase 1 implication: global block gap should move to `0`, and remaining
non-zero flex gaps should be added through narrower component/block owners.

### Intentional Non-Zero Flex Gaps

Selectors:

```css
.shop-intro .is-layout-flex
body .previous-next .is-layout-flex
```

Rendered evidence:

- `.shop-intro` renders on Home, Mary Barbour, and Edu Giveaway, but not the
  local `/shop/` route.
- `.shop-intro .is-layout-flex` currently computes `24px`.
- `.previous-next .is-layout-flex` renders on Mary Barbour and currently
  computes `24px`.

Phase 1 implication: these are intentional follow-on owners for `1.5rem` gaps
after the global block gap changes to `0`.

### Root Flow Spacing

Selector:

```css
body .is-layout-flow > * + *,
body .wp-site-blocks > * + *
```

Rendered matches:

- `.wp-site-blocks > * + *` appears on every covered route.
- Current computed top-level site sibling margin is `0px`.
- Earlier removal probes let WordPress generated block gap add `24px` before
  top-level site blocks.

Phase 1 implication: setting global block gap to `0` should make this broad
late suppressor redundant.

### Constrained Content

Selector:

```css
body
  .is-layout-constrained
  > :where(:not(.alignleft):not(.alignright):not(.alignfull))
```

Rendered matches:

- Home: `20`
- Mary Barbour: `28`
- Edu Giveaway: `21`
- Shop: `15`

Current issue:

- The selector includes `.alignwide`, which contributes to constrained-vs-wide
  width conflicts.
- Removing the `44rem !important` guard previously let Shop nested content
  collapse to a generated `66.66%` width.

Phase implication: defer to Phase 2 and Phase 3.

### Alignwide

Selectors:

```css
body .is-layout-constrained > .alignwide
.alignwide
```

Rendered matches:

- Home: `5` `.alignwide`
- Mary Barbour: `6` `.alignwide`
- Edu Giveaway: `6` `.alignwide`
- Shop: `4` `.alignwide`

Current behavior:

- At `1440px`, constrained alignwide blocks compute around `1360px`.
- Below `1280px`, constrained alignwide blocks stay wider than the `44rem`
  content width because of current CSS overrides.

Phase implication: defer to Phase 3. Do not normalize wide widths during P1.

### Flow Child Centering

Selector:

```css
body .is-layout-flow > *
```

Rendered matches:

- Home: `65` visible from `66`
- Mary Barbour: `99` visible from `100`
- Edu Giveaway: `55` visible from `81`
- Shop: `24` visible from `29`

Current issue:

- Removing broad `margin: auto !important` previously changed flow child
  heights and offsets across covered pages.

Phase implication: defer to Phase 4.

## Phase 1 Starting Position And Attempt Result

Initial P1 candidates:

- Change `theme.json` global block gap from `1.5rem` to `0`.
- Remove duplicate `--wp--style--block-gap: 0` from `styles/shared/settings.css`
  so `theme.json` is the owner.
- Remove broad `body .is-layout-flex { gap: 0 !important; }`.
- Remove broad flow spacing suppressor for `body .is-layout-flow > * + *` and
  `body .wp-site-blocks > * + *`.

Expected follow-on owners:

- Header navigation should explicitly keep `gap: 0`.
- `.previous-next` should explicitly keep `gap: 1.5rem`.
- `.shop-intro` should explicitly keep `gap: 1.5rem`.
- Social links should keep their smaller block-specific gap.
- `.no-gap` remains as an explicit author utility.

Attempt result:

- `theme.json` `blockGap: 0` plus removal of the duplicate settings variable
  and broad flex reset passed targeted computed-style probes.
- The full visual suite failed all full-page snapshots because page heights
  changed across desktop, tablet, and mobile.
- Restoring only the broad flow spacing suppressor did not fix the snapshot
  drift.
- P1 therefore needs a deeper inventory of generated flex, columns, buttons,
  and page-height owners before the global blockGap model can change safely.

Additional P1 owner gaps found during review:

- Header navigation gap owners must be in the frontend-loaded navigation
  stylesheet, not only the shared/editor navigation stylesheet.
- Social links should explicitly preserve their current `8px` block-specific
  gap.
- Columns and buttons need route-by-route computed targets before removing the
  broad flex gap reset.
- `.previous-next` should move from generic layout CSS to a dedicated
  component/pattern owner.
- `.shop-intro` already has a component owner, but its `!important` can only be
  removed after the broad flex reset is retired.
- `.active-dates` may reveal an existing `1rem` page-type gap after the broad
  flex priority is removed, so it must be included in the before/after probe.
- If the whole `body .is-layout-flex` rule is deleted, the next pass must also
  verify anything relying on its current `position: relative`.
