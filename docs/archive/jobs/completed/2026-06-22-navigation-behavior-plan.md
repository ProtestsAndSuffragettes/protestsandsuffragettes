# Navigation Behavior Plan

Planning document created on 2026-06-22.

All paths are relative to the project root.

## Goal

Refine the site navigation for both wide-screen and mobile use. The immediate
planning need is to identify where behavior and markup are controlled before
visual design decisions are made.

The expected behavior change is better mobile navigation, including collapsible
sections and support for larger grouped content. Accessibility and code
visibility are first-class requirements, but navigation links still need to
remain editable in WordPress admin.

The current direction is therefore not a return to a static PHP nav walker.
Prefer file-owned theme structure and behavior, fed by admin-editable
`wp_navigation` content.

## 2026-06-24 Standalone Update

The active implementation target is now
`app/public/wp-content/themes/protestsandsuffragettes-standalone/`.

WP-CLI verified both active options as:

- `stylesheet`: `protestsandsuffragettes-standalone`
- `template`: `protestsandsuffragettes-standalone`

The navigation content remains database-backed through `wp_navigation`. The
Header/Footer structure should be file-owned by the standalone theme once saved
template-part overrides are exported and removed.

## 2026-06-24 Branch Start

Implementation branch: `codex/navigation-behavior-plan`.

Runtime verification on branch start:

- Active `stylesheet`: `protestsandsuffragettes-standalone`
- Active `template`: `protestsandsuffragettes-standalone`
- Live header template part: `wp_template_part` `1027` (`Header`)
- Live footer template part: `wp_template_part` `1026` (`Footer`)
- Live top nav content: `wp_navigation` `1035` (`Top Nav`)
- Live footer nav content: `wp_navigation` `1032` (`Navigation`)

Current source-of-truth gap:

- The saved DB `Header` and `Footer` template parts still outrank filesystem
  parts at runtime.
- The standalone filesystem `parts/header.html` and `parts/footer.html` have
  already been cleaned by the design-token closeout.
- The saved DB records still contain older inline values, including nav
  `blockGap: 1.6em`, footer social `8px`, and footer deep-purple inline
  background.

First implementation rule for this branch:

- Do not change nav behavior until the header/footer source-of-truth decision is
  made explicitly.
- The first non-destructive slice is to make the filesystem/DB divergence
  visible and decide whether to sync the DB records from the standalone files or
  clear the saved template-part overrides so filesystem parts become live.

Source-of-truth cleanup status:

- Complete on 2026-06-24.
- Backup exported to
  `docs/jobs/navigation-db-backups/2026-06-24-navigation-source-records.json`.
- The backup includes saved `wp_template_part` records `1027` (`Header`) and
  `1026` (`Footer`), plus `wp_navigation` records `1035` (`Top Nav`) and `1032`
  (`Navigation`).
- Saved `Header` and `Footer` template-part overrides were deleted after the
  backup.
- `wp_navigation` `1035` and `1032` were intentionally retained so menu links,
  labels, order, and nesting remain editable in WordPress admin.
- The standalone filesystem `parts/header.html` and `parts/footer.html` are now
  the intended live structure owners.
- Validation passed after the DB source switch: standalone theme `pnpm check`,
  focused nav Playwright contracts, and full visual suite `39 passed`.

Agreed v0 implementation decisions:

- Export current DB-backed records before any destructive Site Editor cleanup.
- Make standalone files the intended header/footer structural source of truth.
- Keep `wp_navigation` `1035` as the admin-editable link, label, order, and
  hierarchy source.
- Keep desktop on the core Navigation block for v0.
- Build toward a PNS-owned mobile drawer only after the current nav styling is
  centralized and covered by regression checks.
- Preserve the current nav visuals 1:1 in v0; this slice is a source-of-truth,
  selector, and tokenization pass, not a redesign.

V0 nav styling baseline:

- Add a stable theme-owned class to the rendered top Navigation block instead
  of styling generated `wp-container-*` selectors.
- Centralize current nav dimensions, colors, and type sizes as
  `--pns--navigation--*` tokens in `styles/shared/settings.css`.
- Do not carry forward stale generated-selector values when screenshot coverage
  proves they were not part of the current rendered baseline.
- Align the `theme.json` `core/navigation` font-size default with the rendered
  v0 nav size.
- Keep existing desktop and mobile drawer computed-style contracts green before
  changing mobile behavior.

V0 styling slice status:

- Complete on 2026-06-24.
- `functions.php` adds `pns-primary-navigation` to the rendered top nav block
  for `wp_navigation` ref `1035`.
- `styles/shared/settings.css` owns the current `--pns--navigation--*` token
  values.
- `styles/blocks/core-navigation.css` consumes those tokens and no longer uses
  generated `wp-container-*` selectors.
- Regression coverage now asserts the stable class, current zero closed-nav
  inline padding, open-button padding, and existing mobile drawer rhythm.
- Validation passed: standalone theme `pnpm check`, focused nav Playwright
  contracts, and full visual suite `39 passed`.

V0 nav stylesheet consolidation status:

- Complete on 2026-06-24.
- The former `styles/blocks/core-navigation-frontend.css` primitives were moved
  into `styles/blocks/core-navigation.css`.
- `styles/blocks/index.css` no longer imports a second Navigation stylesheet.
- Mobile drawer selectors are already owned by `styles/blocks/core-navigation.css`;
  no second mobile-nav stylesheet remains.

PNS mobile drawer implementation status:

- Complete on 2026-06-24.
- Desktop and tablet continue to use the core Navigation block output.
- Mobile now uses a PNS-owned drawer appended by
  `render_block_core/navigation` only for `wp_navigation` ref `1035`.
- The drawer content is generated from the editable `wp_navigation` post via
  `get_post()`, `parse_blocks()`, and page-list expansion, not from hardcoded
  links.
- Top-level submenu parents render as disclosure `<button>` controls on mobile.
  If the parent also has a URL, that URL is preserved as the first child link.
- Desktop submenus with parent URLs now receive the same overview-link pattern
  programmatically through `render_block_core/navigation-submenu`; editors do
  not need to duplicate parent links in the WP UI.
- Mobile drawer section headings and nested links now share visible hover/focus
  styling, and nested sublists have explicit inline-start indentation.
- Drawer JavaScript is owned in
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/mobile-navigation.js`
  and handles open/close state, sibling section closing, Escape close, focus
  entry/return, focus wrapping, and body scroll lock.
- Drawer presentation stays in
  `styles/blocks/core-navigation.css`, with values tokenized in
  `styles/shared/settings.css`, so header Navigation styles have one CSS owner.
- Validation passed after implementation: PHP syntax check, standalone theme
  `pnpm check`, focused homepage/mobile-nav Playwright contracts, mobile
  snapshot rerun, and full visual suite `39 passed`.

## Current Implementation

This site does not use a custom PHP nav walker for the header navigation.
Searches across `app/public/wp-content` found no active `wp_nav_menu()`,
`Walker_Nav_Menu`, or custom walker implementation.

The header uses WordPress full-site editing and the core `core/navigation` block.
The header and footer structure are file-backed by the standalone theme. The
navigation records remain database-backed Site Editor objects in the active
standalone theme context.

| Object     | Type            |     ID | Slug           | Title        | Status    | Last modified         |
| ---------- | --------------- | -----: | -------------- | ------------ | --------- | --------------------- |
| Top nav    | `wp_navigation` | `1035` | `navigation-2` | `Top Nav`    | `publish` | `2026-06-24 00:15:32` |
| Footer nav | `wp_navigation` | `1032` | `navigation`   | `Navigation` | `publish` | `2026-06-24 00:15:32` |

Former saved template-part overrides:

| Object | Type               |     ID | Slug     | Title    | Status                             |
| ------ | ------------------ | -----: | -------- | -------- | ---------------------------------- |
| Header | `wp_template_part` | `1027` | `header` | `Header` | Deleted after backup on 2026-06-24 |
| Footer | `wp_template_part` | `1026` | `footer` | `Footer` | Deleted after backup on 2026-06-24 |

The file-backed `Header` template part contains a site logo and a reference to
the `Top Nav` navigation post:

```html
<!-- wp:site-logo {"width":150,"shouldSyncIcon":false,"className":"pands-logo"} /-->
<!-- wp:navigation {"ref":1035,"layout":{"type":"flex","orientation":"horizontal","flexWrap":"wrap","justifyContent":"right"},"style":{"spacing":{"blockGap":"1.6em"}}} /-->
```

The `Top Nav` post contains the actual menu structure. Current top-level entries
are:

- `ArtWorks`
- `About`, with nested links for `Work with Us` and `Gender Inclusion Policy`
- `Herstories`, with a nested `core/page-list` for child pages
- `Shenanigans`, with nested workshop links
- `Educational Resources`
- `News`, with a nested `We're Hiring` link
- `Shop`
- `Contact`

## File-Backed Defaults

The standalone theme provides file-backed header/footer parts. With the saved
Site Editor Header/Footer overrides removed, these files are the intended live
structure source.

| Path                                                                                       | Role                                                                                            |
| ------------------------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------------- |
| `app/public/wp-content/themes/protestsandsuffragettes-standalone/parts/header.html`        | Standalone fallback header. Mirrors nav ref `1035`; nav spacing is intentionally CSS-owned.     |
| `app/public/wp-content/themes/protestsandsuffragettes-standalone/parts/footer.html`        | Standalone fallback footer. Mirrors nav ref `1032`, `fontSize: small`, and vertical nav layout. |
| `app/public/wp-content/themes/protestsandsuffragettes-standalone/templates/*.html`         | Standalone filesystem templates that insert header/footer parts.                                |
| `app/public/wp-content/themes/protestsandsuffragettes/`                                    | Historical child-theme reference only for this migration slice.                                 |
| `app/public/wp-content/themes/estory/parts/header.html` and `estory/inc/patterns/header/*` | Parent-theme history. Do not edit for standalone work.                                          |

If the saved Site Editor header or footer records are reset or deleted,
WordPress can fall back to the standalone filesystem parts. For reliable code
review and deployment, make the standalone parts the intentional structural
source of truth before major nav behavior work.

## Current Rendered Markup

WordPress core still renders the header navigation as a responsive Navigation
block. Desktop and tablet continue to use this output:

- Outer element: `nav.wp-block-navigation` with `aria-label="Top Nav"`.
- Open button: `button.wp-block-navigation__responsive-container-open`.
- Overlay container: `.wp-block-navigation__responsive-container`.
- Close button: `button.wp-block-navigation__responsive-container-close`.
- Menu list: `ul.wp-block-navigation__container`.
- Submenu items: `li.wp-block-navigation-submenu.has-child`.
- Submenu toggle buttons:
  `button.wp-block-navigation__submenu-icon.wp-block-navigation-submenu__toggle`.
- Submenu lists: `ul.wp-block-navigation__submenu-container`.

The rendered Navigation block already includes WordPress Interactivity API
attributes such as `data-wp-interactive="core/navigation"` and click handlers
such as `actions.openMenuOnClick`, `actions.closeMenuOnClick`, and
`actions.toggleMenuOnClick`.

For mobile, the standalone theme now appends a separate
`.pns-mobile-navigation` drawer after the primary `core/navigation` block for
nav ref `1035`. The drawer is generated from the same `wp_navigation` content
source but owns its own disclosure markup and JavaScript behavior.

This matters for collapsible sections: core already outputs submenu toggle
buttons and `aria-expanded` state for submenus. Treat that as useful baseline
behavior for desktop/tablet, not as proof that the core overlay is the right
mobile architecture for larger grouped content.

## Current Style Ownership

Frontend CSS is authored in the standalone theme and compiled with Lightning
CSS:

| Path                                                                                                  | Role                                                                                                              |
| ----------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------- |
| `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/frontend.css`                 | Frontend CSS entrypoint. Imports settings, base, layout, block, component, and vendor CSS.                        |
| `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/shared/settings.css`          | Theme tokens, including current v0 `--pns--navigation--*` values.                                                 |
| `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/blocks/core-navigation.css`   | Registered `core/navigation` block style. Holds top-nav desktop, dropdown, typography, and mobile drawer styling. |
| `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/components/footer-layout.css` | Footer nav typography and rhythm.                                                                                 |
| `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/dist/frontend.min.css`        | Compiled frontend stylesheet served when present. Do not hand-edit.                                               |
| `app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/mobile-navigation.js`        | Mobile drawer behavior: open/close, focus handling, Escape, scroll lock, and section disclosure.                  |
| `app/public/wp-content/themes/protestsandsuffragettes-standalone/functions.php`                       | Enqueues assets, registers block styles, adds stable top-nav classes, and renders the PNS mobile drawer.          |
| `app/public/wp-content/themes/protestsandsuffragettes-standalone/theme.json`                          | Defines public block defaults; `core/navigation` typography should stay aligned with CSS reality.                 |

Existing nav-related CSS includes:

- Wide-screen item padding around `header .wp-block-navigation`.
- Responsive menu icon coloring.
- Mobile overlay item padding at max-width `600px`.
- Submenu container padding resets.
- Submenu icon stroke color.
- Hover pseudo-elements for wide-screen nav items.
- Global `.wp-block-navigation-item` padding and typography.
- Historical generated-class selectors that were removed before major nav work,
  such as `.wp-container-2` and
  `nav.wp-container-core-navigation-is-layout-808e6b47`.

For new work:

- Keep core Navigation block defaults in `styles/blocks/`.
- Keep PNS-owned mobile drawer CSS in `styles/blocks/core-navigation.css` while
  it is tightly coupled to the primary `core/navigation` render path.
- Do not add new rules scoped to generated `wp-container-*` classes.
- Keep the `theme.json` `core/navigation` font-size declaration aligned with
  the CSS-rendered nav baseline.

The parent theme also has historical Navigation block CSS in
`app/public/wp-content/themes/estory/style.css`, but standalone work should not
depend on editing or extending parent theme files.

## Where To Change What

| Change type                                                 | Best owner                                             | Notes                                                                                                                                                             |
| ----------------------------------------------------------- | ------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Desktop spacing, hover, active states, dropdown positioning | Standalone theme block/component CSS                   | Use stable selectors scoped to `header .wp-block-navigation` or a deliberate class, not generated `wp-container-*` classes.                                       |
| Admin-editable menu item order, labels, links, nesting      | `wp_navigation` post `1035`                            | Keep this editable in WordPress admin. Export/sync intentionally if release parity needs to be reproducible from code.                                            |
| Header and footer structure around logo/nav                 | Standalone `parts/header.html` and `parts/footer.html` | Make files the intended structural source, then clear or sync saved DB overrides as a release task.                                                               |
| Navigation block defaults such as typography/color          | `theme.json`                                           | Useful for broad block defaults, but it must match rendered reality. Current `core/navigation` `1.25rem` conflicts with rendered `0.8125rem`/`1rem` nav type.     |
| PNS mobile drawer layout and interaction behavior           | Standalone theme component/PHP/JS                      | Preferred for larger grouped content and auditability. Consume `wp_navigation` content rather than hardcoding links.                                              |
| Small attributes/classes on core Navigation output          | Targeted PHP render filter                             | Use `render_block_core/navigation` only for narrow, HTML-aware changes such as classes/data attributes.                                                           |
| Rich drawer content beyond links                            | Separate template part, pattern, or block              | Keep editorial/promotional content editable separately from menu links. Do not overload `wp_navigation` with arbitrary content blocks unless deliberately tested. |
| Parent fallback header pattern                              | Parent `estory` files                                  | Historical only. Avoid editing.                                                                                                                                   |

Source-of-truth policy for the standalone theme:

1. Structural chrome belongs in the standalone theme files.
2. Link content belongs in `wp_navigation` so users can edit menus in admin.
3. Accessibility-critical mobile interaction belongs in reviewed theme code.
4. Saved DB template-part overrides must be exported, synced, or intentionally
   cleared before release; do not let them silently outrank filesystem changes.

## Markup Strategy Options

### Option A: Style Existing Core Output

Use the current `core/navigation` output as-is. Style the existing responsive
container and submenu toggle buttons so mobile sections feel like accordions.

Pros:

- Smallest change.
- Keeps WordPress core accessibility and keyboard behavior.
- Avoids maintaining custom menu markup.

Risks:

- Core may allow multiple submenus open at once.
- Parent submenu labels that also have links remain split between a link and a
  small toggle button.
- CSS-only collapse styling depends on core classes/ARIA state and must be
  verified in browser.
- This is unlikely to be enough if the mobile nav needs large grouped content,
  custom focus behavior, or highly audited disclosure semantics.

### Option B: Adjust Saved Navigation Block Markup

Edit `wp_navigation` post `1035` so top-level section headings intended to act as
collapsible groups do not also navigate. The current `News` item is already an
example of a submenu label without an `href`.

Pros:

- Uses native Navigation block semantics.
- Reduces custom code.
- Makes mobile accordion targets clearer.

Risks:

- The change lives in the database unless exported or reproduced in code.
- Desktop behavior may need design decisions for non-link section headings.

### Option C: Add a Standalone-Theme Render Filter

Use PHP in `functions.php` to post-process specific Navigation block output. This
could add project-specific classes, wrappers, data attributes, or alternative
labels for the header nav.

Potential hooks:

```php
add_filter( 'render_block_core/navigation', '...', 10, 2 );
add_filter( 'render_block_core/navigation-submenu', '...', 10, 2 );
add_filter( 'render_block', '...', 10, 2 );
```

Pros:

- Keeps behavior versioned in the standalone theme.
- Can target only the header nav by checking block attributes such as
  `ref => 1035`.

Risks:

- String-based HTML mutation can become brittle.
- More invasive than content or CSS changes.
- Must preserve core accessibility attributes and Interactivity API bindings.

If this path is needed, use an HTML-aware approach where possible, such as
`WP_HTML_Tag_Processor`, instead of broad string replacements.

### Option D: Add Standalone-Theme JavaScript

Keep core markup, then add a small script for behavior that core does not supply,
such as closing sibling sections when one mobile section opens.

Pros:

- Good fit for interaction rules that cannot be expressed in CSS.
- Can remain scoped to the header nav.

Risks:

- Must coexist with WordPress core Navigation interactivity.
- Needs keyboard and focus testing.
- Should not duplicate state management that core already owns.

### Option E: Build A PNS Mobile Nav Renderer Fed By `wp_navigation`

Keep `wp_navigation` post `1035` as the editable source for links and hierarchy,
but render the mobile drawer with project-owned PHP markup and project-owned
JavaScript. Desktop can continue using core Navigation unless the accessibility
audit proves it needs the same treatment.

The renderer should consume the saved navigation blocks and output a controlled
mobile disclosure structure:

- a single visible menu trigger;
- real `<button>` elements for collapsible section headings;
- deterministic `aria-expanded` and `aria-controls` relationships;
- clear current-page state;
- predictable focus entry, exit, and return behavior;
- Escape-to-close behavior;
- body scroll handling while the drawer is open;
- large-content support through a separate editable drawer template part or
  pattern, not hardcoded links.

Pros:

- Keeps links editable in WordPress admin.
- Gives the theme full code visibility over accessibility-critical markup and
  interaction.
- Avoids fighting the core mobile overlay if the desired behavior diverges from
  core.
- Lets rich mobile-only drawer content be modeled separately from navigation
  links.

Risks:

- More code to own and test.
- Must preserve semantic navigation behavior and avoid diverging from admin menu
  content.
- Needs a careful parser/renderer for Navigation block content; avoid ad hoc
  string parsing where WordPress block parsing APIs can be used.

## Recommended First Implementation Path

Completed on 2026-06-24:

- Header/footer structural source-of-truth cleanup.
- Stable top-nav class and generated-selector removal.
- Navigation tokenization and stylesheet consolidation.
- PNS mobile drawer renderer and behavior layer fed by `wp_navigation` `1035`.
- Regression coverage for closed visual baseline and mobile drawer behavior.

Next implementation path:

1. Audit the new PNS drawer manually with keyboard and assistive-technology
   workflows:
   - trigger naming and target size;
   - focus entry, wrapping, Escape close, and return;
   - section button announcement;
   - long grouped content and long-label wrapping.
2. Decide whether desktop Navigation still passes the same accessibility bar or
   needs a PNS-owned renderer in a later slice.
3. Add richer mobile-drawer content only through a separate editable template
   part, pattern, or block source. Keep `wp_navigation` limited to links,
   labels, order, and hierarchy.
4. Keep future nav visual changes behind the same regression gate:
   - standalone theme `pnpm check`;
   - focused nav Playwright contracts;
   - full visual suite when closed-state header or shared nav CSS changes.

Historical first path:

1. Make the standalone filesystem parts the intended structural owner for header
   and footer chrome:
   - sync/export the saved `Header` and `Footer` template-part records;
   - decide whether release should clear those DB overrides or keep them with an
     explicit migration/export process.
2. Keep `wp_navigation` `1035` as the editable source for top-nav links and
   hierarchy.
3. Remove generated selector dependencies before deeper behavior work:
   - `.wp-container-2`;
   - `nav.wp-container-core-navigation-is-layout-808e6b47`;
   - any future selector tied to generated layout hashes.
4. Split desktop and mobile decisions:
   - desktop may remain core Navigation if the audit passes;
   - mobile should move toward Option E if larger grouped content and stricter
     accessibility control remain requirements.
5. Add tests before visual redesign:
   - rendered source contract for nav `1035`;
   - open/close behavior;
   - section disclosure state;
   - focus movement and return;
   - keyboard-only traversal;
   - long-label and long-content cases.
6. Only use `render_block_core/navigation` for narrow additions such as stable
   classes or data attributes. Do not make broad string-based mutations to core
   nav markup.
7. Build the PNS mobile drawer as the first custom interaction layer if core
   overlay behavior cannot satisfy the test contract.

## Verification Checklist

Desktop and wide-screen checks:

- Top-level links fit without overlap at common desktop widths.
- Dropdowns open on hover and keyboard focus.
- Submenu links are reachable by keyboard.
- Hover/current states match the agreed visual design.
- Logo and nav do not overlap.

Mobile checks:

- Open menu button is visible, large enough, and not overlapped by the logo.
- Full-screen/overlay menu opens and closes reliably.
- Focus moves into the open menu and returns to the trigger predictably.
- Escape closes the menu.
- Focus is trapped or otherwise bounded while the drawer is open, according to
  the selected interaction model.
- Body/page scroll behavior is intentional while the drawer is open.
- Submenu toggles are large enough to tap.
- Collapsible sections expose clear expanded/collapsed state.
- Collapsible controls use real buttons and expose correct `aria-expanded` and
  `aria-controls` state.
- Current page/section state is exposed visually and semantically.
- Long labels wrap without clipping.
- Long grouped content does not overlap, clip, or trap focus.
- All nested links are reachable.
- The page behind the open menu does not create horizontal scrolling.

Code checks:

- Run CSS compile after style changes:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
```

- Run the standalone theme check before finalizing:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
```

- Use browser verification at `http://localhost:10008` for any layout or
  interaction change.

## Evidence Commands

These commands identify the current live navigation source:

```bash
wp option get stylesheet
wp option get template
wp post list --post_type=wp_template_part,wp_navigation --post_status=any --fields=ID,post_type,post_name,post_title,post_status,post_modified --format=table
wp post get 1027 --field=post_content
wp post get 1026 --field=post_content
wp post get 1035 --field=post_content
wp post get 1032 --field=post_content
```

In Codex's restricted shell, database-backed WP-CLI and local HTTP may need
elevated local-service access because Local's database/socket and web server sit
outside the sandbox.
