# Standalone Theme Migration Plan

Created on 2026-06-22.

All paths are relative to the project root.

## Goal

Create a new project-owned standalone WordPress block theme that absorbs the
active `protestsandsuffragettes` child theme and the small amount of useful
`estory` parent behavior, while leaving the existing `estory` parent plus child
theme setup intact until visual parity is proven.

The migration should not overwrite the current active theme. The new theme
should be built and tested side by side, then activated only after Playwright
visual regression tests and targeted WordPress checks pass.

## Guiding Constraints

- Work on a dedicated git branch for the migration.
- Keep `app/public/wp-content/themes/estory` and
  `app/public/wp-content/themes/protestsandsuffragettes` in place.
- Create a new standalone theme directory rather than mutating the child theme
  into a standalone theme in place.
- Treat current Site Editor database templates and parts as migration source
  material, not as the long-term source of truth.
- Use Playwright visual regression tests as the release gate for each meaningful
  migration slice.
- Keep frontend CSS authored under `styles/` and compiled with Lightning CSS.
- Preserve unrelated local work and avoid changing parent theme or plugin code.

## Proposed Theme Name

Use a new directory:

```text
app/public/wp-content/themes/protestsandsuffragettes-standalone/
```

Use a new theme name:

```text
Protests and Suffragettes Standalone
```

The final production theme name can be changed later, but using a distinct
directory during migration prevents accidental collision with the live child
theme and makes rollback straightforward.

## Phase 0: Branch and Baseline

Purpose: establish a clean migration lane and capture current behavior before
any new theme work starts.

Actions:

1. Settle or intentionally carry forward the current dirty worktree.
2. Create a dedicated branch:

   ```bash
   git switch -c theme/standalone-pns-migration
   ```

3. Confirm active theme state:

   ```bash
   wp option get stylesheet
   wp option get template
   wp theme list --status=active
   ```

4. Compile current child-theme CSS:

   ```bash
   pnpm --dir app/public/wp-content/themes/protestsandsuffragettes compile:css
   ```

5. Run the current visual suite against `http://localhost:10008`:

   ```bash
   pnpm --dir app/public/wp-content/themes/protestsandsuffragettes test:visual
   ```

6. Record baseline CSS metrics:

   ```bash
   pnpm --dir app/public/wp-content/themes/protestsandsuffragettes analyze:css
   ```

Exit criteria:

- Baseline visual tests pass or known failures are documented before migration
  work begins.
- Current active theme is confirmed as `stylesheet=protestsandsuffragettes` and
  `template=estory`.
- The branch contains only intentional migration work.

## Phase 1: Inventory Runtime Theme State

Purpose: capture the filesystem and database state that the standalone theme
must reproduce.

Actions:

1. Discover Site Editor records before exporting individual IDs:

   ```bash
   wp post list --post_type=wp_template,wp_template_part,wp_global_styles,wp_navigation --post_status=any --fields=ID,post_type,post_name,post_title,post_status --format=table
   ```

2. Record which theme slug owns each template, template part, and global style
   record. Site Editor records are scoped through the `wp_theme` taxonomy, so
   `protestsandsuffragettes` records will not automatically belong to a new
   standalone theme slug:

   ```bash
   wp post term list <record-id> wp_theme --format=json
   ```

3. Capture source content for the child-owned template records discovered above.
   The IDs below were observed on 2026-06-22, but should be rediscovered before
   use:

   ```bash
   wp post get <header-template-part-id> --field=post_content
   wp post get <footer-template-part-id> --field=post_content
   wp post get <contact-form-template-part-id> --field=post_content
   wp post get <page-template-id> --field=post_content
   wp post get <home-template-id> --field=post_content
   wp post get <404-template-id> --field=post_content
   wp post get <custom-page-template-id> --field=post_content
   ```

   Observed records from the initial audit:

   | Type | IDs |
   | --- | --- |
   | `wp_template_part` | `1027` header, `1026` footer, `4666` contact form |
   | `wp_template` | `1028` page, `1029` home, `3164` 404, `4689` page-no-contact-form, `4693` education-pack-giveaway |
   | `wp_global_styles` | `1030` for `protestsandsuffragettes` |
   | `wp_navigation` | `1035` top nav, `1032` footer nav |

4. Capture global styles and theme mods:

   ```bash
   wp post get <protestsandsuffragettes-global-styles-id> --field=post_content
   wp option get theme_mods_protestsandsuffragettes --format=json
   ```

5. Confirm plugin-owned dynamic block dependencies from
   `docs/2026-06-22-custom-blocks-patterns-audit.md`, especially:

   - `epico/dynamic-year-block`
   - `ecwid/store-block`
   - `ecwid/product-block`
   - `ec-store/minicart`
   - `jetpack/slideshow`
   - EmailOctopus shortcode/form markup

Exit criteria:

- Every currently rendered template, template part, navigation reference, global
  style override, and dynamic block dependency has a recorded migration target.
- Any database-only layout that should become file-owned is identified.

## Phase 2: Scaffold the Standalone Theme

Purpose: create a new theme that can be discovered by WordPress without changing
the active theme.

Actions:

1. Add the new theme directory.
2. Copy project-owned assets and tooling from the child theme:

   ```text
   fonts/
   styles/
   scripts/
   tests/
   package.json
   pnpm-lock.yaml
   pnpm-workspace.yaml
   playwright.config.ts
   .stylelintrc.json
   .prettierignore
   theme.json
   ```

3. Create a standalone `style.css` theme header without `Template: estory`.
4. Add the required minimal `index.php`.
5. Add a standalone `functions.php` that owns:

   - block style support
   - editor style support
   - frontend CSS enqueue
   - editor CSS enqueue
   - child-theme image sizes
   - Ecwid render cleanup
   - Gutenberg block blacklist

6. Update `.gitignore` allowlists so the new standalone theme path is tracked
   without tracking WordPress core, uploads, caches, parent themes, or plugins.

Exit criteria:

- `wp theme list` shows the new theme as available.
- The existing active theme remains unchanged.
- `pnpm compile:css` passes inside the new theme.
- No parent `estory-style` enqueue dependency remains in the new theme.

## Phase 3: Move File-Owned Templates and Parts

Purpose: make the standalone theme render from version-controlled block theme
files instead of relying on inherited parent files or child-theme database
template records.

Actions:

1. Copy existing child disk templates into the new theme:

   ```text
   templates/home.html
   templates/archive.html
   templates/search.html
   templates/single.html
   ```

2. Export child-owned database templates into files:

   ```text
   templates/page.html
   templates/404.html
   templates/page-no-contact-form.html
   templates/education-pack-giveaway-2.html
   ```

3. Export child-owned database template parts into files:

   ```text
   parts/header.html
   parts/footer.html
   parts/contact-form-octopus-tempate-part.html
   ```

4. Decide whether `home` should come from the existing child disk template or
   the database template. If they match, prefer the disk version.
5. Add missing standalone fallbacks so no parent template is required:

   ```text
   templates/index.html
   ```

6. Remove `theme:"protestsandsuffragettes"` references inside new theme files
   where WordPress can resolve local parts by slug.
7. Keep `wp_navigation` references by ID during the first parity pass. Moving
   navigation into code can be a later cleanup, because the current navigation
   posts are live content rather than theme scaffolding.

Exit criteria:

- The new theme has filesystem templates and parts for every route covered by
  the current child/database setup.
- No new theme template references `theme:"protestsandsuffragettes"` or
  `theme:"estory"`.
- No new theme template depends on an `estory/*` pattern.

## Phase 4: Absorb Only Useful Parent Behavior

Purpose: replace the parent dependency with explicit project-owned behavior.

Actions:

1. Recreate only the needed parent setup in the standalone theme:

   - `add_theme_support( 'wp-block-styles' )`
   - `add_theme_support( 'editor-styles' )`
   - project-owned editor style registration

2. Do not carry forward ThemeGrill admin welcome/demo-importer behavior.
3. Do not carry forward parent DM Sans/Marcellus font preloads unless a visual
   check proves they are still needed.
4. Replace compatibility font-token aliases only where content still needs them:

   - keep `dm-sans` mapped to Libre Franklin during parity
   - keep `marcellus` mapped to Rubik during parity
   - remove or rename after rendered content no longer references them

5. Replace remaining `estory` class coupling:

   - rename `estory-comment` in `single.html`
   - either port required styles into project-owned selectors or remove dead
     parent-class styling

Exit criteria:

- New theme runtime does not load parent PHP, parent CSS, parent fonts, parent
  pattern files, or parent admin code.
- Any retained parent-compatible token or class is documented as temporary
  migration compatibility.

## Phase 5: First Activation Parity Check

Purpose: activate the new theme locally only after it can compile and has file
coverage for known templates.

Actions:

1. Compile CSS in the new theme:

   ```bash
   pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
   ```

2. Activate the new theme locally:

   ```bash
   wp theme activate protestsandsuffragettes-standalone
   ```

3. Confirm active state:

   ```bash
   wp option get stylesheet
   wp option get template
   wp theme list --status=active
   ```

4. Smoke-check core frontend routes in the browser:

   - `/`
   - `/herstories/mary-barbour/`
   - `/shop/`
   - `/educational-resources/`
   - `/not-a-real-page-for-404-check/`

5. If activation breaks the site, immediately roll back:

   ```bash
   wp theme activate protestsandsuffragettes
   ```

Exit criteria:

- The new theme activates without fatal errors.
- Header, footer, navigation, page content, shop, Jetpack slideshow pages, and
  404 render without obvious missing-template or missing-block failures.
- Rollback command is verified.

## Phase 6: Playwright Visual Regression Gate

Purpose: prove the new standalone theme does not visually regress the current
site behavior.

Actions:

1. Run the existing visual suite from the new theme:

   ```bash
   pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual
   ```

2. If tests fail, inspect the report:

   ```bash
   pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:report
   ```

3. For unintended differences, fix the new theme and rerun.
4. For intentional differences, document the reason before any snapshot update.
5. Add or extend visual coverage before migration is considered complete:

   - home page
   - Mary Barbour herstory page
   - shop page with Ecwid output
   - page template with footer contact form
   - news archive/search route
   - 404 route
   - mobile navigation open state

Exit criteria:

- Existing Playwright visual tests pass.
- Added migration-critical route coverage passes.
- Any intentional snapshot update is documented and local-only unless project
  policy changes.

## Phase 7: CSS and Template Debt Cleanup

Purpose: reduce compatibility shims after parity is proven, one concern at a
time.

Actions:

1. Remove dead parent-specific CSS only after rendered content/template searches
   and Playwright checks show it is unused.
2. Replace generated WordPress class selectors such as
   `nav.wp-container-core-navigation-is-layout-808e6b47` with stable scoped
   selectors.
3. Move legacy CSS from `styles/blocks/legacy-*.css` into layered files in small
   batches.
4. Use Wallace metrics to track whether cleanup is reducing size and complexity:

   ```bash
   pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone analyze:css
   ```

Exit criteria:

- Each cleanup batch compiles and passes Playwright before the next batch starts.
- Remaining `!important` rules fit the documented exceptions in the frontend CSS
  regression workflow.
- Parent compatibility shims are either removed or explicitly documented.

## Phase 8: Cutover Decision

Purpose: decide whether the standalone theme is ready to become the maintained
theme.

Actions:

1. Confirm the new theme is active and stable locally.
2. Run final checks:

   ```bash
   pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
   pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual
   ```

3. Confirm WordPress active state:

   ```bash
   wp theme list --status=active
   wp option get stylesheet
   wp option get template
   ```

4. Decide whether to:

   - keep both themes in the repo temporarily
   - rename the standalone theme directory after cutover
   - leave old child/parent themes on disk as rollback only
   - remove old child theme from tracked scope in a later cleanup branch

Exit criteria:

- New standalone theme is the only theme required for normal local rendering.
- Existing `estory` parent plus child setup remains available as rollback until
  the owner explicitly approves cleanup.
- Final migration notes include activation commands, rollback command, test
  evidence, and known residual debt.

## Rollback Strategy

The migration should always preserve an immediate local rollback:

```bash
wp theme activate protestsandsuffragettes
```

Do not delete or overwrite:

```text
app/public/wp-content/themes/estory/
app/public/wp-content/themes/protestsandsuffragettes/
```

Do not update visual baselines just to make the new theme pass. Snapshot updates
are only valid when the visual change is intentional and documented.

## Key Risks

| Risk | Mitigation |
| --- | --- |
| Site Editor templates are database-owned and theme-slug scoped | Export live templates and parts into the new theme before activation. |
| Parent enqueue currently provides the runtime CSS handle | New standalone theme must enqueue its own frontend/editor CSS directly. |
| Parent font preloads may still affect perceived rendering | Remove only after visual comparison; keep project font aliases during parity. |
| Navigation is stored as `wp_navigation` posts | Keep existing navigation refs during parity; code-owned navigation can be a later decision. |
| Plugin dynamic blocks may fail if plugin state changes | Keep plugin inventory in the test matrix and include shop/slideshow/footer routes. |
| Generated WordPress classes may change | Replace generated-class selectors after parity with stable theme-scoped selectors. |
| Old child theme remains tracked while new theme is added | Update `.gitignore` allowlists deliberately and keep commit scope narrow. |

## Recommended Commit Slices

1. Plan and branch setup.
2. New standalone theme scaffold and gitignore allowlist.
3. Template and template-part export.
4. Standalone enqueue/setup behavior.
5. First activation fixes.
6. Expanded Playwright coverage.
7. Parent-compatibility cleanup batches.
8. Final cutover notes.

## Implementation Status: Feasibility Slice

Started on branch `theme/standalone-pns-migration` on 2026-06-23.

Locked decisions for the first implementation slice:

- Use `app/public/wp-content/themes/protestsandsuffragettes-standalone/` as the
  side-by-side standalone theme directory.
- Keep the existing child theme active outside short local activation smokes.
- Port only the useful child/runtime behavior into standalone `functions.php`;
  do not port eStory demo/admin/pattern registration code.
- Keep existing `wp_navigation` refs for parity; navigation can become
  code-owned later if needed.
- Export live child-owned templates and parts into filesystem templates/parts,
  replacing old theme slug references with the standalone slug.
- Keep legacy content/template utility classes such as `pands-logo` and
  `footer-wt` during the parity slice; they are compatibility hooks used by
  existing CSS and visual tests, not theme-slug ownership markers.
- Bake the active child theme global-style typography override into
  standalone `theme.json`, because `wp_global_styles` records are theme-slug
  scoped and the new standalone slug will not inherit
  `wp-global-styles-protestsandsuffragettes`.

Dex tracking:

- Parent task: `trqz1da6`
- Feasibility subtasks: `cucctffg`, `dh4p926n`, `8c4d9xuj`, `gn11e7xd`

Evidence from the first implementation slice:

- WordPress discovered the inactive standalone theme.
- Standalone frontend and editor CSS compiled with Lightning CSS using the
  existing local theme toolchain.
- `functions.php` and `index.php` passed PHP syntax checks.
- The standalone theme activated locally and returned expected route statuses
  for `/`, `/herstories/mary-barbour/`, `/shop/`, `/edu-giveaway/`, and a 404
  route.
- Smoke HTML used standalone theme asset URLs and the `pns-standalone-style`
  handle.
- Local WordPress was rolled back to `protestsandsuffragettes` after the smoke.

Phase 6 visual gate evidence from 2026-06-24:

- Standalone dependencies installed from the committed lockfile.
- Aggregate `pnpm check` remains blocked by the local pnpm version-switch
  signature verification layer, so equivalent direct binary gates were run from
  standalone `node_modules`.
- Direct standalone gates passed: Prettier, Stylelint, frontend Lightning CSS
  compile, editor Lightning CSS compile, and `git diff --check`.
- Local WordPress was activated to
  `stylesheet=protestsandsuffragettes-standalone` and
  `template=protestsandsuffragettes-standalone`.
- Route smokes passed: `/` 200, `/herstories/mary-barbour/` 200, `/shop/` 200,
  `/edu-giveaway/` 200, and `/not-a-real-page-for-404-check/` 404.
- Elevated standalone Playwright visual suite passed 30/30.
- Per owner instruction, local WordPress was not rolled back after this gate;
  the standalone theme remains active locally.

Internal URL cleanup from 2026-06-24:

- Source of the visible production links was mixed: `wp_navigation` records
  `1035` and `1032` owned the header/footer menu URLs, while homepage button
  links came from page/content records such as front page ID `49`.
- Local DB-backed internal links in navigation, pages, reusable blocks,
  templates, template parts, and BlockMeister patterns were normalized from
  `https://protestsandsuffragettes.com/...` to relative paths.
- Feedback submissions were excluded from the DB rewrite.
- Backup of changed DB records was written to
  `/tmp/pns-prod-url-db-backup-20260623231736.json`.
- Standalone filesystem templates were also normalized: footer logo image now
  uses `/wp-content/uploads/2022/08/logo.png`, and the 404 home button now uses
  `/`.
- Verification: rendered home page no longer includes production-origin links
  for internal page routes; the only remaining DB matches for the production
  origin are `feedback` records.

Final cutover resolution from 2026-06-24:

- The aggregate `pnpm check` wrapper remains unreliable in this restricted shell
  because of the local pnpm version-switch signature verification failure. This
  is not a standalone-theme blocker: equivalent direct gates from committed
  `node_modules` pass for formatting, Stylelint, frontend Lightning CSS compile,
  editor Lightning CSS compile, Playwright visual tests, and `git diff --check`.
- The intentionally simplified saved-template cleanup, including
  `page-no-contact-form`, is accepted as parity-plus for this migration. Further
  template refinements can happen in normal follow-up work, but no open
  standalone migration blocker remains.
- Footer parity was corrected after screenshot review: the contact separator now
  spans the contact block, footer social links match production services/order,
  and footer navigation uses the production `font-variation-settings: "wght"
  600` rhythm.
- Mobile visual baselines were intentionally updated for the footer content and
  rhythm changes.
- Final standalone visual gate: elevated Playwright suite passed 39/39.

Standalone migration status: complete. No open plan items remain before merging
the branch to `main`.
