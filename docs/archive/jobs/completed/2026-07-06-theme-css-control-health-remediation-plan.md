# Theme CSS And Control Health Remediation Plan

Created: 2026-07-06.

All paths are relative to the project root.

## Purpose

Triage the high, medium, and low findings from the 2026-07-06 adversarial
review of the active `protestsandsuffragettes-standalone` theme.

This document is no longer an 11-phase final-polish implementation plan. It is a
control-health plan with a narrow landing cut and a deferred backlog.

The landing cut should only establish current ownership evidence, run a
scan-only CSS load-path audit, fix or verify the concrete
`white`/button-default issue if Cut 0 proves live impact, and decide whether
Navigation overlay work should be queued after motion work settles. The broader
findings remain useful, but they are architecture/backlog work rather than final
details.

This document must not be read as approval to implement the deferred backlog.
Each deferred item needs a separate task, current evidence, and its own
validation plan before code or DB changes start.

## Related Work

- Current motion/perceived-loading plan:
  `docs/jobs/2026-07-06-motion-perceived-loading-plan.md`
- Current render-filter/template remediation plan:
  `docs/jobs/2026-07-06-render-filter-template-remediation-plan.md`
- Current layout stability plan:
  `docs/jobs/2026-07-06-layout-stability-plan.md`
- Completed baseline rationalization plan:
  `docs/jobs/__completed/2026-06-28-theme-json-css-structure-rationalization-plan.md`
- Completed color-token cleanup plan:
  `docs/jobs/__completed/2026-07-06-theme-json-color-token-cleanup-plan.md`
- Completed rhythm/type-scale cleanup plan:
  `docs/jobs/__completed/2026-07-06-theme-json-rhythm-type-scale-cleanup-plan.md`
- Completed vendor override re-evaluation plan:
  `docs/jobs/__completed/2026-07-06-vendor-override-debt-final-reevaluation-plan.md`

## Honest Evaluation

The high findings are real. The cascade layer declaration is mostly nominal
until imported or authored rules actually join those layers, and the navigation
overlay suppression is too broad for a theme that should leave ordinary core
Navigation behavior alone.

The medium findings are the fragile part because they expose a deeper question:
when is PHP an honest runtime owner, and when is PHP hiding a mismatch between
editor source and frontend output?

My recommendation is not to try to remove all PHP intervention. That would be a
mistake. Some runtime bridges are legitimate in this project:

- resolving DB-backed navigation/template references by slug;
- repairing known core/plugin output gaps;
- rendering data-dependent Herstories behavior;
- adapting third-party output that cannot be shaped through normal block
  supports or account settings;
- applying route/query policy that static block markup cannot express.

The unhealthy pattern is different: PHP should not make a core block or static
pattern look editor-owned while the frontend is actually replaced by unrelated
dynamic behavior. Where that exists, either the editor surface needs to become
honest, or the dynamic behavior needs to move into a real block or clearly
scoped legacy bridge with a removal gate.

## Non-Goals

- Do not redesign the theme.
- Do not collapse the current motion work into this plan.
- Do not delete saved-content compatibility CSS without a current DB and
  filesystem usage scan.
- Do not move Herstories behavior to a plugin unless the owner boundary and
  migration path are explicit.
- Do not remove `!important` rules broadly. Remove or retain them one family at
  a time, after the owner boundary is known.
- Do not edit parent themes or third-party plugins.

## Guardrails

- Reconfirm active theme, active `wp_global_styles`, DB-backed templates,
  template parts, navigation records, and synced patterns before mutating
  controls or serialized tokens.
- Keep every slice independently revertible.
- Prefer source fixes over render-time repair, but keep render-time bridges when
  the behavior is truly dynamic.
- If a block control is unsupported for a PNS-owned surface, either hide the
  control for that surface or make the surface consume the control honestly.
- If CSS overrides a core block default, make the selector narrow enough that
  ordinary author-created blocks still behave like WordPress blocks.
- Run focused visual lanes during implementation and the lean visual gate before
  closeout of any broad CSS behavior change.
- Treat every deferred section as evidence preservation only. Deferred sections
  do not authorize edits unless Cut 0 identifies a live defect and a separate
  task is created.

## Locked Decisions

These decisions are accepted constraints for any future queued work under this
plan. They do not authorize implementation by themselves.

1. Dex timing.
   - Do not create the full Dex task tree while motion work is still underway.
   - After the motion work settles, create the parent task and Cut 0 first.
   - Do not start concurrent edits in `buttons.css`, navigation, CTA, or
     compiled CSS outputs without first reconciling the active motion branch.
   - Treat completed render-filter/Shop Dex work as input evidence, not work to
     repeat.

2. Baseline freshness.
   - Refresh live DB and filesystem state before implementation.
   - Completed plans are historical evidence, not a source of truth for current
     mutation.
   - Cut 0 must classify overlapping current jobs as residual, superseded, or
     dependency-blocked before later phases are queued.

3. Orphan-looking CSS files.
   - Build the import/registration matrix before deleting CSS files.
   - Delete only files proven unused across bundle imports, block-style enqueue,
     editor loading, rendered handles, compiled output, and tests.
   - `styles/blocks/index.css` is an active global-bundle entrypoint, not an
     orphan candidate.

4. Separator ownership.
   - Use a theme token for separator color.
   - Keep separator spacing as a documented fixed exception unless an existing
     approved spacing token preserves the rendered visual exactly.

5. White/black and `foreground` tokens.
   - Run the scan first.
   - Keep `foreground` short-term if serialized content depends on it.
   - Add explicit `white`/`black` palette tokens only if the scan proves the
     author-facing palette remains confusing.
   - Do not add `white`/`black` only to satisfy `core/button`; first decide
     whether inherited core `white` is intentional.
   - Prove no undefined preset variables remain before button work starts.

6. Button ownership.
   - Treat this as a policy for evaluating Target A, not a standalone cleanup
     mandate.
   - `theme.json` should own honest generic `core/button` defaults if Target A
     proves those defaults are currently wrong.
   - Component CSS owns PNS treatment, states, forms, pagination, and
     plugin-like button controls.
   - Do not reopen broad button treatment or component variants from this plan.

7. Broad selectors and rhythm heuristics.
   - Classify before narrowing.
   - Narrow in small batches.
   - Retain compatibility selectors only with comments, tests, and deletion
     gates.

8. Pattern inserter policy.
   - Keep remote/demo patterns blocked.
   - Allow PNS starter patterns and required plugin patterns.
   - Remove Herstory entry navigation from the public inserter if it becomes
     dynamic, scaffold-only, or internal.

9. Render-filter policy.
   - Use completed render-filter remediation as the baseline.
   - Audit only residual retained bridges that still affect CSS/control health.
   - Keep filters for DB bridges, core/plugin repair, conditional data behavior,
     and route/query policy.
   - Remove filters that only hide bad source markup after source or DB cleanup.

10. Navigation overlay controls.
    - Scope overlay/control suppression to navigation contexts that disallow
      core overlay controls only if Target B is separately queued after Cut 0
      and motion settlement.
    - If Target B is queued, implement one shared predicate in PHP and editor JS:
      `pns_standalone_navigation_disallows_core_overlay( $attrs )` and
      `doesNavigationDisallowCoreOverlay(attributes)`.
    - Preserve normal WordPress Navigation controls for generic author-created
      Navigation blocks.

11. Herstory entry navigation owner.
    - Prefer a Herstories plugin dynamic block if the ordering/content model is
      plugin-owned.
    - Use a theme dynamic block only as an interim owner with a migration path.
    - Keep the current marked Group replacement only as a temporary legacy
      bridge.
    - Treat conversion as separately scoped Herstories follow-up work, not part
      of completed Shop/render-filter remediation.

12. Real cascade layers.
    - Use layered imports for bundled CSS where possible.
    - Explicitly account for CSS loaded through `wp_enqueue_block_style()`.
    - Treat real layer adoption as its own migration, not a mixed cleanup pass.
    - After layer adoption, rerun selector-owner and rhythm contracts because
      layer changes can alter which selectors win.

13. `!important` and compatibility aliases.
    - Review priority rules family by family.
    - Retain only real vendor/runtime/core-inline/serialized-layout boundaries.
    - Add comments and removal gates for retained project-owned priority rules
      and compatibility aliases.

14. DB-backed enforcement.
    - Any phase touching serialized controls, tokens, classes, patterns,
      templates, navigation attributes, or saved block markup must include
      affected-record scans, backup/rollback criteria, and post-apply
      verification inside that phase.
    - Do not defer DB safety to final compatibility cleanup when the mutation
      risk occurs earlier.

## Dex Backing

This plan was initially queued with a new parent and Cut 0 only, as required by
the conservative landing policy. After Cut 0 completed, Cut 1 and Target A were
separately queued because Cut 0 classified them as safe verification work.

Since this plan was drafted, the render-filter/Shop remediation parent
`3q6vu5ah` completed: Shop now uses `page-light-surface`, the Shop surface
render filter was removed, retained runtime bridges were classified, and
navigation/Herstory conversion work was split out.

Treat completed render-filter evidence as input rather than work to repeat. The
motion parent `apu24hdi` remains active, so do not start concurrent edits in
motion files until a future task reconciles current work.

Do not pre-create Target B or deferred backlog tasks. Cut 0 remains the
authorization gate that decides whether each later item is residual, superseded,
dependency-blocked, ready, or not worth doing.

Recommended parent task when this plan is queued:

```text
Theme CSS and control-surface health remediation
```

Recommended landing cut:

| Cut | Work                                                                       | Queue status                                                          |
| --- | -------------------------------------------------------------------------- | --------------------------------------------------------------------- |
| 0   | Refresh live DB/filesystem/Dex baseline and classify active owners.        | Queue first.                                                          |
| 1   | Build the CSS import/enqueue matrix, scan-only.                            | Allowed after Cut 0; no deletions.                                    |
| A   | Verify or narrowly fix `white` / `foreground` / button-default behavior.   | Verify after Cut 0; separate fix task if live impact is proven.       |
| B   | Scope Navigation overlay-control suppression with shared PHP/JS predicate. | Candidate only after motion settles and Cut 0 confirms a live defect. |

Deferred backlog:

| Backlog item                                  | Status                                                      |
| --------------------------------------------- | ----------------------------------------------------------- |
| Separator token/spacing cleanup               | Defer unless Cut 0 proves a live defect.                    |
| Broad core-selector hardening                 | Defer; architecture cleanup, not final polish.              |
| Content-rhythm heuristic hardening            | Defer; architecture cleanup, not final polish.              |
| Pattern inserter policy                       | Defer unless Cut 0 finds an active editor break.            |
| Broad render-bridge audit                     | Defer; completed render-filter evidence is the baseline.    |
| Herstory entry-navigation owner               | Move under active Herstories work or a separate owner task. |
| Real cascade layers                           | Defer; architecture migration with broad cascade risk.      |
| Broad `!important` / DB compatibility cleanup | Defer unless a concrete live defect is found.               |

## Landing Cut

### Cut 0 - Refresh Baseline

Recommendation:

- Reconfirm the active theme is `protestsandsuffragettes-standalone`.
- Reconfirm active `wp_global_styles`, `wp_template`, `wp_template_part`,
  `wp_navigation`, and `wp_block` records before implementation.
- Rebuild the audit ledger for:
  - authored `!important` declarations outside `styles/dist`;
  - block CSS registration versus global-bundle imports;
  - raw `#fff`, `#ffffff`, `#000`, and core white/black assumptions;
  - broad `.wp-block-*`, `.entry-content`, `.alignwide`, and content rhythm
    selectors;
  - render filters that replace, normalize, or strip block attributes.
- Reconcile current Dex and same-day plan state before queueing later phases:
  - mark completed render-filter/Shop work as superseding any broad
    reclassification;
  - mark navigation overlay/control work as residual separated work;
  - mark Herstory dynamic-owner conversion as dependency-blocked or separately
    scoped against Herstories plugin/CPT work;
  - mark motion-owned files as active dependencies until `apu24hdi` settles.
- Record duplicate or historical DB rows separately from active theme-owned
  rows. Current verification found multiple historical `wp_global_styles` rows
  and duplicate template slugs, so inventory must include active-owner
  classification, not only row counts.

Alternative:

- Use the 2026-07-06 completed plans as the baseline without refreshing. This
  is rejected for implementation because it is too drift-prone for a DB-backed
  WordPress site.

Acceptance:

- Fresh baseline notes exist in this plan or a linked implementation note.
- Cut 0 performs no DB mutation and no CSS behavior change.
- No phase starts from stale completed-plan assumptions.
- Any DB mutation phase has an export or rollback path.
- Each later phase is explicitly marked residual, superseded, dependency-blocked,
  or ready.

Cut 0 implementation note, 2026-07-07:

- Dex backing was initially created only for the parent and Cut 0:
  - parent `kvnsu0i8`: Theme CSS and control-surface health remediation;
  - child `2oncv0o8`: Theme CSS health Cut 0 - refresh live ownership baseline.
- Cut 0 later authorized the scan-only Cut 1 matrix and Target A verification
  tasks. It did not authorize Target B or any deferred backlog task.
- Cut 0 performed read-only inventory only. It did not mutate DB content, CSS
  source, compiled CSS, theme settings, templates, navigation, or synced
  patterns.
- Active theme state was reconfirmed through WP-CLI:
  - active theme: `protestsandsuffragettes-standalone`;
  - `stylesheet`: `protestsandsuffragettes-standalone`;
  - `template`: `protestsandsuffragettes-standalone`.
- Active plugin context includes the expected standalone-era owners:
  `pns-blocks`, `pns-herstories`, `ecwid-shopping-cart`, `emailoctopus`,
  `jetpack`, `jetpack-boost`, `mcp-adapter`, `wp-fastest-cache`, and supporting
  admin/security/media plugins.
- Current DB-backed owner inventory:
  - active standalone global styles: post `5256`,
    `wp-global-styles-protestsandsuffragettes-standalone`;
  - historical global styles retained but not active-owner rows: post `1030`
    for `protestsandsuffragettes`, post `1031` for `estory`, and post `1024`
    for `carbon`;
  - standalone templates: `5990` single, `6132` 404, `6138`
    archive-herstory, `6186` home, `6219` page-search, and `6221` search;
  - historical child-theme templates remain for duplicate slugs and legacy
    ownership: `1028` page, `1029` home, `3164` 404, and `4689`
    page-no-contact-form;
  - standalone template parts: `5936` header and `5980` footer;
  - navigation records have no `wp_theme` term and remain shared DB-backed
    content: `1035` primary, `1032` footer, and `5259` banner CTA;
  - synced patterns remain shared `wp_block` content without a `wp_theme` term:
    `1493` contact-form, `1494` connect-social, `1504` read-all-about-it, and
    `1509` shop-intro.
- Current Dex overlap classification:
  - completed render-filter/Shop work `3q6vu5ah` supersedes broad
    render-filter reclassification for this plan;
  - motion/perceived-loading `apu24hdi` remains active, so button, navigation,
    CTA, motion-owned CSS, tests, and compiled assets remain
    dependency-blocked for this plan until that work settles;
  - Herstories plugin/CPT migration `h3rs0t00` remains active with `h3rs0t06`
    open, so Herstory entry-navigation ownership stays under Herstories or a
    separate owner task;
  - Cut 1 scan-only import/enqueue matrix is ready to queue after this Cut 0
    note; no deletion or visual behavior change is authorized by that readiness.
- Filesystem audit classification:
  - `styles/blocks/index.css` is an active global-bundle entrypoint imported by
    `styles/frontend.css`, not an orphan;
  - frontend and editor styles prefer `styles/dist/*.min.css` when present;
  - block-scoped CSS is also registered through `wp_enqueue_block_style()`, so
    Cut 1 must compare imports, registered block handles, compiled output, and
    rendered handles before proposing any later deletion task;
  - `core-social-links-frontend.css` is only an investigation candidate for Cut
    1, not deletion-ready from Cut 0.
- Priority-rule inventory remains deferred:
  - current source CSS contains 69 authored `!important` declarations outside
    `styles/dist`;
  - most are in vendor/runtime or fixed-surface areas: Ecwid, EmailOctopus,
    cross-site banner CTA, footer layout, core Navigation, split section,
    Herstories bios, core Social Links, Jetpack slideshow, and buttons;
  - no broad `!important` cleanup is authorized by Cut 0.
- White/black and button-default classification:
  - `theme.json` still uses raw `#ffffff` in the palette and
    `var(--wp--preset--color--white)` for `core/button` text;
  - source templates, parts, patterns, and synced patterns still serialize
    `white` and `black` color slugs;
  - Target A remains a verification candidate only. No palette rename, slug
    retirement, or button default mutation is authorized until a narrow Target A
    task proves live impact and includes DB scan/rollback criteria.
- Deferred backlog classification:
  - broad core-selector hardening, content-rhythm heuristic narrowing, real
    cascade-layer adoption, broad render-bridge audit, pattern inserter policy,
    Herstory owner conversion, and broad priority/DB compatibility cleanup all
    remain deferred architecture or owner-boundary work;
  - Target B remains residual navigation work, not part of Cut 0 or Cut 1, and
    should only be queued after motion settles and a live broad-suppression
    defect is reconfirmed.

### Cut 1 - Scan-Only Import Matrix

Recommendation:

- Keep formatting as a standing gate. As of the 2026-07-06 reviewer response,
  `pnpm format:check` passes and there are no current formatting nits to plan
  around.
- Create a block CSS import/registration matrix covering `inc/assets.php`,
  `styles/frontend.css`, `styles/editor.css`, and `styles/blocks/`.
- Separate files into:
  - global bundle entrypoints;
  - block-style enqueued files;
  - editor-only files;
  - intentionally retained compatibility files;
  - dead files.
- Treat `styles/blocks/index.css` as an active global-bundle entrypoint imported
  by `styles/frontend.css`, not as a deletion candidate.
- Identify deletion candidates only. Cut 1 must not delete CSS files.
- Require both compiled-bundle evidence and rendered-handle evidence before any
  later deletion task is proposed.
- Do not delete files in Cut 1. If a file looks deletion-ready, record the proof
  and create a separate deletion task after the matrix is reviewed.

Alternative:

- Keep orphan-looking files for one release-watch cycle, but add a clear
  "not loaded" note and a deletion gate. This is a fallback only if the matrix
  cannot prove the file is unused across all load paths.

Acceptance:

- Formatting checks pass or known failures are separately documented.
- Every block CSS file is classified as globally bundled, block-style enqueued,
  editor-only, intentionally unused, or deletion-ready.
- Deletion-ready files have compiled-output and rendered-enqueue proof.
- Deletion-ready files are recorded only; no deletion happens in Cut 1.
- No visual behavior changes are bundled into this nit pass.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone format:check
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:smoke
```

Cut 1 implementation note, 2026-07-07:

- Cut 1 remained scan-only. No CSS source files, compiled CSS files, PHP enqueue
  code, templates, synced patterns, or DB records were changed for this matrix.
- Frontend load path:
  - `inc/assets.php` enqueues `pns-standalone-style` from
    `styles/dist/frontend.min.css` when present, falling back to
    `styles/frontend.css`;
  - `styles/frontend.css` imports `styles/blocks/index.css`;
  - `styles/blocks/index.css` imports `core-navigation.css`, `core-cover.css`,
    and `jetpack-contact-form.css`.
- Editor load path:
  - `inc/assets.php` registers editor styles through `add_editor_style()`, using
    `styles/dist/editor.min.css` when present, falling back to
    `styles/editor.css`;
  - `styles/editor.css` directly imports `core-navigation.css`,
    `core-cover.css`, and `jetpack-contact-form.css`.
- Block-style registration path:
  - `inc/assets.php` registers block-scoped styles with
    `wp_enqueue_block_style()` for `core/columns`, `core/cover`, `core/group`,
    `core/image`, `core/navigation`, `core/quote`, `core/separator`,
    `core/social-links`, and `jetpack/slideshow`;
  - generated handles use `pns-standalone-` plus the block name with `/`
    replaced by `-`, for example `pns-standalone-core-navigation`.
- Block CSS matrix:

| File                                           | Classification                                                  | Cut 1 action                                      |
| ---------------------------------------------- | --------------------------------------------------------------- | ------------------------------------------------- |
| `styles/blocks/index.css`                      | Active global-bundle entrypoint.                                | Keep.                                             |
| `styles/blocks/core-navigation.css`            | Frontend bundled, editor imported, block-style registered.      | Keep.                                             |
| `styles/blocks/core-cover.css`                 | Frontend bundled, editor imported, block-style registered.      | Keep.                                             |
| `styles/blocks/jetpack-contact-form.css`       | Frontend bundled and editor imported.                           | Keep.                                             |
| `styles/blocks/core-columns.css`               | Block-style registered for `core/columns`.                      | Keep.                                             |
| `styles/blocks/core-group.css`                 | Block-style registered for `core/group`.                        | Keep.                                             |
| `styles/blocks/core-image.css`                 | Block-style registered for `core/image`.                        | Keep.                                             |
| `styles/blocks/core-quote.css`                 | Block-style registered for `core/quote`.                        | Keep.                                             |
| `styles/blocks/core-separator.css`             | Block-style registered for `core/separator`.                    | Keep.                                             |
| `styles/blocks/core-social-links.css`          | Block-style registered for `core/social-links`.                 | Keep.                                             |
| `styles/blocks/jetpack-slideshow.css`          | Block-style registered for `jetpack/slideshow`.                 | Keep.                                             |
| `styles/blocks/core-social-links-frontend.css` | Currently unreferenced by imports and block-style registration. | Investigation candidate only; not deletion-ready. |

- No file is deletion-ready from Cut 1. The only unreferenced block CSS file is
  `core-social-links-frontend.css`, and it needs rendered-handle/source-history
  review in a separate deletion-candidate task before any removal is proposed.
- Cut 1 does not promote Target A, Target B, or any deferred backlog item.
- Cut 1 validation passed:
  - `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone format:check`;
  - `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css`;
  - `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css`;
  - `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:smoke`
    passed 4 desktop and 4 mobile smoke tests.

### Conditional Target A - White And Button Defaults

Recommendation:

- Treat this as a targeted verification/fix, not broad palette cleanup.
- Run a scan-only pass for white/black assumptions before changing palette
  names. Keep `foreground` short-term if serialized content depends on it.
- Treat inherited core `white` as a real serialized dependency until proven
  otherwise; current source content already serializes `backgroundColor` with
  the value `white` in places.
- Do not add theme-owned `white`/`black` tokens only to satisfy `core/button`.
- Prove `theme.json` and authored CSS contain no undefined preset variables that
  affect default button rendering.
- If the inherited core `white` dependency is intentional and valid, document it
  and stop.
- If output depends on a missing or misleading token, create or execute a
  separate narrow fix task only if Cut 0 plus Target A verification proves live
  impact.

Alternative:

- Keep `foreground` as the public white slug for compatibility. This is lower
  risk than renaming serialized content, but it should be documented because it
  reads oddly to future maintainers.
- Defer any broader palette rename or slug migration unless Cut 0 proves a live
  serialized-content defect.

Acceptance:

- White/black slug usage is either intentionally retained or queued for a DB
  migration with rollback.
- Any retirement or renaming of serialized `white` or `foreground` usage has an
  affected-record scan, backup, rollback condition, and post-apply scan.
- `core/button` does not depend on undefined theme-owned preset variables.
- No broad palette cleanup is started from this target unless Cut 0 produces a
  specific live defect and a separate task.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:fast
```

Target A verification note, 2026-07-07:

- Target A was verified read-only. No palette, `theme.json`, CSS, DB, template,
  pattern, test, or compiled asset change was made for this target.
- The apparent contradiction is real in source but harmless at runtime:
  - the standalone theme defines `foreground` as `#ffffff`;
  - `core/button` uses `foreground` for default background and `white` for
    default text;
  - WordPress core default palette remains enabled and provides
    `--wp--preset--color--white` and `--wp--preset--color--black`;
  - generated global styles currently include `--wp--preset--color--black:
#000000`, `--wp--preset--color--white: #ffffff`, and
    `--wp--preset--color--foreground: #ffffff`.
- Current serialized/source dependencies make broad cleanup risky:
  - `white` is used by source templates, parts, and patterns for white
    backgrounds;
  - `black` is used by synced patterns for readable text on light surfaces;
  - `foreground` is the theme-owned semantic white compatibility slug used
    across footer, hero, synced sections, buttons, and surface CSS.
- No narrow fix task is justified now. `white` and `black` are intentional
  inherited WordPress default-palette dependencies under current settings, while
  `foreground` remains the theme-owned white compatibility slug.
- Reopen Target A only if the theme disables WordPress `defaultPalette`, removes
  core default color presets, or a future DB/live render scan proves missing
  `--wp--preset--color--white` output.

### Potential Target A Follow-Up - Button Ownership Honesty

Recommendation:

- Do not create this follow-up by default. It is only valid if Conditional
  Target A proves a real generic-button contradiction that cannot be resolved by
  documenting the current token dependency.
- In a separately queued follow-up, make `theme.json` expose honest generic
  `core/button` defaults only for the proven contradiction.
- In that follow-up, fix `core/button` so generic defaults are readable using
  existing public/semantic tokens. Do not depend on an undefined theme-owned
  `white` preset.
- Keep this follow-up limited to the concrete default-button issue identified in
  Conditional Target A.
- Keep PNS-specific treatment in component CSS:
  - `.wp-block-button__link` visual treatment;
  - `.btn` compatibility;
  - form submit and plugin-like buttons;
  - states, pseudo-elements, icon/arrow treatment, and surface variants.
- Record contradictions where `theme.json` claims one default and
  `styles/components/buttons.css` or `styles/components/section-theme.css`
  silently rewrites it for ordinary buttons. Fix only the proven default-button
  contradiction; split broader component cleanup into a later task.
- Do not reopen broad button treatment, hover animation, pagination, or
  component-variant cleanup while motion work is active.

Alternative:

- Promote only native core Button defaults into a dedicated block stylesheet and
  keep mixed `.btn`, forms, pagination, and plugin-like controls in the
  component file. This is a fallback if the component file becomes too broad
  after the honest split.

Acceptance:

- A newly inserted core Button renders with defaults that match the narrow
  visible frontend contract proven by Conditional Target A.
- Conditional Target A has already proven that button defaults do not depend on
  undefined preset variables.
- Block-level text/background choices still override theme defaults when the
  author intentionally picks them.
- PNS section variants remain scoped to the section surface, not all buttons.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:fast
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
```

## Deferred Backlog Details

These sections preserve the architectural findings for later work. They should
not be implemented as part of the landing cut unless Cut 0 proves a specific
live defect and a separate task is created.

Any imperative language below is a requirement for a future separately queued
task, not approval to start work from this plan.

### Deferred - Broad Core-Block Selector Classification

Future task notes:

- Broad selector hardening remains deferred. This is architecture cleanup, not
  polish.
- A future separately scoped task should classify broad selectors into one of
  these owners:
  - real core block default;
  - page-type rhythm;
  - component/pattern surface;
  - saved-content compatibility;
  - vendor/control exclusion.
- Candidate files for a future separately scoped task:
  - `styles/base/elements.css`;
  - `styles/layout/index.css`;
  - `styles/page-types/content-rhythm.css`;
  - `styles/components/section-theme.css`;
  - `styles/components/light-surface.css`;
  - `styles/blocks/core-navigation.css`;
  - `styles/blocks/core-cover.css`.
- Future replacement work should use explicit pattern classes, block styles, or
  surface classes when the intended owner is PNS-specific.
- If a future phase touches saved classes or serialized block-support output,
  run an affected-record scan before editing and a post-apply scan after
  migration.
- Treat this deferred item's computed-style contracts as provisional until real
  layer adoption reruns them.

Alternative:

- Retain broad selectors as compatibility rules, but require a short comment and
  a computed-style test proving what they are protecting. This is allowed only
  as named compatibility debt with a deletion gate.

Future acceptance if separately queued:

- Ordinary editor-created core blocks are not accidentally shaped as PNS
  components.
- Saved-content compatibility rules have comments and deletion gates.
- `.alignwide`, cover, navigation, paragraph/list rhythm, and surface rules have
  clear owners.
- Affected saved-content records are backed up or explicitly proven untouched
  before serialized class/control changes.

Future validation if separately queued:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
```

### Deferred - Content Rhythm Heuristic Hardening

Future task notes:

- Broad rhythm heuristic hardening remains deferred. This is architecture
  cleanup, not polish.
- Keep `styles/page-types/content-rhythm.css` as the central rhythm owner for
  now.
- A future separately scoped task should reduce heuristic breadth only after
  proving exact replacements for current exclusions.
- Treat Navigation, Social Links, Query Pagination, buttons, Jetpack, Search,
  EmailOctopus, and Ecwid exclusions as intentional until fresh tests prove
  otherwise.
- A future task should add explicit tests for the surfaces most likely to
  regress before deleting or broadening exclusions.
- Treat this deferred item's computed-style contracts as provisional until real
  layer adoption reruns them.

Alternative:

- Accept the current heuristic layer as a compatibility layer until the broader
  content-shaping audit completes. This is not elegant, but it is safer than
  removing exclusions from live saved content blindly.

Future acceptance if separately queued:

- The rhythm layer no longer depends on unexplained generated-class exceptions.
- Any remaining heuristic selector has a documented reason and test coverage.
- Editor canvas and frontend rhythm remain aligned for representative pages.
- Any saved-content rhythm migration has affected-record scans, rollback
  criteria, and post-apply verification in this phase.

Future validation if separately queued:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:shop
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:emailoctopus
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
```

### Deferred - Pattern Inserter Policy

Future task notes:

- Defer pattern inserter policy unless Cut 0 finds an active editor break.
- Treat pattern inserter control as editor product policy, not as rendering
  architecture.
- Keep remote/demo pattern suppression as the accepted editorial policy.
- A future separately scoped task should verify the allowlist in
  `inc/patterns.php` admits:
  - PNS starter patterns intended for editors;
  - explicitly allowed plugin patterns such as required form patterns;
  - synced patterns under the proper WordPress "My Patterns" surface.
- If `pns/entry-herstory-navigation` becomes template/scaffold-only, removal
  from the public inserter is only a candidate action for that future task.
- If pattern visibility changes affect existing synced patterns or saved
  content, scan affected `wp_block`, template, template-part, and current content
  records before changing inserter policy.

Alternative:

- Leave the current allowlist alone and document its intent. This is lower risk
  but is rejected unless implementation discovers an editor workflow that
  depends on the current broader allowlist.

Future acceptance if separately queued:

- The inserter exposes only patterns editors should reasonably start from.
- Required plugin patterns are not accidentally blocked.
- No remote/demo pattern library leaks back into the editing workflow.
- Affected DB-backed pattern records are either proven untouched or covered by
  backup/post-apply verification.

Future validation if separately queued:

```bash
php app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-block-templates.php
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
```

### Deferred - Retained Render-Bridge Follow-Up Audit

Future task notes:

- Broad render-bridge audit remains deferred. Completed render-filter
  remediation is the baseline.
- Use the completed render-filter remediation as the baseline.
- Do not reclassify or supersede completed Shop/template work.
- A future residual bridge task should audit only retained bridges whose
  ownership still affects CSS/control health.
- Missing why-comments, tests, or follow-ups should be added only under that
  separately queued residual bridge task.

Likely files:

- `inc/block-filters.php`
- `inc/navigation.php`
- `inc/search.php`
- `inc/template-tags.php`

Alternative:

- Convert every render bridge to a block immediately. This is rejected because
  some behavior is properly runtime-owned, and a blanket conversion would create
  more churn than clarity.

Future acceptance if separately queued:

- No retained render filter exists only because current template or saved
  content is wrong.
- Every retained residual filter has a named owner reason or is split to a
  follow-up.
- Tests assert ownership behavior, not incidental class strings.

Future validation if separately queued:

```bash
php app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-block-templates.php
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
```

Use `check:editor-content` only with explicit exported content fixtures, for
example:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check:editor-content -- path/to/exported-post-content.html
```

Do not list `check:editor-content` as a no-argument gate; the script requires
`<post-content.html>...` paths.

## Conditional Navigation Task

### Conditional Target B - Navigation Overlay And Control Scope

Recommendation:

- This is not part of Cut 0 or Cut 1. Do not start it until motion has settled,
  Cut 0 confirms the broad suppression remains a live defect, and a dedicated
  task is queued.
- Continue the navigation work split out by render-filter classification. Do not
  reopen completed Shop template remediation here.
- Stop treating all core Navigation blocks as PNS drawer/navigation surfaces.
- Keep PHP fallback guards while the editor controls are hardened, but scope
  suppression to navigation contexts that disallow core overlay controls.
- Define one shared predicate in PHP and JS:
  - PHP: `pns_standalone_navigation_disallows_core_overlay( $attrs )`;
  - JS: `doesNavigationDisallowCoreOverlay(attributes)`.
- The predicate should cover:
  - primary navigation by `pnsRefSlug === pns-primary-navigation` or resolved
    primary `ref`;
  - cross-site banner CTA navigation by `className` containing
    `pns-cross-site-banner-cta` or
    `pnsRefSlug === pns-banner-cta-navigation`;
  - any explicitly named future class or slug that uses the custom drawer or
    inline CTA contract.
- The predicate must not automatically include every `pnsRefSlug` or footer
  navigation record.
- Leave generic author-created Navigation blocks with native WordPress overlay
  controls unless they enter an explicitly unsupported PNS context.
- Add editor tests proving unsupported overlay/icon attributes cannot be saved
  through normal editing for disallowed contexts, and generic Navigation overlay
  attrs survive.

Likely files:

- `inc/block-filters.php`
- `inc/navigation.php`
- `scripts/editor-blocks.js`
- `tests/editor/editor.spec.ts`

Alternative:

- Fully embrace core Navigation overlay behavior and retire the custom mobile
  drawer. This is cleaner long-term but much larger, and it should only happen
  if mobile drawer/submenu behavior is being redesigned.
- Keep global suppression and document it. This is rejected because it makes
  ordinary core Navigation controls misleading.

Acceptance:

- Overlay panel/control is not visible for navigation contexts that disallow
  core overlay controls.
- Generic Navigation blocks retain normal WordPress controls.
- Saved primary and CTA nav blocks do not retain ignored overlay/icon attrs.
- Generic Navigation blocks retain overlay/icon attrs when authors set them.
- CTA remains inline across breakpoints.
- Primary mobile drawer still opens, closes, traps focus as expected, and
  preserves desktop submenu behavior.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
```

## Deferred Architecture Work

### Deferred - Herstory Entry Navigation Owner Decision

Future task notes:

- This is decision backlog only unless active Herstories work or a separate
  owner task explicitly adopts it.
- Treat Herstory entry-navigation conversion as separated follow-up work, not
  part of completed Shop/render-filter remediation.
- A future owner task should decide whether the honest dynamic owner belongs in
  the Herstories plugin, an interim theme dynamic block, or a named temporary
  legacy bridge.
- Conversion should proceed only if the Herstories CTA/landing-page direction or
  a separate owner task explicitly scopes it.
- Prefer a Herstories plugin dynamic block if the ordering API and content model
  are plugin-owned.
- Keep the current marked Group replacement only as a temporary legacy bridge
  while saved content migrates.

Likely files:

- `inc/block-filters.php`
- `inc/herstories.php`
- `patterns/entry-herstory-navigation.php`
- Herstories plugin dynamic-block files, if plugin ownership is selected.

Alternative:

- Keep the current marked Group render callback with clearer comments and
  stronger tests. This is acceptable only as a temporary legacy bridge and must
  remain named debt.

Future acceptance if separately queued:

- The owner decision is recorded against current Herstories plugin/CPT state.
- Conversion is either separately scoped or explicitly deferred.
- Any implementation path includes affected-record scans, backup/rollback
  criteria, and post-apply verification for existing saved Herstory content.
- If conversion proceeds, Herstory singles render correct Previous, Back, and
  Next links from editorial order, first/last entries omit only the missing
  side, and editor preview no longer suggests static core Post Navigation is the
  source of frontend output.

Future validation if separately queued:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
```

### Deferred - Real Cascade Layers

Future task notes:

- Defer real cascade layers. This is a broad architecture migration, not final
  polish.
- A future separately scoped task should treat real cascade layers as a
  dedicated migration, not a formatting pass.
- Future layer work can use layered imports for bundled CSS where possible:

```css
@import "shared/settings.css" layer(settings);
```

If an imported file cannot safely participate through a layered import, wrap
that file in an explicit `@layer` block and document why.

- Future layer work should start with one low-risk category such as `base` or
  `utilities`, then compare compiled output and computed styles before moving
  more categories.
- Future layer work must account for CSS loaded through
  `wp_enqueue_block_style()`, not just files bundled through
  `styles/frontend.css`.
- After layer adoption, rerun selector-owner contracts and content-rhythm
  contracts because moving rules into real layers can change which narrowed
  selectors win.

Alternative:

- Keep `styles/shared/layers.css` as documentation only for the landing cut.
  This is acceptable while real cascade-layer migration is deferred, but a
  future layer-migration task must not claim layer closeout until imported and
  block-style CSS actually participate in the declared model.

Future acceptance if separately queued:

- Imported and block-style CSS participates in the declared layer model or is
  explicitly documented as outside it.
- Unlayered CSS is not accidentally relying on the special priority where
  unlayered rules outrank layered rules.
- The compiled CSS remains understandable enough to debug.
- Selector-owner and content-rhythm computed-style contracts still pass after
  layer adoption, or failures are classified and fed back into those deferred
  work items.

Future validation if separately queued:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone analyze:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:fast
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual
```

### Deferred - Remaining Priority And DB Compatibility Debt

Future task notes:

- Broad priority and compatibility cleanup remains deferred unless Cut 0 finds a
  concrete live defect.
- A future separately scoped task should re-evaluate remaining `!important`
  rules only after the owner boundary for the affected family is fixed.
- Keep priority rules when they cross a real boundary:
  - vendor runtime CSS;
  - WordPress inline style conflicts;
  - serialized layout conflicts;
  - documented core Navigation/Social Links priority exceptions.
- If a future migration is separately approved, require a local comment naming
  the boundary for every retained project-owned priority rule.
- If a future migration retires palette slugs, spacing presets, block classes,
  or serialized values, treat it as a DB-backed migration with backup, dry run,
  apply, and post-apply scan.

Alternative:

- Keep compatibility aliases until a later migration window, but each alias
  needs a removal trigger and owner. This is allowed only as a temporary
  migration bridge.

Future acceptance if separately queued:

- Remaining priority rules are current, classified, and tested.
- Compatibility aliases are either gone or attached to a removal gate.
- No live DB content references retired tokens or classes unless the bridge is
  intentionally retained.

Future validation if separately queued:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual
```

DB-backed migrations also require:

```bash
wp theme list --status=active
wp option get stylesheet
wp option get template
wp post list --post_type=wp_global_styles,wp_template,wp_template_part,wp_block,wp_navigation --fields=ID,post_type,post_name,post_status
```

This command list is the final compatibility gate only. Earlier phases with
serialized risk must include their own affected-record scans, backups, rollback
criteria, and post-apply verification at the point where the risk is introduced.

## Landing Closeout Criteria

- Cut 0 records current active owners for DB-backed templates, template parts,
  navigation, global styles, synced patterns, and overlapping Dex work.
- The CSS import/enqueue matrix is complete and scan-only; no CSS file is
  deleted without compiled-bundle and rendered-handle evidence.
- Completed render-filter/Shop work is not reopened; residual navigation and
  Herstory work is classified against current Dex state.
- The `white`/`foreground`/button-default issue is either proven harmless and
  documented, or split to a narrow fix task with matching validation.
- Navigation overlay-control work is classified and, if still needed, queued
  separately after motion settles.
- Deferred backlog items are explicitly not part of this landing cut:
  broad selector hardening, content-rhythm architecture, pattern inserter
  policy, broad render-bridge audit, Herstory owner conversion, real cascade
  layers, and broad priority/DB compatibility cleanup.
- Any item not explicitly promoted by Cut 0 remains parked as backlog evidence,
  not implementation approval.

Landing closeout note, 2026-07-07:

- Landing implementation is complete for the conservative scope authorized by
  this plan.
- Completed Dex tasks:
  - `kvnsu0i8`: Theme CSS and control-surface health remediation;
  - `2oncv0o8`: Theme CSS health Cut 0 - refresh live ownership baseline;
  - `3b03cg40`: Theme CSS health Cut 1 - scan-only import/enqueue matrix;
  - `kzuz62j5`: Theme CSS health Target A - verify white foreground button
    defaults.
- Cut 0 refreshed active theme, DB-backed owner rows, current Dex overlap, and
  filesystem evidence without DB mutation or CSS behavior changes.
- Cut 1 completed the import/enqueue matrix as scan-only. No CSS file was
  deleted; `core-social-links-frontend.css` is only a future investigation
  candidate.
- Target A was proven harmless under current runtime output. WordPress currently
  emits `black`, `white`, and `foreground` preset variables, and no palette,
  button, CSS, or DB fix task was created.
- Target B was not queued because motion/perceived-loading work remains active.
  It remains residual navigation work that may be reconsidered only after motion
  settles and the broad overlay-control suppression is reconfirmed as a live
  defect.
- All deferred backlog items remain parked as evidence, not implementation
  approval.
