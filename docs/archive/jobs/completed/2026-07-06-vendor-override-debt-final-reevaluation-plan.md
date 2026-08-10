# Vendor Override Debt Final Re-Evaluation Plan

Plan started on 2026-07-06.

All paths are relative to the project root.

## Purpose

Run a final evidence-led review of remaining third-party/plugin override debt
before closing the standalone theme rationalization parent.

The goal is not to delete vendor CSS for its own sake. The goal is to identify
which remaining Ecwid, EmailOctopus, Jetpack, Messenger, and project-owned block
override rules are still genuinely required, which can now be simplified after
the surface/token/layout work, and which should become plugin/account settings
or explicit follow-up decisions.

## Parent Work

Parent plan:
`docs/jobs/2026-06-28-theme-json-css-structure-rationalization-plan.md`

Parent Dex: `511irrlz` - Rationalize standalone theme block controls and CSS
authority.

Dex: `52311232` - Final vendor override debt re-evaluation.

## Why This Exists

Several earlier tasks classified or reduced vendor debt:

- `hfux2nkg` fixed root overflow and classified vendor overflow/override debt.
- Light-surface work refactored Ecwid storefront overrides into a surface
  adapter where possible.
- `uzku688y` removed retired spacing-token dependencies from the project-owned
  Ecwid product-grid block.
- `docs/jobs/__completed/vendor-plugin-css-override-triage-plan.md` records the
  historical vendor/plugin override strategy and earlier attempted removals.

Those tasks were valid, but the system has changed since then. Palette,
spacing, layout, surface, and block-control ownership have now been rationalized
enough that some remaining vendor or block-specific overrides may be stale,
duplicative, or only preserving old drift.

This plan is the final re-check before handoff.

## In Scope

- Re-inventory authored source override debt in:
  - `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/vendor-overrides/`
  - `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/blocks/`
  - `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/components/`
  - `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/page-types/`
  - `app/public/wp-content/plugins/pns-blocks/blocks/commerce/ecwid-product-grid/`
- Recount active authored `!important` declarations, CSS variable fallbacks,
  and old-to-new compatibility aliases.
- Classify each remaining vendor/plugin override as one of:
  - plugin/account setting candidate,
  - enqueue/order issue,
  - unavoidable runtime CSS,
  - project-owned block component default,
  - stale/dead override,
  - cleanup candidate with low visual risk.
- Attempt cleanup only where the owner and rendered route evidence are clear.
- Keep cleanup batches small and independently revertible.

## Out Of Scope

- Broad redesign of Ecwid, EmailOctopus, Jetpack, Messenger, or store UI.
- Rewriting third-party plugins.
- Removing a documented override without proving the affected vendor output
  still renders correctly.
- Refreshing visual snapshots to hide vendor drift.
- Folding unrelated utility or template cleanup into this review.

## Initial Suspect Areas

Current quick scan after `uzku688y` found:

- Authored runtime old-to-new spacing bridges: `0` outside migration scripts.
- Legacy numeric spacing presets still exposed in `theme.json`: `20`-`80`.
- CSS variable alias declarations: approximately `69`.
- Authored CSS var fallback lines: approximately `45`.
- Authored `!important` lines: approximately `68`.
- Largest current `!important` concentrations:
  - `styles/vendor-overrides/ecwid.css`
  - `styles/vendor-overrides/emailoctopus.css`
  - `styles/components/cross-site-banner-cta.css`
  - `styles/components/footer-layout.css`
  - `styles/blocks/core-navigation.css`
  - `styles/components/split-section.css`
  - `styles/page-types/herstories-bios.css`

These numbers are a triage starting point, not acceptance criteria. Recount
from source at implementation time.

## Execution Order

1. Rebuild the current inventory.
   - Count authored `!important` declarations, `var()` fallbacks, vendor
     selectors, block-specific selectors, and compatibility aliases.
   - Exclude generated `styles/dist/`, sourcemaps, and plugin build output from
     source counts unless validating generated assets.

2. Map each override to a rendered owner.
   - Ecwid storefront and cart.
   - Project-owned `pns/ecwid-product-grid`.
   - EmailOctopus newsletter/giveaway embeds.
   - Jetpack slideshow/contact form output.
   - Messenger widget hiding.
   - Navigation/footer/split-section rules that are not third-party but are
     still override debt.

3. Re-test removability.
   - For each candidate, try the lowest-risk simplification first:
     - remove stale rule,
     - remove `!important`,
     - replace with stronger scoped selector,
     - move to block/component source,
     - replace with plugin/account setting,
     - document as unavoidable.
   - Do not combine unrelated vendor surfaces in one cleanup batch.

4. Validate each batch.
   - Run CSS compile/check for touched theme files.
   - Run plugin build/check for touched `pns-blocks` files.
   - Run targeted Playwright checks for the affected rendered surface.
   - Run the full visual suite before completing the task.

5. Record the final debt ledger.
   - Rules removed.
   - Rules retained and why.
   - Rules converted to non-important selectors.
   - Remaining plugin/account setting follow-ups, if any.
   - Updated counts.

## Test Plan

- `pnpm check` from
  `app/public/wp-content/themes/protestsandsuffragettes-standalone`.
- If `pns-blocks` changes:
  - `pnpm --dir app/public/wp-content/plugins/pns-blocks run build`
  - `pnpm --dir app/public/wp-content/plugins/pns-blocks run lint:css`
  - targeted Prettier checks for touched files.
- Focused Playwright coverage for affected surfaces:
  - homepage Ecwid product-grid.
  - `/shop/`.
  - `/shop/cart`.
  - representative Ecwid product detail page.
  - homepage EmailOctopus embed.
  - `/edu-giveaway/`.
  - Herstory pages with Jetpack slideshow/media sections.
  - header/navigation/footer affected viewports.
- Full Playwright visual suite before completion.

## Done When

- The current vendor/plugin/block override debt is freshly inventoried.
- Each remaining high-priority override is classified with owner and reason.
- Low-risk stale overrides are removed or simplified.
- Retained `!important` rules have current evidence, not only historical notes.
- Any plugin/account setting cleanup is split into a named follow-up if it
  cannot be completed locally.
- Final counts and validation evidence are recorded in this plan and Dex.
- `5koa559i` final closeout can run without hiding unreviewed vendor debt.

## Implementation Evidence - 2026-07-06

Dex: `52311232`.

### Fresh Inventory

Authored CSS source scanned under
`app/public/wp-content/themes/protestsandsuffragettes-standalone/styles`,
excluding `styles/dist`.

Current active authored `!important` declarations: `62`.

By file:

| File                                          | Active `!important` | Classification                                                                                            |
| --------------------------------------------- | ------------------- | --------------------------------------------------------------------------------------------------------- |
| `styles/vendor-overrides/ecwid.css`           | 31                  | Unavoidable runtime CSS for Ecwid storefront/cart/product UI.                                             |
| `styles/vendor-overrides/emailoctopus.css`    | 8                   | Hosted embed layout/focus/spacing cleanup; account/plugin-side follow-up only if design changes.          |
| `styles/components/cross-site-banner-cta.css` | 8                   | Project-owned navigation/banner component priority against core Navigation styles.                        |
| `styles/components/footer-layout.css`         | 5                   | Project-owned footer layout priority against serialized column basis styles.                              |
| `styles/components/split-section.css`         | 3                   | Jetpack slideshow sizing inside PNS edge-media split sections.                                            |
| `styles/page-types/herstories-bios.css`       | 2                   | Pattern-specific active-date compact measure, retained until source markup owns it.                       |
| `styles/blocks/core-navigation.css`           | 2                   | Documented WordPress core Navigation priority exceptions.                                                 |
| `styles/blocks/core-social-links.css`         | 1                   | Documented WordPress core Social Links priority exception.                                                |
| `styles/blocks/jetpack-slideshow.css`         | 1                   | Jetpack pagination hidden for current design; plugin setting should be checked before permanent deletion. |
| `styles/components/buttons.css`               | 1                   | PNS entry/query pagination gap owner against generated layout priority.                                   |

Other counts:

- Raw authored `!important` grep count: `65`, including three explanatory
  comment lines that intentionally mention documented exceptions.
- Single-line fallback scan found `7` authored `var()` fallbacks outside dist,
  plus `theme.json` layout fallback at `layout.wideSize`.
- Alias declaration scan found `69` custom-property aliases:
  `section-theme.css` `22`, `light-surface.css` `15`, `settings.css` `22`,
  `ecwid.css` `8`, and `core-navigation.css` `2`.
- Runtime old-to-new spacing bridges in authored CSS/theme.json: `0`.
- Retained numeric spacing preset slugs in `theme.json`: `20`, `30`, `40`,
  `50`, `60`, `70`, and `80`. These remain compatibility tokens for serialized
  saved content, not active CSS bridges.

### Cleanup Applied

Removed dead commented-out `!important` experiments from:

- `styles/components/buttons.css`
- `styles/components/cross-site-banner-cta.css`
- `styles/blocks/jetpack-slideshow.css`

No active CSS declarations were removed in this pass. Active removals were not
safe without risking accepted visual output: the remaining priority rules are
either vendor-runtime scoped, WordPress core priority exceptions, serialized
layout conflict guards, or project-owned fixed component contracts.

### Retained Debt Ledger

- Ecwid storefront/cart/product detail: retained. Current rendered checks prove
  the white-surface readability fix, minicart icon color, product-description
  text, and cart recommendation text still depend on the scoped adapter layer.
- `pns/ecwid-product-grid`: no theme-side runtime override debt remains for the
  plugin-owned grid contract; generic plugin validation is owned by `5e6hco3p`.
- EmailOctopus: retained. Rules are scoped to the hosted form id or
  `.emailoctopus-form` output and protect overflow, one-column layout, focus
  state, powered-by hiding, and contact-section alignment.
- Jetpack slideshow: retained. Pagination hiding remains an explicit design
  choice; slideshow sizing remains scoped to Jetpack and PNS split-section
  media contexts.
- Messenger: retained as `.ec-fbmessenger-chat { display: none !important; }`.
  This is a plugin/account-setting candidate if the store account can disable
  the injected widget, but local CSS is the current deterministic owner.
- Navigation/footer/banner: retained. These are not third-party vendor debt, but
  project-owned component contracts against core Navigation, Social Links, and
  serialized column styles.
- Split Section and Herstory active dates: retained. These are fixed PNS pattern
  contracts; future cleanup should happen through source markup/block settings,
  not silent priority removal.

### Validation

- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone run compile:css`
  passed after cleanup.
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone run lint:css`
  passed after cleanup.
- Focused Playwright vendor/surface coverage passed: `33 passed`.
  Covered desktop, tablet, and mobile for:
  - PNS dark-section copy/button inversion.
  - homepage cascade contracts.
  - homepage custom Ecwid product grid.
  - cross-site banner CTA.
  - Herstory cascade contracts.
  - giveaway form cascade contracts.
  - homepage EmailOctopus embed.
  - shop Ecwid cascade contracts.
  - Ecwid product detail white-surface readability.
  - Ecwid product description typography.
  - Ecwid cart recommendation readability.
- Full visual landing gate passed:
  `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone run test:visual`
  reported `235 passed`, `2 skipped`.
