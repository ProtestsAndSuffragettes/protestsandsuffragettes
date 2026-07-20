# Production Readiness and Handoff Compendium Plan

Created: 2026-07-16

## Goal

Make the standalone `protestsandsuffragettes` theme safe to hand to another
developer and straightforward to maintain as a client-owned product. The
theme repository, not the Local-site wrapper repository, will be the canonical
home for durable theme documentation, release guidance, and component
reference material.

This plan turns the current working implementation into a reproducible release
unit: its supported runtime is truthful, its live WordPress state is accounted
for, its quality gates have a clear status, and a developer can understand the
theme without reconstructing its design from historical remediation plans.

## Decisions locked for this plan

1. **Canonical documentation lives in this theme repository.** The durable
   handbook belongs under `docs/` here, with a short developer `README.md` at
   the repository root. `readme.txt` remains the WordPress metadata/readme
   surface, not the full handoff manual.
2. **RAN Booster is the selected deployment basis.** Release Please
   manages version and changelog automation; it does not deploy. The
   GitHub-only, PAT-based plugin is presently a beta and must pass its staging
   acceptance gate before it is treated as production-ready. Its sanitised
   handbook runbook must not store repository credentials, PATs, webhook
   secrets, or environment-specific private access data.
3. **The Local wrapper is an integration environment, not the theme manual.**
   Keep Local-only runtime configuration, database backup exports, credentials,
   and historical execution logs outside this repository.
4. **Historical plans remain evidence.** Do not bulk-rewrite completed plans or
   backup directories just to replace the former `-standalone` path. The
   handbook will explain the rename/history boundary and link to only the
   still-useful records.
5. **Template formalisation follows proof, not assumption.** A saved WordPress
   template, part, navigation, or synced pattern is not silently replaced by a
   file. Reproduce representative pages from blank drafts before treating the
   current template and component set as stable.
6. **Compatibility claims are evidence-based.** Do not change the WordPress
   `Tested up to` value merely because the Local site boots. Passing validation
   on the declared target is required first.
7. **Client-host compatibility is the support boundary.** The PHP minimum is
   Local PHP `8.2.29`, the version serving the current WordPress development
   site. The higher production-host PHP version is a required tested target.
   This is a client-maintained support policy, not a promise to maintain a
   broad public PHP matrix.

## Current evidence and readiness blockers

The initial audit on 2026-07-16 established the following facts:

- The active local site runs WordPress `7.0.1` and theme version `0.2.0`.
- `style.css` and `readme.txt` still claim WordPress `6.0`, tested through
  `6.8`, and PHP `7.4`.
- `theme.json` schema version 3 and related settings require WordPress `6.6`
  or later. Runtime use of `str_contains()` and `str_starts_with()` requires
  PHP `8.0` or later. The current advertised minima are false.
- Portable static gates pass: formatting, CSS asset audit, Stylelint, PHP
  syntax, and CSS compilation.
- The desktop/mobile visual smoke suite is green. Eleven stale baselines were
  deliberately refreshed against the current Local site and individually
  rechecked. Two stale test contracts were corrected: the old 1280px
  wide-layout cap is now read from the rendered CSS cap, and the
  database-owned navigation label check is case-insensitive.
- `check:template-ownership` reports 21 actionable records: diverged overrides
  for Home, Herstory Archive, and Search; matching shadows for Page Search,
  Header, and Footer; and stale/unclassified search/header records. The
  non-mutating release-handoff probe passes.
- This theme is a clean, separate Git repository. The Local wrapper's rename
  and installation state must be documented as an integration concern, but it
  must not become the source of theme documentation.
- RAN Booster is active locally at `0.1.0-dev`, but no package, PAT, or
  webhook is configured. Its installed source is currently unpinned. Treat it
  as a beta deployment candidate until a pinned release passes the documented
  staging acceptance, manual-update, signed-webhook, and recovery checks.
- `readme.txt` now points to the theme-local CSS architecture guide and the
  Release Please changelog path exists. The WordPress/PHP metadata remains
  deliberately unmodified until the compatibility matrix is complete.

## Implementation progress — 2026-07-17

- **Phase 0:** the root README, handbook index, and release/runbook are in the
  theme repository. The runbook now records RAN Booster's beta
  acceptance and PAT/webhook security model. Exact staging and production
  settings remain an environment-operator audit.
- **Phase 1:** three empty disposable drafts exist for the ArtWorks, recent
  full-width news, and Mary Barbour proofs. They preserve their published
  sources and are ready for manual construction through an authenticated
  WordPress editor session; no template, navigation, or synced pattern was
  changed.
- **Phase 3:** CSS asset audit, Stylelint, PHP syntax, targeted documentation
  formatting, and the desktop/mobile visual smoke suite pass. The repository's
  full `format:check` remains blocked by five pre-existing files:
  `scripts/editor-blocks.js`, `styles/blocks/jetpack-slideshow.css`,
  `styles/components/priority-overrides.css`, `styles/editor-canvas.css`, and
  `tests/visual/site-frame-next.spec.ts`.
- **Phase 4:** the `CHANGELOG.md` baseline is present. Compatibility headers
  remain pending the exact production WordPress/PHP evidence and the full
  release gate.
- **Phase 5:** the handbook now includes CSS, ownership/integration,
  release, rebuild-lab, and provisional template/component inventory pages.
  The authoring/admin and compatibility-policy pages follow the editor proof
  and host-matrix evidence.

## Non-goals

- No general visual redesign or CSS-debt removal.
- No blind deletion, reseeding, or file-to-database synchronization.
- No new template, pattern, or plugin feature merely to make the current
  catalogue look complete before page-construction evidence supports it.
- No automatic migration of the wrapper repository's documents, backups, or
  Dex state.
- No promise of broad public-theme or WordPress.org compatibility. This is a
  maintained client theme with an explicit supported environment.
- No changes to project-plugin ownership. `pns-blocks`, `pns-herstories`,
  `pns-search-routing`, and the `ran-*` plugins remain separately documented
  integration boundaries rather than theme implementation detail.

## Target handbook structure

```text
README.md
docs/
  README.md
  architecture/
    overview-and-ownership.md
    css.md
  reference/
    templates-parts-patterns.md
    component-and-functionality-catalog.md
    integrations-and-boundaries.md
  operations/
    authoring-and-admin.md
    development-and-release.md
    compatibility-policy.md
  plans/
    2026-07-16-production-readiness-and-handoff-compendium-plan.md
```

Each handbook page must name its audience, authoritative source, update
trigger, and related validation command. Link rather than duplicate long
command lists or third-party plugin documentation.

## Phase 0: Establish release and repository boundaries

### Objective

Make it possible to identify exactly what commit, repository, and WordPress
state a developer needs to reproduce the client theme.

### Steps

1. Record the active theme repository URL, branch/commit, version/tag, and
   expected installation path in the new root `README.md`.
2. Audit RAN Booster in the staging and production environments. First
   pin the plugin source/release; then record its package mapping, repository,
   branch/ref, subdirectory, secret source, Push-to-Deploy state, update,
   recovery path, and authorised operators. Never commit credentials, PATs,
   webhook secrets, or raw configuration exports.
3. Decide the deployment lane. Recommended default: manually update staging
   from an approved `main` revision while the beta is being accepted; enable
   signed Push-to-Deploy only after that path passes staging. Production uses
   a reviewed approved ref and the complete pre-merge checklist.
4. Add a concise rename/history note: the former
   `protestsandsuffragettes-standalone` path is historical; this repository is
   the current standalone theme.
5. Identify which wrapper documents are durable theme knowledge, which are
   Local-only evidence, and which are historical plans. Create links or short
   migration notes; do not bulk move files.

### Acceptance criteria

- A fresh developer can locate and clone the canonical theme repository from
  the root README.
- The GitHub Booster update and recovery path is explicit without exposing
  secrets.
- Staging and production deployment rules are explicit.
- No documentation claims that the wrapper repository is the canonical theme
  source.

## Phase 1: Prove page construction from current blocks

### Objective

Test whether the current theme, patterns, project blocks, and selected
templates can reproduce real editorial pages from scratch before freezing a
template catalogue or proposing new templates.

### Rules

- Create new disposable drafts named `PNS Rebuild Proof — <source> — <date>`
  with unique non-public slugs.
- Build through the block inserter using current theme patterns, project/plugin
  blocks, and ordinary core blocks. Do not clone or paste a whole saved page,
  use the code editor to recreate hidden structure, or modify a published
  source page.
- Do not touch `wp_template`, template parts, navigation, synced patterns, or
  existing `**TEMPLATE*` drafts during this proof.
- For each draft, capture the selected template, component/dependency map,
  block outline, desktop/mobile previews, and every gap or manual workaround.
  The published source's status and content hash must remain unchanged.

### Proof sequence

1. Rebuild **ArtWorks** as the representative ordinary wide content page. It
   exercises split sections, Jetpack slideshows, quotes, buttons, and synced
   content without Herstory-specific scaffolding.
2. Rebuild a recent editorial news post using `single-full-width-news`. This
   tests the news hero/enhanced cover, metadata, featured image, and entry
   navigation contract without mistaking editorial content for a new template
   need.
3. Rebuild **Mary Barbour** as the Herstory stress test. It exercises the
   Herstory shell and the richest collection of reusable sections, exposing
   genuine component or route-level gaps.

Defer the homepage, shop, forms, search/archive, and 404 work. Those surfaces
depend on site options, external services, or query/runtime behaviour and do
not initially prove that a new editorial template is needed.

### Decisions from the proof

- Add or change a pattern only when the same compositional gap appears in at
  least two proofs.
- Propose a new template only when the missing behaviour is route/type-level
  and cannot be expressed with an existing selected template plus blocks.
- Record one-off composition choices as authoring guidance, not new reusable
  API.
- Do not automatically promote a proof draft into a published page, a file
  template, or a synced pattern.

### Acceptance criteria

- Each proof uses the selected existing template and normal editor surfaces
  without bespoke CSS or an undocumented class stack.
- Desktop/mobile hierarchy, ordering, media crop, and CTA behaviour are
  materially equivalent to the source, or each difference is classified.
- The result is a short evidence table: reusable current pieces, gaps, and a
  prioritised follow-up list. It is not a premature final template catalogue.

## Phase 2: Reconcile live WordPress ownership safely

### Objective

After Phase 1 establishes which structures are stable, restore a reviewed,
documented relationship between versioned templates and the WordPress records
that currently render live routes.

### Steps

1. Keep this phase capture-only until the Phase 1 proof determines that a
   template/component structure is stable. Then re-run the read-only ownership
   audit against the intended Local database
   immediately before any change. Capture only hashes, IDs, slugs, and a
   reviewed export/backup path for records that will be changed.
2. Classify each of the 21 reported records as one of:
    - approved administrator/editor data;
    - an intentional, documented code shadow;
    - a DB-newer structural change to promote to code; or
    - a stale duplicate to remove through the WordPress UI or a reviewed
      migration.
3. Reconcile the diverged Home, Herstory Archive, and Search templates one at
   a time, with rendered-route and block-parser verification after each.
4. Remove matching shadows and stale `search-*`/`header-*` records only after
   confirming that the live route resolves to the approved replacement.
5. Re-run strict template ownership and the release-handoff probe. Document
   any approved exception in `architecture/overview-and-ownership.md`.

### Acceptance criteria

- The ownership report is clean, or every remaining item is an explicit,
  documented exception with a named owner and review date.
- File-backed templates reproduce their intended routes without an accidental
  saved override.
- Navigation, footer social links, synced patterns, and ordinary editor
  content are not overwritten.

## Phase 3: Restore a credible quality baseline

### Objective

Turn existing validation machinery into meaningful release evidence.

### Steps

1. Complete the RAN Booster beta acceptance gate: pin its source,
   prove a least-privilege PAT setup, link/update the theme manually on
   staging, exercise signed SHA-pinned webhook deployment, and prove host
   recovery from a known-good revision. Confirm production error-display policy
   separately.
2. Classify the smoke visual differences as a real regression, an intended
   design change requiring reviewed snapshots, or a fixture/environment issue.
   Never refresh a baseline as the first response.
3. Resolve the five `format:check` failures and run the portable static gates:
   `format:check`, `audit:css-assets`, `lint:css`, and `lint:php:syntax`.
4. Run the appropriate visual lane while iterating, then the lean visual gate
   and the relevant template/navigation routes after the ownership work.
5. Add theme-repository automation for portable checks. Keep Local-database
   ownership and browser checks as documented release-environment gates unless
   a repeatable CI environment is deliberately provisioned.

### Acceptance criteria

- No PHP warnings/notices appear in the rendered test pages.
- Smoke and required landing visual gates pass with intentional snapshots only.
- Portable format, CSS, asset, and PHP checks pass from a clean checkout.
- The handbook distinguishes CI checks from Local release-environment checks.

## Phase 4: Set and verify the compatibility policy

### Objective

Replace false metadata with a small, maintainable client-support policy.

### Steps

1. The supported PHP floor is Local PHP `8.2.29`, the WordPress serving
   runtime. Validate every release there and, once this gate passes, set
   `Requires PHP: 8.2` in both `style.css` and `readme.txt`. The code-level
   PHP 8.0 API floor is not the supported-environment claim.
2. Record the exact, higher PHP version on the client production host as a
   dated release test target. Production must be tested before release, but its
   version does not raise the declared minimum or permit production-only PHP
   features.
3. Use Local's PHP binary explicitly for runtime-representative WP-CLI checks:

    ```sh
    "/Users/anachronistic/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php" /usr/local/bin/wp …
    ```

    Do not treat bare `php` or bare `wp` as serving-runtime evidence.

4. Record the active Local and production WordPress versions. The hard source
   floor remains WordPress 6.6 because of the theme JSON schema/runtime APIs;
   the production WordPress version becomes the current `Tested up to` target
   only after the required checks pass there or on a faithful staging runtime.
5. Define a small client-host verification matrix: Local PHP/WordPress
   baseline, production PHP/WordPress target, and the required visual/runtime
   checks for each. Re-run it whenever either host version changes.
6. After the matrix passes, update `style.css`, `readme.txt`, root README, and
   `operations/compatibility-policy.md` together. Align the `readme.txt`
   changelog with version `0.2.0` or the next released version.
7. Add the Release Please-declared `CHANGELOG.md` with an accurate baseline;
   do not fabricate an historical release narrative from incomplete records.
8. Record upgrade triggers: WordPress major release, PHP minor/major upgrade,
   plugin API change, and block-editor markup change.

### Acceptance criteria

- WordPress/PHP headers are true and traceable to a documented test result.
- No metadata claims support below a runtime API actually used by the theme.
- PHP 8.2 is the declared minimum after Local PHP 8.2.29 passes the release
  gate; the higher production runtime has separate recorded passing evidence.
- A maintainer can tell when compatibility verification must be repeated.
- Release Please references only files that exist and are maintained in this
  repository.

## Phase 5: Build the theme handbook from implementation

### Objective

Create the compendium as current reference material, not a retrospective of
the migration.

### Steps

1. Create `docs/README.md` as the handbook index and root `README.md` as the
   developer entry point.
2. Promote the durable policy from the wrapper's ownership guide into
   `architecture/overview-and-ownership.md`, but refresh all live baseline
   statements from Phase 2 first.
3. Move/adapt the durable CSS architecture into `architecture/css.md`; repair
   paths and remove Local-wrapper assumptions. Update `readme.txt` so it links
   to the new canonical path rather than the currently missing CSS document.
4. Build `reference/templates-parts-patterns.md` from `theme.json`,
   `templates/`, `parts/`, `patterns/`, `navigation/`, and `synced-patterns/`.
   For each item record purpose, owner, route/content contract, inserter state,
   dependencies, and editing boundary. Mark structures exercised by Phase 1 as
   `stable`, `provisional`, or `under evaluation`; do not present the current
   set as a frozen authoring system before the proof supports it.
5. Build `reference/component-and-functionality-catalog.md` from `inc/`,
   `blocks/`, assets, and scripts. Cover routing, navigation, featured-image
   focus, media sizes, lifecycle/seeding, render bridges, editor controls, and
   removal conditions.
6. Build `reference/integrations-and-boundaries.md` with required/optional
   plugin and service dependencies, settings owner, theme adapter location,
   and transition/removal path.
7. Build `operations/authoring-and-admin.md` with exact WordPress admin paths
   for navigation, footer social links, template parts, synced patterns, logo,
   and page-template selection. Separate safe content edits from release work.
8. Build `operations/development-and-release.md` from actual `package.json`
   scripts and current validation evidence: setup, commands, database audit,
   cache caveats, the sanitised GitHub Booster beta acceptance/deployment/
   recovery procedure, and handoff checklist.

### Acceptance criteria

- A new developer can locate every template, component, runtime behaviour, and
  integration without reading completed plans.
- Every non-obvious bridge has an owner, reason, and removal trigger.
- Admin/editor instructions use exact control paths and never direct a release
  to overwrite administrator or editor data.
- Documents describe the active theme path and support policy only; historical
  names are confined to a short maintenance note.

## Phase 6: Keep the handbook current

### Objective

Make documentation drift visible when the theme evolves.

### Steps

1. Add a lightweight, theme-local documentation check that validates internal
   links and verifies the catalogue accounts for each current template, part,
   code pattern, synced fixture, `inc/` module, and custom block.
2. Run it as part of the portable theme check without depending on the Local
   wrapper repository or a WordPress database.
3. Require a handbook update whenever a catalogued runtime surface is added,
   removed, or materially changes ownership.

### Acceptance criteria

- Broken internal handbook links fail a local check.
- New or removed runtime surfaces cannot silently escape the catalogue.
- The check runs from a clean clone of this repository.

## Phase 7: Prove the handoff

### Objective

Verify that the handbook and release process work for someone other than the
person who built the theme.

### Steps

1. Have a developer follow the root README in a clean checkout/Local site
   without relying on wrapper-repository notes.
2. Execute the documented portable checks and Local release-environment
   checks, recording the commit, runtime versions, and results.
3. Exercise the documented GitHub Booster manual update, signed webhook, and
   recovery procedure in staging using the approved ref policy. Confirm the
   deployed version and release record without exposing deployment credentials.
4. Perform one safe administrator task (for example, inspect navigation or
   update a test social-link setting) and one safe theme task (for example,
   identify a template owner) using only the handbook.
5. Resolve documentation gaps discovered during the rehearsal, then tag or
   otherwise identify the first handoff-ready theme release.

### Acceptance criteria

- The rehearsal does not require tribal knowledge or historical plan hunting.
- The documented checks reproduce the expected results.
- A staging GitHub Booster update, signed deployment, and recovery follow the
  documented policy successfully.
- Release, rollback, and data-ownership boundaries are understandable and
  usable by another developer.

## Deferred work

- Broad CSS aesthetic cleanup beyond the red visual gate.
- Consolidating documentation for independently released plugins into this
  repository.
- Rewriting completed plans, backup exports, or old Local-environment records.
- Creating a hosted CI environment for database/browser acceptance checks.

## Related material to consult, not duplicate

- Theme `navigation/README.md` and `synced-patterns/README.md`.
- The current template-ownership audit and release-handoff scripts.
- The wrapper's existing ownership and CSS guides, after live facts are
  revalidated.
- Historical remediation plans only when their decisions remain relevant to a
  current component or integration boundary.
