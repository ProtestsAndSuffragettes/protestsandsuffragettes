# Standalone Template Ownership and Health Remediation Plan

Created: 2026-07-13.

Status: Cuts 0 through 7 are implemented locally. The Cut 7 release-handoff,
editor, layout, and lean visual landing gates pass. The Mary Barbour Split
Section follow-up preserves its intentional `edge-media-right` selection and
repairs the shared responsive block contract in code.

All paths are relative to the project root.

## Goal

Make the settled visual and structural parts of the active
`protestsandsuffragettes-standalone` theme reliably code-backed, without
turning the whole site into a Git-managed CMS.

The intended result is a clear hybrid model:

- code owns site structure, design tokens, template composition, and fixed
  layout behavior;
- editors own ordinary page/post/Herstory content; Administrators own the named
  site data for navigation and footer social links through deliberately narrow
  control paths;
- a release guard identifies an unintended database override before it becomes
  a deployment or handoff surprise.

This plan also captures the remaining evidence-led health work from the
standalone-theme review. It deliberately does not reopen completed broad CSS,
render-filter, or plugin-ownership programmes.

## Non-Goals

- Do not make navigation, social links, ordinary editorial content, or campaign
  copy code-only.
- Do not remove Gutenberg from page, post, or Herstory editing.
- Do not automatically overwrite or delete a DB-backed record merely because a
  file exists.
- Do not edit third-party plugins, including Ecwid.
- Do not restart a broad cascade-layer migration, theme rewrite, or generic
  custom-block redesign.
- Do not remove `appearanceTools` or broad editor capabilities before evidence
  proves that the remaining navigation/social workflow still works.

## Current Evidence

The 2026-07-13 review confirmed that the standalone theme is active, its
formatting/CSS lint/asset audit pass, and its focused editor and visual checks
have useful coverage. The project-owned plugin split is generally healthy:
`pns-blocks` owns custom block behavior, `pns-herstories` owns the content
model, and `ran-forms` owns the modern form integration.

The main handoff risk is live source divergence. The active database currently
contains meaningful overrides for code-backed structural surfaces, including
templates such as `home`, `page-light-surface`, `archive-herstory`, and
`single`, plus a synced workshop pattern. The known examples include a DB
template that expands a pattern reference into copied markup and a saved
workshop query that has lost a taxonomy-slug constraint. The current theme
documentation names this risk, but it is not yet an operational release gate.

The review also found these residual concerns:

1. `pns/featured-post` emits Split Section markup without declaring the Split
   Section style it relies on. It is used by live templates, so this is a real
   contract defect even where current page asset order happens to mask it.
2. Ecwid's third-party frontend assets are emitted on editorial routes as well
   as Shop. The theme correctly scopes its own small transition helper, but any
   vendor-asset reduction must preserve the cart, static product grid, and Shop
   runtime.
3. `styles/components/priority-overrides.css` still duplicates a few rules
   owned elsewhere, and `inc/block-filters.php` has grown into a large mixed
   collection. These are maintenance concerns, not permission to remove
   WordPress/vendor compatibility rules by static search.
4. Test-output naming does not entirely match the Git ignore policy, plugin
   tests are lighter than theme integration tests, and there is no established
   PHP static-analysis/CI gate.

## Locked Decisions

### Code owns structural composition

The following are code-backed once this plan has reconciled their approved live
state and removed only their corresponding DB overrides:

- `theme.json`, fonts, CSS, assets, and editor defaults.
- All `templates/*.html`: they own header/footer placement, hero/query/card
  composition, comments/pagination, surface classes, and the `post-content`
  slot. They do not own the body placed in that slot.
- `parts/header.html` and `parts/footer.html`: they own shell markup, placement,
  classes, responsive layout, and references to named data.
- Theme patterns used as template composition, query/card markup, fixed block
  configuration, and plugin-owned block behavior.
- The form integration and its fixed composition, owned by `ran-forms` rather
  than an editable shortcode bridge.

### Editors own normal content; Administrators own named site data

- Primary, footer, and banner CTA `wp_navigation` records remain
  Administrator-owned data. `navigation/*.html` remains a seed/recovery
  snapshot, not a file that a release overwrites. Normal WordPress Editors do
  not receive `edit_theme_options`.
- Footer social links remain Administrator-owned data, but move out of direct
  file-backed footer markup into one theme-owned setting,
  `pns_footer_social_links`. A hidden dynamic `pns/footer-social-links` block
  in the file-backed footer renders it. The fixed service catalog and display
  order remain code; Administrators control only enabled services and HTTPS
  URLs. Do not use a synced `wp_block` or repurpose `connect-social`.
- Page, post, and Herstory `post_content`, images, normal block attributes, and
  campaign copy remain editor-owned.
- A theme pattern inserted into ordinary post content is an editor-owned copy.
  It is not a code-owned instance merely because its starter lives in PHP.

### There is no half-owned record

Every shared WordPress surface must be classified as one of:

- **Code**: files are authoritative; an unexpected DB override is release
  drift.
- **Editor data**: a DB record is authoritative; code may provide a seed or a
  stable reference but must not overwrite it.
- **Administrator data**: a named DB record or project setting is authoritative
  through an Administrator-only control path; code may provide its stable
  renderer but normal Editors cannot edit it.
- **Managed fixture**: a file may create/recover a missing record, while an
  explicit export/audit is required before promotion in either direction.

## Guardrails

- Start each implementation cut with an active-theme and DB freshness check.
- Export and review the live record before changing a template, part, synced
  pattern, navigation, or global-style record.
- For a code-owned template/part migration: first reconcile the intended state
  into the file, validate it, back up the DB override, then remove only that
  override after a browser check. Never reverse that sequence.
- Do not delete or regenerate `wp_navigation` records during structural
  migration.
- Do not let an Administrator social-settings edit recreate a full footer
  template-part override.
- Treat block locking as an authoring guardrail, not security. Capability and
  REST access must be verified separately before changing roles.
- Keep existing WordPress/plugin `!important` exceptions unless browser
  evidence proves the exact rule is redundant.

## Dex Tracking

Standalone tracker:

```bash
dex --storage-path app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex list wf38az6c
```

Parent:

- `wf38az6c` - Formalize standalone theme ownership and resolve health findings

Cuts:

- `h7o92y3q` - Ownership Cut 0 - establish the live ownership matrix
- `zjo5vi1u` - Ownership Cut 1 - carve Administrator-owned navigation and social slots
- `zuv0avjk` - Ownership Cut 2 - restore code authority for settled template structure
- `5bh1y7x5` - Ownership Cut 3 - add a DB versus file release guard
- `7atwsiig` - Health Cut 4 - repair the Featured Post block asset contract
- `dgjuz7r3` - Health Cut 5 - measure and scope non-store Ecwid runtime assets
- `zezkb87d` - Health Cut 6 - retire only proven CSS and filter handoff debt
- `iz2dtjtf` - Ownership Cut 7 - validate release and editor handoff

Dependency rules:

- Cut 0 is complete. Cut 1 follows it; Cut 2 follows Cut 1 so social data is safe before a
  file-backed footer is restored.
- Cut 3 follows Cut 2.
- Cuts 4 and 5 follow Cut 0 and can run independently from the ownership
  migration after its baseline is known.
- Cut 6 follows Cut 3; it must not reopen completed broad cleanup work.
- Cut 7 waits for Cuts 3 through 6.

## Cut Plan

### Cut 0 - Establish the Live Ownership Matrix

Dex: `h7o92y3q`

Produce and maintain
[`2026-07-13-standalone-ownership-matrix.md`](2026-07-13-standalone-ownership-matrix.md)
as the concise, versioned ownership matrix for every live `wp_template`,
`wp_template_part`, `wp_navigation`, `wp_block`, and relevant
`wp_global_styles` record. The document starts with the initial ownership
decisions for cards, Featured Post, header/footer shells, navigation, and
social links. Append the live record inventory with source/DB hash
relationship, page assignment, owner class, editor control path, release
behavior, and rollback export.

Verify the actual WordPress capability and REST behavior for an editor who may
change navigation/social data but must not casually edit templates or global
styles. Do not assume that simply removing `edit_theme_options` achieves both
goals.

Acceptance:

- The standalone matrix document classifies every shared surface as Code,
  Editor data, or Managed fixture.
- It records and verifies the initial card/Featured Post/header/footer/social
  ownership decisions before later cuts change live records.
- Navigation and social-link editing have a tested proposed control path.
- No live DB record or theme/plugin behavior changes in this cut.

### Cut 1 - Carve Administrator-Owned Navigation and Social Slots

Dex: `zjo5vi1u`

Preserve `pns-primary-navigation`, `pns-footer-navigation`, and
`pns-banner-cta-navigation` as Administrator-owned WordPress data, while
keeping header/footer placement and classes in files. Normal WordPress Editors
must not receive `edit_theme_options`, because it would also expose templates
and Global Styles.

Replace only the direct Social Links declaration in the footer with a hidden
theme-owned dynamic `pns/footer-social-links` block. It renders a
`pns_footer_social_links` setting whose Settings API control is restricted to
`manage_options` at **Appearance → Footer Social Links**. Do not use a synced
`wp_block` or `connect-social` for this purpose. Keep the surrounding footer
markup and current Core Social Links HTML classes intact. The code-owned
service list and order are fixed; Administrators can supply a HTTPS URL or
leave it empty to hide that service.

Document the Administrator navigation and social-links control paths, their
capabilities, code defaults, and recovery/rollback procedure.

Acceptance:

- An Administrator can change a navigation label/URL and footer social links
  without changing a template or part; normal WordPress Editors cannot.
- The file-backed header/footer still render the changed data correctly.
- Navigation files are not treated as deployment-authoritative content.

Implementation notes:

- The setting and dynamic block live in the standalone theme, alongside the
  existing Administrator-only theme setup screen. This keeps the narrow site
  control with the code-owned footer shell rather than creating a second plugin
  settings architecture.
- The missing-option fallback contains the seven former footer links. A saved
  empty array deliberately renders no links; deleting the option restores the
  versioned defaults.
- Before the local DB footer part was updated to the new block declaration, its
  exact previous source-equivalent content was pinned in
  `docs/jobs/standalone-template-ownership-db-backups/20260713T105646Z-before-wp_template_part-5980-footer.json`.
  Cut 2 still owns removing that temporary matching DB override.
- Cut 1 validation passed PHP syntax, footer block serialization, the live
  Administrator/Editor capability and Settings API contract, a temporary
  save/render/restore proof that did not touch the footer part, browser markup
  inspection, the focused homepage footer contract, and the desktop/mobile
  navigation lane (12 tests). The Mary Barbour full-page mismatch observed at
  the time was unrelated to this social-link change and was later closed by
  the Cut 7 Split Section follow-up below.

### Cut 2 - Restore Code Authority for Settled Template Structure

Dex: `zuv0avjk`

For each Code-classified template/part, reconcile the approved current layout
into the file source and remove only its matching DB override. Preserve
`post-content`, editor data slots, all navigation records, and campaign copy.

Prioritize `home`, `single`, `archive-herstory`, the light-surface page
templates, and shared template parts only after Cut 0 proves the intended live
state. Prefer clean code abstractions such as pattern references over
editor-expanded markup.

Acceptance:

- Browser output matches the approved state after the targeted DB override is
  removed.
- No code-owned template remains silently shadowed by a stale DB override.
- Every changed record has a rollback export and focused visual/template check.

Outcome (2026-07-13):

- Re-audited all eleven approved standalone `wp_template` and
  `wp_template_part` rows against their source files. The seven byte-equal
  records and the four already-approved Code-wins pattern/serialization
  differences still matched the Cut 0 matrix exactly; no new DB drift was
  adopted.
- Exported one recovery manifest per record under
  `docs/jobs/standalone-template-ownership-db-backups/` with prefix
  `20260713T114131Z-`. Each manifest carries the exact original content as
  base64 plus its `rtrim` SHA-256, metadata, and theme terms.
- Permanently deleted only rows `6132`, `6278`, `6138`, `6186`, `6383`,
  `6390`, `6219`, `6221`, `5990`, `5936`, and `5980`. A non-force deletion was
  deliberately avoided because a trashed template still shadows its theme
  file in WordPress.
- Post-delete `get_block_template()` assertions proved every target resolves
  with `source: theme`, `has_theme_file: true`, and the expected source hash.
  Navigation records `1035`, `5259`, and `1032`; synced block `6487`; global
  styles `5256`; and the `pns_footer_social_links` option were fingerprinted
  before and after and remain unchanged.
- The block-template parser and retained-render-bridge checks passed. The
  desktop/mobile navigation lane passed 12/12, and the 25 Cut 2-relevant
  desktop template contracts passed. The only excluded assertion is the
  pre-existing Contact Us contract: page `6236` is currently assigned the
  editor-owned `default` template, not
  `page-light-surface-no-contact-form`. Cut 2 preserves that assignment;
  changing it requires an explicit editorial/layout decision.

### Cut 3 - Add a DB-versus-File Release Guard

Dex: `5bh1y7x5`

Add a repeatable report that separates unexpected drift in Code-classified
templates/parts from expected Editor-data changes in navigation, social, and
content records. It should support a warning locally and a chosen fail/warn
policy in the release workflow, without auto-syncing either direction.

Acceptance:

- A developer can run one documented command before template work or release.
- The report identifies an unexpected code-owned DB override by name.
- Expected navigation/social edits do not create false alarms.
- Documentation covers CLI, Site Editor/UI recovery, backup, and rollback.

Outcome (2026-07-13):

- Added the read-only `scripts/audit-template-ownership.php` report and package
  commands `pnpm audit:template-ownership` (local warn policy) and
  `pnpm check:template-ownership` (release strict policy). The report has no
  sync, update, delete, restore, or export mode.
- The guard discovers every `templates/*.html` and `parts/*.html` source file,
  then detects active-theme `wp_template` and `wp_template_part` rows in every
  WordPress status, including trash. Any row is actionable even when its
  serialized content equals the file, because it still shadows code.
- Administrator navigation and footer-social data, managed synced-block
  fixtures, and ordinary editor content are reported or excluded without
  becoming template failures. The neutral standalone global-styles record is
  expected; a real user styles/settings payload requires review and blocks the
  strict gate.
- Clean local baseline: 17 code source files, zero saved standalone
  templates/parts, all sources resolving as theme files, expected navigation,
  footer-social defaults, fixtures, and neutral global styles. A disposable
  saved `single-full-width-news` override proved warn reports the exact slug
  while strict exits non-zero; the probe was permanently deleted and the clean
  strict baseline was restored.

### Cut 4 - Repair the Featured Post Block Asset Contract

Dex: `7atwsiig`

Make `pns/featured-post` self-contained or explicitly share the Split Section
asset it renders with. Keep the source and generated block metadata aligned,
correct the README block inventory, and add a focused editor/frontend check.

Acceptance:

- Featured Post has all required front-end and editor styling when used alone.
- Source and `build/` metadata agree.
- The visual/editor regression covers the independent asset contract.

Outcome (2026-07-13):

- `pns/featured-post` now explicitly declares the registered Split Section
  frontend (`pns-split-section-style`) and editor
  (`pns-split-section-editor-style`) handles. The block continues to own the
  query renderer; it does not duplicate layout CSS or depend on the page also
  containing a `pns/split-section` block.
- Source and generated `block.json` now agree. The project-owned plugin README
  records both the query block family and its deliberate shared-style contract.
- `scripts/verify-featured-post-asset-contract.php` checks source/build
  metadata plus the active WordPress registry. The focused Home frontend
  regression also verifies the Split Section stylesheet is present where the
  Featured Post composition renders.

### Cut 5 - Measure and Scope Non-Store Ecwid Runtime Assets

Dex: `dgjuz7r3`

Measure the vendor CSS, JavaScript, preconnect, and cart behavior on editorial
routes. Choose only a supported plugin configuration or a narrow project-owned
integration hook, and prove it does not break Shop, cart visibility, static
product grids, product routes, or cached output.

Acceptance:

- The before/after asset map covers an editorial route and Shop.
- Any delivery reduction has focused Shop/Ecwid/cart regression coverage.
- No third-party Ecwid plugin file is edited.

Outcome (2026-07-13):

- The baseline showed that Home, News, and Herstories emitted the Ecwid
  stylesheet, frontend helper, five preconnects, a bootstrap-script prefetch,
  and a Shop prerender without rendering a native storefront or cart. The known
  uncompressed editorial overhead was at least 15.35 KB: `frontend.css`
  (2,272 B), `frontend.js` (2,030 B), its icon font (3,240 B), and the
  prefetched external bootstrap (7,811 B). Jetpack Boost can bundle the helper
  with other scripts, but does not remove that delivery cost.
- `inc/assets.php` now makes one guarded decision for native Ecwid delivery. It
  keeps the runtime for Ecwid's own store-page predicate (including product and
  cart routes), native Ecwid blocks/shortcodes, active Ecwid widgets, or a
  deliberately site-wide floating cart. A
  `pns_standalone_load_native_ecwid_assets` filter remains as the explicit
  future escape hatch. The project-owned `ran/ecwid-shop-teaser` is correctly
  excluded because it renders static server-side cards that link into Shop.
- On a request that does not need the native runtime, the standalone theme
  removes Ecwid's global metadata/config callbacks before they run and removes
  the vendor enqueue callback before its default priority. Theme CSS only
  depends on `ecwid-css` when that runtime is needed. No Ecwid plugin file or
  database option was changed. The plugin's `ecwid_hide_prefetch` option was
  rejected because it suppresses Shop preloading globally rather than scoping
  it by route.
- `pnpm audit:ecwid-assets` is the repeatable after-map. It now proves zero
  native Ecwid CSS, frontend script, head hints, or cart markup on Home, News,
  and Herstories, while `/shop/` retains the stylesheet, runtime, storefront,
  and cart markup. The focused Playwright contract repeats that assertion and
  also proves the Home teaser remains present.
- Validation passed: PHP syntax, Node syntax, touched-file Prettier, the route
  asset audit, the new focused delivery Playwright test, the existing product
  view-transition/storefront-reserve/Home-teaser contracts, and the existing
  Ecwid cart recommendation check. The broad `@ecwid` invocation still reaches
  the known stale Shop full-page screenshot baseline (expected height 2537 px,
  current 2580 px); its non-snapshot Shop/Ecwid contracts passed, and this cut
  does not refresh that unrelated baseline.

### Cut 6 - Retire Only Proven CSS and Filter Handoff Debt

Dex: `zezkb87d`

After the ownership guard exists, reassess only the concrete review findings:
the duplicated priority tail, the dead footer colour declaration, insufficient
owner/removal-gate comments, and the mixed `block-filters.php` handoff cost.
Decide PHP static-analysis/CI feasibility and correct the test-artifact ignore
path.

Acceptance:

- Each deleted rule/filter is proven redundant by the cascade/runtime, not by
  selector search alone.
- Retained bridges name owner, external pressure, and removal gate.
- Any file split is by domain and preserves hook order/behavior.
- Tooling recommendations are executable in this checkout.

Outcome (2026-07-13):

- CSSOM evidence proved that `styles/components/footer.css` was an obsolete
  scaffold declaration: removing its lone `#fff` rule left the rendered footer
  copyright white because `footer-layout.css` is the canonical component owner
  and the unlayered tail is the required Core/global conflict bridge. The file
  and both barrel imports were removed.
- The other apparent priority-tail duplicates were retained intentionally. CSSOM
  removal changed the footer copyright from white to `rgb(79, 77, 73)` and the
  separator from 400 px to 546 px. Every remaining tail rule now names its
  layered owner, the unlayered external pressure, and its cascade-proof removal
  gate.
- No retained render filter was proven redundant. The Herstories archive and
  entry-navigation callbacks moved intact into `inc/herstories.php`, their
  feature-domain owner; `inc/block-filters.php` now retains only shared
  cross-domain hooks. The shared block-class helper stays there because stable
  Query pagination also consumes it. The render hooks target distinct block
  contracts, so the bootstrap registration move does not change their effective
  order or output.
- Both root Git and theme Prettier ignore rules now cover the runner's actual
  `test-results*` directories, including `test-results-desktop-smoke/`.
- `pnpm lint:php:syntax` is now the immediate, executable syntax gate. PHPStan
  or Psalm is intentionally deferred: this checkout has no analyzer, WordPress
  stubs, configuration, or CI, while the existing WPCS scan has 308 errors and
  212 warnings that require a separate owned baseline rather than a misleading
  Cut 6 gate.
- Validation passed PHP syntax, CSS lint/build and asset audit, package JSON and
  touched-CSS formatting, `git diff --check`, the database-backed retained
  bridge validator, 12 desktop/mobile navigation contracts, and the targeted
  template/archive lane. `pnpm check` remains blocked by the pre-existing
  formatting mismatch in `tests/visual/frontend.spec.ts`; this cut did not
  modify that file.

### Cut 7 - Validate Release and Editor Handoff

Dex: `iz2dtjtf`

Validate the full hybrid model: parser checks for code templates, ownership
report, Administrator updates to navigation/social data, template/browser contracts,
Featured Post styling, Ecwid behavior, and normal quality gates. Archive this
plan only after the deployment and rollback runbook is reproducible.

Acceptance:

- Code-owned structure survives a release without DB shadowing.
- Administrator-owned navigation/social data survives a release unchanged.
- The documented rollback restores a targeted template/part without touching
  Administrator data.
- `pnpm check`, relevant Playwright lanes, PHP/template parsing, and the
  release-guard report pass or have a named external blocker.

Outcome (2026-07-13):

- Added `scripts/validate-release-handoff.php`, with `capture`, `verify`, and
  default `probe` modes. The local ignored snapshot contains only record IDs,
  state, and SHA-256 fingerprints: it contains neither navigation markup nor
  social URLs.
- The probe verifies the file-backed header/footer references, requires all
  three named navigation records, runs the non-overwriting seed path, and
  confirms its result is `kept` for every record with no before/after state
  change. It also covers the social-links setting and global-styles identity.
- Capability and REST checks confirm that a normal WordPress Editor cannot
  write templates, template parts, navigation, or footer social links; an
  Administrator can use the narrow Navigation and Footer Social Links control
  paths.
- Strict ownership, release-handoff capture/verify/probe, template parsing,
  retained-render-bridge, PHP syntax, CSS/build, and Ecwid route-asset checks
  pass locally. The editor suite passes six real contracts with two
  intentionally skipped private-fixture tests. The PNS Blocks editor stylesheet
  now preserves the frontend Split Section slideshow crop contract after
  Jetpack hydration, and its test waits for that real geometry rather than
  sampling an intermediate zero-height state.
- Mary Barbour's saved `edge-media-right` selection is intentional and remains
  editor-owned data. No DB mutation was required. The apparent content problem
  was a shared PNS Blocks regression: copy padding was dropped when Split
  Section CSS moved into the plugin, while existing blocks retained inline zero
  padding values.
- The plugin now owns 1rem stacked / 2rem row copy spacing, a media-facing seam
  gutter, and full-row non-video media with `object-fit: cover`. The row
  breakpoint is 60rem: the 900px tablet project stacks, while 960px and the
  reviewed 1054px reference remain two-column. Image, Featured Post, and
  Jetpack slideshow media fill the row; video remains uncropped at 16:9.
- The responsive contract is covered at 390, 768, 800, 900, 960, 1054, and
  1280px across all four variants. Validation passed the plugin build/lints,
  theme `pnpm check`, 28 desktop plus 8 mobile layout contracts, six editor
  tests with two intentional fixture skips, and the lean visual landing gate
  with 50 desktop plus 10 mobile tests. Only intentionally affected local
  snapshots were refreshed.

## Deferred Work

The following review findings are recorded but must not expand this plan unless
the relevant cut proves they still require work:

- broad CSS layering migration or vendor override redesign;
- generic plugin rewrites or direct third-party plugin modifications;
- a global removal of `appearanceTools`;
- a complete split of `inc/block-filters.php` without current runtime evidence;
- broad visual-baseline refreshes unrelated to a changed contract.

## Open Questions

1. Resolved: Navigation is Administrator-only through core Navigation; footer
   social links are Administrator-only through `pns_footer_social_links` and
   hidden `pns/footer-social-links`. Normal WordPress Editors do not receive
   Site Editor access.
2. Should the site logo and other Site Identity values join Administrator data
   for navigation/social, or remain a developer-managed theme setting?
3. Should shared promotional copy (`read-all-about-it*`, Shop intro) remain
   synchronised editor data, or should only its fixed layout move into a
   code-backed template/pattern with a smaller editor copy slot?
