# PNS Video Banner Block Plan

Created: 2026-06-27

Dex tracker root: `829hnr81` - `PNS custom blocks plugin`

Sibling plan: `docs/jobs/2026-06-24-pns-custom-blocks-plugin-plan.md`

## Goal

Add a portable `pns/video-banner` block to the project-owned `pns-blocks`
plugin. The block should provide a Cover-inspired video banner for PNS page
headers and feature banners while adding the accessibility and control behavior
that the core Cover block does not currently provide.

This is a media/content banner block. It is not related to the completed
cross-site CTA/navigation banner work.

The first deliverable should support:

- Plugin path: `app/public/wp-content/plugins/pns-blocks/`.
- Block name: `pns/video-banner`.
- Rendering model: PHP-rendered dynamic wrapper plus a small frontend
  controller; saved content remains the nested `InnerBlocks` content.
- Content model: nested `InnerBlocks` for heading and optional hook/body text.
- Media model: video background with poster/still image fallback.
- Motion model: honour `prefers-reduced-motion: reduce` by starting paused or
  poster-first.
- Control model: unintrusive pause/play button, defaulting to bottom right, with
  exact positioning available.
- Colour model: optional colour wash using theme palette presets or a custom
  colour picker.

## Shared Plugin Boundary

This plan uses the same `pns-blocks` plugin scaffold as the Ecwid product-grid
plan. Whichever block stream starts first must complete the shared scaffold task
instead of creating a one-off plugin structure.

Shared Dex task:

- `45yn1av3` - `CB1: Scaffold project-owned blocks plugin`

The shared scaffold must create extensible conventions for multiple blocks:

```text
app/public/wp-content/plugins/pns-blocks/
  pns-blocks.php
  includes/
    Blocks.php
    Assets.php
  blocks/
    commerce/
      ecwid-product-grid/
    media/
      video-banner/
  README.md
```

The plugin owns block registration, editor scripts, saved/rendered markup
contracts, frontend behavior, reusable block helpers, and self-contained block
styles. The block should rely on WordPress/theme.json design artifacts such as
preset CSS variables and block supports rather than project-global theme CSS.
Themes may tune the block through standard theme.json presets and scoped block
overrides, but the component should remain portable into a generic plugin.

The video-banner work belongs under the `media` family. It must not share
commerce/Ecwid data helpers, renderers, or release assumptions.

## Locked Decisions

These choices are locked for v1 implementation:

| Question | Decision |
| --- | --- |
| First rollout target | Build and verify on a test page or test pattern first, then migrate the Educational Resources banner after the block is proven. Do not migrate all banners in the first pass. |
| Source of truth | Audit with WP-CLI before editing. Do not mutate page content, synced patterns, templates, or template parts until the live owner is confirmed and exported for rollback. |
| Media requirements | Require uploaded MP4 plus poster/still image for v1. WebM can be added later if performance testing justifies it. Treat GIF as a legacy source, not as a fallback path. |
| Reduced motion behavior | For `prefers-reduced-motion: reduce`, show the poster/still state and keep the video paused unless the visitor explicitly presses play. |
| Text model | Use `InnerBlocks` with a light template: heading plus optional paragraph/hook text. Editors may add another paragraph or button when a banner legitimately needs it. |
| Rendering model | Use a PHP-rendered dynamic wrapper plus frontend `view.js`. Implementation found a concrete normalization requirement: the live Cover-to-video migration needs server-owned wrapper markup while preserving nested content as saved `InnerBlocks`. |
| Colour wash | Support theme palette colours, custom colour picker values, and a separate opacity slider. Do not add gradient support in v1. |
| Pause/play positioning | Default bottom right. Provide preset positions plus inset controls for exact block/inline positioning. |
| Styling ownership | Plugin owns self-contained block styles using WordPress/theme.json design artifacts. Do not require global theme CSS for the component to work. |
| Button treatment | Use a custom icon-style pause/play button with an accessible label. Do not use native video controls/chrome. |

## Current Evidence

- This site currently has no project-owned custom block types in the child
  theme. Existing block registration is WordPress/plugin/database-owned.
- The existing custom-block plan already targets `pns-blocks` as the
  project-owned plugin path.
- Core Cover is a useful reference primitive because it already models image or
  video background, overlay colour, focal point, min height, content position,
  and nested content.
- Local WordPress 7.0 core Cover saves video backgrounds with `autoplay`,
  `muted`, `loop`, and `playsinline`; see
  `app/public/wp-includes/js/dist/block-library.js`.
- Core Cover CSS includes a `prefers-reduced-motion: reduce` rule for parallax
  background attachment, but it does not pause or suppress video playback; see
  `app/public/wp-includes/blocks/cover/style.css`.
- Core Cover does not render a frontend pause/play control for background
  videos.
- 2026-07-04 implementation evidence: `pns/video-banner` is registered from
  `app/public/wp-content/plugins/pns-blocks/build/blocks/media/video-banner`.
  The block is PHP-rendered from `render.php`, saves only inner content, and
  enqueues block-local editor/style CSS plus `view.js`.
- 2026-07-04 migration evidence: draft page `6118` (`Educational Resources`)
  now uses `pns/video-banner` as its first block, with the supplied MP4,
  original GIF poster, original inner copy, focal point, 80vh height, and 80%
  colour wash preserved. Rollback export:
  `docs/jobs/live-adoption-db-backups/2026-07-04-page-6118-before-video-banner.json`.
- 2026-07-06 release-gate evidence: focused plugin JS/style lint passed,
  PHP syntax passed for video-banner/plugin files, `wp-scripts build` passed,
  WP-CLI confirmed `pns/video-banner` is registered with PHP render callback
  plus editor/view/style handles, and WP-CLI render smoke confirmed draft page
  `6118` renders the wrapper, video, pause/play toggle, and no native video
  controls. Browser verification on temporary page `6229` confirmed the view
  script loads, the video has no native controls, pause/play updates state, and
  both play and pause preferences survive reload through site-wide persistence.
  The temporary page was deleted after verification.

## Block Type Decision

Build a custom block inspired by Cover rather than extending or filtering
`core/cover`.

Do not:

- edit WordPress core;
- register a replacement for `core/cover`;
- mutate core Cover saved markup with broad filters;
- depend on private Core Cover internals for frontend behavior.

Use:

- `block.json` and `register_block_type()` for registration;
- `MediaUpload` or `MediaPlaceholder` for video and poster selection;
- `FocalPointPicker` for object positioning;
- `InspectorControls` and block-editor color primitives for palette/custom
  colour selection;
- `InnerBlocks` for heading and optional hook/body text;
- a small frontend script for motion preference and pause/play behavior.

## Block Contract

Block name: `pns/video-banner`

Initial attributes:

```json
{
  "videoId": 0,
  "videoUrl": "",
  "posterId": 0,
  "posterUrl": "",
  "focalPoint": {
    "x": 0.5,
    "y": 0.5
  },
  "minHeight": 80,
  "minHeightUnit": "vh",
  "contentPosition": "center left",
  "overlayColor": "background",
  "customOverlayColor": "",
  "overlayOpacity": 70,
  "pauseControl": true,
  "pauseControlPosition": "bottom right",
  "pauseControlInsetBlock": "1rem",
  "pauseControlInsetInline": "1rem",
  "className": ""
}
```

The default inner block template should be:

```js
[
  [ 'core/heading', { level: 1, placeholder: 'Banner heading' } ],
  [ 'core/paragraph', { placeholder: 'Optional hook text' } ],
]
```

Use light locking only if editor testing proves it is needed. Editors should be
able to add another paragraph or button when a banner legitimately needs it.

## Rendered Markup Contract

The post content saves block attributes and nested `InnerBlocks` content. PHP
owns the frontend wrapper so migrated Cover content and future editor changes
render through one normalized component surface.

Target rendered front-end shape:

```html
<section
  class="wp-block-pns-video-banner pns-video-banner"
  style="--pns-video-banner-wash: var(--wp--preset--color--background); --pns-video-banner-wash-opacity: .7;"
>
  <video
    class="pns-video-banner__media"
    autoplay
    muted
    loop
    playsinline
    poster="..."
  >
    <source src="..." type="video/mp4">
  </video>
  <span class="pns-video-banner__wash" aria-hidden="true"></span>
  <div class="pns-video-banner__content">
    <!-- InnerBlocks content -->
  </div>
  <button class="pns-video-banner__toggle" type="button" aria-pressed="false">
    <span class="pns-video-banner__toggle-label">Pause animation</span>
  </button>
</section>
```

The exact button label/icon can be refined in implementation, but it must remain
keyboard reachable and screen-reader understandable.

## Frontend Behavior

The frontend controller should:

1. Find each `.pns-video-banner` instance independently.
2. Detect `window.matchMedia('(prefers-reduced-motion: reduce)')`.
3. If reduced motion is requested, pause the video before or immediately after
   initialization and mark the control as ready to play.
4. If reduced motion is not requested, allow normal muted loop autoplay.
5. Toggle only the current block instance when the pause/play button is clicked.
6. Keep the native video `controls` attribute absent.
7. Avoid global state unless later requirements need persistence.

If browser autoplay fails, the block should degrade to the poster/still frame
with readable text and a functional play button where possible.

## Colour Wash

Use a separate overlay layer, not video filters, for the colour wash.

Theme preset colours should resolve through WordPress preset variables, for
example:

```css
--pns-video-banner-wash: var(--wp--preset--color--background);
```

Custom colours should be serialized as CSS custom properties on the wrapper. The
wash opacity should be a separate numeric setting so editors can tune legibility
without changing the colour token.

## Pause/Play Positioning

Default:

- bottom right;
- inset `1rem` from block and inline edges;
- visually quiet until focused or hovered;
- high enough contrast against both light and dark footage.

Exact positioning should be stored as attributes and applied through custom
properties such as:

```css
--pns-video-banner-toggle-block-start: auto;
--pns-video-banner-toggle-block-end: 1rem;
--pns-video-banner-toggle-inline-start: auto;
--pns-video-banner-toggle-inline-end: 1rem;
```

Editor controls should expose preset positions first. Advanced unit controls can
follow once the preset behavior is stable.

## Phases

### VB0: Confirm Video Banner Sources and Requirements

Dex: `hx16lxq3`

1. Identify pages/templates/patterns that currently use animated GIF or
   Cover-like media banners.
2. Create or choose the test page/pattern target for the first implementation
   pass, then confirm the Educational Resources banner as the first real
   migration target after verification.
3. Export DB-backed records before any migration.
4. Confirm available video and poster/still assets.
5. Confirm whether the default heading plus optional paragraph/hook template is
   sufficient for the first target, and document any additional allowed nested
   blocks needed for that target.
6. Record rollback commands.

Acceptance:

- Source records, routes, media assets, and first rollout target are documented.
- No content migration starts before the source of truth is confirmed.

### VB1: Define Cover-Inspired Video Banner Block Contract

Dex: `og5h3mn3`

1. Add `blocks/media/video-banner/block.json` to the shared plugin scaffold.
2. Document attributes for media, poster, focal point, min height, content
   position, overlay colour/opacity, pause control, and positioning.
3. Define the `InnerBlocks` template.
4. Use the PHP-rendered wrapper contract. The concrete normalization need has
   appeared: migrated Cover blocks should preserve inner content while avoiding
   hand-built saved wrapper HTML.

Acceptance:

- `pns/video-banner` has a clear contract before implementation.
- The contract is distinct from `core/cover` and does not rely on core mutation.

### VB2: Build Video Banner Editor Experience

Dex: `x4ds64kj`

1. Implement editor controls with WordPress block editor primitives.
2. Add media selection for video and poster.
3. Add focal point, min height, content position, overlay colour/opacity, and
   pause-control positioning controls.
4. Add the heading/hook `InnerBlocks` template.
5. Provide a useful editor preview without requiring frontend autoplay.

Acceptance:

- The block appears in the inserter.
- Editor controls persist valid attributes.
- The editor does not produce block validation errors.

### VB3: Build Video Banner Frontend Render and Motion Controls

Dex: `8xbls9fe`

1. Render stable PNS-owned markup.
2. Add the no-native-controls background video.
3. Add poster/still fallback behavior.
4. Add per-instance pause/play behavior.
5. Honour `prefers-reduced-motion: reduce`.

Acceptance:

- Reduced-motion users do not get forced autoplay.
- The pause/play control works with pointer and keyboard interaction.
- The block remains readable when video playback fails.

### VB4: Style Video Banner and Colour Wash

Dex: `fskky57x`

1. Add self-contained plugin CSS for stable `.pns-video-banner*` selectors,
   relying on WordPress/theme.json design artifacts where possible.
2. Match the reference banner language: full-width media background, strong
   white heading text, readable hook/body copy, and tinted wash.
3. Verify desktop, tablet, and mobile layout.
4. Keep text from overlapping the button or escaping its content area.

Acceptance:

- Theme preset and custom colour washes render correctly.
- The default pause/play button position is unobtrusive and usable.
- Text remains readable across covered viewports.

### VB5: Migrate Target Video Banner Content

Dex: `gajuit98`

1. Export selected source records before edits.
2. Replace only the confirmed target banner markup with `pns/video-banner`.
3. Preserve heading, hook text, visual tint, focal point, height, and route
   behavior.
4. Keep rollback commands in the job doc.

Acceptance:

- Only confirmed target records are changed.
- Rollback is documented and practical.
- CTA/navigation banner work is untouched.

### VB6: Verify Video Banner Release Gate

Dex: `uf2y9vz8`

Run verification in this order:

1. PHP syntax for plugin files.
2. WP-CLI block registration smoke.
3. Editor smoke for controls and block validation.
4. Frontend browser checks for video autoplay, no native controls, pause/play,
   poster fallback, and reduced-motion behavior.
5. Active-theme CSS compile.
6. Playwright coverage on migrated routes.
7. Docs and Dex results update.

Acceptance:

- Visual checks pass or intentional deltas are documented.
- Reduced-motion and pause/play behavior are proven in browser checks.
- Dex has final evidence for each phase.

## Final Resolution

No implementation-blocking product decisions remain for v1. The implementation
settled the remaining details as follows:

1. Block inserter icon: inline SVG generated by the block editor script.
2. Pause/play control: text label with accessible state in v1; no native video
   chrome.
3. Preview: portable block `example` data plus a CSS-only generic preview
   surface, with no media/upload dependency.
4. First test surface: temporary public pages used for browser verification,
   then removed; draft page `6118` remains the first migrated real target.
