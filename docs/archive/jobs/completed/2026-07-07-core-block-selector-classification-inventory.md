# Core Block Selector Classification Inventory

Created: 2026-07-07.

Related plan: `docs/jobs/2026-07-07-core-block-selector-classification-plan.md`

Dex:

- Parent: `mv15xqi8` - Classify core-block selector ownership before CSS
  hardening
- Cut 0: `h7fkwbp6` - Selector classification Cut 0 - refresh evidence and
  inventory broad selectors

## Scope

This is a scan-only inventory. No CSS behavior changes were made.

Candidate files:

- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/base/elements.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/layout/index.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/page-types/content-rhythm.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/components/section-theme.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/components/light-surface.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/blocks/core-navigation.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/blocks/core-navigation-base.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/blocks/core-navigation-desktop.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/blocks/core-navigation-core-drawer.css`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/blocks/core-cover.css`

Excluded:

- `h3rs0t00` / Herstories migration remains client-approval pending.
- No saved classes, spacing presets, palette slugs, or serialized block-support
  output were migrated.

## Refreshed Evidence

Working tree at the start of the scan contained only uncommitted plan docs from
the prior planning step.

Active WordPress state:

- Active theme: `protestsandsuffragettes-standalone`
- `stylesheet`: `protestsandsuffragettes-standalone`
- `template`: `protestsandsuffragettes-standalone`
- Active global styles row:
  `wp-global-styles-protestsandsuffragettes-standalone` (`ID 5256`)
- Active navigation rows include:
  - `pns-primary-navigation` (`ID 1035`)
  - `pns-footer-navigation` (`ID 1032`)
  - `pns-banner-cta-navigation` (`ID 5259`)
- DB-backed template and pattern rows are present for active templates,
  template parts, and synced blocks, so selector narrowing must treat saved
  classes and block-support output as DB-backed risk.

Load-path evidence:

- `styles/blocks/core-navigation.css` is a wrapper imported by
  `styles/blocks/index.css` and `styles/blocks/editor.css`.
- `core/navigation`, `core/cover`, and `core/quote` are intentionally bundled
  and block-registered through `styles/css-assets.json` and `inc/assets.php`.
- The navigation partials are private imports of `core-navigation.css`; they are
  not individually block-registered.

## Summary

| Area                          | Current owner decision                                 | Risk   | Recommended next action                                       |
| ----------------------------- | ------------------------------------------------------ | ------ | ------------------------------------------------------------- |
| Base element defaults         | Mostly real core/default theme typography              | Medium | Remain; split saved preset classes only with DB proof         |
| Layout shell and `.alignwide` | Site shell plus saved-content compatibility            | High   | Remain; keep `.alignwide` visual contracts                    |
| Content rhythm                | Page-type rhythm plus compatibility exclusions         | High   | Remain; harden in separate rhythm task                        |
| Section theme                 | PNS component/pattern surface                          | Medium | Remain; add comments/tests only if touched                    |
| Light surface                 | PNS template/surface contract                          | High   | Remain; keep tested                                           |
| Navigation base               | Broad core Navigation defaults for this theme          | Medium | Remain; later scope generic nav vs primary nav                |
| Navigation desktop            | Mixed primary-nav surface and generic submenu behavior | Medium | Follow-up: narrow hover/submenu selectors if tests prove safe |
| Navigation core drawer        | Primary Navigation core drawer adapter                 | Medium | Remain; do not reopen accepted drawer migration               |
| Core cover                    | One true core default plus PNS cover-section behavior  | Medium | Remain; comment/test before narrowing                         |

## Classification Table

| File/lines                                          | Selector group                                                    | Owner                                              | DB risk | Parity risk | Disposition                                                                 |
| --------------------------------------------------- | ----------------------------------------------------------------- | -------------------------------------------------- | ------- | ----------- | --------------------------------------------------------------------------- |
| `styles/base/elements.css:1`                        | `h1`-`h6` reset                                                   | real core block default                            | Medium  | Low         | Remain. Test if heading reset changes.                                      |
| `styles/base/elements.css:11`                       | `h1`-`h4` brand heading defaults                                  | real core block default                            | Medium  | Low         | Remain as theme heading default.                                            |
| `styles/base/elements.css:21`                       | `h2`, `h3`, `.has-large-font-size`                                | mixed core default and saved-content compatibility | High    | Medium      | Split follow-up: element defaults and saved preset class are mixed.         |
| `styles/base/elements.css:30`                       | `.has-x-large-font-size`                                          | saved-content compatibility                        | High    | Low         | Remain unless theme.json/generated preset output proves redundant.          |
| `styles/base/elements.css:36`                       | `.has-medium-font-size`                                           | saved-content compatibility                        | High    | Low         | Remain unless theme.json/generated preset output proves redundant.          |
| `styles/base/elements.css:41`                       | `p`                                                               | real core block default                            | Medium  | Low         | Remain. Add paragraph computed-style test only if changed.                  |
| `styles/base/elements.css:50`                       | `ul`, `ol` margin reset                                           | real core block default                            | Medium  | Medium      | Remain; controls depend on later rhythm exclusions.                         |
| `styles/layout/index.css:1`                         | Mobile `.alignwide`, `.alignwide.alignfull`                       | saved-content compatibility                        | High    | Medium      | Remain; existing `.alignwide` visual contracts are the right test home.     |
| `styles/layout/index.css:11`                        | `.alignwide` max width/margins                                    | saved-content compatibility                        | High    | Medium      | Remain; comment/test if changed.                                            |
| `styles/layout/index.css:16`                        | `html`, `body` min height                                         | component/pattern surface                          | Low     | Low         | Remain as site-shell base.                                                  |
| `styles/layout/index.css:21`                        | `.wp-site-blocks` flex shell                                      | component/pattern surface                          | Low     | High        | Remain; editor parity is explicit.                                          |
| `styles/layout/index.css:29`                        | `.wp-site-blocks > main`                                          | component/pattern surface                          | Low     | Medium      | Remain; already commented.                                                  |
| `styles/page-types/content-rhythm.css:1`            | Post/entry root block gap                                         | page-type rhythm                                   | High    | Medium      | Remain; test ordinary content stack before narrowing.                       |
| `styles/page-types/content-rhythm.css:5`            | Root child margin normalization                                   | page-type rhythm                                   | High    | Medium      | Remain; test before narrowing.                                              |
| `styles/page-types/content-rhythm.css:17`           | Nested generated flow reset                                       | saved-content compatibility                        | High    | High        | Remain with comment/test; depends on generated layout classes.              |
| `styles/page-types/content-rhythm.css:30`           | PNS sections/template parts/pattern QA reset                      | component/pattern surface                          | Medium  | Medium      | Split follow-up to component/pattern owners when practical.                 |
| `styles/page-types/content-rhythm.css:38`           | Editor root stack spacing                                         | page-type rhythm                                   | Low     | High        | Remain; editor parity contract.                                             |
| `styles/page-types/content-rhythm.css:50`           | Wrap reset for nav/social/pagination/buttons/search/vendor        | vendor/control exclusion                           | Medium  | High        | Remain; add/keep exclusion tests.                                           |
| `styles/page-types/content-rhythm.css:66`           | List rhythm plus exclusions                                       | page-type rhythm                                   | High    | High        | Remain; split only with replacement proof.                                  |
| `styles/page-types/content-rhythm.css:81`           | Nested list and `li + li` spacing                                 | page-type rhythm                                   | High    | High        | Remain with tests for nested lists and excluded controls.                   |
| `styles/page-types/content-rhythm.css:96`           | `li::marker`                                                      | page-type rhythm                                   | Medium  | High        | Remain; consider follow-up narrowing to real content lists.                 |
| `styles/page-types/content-rhythm.css:102`          | Margin/padding reset for controls/vendors                         | vendor/control exclusion                           | Medium  | High        | Remain; intentional control protection.                                     |
| `styles/page-types/content-rhythm.css:119`          | `.wp-block-quote p`                                               | real core block default                            | High    | High        | Split follow-up: duplicate owner exists in `styles/blocks/core-quote.css`.  |
| `styles/components/section-theme.css:5`             | `.pns-section:is(...)` surface variables                          | component/pattern surface                          | Medium  | Low         | Remain.                                                                     |
| `styles/components/section-theme.css:28`            | PNS section plus dark WP palette classes                          | component/pattern surface                          | Medium  | Low         | Remain; comment useful if touched.                                          |
| `styles/components/section-theme.css:53`            | PNS section root color with `:not(.has-text-color)`               | vendor/control exclusion                           | Medium  | Medium      | Remain; already test-adjacent.                                              |
| `styles/components/section-theme.css:81`            | Headings/paragraphs/lists under PNS sections                      | component/pattern surface                          | Medium  | Medium      | Remain; comment/test if touched.                                            |
| `styles/components/section-theme.css:124`           | Core button links under PNS sections                              | component/pattern surface                          | Medium  | Medium      | Remain; existing cascade test covers part.                                  |
| `styles/components/section-theme.css:166`           | Dark-section text descendants excluding `.pns-split-section__cta` | component/pattern surface                          | Medium  | Medium      | Remain; comment CTA carve-out if touched.                                   |
| `styles/components/section-theme.css:187`           | Dark-section core button background/text                          | component/pattern surface                          | Medium  | Medium      | Remain; existing cascade fixture covers.                                    |
| `styles/components/section-theme.css:222`           | `.clear-button` reset/inversion inside dark PNS sections          | vendor/control exclusion                           | Medium  | Medium      | Remain; existing cascade fixture covers.                                    |
| `styles/components/light-surface.css:5`             | `.pns-light-surface` surface variables/background/text            | component/pattern surface                          | High    | High        | Remain; keep tested.                                                        |
| `styles/components/light-surface.css:25`            | Text and heading descendants in light surface                     | component/pattern surface                          | High    | High        | Remain; add explicit comment/test if touched.                               |
| `styles/components/light-surface.css:36`            | Core post/query/comment metadata blocks                           | page-type rhythm                                   | Medium  | Medium      | Split to follow-up if hardening.                                            |
| `styles/components/light-surface.css:49`            | Non-button links, hover/focus, separators                         | component/pattern surface                          | High    | High        | Remain; comment surface contract if touched.                                |
| `styles/components/light-surface.css:63`            | Core button links/pseudo-element in light surface                 | component/pattern surface                          | High    | Medium      | Remain; existing button readability test covers.                            |
| `styles/components/light-surface.css:82`            | `.pns-section.pns-light-surface` variable bridge                  | component/pattern surface                          | High    | Medium      | Remain; comment bridge if touched.                                          |
| `styles/blocks/core-navigation.css:1`               | Navigation barrel imports only                                    | load-path wrapper                                  | Low     | Low         | No styling owner; keep wrapper-only.                                        |
| `styles/blocks/core-navigation-base.css:5`          | `.wp-block-navigation` root font/line-height/padding              | real core block default                            | Medium  | Medium      | Remain; broad theme default for Navigation.                                 |
| `styles/blocks/core-navigation-base.css:11`         | Navigation default color unless `.has-text-color`                 | real core block default                            | Medium  | Medium      | Remain; respects author color control.                                      |
| `styles/blocks/core-navigation-base.css:15`         | Navigation flex/container gap                                     | real core block default                            | Medium  | Medium      | Remain; bridge to `--pns--navigation--gap`.                                 |
| `styles/blocks/core-navigation-base.css:20`         | `.wp-block-navigation-item` padding                               | mixed core default and component surface           | Medium  | High        | Follow-up: verify generic Navigation blocks before narrowing.               |
| `styles/blocks/core-navigation-base.css:26`         | `.wp-block-navigation-item__content` padding/position             | mixed core default and component surface           | Medium  | High        | Follow-up with generic Navigation control test if changed.                  |
| `styles/blocks/core-navigation-base.css:32`         | Navigation item typography/text wrapping                          | real core block default                            | Medium  | Medium      | Remain; font-size controls are handled below.                               |
| `styles/blocks/core-navigation-base.css:44`         | Inline font-size inheritance                                      | vendor/control exclusion                           | Low     | Medium      | Remain; protects author-set font-size.                                      |
| `styles/blocks/core-navigation-base.css:48`         | `.has-small-font-size` Navigation                                 | saved-content compatibility                        | Medium  | Medium      | Remain unless generated preset output proves redundant.                     |
| `styles/blocks/core-navigation-base.css:52`         | `.has-large-font-size` Navigation                                 | saved-content compatibility                        | Medium  | Medium      | Remain unless generated preset output proves redundant.                     |
| `styles/blocks/core-navigation-base.css:56`         | Submenu icon stroke                                               | component/pattern surface                          | Medium  | Medium      | Follow-up: scope to primary/header if generic nav should differ.            |
| `styles/blocks/core-navigation-desktop.css:5`       | `.pns-primary-navigation` gap/z-index                             | component/pattern surface                          | Medium  | Medium      | Remain; primary nav owner.                                                  |
| `styles/blocks/core-navigation-desktop.css:13`      | Generic submenu item border/padding                               | mixed component surface and core default           | Medium  | Medium      | Follow-up: test footer/generic Navigation before narrowing.                 |
| `styles/blocks/core-navigation-desktop.css:20`      | Submenu link padding with priority                                | vendor/control exclusion                           | Medium  | Medium      | Remain; already documented core priority exception.                         |
| `styles/blocks/core-navigation-desktop.css:25`      | Suppress submenu hover marker                                     | component/pattern surface                          | Medium  | Medium      | Remain with Navigation tests.                                               |
| `styles/blocks/core-navigation-desktop.css:31`      | Top-item padding at 582px+                                        | mixed component surface and core default           | Medium  | Medium      | Follow-up: scope if generic Navigation blocks need core defaults.           |
| `styles/blocks/core-navigation-desktop.css:41`      | Primary top-item padding reset                                    | component/pattern surface                          | Medium  | Medium      | Remain; primary nav owner.                                                  |
| `styles/blocks/core-navigation-desktop.css:47`      | Submenu icon placement and hover marker at 600px+                 | mixed component surface and core default           | Medium  | High        | Follow-up: broad hover selector should be checked against generic nav.      |
| `styles/blocks/core-navigation-desktop.css:71`      | Top-level content gap/padding at 990px+                           | mixed component surface and core default           | Medium  | Medium      | Follow-up: selector may be ineffective or too broad; verify before editing. |
| `styles/blocks/core-navigation-desktop.css:80`      | Wide primary and non-primary item gap                             | mixed component surface and core default           | Medium  | Medium      | Follow-up: determine whether non-primary Navigation should inherit this.    |
| `styles/blocks/core-navigation-core-drawer.css:5`   | Open/close icon color                                             | component/pattern surface                          | Low     | Medium      | Remain; core drawer adapter.                                                |
| `styles/blocks/core-navigation-core-drawer.css:10`  | Drawer keyframes                                                  | component/pattern surface                          | Low     | Low         | Remain; no selector risk.                                                   |
| `styles/blocks/core-navigation-core-drawer.css:32`  | `html.has-modal-open` overflow lock                               | vendor/control exclusion                           | Low     | Medium      | Remain; WordPress core modal state.                                         |
| `styles/blocks/core-navigation-core-drawer.css:36`  | Primary open-button padding                                       | component/pattern surface                          | Low     | Medium      | Remain.                                                                     |
| `styles/blocks/core-navigation-core-drawer.css:40`  | Header primary nav z-index when drawer open                       | component/pattern surface                          | Low     | Medium      | Remain; fixes logo overlap.                                                 |
| `styles/blocks/core-navigation-core-drawer.css:48`  | Primary core responsive container drawer panel                    | component/pattern surface                          | Medium  | Medium      | Remain; accepted drawer adapter.                                            |
| `styles/blocks/core-navigation-core-drawer.css:87`  | Fixed close button placement                                      | component/pattern surface                          | Low     | Medium      | Remain; accepted drawer refinement.                                         |
| `styles/blocks/core-navigation-core-drawer.css:104` | Drawer list/submenu reset                                         | component/pattern surface                          | Medium  | Medium      | Remain; core drawer adapter.                                                |
| `styles/blocks/core-navigation-core-drawer.css:153` | Drawer submenu hidden/open state with priority                    | vendor/control exclusion                           | Medium  | High        | Remain; core drawer priority exception.                                     |
| `styles/blocks/core-navigation-core-drawer.css:170` | Drawer item row box model                                         | component/pattern surface                          | Medium  | Medium      | Remain.                                                                     |
| `styles/blocks/core-navigation-core-drawer.css:186` | Drawer row link/button styling                                    | component/pattern surface                          | Medium  | Medium      | Remain.                                                                     |
| `styles/blocks/core-navigation-core-drawer.css:199` | Suppress desktop hover marker in drawer                           | component/pattern surface                          | Low     | Medium      | Remain.                                                                     |
| `styles/blocks/core-navigation-core-drawer.css:208` | Drawer submenu trigger layout                                     | component/pattern surface                          | Medium  | Medium      | Remain.                                                                     |
| `styles/blocks/core-navigation-core-drawer.css:232` | Drawer hover background/text inversion                            | component/pattern surface                          | Medium  | Medium      | Remain; accepted visual behavior.                                           |
| `styles/blocks/core-navigation-core-drawer.css:248` | Drawer plus/minus indicators                                      | component/pattern surface                          | Medium  | Medium      | Remain.                                                                     |
| `styles/blocks/core-navigation-core-drawer.css:282` | Nested drawer item inset                                          | component/pattern surface                          | Medium  | Medium      | Remain.                                                                     |
| `styles/blocks/core-navigation-core-drawer.css:316` | Closed responsive-container submenu hover border                  | mixed core default and component surface           | Medium  | Medium      | Follow-up: likely belongs in desktop partial after testing.                 |
| `styles/blocks/core-navigation-core-drawer.css:325` | Responsive submenu hover marker suppression                       | mixed core default and component surface           | Medium  | Medium      | Follow-up: consolidate with desktop hover marker rules.                     |
| `styles/blocks/core-navigation-core-drawer.css:338` | Small-screen drawer row padding                                   | component/pattern surface                          | Low     | Medium      | Remain.                                                                     |
| `styles/blocks/core-navigation-core-drawer.css:346` | 999px open-button/container display contract                      | component/pattern surface                          | Medium  | High        | Remain; breakpoint contract is intentional.                                 |
| `styles/blocks/core-cover.css:5`                    | Global cover padding reset                                        | real core block default                            | Medium  | Low         | Remain; comment if policy wants owner markers.                              |
| `styles/blocks/core-cover.css:10`                   | PNS section cover custom-position inner sizing                    | component/pattern surface                          | Medium  | Low         | Remain; add focused test/comment before narrowing.                          |

## Follow-Up Candidates

These are not authorized implementation tasks yet.

1. Split saved preset class fallbacks from base element defaults:
   `.has-large-font-size`, `.has-x-large-font-size`, and
   `.has-medium-font-size`.
2. Decide whether `.alignwide` remains a compatibility bridge or becomes a
   narrower template/content contract.
3. Move `content-rhythm.css` PNS section resets to component owners where
   existing hooks are stable.
4. Reconcile `.wp-block-quote p` between `content-rhythm.css` and
   `styles/blocks/core-quote.css`.
5. Narrow generic Navigation item padding, hover marker, submenu icon, and
   submenu border selectors after proving generic/footer Navigation behavior.
6. Move closed responsive-container submenu hover rules out of the core drawer
   partial if they remain desktop-owned.
7. Add comments/tests around high-risk retained surface contracts:
   `.pns-light-surface`, generated flow resets, and drawer submenu priority
   overrides.

## Validation Notes

No visual tests were run because this cut made no CSS behavior changes.

Formatting/check validation should cover this inventory and the related plan
docs. Any future behavior-changing follow-up should use the relevant lanes:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
```
