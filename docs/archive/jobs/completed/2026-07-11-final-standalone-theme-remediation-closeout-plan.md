# Final Standalone Theme Remediation Closeout Plan

Created: 2026-07-11.

Status: Completed.

All paths are relative to the project root.

## Goal

Land a tight final remediation pass for the active
`protestsandsuffragettes-standalone` theme. This plan exists because several
concerns escaped earlier completed remediation plans, especially around real CSS
layer adoption, DB-backed WordPress source-of-truth handling, duplicate block
CSS delivery, retained render bridges, plugin ownership, editor governance, and
normal validation gates.

This is not another broad design-system migration. Each cut must either remove
the concern, fail closed, or leave an explicitly documented runtime exception
with focused validation.

## Non-Goals

- Do not redesign the visual system.
- Do not rewrite the standalone theme architecture from scratch.
- Do not delete compatibility selectors or render bridges from static search
  alone.
- Do not treat file-backed templates, parts, synced-pattern fixtures, or
  navigation fixtures as live truth without a DB freshness check.
- Do not move legitimate presentation markup out of the theme into plugins.
- Do not close this plan or move it to `docs/jobs/__completed/` until the final
  validation cut has passed.

## Dex Tracking

Standalone theme queue:

```bash
dex --storage-path app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex list 4l44i4cy
```

Parent:

- `4l44i4cy` - Final standalone theme remediation closeout

Cuts:

- `8xia1v75` - Closeout Cut 0 - freeze escaped-concern baseline
- `bchmw0hk` - Closeout Cut 1 - harden DB/file source-of-truth and refs
- `eeuvp4ok` - Closeout Cut 2 - finish real cascade layers and CSS delivery
- `2jt9fbnj` - Closeout Cut 3 - reduce theme-owned priority and motion leakage
- `ds6gcgvh` - Closeout Cut 4 - resolve plugin ownership leaks
- `ovz985hf` - Closeout Cut 5 - scope Ecwid runtime and fix source maps
- `1j6asqpm` - Closeout Cut 6 - make editor governance non-destructive
- `93pgz5db` - Closeout Cut 7 - repair tooling and normal gates
- `867xl40e` - Closeout Cut 8 - final validation and archive discipline

Dependency rules:

- Cut 0 blocks every implementation cut.
- Cut 3 and Cut 5 also wait for Cut 2, because CSS layer and delivery ownership
  affects priority cleanup and motion/source-map work.
- Cut 6 also waits for Cut 1, because editor governance must agree with the
  final source-of-truth/ref policy.
- Cut 8 waits for Cuts 1 through 7.

## Current Evidence

The health review confirmed:

- Active theme is `protestsandsuffragettes-standalone`.
- Active project-owned plugins include `pns-blocks`, `pns-herstories`, and
  `ran-forms`.
- Live DB-backed `wp_template`, `wp_template_part`, `wp_block`, and
  `wp_navigation` records exist and can be fresher than file fixtures.
- `styles/shared/layers.css` declares a layer order, but important authored CSS
  still arrives unlayered or through separate block-scoped delivery paths.
- `styles/css-assets.json` intentionally allowlists duplicate load paths for
  `styles/blocks/core-cover.css`, `styles/blocks/core-navigation.css`, and
  `styles/blocks/core-quote.css`.
- Current authored standalone theme CSS contains 103 `!important` occurrences
  outside `styles/dist/`: Ecwid 79, EmailOctopus 8, core Navigation drawer 6,
  footer layout 5, Herstories bios 2, core Navigation desktop 1, core Social
  Links 1, and Jetpack slideshow 1. The remaining split-section priority rules
  live in `pns-blocks`, where split-section structure belongs.
- Compiled frontend CSS measured with Wallace at 109.7 KB, 537 rules, 682
  selectors, 1,546 declarations, and 118 compiled `!important` declarations.
- `scripts/ecwid-view-transitions.js` is enqueued site-wide and installs a
  document click handler plus body-wide `MutationObserver`, even though it only
  serves Shop/Ecwid behavior.
- Generated CSS source-map comments currently point to
  `styles/dist/*.css.map` from files already inside `styles/dist/`.
- `allowed_block_types_all` rebuilds a full allowed block list instead of
  preserving prior policy.
- `docs/css/README.md` says it is the standalone CSS source of truth while
  still naming old child-theme paths in its build-path section.

Cut 0 refreshed on 2026-07-11 confirmed:

- `stylesheet` and `template` both resolve to
  `protestsandsuffragettes-standalone`; the standalone theme is the active
  runtime theme, not a child/parent pair.
- Active project-owned plugins are `pns-blocks`, `pns-herstories`, and
  `ran-forms`. Active vendor/block plugins include Ecwid, EmailOctopus,
  Jetpack, Jetpack Boost, Dynamic Year Block, Animations For Blocks, and cache
  or security plugins that can affect rendered checks.
- Published DB-backed records currently include 13 `wp_template` posts, 2
  `wp_template_part` posts, 5 `wp_block` synced patterns, and 3
  `wp_navigation` posts. Several DB records are newer than file fixtures, so
  DB freshness remains a hard gate before template, navigation, part, or synced
  pattern edits.
- Page-template assignments using non-default templates are: Shop
  `page-light-surface`, Edu Giveaway `page-no-contact-form`, Search
  `page-search`, Contact Us/Thank You `page-light-surface-no-contact-form`, and
  the private PNS Style Guide `page-light-surface`.
- CSS asset audit still passes with warnings for the three allowed duplicate
  block load paths: `core-cover`, `core-navigation`, and `core-quote`.
- Wallace is unchanged at 109.7 KB, 537 rules, 682 selectors, 1,546
  declarations, and 118 compiled `!important` declarations.
- Source `!important` counts outside `styles/dist/` are now concentrated in
  Ecwid 79, EmailOctopus 8, core Navigation drawer 6, footer layout 5,
  Herstories bios 2, core Navigation desktop 1, core Social Links 1, and
  Jetpack slideshow 1. Buttons, banner CTA, and theme-owned split-section
  priority work are no longer active categories; split-section structural
  priority lives in `pns-blocks`.
- `styles/frontend.css` and `styles/editor.css` still import most buckets
  unlayered; only settings and utilities are explicitly layer-imported at the
  entrypoint.
- `pns_standalone_resolve_template_ref_id()` still used numeric fallback IDs
  after checking only `post_type`; the remediation must require fallback slug
  agreement before returning an ID.
- `allowed_block_types_all` still rebuilt a full registered-block allowlist;
  the remediation must preserve prior array/false policy and subtract only the
  unsupported blocks.

## Completed Work That This Plan Must Hold Accountable

- `docs/jobs/__completed/2026-07-07-css-cascade-layer-migration-plan.md`
  claimed the CSS layer migration was landed for safe defaults. The new plan
  must verify that claim against actual imports, block-scoped CSS, and compiled
  output, then finish or narrow every remaining exception.
- `docs/jobs/__completed/2026-07-11-standalone-theme-adversarial-review-remediation-plan.md`
  claimed actionable adversarial-review findings were closed. It still retained
  the three duplicate block CSS load paths as allowed exceptions; this plan must
  either remove them or prove why they remain necessary.
- `docs/jobs/__completed/2026-07-07-core-block-selector-classification-plan.md`
  and
  `docs/jobs/__completed/2026-07-07-core-block-selector-classification-inventory.md`
  classified selectors but did not remove duplicate block delivery or DB-backed
  compatibility risk.
- `docs/jobs/__completed/2026-07-06-render-filter-template-remediation-plan.md`
  moved Shop light-surface ownership away from the old render-filter path, but
  retained several other runtime bridges.
- `docs/jobs/__completed/2026-07-07-retained-render-bridge-audit-plan.md` and
  `docs/jobs/__completed/2026-07-07-retained-render-bridge-remediation-plan.md`
  narrowed and documented retained bridges, but left some conversion/removal
  decisions gated on later evidence.
- `docs/jobs/__completed/2026-06-22-custom-blocks-patterns-audit.md` mapped the
  WordPress/block/plugin ownership landscape but deferred dependency reduction
  and DB source consolidation.
- `docs/jobs/__completed/2026-06-24-pns-custom-blocks-plugin-plan.md`
  established `pns-blocks`, but some Ecwid/product-grid integration and runtime
  ownership remains split between theme and plugin.
- `docs/jobs/__completed/2026-07-07-pattern-inserter-allowlist-cleanup-plan.md`
  left editor governance reactive rather than making the allowlist policy
  robust.
- `docs/jobs/__completed/2026-07-04-light-surface-theme-extraction-plan.md`
  created a reusable light-surface contract, but Ecwid runtime shims and
  compatibility hooks remained.
- `docs/jobs/__completed/2026-07-06-vendor-override-debt-final-reevaluation-plan.md`
  re-inventoried vendor/plugin override debt but left active priority rules in
  vendor and theme-owned areas.
- `docs/jobs/__completed/2026-07-06-motion-perceived-loading-plan.md` deferred
  true view-transition work around vendor-hydrated regions.
- `docs/jobs/__completed/2026-07-06-visual-suite-fastlane-optimization-plan.md`
  made lanes more usable, but validator coverage still depends on choosing the
  right lane.

## Cut Plan

### Cut 0 - Freeze Escaped-Concern Baseline

Dex: `8xia1v75`

Purpose: create the evidence baseline before edits.

Required work:

- Re-run active theme and active plugin checks with WP-CLI.
- List live `wp_template`, `wp_template_part`, `wp_block`, `wp_navigation`, and
  relevant page-template assignments.
- Compare current file-backed fixtures against live DB records where the later
  cuts will touch templates, parts, synced patterns, or navigation.
- Re-run CSS asset audit and Wallace metrics.
- Inspect layer placement in source and compiled CSS.
- Confirm current `!important` counts by file family.
- Inspect current project-owned plugin tooling state, especially `pns-blocks`.
- Record exactly which completed plan each escaped concern belongs to.

Acceptance:

- No source remediation happens in this cut.
- The plan doc is updated with any changed live evidence.
- All later cuts have exact acceptance checks before implementation starts.

### Cut 1 - Harden DB/File Source-Of-Truth And Refs

Dex: `bchmw0hk`

Purpose: make WordPress source-of-truth rules impossible to miss and make
slug-backed refs fail safely.

Required work:

- Update standalone theme docs so a developer knows which DB-backed records can
  override file fixtures and how to audit freshness before editing.
- Harden `pns_standalone_resolve_template_ref_id()` so numeric fallback IDs do
  not silently render the wrong record. Preferred fix: require fallback
  `post_name` to match the requested slug. Failing closed is acceptable.
- Add focused tests or validation fixtures for missing and mismatched navigation
  and synced-pattern refs.
- Re-check saved template/navigation/synced-pattern fixtures after any DB sync.

Acceptance:

- A missing slug cannot silently resolve to an unrelated local ID.
- The handoff docs name the DB/file source-of-truth rule in one obvious place.
- Validation proves both success and failure behavior.

### Cut 2 - Finish Real Cascade Layers And CSS Delivery

Dex: `eeuvp4ok`

Purpose: finish the layer migration that the completed layer plan claimed to
land.

Required work:

- Convert safe imports to actual layer ownership, not just declared layer order.
- Keep explicit unlayered override tails only where WordPress core, inline block
  supports, or plugin CSS require them.
- Resolve the duplicate block CSS delivery for `core-cover`, `core-navigation`,
  and `core-quote`, or prove with current browser/runtime evidence that each
  duplicate path is still required.
- Update `styles/css-assets.json` and `scripts/audit-css-assets.mjs` so the
  audit enforces the final policy.
- Inspect compiled CSS to prove layer movement is real.

Acceptance:

- The layer model in source matches what the browser receives.
- Duplicate block CSS delivery is gone or each retained duplicate has current
  proof and a removal gate.
- `pnpm compile:css`, `pnpm lint:css`, and `pnpm audit:css-assets` pass.
- Relevant visual lanes pass for affected block/component surfaces.

### Cut 3 - Confirm Retained Priority Exceptions

Dex: `2jt9fbnj`

Purpose: close the current retained priority debt without reopening stale
motion, button, banner, or split-section work.

Required work:

- Do not implement stale work:
  - global View Transition policy already moved to
    `styles/components/motion.css`;
  - Ecwid keeps only marker containment and route-scoped runtime behavior;
  - buttons and banner CTA currently have zero authored `!important`;
  - split-section structural CSS is owned by `pns-blocks`, not the theme.
- Confirm or remove the retained non-vendor priority rules that still exist in
  authored CSS:
  - footer layout flex-basis overrides;
  - Herstories active-date max-width overrides;
  - Jetpack slideshow pagination hiding;
  - core Navigation drawer/desktop adapter overrides;
  - core Social Links padding.
- Replace TODO-style comments with explicit owner/removal-gate comments.
- Keep `styles/components/priority-overrides.css` as a tiny unlayered bridge for
  WordPress/core generated CSS pressure only.
- Update this plan with current `!important` counts and retained/deleted
  decisions.

Acceptance:

- Every retained non-vendor `!important` has a current source-adjacent reason
  stronger than "the screenshot needs it."
- Any removable priority rule is removed and validated.
- No stale View Transition, button, banner, or theme split-section work remains
  in this task.
- `pnpm check` passes, plus focused visual lanes selected by touched surfaces.

### Cut 4 - Reconcile Remaining Plugin/Theme Ownership Bridges

Dex: `ds6gcgvh`

Purpose: reconcile only the plugin/theme boundaries that still exist after
`ran-forms` and `pns-blocks` ownership work.

Required work:

- Do not rework resolved ownership:
  - `ran-forms` owns Jetpack form submission handling, EmailOctopus API/list
    mapping, newsletter opt-in, Turnstile, settings, and health checks;
  - `pns-blocks` owns the generic Ecwid product-grid block, rendering, and
    generic fallback behavior;
  - the theme may continue to provide site-specific Ecwid fallback product data
    through the `pns-blocks` filter when documented as site content.
- Decide whether the theme EmailOctopus shortcode bridge in
  `inc/block-filters.php` should move to `ran-forms` or stay as a narrow theme
  compatibility bridge with a removal gate.
- Reconcile `synced-patterns/contact-form.html`, which still contains a direct
  EmailOctopus shortcode, with the `ran-forms/contact-form` pattern contract.
- Document or narrow the Ecwid render cleanup bridge for `ecwid/store-block` so
  it has a clear owner and removal gate.
- Update plugin/theme docs so `ran-forms`, `pns-blocks`, and the standalone
  theme agree on the boundary.

Acceptance:

- Forms/newsletter/captcha integration remains plugin-owned by default.
- Any retained theme bridge is explicitly presentation, placement,
  compatibility, or site-content only.
- The old direct EmailOctopus synced-pattern fixture is migrated, retired, or
  documented with a removal gate.
- Product-grid/fallback work is not duplicated in the theme.
- Relevant PHP lint/checks and targeted visual lanes pass for touched form,
  EmailOctopus, Ecwid, or contact surfaces.

### Cut 5 - Scope Ecwid Runtime And Fix Source Maps

Dex: `ovz985hf`

Purpose: remove avoidable runtime cost and repair debugging artifacts.

Required work:

- Load `pns-standalone-ecwid-view-transitions` only on Shop/Ecwid surfaces, or
  make it return before installing global listeners on non-Ecwid pages.
- Keep route-transition behavior intact for category and product pages.
- Fix CSS source-map comments so `frontend.min.css.map` and
  `editor.min.css.map` resolve correctly from `styles/dist/`.
- Add source-map inspection to the normal verification path if practical.

Acceptance:

- Non-shop pages do not install the Ecwid body-wide observer.
- Shop/Ecwid transitions still work or fail gracefully with reduced motion.
- Source maps resolve to the existing files.

### Cut 6 - Make Editor Governance Non-Destructive

Dex: `1j6asqpm`

Purpose: make editor rules explicit without overwriting other owners.

Required work:

- Change `allowed_block_types_all` behavior so it preserves an existing array of
  allowed blocks and removes only unsupported blocks from that array.
- Decide whether site-wide editor governance should stay in the theme or move to
  a project plugin later. This cut should fix the destructive behavior either
  way.
- Split `scripts/editor-blocks.js` by domain or add a short editor-bridges index
  documenting:
  - navigation ref resolution
  - navigation overlay cleanup
  - light-surface editor class bridge
  - featured-image focus UI
  - DOM observers and why they exist
- Validate editor behavior with targeted editor tests or authenticated manual
  probes when automation is not practical.

Acceptance:

- Existing block restrictions from other code are not discarded.
- A new developer can find editor bridge ownership without reading the entire
  script.
- Editor parity checks are run for touched surfaces.

### Cut 7 - Repair Tooling And Normal Gates

Dex: `93pgz5db`

Purpose: make normal checks catch the class of problems this plan addresses.

Required work:

- Fix or align `pns-blocks` package tooling so local validation does not hang.
- Decide whether `pnpm check` should include the block-template validator and
  other executable WordPress-specific validators, or create a documented landing
  gate script that does.
- Repair `docs/css/README.md` so standalone paths are correct.
- Ensure no validation command in this plan depends on placeholder arguments.
- Verify `.dex/tasks.jsonl` still parses after Dex edits.

Acceptance:

- A developer has one reliable normal validation path for this closeout.
- `pns-blocks` can be checked locally or the blocker is documented with a
  separate owner.
- CSS docs point at `protestsandsuffragettes-standalone`, not the old child
  theme path.

### Cut 8 - Validate Narrowed Closeout And Archive Plans

Dex: `867xl40e`

Purpose: land the narrowed remediation only after the remaining current-state
cuts are proven.

Required work:

- Do not rerun stale work just because the original closeout plan named it.
  Treat these as already handled unless current validation disproves them:
  DB/file ref hardening, duplicate block CSS delivery, global View Transition
  relocation, Ecwid script route gating, editor governance preservation, CSS
  source-map repair, page-specific CSS ownership, and `pns-blocks`
  split-section ownership.
- Re-read active theme/plugin state and live Dex status.
- Run DB/file freshness audit only for records touched after the last
  validation.
- Run PHP lint and block-template validation for touched PHP/templates/pattern
  fixtures.
- Run `pnpm check` for the standalone theme.
- Run plugin checks only for touched project-owned plugin surfaces.
- Run targeted visual lanes for touched priority, form/EmailOctopus, contact,
  Ecwid, navigation, footer, Herstories, or vendor surfaces.
- Run the lean visual gate before archive/parent closeout.
- Update this plan with final current-state evidence.

Acceptance:

- No stale plan item remains represented as active work.
- Remaining retained bridges/priority exceptions have owner, reason, removal
  gate, and validation evidence.
- The final status section lists exact commands and outcomes.
- The plan can be moved to `docs/jobs/__completed/` only after this gate passes.
- Parent `4l44i4cy` can close only after this task completes.

## Landing Gates

The expected landing gate is:

```bash
git status --short
php scripts/validate-block-templates.php
pnpm lint:css
pnpm audit:css-assets
pnpm compile:css
pnpm analyze:css
pnpm test:visual:smoke
pnpm test:visual:fast
pnpm test:visual
```

Add area lanes based on touched surfaces:

```bash
pnpm test:visual:navigation
pnpm test:visual:templates
pnpm test:visual:shop
pnpm test:visual:ecwid
pnpm test:visual:emailoctopus
pnpm test:editor
```

Use elevated local process access for Playwright in this Local WP checkout when
Chromium hits the known macOS sandbox failure.

## Locked Decisions

- This plan is active work. Do not place it under `__completed` until Cut 8 is
  done.
- Completed-plan references are accountability evidence, not implementation
  authority. Re-check the live tree before editing.
- Cut 0 must run first and remain read-only.
- No cleanup is accepted from static file search alone when DB-backed content,
  plugin output, editor DOM, or rendered markup can own the behavior.
- Any retained exception must have a removal gate, a test, or a reason that is
  stronger than "it was already there."

## Implementation Pass - 2026-07-11

Completed in this pass:

- Cut 1 source-of-truth hardening:
  - template ref fallbacks now fail closed unless both `post_type` and
    `post_name` match the expected slug;
  - retained render bridge validation now covers missing navigation refs,
    missing synced-pattern refs, mismatched fallback IDs, and block blacklist
    preservation behavior;
  - block-template validation now includes `navigation/` and
    `synced-patterns/`;
  - `readme.txt` documents DB-backed records as the source of truth over file
    fixtures.
- Cut 2 CSS delivery hardening:
  - duplicate block-scoped delivery for `core/cover`, `core/navigation`, and
    `core/quote` was removed from theme registration and the CSS asset manifest;
  - CSS asset audit now reports zero allowed duplicate load paths;
  - stylesheet source-map comments are normalized after Lightning CSS output.
- Cut 5 Ecwid/runtime hardening:
  - global Ecwid view-transition CSS was moved out of the Ecwid vendor override;
  - the Ecwid view-transition script now no-ops off Ecwid/shop surfaces;
  - the script enqueue is gated to shop/Ecwid routes with a filter escape hatch.
- Cut 6 editor governance:
  - `allowed_block_types_all` now preserves existing restrictions and removes
    only this theme's unsupported blocks;
  - `scripts/editor-blocks.js` now starts with an editor bridge index.
- Cut 7 docs/tooling:
  - `docs/css/README.md` now points at the standalone theme paths;
  - CSS source-map repair is wired into `pnpm compile:css` and `pnpm check`.

Additional cascade fixes applied while validating:

- Added a small unlayered `styles/components/priority-overrides.css` tail for
  documented WordPress/core pressure points:
  - footer separator width;
  - dark footer copyright text;
  - site-shell `main`/`footer` block gaps;
  - header wide-row/logo-overlap geometry;
  - compact cross-site banner CTA link padding.
- Promoted frontend element defaults out of the low-priority base layer after
  visual evidence showed typography rasterization/wrapping still needed to beat
  unlayered WordPress global styles.

Validation run:

- `pnpm compile:css` - passed.
- `pnpm lint:css` - passed.
- `pnpm format:check` - passed.
- `pnpm audit:css-assets` - passed with 0 allowed duplicates and 0 dormant CSS
  files.
- `php scripts/validate-block-templates.php` - passed for 25 files.
- `php -l inc/assets.php` - passed.
- `php -l inc/block-filters.php` - passed.
- `php -l inc/block-styles.php` - passed.
- `php -l scripts/validate-retained-render-bridges.php` - passed.
- `wp eval-file scripts/validate-retained-render-bridges.php` - passed.

Current status after later remediation:

- The desktop smoke snapshot drift named above is no longer the active blocker.
  It was classified during page-specific CSS ownership closeout and the current
  local smoke/fast/layout/lean visual gates passed afterward.
- Do not archive this plan or complete Cut 8 until the narrowed remaining work
  in Cut 3 and Cut 4 is complete and Cut 8 validates the current tree.

## Staleness Refresh - 2026-07-13

The live Dex tasks were narrowed after later remediation work resolved several
items that this plan originally named.

- `2jt9fbnj` was renamed to "Closeout Cut 3 - confirm retained priority
  exceptions." Do not implement its stale View Transition, button, banner CTA,
  or theme split-section work. The remaining scope is the current retained
  priority exceptions in footer layout, Herstories active dates, Jetpack
  slideshow, core Navigation, and core Social Links.
- `ds6gcgvh` was renamed to "Closeout Cut 4 - reconcile remaining plugin/theme
  ownership bridges." `ran-forms` and `pns-blocks` already own their main
  domains. The remaining scope is the theme EmailOctopus shortcode bridge, the
  old direct EmailOctopus synced contact pattern, and the Ecwid render cleanup
  bridge/removal gate.
- `867xl40e` was renamed to "Closeout Cut 8 - validate narrowed closeout and
  archive plans." It should validate only current-state work unless live checks
  produce fresh evidence that a previously completed concern regressed.
- Parent `4l44i4cy` was rewritten to name only the remaining current-state
  closeout: retained priority exceptions, plugin/theme bridge reconciliation,
  and final validation/archive discipline.

## Bridge And Priority Closeout - 2026-07-13

Cut 3 retained-priority decisions:

- `styles/components/priority-overrides.css` remains a tiny unlayered bridge
  for WordPress/core generated CSS pressure only. It is not a general escape
  hatch for project-owned block structure.
- Footer flex-basis priority remains in `styles/components/footer-layout.css`
  because saved Core Columns serialize inline widths and WordPress does not
  provide breakpoint-specific footer column controls. Removal gate: rebuild the
  footer as a dedicated responsive footer block/template-part contract.
- Herstories active-date max-width priority remains in
  `styles/page-types/herstories-bios.css` as a CPT pattern bridge for saved
  Core layout width styles. Removal gate: move active-date output into
  `pns-herstories` or a dedicated block.
- Core Navigation drawer/desktop and Core Social Links priority remains as
  core block adapters for theme chrome. Removal gate: Core exposes equivalent
  block supports/controls or the site replaces those Core blocks with
  project-owned blocks.
- Jetpack slideshow pagination hiding remains a vendor bridge. Removal gate:
  Jetpack exposes a per-block pagination setting or the slideshow moves to a
  project-owned media block.
- EmailOctopus and Ecwid vendor adapters are documented as hosted/plugin output
  bridges with removal gates next to brittle selectors.

Cut 4 plugin/theme ownership decisions:

- `pns-blocks` owns split-section layout, editor parity, edge-media alignment,
  image-frame behavior, and generic Ecwid product-grid rendering/fallback
  mechanics. The theme must not recreate those mechanics to solve cascade
  problems.
- The theme may provide PNS-specific fallback product data through the
  `pns_ecwid_product_grid_static_fallback_products` filter because that data is
  site content, not block logic.
- The Ecwid `[]` cleanup remains in `inc/block-filters.php` as a presentation
  cleanup for the third-party `ecwid/store-block`. Removal gate: Ecwid stops
  emitting the marker or the stored template no longer renders that vendor
  block.
- `ran-forms` owns Jetpack contact submissions, EmailOctopus API subscription,
  newsletter opt-in, Turnstile, settings, and health checks.
- The EmailOctopus shortcode bridge remains in the theme only because the
  remaining shortcode lives in theme-owned synced-pattern content. The direct
  `synced-patterns/contact-form.html` fixture is a legacy hosted newsletter
  embed, not equivalent to the `ran-forms/contact-form` contact-flow pattern.
  Removal gate: migrate or retire that hosted embed fixture, then delete the
  shortcode render bridge.

Documentation updated:

- Theme `readme.txt` now names accepted retained bridge categories and the
  sidecar plugin boundary.
- `docs/css/README.md` now codifies bridge comments, unlayered conflict tails,
  and project-owned sidecar plugin ownership.
- `pns-blocks/README.md` now says split-section structure belongs in the plugin.
- `ran-forms/README.md` now distinguishes the modern form flow from legacy
  hosted EmailOctopus embeds.

Validation:

- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone
check` - passed.
- `php app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-block-templates.php`
  - passed for 25 files.
- `php -l
app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/block-filters.php`
  - passed.
- `git diff --check` - passed.
- Root docs/plugin README Prettier check for touched docs - passed.
- `wp theme list --status=active --fields=name,status` - confirmed
  `protestsandsuffragettes-standalone` active.
- `wp plugin list --status=active --fields=name,status` - confirmed
  `pns-blocks`, `pns-herstories`, `ran-forms`, Ecwid, EmailOctopus, Jetpack,
  and the expected active vendor/runtime plugins.
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone
test:visual` - passed: 49 desktop tests and 10 mobile tests.
