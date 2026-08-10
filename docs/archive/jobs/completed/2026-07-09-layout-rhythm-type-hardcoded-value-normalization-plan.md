# Layout Rhythm Type And Hard-Coded Value Normalization Plan

Created: 2026-07-09.

All paths are relative to the project root.

## Purpose

Run a scan-first normalization pass for the remaining public-token candidates
that are tightly coupled in this theme: layout primitives, content rhythm,
typography, repeated hard-coded spacing, and the small global motion question.

This plan covers these style-guide `Public Token Candidates` items:

> Layout primitives: content widths, site frame sizing, single-post headers, and
> alignwide behavior should be visible in the system when they are page-level
> layout rules.

> Line height and content rhythm: about 9 properties for paragraph rhythm,
> heading margins, list spacing, quote line-height, and display-tight
> line-height. The baseline rhythm should be centralized; component rhythm
> should be private only when the component owns the behavior.

> Motion: 3 global timing/easing properties. Global motion values should be
> named once rather than copied through component CSS.

> Hard-coded typography: about 68 non-tokenized font declarations across 37
> unique values. Highest-value cleanup targets are heading fallback sizes and
> repeated line-height values.

> Hard-coded spacing: about 174 non-tokenized spacing declarations across 83
> unique values. Promote repeated cross-surface values first; do not promote
> one-off component geometry just because it is counted.

## Depends On

- Public token umbrella:
  `docs/jobs/2026-07-09-public-token-candidates-normalization-plan.md`
- Legacy spacing/saved content/rhythm cleanup:
  `docs/jobs/2026-07-08-legacy-spacing-saved-content-rhythm-cleanup-plan.md`

## Related Work

- Completed rhythm/type scale cleanup:
  `docs/jobs/__completed/2026-07-06-theme-json-rhythm-type-scale-cleanup-plan.md`
- Typography/rhythm polish:
  `docs/jobs/2026-07-06-typography-rhythm-polish-plan.md`
- Content-rhythm heuristic hardening:
  `docs/jobs/2026-07-07-content-rhythm-heuristic-hardening-plan.md`
- Layout stability:
  `docs/jobs/2026-07-06-layout-stability-plan.md`
- Motion/perceived loading:
  `docs/jobs/2026-07-06-motion-perceived-loading-plan.md`

## Dex Tracking

- Parent: `k8yd3l0x` - Normalize public token candidates and grayscale ladder
- Workstream: `01j3oq9u` - Token normalization Cut 5 - normalize remaining
  public candidates
- Task: `03f65m72` - Token normalization Cut 5c - layout rhythm type and
  hard-coded values

## Guardrails

- Do not reopen the accepted type scale unless the scan proves a missing use
  case.
- Do not reopen `.alignwide` as a broad theme layout and saved-content
  compatibility contract without new evidence.
- Do not promote component geometry just because it is counted often.
- Do not move vendor/runtime reservations into public tokens.
- Treat motion as a cited existing plan. This pass only decides whether the
  existing global motion values remain private CSS settings or become documented
  theme custom tokens.
- Default motion direction is to keep the current motion values in
  `styles/shared/settings.css` unless the audit proves they are useful as
  documented theme custom tokens.
- Keep `h3rs0t00` client-approval pending and out of scope.

## Current Evidence

Current public/system layout owners:

- `theme.json` `settings.layout.contentSize`
- `theme.json` `settings.layout.wideSize`
- `styles/shared/settings.css`
  - `--pns--layout--content-size`
  - `--pns--layout--wide-size`
  - `--pns--layout--section-frame-size`

`--pns--layout--section-frame-size` is currently defined as
`calc(50vw + var(--pns--layout--content-size))`. It is a wide framing helper,
not an editor-facing WordPress layout preset. Current consumers include
`.pns-section-inner`, `.pns-section-frame`, footer framing, Shop storefront
framing, and editor split-section previews. It intentionally remains wider than
`wideSize`; a source review found that consolidating the PNS section/frame tier
to `wideSize` would change the established footer, Shop, and split-section
frame contract.

Current rhythm/type owners:

- `theme.json` font-size scale, font-family scale, line-height custom values,
  and element heading defaults;
- `styles/shared/settings.css` compact line-height aliases, display-tight alias,
  and private `rlh` / `lh` content-rhythm values;
- `styles/base/elements.css` baseline paragraph rhythm;
- `styles/page-types/content-rhythm.css` block composition rhythm and
  exclusions;
- `styles/blocks/core-quote.css` quote-specific rhythm.

Designer-facing width map:

| Knob                             | Current value                                | Primary responsibility                                  | Typical consumers                                                                                      |
| -------------------------------- | -------------------------------------------- | ------------------------------------------------------- | ------------------------------------------------------------------------------------------------------ |
| `contentSize`                    | `44rem`                                      | Readable text/content column                            | constrained post content, `.pns-content-frame`, `.pns-copy-column`, comments, single-post header inner |
| `wideSize`                       | `min(100%, calc(50vw + contentSize - 4rem))` | WordPress wide alignment and header-width framing       | `.alignwide`, header inner, wide featured images                                                       |
| `--pns--layout--section-frame-size` | `calc(50vw + contentSize)`                   | PNS section wrapper geometry wider than `wideSize`      | `.pns-section-inner`, `.pns-section-frame`, footer frame, Shop frame, split-section/editor frame math  |

Designer guidance:

- To change ordinary article/page text measure, style-guide inner columns, and
  most readable copy blocks, adjust `contentSize`.
- To change WordPress `alignwide` content and the current header inner width,
  adjust `wideSize`.
- To change PNS full-section wrapper width, footer framing, Shop framing, and
  split-section frame behavior, adjust
  `--pns--layout--section-frame-size`.
- If the design intent is "make the whole site feel wider", expect to review
  all three because they intentionally describe different width tiers.

Current motion values:

- `--pns--motion--duration-interaction`
- `--pns--motion--duration-page-reveal`
- `--pns--motion--easing-standard`

Hard-coded spacing/type counts from the style-guide audit are discovery signals,
not promotion decisions.

2026-07-09 closeout scan after token cleanup:

- authored CSS files scanned outside `styles/dist`: 54;
- unique CSS custom properties outside `theme.json`: 90;
- current custom-property groups:
  - navigation: 31;
  - surface roles: 11;
  - section bridge roles: 6;
  - button primitives: 7;
  - form primitives: 8;
  - rhythm/type aliases: 7;
  - layout aliases: 3;
  - motion aliases: 3;
  - vendor adapters: 10;
  - component-local helpers: 10;
  - WordPress generated-flow bridge: 1.
- hard-coded declaration families are still present, but now classified as
  mostly component, vendor, generated-flow, or fallback behavior rather than
  obvious missing public tokens:
  - spacing/layout-like values: 192;
  - typography values: 48;
  - line-height values: 6;
  - layout mechanics: 16;
  - motion mechanics: 4;
  - color/shadow/opacity values: 25;
  - other control keywords and geometry: 30.

Closeout classification:

- Navigation metrics stay private because they encode the core drawer/desktop
  breakpoint, hover marker, submenu, and z-index behavior.
- Surface and section variables stay private adapters over public
  `theme.json` roles; editors choose palette/background classes, and section
  CSS translates those choices into supported text/button behavior.
- Button and form variables stay private control primitives because they
  normalize native controls, block links, legacy classes, and form submits.
- Rhythm/type aliases stay private where they preserve compact display and
  prose-flow behavior. The WordPress `--wp--style--block-gap` assignment in
  `content-rhythm.css` is retained as a generated-flow bridge, not a public PNS
  token.
- Layout aliases stay private wrappers around WordPress `contentSize` and
  `wideSize`, plus the PNS section frame tier.
- Motion aliases remain in CSS because they are implementation timing contracts
  rather than editor controls.
- Vendor adapters remain scoped to Ecwid and EmailOctopus.
- Component-local helpers remain with their owning components: cross-site
  banner CTA geometry, header logo sizing, query pagination surface, single
  header spacing, and split-section frame math.

## Ownership Rules

Promote:

- global content/wide size and true page-level layout primitives;
- repeated cross-surface spacing values that match the public spacing ladder;
- repeated line-height and typography values that match the public type/rhythm
  system;
- global motion values only if they are intentionally site-wide and worth
  documenting as theme custom tokens.

Keep private:

- split-section frame math;
- header/logo compensation;
- navigation drawer metrics;
- card image negative margins;
- vendor storefront reservations;
- z-index and overlay mechanics;
- `rlh` / `lh` helper values that make block composition work;
- component-specific quote, card, nav, or split-section rhythm.

Accepted direction:

- Keep `contentSize` and `wideSize` as the WordPress-native layout owners.
- Do not reopen the accepted `.alignwide` contract without new evidence.
- Normalize only clear cross-surface spacing/type matches.
- Preserve exact component geometry privately when normalization would make the
  component less honest or visually worse.
- Keep vendor/runtime reservations out of public tokens.
- Keep global motion values in `styles/shared/settings.css` by default.
- Keep `rlh` / `lh` as the preferred internal mechanism for baseline prose and
  block-composition rhythm. Do not replace them with spacing presets merely for
  token purity.
- Keep the third width tier as `--pns--layout--section-frame-size` rather than
  consolidating `.pns-section-frame` / `.pns-section-inner` to `wideSize`.
  The previous `site-frame-size` name was too generic for a private helper that
  primarily owns expanded section and footer/shop framing.
- Remove unused public/custom rhythm tokens instead of wiring selectors just to
  justify them. `--pns--content-rhythm--heading-margin-block-end` had no
  source consumer, so this pass removes it.
- Remove unused custom line-height entries instead of keeping aliases for
  theoretical controls. `paragraph-compact` had no authored consumer, so this
  pass removes it from `theme.json` and CSS aliases.
- Keep recently tuned drawer motion component-owned. The drawer still uses its
  `100ms ease-out` behavior, but it is named through navigation private custom
  properties rather than repeated literal values.

## Execution Plan

### 1. Generate A Value Inventory

- Scan authored CSS outside `styles/dist`.
- Bucket raw values by property family:
  - layout;
  - spacing;
  - typography;
  - rhythm/line-height;
  - motion;
  - vendor/runtime.
- Count repeated values but do not treat count alone as priority.

### 2. Classify Layout Primitives

- Keep `contentSize` and `wideSize` as WordPress-native layout owners.
- Keep the third frame tier because source review shows `.pns-section-frame`,
  `.pns-section-inner`, footer, Shop, and split-section frame math depend on a
  wider-than-`wideSize` tier.
- Rename the helper from `--pns--layout--site-frame-size` to
  `--pns--layout--section-frame-size` so the name matches its actual ownership.
- Point repeated page-level rules to the chosen layout primitives.
- Leave component geometry local and documented.

### 3. Classify Rhythm And Typography

- Verify which line-height values already map to `theme.json` custom tokens.
- Replace repeated raw line-height values where they clearly map.
- Keep quote/display/card exceptions private when the component owns them.
- Preserve `rlh` / `lh` for prose and block-composition rhythm unless focused
  testing proves a specific value is wrong.
- Remove `--pns--content-rhythm--heading-margin-block-end` unless a later pass
  proves a concrete heading-rhythm selector needs it.
- Find heading fallback sizes and repeated title sizes.
- Replace with existing font-size presets where visually acceptable.

### 4. Classify Hard-Coded Spacing

- Replace repeated cross-surface values with public spacing presets when they
  match the ladder.
- For near-matches, decide whether to preserve the exact value privately or
  intentionally normalize to the ladder.
- Route button/form spacing to the control primitive plan.
- Route page-level layout spacing to this plan.
- Add comments for retained private component geometry.

### 5. Decide Motion Token Ownership

- Verify all interaction/page-reveal transitions consume the shared motion
  values.
- Name retained component-specific motion values near their owner when forcing
  them into the global interaction/page-reveal values would change tuned
  behavior.
- Decide whether motion remains in `styles/shared/settings.css` or moves to
  `theme.json` custom tokens.
- Keep reduced-motion behavior unchanged.
- Do not create a broad new motion project unless this audit proves one is
  needed.

### 6. Update Tests And Style Guide

- Keep or add visual/computed checks for:
  - content width;
  - alignwide width;
  - paragraph baseline rhythm;
  - heading scale defaults;
  - representative line-height values;
  - reduced-motion-safe behavior where touched.
- Update the private style guide with the final public/private/vendor
  classification counts.

## Validation

```bash
php app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-block-templates.php
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone analyze:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
```

Run the lean `test:visual` gate before closing broad hard-coded value cleanup.

## Done When

- Page-level layout primitives have one documented owner.
- Baseline rhythm remains centralized and covered.
- `rlh` / `lh` remain documented as the accepted internal rhythm mechanism.
- `--pns--content-rhythm--heading-margin-block-end` is removed as unused.
- Repeated line-height and heading fallback values map to public tokens or
  documented private component rhythm.
- Repeated cross-surface spacing values either use the spacing ladder or have a
  documented reason not to.
- Global motion values are named once and consumed consistently.
- Private component geometry is documented near its owner.
- The style guide reflects the final public/private/vendor split.
