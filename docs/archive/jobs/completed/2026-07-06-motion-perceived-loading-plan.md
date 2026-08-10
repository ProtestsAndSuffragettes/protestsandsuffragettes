# Motion And Perceived Loading Plan

Created: 2026-07-06

Related plans:

- `docs/jobs/2026-07-06-layout-stability-plan.md`
- `docs/jobs/2026-06-28-theme-json-css-structure-rationalization-plan.md`

## Goal

Add restrained, accessible motion that makes normal WordPress navigation and
query-surface changes feel less abrupt without delaying content or masking real
layout instability.

This plan covers:

- Button, pagination, entry-navigation, and CTA micro-interactions.
- Incoming page/template reveal.
- Query result and card reveals for Search, News, and Herstories.
- Optional progressive enhancement for true page transitions.

Plugin-owned Herstory CTA source and scope decisions are tracked separately in
Dex task `73hwfmot` under `h3rs0t00`; this plan only covers theme-side motion
for surfaces that already have a confirmed owner.

## Non-Goals

- Do not build an SPA navigation layer.
- Do not delay rendering so animations are more noticeable.
- Do not animate layout dimensions, page height, scroll position, or large
  vendor-hydrated regions.
- Do not apply broad motion to all `.wp-block-group` or all query-loop children.
- Do not rely on Animations for Blocks plugin metadata as the main theme motion
  system.
- Do not animate focus outlines or hide keyboard focus states.

## Current Evidence

Current authored motion is minimal:

- No theme-owned motion system or `prefers-reduced-motion` policy was found in
  the standalone CSS sources.
- `styles/components/buttons.css` contains an inherited button pseudo-element
  `transition: all 0.5s`; new motion should use explicit properties instead.
- Playwright tests already disable animations in some visual checks, so new
  motion needs targeted enabled-motion checks as well as stable disabled-motion
  screenshots.

Useful existing hooks:

- `main.pns-template`
- `.pns-template-search`
- `.pns-search-results`
- `.pns-search-result`
- `.pns-template-news-archive`
- `.pns-news-more-section`
- `.pns-template-herstories-archive`
- `.pns-herstories-more-section`
- `.pns-query-pagination`
- `.pns-entry-navigation`
- `nav.pns-cross-site-banner-cta`

Gaps to close before broad card motion:

- News and Herstories cards are currently inline block-template structures, not
  named card components.
- Generic archive pagination still differs from the styled native pagination
  used by Search, News, and Herstories.
- Search can be disabled by theme option, so search motion must tolerate 404 and
  no-results states.
- Herstories archive sections can be removed server-side when there are too few
  entries; do not reserve or animate absent sections.

## Motion Principles

- Prefer opacity and small translate transforms.
- Keep durations short: roughly 120ms to 220ms for simple reveals, up to 280ms
  only for a deliberate page-level transition.
- Use easing that settles quickly, not bouncy or attention-grabbing motion.
- Define timing and easing as private CSS variables.
- Provide `prefers-reduced-motion: reduce` behavior for movement, reveals,
  scroll effects, scale/translate transforms, and delayed visibility. Simple
  color, underline, border, and shadow state changes do not need a separate
  reduced-motion branch.
- Keep hover/focus feedback immediate and keyboard-visible.
- Use explicit selectors and semantic hooks.
- Treat true outgoing page transitions as progressive enhancement only.

## Dex Tracking

Dex task state for this rollout is stored under the standalone theme root:

```text
app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex/tasks.jsonl
```

Use the tracker with the storage path explicitly:

```bash
dex --storage-path /Users/anachronistic/Local\ Sites/pns-stageing/app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex list --all
```

Parent task:

```text
apu24hdi - Plan motion and perceived-loading polish
```

Phase tasks:

| Phase | Dex ID     | Task                                               |
| ----- | ---------- | -------------------------------------------------- |
| 0     | `3hakasb4` | Audit current motion and support boundaries        |
| 1     | `ayybx956` | Normalize button and navigation micro-interactions |
| 2     | `esymm6fx` | Decide reduced-motion-safe template reveal         |
| 3     | `4otp80gk` | Add explicit search and archive card transitions   |
| 4     | `066z5anc` | Decide progressive page-transition enhancement     |
| 5     | `1jdpcasa` | Validate accessibility, performance, and visuals   |

## Phase 0: Motion Audit And Support Boundaries

Objective: define what the theme already does and what it must not animate.

Steps:

1. Search CSS and templates for `transition`, `animation`,
   `animationsForBlocks`, and `prefers-reduced-motion`.
2. Identify any plugin-provided animation attributes that should be removed,
   ignored, or left as content metadata.
3. List route surfaces where motion can be site-owned:
   Search, News, Herstories, entry navigation, and banner CTA.
4. List no-go surfaces:
   Ecwid storefront internals, EmailOctopus internals, focus outlines, and
   server-removed Herstories sections.
5. Confirm visual tests that disable animations so new tests avoid false
   negatives.

Acceptance criteria:

- Existing motion sources are documented.
- New motion has a clear CSS ownership file and import path.
- The distinction between immediate state affordances and movement/reveal motion
  is documented before implementation.

### Phase 0 Closeout - 2026-07-06

Dex task: `3hakasb4`.

Active runtime context:

- Active stylesheet/template: `protestsandsuffragettes-standalone`.
- Active saved global styles row:
  `wp-global-styles-protestsandsuffragettes-standalone` (`ID 5256`).
- Active motion-adjacent plugins include `animations-for-blocks`, `jetpack`,
  `ecwid-shopping-cart`, `emailoctopus`, `pns-blocks`, `performance-lab`, and
  `wp-fastest-cache`.

Current theme-authored motion sources:

- No standalone theme motion module currently exists.
- No authored standalone theme `prefers-reduced-motion` policy currently exists.
- `styles/components/buttons.css` is the only active theme-owned transition
  source found in authored CSS. It applies inherited `-webkit-transition`,
  `-o-transition`, and `transition: all 0.5s` to button pseudo-elements.
- `scripts/editor-blocks.js` uses `requestAnimationFrame()` for editor block UI
  timing only; it is not a frontend motion system.
- Filesystem templates `templates/page.html` and `templates/single-herstory.html`
  include footer `animationsForBlocks` metadata with `animation: none`.
- `synced-patterns/read-all-about-it.html` includes
  `animationsForBlocks: { "animation": "none", "variation": "in" }`.

Animations for Blocks evidence:

- The `animations-for-blocks` plugin is active.
- Plugin settings are:
  - `lazyloadAssets: true`
  - `lenis: "off"`
  - `ignoreReducedMotionPreference: false`
  - default animation `scale` / `in-x`, duration `800`, easing
    `ease-out-cubic`, offset `120`, once `true`.
- The plugin registers its AOS stylesheet for
  `screen and (prefers-reduced-motion: no-preference)` unless reduced-motion
  preference is explicitly ignored.
- Saved-content scan found `animationsForBlocks` metadata in:
  - `page`: 9 rows
  - `herstory`: 1 row
  - `wp_block`: 1 row
  - `wp_template`: 1 row
- Saved-content scan found rendered `data-aos` references in:
  - `page`: 9 rows
  - `herstory`: 1 row
- Animation values are not all inert:
  - `page`: 9 `slide*` hits
  - `herstory`: 1 `slide*` hit
  - `wp_block`: 1 `animation: none` hit
  - `wp_template`: 1 `animation: none` hit

Live route probe:

- `/` rendered `0` `[data-aos]` elements. Transition samples came from Jetpack
  slideshow controls.
- `/shenanigans/` rendered `4` `[data-aos]` elements, all on split-section copy
  surfaces with `slide-left` / `slide-right`.
- `/herstories/mary-barbour/` rendered `2` `[data-aos]` elements on Herstory
  facts/background copy surfaces.
- `/herstories/` rendered `4` `[data-aos]` elements on archive copy/card-like
  split-section surfaces.
- `/?s=suffrage` rendered `0` `[data-aos]` elements.
- `/news/` rendered `0` `[data-aos]` elements.

Visual-test interaction:

- The visual harness scrolls pages, calls `AOS.refreshHard()` /
  `AOS.refresh()`, forces `[data-aos]` elements into `aos-init aos-animate`,
  and injects CSS that zeroes animation and transition durations.
- Snapshot assertions call `toHaveScreenshot(..., { animations: "disabled" })`.
- Jetpack slideshow wrappers are force-reset to `transform: none` in visual
  stabilization helpers.
- Existing tests already exercise hover/focus-adjacent surfaces for buttons,
  navigation, mobile navigation, banner CTA, EmailOctopus, Ecwid, and Herstory
  sections, but there is no dedicated enabled-motion contract yet.

Site-owned motion surfaces for later phases:

- Shared buttons and `.wp-block-button__link`.
- Mobile navigation state hooks in `scripts/mobile-navigation.js` and
  `.pns-mobile-navigation*`, as long as focus and open/close state remain
  immediate.
- Native query pagination: `.pns-query-pagination`.
- Entry navigation: `.pns-entry-navigation`.
- Banner CTA: `nav.pns-cross-site-banner-cta`.
- Search results: `.pns-template-search`, `.pns-search-results`,
  `.pns-search-results__list`, `.pns-search-result`.
- News route shell and section hooks: `.pns-template-news-archive`,
  `.pns-news-more-section`.
- Herstories archive route shell and section hooks:
  `.pns-template-herstories-archive`, `.pns-herstories-more-section`.
- Page shell for a future incoming-only reveal, if approved:
  `main.pns-template`.

No-go and caution surfaces:

- Do not animate Ecwid internals (`.ecwid`, `.ec-store`) or EmailOctopus
  internals (`.emailoctopus-form`, hosted embed wrappers).
- Do not animate Jetpack slideshow internals; they already own Swiper/AOS-like
  transitions and visual tests forcibly stabilize them.
- Do not animate focus outlines, focus visibility, or keyboard affordances.
- Do not animate block dimensions, page height, scroll position, wide vendor
  hydration regions, or layout-reservation wrappers.
- Do not treat Animations for Blocks as the theme motion system. Existing AOS
  metadata remains content/plugin-owned unless a later migration explicitly
  removes it.
- Do not add broad selectors such as all `.wp-block-group`, all
  `.is-layout-flow > *`, or all Query Loop children.

Phase 1 boundary:

- The next implementation slice should remain micro-interactions only:
  buttons, native query pagination, entry navigation, and banner CTA.
- Replace `transition: all` with explicit property transitions.
- Add private motion tokens in the existing shared settings root so frontend
  and editor bundles both retain the definitions, then apply per-component
  selectors in the relevant component files.
- Reduced-motion handling is only required in this slice if we introduce actual
  movement such as scale, translate, smooth-scroll, or delayed visibility.
  Immediate color, underline, border, and shadow affordances should remain
  direct state changes with no separate reduced-motion branch.
- Search/News/Herstories card or page reveal motion should wait until Phase 1 is
  validated and News/Herstories have explicit card hooks where needed.

Validation lanes for later phases:

- Button/navigation micro-interactions: `pnpm test:visual:navigation` and
  `pnpm test:visual:fast`.
- Search, News, Herstories, and pagination: `pnpm test:visual:templates`, plus
  `pnpm test:visual:layout` if spacing or page height can change.
- Vendor guardrails: `pnpm test:visual:shop` / `pnpm test:visual:ecwid` for
  Ecwid and `pnpm test:visual:emailoctopus` for EmailOctopus.
- Broad landing gate after visible motion changes: `pnpm test:visual` plus the
  targeted lane for the touched surface.

## Phase 1: Button And Navigation Micro-Interactions

Objective: improve the most frequently touched controls before adding broader
page motion.

Recommended implementation direction:

- Start in `styles/components/buttons.css` and
  `styles/components/cross-site-banner-cta.css`.
- Replace inherited `transition: all` behavior with explicit transitions.
- Cover shared buttons, native query pagination, entry navigation, and banner
  CTA links.
- Keep focus-visible states immediate and highly visible.
- Do not animate the banner CTA notch, overlap, or clip-path.
- Prefer non-moving state changes for Phase 1. If a transform is added, it must
  be small, dimension-stable, and disabled for reduced-motion users.

Candidate properties:

- `box-shadow`
- `text-decoration-thickness`
- pseudo-element `background-color`
- `color` only where a component-owned link state actually changes color
- small `transform` only on controls whose dimensions are stable

Acceptance criteria:

- Hover and focus treatment feels intentional on buttons, pagination, entry
  navigation, and banner CTA links.
- Focus outlines are not delayed, faded, or hidden.
- Reduced-motion handling is present only for any transform/movement added in
  this phase; pure color, underline, border, or shadow transitions remain
  ordinary interactive states.
- No layout-sensitive CTA geometry moves.

### Phase 1 Closeout - 2026-07-06

Dex task: `ayybx956`.

Implemented:

- Added private interaction timing tokens in `styles/shared/settings.css` so
  both frontend and editor bundles retain `--pns--motion--duration-interaction`
  and `--pns--motion--easing-standard`.
- Replaced the inherited shared button `transition: all 0.5s` with explicit
  transitions for the actual interactive properties.
- Removed shared button hover offsets (`inset-block-start` /
  `inset-inline-start`) so button hover no longer moves layout geometry.
- Kept shared button color/background changes immediate. Only the visible
  hover affordances now animate: button `box-shadow` and the button pseudo
  background. This avoids animating non-interactive theme class changes such as
  dark-surface button inversion.
- Added explicit native query pagination and entry-navigation link transitions
  for `box-shadow` and `text-decoration-thickness`, with underline hover/focus
  affordances.
- Added explicit banner CTA link transition properties while keeping the banner
  notch, overlap, clip-path, and rect geometry static. The CTA underline is
  component-owned and uses targeted priority because core navigation CSS
  otherwise suppresses link decoration.
- Added no `prefers-reduced-motion` branch in this phase because no transform,
  translate, scale, scroll, delayed visibility, or reveal motion was introduced.

Test coverage added:

- Button tests now assert no active `transition-property: all`, stable hover
  geometry, stable logical inset values, and explicit button pseudo-element
  background transition.
- Native search pagination and single entry navigation tests now assert
  explicit transition properties and stable hover geometry.
- Banner CTA tests now assert explicit link transition properties, unchanged
  clip-path, unchanged banner/link geometry, and no transform.

Validation:

- `pnpm compile:css`
- `pnpm lint:css`
- `pnpm test:visual:navigation`
- `pnpm test:visual:templates`
- `pnpm exec playwright test tests/visual/frontend.spec.ts --grep "PNS section theme inverts copy and CTA buttons on dark backgrounds" --project=desktop`
- `pnpm exec playwright test tests/visual/frontend.spec.ts --grep "visual snapshot: home" --project=desktop`
- `pnpm test:visual:fast`

The first fast rerun hit a transient homepage snapshot failure caused by a
visible EmailOctopus PHP warning replacing the signup form. The isolated home
snapshot passed immediately afterward, and the final full fast rerun passed.

## Phase 2: Reduced-Motion-Safe Template Reveal

Objective: decide whether to add a small page-level incoming reveal after
micro-interactions are proven.

Recommended implementation direction:

- Add a theme-owned motion CSS module, for example
  `styles/components/motion.css`, imported by `styles/components/index.css`.
- Define private tokens:
  - `--pns--motion--duration-short`
  - `--pns--motion--duration-page`
  - `--pns--motion--easing-standard`
- If approved, apply a simple incoming reveal to `main.pns-template`.
- Use `opacity` plus a very small block-axis translate.
- Disable the reveal under `prefers-reduced-motion: reduce`.

Acceptance criteria:

- Normal page navigation shows a subtle incoming reveal.
- Reduced-motion users get no movement and no delayed content.
- Visual regression screenshots remain stable with animations disabled.
- No route waits for JavaScript before content appears.
- If the reveal is not approved, this phase records the decision and leaves the
  site on micro-interactions only.

### Phase 2 Closeout - 2026-07-07

Dex task: `esymm6fx`.

Decision: do not add a page-level incoming reveal for this closeout.

Rationale:

- Phase 1 already improved the high-frequency controls without moving layout.
- A site-wide `main.pns-template` reveal would affect every route and create a
  larger visual surface than this final polish phase needs.
- There is no user-visible defect that requires delayed visibility, opacity
  reveal, or translate movement on initial page render.

Result:

- No `main.pns-template` animation was added.
- No `prefers-reduced-motion` branch was required for Phase 2 because no
  page-level movement, delayed visibility, smooth scrolling, or transform was
  introduced.
- The site remains on immediate server-rendered WordPress navigation with
  component-level micro-interactions only.

### Phase 2 Trial Addendum - 2026-07-07

Follow-up decision: try a rollback-gated page-level reveal because the
component-only motion was not perceptible as page-transition polish.

Implemented:

- Added `pns_standalone_template_reveal_enabled()` in
  `inc/template-tags.php`.
- Added `body.pns-template-reveal-enabled` through the existing body-class
  helper when the trial is enabled.
- Added frontend-only `styles/components/template-reveal.css`, imported by
  `styles/components/frontend.css` only.
- Applied the reveal to
  `body.pns-template-reveal-enabled .wp-site-blocks > main.pns-template`.
- Used a short `220ms` opacity/translate reveal from `opacity: 0.92` and
  `translateY(0.35rem)` so content is never fully hidden.
- Added a `prefers-reduced-motion: reduce` branch that removes the animation
  and forces `opacity: 1` / `transform: none`.
- Updated visual-test readiness to wait for active template animations before
  geometry probes while keeping a dedicated enabled-motion contract test.

Rollback:

- Preferred local toggle: define `PNS_STANDALONE_TEMPLATE_REVEAL` as `false`
  before the theme loads.
- Code-level toggle: add
  `add_filter( 'pns_standalone_enable_template_reveal', '__return_false' );`.
- Full rollback: remove the body-class addition, the
  `template-reveal.css` import/file, and the dedicated visual assertion.

Validation:

- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone format`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css`
- `php -l app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/template-tags.php`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:fast`

Results:

- `test:visual:templates` passed: 23 passed.
- `test:visual:fast` passed: desktop 31 passed, mobile 12 passed.
- The first template-lane run exposed a geometry probe sampling during the new
  reveal; the final implementation keeps the reveal active for users while
  making contract tests wait for page-level animations before measuring static
  geometry.

## Phase 3: Search And Archive Cards

Objective: add motion where users perceive a result set or next-entry action.

Recommended implementation direction:

- Add explicit card classes before applying card motion to News and Herstories.
- Reuse existing Search classes directly:
  `.pns-search-results__list` and `.pns-search-result`.
- Add or normalize semantic hooks for News and Herstories cards rather than
  targeting all Query block children.
- Add a small card/media hover or focus treatment only if it does not disturb
  layout.
- Keep pagination and entry-navigation interaction polish in Phase 1 unless a
  route-specific issue remains.

Candidate surfaces:

- Search result rows.
- News archive cards in `.pns-news-more-section`.
- Herstories archive cards in `.pns-herstories-more-section`.

Acceptance criteria:

- Search results, news cards, and Herstories cards have consistent but not
  identical treatment where their layout differs.
- Pagination remains native WordPress query pagination.
- Keyboard focus is at least as visible as before.
- No broad query-loop selector animates unrelated blocks.

### Phase 3 Closeout - 2026-07-07

Dex task: `4otp80gk`.

Implemented:

- Added `styles/components/motion.css` and imported it through the component
  entrypoints.
- Added explicit archive-card hooks to the News and Herstories block templates:
  `.pns-archive-card.pns-news-card` and
  `.pns-archive-card.pns-herstory-card`.
- Synced the same hooks into the saved Local `wp_template` overrides for
  `home` (`ID 6186`) and `archive-herstory` (`ID 6138`) so runtime checks
  exercise the same contract as the filesystem templates.
- Added scoped transition treatment for:
  - Search result image links, title links, and term links.
  - News archive card image/title links.
  - Herstories archive card image/title links.

Guardrails:

- No broad Query Loop child selectors were introduced.
- No layout dimensions, page height, scroll position, or vendor-owned regions
  are animated.
- Motion is limited to non-moving state affordances: `filter`, `box-shadow`,
  `color`, and `text-decoration-thickness`.
- No reduced-motion branch was required for this phase because no transform,
  translate, scale, scroll behavior, delayed visibility, or reveal motion was
  introduced.

## Phase 4: Progressive Page-Transition Enhancement

Objective: decide whether true outgoing/incoming page transitions are worth the
added complexity.

Options to evaluate:

- CSS-only incoming reveal.
- View Transitions API as progressive enhancement.
- Tiny JavaScript class toggles on same-origin link clicks.
- No outgoing transition.

Recommendation:

Start with CSS-only incoming reveal. Revisit the View Transitions API only after
layout stability and route-specific block motion are proven. Avoid custom JS
navigation unless there is a clear user-visible problem that CSS cannot solve.

Acceptance criteria:

- Normal WordPress navigation remains the source of truth.
- Unsupported browsers get the normal page load.
- Same-page anchors, downloads, admin links, forms, and external links are not
  intercepted.
- Reduced-motion disables transition effects.

### Phase 4 Closeout - 2026-07-07

Dex task: `066z5anc`.

Decision: do not add the View Transitions API or a custom JavaScript navigation
enhancer in this phase.

Rationale:

- Normal WordPress navigation remains the source of truth.
- The current polish work does not need outgoing transitions or link-click
  interception.
- Same-page anchors, forms, downloads, admin links, external links, and plugin
  routes avoid a new interception surface.
- If a later user-visible problem remains after layout stability and scoped
  component motion are proven, View Transitions can be reconsidered as a
  progressive enhancement.

## Phase 5: Validation

Objective: prove the motion is accessible, fast, and visually controlled.

Target routes:

- `/`
- `/contact-us/`
- `/contact-success/`
- `/search/`
- `/?s=protest`
- an empty search result URL
- `/news/`
- `/news/page/2/` if available
- `/herstories/`
- a standard post
- a Herstory single
- `/shop/`

Checks:

- `prefers-reduced-motion: reduce` removes movement, reveals, smooth-scroll, and
  delayed visibility; it does not need to remove immediate color, underline,
  border, or shadow state changes.
- Keyboard focus remains visible on cards, pagination, CTA links, and entry
  navigation.
- No transition delays initial content visibility.
- No horizontal overflow is introduced by transform effects.
- Visual snapshots remain deterministic with animations disabled.
- A targeted enabled-motion check verifies that animation properties exist only
  on intended selectors.

Commands:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual -- --grep "search|news|herstories|entry navigation|banner"
```

Run the full visual suite before landing broad motion behavior:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual
```

### Phase 5 Closeout - 2026-07-07

Dex task: `1jdpcasa`.

Validation performed:

- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone format`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:fast`

Results:

- CSS compiled and linted successfully.
- The first template visual run exposed that Local was serving saved
  `wp_template` overrides without the new archive-card hooks.
- After syncing the saved `home` and `archive-herstory` templates with the
  filesystem templates, `test:visual:templates` passed: 22 passed.
- `test:visual:fast` passed: desktop 30 passed, mobile 12 passed.
- Search, News, and Herstories tests now assert the enabled-motion contract on
  explicit selectors and reject `transition-property: all`.
- Existing template, archive, search, entry-navigation, shop-template, and
  light-surface checks remained green in the targeted lane.

## Locked Decisions

### 1. What should the first motion slice be?

Options:

- A. Micro-interactions only.
- B. Section/page reveals.
- C. Scroll-triggered reveals.

Locked decision: A. Controls and navigation are high-frequency, low-risk
surfaces. Section/page reveals should follow only after layout stability is
confirmed.

### 2. Should page transitions be CSS-only or View Transitions API?

Options:

- A. CSS-only incoming reveal.
- B. View Transitions API as progressive enhancement.
- C. Custom JavaScript navigation class toggles.

Locked decision: A if page reveal is approved after Phase 1. B is a later
enhancement if the site still feels abrupt after the safer CSS layer. Avoid C
unless there is a specific route-level problem.

### 3. Where should motion CSS live?

Options:

- A. New `styles/components/motion.css`.
- B. Add small rules to each existing component file.
- C. Put everything in utilities.

Locked decision: A. Motion is cross-surface behavior, but it still belongs in
the source CSS component layer and should be easy to audit.

### 4. Should News and Herstories cards get new semantic classes?

Options:

- A. Add explicit card classes to the block templates.
- B. Target current nested WordPress block markup.
- C. Only animate Search because it already has hooks.

Locked decision: A. Search can start immediately, but News and Herstories should
get explicit hooks before motion lands.

### 5. Should Shop get motion while Ecwid loads?

Options:

- A. Animate the Ecwid region.
- B. Add only a stable wrapper/loading-state reveal after layout reservation.
- C. Exclude Shop from motion until layout stability is solved.

Locked decision: C until the layout-stability plan resolves reservation. Then B
if the wrapper has a reliable loaded state. Avoid A.

### 6. Should hover motion apply to all cards?

Options:

- A. All post/result cards.
- B. Only cards with links and stable dimensions.
- C. No hover motion, only load/reveal motion.

Locked decision: B. Hover motion should be reserved for clearly clickable cards
or linked media/title areas. Avoid making static text containers feel clickable.

### 7. Should Animations for Blocks be used?

Options:

- A. Use plugin attributes for this polish.
- B. Keep plugin metadata alone and implement theme-owned CSS.
- C. Remove plugin animation metadata from templates while implementing CSS.

Locked decision: B for now. Use theme-owned CSS so behavior is auditable and
testable. Consider C only if stale metadata creates visible effects or editor
confusion.

### 8. What should reduced motion do?

Options:

- A. Remove movement and reveals; keep immediate non-moving state affordances.
- B. Remove all nonessential transitions, including color/shadow changes.
- C. Shorten durations only.

Locked decision: A. Under `prefers-reduced-motion: reduce`, remove transforms,
reveals, smooth-scroll, and delayed visibility; keep immediate color, outline,
underline, border, and shadow affordances when they do not move content.

### 9. Should search get AJAX or pending-submit states?

Options:

- A. Native reload polish only.
- B. Submit-pending JavaScript state.
- C. AJAX search.

Locked decision: A. Keep Search native and server-rendered unless there is a
separate product decision to build an interactive search experience.
