# Editor CSS Ownership Inventory

Status: Phase 2 inventory for
`docs/jobs/__completed/editor-css-design-system-pivot.md`.
No CSS behavior changes were made for this phase.

## Current Editor Loading Path

- `functions.php` loads the editor stylesheet through `add_editor_style()`.
- `styles/editor.css` currently imports:
  - `shared/layers.css`
  - `blocks/legacy-editor.css`
  - `components/index.css`
  - `utilities/index.css`
  - `vendor-overrides/index.css`
- `functions.php` registers block-owned styles with `wp_enqueue_block_style()`
  for Columns, Cover, Group, Image, Navigation, Quote, Separator, Social Links,
  and Jetpack Slideshow.
- `theme.json` owns supported design defaults including layout widths, font
  families, font sizes, global typography, button defaults, heading defaults,
  separator defaults, navigation typography, and paragraph defaults.

## Canonical Owners

| Rule family                      | Current legacy ranges                                                    | Canonical owner                                                                                                      | Phase 3 posture                                                                                                                          |
| -------------------------------- | ------------------------------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| Fonts and root tokens            | 1-26, 748-768, 784-819                                                   | `styles/shared/fonts.css`, `styles/shared/settings.css`, `theme.json`                                                | Make shared font/settings imports explicit in `styles/editor.css`, then remove duplicate legacy copies.                                  |
| Layout widths and flow resets    | 28-51, 171-179, 224-226, 831-838, 885-887                                | `theme.json`, `styles/layout/index.css`                                                                              | Do not bulk-delete yet. Remove only covered duplicate flow/group rules after editor assertions.                                          |
| Heading and paragraph typography | 237-288, 475-482, 728-730, 779-782, 840-863, 947-954                     | `theme.json`, `styles/base/elements.css`, narrow editor canvas rules if needed                                       | Remove duplicated defaults only where `theme.json` or block editor output proves parity. Keep editor wrapper rules only if still needed. |
| Core Image                       | 53-56, 158-160, 427-431, 714-722, 762-772, 828-830                       | `styles/blocks/core-image.css`, utilities for intentional width helpers                                              | Good Phase 3 candidate for image margin/reset duplication. Width helpers need utility coverage first.                                    |
| Core Cover                       | 329-363, 900-933                                                         | `styles/blocks/core-cover.css`                                                                                       | Good Phase 3 candidate for covered cover padding/inner rules.                                                                            |
| Core Group                       | 224-226, 831-838                                                         | `styles/blocks/core-group.css`                                                                                       | Good Phase 3 candidate after shared import check.                                                                                        |
| Core Quote                       | 270-288, 728-730, 861-863, 745-747                                       | `styles/blocks/core-quote.css`, `theme.json`                                                                         | Good Phase 3 candidate because the fixture already covers quote typography.                                                              |
| Core Separator                   | 136-144, 458-460                                                         | `styles/blocks/core-separator.css`, `theme.json`                                                                     | Good Phase 3 candidate because the fixture already covers separator dimensions.                                                          |
| Core Social Links                | 311-313, 521-524, 638-641, 882-884, 966-969, 1072-1075                   | `styles/blocks/core-social-links.css`, `theme.json`                                                                  | Good candidate, but social icon size drift needs explicit before/after assertion.                                                        |
| Core Navigation                  | 73-127, 146-156, 228-235, 935-945                                        | `styles/blocks/core-navigation.css`; frontend-only item sizing in `core-navigation-frontend.css`                     | Defer. Existing selectors include generated `.wp-container-2` and broad editor rules that need a stable editable navigation surface.     |
| Jetpack Slideshow                | 162-169, 500-520, 732-739, 961-964                                       | `styles/blocks/jetpack-slideshow.css`                                                                                | Defer until a real editor-rendered slideshow surface is confirmed or fixture coverage is added.                                          |
| Contact/form fields              | 469-473, 654-660                                                         | No active canonical editor owner yet; possible plugin/form override                                                  | Defer. Confirm rendered editor output before carrying forward.                                                                           |
| Buttons and form buttons         | 525-636, 971-1070                                                        | `styles/components/buttons.css`, `theme.json`, plugin-specific overrides where needed                                | Defer to Phase 4. `components/buttons.css` is not currently imported by `components/index.css`, and the two legacy button chunks drift.  |
| Utility classes                  | 181-211, 290-292, 432-434, 484-499, 646-673, 718-726, 956-959, 1076-1109 | `styles/utilities/index.css`                                                                                         | Remove covered duplicate utilities gradually. Add assertions for untested helpers before deletion.                                       |
| Footer and logos                 | 129-144, 315-324, 409-425, 458-460, 774-777, 888-895                     | `styles/components/footer.css`, `footer-layout.css`, `logos.css`                                                     | Defer until touching editable footer/template surfaces.                                                                                  |
| Page/content composition         | 405-407, 437-443, 458-460, 687-712                                       | `styles/page-types/herstories-bios.css`, `styles/page-types/shop.css`, scoped editor-content file if authors need it | Defer. These are content-family rules, not global block defaults.                                                                        |
| Vendor/plugin overrides          | 162-169, 469-473, 500-520, 654-660, 732-739, 961-964                     | Editor-specific vendor override only when a plugin preview proves it is needed                                       | Defer to Phase 5. Current editor entrypoint imports all frontend vendor overrides without proof.                                         |

## Editor-Only Rules Found

The only clearly editor-scoped active selector family in `legacy-editor.css` is
the duplicated `.editor-styles-wrapper h1` through `h6` block at lines 475-482
and 947-954. Even that may be redundant with `theme.json` and registered editor
support. Treat it as a Phase 3 verification target, not as a permanent editor
owner by default.

## Duplicate And Drift Hotspots

- Font faces are repeated three times: 1-13, 748-760, and 784-796.
- Root tokens are repeated three times with partial drift: 15-26, 766-768, and
  809-819.
- Image margin resets are repeated at 158-160, 770-772, and 828-830.
- Navigation rules repeat at 81-91 and 935-945, including a fragile generated
  `.wp-block-navigation.wp-container-2` selector.
- Social icon sizing repeats with conflicting 2rem and 3rem values.
- Button styling repeats in two large chunks with padding and font-size drift:
  532-636 and 971-1070.
- `.is-layout-flow ul` repeats with conflicting padding values: 307-309,
  642-644, 878-880, and 1076-1078.
- Utility tail rules repeat near 646-673 and 1080-1099.

## Release-Watch Candidates

Do not carry these forward without rendered evidence:

- Comment-only legacy experiments at 58-71, 213-222, 294-306, 333-356,
  382-395, 446-456, 674-685, 798-807, 821-826, 865-877, and 900-933.
- Empty or no-op active rules at 171-174 and 463-467.
- Generated navigation selector `.wp-block-navigation.wp-container-2`.
- Legacy-only helpers `.pt15`, `.ml0`, `.mw-584`, and
  `.is-layout-flow .wp-block-image img.is-50vw`.
- Plugin selectors `.wpcf7-submit`, `.simplefavorite-button`, and
  `.pushbutton-wide` unless the active editor surface proves they still render.

## Current Harness Coverage

The Phase 1 editor harness already covers:

- headings and registered font-size classes;
- paragraphs through real-page heading smoke checks;
- button links;
- quote;
- separator;
- cover;
- group with background;
- columns with `no-gap`;
- `m-auto`;
- `active-dates`;
- `fun-facts` list markers;
- social links and icon sizing;
- `vw-100`;
- `w-100`;
- block recovery warnings.

The harness does not yet assert:

- `.mt0`, `.p1`, `.p2-m`, `.pr0`, `.pl1`, `.pr1`, `.lh0`;
- `.ml0`, `.mw-584`, `.pt15`;
- detailed navigation styles;
- contact form or Jetpack slideshow editor previews;
- footer/logo/template editor surfaces;
- frontend vendor override imports inside the editor.

Phase 3 update: `.mb05`, `.mw-intro-text`, and `.w-50-m` now have focused
computed-style assertions in the editor harness.

Phase 4 update: editor buttons now import the canonical component owner through
`styles/components/index.css`, and duplicated legacy button/covered utility
rules were removed from `legacy-editor.css`. Remaining utility/content risks
are `.pt15`, `.ml0`, `.mw-584`, `.active-dates`, `.fun-facts`, contact form
fields, and the later `.mt0` padding drift.

Phase 5/6 update: `styles/editor.css` no longer imports frontend vendor
overrides. Canonical layout, base, page-type, Jetpack contact form, footer
layout, and logo owners are now imported directly for the editor. Active
`legacy-editor.css` declarations have been replaced by dated release-watch
comments only.

## Recommended Phase 3 First Batch

1. Update `styles/editor.css` so shared fonts/settings are explicit editor
   imports instead of being implicitly supplied by `legacy-editor.css`.
2. Extend the editor harness with focused assertions for `.mb05`,
   `.mw-intro-text`, and `.w-50-m`, because those are intentionally listed in
   the planning doc but are not yet tested.
3. Remove duplicate legacy rules for the covered native block families:
   fonts/tokens after shared imports, Core Quote, Core Separator, Core Image
   margin reset, Core Cover, Core Group background spacing, and Core Social
   Links.
4. Recompile editor CSS and run the editor harness.
5. Run the frontend visual suite if Phase 3 touches shared files, registered
   block styles, components, utilities, or `theme.json`.

Defer buttons, navigation, plugin/vendor rules, page-specific composition,
footer/logo rules, and release-watch-only helpers to later phases.
