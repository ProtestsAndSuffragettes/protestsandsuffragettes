# Vendor Plugin CSS Override Triage Plan

## Objective

Eliminate `!important` from Jetpack, Ecwid, EmailOctopus, and Messenger styling
where the plugin or WordPress gives us a better ownership path.

Do not treat vendor CSS as one bucket. Each plugin must be triaged through the
same escalation ladder and must record evidence before using the last two
options.

## Escalation Ladder

Use these options in order. Options 4 and 5 are allowed only when the earlier
options demonstrably do not work for the specific selector family.

1. Plugin settings or design APIs.
   Check the plugin admin UI, hosted service UI, shortcode/block settings, and
   documented hooks before writing CSS.
2. WordPress-owned block styling.
   Use `theme.json` where it can express the style. Use
   `wp_enqueue_block_style()` when the plugin registers a block and CSS is still
   needed.
3. Theme-owned override stylesheet.
   Use scoped selectors and enqueue order/dependencies so the theme stylesheet
   loads after the plugin stylesheet it overrides.
4. Scoped, documented `!important`.
   Keep only the smallest possible selector and record why plugin settings,
   block styles, and normal cascade ordering failed.
5. Disable, dequeue, or hide output.
   Use only when the output is unwanted and the plugin cannot disable it at the
   source. Do not dequeue a plugin stylesheet unless we are prepared to own every
   affected visual contract it provided.

## Evidence Required Before Options 4 Or 5

Before keeping `!important`, hiding injected output, or dequeuing plugin assets,
record the following in `docs/frontend-css-important-triage.md`:

- The plugin setting, hosted-service setting, shortcode setting, or block
  control that was checked.
- The registered block name if one exists, or the evidence that the current
  markup is not block-owned.
- The relevant stylesheet handle and load order.
- The non-`!important` selector or enqueue dependency that was tried.
- The Playwright route and computed-style assertion that proved the weaker
  option failed.

For vendor rules, screenshots alone are not enough. The test route must actually
render the vendor output being changed.

## Current Vendor Scope

Current authored frontend vendor/plugin `!important` usage:

- Jetpack slideshow: 1 declaration in
  `styles/blocks/jetpack-slideshow.css`.
- Ecwid storefront: 3 declarations in
  `styles/vendor-overrides/ecwid.css`.
- Messenger widget: 1 hide declaration in
  `styles/vendor-overrides/messenger.css`.
- EmailOctopus: 6 declarations in
  `styles/vendor-overrides/emailoctopus.css`.

## Jetpack Slideshow

### Local Evidence

- Jetpack registers the Slideshow block as `jetpack/slideshow` in
  `app/public/wp-content/plugins/jetpack/_inc/blocks/slideshow/block.json`.
- The block render callback in
  `app/public/wp-content/plugins/jetpack/extensions/blocks/slideshow/slideshow.php`
  loads assets through Jetpack's block asset loader.
- Jetpack's block asset loader enqueues the block stylesheet with the
  `jetpack-block-slideshow` handle.
- Jetpack also enqueues Swiper with the `jetpack-swiper-library` handle for
  frontend slideshow behavior.
- Jetpack slideshow CSS is queued dynamically during block render, so relying
  on the monolithic child stylesheet order is weaker than a block-owned
  stylesheet with explicit dependencies.
- Current theme rules target `.wp-block-jetpack-slideshow`,
  `.wp-block-jetpack-slideshow_image`, slideshow buttons, and slideshow
  pagination.
- Earlier Batch 21 removal attempts caused visual/computed regressions for
  pagination and image fitting.
- Phase 4 moved the current Jetpack slideshow rules to the block-owned
  `styles/blocks/jetpack-slideshow.css` stylesheet and enqueued it with
  dependencies on `jetpack-block-slideshow` and `jetpack-swiper-library`.
- Phase 6 confirmed covered routes render Jetpack custom pagination, not the
  `swiper-pagination-bullets` variant. The unused positioning rule is disabled
  under release-watch, leaving only pagination hiding as an active priority.

### Preferred Path

1. Check whether the slideshow block settings can express the desired behavior:
   alignment, autoplay, image sizing, and pagination behavior.
2. Keep the current block-owned stylesheet registered with:

   ```php
   wp_enqueue_block_style(
       'jetpack/slideshow',
       array(
           'handle' => 'protestsandsuffragettes-jetpack-slideshow',
           'src'    => get_stylesheet_directory_uri() . '/styles/blocks/jetpack-slideshow.css',
           'path'   => get_stylesheet_directory() . '/styles/blocks/jetpack-slideshow.css',
           'deps'   => array( 'jetpack-block-slideshow', 'jetpack-swiper-library' ),
       )
   );
   ```

3. Keep the disabled bullet-positioning rule on release-watch for a cycle, then
   delete it if no Jetpack slideshow regression appears.
4. Try removing the remaining pagination-hide priority only if a future Jetpack
   block setting or stronger block-owned selector proves equivalent behavior.

### Last-Resort Rules

Keep scoped `!important` only if the Jetpack block stylesheet, registered after
`jetpack-block-slideshow`, still loses to Jetpack or inline runtime styles.

Do not dequeue Jetpack slideshow assets unless we replace both the visual CSS and
runtime slideshow behavior. That is Option 5 and should be treated as a separate
feature decision.

### Test Coverage

- Keep slideshow assertions on any route that renders
  `.wp-block-jetpack-slideshow`.
- Assert image `object-fit`, image max dimensions, pagination visibility, and
  button placement after each batch.
- If the current visual suite has only one slideshow route, add another route
  before broad Jetpack cleanup.

## Ecwid Storefront

### Local Evidence

- Ecwid enqueues its frontend stylesheet as `ecwid-css` from
  `app/public/wp-content/plugins/ecwid-shopping-cart/ecwid-shopping-cart.php`.
- The rendered `/shop/` stylesheet order currently places `ecwid-css` before
  the child theme stylesheet, so the theme can already override the local Ecwid
  stylesheet. An explicit dependency stylesheet is still preferred because it
  documents that contract and is more resilient to optimizers.
- Rendered storefront markup uses Ecwid-owned containers such as
  `html#ecwid_html body#ecwid_body .ec-store`.
- Current theme styling still overrides Ecwid typography and product-title
  sizing, including one proven `!important` on `.grid-product__title-inner`.
- Batch 24 proved the product price override can work without priority when
  scoped under Ecwid markup, while product title sizing still lost without
  priority.
- Phase 6 local probe confirmed a last-loaded normal-priority title-size rule
  still lost, while the same selector with priority won.
- Phase 6 `/shop/` coverage confirmed `.ec-fbmessenger-chat` currently renders,
  so the Messenger hide rule is no longer treated as an unobserved selector.
- The Ecwid heading selectors currently do not render on `/shop/`, so their
  priority declarations are retained but not yet covered by a computed-style
  contract.
- Local Ecwid code exposes a Chameleon design hook,
  `ecwid_chameleon_settings`, for store colors/fonts. Treat that hook and the
  hosted Ecwid design UI as Option 1 checks before writing more CSS.

### Preferred Path

1. Check Ecwid's storefront design settings first, including typography,
   product grid/card settings, and any custom CSS/design controls in the Ecwid
   admin or hosted dashboard.
2. If the desired style is not configurable, create a theme-owned Ecwid override
   stylesheet, for example `styles/vendor-overrides/ecwid-storefront.css`.
3. Enqueue that file after `ecwid-css` instead of relying only on the global
   bundled stylesheet order.
4. Scope selectors under Ecwid's rendered context:

   ```css
   html#ecwid_html body#ecwid_body .ec-store .grid-product__title-inner {
     font-size: clamp(1rem, 2vw, 20px);
   }
   ```

5. Try a second pass with Ecwid-specific parent context before keeping
   `!important`; the existing price cleanup shows this can work for some rules.

### Last-Resort Rules

Keep `!important` only for Ecwid runtime styles that are injected after the
theme-owned Ecwid stylesheet or applied inline by the storefront app.

Do not dequeue `ecwid-css` unless a dedicated storefront visual suite proves all
cart, product-grid, product-detail, minicart, and checkout-adjacent UI still
render acceptably.

### Test Coverage

- Keep `/shop/` or the current storefront route in the visual suite when
  changing Ecwid CSS.
- Assert product title font size, price font size, product grid width, and any
  styled storefront headings.
- Do not use routes that only contain an Ecwid placeholder; the rendered Ecwid
  app must be present.

## Messenger Widget

### Local Evidence

- The current rule is `.ec-fbmessenger-chat { display: none !important; }`.
- Phase 6 isolated the rule in `styles/vendor-overrides/messenger.css` so the
  Option 5 hide is not mixed into storefront typography rules.
- The selector suggests the widget is injected through Ecwid/Facebook Messenger
  integration rather than authored by the theme.
- Local plugin source did not expose a matching `.ec-fbmessenger-chat` selector.
  Rendered evidence points to Ecwid-hosted runtime code from
  `https://app.ecwid.com/script.js`.
- Local Ecwid admin data exposes an `fbmessenger` admin path, but the local
  WordPress code/options inspected so far do not expose a direct Messenger
  toggle.

### Preferred Path

1. Check Ecwid admin, Ecwid hosted dashboard, Facebook Messenger, and connected
   app settings for an option to disable the chat widget at the source.
2. If disabling is possible, remove the CSS rule and test routes that currently
   render `.ec-fbmessenger-chat`.
3. If the widget must remain disabled and no source setting exists, keep a
   dedicated `styles/vendor-overrides/messenger.css` file or a clearly labelled
   Messenger section in `styles/vendor-overrides/ecwid.css`.

### Last-Resort Rules

The current hide rule is Option 5. It may stay only with an explicit note that
the widget could not be disabled through Ecwid/Facebook/Messenger settings.

If CSS hiding remains necessary, keep it as a single documented rule. Do not
expand it into broader Ecwid container hiding.

### Test Coverage

- Add a computed assertion on a route where `.ec-fbmessenger-chat` actually
  appears before changing or removing the rule.
- If the widget is absent from all tested routes, do not count a passing visual
  run as proof that the rule is unnecessary.

## EmailOctopus

### Local Evidence

- The EmailOctopus plugin registers `emailoctopus/form-block`.
- The block render callback outputs the `[emailoctopus]` shortcode.
- The block's current assets are editor-only. The plugin registers legacy
  frontend assets, but current rendered pages do not include the local
  `emailoctopus_frontend` stylesheet.
- For current non-deprecated forms, the shortcode emits an external script tag
  with `data-form="{form-id}"`, and that hosted script hydrates the real form
  markup.
- The plugin admin exposes display placement settings and links to the hosted
  EmailOctopus form editor, but local PHP does not expose typography, field
  padding, powered-by, or one-column layout controls.
- Current overrides target the specific giveaway form ID
  `5e60a222-ff72-11ef-8552-6b8c59d486cb`.
- The homepage also renders a second EmailOctopus form ID,
  `3637e2c8-ff87-11ef-8123-45a2d1a97169`, so broad `.emailoctopus-form` rules
  must be tested outside `/edu-giveaway/`.
- Batches 3 and 17 proved that focus, grid, padding-wrapper, and powered-by
  rules lost without priority against the injected form styles.
- Phase 6 local probe confirmed normal-priority grid, wrapper-padding, and
  powered-by rules still lose after the hosted form hydrates. The focus rule
  remains a TODO because the hosted EmailOctopus form editor should be checked
  before accepting CSS priority as permanent.

### Preferred Path

1. Check the EmailOctopus hosted form editor first for layout, field spacing,
   focus, branding/powered-by, and hidden-column settings.
2. If the form editor can own the design, remove the equivalent CSS and retest
   `/edu-giveaway/`.
3. If the WordPress block wrapper needs spacing only, use
   `wp_enqueue_block_style( 'emailoctopus/form-block', ... )` for wrapper-level
   block CSS. Do not expect that to control hosted form internals after the
   external script hydrates.
4. For hydrated form internals, keep a theme-owned vendor stylesheet scoped to
   the exact form ID:

   ```css
   [data-form="5e60a222-ff72-11ef-8552-6b8c59d486cb"] .emailoctopus-form {
     /* form-specific overrides only */
   }
   ```

5. Re-test stronger hydrated selectors before retaining the current priorities.

### Last-Resort Rules

Keep `!important` only for hosted-form CSS that is injected after our theme CSS
and cannot be changed in the EmailOctopus form editor.

The powered-by rule may also be an account/product-plan issue. Do not assume CSS
is the legitimate long-term fix until the hosted EmailOctopus settings have been
checked.

### Test Coverage

- `/edu-giveaway/` must remain in the suite for EmailOctopus changes.
- Add a lighter homepage assertion before changing broad `.emailoctopus-form`
  rules, because `/` currently renders a different EmailOctopus form.
- Assert the current pages are using hydrated v2
  `[data-form][data-version="2"]` containers and not the local
  `emailoctopus_frontend` legacy stylesheet.
- Assert one-column layout, hidden second column, powered-by visibility, input
  padding, focus border, focus box shadow, submit padding, and submit radius.
- Wait for the hosted form script to hydrate before taking screenshots or
  computed-style snapshots.

## Batch Order

1. Add missing vendor route/assertion coverage before changing each vendor.
2. Jetpack: delete the disabled bullet-positioning release-watch rule after a
   cycle, and retry the remaining pagination-hide priority only if a future
   block setting or stronger block-owned selector proves equivalent behavior.
3. Ecwid: review storefront design settings or the `ecwid_chameleon_settings`
   hook, then retry the remaining typography/title priorities.
4. Messenger: verify whether the widget can be disabled in Ecwid/Facebook
   settings before treating the CSS hide as permanent.
5. EmailOctopus: check the hosted form editor and then retry stronger
   form-scoped selectors for the six remaining priorities.

Run direct Lightning CSS compile or `pnpm compile:css`, then `pnpm test:visual`
after every accepted CSS batch. Use `pnpm analyze:css` when a batch should
reduce compiled selector/declaration/priority count.
