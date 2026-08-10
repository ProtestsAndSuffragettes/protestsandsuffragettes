# Design Token Consolidation Plan

Created on 2026-06-24.

All paths are relative to the project root.

## Goal

Formalize the emerging Protests and Suffragettes design rhythm before the
standalone theme hardens around inherited one-off values.

The desired outcome is not a generic design-token framework. The goal is a small
source-of-truth map that tells future theme work where typography, spacing,
layout, component, and vendor-exception values belong.

## Current State

The design system is already partly centralized, but it is split across
WordPress-supported `theme.json` settings, shared CSS aliases, base element
rules, component CSS, block CSS, page-type CSS, vendor overrides, and in-progress
standalone-theme work.

Runtime state must be verified before any implementation slice. The accepted
baseline for this consolidation work is
`app/public/wp-content/themes/protestsandsuffragettes-standalone/` before token
cleanup began on 2026-06-24. Confirm active `stylesheet`, `template`, and
`wp_global_styles` records with WP-CLI before editing; the older child theme is
historical reference only for this work.

### Existing Central Owners

| Concern             | Current owner                                                                | Current values                                                                                                                     | Notes                                                                                                        |
| ------------------- | ---------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------ |
| Color palette       | `app/public/wp-content/themes/protestsandsuffragettes/theme.json` lines 6-38 | purple background, white foreground, red, mint, grey tones                                                                         | Correct broad owner. Palette should stay in `theme.json` unless a value is private to a component.           |
| Layout widths       | `theme.json` lines 46-49 and `styles/shared/settings.css` line 7             | `contentSize: 44rem`, wide size via `--pns--layout--wide-breakout-size: calc(50vw + 40rem)`                                        | Keep `contentSize` and `wideSize` in `theme.json`; keep only the custom math helper in shared settings.      |
| Font families       | `theme.json` lines 70-96 and `styles/shared/settings.css` lines 2-6          | Libre Franklin and Rubik, plus compatibility aliases                                                                               | Intended owner is `theme.json`. Shared CSS currently patches legacy aliases and should be reduced over time. |
| Font-size presets   | `theme.json` lines 50-69                                                     | `medium`, `large`, `x-large`                                                                                                       | Correct owner, but the scale needs clearer names and maybe a body/small/nav decision.                        |
| Global block gap    | `theme.json` lines 129-136                                                   | `blockGap: 0` and global margin reset                                                                                              | Correct owner. Visible rhythm should be opt-in at block, component, page-type, or utility level.             |
| Core block defaults | `theme.json` lines 181-284                                                   | columns, social links, categories, button, heading, spacer, separator, navigation, site title/tagline, paragraph, query pagination | Keep supported block defaults here, but move unsupported visual mechanics to block/component CSS.            |

### Existing Distributed Owners

| Concern                                | Current owner                                                                                                                                                                    | Current values                                                                                                                                        | Consolidation problem                                                                                                                                             |
| -------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Body typography                        | `theme.json` lines 138-143; `styles/base/elements.css` lines 19-23                                                                                                               | `theme.json` says body `1rem`/`1.6`; child base CSS forces paragraph line-height `1.26666667`; standalone `theme.json` currently says body `0.875rem` | This is the most important unresolved decision. Pick one body contract and make base CSS stop contradicting it.                                                   |
| Display headings                       | `theme.json` lines 150-179 and 216-221; `styles/base/elements.css` lines 30-46                                                                                                   | Rubik, weight `800`, uppercase, `clamp(1.5rem, 4vw, 45px)` for h2/h3/large, `clamp(2rem, 4vw, 56px)` for x-large                                      | Family, weight, and line-height belong in `theme.json`; uppercase treatment may stay in base CSS if WordPress cannot express it cleanly for all heading elements. |
| Tight content rhythm                   | child component/page CSS; standalone `styles/page-types/content-rhythm.css` lines 1-40                                                                                           | `0.625rem` paragraph bottoms, `1.25rem` heading bottoms, exceptions for graphic/template-composed routes                                              | This should become a named page-type/content owner, not a global block-gap value. The standalone file is the right direction.                                     |
| Gutters                                | `styles/layout/index.css` lines 5-12; `styles/components/shop-intro.css` lines 1-25; `styles/components/footer-layout.css` lines 9-20; `styles/utilities/index.css` lines 99-103 | mobile `1rem`, wider `2rem`, repeated max-inline-size calculation                                                                                     | The gutter tokens should be named in `theme.json` custom spacing or shared settings; the application stays in layout/component CSS.                               |
| Buttons                                | `theme.json` lines 203-214; `styles/components/buttons.css` lines 1-153                                                                                                          | block button type/radius in `theme.json`; component mechanics use `10px`, `20px 30px`, `1rem`, `1.25rem`, `2.4375rem`, `0.25rem` shadow/hover offset  | Button shape, color, font, and basic padding should be centralized. Interactive mechanics and previous/next layout stay in component CSS.                         |
| Navigation                             | `theme.json` lines 242-249; `styles/blocks/core-navigation.css` lines 10-159; `styles/blocks/core-navigation-frontend.css` lines 1-18                                            | `theme.json` says nav `1.25rem`; CSS uses `1rem`, `0.8125rem`, `1rem` gaps, `166px`, `2.8125rem`, `2em`, submenu priority padding                     | Treat as a component/block exception until header markup is stabilized. Do not make nav magic offsets global tokens.                                              |
| Standalone template-part inline values | `protestsandsuffragettes-standalone/parts/header.html` lines 1-5; `parts/footer.html` lines 1-65                                                                                 | preset padding, custom spacing, nav `blockGap: 1.6em`, three `33%` footer columns, social `8px` gap, footer background `#170145`                      | Structural block composition can stay in parts, but repeated colors/spacing should use presets before further token cleanup.                                      |
| Database global styles                 | `wp_global_styles` records discovered by WP-CLI                                                                                                                                  | child global styles may carry typography overrides; standalone global styles may be empty or divergent                                                | Must be reconciled before declaring filesystem `theme.json` the only source of truth.                                                                             |
| Vendor surfaces                        | `styles/vendor-overrides/ecwid.css` lines 1-30; `styles/vendor-overrides/emailoctopus.css` lines 1-84                                                                            | Ecwid heading type, product title size, EmailOctopus form spacing/focus/field padding                                                                 | Keep in vendor overrides. They may reference global tokens, but vendor-specific selectors and priorities must not move into `theme.json`.                         |

## Recommended Source Of Truth

### 1. `theme.json`: Public WordPress Design Contract

Use `theme.json` for values that WordPress, the Site Editor, block supports, and
block themes can understand.

Move or keep these here:

- Palette presets.
- Font family presets: `libre-franklin`, `rubik`.
- Font size presets.
- Line-height custom values for body and display copy.
- Spacing presets that authors may choose intentionally.
- `contentSize` and `wideSize`.
- Global `blockGap: 0`.
- Supported defaults for core blocks: heading, paragraph, button, separator,
  categories, social links, site title, site tagline.

Do not use `theme.json` for:

- Header/nav compensation offsets.
- Vendor plugin selectors.
- Page-specific rhythm exceptions.
- CSS-only interaction effects such as button shadows and hover movement.
- Old compatibility aliases once markup and generated styles stop needing them.

### 2. `styles/shared/settings.css`: Private CSS Tokens And Compatibility

Use `settings.css` for CSS variables that cannot live cleanly in `theme.json` or
that are private implementation helpers.

Keep:

- `--pns--layout--wide-breakout-size`.
- Temporary compatibility aliases while migrated content or generated styles
  still reference old slugs.

Add only if needed:

- Private component-neutral helpers such as `--pns--button--shadow-offset`.
- Private layout helpers that are not useful in the Site Editor.

Do not keep long-term:

- `--wp--preset--font-family--dm-sans` as the normal way to mean Libre Franklin.
- `--wp--preset--font-family--marcellus` as the normal way to mean Rubik.
- Duplicates of values already available through `theme.json` presets.

### 3. `styles/base/`: Plain Element Defaults Only

Use base CSS for plain HTML behavior that WordPress cannot express well or that
must apply outside block markup.

Keep:

- Margin resets for headings, paragraphs, lists.
- Plain heading uppercase treatment if it remains a site-wide element rule.
- The current horizontal-overflow guard until the Edu Giveaway source issue is
  fixed.

Move out or remove:

- Paragraph line-height values that contradict `theme.json`.
- Duplicated heading scale values once `theme.json` and block styles fully own
  them.

### 4. `styles/layout/`: Site Layout Primitives

Use layout CSS for application of the width and gutter system:

- `.alignwide` width behavior.
- Mobile `1rem` gutters.
- Wide `2rem` gutters.
- Ordinary flow list indentation while it remains a site-wide default.

Layout CSS should use named central values, not invent new spacing values.

### 5. `styles/blocks/`: True Block Defaults

Use block CSS when the value describes how a WordPress block should look in this
theme and cannot be fully expressed in `theme.json`.

Good owners:

- Quote padding and quote typography.
- Social-link sizing and generated-list cleanup.
- Separator height or cross-browser details.
- Navigation only for true `core/navigation` behavior.

Avoid:

- Page composition.
- Header-specific layout compensation.
- Database-template-specific spacing.

### 6. `styles/components/`: Reusable Theme Components

Use component CSS for reusable site UI that has behavior beyond tokens:

- Buttons and hover mechanics.
- Footer/contact layout rhythm.
- Logo placement.
- Shop intro layout.

Components should consume centralized tokens where practical, but their
component-specific mechanics should remain local.

### 7. `styles/page-types/`: Editorial Rhythm And Route Families

Use page-type CSS for content-family rhythm:

- Herstories biography layout.
- Shop page composition.
- Education/EmailOctopus landing composition when the layout is route-specific.
- The standalone `content-rhythm.css` pattern for ordinary content pages/posts.

This is the right place for the question: "What does normal body content do
between headings and paragraphs?" It is not the same as global block gap.

### 8. `styles/vendor-overrides/`: Plugin And Hosted Output

Use vendor overrides only after checking plugin or hosted-service settings.

Vendor CSS can reference theme tokens, for example Rubik heading type or field
rhythm, but selectors, hydration overrides, and `!important` exceptions stay
scoped here.

## Proposed Token Contract

These are the values worth formalizing first.

### Typography

| Token             | Value                                                       | Canonical owner                               | Current evidence                                                   |
| ----------------- | ----------------------------------------------------------- | --------------------------------------------- | ------------------------------------------------------------------ |
| Body family       | `var(--wp--preset--font-family--libre-franklin)`            | `theme.json`                                  | `theme.json` lines 86-95; `settings.css` lines 5-6                 |
| Display family    | `var(--wp--preset--font-family--rubik)`                     | `theme.json`                                  | `theme.json` lines 86-95; heading block lines 216-221              |
| Body size         | Decide between `1rem` and the standalone spike's `0.875rem` | `theme.json`                                  | child `theme.json` line 139; standalone `theme.json` line 139      |
| Body line-height  | Decide between `1.6` and current paragraph `1.26666667`     | `theme.json`; base must agree                 | `theme.json` lines 113-117 and 269-273; `elements.css` lines 19-23 |
| UI/nav size       | `0.8125rem` small, `1rem` desktop                           | component/block CSS, maybe custom token later | `core-navigation-frontend.css` lines 10-18                         |
| Medium text       | `clamp(1rem, 2vw, 20px)`                                    | `theme.json` font-size preset                 | `theme.json` lines 53-58                                           |
| Display text      | `clamp(1.5rem, 4vw, 45px)`                                  | `theme.json` font-size preset                 | `theme.json` lines 59-63                                           |
| Hero/display text | `clamp(2rem, 4vw, 56px)`                                    | `theme.json` font-size preset                 | `theme.json` lines 64-68                                           |
| Body weight       | `400`                                                       | `theme.json`                                  | `theme.json` lines 138-143                                         |
| UI/button weight  | `600`                                                       | component CSS or custom token                 | `buttons.css` lines 35-57; footer heading line 1                   |
| Display weight    | `800`                                                       | `theme.json`                                  | `theme.json` lines 216-221; `elements.css` lines 30-36             |

### Spacing And Layout

| Token            | Value                                               | Canonical owner                                                             | Current evidence                                   |
| ---------------- | --------------------------------------------------- | --------------------------------------------------------------------------- | -------------------------------------------------- |
| Gap none         | `0`                                                 | `theme.json` global block gap; local CSS for explicit opt-outs              | `theme.json` lines 129-136                         |
| Space 2xs        | `0.25rem`                                           | `theme.json` custom spacing if used by authors; otherwise private CSS token | button hover/shadow offset lines 69-70 and 125-126 |
| Space xs         | `0.5rem`                                            | `theme.json` custom spacing                                                 | `theme.json` line 100; nav item padding line 24    |
| Space compact    | `0.625rem`                                          | `theme.json` custom spacing or page-type token                              | shop/footer/content rhythm lines 12-14 and 41-43   |
| Space sm         | `1rem`                                              | `theme.json` custom spacing                                                 | `theme.json` line 101; gutters and utilities       |
| Space md         | `1.5rem`                                            | `theme.json` custom spacing                                                 | `theme.json` line 102; footer/contact/shop gaps    |
| Space lg         | `2rem`                                              | `theme.json` custom spacing                                                 | `theme.json` line 103; desktop gutters             |
| Content width    | `44rem`                                             | `theme.json` layout                                                         | `theme.json` lines 46-49                           |
| Intro text width | `40rem`                                             | layout or utility token if reused                                           | `styles/utilities/index.css` lines 105-107         |
| Wide breakout    | `min(100%, var(--pns--layout--wide-breakout-size))` | `theme.json` + shared CSS helper                                            | `theme.json` line 48; `settings.css` line 7        |

## Consolidation Phases

### Phase 0: Freeze The Inventory

Purpose: prevent token cleanup from racing the standalone migration.

Actions:

1. Confirm which theme directory is the active implementation target for the next
   slice: existing child theme or `protestsandsuffragettes-standalone`.
2. Record current dirty standalone work before editing token files.
3. Re-run the source inventory for:

   ```text
   theme.json
   parts/header.html
   parts/footer.html
   styles/shared/settings.css
   styles/base/elements.css
   styles/layout/index.css
   styles/blocks/
   styles/components/
   styles/page-types/
   styles/vendor-overrides/
   ```

4. Verify runtime ownership before changing source-of-truth files:

   ```bash
   wp option get stylesheet
   wp option get template
   wp post list --post_type=wp_global_styles --post_status=any --fields=ID,post_name,post_status --format=table
   wp post term list <global-styles-record-id> wp_theme --format=json
   ```

Exit criteria:

- The working branch has one intended token-consolidation target.
- Existing unrelated standalone changes are preserved.
- Active filesystem theme and database global-style ownership are known.

### Phase 1: Establish The Token Contract

Purpose: define names and ownership before changing rendered behavior.

Actions:

1. Decide the body type contract:
   - child baseline: `1rem` body size and current paragraph line-height
     `1.26666667`;
   - standalone spike: `0.875rem` body size and `1.6` token line-height;
   - or a third explicit choice after visual review.
2. Rename font-size presets if needed so they describe use, not only size:
   `body`, `medium`, `display`, `display-large` or similar.
3. Expand `theme.json.settings.custom.spacing` only enough to represent the
   recurring scale:
   `0.25rem`, `0.5rem`, `0.625rem`, `1rem`, `1.25rem`, `1.5rem`, `2rem`.
4. Document compatibility aliases in `settings.css` with a removal condition.

Exit criteria:

- `theme.json` can answer: font families, type scale, body/display
  line-heights, spacing scale, content width, wide width, block gap.
- `settings.css` contains only private helpers and clearly temporary aliases.

### Phase 2: Remove Contradictory Global Rules

Purpose: make the central contract trustworthy.

Actions:

1. Update base paragraph line-height to agree with the chosen body contract.
2. Keep base margin resets, but move visible paragraph/heading spacing into the
   standalone-style content rhythm owner.
3. Stop using `--wp--preset--font-family--dm-sans` as the normal body family in
   `theme.json`; point body text at Libre Franklin directly.
4. Stop using `--wp--preset--font-family--marcellus` as the normal display
   family once no generated or content CSS still depends on it.

Exit criteria:

- There is no conflict between `theme.json` body typography and base paragraph
  CSS.
- Compatibility aliases are either gone or documented as temporary.

### Phase 3: Consolidate Gutters And Content Rhythm

Purpose: standardize spacing without reintroducing broad automatic block gaps.

Actions:

1. Keep global `blockGap: 0`.
2. Use layout CSS to apply the central gutter values:
   - `1rem` on narrow screens;
   - `2rem` on wider screens.
3. Use `page-types/content-rhythm.css` or equivalent for ordinary editorial
   pages/posts:
   - heading bottom rhythm;
   - paragraph bottom rhythm;
   - exceptions for graphic/template-composed routes.
4. Replace repeated literal gutter values in footer/shop/layout utilities only
   after Playwright proves no drift.

Exit criteria:

- Normal content pages have readable default rhythm.
- Graphic landing pages keep their dense composed bands.
- Gutter values are named or directly traceable to the central scale.

### Phase 4: Consolidate Button Primitives

Purpose: make buttons use the central system while keeping the visual character.

Actions:

1. Keep core button support values in `theme.json` where WordPress supports them:
   radius, text/background defaults, font size, line-height.
2. Move button padding to token-derived values only after comparing current
   `10px 10px` and `20px 30px` against rem alternatives.
3. Keep hover shadow, hover offset, and previous/next layout in
   `styles/components/buttons.css`.
4. Remove duplicate or dead button declarations such as the competing
   `margin-block-end` values only after visual tests cover affected button
   groups.

Exit criteria:

- Button font, color, basic shape, and spacing are traceable.
- Button interaction mechanics remain locally owned by the component.

### Phase 5: Treat Navigation As A Separate Hardening Slice

Purpose: avoid polluting the token system with header-specific compensation.

Status update: detailed navigation and header-placement work is now covered by
`docs/jobs/2026-06-22-navigation-behavior-plan.md` and
`docs/jobs/2026-06-22-cross-site-banner-cta-plan.md`. This design-token plan no
longer owns the nav implementation path; it only records which nav values should
not become global tokens.

Actions:

1. Do not promote nav values like `166px`, `2.8125rem`, `10px`, `25px`, or `2em`
   into global tokens.
2. Decide whether header navigation is a block default, a header component, or a
   database-template artifact in the standalone theme.
3. Reconcile inline template-part navigation values such as header/footer
   `blockGap: 1.6em` with the selected nav rhythm before changing nav CSS.
4. Standardize only the stable primitives:
   - nav text size;
   - nav text weight;
   - basic submenu padding;
   - mobile drawer padding.
5. Replace layout compensation only after header markup/template ownership is
   file-owned and stable.

Exit criteria:

- Navigation no longer contradicts `theme.json` font sizing accidentally.
- Remaining nav magic numbers are documented as component-specific or removed.

### Phase 6: Keep Vendor Overrides Scoped

Purpose: let plugins consume the design language without becoming the design
source of truth.

Actions:

1. Update Ecwid overrides to reference the Rubik/Libre Franklin/font-size tokens
   where possible. Ecwid styling remains intentionally visible in theme code; do
   not move these decisions into the Ecwid UI.
2. Update EmailOctopus field and submit-button values in theme code and document
   the standalone theme as the owner of visible embed cleanup.
3. Keep vendor `!important` exceptions next to the vendor selector with the
   documented reason.

Exit criteria:

- Vendor typography visually matches the theme.
- Vendor selectors and priority rules remain isolated in `vendor-overrides/`.
- EmailOctopus and Ecwid styling decisions are documented as theme-owned when
  they must beat hosted/runtime CSS.

### Phase 7: Normalize Template-Part Inline Values

Purpose: keep block template parts readable while reducing hidden token drift.

Actions:

1. Leave structural block composition in `parts/header.html` and
   `parts/footer.html`.
2. Replace repeated inline values with presets when a matching preset exists:
   - preset padding instead of hard-coded custom padding;
   - preset color instead of custom footer background `#170145`, if it is a real
     brand color;
   - spacing preset instead of social `8px` gap, if the visual survives.
3. Do not replace structural values such as footer `33%` columns unless the
   layout itself is being changed.

Exit criteria:

- Template parts use presets for design decisions where practical.
- Structural composition remains explicit in the template files.

## Validation Gates

Every consolidation slice that changes CSS or `theme.json` should run:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes test:visual
```

For standalone-theme slices, run the same commands in:

```text
app/public/wp-content/themes/protestsandsuffragettes-standalone
```

Use local Playwright routes and visual references:

- `/`
- `/herstories/mary-barbour/`
- `/edu-giveaway/`
- `/shop/`
- mobile nav-open state

Classify each diff as:

- `accept`: intentional simplification that still matches the design language;
- `token-fix`: the central value is close but wrong;
- `component-exception`: the surface needs local ownership;
- `legacy-bug`: existing CSS was compensating for markup, DB state, or plugin
  output.

## Implementation Log

### 2026-06-24: Slice 1, Standalone Token Contract

Accepted baseline:

- `protestsandsuffragettes-standalone` before token cleanup began.
- WP-CLI verified active `stylesheet` and `template` as
  `protestsandsuffragettes-standalone`.
- Active standalone `wp_global_styles` record `5256` contains no design override
  beyond global-styles metadata, so filesystem `theme.json` is the source of
  truth for this slice.

Changes:

- Pointed global body typography at the declared Libre Franklin preset instead
  of the inherited `dm-sans` alias.
- Added missing custom spacing values for the recurring scale:
  `0.25rem`, `0.625rem`, and `1.25rem`, while making `1.5rem` and `2rem`
  direct token values.
- Added custom color tokens for the existing distributed purple/mint/deep-purple
  values.
- Kept legacy CSS aliases in `settings.css`, but pointed active brand/accent
  aliases at the new PNS color tokens.
- Renamed the `tertiary` palette label from `Foreground` to `Mint`.
- Aligned the custom button radius token with the actual square button design.

Validation:

- `theme.json` parsed successfully.
- Direct Lightning CSS frontend and editor compiles passed.
- Prettier passed for touched files.
- Stylelint passed for `styles/shared/settings.css`.
- Elevated Playwright visual suite: 38 passed, 1 desktop home snapshot failed
  because the homepage screenshot did not stabilize around the Wikipedia image
  load; all computed-style contracts passed.

### 2026-06-24: Slice 2, Tokenized Application Owners

Changes:

- Added private `--pns--...` helpers in `styles/shared/settings.css` for the
  site max-inline-size calculation, compact/display line-heights, button
  mechanics, and form-field padding.
- Moved base paragraph line-height to the public `theme.json` paragraph rhythm
  and kept compact line-height as a private helper for true exceptions.
- Tokenized repeated gutters and rhythm values in layout, footer, shop intro,
  utility, quote, button, Ecwid, EmailOctopus, and Herstories styles.
- Kept template structural values local where tokenizing them changed visual
  output: footer social `8px`, footer deep-purple inline background, and nav
  `1.6em` gaps.
- Exposed the distributed brand purple, accent mint, and deep purple values as
  palette entries while retaining their custom-color aliases for CSS.
- Left the legacy `dm-sans`, `marcellus`, uppercase `Rubik`, `--brandcolor`,
  and `--altcolor` aliases in `settings.css` as compatibility shims, not as new
  source-of-truth names.

Validation:

- `theme.json` parsed successfully.
- Direct Lightning CSS frontend and editor compiles passed.
- Prettier passed for touched theme files and this plan.
- Stylelint passed for `styles/**/*.css`.
- Focused Playwright checks passed after scoping exceptions:
  - homepage cascade contracts passed across desktop, tablet, and mobile;
  - Mary Barbour desktop snapshot passed;
  - Giveaway form cascade contracts passed across desktop, tablet, and mobile.
- Full elevated Playwright visual suite: 36 passed, 3 Giveaway snapshots failed
  because the `/edu-giveaway/` hero background image loaded in the actual
  screenshots while the stored baselines show the same hero region as flat
  purple. The failures are isolated to the full-page Giveaway snapshots; all
  computed-style contracts passed.

Follow-up correction:

- The Giveaway page should not be treated as a compact-rhythm exception. A
  production comparison showed the accepted Giveaway copy rhythm uses the
  editorial defaults: heading bottom rhythm `20px`, paragraph bottom rhythm
  `10px`, and paragraph line-height `1.6`. The standalone route exception was
  removed so `/edu-giveaway/` inherits the default content rhythm again.

### 2026-06-24: Slice 3, Navigation Ownership And Value Inventory

Requested scope:

- Complete the first two navigation-hardening steps before larger nav-system
  work begins:
  1. confirm navigation ownership across filesystem, database, and rendered
     markup;
  2. inventory the nav-specific values and decide what is safe to centralize.

Ownership findings:

- WP-CLI verified the active `stylesheet` and `template` as
  `protestsandsuffragettes-standalone`.
- Header navigation content is database-owned by `wp_navigation` record `1035`
  (`Top Nav`). Footer navigation content is database-owned by `wp_navigation`
  record `1032` (`Navigation`).
- Header template composition is database-owned by `wp_template_part` record
  `1027` (`Header`). Footer template composition is database-owned by
  `wp_template_part` record `1026` (`Footer`).
- Filesystem parts mirror the same core navigation references:
  - `parts/header.html` references nav `1035` with inline `blockGap: 1.6em`;
  - `parts/footer.html` references nav `1032` with inline `blockGap: 1.6em`,
    `fontSize: small`, and vertical layout.
- CSS ownership is split across:
  - `theme.json` `core/navigation`, currently declaring `1.25rem` nav type and
    primary text color;
  - `styles/blocks/core-navigation.css`, registered as the `core/navigation`
    block style and holding legacy/header/drawer mechanics;
  - `styles/blocks/core-navigation-frontend.css`, holding current frontend nav
    primitives;
  - `styles/components/footer-layout.css`, holding footer nav typography.
- The Playwright visual contracts encode the current rendered behavior for
  header nav, footer nav, submenu padding, and the mobile drawer. Treat those as
  the regression gate for any future nav cleanup.

Rendered behavior snapshot:

| Surface           | Current rendered values                                                                                                                                               | Notes                                                                                                              |
| ----------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------ |
| Header nav        | flex layout, `gap: 0`, Libre Franklin, `1rem` desktop/tablet links, `0.8125rem` mobile links, `font-variation-settings: "wght" 600`, line-height `1`                  | CSS overrides the `theme.json` `1.25rem` nav size.                                                                 |
| Header spacing    | source template says `blockGap: 1.6em`; CSS forces `.wp-block-navigation.is-layout-flex { gap: 0; }`; top-level item padding becomes `0.8125rem` and then `2.8125rem` | The rendered rhythm is currently CSS-owned, not template-owned.                                                    |
| Header hover      | red underline bar, `10px` high, `28px` wide, offset `-0.8125rem`; submenu icon stroke is mint                                                                         | Visual treatment is component-specific and should not become a global token.                                       |
| Submenu items     | submenu anchor padding `0.5rem`; submenu item border-inline-end `10px` transparent/red on hover                                                                       | `0.5rem` can use the central spacing scale; the border behavior should stay local to nav.                          |
| Footer nav        | vertical nav, `1rem` links, line-height `1.6`, `font-variation-settings: "wght" 600`, foreground text                                                                 | Footer typography is stable and can consume shared UI-type primitives.                                             |
| Mobile drawer     | open-button icon fill red; drawer item border-inline-end `6px`; drawer item padding/margin inline-end `10px`; nested item padding `25px`, margin `-12px`              | These values describe the current drawer mechanics. Keep local until the drawer markup and interaction are stable. |
| Generated classes | stale-looking selectors include `.wp-container-2` and `nav.wp-container-core-navigation-is-layout-808e6b47`; current rendered header class differs                    | Do not standardize generated class selectors. Replace or delete only after a focused rendered-markup check.        |

Centralization decision:

- Centralize stable nav primitives only:
  - UI/nav type sizes: `0.8125rem` mobile and `1rem` desktop;
  - UI/nav weight: `600`;
  - submenu link padding: `0.5rem`;
  - footer nav line-height: `1.6`.
- Do not centralize layout compensation or generated-class values:
  - `166px`;
  - `2.8125rem`;
  - `2em`;
  - `25px`;
  - `-12px`;
  - `.wp-container-*` selectors.
- Resolve the `theme.json` contradiction before further nav work. Either make
  `theme.json` truthfully declare the rendered nav font size, or remove the
  block-level nav font-size declaration and let the header/footer component CSS
  own nav typography.
- Before major nav-system work, choose a template ownership model:
  1. export/sync the saved `Header`, `Footer`, `Top Nav`, and `Navigation`
     records into the standalone theme and make files the primary source; or
  2. keep database ownership explicit and treat filesystem parts as fallback
     scaffolding only.

Navigation handoff:

- Continue nav implementation from
  `docs/jobs/2026-06-22-navigation-behavior-plan.md`.
- Treat header placement and the cross-site CTA relationship as covered by
  `docs/jobs/2026-06-22-cross-site-banner-cta-plan.md`.
- This token plan should only be updated again if nav values are promoted into
  the shared token vocabulary.

### 2026-06-24: Slice 4, Remaining Token Cleanup Closeout

Requested scope:

- Mark detailed nav/header work as owned by
  `docs/jobs/2026-06-22-navigation-behavior-plan.md` and
  `docs/jobs/2026-06-22-cross-site-banner-cta-plan.md`.
- Complete the remaining filesystem template-part inline-value cleanup from
  Phase 7.
- Complete the EmailOctopus vendor override pass.
- Keep Ecwid styling in theme code, not Ecwid UI configuration.
- Remove remaining compatibility aliases where theme code no longer needs them.
- Refresh the local `/edu-giveaway/` visual snapshot with a longer settle window
  for animations/assets.

Changes:

- Removed the standalone filesystem header/footer nav `blockGap: 1.6em` inline
  values. Nav behavior and future header placement are now tracked in the nav
  and cross-site banner plans instead of this token plan.
- Replaced the footer social links `8px` inline gap with the existing
  `var:preset|spacing|20` spacing preset.
- Replaced the footer deep-purple inline background with the `deep-purple`
  palette preset.
- Removed `settings.css` compatibility aliases for `dm-sans`, `marcellus`,
  uppercase `Rubik`, `--brandcolor`, `--altcolor`, and `--white`.
- Updated button CSS to use the PNS brand and accent variables directly instead
  of the removed `--brandcolor` and `--altcolor` aliases.
- Converted EmailOctopus TODOs into explicit theme-owned override comments and
  replaced hosted field variables with theme-owned field color, border, and size
  values.
- Kept Ecwid overrides in `styles/vendor-overrides/ecwid.css` and documented
  that these storefront styles intentionally live in code rather than Ecwid UI
  settings.
- Added an explicit `/edu-giveaway/` snapshot settle delay and longer screenshot
  assertion timeout before refreshing the local baselines.

Remaining caveat:

- The filesystem template parts are now cleaner, but the live header/footer
  template parts are still database-backed. Any release that depends on these
  filesystem changes must export, sync, or intentionally clear the saved
  `wp_template_part` records first.

## Answer To The Centralization Question

Yes, the durable values should be centralized, but not all in one file.

Use `theme.json` as the public WordPress design contract. Use
`styles/shared/settings.css` only for private CSS helpers and temporary
compatibility aliases. Keep application of those values close to the surface:
layout in `styles/layout/`, block behavior in `styles/blocks/`, reusable UI in
`styles/components/`, editorial rhythm in `styles/page-types/`, and plugin
exceptions in `styles/vendor-overrides/`.

The best consolidation target is therefore:

1. Centralize the vocabulary in `theme.json`.
2. Keep private implementation helpers in `settings.css`.
3. Apply the values through the narrowest owner that matches the rendered
   surface.
4. Promote only stable repeated values into tokens.
5. Leave one-off layout compensation local until the standalone templates and
   navigation are file-owned and visually proven.
