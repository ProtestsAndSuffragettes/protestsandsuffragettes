# Frontend CSS `!important` Triage

## Current Scope

Step 2 covers authored frontend CSS only:

- `app/public/wp-content/themes/protestsandsuffragettes/styles/blocks/legacy-frontend.css`
- `app/public/wp-content/themes/protestsandsuffragettes/styles/blocks/core-*.css`
- `app/public/wp-content/themes/protestsandsuffragettes/styles/components/*.css`
- `app/public/wp-content/themes/protestsandsuffragettes/styles/vendor-overrides/*.css`

Editor CSS and compiled `styles/dist/` files are out of scope for this pass.

## Baseline

- `legacy-frontend.css`: 77 active `!important` declarations, excluding comments.
- `customizer-migrated.css`: 20 active `!important` declarations, excluding comments.
- Total active frontend scope: 97.

Visual baseline after Customizer Additional CSS removal:

- `pnpm test:visual`: 18 passed.

Wallace compiled-CSS baseline:

- See `docs/frontend-css-wallace-baseline.md`.
- Wallace analyzes compiled CSS and is used for trend tracking, not selector-unused proof.

## Classification

### `legacy-frontend.css`

- `remove-now`: 2 declarations.
  - Superseded `.alignwide` `max-width: 1280px !important`.
  - Duplicated button `text-decoration: none !important`.
- `component-cleanup`: 27 declarations.
  - Narrow block/component selectors such as columns, nav spacing, separators, social links, logos, active dates, fun facts, and clear buttons.
- `layout-high-risk`: 20 declarations.
  - Global width/overflow, `.is-layout-*`, `.alignwide`, block spacing, headings, paragraph margins, and quote spacing.
- `vendor/plugin`: 13 declarations.
  - Jetpack slideshow, Ecwid typography/pricing, contact form fields, and plugin-related block behavior.
- `keep-documented`: 15 declarations.
  - Utility-style width/margin/gap helpers and narrow rules that should be validated in their own batches before changing.

### `customizer-migrated.css`

- `remove-now`: 0 declarations.
- `component-cleanup`: 1 declaration.
  - `.footer-wt p { color: #fff !important; }`
- `layout-high-risk`: 8 declarations.
  - Navigation submenu and mobile drawer margin, padding, and border overrides.
- `vendor/plugin`: 9 declarations.
  - Ecwid/Facebook Messenger, Jetpack slideshow, EmailOctopus layout, focus, input, and powered-by overrides.
- `keep-documented`: 2 declarations.
  - Navigation pseudo-element suppression for inherited hover styles.

## Batch Log

### Batch 1: Mechanical Duplicates

Remove only declarations that cannot change modern-browser computed output:

- Removed the superseded `max-width: 1280px !important` before the later `calc(...) !important` declaration in the same `.alignwide` rule.
- Removed `!important` from the first button `text-decoration: none` declaration because the same rule later sets `text-decoration: none` again.

Verification:

- `pnpm compile:css`
- `pnpm test:visual`: 18 passed.

Resulting active frontend `!important` count: 95.

### Batch 2: Footer Color

Remove one narrow component override:

- Removed `!important` from `.footer-wt p { color: #fff; }`.
- Added a homepage computed-style assertion that `.footer-wt p` remains white.

Required verification:

- `pnpm compile:css`
- `pnpm test:visual`

Verification:

- `pnpm compile:css`
- `pnpm test:visual`: 18 passed.

Resulting active frontend `!important` count: 94.

### Batch 3: EmailOctopus Focus Style Attempt

Attempted to remove two form focus flags covered by `/edu-giveaway/` computed assertions:

- `.form-control:focus { border-color: #000 !important; }`
- `.form-control:focus { box-shadow: 0 0 0 1px #000 !important; }`

Verification:

- `pnpm compile:css`
- `pnpm test:visual`: failed the `/edu-giveaway/` computed assertion because focused input `border-color` fell back to `rgb(220, 221, 222)`.

Result:

- Reverted this batch.
- Keep these two declarations documented as vendor/plugin overrides unless the EmailOctopus selector is refactored with stronger non-`!important` specificity in a future batch.
- Restored state verification: `pnpm test:visual` passed with 18 tests.

## Next Candidates

Do not start these until the reverted Batch 3 state has passed visual tests again.

### Batch 4: EmailOctopus Input Padding

Remove one form padding flag covered by `/edu-giveaway/` computed assertions:

- Removed `!important` from EmailOctopus text/email/tel/url/number/textarea `padding: 26px`.

Required verification:

- `pnpm compile:css`
- `pnpm test:visual`

Verification:

- `pnpm compile:css`
- `pnpm test:visual`: 18 passed.

Resulting active frontend `!important` count: 93.

### Batch 5: Release-Watch Unused Utilities

Comment out, but do not delete, high-confidence unused selector families:

- `.m-auto-wide`
- `.wide-img`
- `.pt15`
- `.ml0`
- `.is-50vw`

Evidence:

- No child-theme hits outside the legacy CSS.
- No selected WP content hits.
- No rendered DOM hits in the published-page coverage crawl.

Required verification:

- `pnpm compile:css`
- `pnpm test:visual`
- `pnpm analyze:css`

Verification:

- `pnpm compile:css`
- `pnpm analyze:css`: 167 rules, 238 selectors, 449 declarations, 93 compiled `!important` declarations.
- `pnpm test:visual`: 21 passed.

Resulting active frontend `!important` count: 90.

## Next Candidates

Do not start these until Batch 5 has passed visual tests.

### Batch 6: Clear Button Background Attempt

Attempted to remove one narrow previous/next button flag:

- `.clear-button .wp-block-button__link` and `::after` transparent background.
- Added Mary Barbour computed assertions for the clear button and its `::after` background.

Verification:

- `pnpm compile:css`
- `pnpm test:visual`: failed the Mary Barbour computed assertion because `.clear-button .wp-block-button__link` background changed from transparent to white.

Result:

- Reverted this batch.
- Keep this declaration documented until it is refactored with stronger non-`!important` specificity or button base styles are reorganized.
- Restored state verification: `pnpm test:visual` passed with 21 tests.

## Next Candidates

Do not start these until Batch 6 has passed visual tests.

### Batch 7: Fun Facts Active List Items

Remove one page-specific list-style flag:

- Removed `!important` from `.fun-facts li { list-style: none; }`.
- Added Mary Barbour computed assertions for current `.fun-facts` markup, native bullet suppression, and custom `::before` bullets.
- Left `.fun-facts ul` and `.fun-facts .is-layout-flow ul` untouched because they do not match the current Mary Barbour markup and should be handled as a separate dead-selector/release-watch batch.

Required verification:

- `pnpm compile:css`
- `pnpm test:visual`

Verification:

- `pnpm compile:css`
- `pnpm test:visual`: 21 passed.
- `pnpm analyze:css`: 167 rules, 238 selectors, 449 declarations, 92 compiled `!important` declarations.

Resulting active frontend `!important` count: 89.

## Completed Follow-Up Batches

### Batch 8: Release-Watch Nested Fun Facts Selectors

Comment out, but do not delete, nested fun-facts selectors that do not match the current Mary Barbour markup:

- `.fun-facts ul { list-style: none !important; }`
- `.fun-facts .is-layout-flow ul { padding-left: 1rem !important; }`

Evidence:

- The Mary Barbour route has active `.fun-facts li` markup.
- Playwright asserts `document.querySelectorAll('.fun-facts ul').length === 0`.
- Playwright asserts `document.querySelectorAll('.fun-facts .is-layout-flow ul').length === 0`.

Verification:

- `pnpm compile:css`
- `pnpm test:visual`: 21 passed.
- `pnpm analyze:css`: 165 rules, 236 selectors, 445 declarations, 90 compiled `!important` declarations.

Resulting active frontend `!important` count: 87.

### Batch 9: Button Radius

Remove one redundant button component flag:

- Removed `!important` from `.wp-block-button .wp-block-button__link { border-radius: 0; }`.
- Existing homepage computed-style assertion already checks the button radius remains `0px`.

Verification:

- `pnpm compile:css`
- `pnpm test:visual`: 21 passed.

Resulting active frontend `!important` count: 86.

### Batch 10: Social Link Partial Cleanup

Attempted to remove `!important` from social-list padding, social-link anchor padding, and logo-only SVG dimensions.

Result:

- Kept `.wp-block-social-links { padding-left: 0 !important; }`.
- The first run failed because the social links list fell back to `padding-left: 16px`.
- Added a short keep comment to document this real cascade dependency.
- Removed `!important` from `.wp-block-social-links .wp-social-link a { padding: ...; }`.
- Removed `!important` from `.wp-block-social-links.is-style-logos-only .wp-social-link svg` width and height.
- Added homepage computed-style assertions for social list padding, anchor padding, and both SVG dimensions.

Verification:

- `pnpm compile:css`
- Initial `pnpm test:visual`: failed homepage computed assertions for social-list padding.
- Adjusted only the social-list padding declaration back to `!important`.
- `pnpm compile:css`
- `pnpm test:visual`: 21 passed.

Resulting active frontend `!important` count: 83.

### Batch 11: Release-Watch Legacy Contact Form Fields

Comment out, but do not delete, the old `.contact-form` field sizing rule:

- `.wp-block-column .contact-form input[type="email"]`
- `.wp-block-column .contact-form input[type="text"]`
- `.wp-block-column .contact-form input[type="url"]`
- `.wp-block-column .contact-form textarea`

Evidence:

- Rendered selector probes found zero `.contact-form` matches across current coverage pages and additional public pages.
- Current live form coverage on `/edu-giveaway/` is EmailOctopus, not `.contact-form`.
- Existing `/edu-giveaway/` computed assertions continued to pass.

Verification:

- `pnpm compile:css`
- `pnpm test:visual`: 21 passed.
- `pnpm analyze:css`: 161 rules, 232 selectors, 437 declarations, 78 compiled `!important` declarations.

Resulting active frontend `!important` count: 81.

### Batch 12: Quote Block

Remove seven quote block flags that were exercised by current route coverage:

- `.wp-block-quote { border-left: 0em solid; }`
- `.wp-block-quote` mobile padding left/right.
- `.wp-block-quote` tablet/desktop padding left/right at `min-width: 600px`.
- `.wp-block-quote` wide desktop padding left/right at `min-width: 1300px`.

Evidence:

- Current visual routes exercise quote markup on `/`, `/herstories/mary-barbour/`, and `/edu-giveaway/`.
- Added Mary Barbour computed assertions for responsive quote padding:
  - mobile: `16px`
  - tablet: `32px`
  - desktop: `0px`
- Existing assertion checks quote border width remains `0px`.

Verification:

- `pnpm compile:css`
- `pnpm test:visual`: 21 passed.
- `pnpm analyze:css`: 161 rules, 232 selectors, 437 declarations, 71 compiled `!important` declarations.

Resulting active frontend `!important` count: 74.

### Batch 13: MB05 Attempt

Attempted to remove one local utility flag:

- `.mb05 { margin-bottom: 0.5rem !important; }`

Result:

- Reverted the removal and kept the `!important`.
- Replaced the broad `.mb05` selector with scoped `.wp-block-heading.mb05, p.mb05`.
- Added a short keep comment in the CSS explaining that priority is still required until the global heading/paragraph `margin-block-end` flags are removed.
- Added a homepage computed-style assertion for `.wp-block-heading.mb05` so future refactors protect the active heading usage.
- The failed run showed `.mb05` is active on current routes and removing priority changes page heights.
- Without priority, Mary Barbour `.mb05` paragraphs computed to `0px`, and homepage promo headings did not keep the intended `8px` margin.

Verification:

- Initial `pnpm test:visual`: failed snapshot and computed-style checks.
- Restored only the `.mb05` priority and removed the temporary failed assertion.
- Added the more precise homepage `.wp-block-heading.mb05` assertion.
- `pnpm compile:css`
- `pnpm test:visual`: 21 passed.
- Narrowed selector verification:
  - `pnpm test:visual`: 21 passed.
  - `pnpm check`: passed.

Resulting active frontend `!important` count: 74.

### Batch 14: Active Dates

Remove three active-dates flags as one component batch:

- `.is-layout-flow .active-dates ul { padding: 0; }`
- `.active-dates { max-width: 20rem; }`
- `.active-dates { margin: 0; }`

Evidence:

- `/herstories/mary-barbour/` renders `.active-dates` and is covered at all visual viewports.
- Added computed assertions for `.active-dates` max width and `.active-dates ul` padding on all sides.
- Did not assert wrapper margin as `0px`, because the current computed layout auto-centers this element through the broader flow rule.

Verification:

- `pnpm compile:css`
- `pnpm test:visual`: 21 passed.
- `pnpm analyze:css`: 161 rules, 232 selectors, 437 declarations, 68 compiled `!important` declarations.

Resulting active frontend `!important` count: 71.

### Batch 15: Typography Presets

Move current frontend font-size preset values into WordPress-native `theme.json` settings, then remove authored frontend priority from the matching preset classes:

- Added `medium`, `large`, and `x-large` entries to `settings.typography.fontSizes`.
- Updated `theme.json` `h2` and `h3` element font sizes to the existing responsive clamp value.
- Removed `!important` from `.has-medium-font-size`, `.has-large-font-size`, and `.has-x-large-font-size`.
- Split `.grid-product__title-inner` into its own Ecwid-specific rule and kept its `!important` for a later shop/Ecwid batch.
- Added homepage computed-style assertions for medium, large, x-large, and plain `h2` font sizes.

Evidence:

- Rendered probes confirmed `.has-medium-font-size`, `.has-large-font-size`, `.has-x-large-font-size`, and plain `h2` are exercised by current visual routes.
- `/shop/` renders `.grid-product__title-inner`, so it was deliberately excluded from this preset cleanup.

Verification:

- `pnpm compile:css`
- `pnpm test:visual`: 21 passed.

Resulting active frontend `!important` count: 67.

### Batch 16: Header Navigation Font Size

Replace a broad navigation font-size priority rule with a scoped header selector:

- Changed `.button.wp-block-navigation-item__content, .wp-block-navigation-item__content` to `header .wp-block-navigation .wp-block-navigation-item__content`.
- Removed the mobile `font-size: .8125rem !important`.
- Removed the tablet/desktop `font-size: 1rem !important`.
- Added a homepage computed-style assertion that header nav items remain `13px` on mobile and `16px` on tablet/desktop.

Evidence:

- Navigation is rendered on every visual route.
- Deeper submenu/drawer rules remain out of scope because current screenshots do not open the mobile drawer or hover submenus.

Verification:

- `pnpm compile:css`
- `pnpm test:visual`: 21 passed.

Resulting active frontend `!important` count: 65.

### Batch 17: EmailOctopus Structural Attempt

Attempted to remove four vendor/form flags:

- `[data-form="5e60a222-ff72-11ef-8552-6b8c59d486cb"] .nurture-container { grid-template-columns: 1fr !important; }`
- `[data-form="5e60a222-ff72-11ef-8552-6b8c59d486cb"] .align-mid.pt-5.pb-5` top/bottom padding.
- `[eo-block="powered-by"] { display: none !important; }`

Result:

- Reverted all four removals.
- The powered-by rule is global plugin output; removing priority changed every route snapshot by exposing extra EmailOctopus markup.
- The grid and padding rules lose to injected EmailOctopus styles without priority. The form contract caught both failures.
- Kept the new padding-wrapper assertion because it documents the real vendor conflict.

Verification:

- Failed `pnpm test:visual` with `/edu-giveaway/` snapshot and computed-style regressions.
- Restored only the failed declarations.
- `pnpm test:visual`: 21 passed.

Resulting active frontend `!important` count: 65.

### Batch 18: Navigation Spacing

Remove nine nav spacing flags while keeping documented exceptions where assertions proved priority is still required:

- Scoped top-level nav item padding to `.wp-block-navigation__container > .wp-block-navigation-item` and removed two padding flags.
- Removed submenu item margin/padding flags in `customizer-migrated.css`.
- Removed mobile drawer item border/margin/padding priority and nested drawer margin/padding priority.
- Added computed assertions for top-level nav item padding, submenu item resets, and opened mobile drawer spacing.
- Kept `.wp-block-navigation__responsive-container... .wp-block-navigation__submenu-container { padding: 0 !important; }`, because the open drawer fell back to `32px` padding without priority.
- Kept submenu link padding priority, because hidden submenu link inline padding changed without priority.

Verification:

- `pnpm compile:css`
- `pnpm test:visual`: 24 passed.

Resulting active frontend `!important` count: 56.

### Batch 19: Full-Bleed Utility Slice

Remove five live utility flags as one covered batch:

- `.w-50-m img { width: 50vw; }`
- Scoped `.w-100` to `.wp-block-column.w-100, .wp-block-group.w-100` and removed priority.
- `.no-gap { gap: 0; }`
- `.vw-100 { width: 100vw; max-width: 100vw; }`

Evidence:

- Rendered probes confirmed these utilities are live across current visual routes.
- Added homepage computed assertions for `.no-gap`, `.vw-100`, scoped `.w-100`, and `.w-50-m img`.
- Scoped `.w-100` avoids applying the theme utility to plugin/form classes such as `input.btn.w-100`.

Verification:

- `pnpm compile:css`
- `pnpm test:visual`: 24 passed.

Resulting active frontend `!important` count: 51.

### Batch 20: Margin Family Attempt

Attempted to remove the global heading, paragraph, and `.mb05` margin flags together:

- `h1,h2,h3,h4,h5,h6 { margin-block-end: 1.25rem !important; }`
- `p { margin-block-end: .625rem !important; }`
- `.wp-block-heading.mb05, p.mb05 { margin-block-end: 0.5rem !important; }`

Result:

- Reverted all three removals.
- The batch compressed current route screenshots by 20-50px depending on viewport and route.
- These rules remain active layout controls, even though broad flow rules mask some individual computed samples.

Verification:

- Failed `pnpm test:visual` on `/` and `/herstories/mary-barbour/` snapshots.
- Restored the batch.

Resulting active frontend `!important` count: 51.

### Batch 21: Jetpack Slideshow Attempt

Attempted to remove three Jetpack slideshow flags:

- `.wp-block-jetpack-slideshow .wp-block-jetpack-slideshow_image` max width/height.
- `.wp-block-jetpack-slideshow_pagination { display: none !important; }`
- `.wp-block-jetpack-slideshow .wp-block-jetpack-slideshow_image { object-fit: cover !important; }`

Result:

- Reverted all three removals.
- Even though slideshow regions are masked in screenshots, the plugin markup still affects page height and produced large snapshot diffs.
- Keep these documented as plugin overrides unless replaced by stronger Jetpack-specific selectors and re-tested.

Verification:

- Failed `pnpm test:visual` on home snapshots.
- Restored the batch.

Resulting active frontend `!important` count: 51.

### Batch 22: Duplicate Flow Utility Rules

Remove five redundant flow utility flags while keeping the broad flow reset in place:

- Removed `.m-auto` logical margin priority declarations.
- Removed duplicate `body .is-layout-flow .m-auto` declarations.
- Removed duplicate `body .is-layout-flow .mt0` declaration.
- Kept `.m-auto { margin: auto; }`.
- Kept the later `.mt0 { margin-top: 0 !important; }` utility.
- Added homepage computed assertions for `.m-auto` margin/max-width and `.mt0` margin.

Verification:

- `pnpm compile:css`
- `pnpm test:visual`: 24 passed.

Resulting active frontend `!important` count: 46.

### Batch 23: Navigation Hover And Icon State

Remove five navigation state flags covered by computed assertions:

- `header .wp-block-navigation .wp-block-navigation__submenu-icon { margin-left: 0; }`
- `.wp-block-navigation__responsive-container-open svg, ...close svg { fill: #D4000F; }`
- Two submenu hover pseudo-element `content: none` flags in `customizer-migrated.css`.
- Open drawer hover border-right flag in `customizer-migrated.css`.
- Added assertions for menu icon fill, submenu icon margin, and drawer hover border.

Verification:

- `pnpm compile:css`
- `pnpm test:visual`: 24 passed.

Resulting active frontend `!important` count: 41.

### Batch 24: Ecwid Price Cleanup

Attempted Ecwid shop typography cleanup:

- Tried replacing both `.grid-product__price` and `.grid-product__title-inner` with scoped non-important Ecwid selectors.
- Added `/shop/` computed assertions for product title and price typography.

Result:

- Kept `.grid-product__title-inner` as `!important`; without priority, mobile/tablet product titles fell back to `20px` and shop snapshots changed.
- Removed `!important` from `.grid-product__price` using a scoped Ecwid selector.

Verification:

- Initial `pnpm test:visual` failed on mobile/tablet shop snapshots and title font-size assertions.
- Restored only the title priority.
- `pnpm test:visual`: 27 passed.

Resulting active frontend `!important` count: 40.

### Batch 25: Theme JSON V3 Compatibility

Migrate the child theme to `theme.json` version 3 before moving more native block
styling into WordPress-generated styles.

- Changed `theme.json` from version 2 to version 3.
- Added `settings.typography.defaultFontSizes: false`.
- Without `defaultFontSizes: false`, WordPress 7.0 default font-size presets won for
  shared slugs such as `medium`, `large`, and `x-large`, changing route heights and
  failing visual tests.

Verification:

- Direct Lightning CSS frontend/editor compile.
- First schema-only `pnpm test:visual` failed with 12 regressions.
- Restored the child theme preset contract with `defaultFontSizes: false`.
- `pnpm test:visual`: 27 passed.

Resulting active frontend `!important` count: 40.

### Batch 26: Native Core Block Spacing

Move low-risk WordPress-native block spacing into `theme.json`, then remove only
the matching priorities that stayed visually equivalent.

Accepted:

- Set `settings.layout.contentSize` to `44rem`.
- Removed the duplicate legacy `--wp--style--global--content-size: 44rem`
  custom property.
- Added homepage assertions for constrained content width.
- Added `styles.blocks.core/columns.spacing.margin.bottom: "0"` and removed
  priority from `.wp-block-columns { margin-bottom: 0; }`.
- Added `styles.blocks.core/separator.spacing.margin.top/bottom: "20px"` and
  removed priority from `.wp-block-separator { margin: 20px 0; }`.
- Added homepage assertions for columns margin, group background padding, and
  separator margins.

Rejected and restored:

- Removing the broad constrained-content selector allowed content-authored widths
  such as `200px` and `66.66%` to survive, causing mobile route diffs. Keep
  `body .is-layout-constrained > :where(...) { max-width: 44rem !important; }`
  until the intended width model is redesigned.
- Moving Social Links padding to `theme.json` did not replace the list padding;
  computed padding became `16px`. Keep the priority for now and revisit through a
  `core/social-links` block stylesheet.
- Moving Group background padding to `theme.json` left horizontal padding at
  `16px`. Keep the priority for now and revisit through a `core/group` block
  stylesheet or a more specific block-support override.

Verification:

- Direct Lightning CSS frontend/editor compile after each accepted batch.
- `pnpm test:visual`: 27 passed.
- Wallace compiled CSS after the accepted batch: 160 rules, 230 selectors, 432
  declarations, 37 compiled `!important` declarations.

Resulting active frontend `!important` count: 38.

### Batch 27: Core Block Stylesheet Ownership

Move narrow WordPress-native block exceptions out of the legacy catch-all CSS and
into block-owned stylesheets registered with `wp_enqueue_block_style()`.

Added block stylesheets:

- `styles/blocks/core-group.css` for `core/group`.
- `styles/blocks/core-social-links.css` for `core/social-links`.

Moved out of `legacy-frontend.css`:

- `.wp-block-group.has-background { padding: 0 !important; }`
- `.wp-block-social-links { margin-bottom: 1rem; padding-left: 0 !important; }`

Result:

- Ownership is now aligned with the WordPress block stylesheet path instead of the
  legacy global stylesheet.
- Both rules still require `!important`; removing priority in the block
  stylesheet allowed WordPress-generated/core padding to win and failed computed
  assertions.
- Comments in the block stylesheets document these as block-support/core padding
  exceptions.

Verification:

- Direct Lightning CSS frontend/editor compile.
- `php -l functions.php`.
- CSS Prettier check for touched CSS files.
- `pnpm test:visual` had one unstable desktop Mary Barbour screenshot after 26
  passes; rerunning the single failing desktop snapshot passed.

Resulting active frontend `!important` count: 38, now distributed as:

- `legacy-frontend.css`: 28.
- `core-group.css`: 1.
- `core-social-links.css`: 1.
- `customizer-migrated.css`: 8.

### Batch 28: Core Navigation Stylesheet Ownership

Move WordPress Navigation block styling out of the legacy catch-all and migrated
Customizer files into a block-owned stylesheet registered with
`wp_enqueue_block_style()`.

Added block stylesheet:

- `styles/blocks/core-navigation.css` for `core/navigation`.

Moved out of `legacy-frontend.css`:

- Header Navigation typography, spacing, submenu icon, open/close icon, submenu
  link padding, and hover pseudo-element rules.

Moved out of `customizer-migrated.css`:

- Large-screen submenu border/padding/hover rules.
- Mobile drawer padding, item border, hover, and nested-depth rules.
- The brittle generated open-button selector remains for now, but is now isolated
  in the Navigation block stylesheet for later replacement.

Result:

- Navigation is now owned by the WordPress block stylesheet path rather than a
  mix of legacy CSS and migrated Customizer CSS.
- The two remaining Navigation priorities are documented in
  `core-navigation.css` as WordPress core Navigation padding exceptions.
- Global compiled CSS is smaller because block-owned CSS is no longer bundled
  into `styles/dist/frontend.min.css`.

Verification:

- Direct Lightning CSS frontend/editor compile.
- `pnpm test:visual`: 27 passed.
- Wallace compiled global CSS after the move: 135 rules, 201 selectors, 381
  declarations, 33 compiled `!important` declarations.

Resulting active frontend `!important` count: 38, now distributed as:

- `legacy-frontend.css`: 26.
- `core-group.css`: 1.
- `core-navigation.css`: 2.
- `core-social-links.css`: 1.
- `customizer-migrated.css`: 8.

### Batch 29: Customizer Quarantine Decomposition

Remove the temporary `customizer-migrated.css` quarantine file now that its
remaining rules have clear owners.

Moved to vendor overrides:

- Ecwid/Facebook Messenger widget hiding -> `vendor-overrides/ecwid.css`.
- EmailOctopus giveaway form rules -> `vendor-overrides/emailoctopus.css`.
- Jetpack slideshow line-height carryover -> `vendor-overrides/jetpack-slideshow.css`.

Moved to components:

- Footer copyright color -> `components/footer.css`.

Result:

- Deleted `styles/vendor-overrides/customizer-migrated.css`.
- `vendor-overrides/index.css` now imports specific vendor files.
- `components/index.css` now imports `footer.css`.
- Historical batch notes still mention `customizer-migrated.css` where relevant,
  but current ownership no longer uses that file.

Verification:

- Direct Lightning CSS frontend/editor compile.
- `pnpm test:visual`: 27 passed.
- Wallace compiled global CSS remains 135 rules, 201 selectors, 381
  declarations, 33 compiled `!important` declarations.

Resulting active frontend `!important` count: 38, now distributed as:

- `legacy-frontend.css`: 26.
- `core-group.css`: 1.
- `core-navigation.css`: 2.
- `core-social-links.css`: 1.
- `vendor-overrides/ecwid.css`: 1.
- `vendor-overrides/emailoctopus.css`: 6.
- `vendor-overrides/jetpack-slideshow.css`: 1.

### Batch 30: Jetpack Slideshow Block Ownership

Move active Jetpack slideshow overrides out of the global compiled frontend CSS
and into a block-owned stylesheet registered for `jetpack/slideshow`.

Moved out of `legacy-frontend.css`:

- Slideshow previous/next display and offsets.
- Slideshow image width, max dimensions, and `object-fit`.
- Slideshow desktop figure height/min-height.
- Slideshow pagination positioning and hiding.
- Slideshow margin reset.

Moved out of vendor overrides:

- The migrated slideshow `display`/`line-height` rule from
  `vendor-overrides/jetpack-slideshow.css`.

Result:

- Added `styles/blocks/jetpack-slideshow.css`.
- Registered it with `wp_enqueue_block_style( 'jetpack/slideshow', ... )`.
- Added dependencies on `jetpack-block-slideshow` and
  `jetpack-swiper-library` so the stylesheet is explicitly ordered after the
  plugin block assets.
- Deleted `styles/vendor-overrides/jetpack-slideshow.css` and removed its
  import from `vendor-overrides/index.css`.
- Added computed assertions for the moved slideshow contracts because visual
  snapshots intentionally mask the slideshow.
- The live slideshow figure remains `display: block`; the old `display: inline`
  selector was not the actual computed contract after hydration. The image
  remains `display: inline`, `line-height: 0`, `object-fit: cover`, and has
  unset max dimensions.
- Removed six Jetpack priorities after moving to block ownership:
  `line-height`, slideshow/image margin reset, image width, image max-width,
  image max-height, and image `object-fit`.
- Attempted to remove the pagination `display: none !important`, but the
  homepage contract failed because Jetpack restored the pagination as
  `display: flex`. Reverted that declaration only.

Verification:

- Direct Lightning CSS frontend/editor compile.
- `php -l functions.php`.
- CSS/TS Prettier checks for touched files.
- Targeted homepage cascade contract: 3 passed.
- `pnpm test:visual`: 27 passed before the next batch.

Resulting active frontend `!important` count: 32, now distributed as:

- `legacy-frontend.css`: 19.
- `core-group.css`: 1.
- `core-navigation.css`: 2.
- `core-social-links.css`: 1.
- `styles/blocks/jetpack-slideshow.css`: 2.
- `vendor-overrides/ecwid.css`: 1.
- `vendor-overrides/emailoctopus.css`: 6.

### Batch 31: Ecwid And Messenger Ordering

Make the current Ecwid override order explicit without changing CSS behavior.

Result:

- Added a `wp_enqueue_scripts` callback that appends `ecwid-css` to the
  existing `estory-style` dependencies when both handles are enqueued.
- Kept the Messenger hide rule in `vendor-overrides/ecwid.css` for now because
  the widget is injected by the Ecwid/Facebook integration and should be
  disabled in Ecwid/Facebook settings before the CSS hide is treated as
  permanent.
- Added a `/shop/` computed assertion that verifies
  `.ec-fbmessenger-chat { display: none; }` only when the remote widget is
  actually present.

Verification:

- `php -l functions.php`.
- Targeted shop Ecwid cascade contract: 3 passed.
- `pnpm test:visual`: 27 passed before the next batch.

Resulting active frontend `!important` count: 32.

### Batch 32: EmailOctopus Coverage Before Cleanup

Add regression coverage for the EmailOctopus rules before changing the remaining
form priorities.

Result:

- Extended `/edu-giveaway/` assertions to cover label display/weight and confirm
  the local legacy EmailOctopus stylesheet is not present.
- Added a homepage EmailOctopus contract for the second rendered form ID,
  `3637e2c8-ff87-11ef-8123-45a2d1a97169`.
- The script element itself is transient after hosted-form hydration, so tests
  assert the stable hydrated `[data-form]` container instead.

Verification:

- Targeted EmailOctopus contracts: 6 passed.
- `pnpm test:visual`: 30 passed.

Resulting active frontend `!important` count: 32.

### Batch 33: Clear Button Local Component Priority

Remove the transparent background priority from the local `.clear-button`
button variant by targeting the rendered WordPress button/background-class
shape.

Rationale:

- A plain `.clear-button .wp-block-button__link` selector regressed because
  WordPress applies generated background preset classes to the rendered link.
- A scoped `render_block` filter now removes background preset classes from
  `core/button` links when the saved block class includes `clear-button`. That
  lets the component CSS win without fighting WordPress's generated preset
  `!important` rule.
- `.mt0`, `.mb05`, and the Ecwid product title were inspected in the same slice
  but kept documented because they are still blocked by broader global margin
  priorities or Ecwid runtime CSS.

Changed:

- `functions.php` normalizes rendered `core/button.clear-button` markup by
  removing `has-background` and `has-*-background-color` from the link while
  leaving text-color classes intact.
- `.clear-button .wp-block-button__link`,
  `.wp-block-button.clear-button .wp-block-button__link.has-background`, and
  `.wp-block-button.clear-button .wp-block-button__link:after` now set
  `background-color: transparent` without `!important`.

Verification:

- Direct Lightning CSS compile for frontend and editor.
- Full Playwright visual suite after the change.

Resulting active frontend `!important` count: 31.

### Batch 34: Utility And Ecwid Ownership Split

Moved obvious utility classes and Ecwid storefront rules out of
`legacy-frontend.css` without changing intended behavior.

Moved to `styles/utilities/index.css`:

- `.grid`, `.m-auto`, `.w-100`, `.w-50-m`, `.pr0`, `.mt0`, `.p1`, `.p2-m`,
  `.pl1`, `.pr1`, `.no-gap`, `.vw-100`, and `.lh0`.

Moved to `styles/vendor-overrides/ecwid.css`:

- Ecwid heading/product-title typography.
- Ecwid grid product price sizing.
- Ecwid grid product title sizing.

Verification:

- Prettier check.
- Direct Lightning CSS compile for frontend and editor.
- Full Playwright run had desktop full-page screenshot stabilization flakes on
  `/` and `/herstories/mary-barbour/`, but all computed contracts passed.
- Targeted rerun of the two failing desktop snapshots passed.

Resulting active frontend `!important` count: 31.

### Batch 35: Herstories Bio Page-Type Split

Moved Herstories biography page rules out of `legacy-frontend.css` and into
`styles/page-types/herstories-bios.css`.

Moved:

- `.active-dates` sizing, spacing, nested list spacing, and strong typography.
- `.fun-facts` current list-item and custom bullet styling.
- Release-watch notes for nested `.fun-facts ul` selectors that still have no
  rendered/content hits on the covered Mary Barbour route.

Verification:

- Prettier check for touched CSS files.
- Direct Lightning CSS compile for frontend and editor.
- Mary Barbour visual snapshots passed on mobile and tablet; desktop hit the
  known full-page screenshot stabilization failure in the three-project run.
- Targeted desktop Mary Barbour snapshot rerun passed.
- Herstory computed-style contracts passed on desktop, tablet, and mobile.

Resulting active frontend `!important` count: 31.

### Batch 36: Vendor Override Rationalization

Isolated and documented retained vendor/plugin priorities instead of removing
rules that still lose to hydrated or runtime plugin CSS.

Changed:

- Moved the Messenger widget hide from `styles/vendor-overrides/ecwid.css` into
  `styles/vendor-overrides/messenger.css`.
- Added comments to the retained Jetpack slideshow pagination-hide priority.
- Disabled the unused Jetpack `swiper-pagination-bullets` positioning priority
  under release-watch because covered routes render custom pagination instead.
- Added comments to retained Ecwid storefront typography/product-title
  priorities.
- Added comments to retained EmailOctopus hosted-form priorities and kept the
  focus styling as a TODO until the hosted form editor can be reviewed.
- Strengthened Playwright contracts so `/shop/` must actually render the
  Messenger widget before the hide rule counts as covered, and the
  EmailOctopus routes must render hydrated v2 forms rather than legacy local
  plugin CSS.

Evidence:

- Jetpack slideshow is now block-owned through
  `styles/blocks/jetpack-slideshow.css`; only pagination hiding still requires
  priority on covered routes.
- Ecwid product-title sizing still loses to runtime storefront styles without
  priority, even when a normal-priority rule is injected last.
- Ecwid Messenger currently renders on `/shop/`, and the theme intentionally
  hides it until the widget can be disabled through Ecwid/Facebook/Messenger
  settings.
- EmailOctopus giveaway grid, wrapper spacing, and powered-by hiding still lose
  to the hosted hydrated form CSS without priority.
- Ecwid heading selectors are retained but not covered by `/shop/` because that
  route currently renders no matching heading/product-detail title elements.

Resulting active frontend `!important` count: 30.

### Checkpoint: Design-System Pivot Anchor

The current CSS split is an intentionally imperfect checkpoint before pivoting
away from legacy-cascade preservation. It is not visually green: recent
snapshot runs showed stable mobile/tablet height differences while computed
contracts continued to pass.

This checkpoint exists as a rollback anchor. Future cleanup should treat the
current accepted screenshots as visual reference material and rebuild the CSS
around modern WordPress theming, `theme.json`, block-owned styles, explicit
design tokens, and intentional component/page-type ownership.

## Current State

- `base/elements.css`: 5 active `!important` declarations, excluding comments.
- `core-group.css`: 1 active `!important` declaration, excluding comments.
- `core-navigation.css`: 2 active `!important` declarations, excluding comments.
- `core-social-links.css`: 1 active `!important` declaration, excluding comments.
- `jetpack-slideshow.css`: 1 active `!important` declaration, excluding comments.
- `layout/index.css`: 8 active `!important` declarations, excluding comments.
- `utilities/index.css`: 2 active `!important` declarations, excluding comments.
- `vendor-overrides/ecwid.css`: 3 active `!important` declarations, excluding comments.
- `vendor-overrides/emailoctopus.css`: 6 active `!important` declarations, excluding comments.
- `vendor-overrides/messenger.css`: 1 active `!important` declaration, excluding comments.
- Total active frontend scope: 30.

Dedicated vendor/plugin plan:

- Use `docs/vendor-plugin-css-override-triage-plan.md` before touching Jetpack,
  Ecwid, EmailOctopus, or Messenger rules. Plugin settings/design APIs,
  block-owned CSS, and theme-owned ordered stylesheets must be tried before
  keeping scoped `!important` rules or hiding/dequeuing plugin output.

Next likely candidates:

- EmailOctopus structural overrides should stay documented unless a stronger hydrated selector is proven against the injected vendor CSS.
- Social Links, Group background padding, and Navigation are now block-stylesheet-owned documented exceptions.
- Customizer-migrated CSS has been fully decomposed into real owner files.
- Previous-next and shop-intro gap still depend on the broad flex gap reset.
- `.mb05` should stay documented until the broader heading/flow margin rules are refactored.
- Jetpack slideshow and Ecwid title rules are proven plugin exceptions for now.

Keep nav layout, global layout, `.alignwide`, `.is-layout-*`, Jetpack slideshow, and Ecwid out of broad batches.
