# Accessible Header Search Drawer Plan

Created: 2026-07-15.

Status: Complete locally. The feature and its focused accessibility contracts
are implemented and verified; the broad visual gate retains unrelated existing
baseline drift.

## Goal

Make editorial search directly discoverable from the header without requiring a
desktop visitor to visit the `/search/` landing page first.

The agreed experience is deliberately split by the existing navigation
breakpoint:

- **Desktop (1152px and wider):** a visible `Search` trigger at the end of the
  primary header navigation reveals a non-modal search row inside the white
  header surface, between the horizontal navigation and the yellow CTA
  navigation. It contains a labelled native search form. Submitting sends the
  visitor to the existing native WordPress results route
  (`/?s=<query>`).
- **Compact navigation (1151px and narrower):** no search drawer and no
  search-specific JavaScript. The existing Core Navigation overlay includes a
  normal `Search` link that goes to `/search/`, where the existing landing-page
  form is available.

The drawer is an interaction convenience, not a replacement search system.
Native server-rendered WordPress search and the current results template remain
the source of truth.

## Locked Product Decisions

- The desktop panel is an in-flow header row, not a floating popup: it spans
  the header's content rail and opens between the horizontal navigation and
  the CTA navigation, pushing the CTA down. The form itself remains a compact
  right-aligned `32rem` control group within that row.
- The trigger visibly reads `Search`; a magnifier may be decorative support,
  but it is not the sole visible or accessible label.
- The desktop trigger remains a text control in the primary-navigation visual
  language; it does not carry the theme button class or a custom focus colour.
  The revealed form follows the existing Core Search control pattern: an
  SR-only `Search the site` label, `know your herstory...` placeholder, and
  inline text-button submit control.
- Submitting desktop search continues to the existing native WordPress results
  URL (`/?s=<query>`). Mobile's `/search/` link remains a landing-page entry
  point, not a new search-results routing scheme.
- The desktop disclosure begins at `1152px`. The current live navigation has
  enough content to wrap before that width when a 44px Search control is added;
  the existing responsive Core menu is the accessible compact-navigation
  treatment below the verified fit threshold.

## Current Evidence And Ownership

- The active theme is `protestsandsuffragettes-standalone`.
- Native search is already enabled. `inc/search.php` limits its main results
  query to editorial `post` and `page` content; Ecwid product search remains
  separate and out of scope.
- `templates/search.html` owns results for `/?s=<query>`. Published `/search/`
  (page `6023`) uses `templates/page-search.html` as the landing-form fallback.
- The live Header is DB record `wp_template_part #6709`, and the dormant Top
  Nav record is `wp_navigation #1035`. Both differ from their filesystem
  fixtures. Header `#6709` serializes the rendered primary navigation inline,
  so it—not Top Nav—is authoritative for the visible mobile Search link.
- `parts/header.html` and `navigation/primary.html` are recovery/default
  fixtures. They must never be seeded over the current live Header or Top Nav
  just to deliver this feature.
- The existing mobile navigation overlay changes at `max-width: 1035px` and
  already has Core Navigation focus/escape behaviour plus the narrowly scoped
  `scripts/core-navigation-drawer.js` compatibility shim. It is a control
  surface, not an implementation target for desktop search behaviour.
- Header-search CSS deliberately extends that Core overlay breakpoint to
  `1151px` for this feature. This removes the proven 1036–1151px logo/nav
  collision rather than squeezing controls below their accessible target size.

## Non-Goals

- No AJAX, typeahead, live results, custom index, or third-party search
  plugin.
- No product/catalog results in editorial search.
- No desktop modal, focus trap, `role="dialog"`, or `aria-modal` behaviour.
- No custom mobile search drawer, search toggle, or mobile event handling.
- No replacement or broad rewrite of the Core Navigation drawer.
- No blind sync of DB-backed navigation/header records from theme files.

## Chosen Architecture

Use a small, theme-owned desktop search disclosure beside the rendered primary
navigation, with its revealed form in the following header row, and a separate,
administrator-owned mobile navigation link.

### Desktop component

Add a server-rendered theme primitive, scoped to the resolved
`pns-primary-navigation`, rather than putting a second interaction into the
Core mobile responsive container. Its markup will contain:

```html
<button type="button" aria-expanded="false" aria-controls="pns-site-search-panel">
  Search
</button>
<section id="pns-site-search-panel" hidden>
  <form role="search" method="get" action="/">
    <label for="pns-site-search-input">Search the site</label>
    <input id="pns-site-search-input" type="search" name="s" required>
    <button type="submit">Search</button>
  </form>
</section>
```

The actual implementation must use WordPress escaping and `home_url( '/' )`,
not hard-coded production URLs. The label remains visibly available in the
drawer; an icon may supplement the trigger, but cannot be its only accessible
name.

The small associated script only manages disclosure state and focus. The form
itself is ordinary HTML GET behaviour, so a submitted query is shareable,
crawlable, and works without JavaScript.

### Mobile link

Add the final inline Header navigation item as an administrator-owned Core
Navigation link:

- label: `Search`;
- URL: `/search/`;
- class: `pns-header-search__mobile-link`.

CSS hides that link on desktop and shows it inside the existing Core mobile
navigation overlay. Conversely, the desktop disclosure is hidden at mobile
widths. This avoids duplicate exposed search controls and gives mobile users
the straightforward page navigation requested.

### File ownership

Expected implementation surfaces are:

| Concern | Owner / expected surface |
| --- | --- |
| Primary-nav render anchor and desktop markup | `inc/navigation.php` |
| Script registration | `inc/assets.php` |
| Small disclosure controller | new `scripts/header-search-drawer.js` |
| Header search presentation | new `styles/components/header-search.css`, imported frontend-only at the unlayered tail of `styles/frontend.css` so the header-specific control rules retain their intentional priority |
| Header layout reference | `styles/components/header.css` |
| Existing mobile-overlay control group | `styles/blocks/core-navigation-core-drawer.css` and `scripts/core-navigation-drawer.js` (do not extend unless a proven shared defect requires it) |
| Mobile Search item | live Header `wp_template_part #6709` via Site Editor; fixture update only after a reviewed comparison |
| Visual and interaction coverage | `tests/visual/frontend.spec.ts` |

## Accessibility Contract

The desktop control is a non-modal disclosure. It must satisfy all of the
following contracts before it can land.

### Semantics and state

- The trigger is a native `button`, has the accessible name `Search`, and
  carries accurate `aria-expanded` and `aria-controls` values.
- The panel has one stable ID and starts with the semantic `hidden` attribute.
  The implementation must keep `hidden`, visibility, and `aria-expanded` in
  sync; opacity alone is not a closed state.
- The panel contains a real `role="search"` GET form, visible label, associated
  `type="search"` input with `name="s"`, and an explicit submit button.
- Do not add `role="dialog"`, `aria-modal`, a focus trap, duplicate IDs, or an
  ARIA live region. Search results are a normal destination page.

### Keyboard and focus

- Trigger works with pointer activation, Enter, and Space through native button
  semantics.
- Opening the panel sets `aria-expanded="true"`, removes `hidden`, and moves
  focus immediately to the search field.
- Escape while focus is inside the component closes the panel and restores
  focus to the trigger.
- Re-activating the trigger closes the panel and restores focus to the trigger.
- Pointer activation outside the component closes the panel but does not steal
  focus from the element the visitor selected.
- Keep the disclosure open while focus moves among its trigger, field, and
  submit control. When focus leaves that whole Search component, close it just
  as an outside pointer activation does; do not restore focus or interrupt the
  visitor's next target.
- At a resize or media-query transition to mobile, force a closed desktop state
  so no hidden-but-focusable desktop control can survive the breakpoint.

### Visual accessibility

- Retain or strengthen the project’s existing visible `:focus-visible` affordance
  for trigger and submit controls. Do not suppress outlines.
- Trigger and submit hit areas must be at least 44 by 44 CSS pixels.
- Panel text, border, field, and focus colours must meet normal-text/UI control
  contrast expectations against the header surface.
- Panel z-index must sit above header content without exceeding the Core mobile
  drawer token (`--pns--navigation--drawer-z-index`); it must not be clipped by
  header overflow.
- Any open/close transition is cosmetic, has no focus delay, and only runs
  under `prefers-reduced-motion: no-preference`. The `hidden` state always wins.

## Delivery Phases

### Phase 0 — Preserve live source authority

1. Record `git status --short` and preserve all unrelated worktree changes.
2. With elevated WP-CLI access, verify active theme, Header `#6709`, Top Nav
   `#1035`, search setting, `/search/` page, and the `/?s=` result contract.
3. Export timestamped backups of both live records under
   `docs/jobs/header-search-drawer-db-backups/` before changing them.
4. Compare the exported Header/Top Nav with `parts/header.html` and
   `navigation/primary.html`; record the unrelated drift and make a narrow
   source-of-truth decision. Do not flatten live editor changes into fixtures.

**Acceptance:** a rollback record exists, active authority is explicit, and the
feature scope has no unreviewed overwrite risk.

### Phase 1 — Build the semantic desktop primitive

1. Add the scoped server-rendered markup through the navigation render path.
2. Add a separately named script asset; do not attach handlers to generic
buttons or the existing mobile-drawer shim.
3. Implement only the open, close, Escape, outside-pointer, focus-return, and
breakpoint-reset state machine described above.
4. Keep the form native: `method="get"`, `action=home_url( '/' )`, and `s` as
the input name.

**Acceptance:** script failure still leaves a valid native form if it is
rendered; no search result is fetched or generated by JavaScript.

### Phase 2 — Wire responsive entry points

1. Add `Search → /search/` to the live Header navigation in Site Editor, with
   the `pns-header-search__mobile-link` class.
2. Render the desktop trigger/drawer only at 1152px and wider and the plain nav
   link only at 1151px and narrower.
3. Update `navigation/primary.html` only if the reviewed DB comparison shows a
   safe recovery snapshot can be captured without discarding unrelated live
   navigation changes.

**Acceptance:** exactly one search entry point is exposed at each viewport;
mobile has no desktop disclosure script behaviour.

### Phase 3 — Add narrow component styling

1. Add `header-search.css` as a frontend-only component and import it after
   the existing priority overrides in `styles/frontend.css`.
2. Position the desktop panel as an in-flow row immediately below the primary
   navigation and above the CTA navigation; it must push the CTA down rather
   than overlaying it.
3. Reuse existing form primitives, then make only scoped compact-field and
   header-control adjustments required by the drawer.
4. Verify the 1151/1152 seam and the existing wide-navigation range, including
   the 1294px spacing transition.

**Acceptance:** no horizontal overflow, no collision with the logo or menu,
and no change to the Core mobile overlay geometry.

### Phase 4 — Prove behaviour and release quality

Add focused Playwright contracts before calling the feature done:

- Desktop initial state: trigger visible, panel hidden, state false.
- Trigger activation: panel visible, state true, input focused.
- Keyboard: Enter/Space activation, Tab and Shift+Tab through the form, Escape
  close and focus return.
- Pointer outside: panel closes without an artificial focus jump.
- Native submission: a fixed query navigates to `?s=<query>` and renders
  `.pns-template-search` plus expected results/no-results output.
- Accessibility: correct trigger state/name/control relation, real label/input
  relation, no dialog semantics, no focus trap, no duplicate exposed search
  controls.
- Mobile (390px): no desktop trigger/panel; Core menu exposes Search and the
  link navigates to `/search/`.
- Breakpoint seam: 1151px uses only the compact-navigation route; 1152px uses
  only the desktop disclosure.
- Reduced motion: opening is immediate and no drawer animation remains active.

Run the following from the standalone theme after each relevant slice:

```text
pnpm format:check
pnpm lint:css
pnpm compile:css
pnpm test:visual:navigation
pnpm test:visual:fast
pnpm test:visual
```

Use elevated local-process access for Playwright in this Local WP environment.
Also run PHP syntax checks for touched PHP, the block-template validator for
any changed serialized markup, `pnpm check:template-ownership` with elevated
WP-CLI access, and `git diff --check`. Update local-only visual baselines only
after accepting the intended drawer appearance.

## Implementation Record

- Backed up the live Header and Top Nav before mutation in
  `docs/jobs/header-search-drawer-db-backups/20260715T132226+0100-*`.
- Added the desktop disclosure through the scoped primary-navigation render
  filter, registered the small independent controller, and added narrowly
  scoped responsive CSS. The component opens at `1152px`; at narrower widths,
  the existing Core Navigation menu is used and exposes the saved `/search/`
  link only.
- Refined the initial presentation after visual review: the desktop form now
  uses the existing wrapping header row and is revealed in normal document
  flow above the CTA. The trigger has an explicit dark navigation colour; the
  form has no absolute positioning, floating shadow, or CTA overlap.
- Refined the component again against the live Style Guide reference: the form
  uses Core Search markup with an SR-only label and `know your herstory...`
  placeholder, while the nav trigger keeps its original text-control treatment.
  The in-flow row is contained by the navigation wrapper, so it expands directly
  below Search with the same wrapper alignment and pushes the CTA down. It
  expands/collapses over the theme's 220ms reveal duration; motion is disabled
  for reduced-motion visitors. Leaving the entire Search component by keyboard
  closes the drawer without changing the visitor's new focus.
- Added the `Search → /search/` item to the authoritative Header `#6709`, not
  the dormant Top Nav `#1035`. The Top Nav record was restored from its backup
  after that source-of-truth correction.
- A first raw `wp_update_post()` mutation stripped the escape slash from the
  Header's serialized `\\u0026` characters, producing the visible `PU0026S`
  CTA corruption. Header `#6709` was immediately restored from the backup and
  the narrow Search-link update reapplied with `wp_slash()`; the CTA text was
  then verified as restored. Future serialized block-content writes must use
  `wp_slash()`.
- Focused checks passed: the desktop search disclosure, the 1151/1152 header
  seam, the mobile Core-menu Search link, and the banner CTA text contract.
  PHP lint, Prettier, CSS lint, CSS compilation, template-ownership audit, and
  `git diff --check` also passed.
- The lean `pnpm test:visual` run completed with **49 passed, 15 failed, 15
  skipped**. The header-search and CTA tests passed; the failures are existing
  cross-template/snapshot/Ecwid visual drift outside this feature slice, so no
  baseline was updated and no unrelated fix was made.

## Rollback

- Restore the timestamped Header and Top Nav exports through the same
  DB-authoritative workflow used for the change.
- Remove the theme-owned desktop primitive, its asset registration, component
  CSS import, and tests as one feature slice.
- The existing `/search/` landing page and `/?s=` results remain available even
  if the header feature is removed.

## Definition Of Done

- Desktop visitors can open, use, and close the accessible non-modal drawer
  using pointer and keyboard.
- Submitting a term reaches the existing native editorial results template.
- Mobile visitors see only the direct `/search/` link in the normal Core menu.
- All focus, ARIA, responsive, reduced-motion, and native-form contracts above
  are covered by automated tests and manual local verification.
- Live DB changes are backed up, fixture status is explicitly documented, and
  unrelated working-tree changes remain untouched.
