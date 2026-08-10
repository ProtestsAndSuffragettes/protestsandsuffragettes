# Template And Component Extraction Plan

Plan started on 2026-06-24.

All paths are relative to the project root.

## Goal

Move important site structures out of plugin-owned or click-ops-only workflows
and into source-of-truth models that match their real behavior:

- global site chrome stays globally updatable;
- navigation items stay editable in WordPress admin;
- stable starter layouts become reviewable theme code;
- synced content stays synced only when global update semantics are intentional;
- plugin-owned blocks remain explicit dependencies rather than hidden structure.

This plan supersedes any earlier assumption that foundational site elements such
as the footer, header, or top banner should be migrated as normal inserter
patterns. Inserter patterns copy markup into content and are therefore the wrong
model for global structures that must remain consistent across pages.

## WordPress Layout Nomenclature Key

WordPress uses overlapping names for several different layout systems. This is
the operational vocabulary for this project.

| Term                                  | Plain meaning                                                                                                                                              | Where it usually lives                                                                                                              | Update behavior                                                                                                                                                                 | Project examples                                                                                      |
| ------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------- |
| Block type                            | A registered editing/rendering unit, similar to a component class. Content can only use a block if core, the theme, or a plugin registers that block type. | Core, plugin PHP/JS, or project code through `block.json` / `register_block_type()`                                                 | Registration controls whether saved block markup can render correctly. Editing one page only changes that page unless the block is inside a synced object.                      | `core/group`, `core/navigation`, `ecwid/store-block`, `jetpack/slideshow`, `epico/dynamic-year-block` |
| Block markup                          | The saved instance of a block in page/template content. It is the comment-delimited HTML-like content WordPress parses at render time.                     | `post_content` in pages, posts, `wp_block`, `wp_template`, `wp_template_part`, `wp_navigation`, or files such as `templates/*.html` | Changes only the object where the markup is stored, unless it references a synced pattern.                                                                                      | `<!-- wp:group -->...<!-- /wp:group -->` inside templates and patterns                                |
| Template                              | Route-level layout for a page type or specific page. It decides the overall page structure.                                                                | Filesystem `templates/*.html` or DB post type `wp_template`                                                                         | One template affects every route that resolves to it. DB-saved templates can override theme files.                                                                              | `templates/page.html`, `templates/home.html`, `templates/education-pack-giveaway-2.html`              |
| Template part                         | Reusable structural region included by templates. This is the right model for global chrome.                                                               | Filesystem `parts/*.html` or DB post type `wp_template_part`                                                                        | One part affects every template that includes it. DB-saved parts can override theme files.                                                                                      | `parts/header.html`, `parts/footer.html`                                                              |
| Navigation block                      | A block that renders a menu. Its saved block instance usually points at a `wp_navigation` post.                                                            | Template part markup plus `wp_navigation` content                                                                                   | The wrapper/placement belongs to the template part; labels, URLs, ordering, and nesting belong to the `wp_navigation` post.                                                     | Top nav in Header, footer nav in Footer                                                               |
| `wp_navigation`                       | The database post type WordPress uses to store Navigation block menu content.                                                                              | WordPress database, editable in the admin/site editor                                                                               | Editing the nav record updates every Navigation block that references it.                                                                                                       | Top Nav `#1035`, footer Navigation `#1032`, future Banner CTA Nav                                     |
| Registered pattern / inserter pattern | A reusable starter layout shown in the editor inserter. Inserting it normally copies its block markup into the current page.                               | Registered by core, a theme, a plugin, BlockMeister, or project PHP                                                                 | Future edits to the source pattern usually do not update pages where it was already inserted.                                                                                   | Parent `estory/*` patterns, BlockMeister patterns, project `pns/*` patterns                           |
| Theme pattern / code-backed pattern   | A registered inserter pattern whose source is versioned in the standalone theme.                                                                           | `patterns/*.php`, registered by the theme                                                                                           | Same copied-markup behavior as other inserter patterns, but source is reviewable in Git.                                                                                        | `pns/welcome-header`, `pns/blockquote-cover`, `pns/blockquote-with-red-line`                          |
| Synced pattern / reusable block       | Shared content stored once and referenced elsewhere through a `core/block` ref. WordPress formerly called these reusable blocks.                           | DB post type `wp_block`                                                                                                             | Editing the `wp_block` updates every `core/block` reference to it. This is global shared content, not copied starter markup.                                                    | `Shop Intro` `wp_block #1509`, `Connect Social` `#1494`                                               |
| BlockMeister pattern                  | A plugin-managed pattern source stored as a `blockmeister_pattern` post. BlockMeister registers these posts as normal inserter patterns.                   | DB post type `blockmeister_pattern`, managed by the BlockMeister plugin UI                                                          | Inserting normally copies markup into content. Editing the BlockMeister source changes what appears in the inserter later, but does not reliably update already-inserted pages. | `Welcome Header`, `Blockquote Cover`, `Header w/nav`, `Footer w/nav`                                  |
| Global styles / `theme.json`          | Design settings, presets, and block style defaults. This is not content and not a template.                                                                | `theme.json`, plus possible DB `wp_global_styles` overrides                                                                         | Controls available design tokens and defaults; content/templates choose whether to use those presets.                                                                           | Color palette, typography scale, spacing presets, layout widths                                       |
| Custom template                       | A named template editors can assign to specific pages.                                                                                                     | `templates/*.html` plus `theme.json` `customTemplates` metadata                                                                     | Affects pages assigned to that template.                                                                                                                                        | Education giveaway template                                                                           |
| Plugin block                          | A block type registered by a third-party plugin.                                                                                                           | Plugin code and plugin assets                                                                                                       | Rendering depends on the plugin staying active. Theme CSS may style it, but the behavior remains plugin-owned.                                                                  | Ecwid store/product blocks, Jetpack slideshow/forms, Dynamic Year                                     |
| Custom project block                  | A block type this project would own in code.                                                                                                               | Future project plugin or theme block code                                                                                           | Best when strict fields, validation, dynamic rendering, or repeatable behavior cannot be expressed cleanly with core blocks/patterns/templates/nav.                             | Possible future shop/product grid or structured CTA block                                             |

### BlockMeister In This Project

BlockMeister solves a real authoring problem: it lets site builders create and
manage editor-insertable block patterns from the WordPress admin without writing
theme code. On this inherited site it became a convenient pattern library for
repeatable scaffolds such as quote sections, welcome headers, previous/next
sections, and even header/footer experiments.

That convenience is also the reason it is now a migration concern:

- the canonical source lives in database records instead of Git;
- review happens through plugin/admin UI rather than normal code review;
- the plugin has its own pattern-management model and naming behavior;
- inserted patterns become copied page markup, so editing the BlockMeister
  pattern is not the same as globally updating every page that used it;
- some BlockMeister entries are not really patterns anymore, such as
  header/footer structures that should be governed as template parts.

Current stance:

- Keep BlockMeister installed while existing editor workflow and legacy pattern
  sources still depend on it.
- Extract stable, reusable starter scaffolds into theme-owned `pns/*` patterns.
- Do not use BlockMeister for global chrome, navigation, synced shared content,
  or structures that need deterministic review and rollout.
- Do not delete BlockMeister records until replacements are proven, exported,
  and editors have a clear transition path.

## DEX Task Governance

This plan is DEX-backed. The markdown plan describes the architecture and
acceptance model; DEX owns sequencing, active work state, and closeout evidence.

Run DEX from the project root. The project `.dex/config.toml` currently points
task storage at the existing child-theme DEX file:

```text
app/public/wp-content/themes/protestsandsuffragettes/.dex/tasks.jsonl
```

Standard commands:

```bash
dex list --all
dex show 3w25cbsg --full
```

If DEX ever resolves to a stale global store, pass the storage path explicitly:

```bash
dex --storage-path /Users/anachronistic/Local\ Sites/pns-stageing/app/public/wp-content/themes/protestsandsuffragettes/.dex list --all
```

Parent task:

```text
3w25cbsg - Template/component extraction governance
```

Phase tasks:

| Plan phase | DEX ID     | Task                                  | Gate before next phase                                                                      |
| ---------- | ---------- | ------------------------------------- | ------------------------------------------------------------------------------------------- |
| Phase 0    | `yij4u2le` | `TC0: Inventory and source map`       | Current DB/file ownership and rollback exports are documented.                              |
| Phase 1    | `53ss0p31` | `TC1: Global chrome governance`       | Header/footer/banner ownership is proven with runtime and visual checks.                    |
| Phase 2    | `hn6yajot` | `TC2: Code-backed pattern QA`         | `pns/*` patterns are visible in the inserter and render on a QA route.                      |
| Phase 3    | `acpakun8` | `TC3: Live adoption trial`            | One low-risk live replacement is exported, applied, visually checked, and documented.       |
| Phase 4    | `bg9rk0xa` | `TC4: Next extraction candidates`     | Each candidate is classified before any implementation.                                     |
| Phase 5    | `koh2py8u` | `TC5: Synced content governance`      | `wp_block` reference counts and affected routes are documented before mutation.             |
| Phase 6    | `roi4jyzh` | `TC6: Plugin dependency reduction`    | No plugin is disabled or removed without usage, runtime, and visual proof.                  |
| Phase 7    | `56yumbi8` | `TC7: Legacy deprecation and cleanup` | Legacy records are exported, replacement QA is complete, and editor transition notes exist. |

Operating rules:

- Start the relevant DEX task before implementation:

  ```bash
  dex start <task-id>
  ```

- Do not begin a later phase while its predecessor gate is unresolved, unless a
  new child task explicitly records why the work is independent.
- Every implementation slice should either use the existing phase task or create
  a narrower child task under it.
- Each task result must include:
  - files changed;
  - database records touched or exported;
  - WP-CLI/runtime checks;
  - visual checks or a reason visuals were not applicable;
  - rollback path;
  - follow-up tasks, if any.
- Complete a planning-only task with `--no-commit`. Complete implementation
  tasks with the relevant commit SHA when a commit exists.
- Do not mark the parent task complete until every phase task is complete or a
  documented successor DEX task owns the deferred work.

## Source-Of-Truth Types

| Type                               | Owns                                 | Update behavior                                                              | Correct use                                                                                               |
| ---------------------------------- | ------------------------------------ | ---------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------- |
| Standalone template part           | Structural chrome in `parts/*.html`  | One structure wherever that template part is used                            | Header, footer, top banner wrapper, site-wide contact/footer layout                                       |
| `wp_navigation`                    | Menu labels, URLs, ordering, nesting | Editors update links in WordPress admin; all rendered uses update            | Top nav, footer nav, banner CTA links                                                                     |
| Native synced pattern / `wp_block` | Shared editable content              | Editing the `wp_block` updates all `core/block` references                   | Content modules where global update semantics are desired                                                 |
| Theme inserter pattern / `pns/*`   | Versioned starter markup             | Insertion copies markup; future pattern changes do not update existing pages | Page sections, quote modules, hero starters, biography starter layouts                                    |
| Filesystem template                | Route-level structure                | One template per matching route/post type                                    | Page/post/archive templates, future herstory detail template                                              |
| Plugin block                       | Plugin-owned dynamic behavior        | Depends on plugin runtime                                                    | Ecwid store/products, Jetpack slideshow/forms, dynamic year until replaced                                |
| Custom project block               | Project-owned structured behavior    | Code-reviewed rendering and fields                                           | Only when core blocks, template parts, synced patterns, or nav records cannot enforce the needed contract |

## Filesystem Layout Decision

The standalone theme keeps WordPress's standard block-theme directories at the
theme root:

```text
templates/
parts/
patterns/
```

These names are not project-invented clutter. WordPress core discovers block
templates from `templates/`, template parts from `parts/`, and theme pattern
files from `patterns/`. We will learn and preserve that standard structure
rather than moving the files behind a custom source root, symlinks, or generated
copies.

Human-facing organization should come from clear docs, validation, and naming:

- keep route-level structures in `templates/`;
- keep global structural regions in `parts/`;
- keep copied starter layouts in `patterns/`;
- keep those block-template files out of generic HTML formatters;
- validate their WordPress block delimiters with the project block-template
  validator.

## Current Known Sources

| Object                              | Current source                                                     | Target stance                                                                                                      |
| ----------------------------------- | ------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------ |
| Header structure                    | `parts/header.html` in the standalone theme                        | Keep code-owned. Top nav remains `wp_navigation` `1035`.                                                           |
| Footer structure                    | `parts/footer.html` in the standalone theme                        | Keep code-owned. Footer nav remains `wp_navigation` `1032`.                                                        |
| Top navigation content              | `wp_navigation` `1035` (`Top Nav`)                                 | Keep admin-editable; theme owns mobile drawer behavior.                                                            |
| Footer navigation content           | `wp_navigation` `1032` (`Navigation`)                              | Keep admin-editable; footer layout stays code-owned.                                                               |
| Top banner CTA                      | `parts/header.html` plus `wp_navigation` `5259` (`Banner CTA Nav`) | Keep code-owned wrapper/placement in the header; labels and URLs remain admin-editable in WordPress navigation UI. |
| BlockMeister stable layout patterns | `blockmeister_pattern` posts                                       | Replace selectively with `pns/*` theme patterns.                                                                   |
| `Shop Intro`                        | Native synced pattern `wp_block #1509`                             | Keep synced for now because existing references depend on global update behavior. Live reference count is 12.       |
| Header/Footer BlockMeister patterns | `Header w/nav`, `Footer w/nav`                                     | Do not migrate as patterns; template parts already own this concern.                                               |

## 2026-06-25 P0/P1 Evidence

DEX tasks:

- `yij4u2le` (`TC0`) started on 2026-06-25.
- `53ss0p31` (`TC1`) started on 2026-06-25.

Inventory and rollback exports:

| Export                                                                                                  | Purpose                                                                                                                    |
| ------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| `docs/jobs/template-component-db-backups/2026-06-25-template-component-source-records.json`             | Pre-change rollback export for `wp_template`, `wp_template_part`, `wp_navigation`, `wp_block`, and `blockmeister_pattern`. |
| `docs/jobs/template-component-db-backups/2026-06-25-template-component-source-records-after-p0-p1.json` | Post-P0/P1 source-map export after template reference normalization and banner nav creation.                               |

Confirmed active theme:

```text
stylesheet: protestsandsuffragettes-standalone
template: protestsandsuffragettes-standalone
```

P0 source-map correction:

- Saved DB templates existed for `Education Pack Giveaway`, `page-no-contact-form`,
  `404`, `Page`, and `Home`.
- Those saved templates still referenced `theme:"protestsandsuffragettes"` for
  template parts.
- The saved template references were normalized to
  `theme:"protestsandsuffragettes-standalone"` after the rollback export.
- No saved DB Header or Footer template-part override was present in the
  refreshed source list; header/footer structure remains file-owned.

P1 implementation:

- Created `wp_navigation` `5259`, `Banner CTA Nav`.
- Added the banner navigation block to `parts/header.html`.
- Added scoped component styling in
  `styles/components/cross-site-banner-cta.css`.
- Added Playwright banner contracts for cross-route rendering, CTA links, color,
  notch, and horizontal overflow.

## Extraction Decision Matrix

Use this matrix before moving any object:

| Question                                               | If yes                                                          | If no                                    |
| ------------------------------------------------------ | --------------------------------------------------------------- | ---------------------------------------- |
| Must every page show the same result after one update? | Template part, `wp_navigation`, synced pattern, or custom block | Theme inserter pattern may be acceptable |
| Are links/order the only editable data?                | `wp_navigation`                                                 | Continue evaluating                      |
| Is it route structure rather than content?             | Filesystem template or template part                            | Continue evaluating                      |
| Is it a starter section editors copy and adapt?        | Theme inserter pattern                                          | Continue evaluating                      |
| Does it require structured fields or validation?       | Custom project block candidate                                  | Avoid custom block overhead              |
| Does it depend on a third-party dynamic block?         | Keep dependency explicit; defer or wrap carefully               | Code-backed extraction is lower risk     |

## Phase 0 - Inventory And Lock The Map

DEX gate: `yij4u2le`.

Purpose: prevent source-of-truth confusion before extracting more objects.

Actions:

1. Refresh DB-backed object inventories:
   - `wp_template`
   - `wp_template_part`
   - `wp_navigation`
   - `wp_block`
   - `blockmeister_pattern`
2. Export the current records before any destructive changes.
3. Update the source map in this plan if IDs or titles drift.
4. Confirm the active theme is `protestsandsuffragettes-standalone`.

Acceptance checks:

- Current IDs and ownership are documented.
- Any DB mutation has a backup file under `docs/jobs/`.
- No BlockMeister object is deleted before a replacement is verified.

## Phase 1 - Global Chrome Governance

DEX gate: `53ss0p31`.

Purpose: make site-wide structures global and code-reviewable.

### Header

Target owner:

- Structure: `parts/header.html`
- Top nav links: `wp_navigation` `1035`
- Mobile nav behavior: standalone theme PHP/JS/CSS

Status:

- Header filesystem part is already the intended structure owner.
- Top nav is already admin-editable through `wp_navigation`.
- Mobile drawer is already code-owned and fed by `wp_navigation`.

Next checks:

- Confirm no saved DB Header override has reappeared.
- Keep visual coverage on home, interior, shop, and giveaway routes.

### Footer

Target owner:

- Structure: `parts/footer.html`
- Footer nav links: `wp_navigation` `1032`
- Footer visual styling: standalone theme CSS

Status:

- Footer filesystem part is already the intended structure owner.
- Footer nav is already admin-editable through `wp_navigation`.

Next checks:

- Confirm no saved DB Footer override has reappeared.
- Decide whether footer social/contact content should remain code-owned in
  `parts/footer.html` or move to a small editable synced object. Default:
  keep code-owned unless the client needs frequent edits.

### Top Banner CTA

Target owner:

- Structure and styling: standalone theme component/template part.
- CTA labels and links: dedicated `wp_navigation` record, for example
  `Banner CTA Nav`.

Do not implement as:

- BlockMeister pattern.
- Normal theme inserter pattern.
- Per-page copied markup.

Reason:

- The banner is global site chrome and contains navigation-like links.
- `wp_navigation` gives editors the right controls without letting wrapper
  structure drift page by page.

Implementation steps:

1. Create or identify the `Banner CTA Nav` `wp_navigation` record.
2. Add a code-owned banner wrapper below the header navigation.
3. Render links from the banner nav record.
4. Add component CSS for layout, color, notch, wrapping, and focus state.
5. Add Playwright contracts:
   - banner renders once per page;
   - banner links match the nav record;
   - no horizontal overflow;
   - mobile nav still opens and closes.

Acceptance checks:

- Banner appears consistently on all templates using the header.
- Editors can update labels/URLs in WP admin.
- Full visual suite passes or intentional baselines are reviewed.

## Phase 2 - Code-Backed Inserter Patterns

DEX gate: `hn6yajot`.

Purpose: replace stable BlockMeister starter layouts with Git-owned `pns/*`
patterns.

Completed first slice:

| BlockMeister source        | Code-backed pattern            |
| -------------------------- | ------------------------------ |
| `Welcome Header`           | `pns/welcome-header`           |
| `Blockquote Cover`         | `pns/blockquote-cover`         |
| `Blockquote with red line` | `pns/blockquote-with-red-line` |

Completed QA gate on 2026-06-25:

- DEX task `hn6yajot` (`TC2`) was started.
- Added `scripts/seed-pattern-qa-page.php`.
- Seeded local page `PNS Pattern QA` (`#5265`) at `/pns-pattern-qa/`.
- The QA page is generated from the registered `pns/*` pattern contents.
- Runtime registry confirms:
  - `pns/welcome-header` as `PNS - Welcome Header` under `pns-layout`;
  - `pns/blockquote-cover` as `PNS - Blockquote Cover` under `pns-quotes`;
  - `pns/blockquote-with-red-line` as `PNS - Blockquote With Red Line` under
    `pns-quotes`;
  - categories `pns-layout` / `PNS Layouts` and `pns-quotes` / `PNS Quotes`.
- Added Pattern QA visual snapshot coverage and a computed contract to
  `tests/visual/frontend.spec.ts`.

The visible `PNS -` title prefix is intentional so editors can distinguish
theme-owned code-backed patterns from legacy BlockMeister entries in the
inserter.

Acceptance checks:

- Pattern registry exposes the `pns/*` slugs.
- Pattern QA page renders without missing assets or editor-only artifacts.
- Visual route passes across viewports.

Only after this:

- Ask editors to use `pns/*` replacements for new content.
- Rename BlockMeister duplicates as `Legacy` or remove them after export.

## Phase 3 - Live Adoption Trials

DEX gate: `acpakun8`.

Purpose: prove replacement workflows on low-risk live content.

Recommended first live trial:

- A quote section using `pns/blockquote-cover` or
  `pns/blockquote-with-red-line`.

Avoid first:

- Homepage hero.
- Footer/header.
- Shop or Ecwid content.
- Large biography pages.

Steps:

1. Identify one page section that visually matches a new `pns/*` pattern.
2. Export the page before editing.
3. Replace the copied BlockMeister-derived section with the `pns/*` insertion.
4. Run focused visual snapshot for that route.
5. Run full visual suite if the route is covered or shared styles changed.

Acceptance checks:

- The live page is visually equivalent or the difference is intentional.
- The source of the replacement is clear in docs.
- Rollback is possible from the exported page content.

Completed first trial on 2026-06-25:

- DEX task `acpakun8` (`TC3`) was started.
- Selected published test-like page `Store BLOCK TEST` (`#3228`) at
  `/store-block-test/` as the low-risk route.
- Avoided the main Mary Barbour biography even though it contains similar
  markup, because that page is a higher-value biography route.
- Exported the page before and after the edit:
  - `docs/jobs/live-adoption-db-backups/2026-06-25-page-3228-store-block-test-before-tc3.json`
  - `docs/jobs/live-adoption-db-backups/2026-06-25-page-3228-store-block-test-after-tc3.json`
- Replaced exactly one copied `core/cover` quote section containing
  `Quote_image_1.jpg` and the Mary Barbour citation with the registered
  `pns/blockquote-with-red-line` pattern content.
- Runtime verification confirmed the replacement block serializes exactly to
  the registered `pns/blockquote-with-red-line` content.
- Added focused Playwright coverage for the adopted quote section on
  `/store-block-test/` across desktop, tablet, and mobile.

Rollback path:

- Restore `post_content` for page `#3228` from
  `docs/jobs/live-adoption-db-backups/2026-06-25-page-3228-store-block-test-before-tc3.json`.

## Phase 4 - Next Extraction Candidates

DEX gate: `bg9rk0xa`.

Purpose: continue migration while separating pattern, template, and block
concerns.

| Candidate                                | Target                                                                               | Why                                                                   | Required cleanup                                                                                                  |
| ---------------------------------------- | ------------------------------------------------------------------------------------ | --------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------- |
| `Suffragette Stats`                      | `pns/suffragette-stats` pattern                                                      | Reusable-looking site module                                          | Completed first cleanup: old local upload URL replaced with a relative uploads URL. Stats remain starter content. |
| `Previous Next`                          | Prefer template/query navigation if automatic; otherwise `pns/previous-next` pattern | Manual repeated navigation should not drift if behavior is systematic | Decide whether links are editorial or route-derived                                                               |
| `Individual Activist Page`               | `page-activist` custom template plus `pns-herstories` section patterns               | Canonical biography structure                                         | Completed cleanup: giant BlockMeister source split into template plus smaller dependency-light section patterns.  |
| `Two Columns`                            | `pns/two-columns` pattern                                                            | Reusable text/media scaffold                                           | Completed cleanup: replaced `jetpack/slideshow` with a core image placeholder.                                    |
| `Basic Centred Content`                  | `pns/basic-centred-content` pattern                                                  | Generic content scaffold                                              | Completed cleanup: privacy-policy starter copy replaced with neutral starter copy.                                |
| `Mary Barbour TEMPLATE PAGE -- June2023` | Do not migrate directly                                                              | Page-specific snapshot                                                | Use only as reference material                                                                                    |

2026-06-25 safe-pattern extraction:

- DEX child `mzbodxnw` (`TC4a`) was created for the remaining safe
  BlockMeister starter-pattern migration.
- Exported source records before and after:
  - `docs/jobs/live-adoption-db-backups/2026-06-25-blockmeister-safe-patterns-before-extraction.json`
  - `docs/jobs/live-adoption-db-backups/2026-06-25-blockmeister-safe-patterns-after-extraction.json`
- Added code-backed theme patterns:
  - `pns/basic-centred-content` / `PNS - Basic Centred Content`;
  - `pns/suffragette-stats` / `PNS - Suffragette Stats`;
  - `pns/previous-next` / `PNS - Previous Next`.
- Corrected `pns/previous-next` after extraction so it contains only the
  previous/back/next controls. The quote cover inherited from the original
  BlockMeister source was intentionally removed as design noise.
- Updated Pattern QA to render the current code-backed `pns/*` patterns.
- Unpublished replaced BlockMeister sources by setting `post_status` to
  `draft` for:
  - `blockmeister_pattern #1451` / `Basic Centred Content`;
  - `blockmeister_pattern #2960` / `Suffragette Stats`;
  - `blockmeister_pattern #2965` / `Previous Next`.
- Deferred:
  - `Header w/nav` and `Footer w/nav`, because global chrome belongs to
    template parts;
  - `Mary Barbour TEMPLATE PAGE -- June2023`,
    because they are page/template-scale structures rather than clean starter
    patterns.

2026-06-25 Individual Activist template conversion:

- Added assignable custom page template
  `templates/page-activist.html` / `Activist / Herstory Page`.
- Added `PNS Herstories` section patterns:
  - `pns/activist-hero`;
  - `pns/activist-text-media`;
  - `pns/activist-facts`;
  - `pns/activist-image-strip`.
- The section patterns avoid plugin-owned blocks from the original giant
  BlockMeister source and use editable core block placeholders.
- Removed `/individual-activist-page` from the transitional allowlist in code.
- Exported rollback source:
  - `docs/jobs/live-adoption-db-backups/2026-06-25-blockmeister-individual-activist-before-draft.json`
- Unpublished the replaced BlockMeister source by setting
  `blockmeister_pattern #2267` / `Individual Activist Page` to `draft`.

2026-06-25 Two Columns adoption:

- Added `pns/two-columns` / `PNS - Two Columns` as a code-backed theme pattern.
- Replaced the original BlockMeister `jetpack/slideshow` dependency with a core
  image placeholder so the blessed pattern remains dependency-light.
- Exported rollback source:
  - `docs/jobs/live-adoption-db-backups/2026-06-25-blockmeister-two-columns-before-draft.json`
- Unpublished the replaced BlockMeister source by setting
  `blockmeister_pattern #1506` / `Two Columns` to `draft`.

## Phase 5 - Synced Content Governance

DEX gate: `koh2py8u`.

Purpose: keep global-update content global while reducing accidental DB mystery.

Objects:

- `Shop Intro` (`wp_block #1509`)
- `Connect Social` (`wp_block #1494`)
- `Read all about it` (`wp_block #1504`)
- form-related `wp_block` entries

Rules:

- Keep `Shop Intro` synced until there is a better custom block or template
  model, because it has many observed live references.
- Do not convert synced patterns to copied `pns/*` patterns unless losing global
  update behavior is acceptable.
- If synced content becomes structural chrome, prefer template part ownership.
- If synced content needs strict fields, consider a custom project block.

Validation:

- Count `core/block` references before editing.
- Confirm edits update all intended references.
- Run visual checks on all routes that reference the synced block.

2026-06-25 scoped warning cleanup:

- DEX child `d5g7pbaj` (`TC5a`) was created under `TC5` after the TC3 route
  showed a visible PHP warning on `/store-block-test/`.
- The warning was present before the TC3 quote-pattern replacement.
- Backtrace showed the source was Ecwid rendering synced block `#3816`
  (`Shop Intro (Using Ecwid Blocks)`), not the `pns/blockquote-with-red-line`
  pattern.
- Reference count for `#3816` was limited to published page `#3228`
  (`Store BLOCK TEST`) plus revisions.
- Exported the synced block before and after cleanup:
  - `docs/jobs/live-adoption-db-backups/2026-06-25-wp-block-3816-shop-intro-ecwid-before-warning-cleanup.json`
  - `docs/jobs/live-adoption-db-backups/2026-06-25-wp-block-3816-shop-intro-ecwid-after-warning-cleanup.json`
- Removed one non-Ecwid `animationsForBlocks` array attribute from an
  `ecwid/product-block`; Ecwid's shortcode path was passing that array to
  `esc_attr()`, causing `Array to string conversion`.
- Added a Playwright guard so `/store-block-test/` fails if PHP warning text is
  rendered into the page again.

2026-06-25 synced-content closeout:

- DEX task `koh2py8u` is complete.
- `Shop Intro` (`wp_block #1509`) remains synced because it still serves the
  expected global-update behavior across the live routes.
- Current live `wp_block` reference counts are:
  - `#1509` / `Shop Intro`: 12;
  - `#1494` / `Connect Social`: 2;
  - `#1504` / `Read all about it`: 3;
  - `#3816` / `Shop Intro (Using Ecwid Blocks)`: 1;
  - `#4654` / `Contact Form (original) (Copy)`: 0.
- The synced-content gate is satisfied without mutating `#1509`, and future
  replacement work can be scheduled only when a better owner is proven.

## Phase 6 - Plugin Dependency Reduction

DEX gate: `roi4jyzh`.

Purpose: separate plugin-owned behavior from site-owned structure.

Do not remove a plugin just because a pattern was extracted. First prove that
no live content depends on its block types or runtime behavior.

Plugin-specific stance:

| Plugin/block family     | Current stance                                                                                                              |
| ----------------------- | --------------------------------------------------------------------------------------------------------------------------- |
| BlockMeister            | Gradually retire as canonical source for stable patterns; keep until editor workflow no longer needs plugin-managed copies. |
| Ecwid                   | Keep plugin-owned behavior; theme owns visible styling and wrappers.                                                        |
| Jetpack slideshow/forms | Treat as plugin dependency; replace only after choosing core/custom alternatives.                                           |
| Dynamic year block      | Footer dependency; consider replacing with theme-rendered dynamic year later if reducing plugin surface matters.            |
| Animations for Blocks   | Audit usage separately before removing; some pattern markup still carries `animationsForBlocks` attrs.                      |

2026-06-25 plugin-dependency closeout:

- DEX task `roi4jyzh` is complete.
- No plugin was disabled or removed because the live dependency map still
  includes BlockMeister pattern management, Ecwid storefront/product blocks,
  Jetpack blocks, the footer dynamic year, and pattern markup that still carries
  animation metadata.
- The dependency audit is now explicit in this plan and in the custom blocks /
  patterns audit, so any later plugin reduction is a separate follow-up task,
  not an unresolved gap in this plan.

## Phase 7 - Deprecation And Cleanup

DEX gate: `56yumbi8`.

Purpose: make the editor UI less confusing after replacements are proven.

Steps:

1. Export BlockMeister source posts.
2. Rename replaced BlockMeister patterns with a `Legacy` prefix.
3. Observe editor workflow for one iteration.
4. Remove or unpublish legacy patterns only after:
   - code-backed replacements are verified;
   - editors know the new `PNS` categories;
   - no planned workflow still depends on BlockMeister.

Do not:

- Delete BlockMeister data without export.
- Disable BlockMeister while active editor workflows still rely on its UI.
- Assume existing pages change when a pattern source changes.

2026-06-25 first legacy-title cleanup:

- DEX child `t1mp6nsf` (`TC7a`) was created after the editor showed both the
  code-backed `pns/*` patterns and the original BlockMeister patterns with
  similar names.
- Exported the three extracted BlockMeister records before and after title
  updates:
  - `docs/jobs/live-adoption-db-backups/2026-06-25-blockmeister-extracted-patterns-before-legacy-title-prefix.json`
  - `docs/jobs/live-adoption-db-backups/2026-06-25-blockmeister-extracted-patterns-after-legacy-title-prefix.json`
- Updated only `post_title` for:
  - `blockmeister_pattern #1445` to
    `Legacy BlockMeister - Welcome Header`;
  - `blockmeister_pattern #1452` to
    `Legacy BlockMeister - Blockquote Cover`;
  - `blockmeister_pattern #2611` to
    `Legacy BlockMeister - Blockquote with red line`.
- Kept BlockMeister post slugs, status, and content unchanged.
- Runtime registry now shows `PNS - ...` theme patterns beside
  `Legacy BlockMeister - ...` plugin-managed patterns.

Rollback path:

- Restore the original `post_title` values from
  `docs/jobs/live-adoption-db-backups/2026-06-25-blockmeister-extracted-patterns-before-legacy-title-prefix.json`.

2026-06-25 duplicate unpublish cleanup:

- DEX child `cor1jyfj` (`TC7b`) was created after confirming existing pages
  render copied block markup rather than live references to the BlockMeister
  pattern source posts.
- Exported the three extracted BlockMeister records before and after
  unpublishing:
  - `docs/jobs/live-adoption-db-backups/2026-06-25-blockmeister-extracted-patterns-before-unpublish.json`
  - `docs/jobs/live-adoption-db-backups/2026-06-25-blockmeister-extracted-patterns-after-unpublish.json`
- Updated only `post_status` from `publish` to `draft` for:
  - `blockmeister_pattern #1445`
    `Legacy BlockMeister - Welcome Header`;
  - `blockmeister_pattern #1452`
    `Legacy BlockMeister - Blockquote Cover`;
  - `blockmeister_pattern #2611`
    `Legacy BlockMeister - Blockquote with red line`.
- Kept slugs, titles, and content unchanged.
- Runtime registry now shows only the code-backed replacements:
  - `pns/welcome-header` / `PNS - Welcome Header`;
  - `pns/blockquote-cover` / `PNS - Blockquote Cover`;
  - `pns/blockquote-with-red-line` /
    `PNS - Blockquote With Red Line`.
- Smoke checks:
  - `/store-block-test/` returned `200`;
  - `/pns-pattern-qa/` returned `200`;
  - all three `pns/*` replacements remained registered.

Rollback path:

- Restore `post_status` to `publish` for `#1445`, `#1452`, and `#2611`, or
  restore from
  `docs/jobs/live-adoption-db-backups/2026-06-25-blockmeister-extracted-patterns-before-unpublish.json`.

2026-06-25 blessed editor pattern library cleanup:

- DEX child `9jw4xqia` (`TC7c`) was created after the editor inserter showed
  too much generic pattern noise and an unclear split between `My patterns`,
  BlockMeister patterns, core/plugin starter patterns, and PNS theme patterns.
- Exported the remaining published BlockMeister records before and after the
  initial draft pass:
  - `docs/jobs/live-adoption-db-backups/2026-06-25-blockmeister-remaining-before-library-cleanup.json`
  - `docs/jobs/live-adoption-db-backups/2026-06-25-blockmeister-remaining-after-library-cleanup.json`
- Corrected that pass after review and exported the corrected state:
  - `docs/jobs/live-adoption-db-backups/2026-06-25-blockmeister-corrected-library-state.json`
- Exported current `wp_block` synced pattern records for inventory/reference:
  - `docs/jobs/live-adoption-db-backups/2026-06-25-wp-block-my-patterns-library-inventory.json`
- Updated the standalone theme pattern registry cleanup so the editor inserter
  exposes the blessed code-backed pattern set:
  - `pns/welcome-header`;
  - `pns/blockquote-cover`;
  - `pns/blockquote-with-red-line`;
  - `pns/basic-centred-content`;
  - `pns/suffragette-stats`;
  - `pns/previous-next`;
  - `pns/two-columns`;
  - `pns/activist-hero`;
  - `pns/activist-text-media`;
  - `pns/activist-facts`;
  - `pns/activist-image-strip`.
- Restored a transitional allowlist for BlockMeister patterns that have not
  yet been adopted into code or explicitly retired:
  - `/mary-barbour-template-page-june2023`;
  - `/footer-w-nav`;
  - `/header-w-nav`.
- Updated the standalone theme category cleanup so registered pattern
  categories are limited to:
  - `pns-layout` / `PNS Layouts`;
  - `pns-quotes` / `PNS Quotes`;
  - `default` / `Protests and Suffragettes`, used only for the unresolved
    BlockMeister transition items.
- Kept the adopted BlockMeister duplicates in `draft`:
  - `blockmeister_pattern #1445` / `Legacy BlockMeister - Welcome Header`;
  - `blockmeister_pattern #1451` / `Basic Centred Content`;
  - `blockmeister_pattern #1452` / `Legacy BlockMeister - Blockquote Cover`;
  - `blockmeister_pattern #2611` /
    `Legacy BlockMeister - Blockquote with red line`;
  - `blockmeister_pattern #2960` / `Suffragette Stats`;
  - `blockmeister_pattern #2965` / `Previous Next`.
- Restored the unresolved BlockMeister sources to `publish` until each has a
  deliberate adopt-or-retain decision:
  - `blockmeister_pattern #3227` / `Mary Barbour TEMPLATE PAGE -- June2023`;
  - `blockmeister_pattern #2850` / `Footer w/nav`;
  - `blockmeister_pattern #2848` / `Header w/nav`.
- The `My patterns` inserter group remains expected. Those entries are native
  synced patterns stored as `wp_block` posts, not registered starter patterns.
  They are currently DB/content-owned and should be governed by Phase 5 before
  any migration, consolidation, or retirement work.

Runtime verification after cleanup:

```text
patterns
pns/welcome-header | PNS - Welcome Header | pns-layout
pns/blockquote-cover | PNS - Blockquote Cover | pns-quotes
pns/blockquote-with-red-line | PNS - Blockquote With Red Line | pns-quotes
pns/basic-centred-content | PNS - Basic Centred Content | pns-layout
pns/suffragette-stats | PNS - Suffragette Stats | pns-layout
pns/previous-next | PNS - Previous Next | pns-layout
pns/two-columns | PNS - Two Columns | pns-layout
pns/activist-hero | PNS - Activist Hero | pns-herstories
pns/activist-text-media | PNS - Activist Text and Media | pns-herstories
pns/activist-facts | PNS - Activist Facts | pns-herstories
pns/activist-image-strip | PNS - Activist Image Strip | pns-herstories
/mary-barbour-template-page-june2023 | Mary Barbour TEMPLATE PAGE -- June2023 | default
/footer-w-nav | Footer w/nav | default
/header-w-nav | Header w/nav | default
categories
pns-layout | PNS Layouts
pns-quotes | PNS Quotes
pns-herstories | PNS Herstories
default | Protests and Suffragettes
published_blockmeister
2848 | Header w/nav | publish
2850 | Footer w/nav | publish
3227 | Mary Barbour TEMPLATE PAGE -- June2023 | publish
wp_block_my_patterns
1493 | Contact Form | publish
1494 | Connect Social | publish
1504 | Read all about it | publish
1509 | Shop Intro | publish
3816 | Shop Intro (Using Ecwid Blocks) | publish
4654 | Contact Form (original) (Copy) | publish
```

Rollback path:

- Remove one of the transitional BlockMeister slugs from
  `pns_standalone_get_transitional_blockmeister_patterns()` only after that
  source has either been adopted into code or deliberately retired.
- Remove or relax `pns_standalone_enforce_blessed_pattern_library()` if the
  project later decides to expose specific core/plugin patterns in addition to
  the PNS and transitional BlockMeister sets.

2026-06-25 extraction closeout:

- DEX task `56yumbi8` is complete.
- All child tasks under `3w25cbsg` are now complete.
- The extraction plan is fully closed: inventory, chrome, pattern QA, live
  adoption, synced-content governance, plugin-dependency audit, and legacy
  cleanup all have final evidence in this plan and the related audit note.

## Validation Gates

Every extraction slice must include the relevant gates below.

Code checks:

```bash
php -l app/public/wp-content/themes/protestsandsuffragettes-standalone/functions.php
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
git diff --check
```

WordPress runtime checks:

```bash
wp option get stylesheet
wp post list --post_type=wp_template,wp_template_part,wp_navigation,wp_block,blockmeister_pattern --post_status=any --fields=ID,post_type,post_name,post_title,post_status,post_modified --format=table
wp eval '$patterns = WP_Block_Patterns_Registry::get_instance()->get_all_registered(); foreach ( $patterns as $pattern ) { $name = $pattern["name"] ?? ""; if ( 0 === strpos( $name, "pns/" ) ) { echo $name . "\n"; } }'
```

Visual checks:

- Focused route screenshots for any page whose content changes.
- Full visual suite when global chrome, shared CSS, templates, or nav behavior
  changes.
- Mobile checks whenever header, banner, nav, or footer changes.

Editor checks:

- Pattern appears in the expected category.
- Editor can insert it without missing assets.
- For `wp_navigation`, editor can change label/URL/order and frontend updates.
- For synced content, editing once updates all references.

Documentation checks:

- Update `docs/2026-06-22-custom-blocks-patterns-audit.md`.
- Update this plan with completed slices.
- Update navigation/banner/footer plans when global chrome changes.
