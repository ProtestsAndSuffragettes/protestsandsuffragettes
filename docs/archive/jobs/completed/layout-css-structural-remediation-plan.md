# Layout CSS Structural Remediation Plan

## Summary

The remaining TODOs in `styles/layout/index.css` are not simple deletion
candidates. They are late-cascade guards compensating for upstream ownership
conflicts between `theme.json`, WordPress generated layout support CSS, page
content layout settings, and broad inherited legacy layout behavior.

The goal is to make `styles/layout/index.css` smaller and less forceful by
correcting those upstream owners first. Do not remove the remaining
`!important` layout rules until the relevant upstream phase has passed its Dex
gate and regression checks.

## Current Root Causes

- `theme.json` sets global `styles.spacing.blockGap` to `1.5rem`, while
  `styles/shared/settings.css` sets `--wp--style--block-gap: 0`.
- WordPress generated layout support CSS emits flow margins and flex/grid gaps
  from the global block gap.
- The constrained-content guard does not exclude `.alignwide`, so constrained
  and wide-width behavior fight each other.
- Some page content appears to carry generated container width settings, such as
  the Shop `66.66%` nested layout behavior.
- The current wide layout design is more fluid than `theme.json`'s
  `wideSize: 1280px`, so CSS still owns custom wide calculations.
- Broad legacy centering through `body .is-layout-flow > * { margin: auto
!important; }` is still affecting real rendered pages.

## Resolved Decisions Before Implementation

Use these decisions unless Phase 0 evidence proves they are wrong:

- Global block gap: set `theme.json` `styles.spacing.blockGap` to `0`, then
  add intentional gaps in block, component, and page owners. The current CSS is
  already suppressing WordPress' generated `24px` spacing broadly, so `0`
  matches the inherited site model better than `1.5rem`. Initial Phase 1
  implementation showed this cannot be flipped safely until more generated gap
  and height owners are mapped; keep this as the target model, not an
  immediate isolated edit.
- Wide width intent: preserve the current fluid wide behavior for now. The
  current `1360px`-at-`1440px` behavior appears intentional enough that it
  should be documented as a layout primitive before attempting to normalize to
  static `wideSize: 1280px`.
- Shop generated container issue: fix stored block/page layout settings rather
  than keeping a global `44rem !important` guard permanently. The CSS guard is
  compensating for generated content configuration, not expressing design
  intent.
- Visual instability: fix or explicitly account for the Home-to-Mary-Barbour
  desktop snapshot instability before using full-page screenshots as the only
  layout gate. During remediation, pair visual tests with targeted computed
  probes and isolated reruns.
- Editor impact: run `pnpm test:editor` for every phase that touches
  `theme.json`, shared layout CSS, shared components, or editor imports.
- Dex setup: create the parent and all phase tasks before Phase 0 starts, then
  record task IDs in this document.

## Dex Tracking

All implementation must be tracked through the child-theme Dex store:

```bash
cd app/public/wp-content/themes/protestsandsuffragettes
dex list --all
```

If local Dex config is missing or stale, use:

```bash
dex --storage-path /Users/anachronistic/Local\ Sites/pns-stageing/app/public/wp-content/themes/protestsandsuffragettes/.dex list --all
```

Before implementation, create one parent task and one child task per phase.
Record the resulting IDs here:

- Parent task: `jqx38hw6` - Layout CSS structural remediation
- Phase 0: `xfcs2uru` - Establish layout regression baseline
- Phase 1: `cdsgy7x3` - Resolve global block gap ownership
- Phase 2: `g6rmfm6p` - Fix generated constrained container settings
- Phase 3: `483xq6a9` - Repair alignwide ownership and wide-size model
- Phase 4: `krte0k5m` - Replace broad flow child centering
- Phase 5: `tr7qpdq1` - Remove obsolete layout TODOs and priorities

Do not mark a phase complete until the verification notes, commands, and any
known instability are recorded in Dex.

## Global Gates

Every phase must pass or explicitly document:

- CSS compile: `pnpm compile:css`
- Diff hygiene: `git diff --check`
- Targeted computed-style probes for the selectors being changed
- Frontend visual regression: `pnpm test:visual`
- Editor harness when shared layout, shared components, `theme.json`, or editor
  imports are touched: `pnpm test:editor`

Known caveat: the desktop Mary Barbour full-page snapshot can fail in suite
order after the Home snapshot while passing in isolation. Treat this as a
visual harness instability unless a targeted computed-style or isolated visual
rerun proves the CSS batch changed Mary Barbour layout.

## Phase 0: Baseline And Selector Inventory

### Objective

Record the current behavior before changing ownership.

### Work

- Inventory rendered matches for:
  - `body .is-layout-flex`
  - `body .is-layout-flow > *`
  - `body .wp-site-blocks > * + *`
  - `body .is-layout-constrained > :where(...)`
  - `body .is-layout-constrained > .alignwide`
  - `.alignwide`
- Capture computed widths, gaps, margins, and scroll widths on:
  - `/`
  - `/herstories/mary-barbour/`
  - `/edu-giveaway/`
  - `/shop/`
- Record whether each rule is affected by WordPress generated CSS, authored
  theme CSS, inline block support styles, or page content settings.

### Dex Gate

Phase 0 can close only when Dex includes the selector inventory and a baseline
verification run.

## Phase 1: Resolve Global Block Gap Ownership

### Objective

Stop fighting WordPress generated flex/flow spacing from below.

### Upstream Fix

Choose the real global spacing model:

- If the site default is no automatic block spacing, set
  `theme.json` `styles.spacing.blockGap` to `0`.
- If the site default is `1.5rem`, remove the global reset and move zero-gap
  requirements to narrower owners.

Final decision: `theme.json` owns the global block gap as `0`.
`styles/shared/settings.css` must not also define `--wp--style--block-gap`.
Intentional visible spacing is owned locally by block, component, utility, or
page-type selectors.

### Candidate Rules To Retire

- `body .is-layout-flex { gap: 0 !important; }`
- `body .is-layout-flow > * + *`
- `body .wp-site-blocks > * + *`

### Follow-On Owners

After changing the global default, add intentional gaps where needed:

- Navigation block/header composition
- Columns and buttons where the block needs visible spacing
- `.previous-next`
- `.shop-intro`
- Page-type or component sections that intentionally use the old `1.5rem`
  rhythm

### Dex Gate

Close only when visual and computed-style checks prove no unintended global
spacing drift, especially top-level site blocks, navigation, columns, buttons,
Shop intro, and previous/next controls.

### Implementation Note: Initial Attempt

Initial implementation set `theme.json` `styles.spacing.blockGap` to `0`,
removed the duplicate `--wp--style--block-gap: 0` setting, removed the broad
flex gap reset, and added narrower owners for navigation, `.previous-next`, and
`.shop-intro`.

Computed-style contracts passed, but all full-page visual snapshots drifted in
height across desktop, tablet, and mobile. Restoring only the root flow-spacing
reset did not fix the drift, which means the broad flex/global blockGap change
still affects generated layout behavior not yet covered by narrower owners.

Result: Phase 1 remains open. Do not repeat the isolated global blockGap flip
until Phase 1 has a more complete inventory of generated flex/column/button
spacing owners and expected page-height effects.

### Implementation Note: Completed P1 Ownership Migration

The completed P1 pass treated block gap as an ownership migration rather than a
single global flip:

- `theme.json` now owns `styles.spacing.blockGap: "0"`.
- `styles/shared/settings.css` no longer defines `--wp--style--block-gap`.
- The broad `body .is-layout-flex { gap: 0 !important; }` reset was removed;
  `position: relative` remains in layout CSS.
- The broad root flow spacing reset was removed after footer/template-part and
  Shop intro rhythm gained narrower owners.
- Intentional visible gaps now live in block/component/page owners for
  navigation, social links, columns, buttons, previous/next, shop intro, active
  dates, footer/template-part internals, and Shop intro paragraph pairs.

P1 gates passed after the Mary Barbour visual harness was stabilized for its
image-heavy full-page snapshot: compile via local Lightning CSS, editor
regression, full visual regression, `layout-gap-owner-probe`, and
`git diff --check`.

### Next Attempt Requirements

Before the next Phase 1 implementation attempt, add or verify these narrower
owners:

- Frontend navigation: the frontend bundle imports
  `styles/blocks/core-navigation-frontend.css`, so header navigation gap owners
  must live there or in another frontend-loaded block stylesheet. Preserve both
  the current default `header .wp-block-navigation` zero-gap behavior and the
  special `header .wp-block-navigation.wp-container-2` `1rem` behavior if both
  still render.
- Social links: verify or add a block-specific owner for the current `8px`
  social icon gap.
- Columns and buttons: inventory every rendered
  `.wp-block-columns.is-layout-flex` and `.wp-block-buttons.is-layout-flex`
  before choosing `0`, `1.5rem`, or responsive row/column gap owners.
- Previous/next controls: move the `1.5rem` gap out of generic layout CSS into
  a dedicated previous/next component or pattern owner.
- Shop intro: keep its `1.5rem` component gap in `shop-intro.css`; remove
  `!important` only after the broad flex reset is gone.
- Herstories active dates: verify `.active-dates` and
  `.active-dates .is-not-stacked-on-mobile`, because removing the broad flex
  priority may reveal the existing `1rem` page-type gap and affect page height.
- Flex positioning: if deleting the whole broad flex rule, not just its gap,
  verify dependents that may rely on `position: relative`, including logo and
  absolutely positioned navigation affordances.

The next Phase 1 probe must list every `body .is-layout-flex` match on Home,
Mary Barbour, Edu Giveaway, and Shop, grouped by classes/selector, with `gap`,
`rowGap`, `columnGap`, box height, and offset before and after the change. Also
record route `scrollHeight` and top-level
`body .wp-site-blocks > * + *` `margin-block-start`.

## Phase 2: Fix Generated Constrained Container Settings

### Objective

Remove the need for the late `44rem !important` constrained-content guard.

### Upstream Fix

Inspect the block editor/page content for generated container layout settings
that override the global content width. The known high-risk case is `/shop/`,
where removing the guard lets nested content shrink to `66.66%`.

Fix the stored block layout settings or page template composition so constrained
children inherit the intended `44rem` content width without a late global
override.

### Candidate Rule To Retire

```css
body
  .is-layout-constrained
  > :where(:not(.alignleft):not(.alignright):not(.alignfull)) {
  max-inline-size: 44rem !important;
}
```

### Dex Gate

Close only when removing the rule keeps constrained child content at the
intended width on Home, Mary Barbour, Edu Giveaway, and Shop, including the Shop
nested content that previously collapsed to `66.66%`.

### Implementation Notes

P2 corrected the stored source rather than adding another CSS override.

- Live source: saved `wp_template_part` ID `4666`,
  `contact-form-octopus-tempate-part`.
- Faulty stored setting:
  `<!-- wp:group {"layout":{"type":"constrained","contentSize":"66.66%"}} -->`.
- Correction: changed that group layout to `contentSize:"44rem"`, matching the
  theme content width.
- Backup before DB mutation:
  `/tmp/pns-template-part-4666-before-p2.html`.
- Targeted gate artifact:
  `/tmp/pns-p2-constrained-layout-probe.json`.
- P2 probe result: 12 route/viewport checks passed across Home, Mary Barbour,
  Edu Giveaway, and Shop; core block-supports generated `66.66%` rules were no
  longer present after the saved template-part correction.

The only remaining non-`704px` constrained child width observed by the P2 probe
was the component-owned footer separator at `200px`.

## Phase 3: Repair Alignwide Ownership And Wide-Size Model

### Objective

Stop making `.alignwide` fight the constrained-content guard.

### Upstream Fix

First exclude `.alignwide` from the constrained-content guard:

```css
:not(.alignwide): not(.alignfull);
```

Then decide whether the current fluid wide behavior is intentional:

- Normalize to `theme.json` `wideSize: 1280px`, or
- Update the design token model so the current fluid width is explicit and
  documented.

The current CSS expresses behavior that `theme.json` does not:

- below `1280px`, constrained alignwide blocks need to stay wider than `44rem`;
- at `1440px`, the desktop calculation produces approximately `1360px`, not
  the static `1280px` wide size.

### Candidate Rules To Retire

- `body .is-layout-constrained > .alignwide { max-inline-size: 100% !important; }`
- desktop `body .is-layout-constrained > .alignwide` custom calculation
- duplicate global `.alignwide` width rules if `theme.json` becomes the owner

### Dex Gate

Close only when computed checks prove alignwide behavior is intentional at
mobile, tablet, 1279px, 1280px, 1440px, and wide desktop widths.

### Implementation Notes

P3 keeps the existing fluid wide behavior rather than normalizing to a static
`1280px` model.

- `theme.json` now sets `settings.layout.wideSize` to
  `min(100%, var(--pns--layout--wide-breakout-size))`.
- `styles/shared/settings.css` defines
  `--pns--layout--wide-breakout-size: calc(50vw + 40rem)`.
- The broad constrained-alignwide priority rules were removed from
  `styles/layout/index.css`.
- Repeated page/component wrapper calculations in Shop, Shop intro, footer
  wrapper, and the `.max-inline-size` utility were intentionally left on the
  historical `calc(88rem + calc(50vw - 44rem))` model because switching them to
  the clamped theme wide token caused desktop scroll-height drift.
- Footer columns keep their historical desktop breakout through a scoped
  `footer.alignwide .is-layout-constrained > .alignwide` owner above `1280px`;
  this replaces the old broad layout priority without changing the measured
  footer widths.
- Targeted gate artifacts:
  `/tmp/pns-p3-alignwide-before.json` and
  `/tmp/pns-p3-alignwide-after.json`.
- P3 probe result: all 24 route/viewport checks matched baseline widths and
  overflow state across mobile, tablet, 1279px, 1280px, 1440px, and 1920px.

## Phase 4: Replace Broad Flow Child Centering

### Objective

Remove inherited global centering and replace it with intentional owners.

### Upstream Fix

Inventory which flow children actually depend on:

```css
body .is-layout-flow > * {
  margin: auto !important;
}
```

Then move those needs to narrower homes:

- `theme.json` layout support where WordPress can own it
- `.m-auto` utility for author-applied content centering
- page-type CSS for specific page families
- component CSS for reusable sections
- block-owned styles only when the behavior is truly a block default

### Candidate Rule To Retire

- `body .is-layout-flow > * { margin: auto !important; }`

### Dex Gate

Close only when removing the broad rule no longer changes flow child offsets,
heights, or page scroll height across the covered routes.

### Implementation Note: First P4 Attempt Rolled Back

Attempted on 2026-06-23 after frontend/editor Phase 7 completion.

Actions tried:

- Added `tests/visual/flow-centering-probe.mjs` to inventory rendered
  `body .is-layout-flow > *` children across `/`, `/herstories/mary-barbour/`,
  `/edu-giveaway/`, and `/shop/` at desktop, tablet, and mobile.
- Removed the all-axis `body .is-layout-flow > * { margin: auto !important; }`
  guard.
- Tried narrower owners for `.grid > .m-auto`, `.active-dates`, and
  `.inline-container`.
- Moved the global paragraph margin reset from a priority rule toward a normal
  base reset.
- Tried replacing the all-axis centering rule with a block-axis-only flow reset.

Result:

- Compile, Prettier, `git diff --check`, Wallace, and the editor harness passed.
- Frontend computed-style contracts passed.
- Full-page snapshots failed across all 12 route/viewport screenshots because
  rendered page heights shrank by roughly 30-60px.
- The CSS changes were backed out except for the reusable probe. The failure is
  now documented next to the retained layout and paragraph TODO comments.

Next P4 attempt should not start by deleting the flow guard again. First isolate
the remaining height owner inside nested Shop/social/footer flow content and
decide whether the 30-60px height reduction is an accepted visual improvement
or a regression that needs exact owner replacement.

### Implementation Note: Option C Source Owners

Second attempt on 2026-06-23 used source-owned rhythm and alignment instead of
preserving the broad flow child reset.

Actions:

- Expanded `tests/visual/flow-centering-probe.mjs` to capture direct flow
  rhythm elements and visible paragraphs, including owner guesses, margins,
  offsets, heights, and text samples.
- Removed `body .is-layout-flow > * { margin: auto !important; }` from
  `styles/layout/index.css`.
- Changed base headings, paragraphs, lists, and ordered lists to explicit
  `margin-block: 0` in `styles/base/elements.css`.
- Moved remaining rhythm and alignment to narrower owners:
  `.grid > .m-auto`, `.inline-container`, `.active-dates`,
  `.active-dates ul`, Shop intro paragraph groups, Shop page intro paragraphs,
  footer/contact paragraphs, footer copyright text, and EmailOctopus status
  paragraphs.

Gate evidence:

- Targeted before/after probe files:
  `/tmp/pns-p4-optionc-before-rich.json` and
  `/tmp/pns-p4-optionc-after3-rich.json`.
- Probe comparison showed zero drift across all covered routes/viewports for
  body scroll height, document scroll height, flow-child count, flow-child
  inline margins, flow-child heights, flow-child offsets, and paragraph margin
  values.

## Phase 5: Remove Obsolete Layout TODOs And Priorities

### Objective

Finish the cleanup after upstream ownership is corrected.

### Work

- Remove any layout TODO whose blocker was resolved in Phases 1-4.
- Remove `!important` declarations that no longer protect against generated
  WordPress or plugin output.
- Review the remaining `.is-layout-flow ul { padding-inline-start: 1rem; }`
  broad list-indentation owner and either move it to narrower list/page owners
  or document why global flow list indentation remains intentional.
- Update `docs/css/README.md` if the layout ownership strategy changes.
- Refresh Wallace stats if the compiled CSS changes materially.
- Record final active authored `!important` count.

### Dex Gate

Close only when the final Dex note includes:

- removed selectors and why they are safe;
- retained selectors and the remaining blocker;
- compile/test results;
- updated CSS metrics if applicable.

## Recommended Implementation Order

1. Phase 0 inventory.
2. Phase 1 block gap ownership.
3. Phase 2 generated constrained container cleanup.
4. Phase 3 alignwide model.
5. Phase 4 flow centering.
6. Phase 5 final TODO and priority cleanup.

Do not start with the alignwide or flow-centering deletions. They are symptoms
of the upstream spacing and constrained-layout conflicts and will produce noisy
visual drift if handled first.
