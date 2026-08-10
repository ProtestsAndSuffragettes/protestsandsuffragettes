# PNS Herstories Plugin And CPT Migration Plan

Plan started on 2026-06-27.

All paths are relative to the project root.

## Goal

Create a dedicated `pns-herstories` WordPress plugin that owns the Herstories
content model and migration mechanics, while the active theme owns the final
templates and CSS.

The current page-based Mary Barbour setup remains page-owned until cutover. The
target system is a `herstory` custom post type with dedicated taxonomies,
deterministic previous/next navigation, archive behavior, and a migration path
from the existing page tree.

## Closeout - 2026-07-10

This rollout is closed in the standalone tracker.

- The `pns-herstories` plugin is active and owns the `herstory` CPT.
- Six migrated Herstory CPT records are published.
- The public namespace has been cut over to `/herstories/`.
- The legacy static `/herstories/` page family has been drafted.
- Primary and footer navigation now link to the Herstories archive.
- Route checks and focused visual contracts were refreshed under completed
  subtasks `lsxmjb8y` and `yq24c2v0`.

The plan below remains historical implementation context.

## Ownership Decision

Use a custom plugin for Herstories functionality.

This is a deliberate split:

- The plugin owns the `herstory` post type, taxonomies, ordering rules, default
  editor template, permalink behavior, and migration helpers.
- The theme owns the rendered templates, responsive layout, CSS, and visual
  regressions.

Do not place the CPT registration in the theme. The Herstories content model
needs to survive theme changes and remain portable across future presentation
work.

## Execution Model

This rollout should be driven through DEX and coordinated by an orchestration
agent rather than executed as a single-threaded hand edit.

Rules:

- DEX is the source of progress tracking for each phase and subtask.
- The orchestration agent owns synthesis, sequencing, and final decisions.
- Use subagents for bounded inspection, validation, and narrow implementation
  work when they materially reduce context load or parallelize independent
  tasks.
- Prefer `gpt-5.4-mini` for small focused inspection or verification tasks.
- Prefer `gpt-5.3-codex-spark` for very narrow code-edit or scaffold work.
- Prefer `gpt-5.4` for higher-judgment implementation or review tasks when a
  subagent needs stronger reasoning.
- The orchestration agent should unify the results, resolve conflicts, and own
  the final plan and code direction.

## Current Baseline

- `/herstories/mary-barbour/` currently lives inside a page hierarchy rather
  than a dedicated content type.
- The theme already carries herstories-specific visual and pattern work,
  including the `pns-herstories` pattern category and page-family template
  work.
- There is no project-owned `herstory` CPT, taxonomy, or migration tooling yet.
- The existing `pns-blocks` plugin is a separate project-owned functionality
  plugin and is a useful precedent for splitting behavior from presentation.

## Current Implementation Constraints

- The plugin should own functionality, not final presentation.
- The theme should own final templates, CSS, and responsive layout decisions.
- Do not model Herstories as News categories.
- Do not keep the page tree as the final data model once the CPT is in place.
- Preserve the `/herstories/{slug}/` URL shape as the final cutover target, but
  use `/herstories-archive/{slug}/` for CPT single previews before cutover.
- Keep the plugin narrow and dependency-light; it should register the content
  model, taxonomies, ordering rules, and migration helpers, not broad theme
  styling.
- Keep the temporary route split strict: `/herstories/*` is page-owned while
  `/herstories-archive/*` is CPT-owned.

## Current Implementation Notes

- Existing theme work already proves the visual language for Herstories while
  the CPT data model is prepared separately.
- The plugin should be a normal project-owned plugin at
  `app/public/wp-content/plugins/pns-herstories/`.
- Theme templates should consume the plugin-driven data model rather than own
  the model themselves.
- The plugin should not be merged into `pns-blocks`; that plugin is for custom
  blocks, not Herstories content ownership.

## Editor UI

The WordPress editor experience for `herstory` should feel like a normal custom
post type, not a page clone or a bespoke app.

Expected editor-facing behavior:

- A left-nav `Herstories` admin section under `wp-admin`.
- A standard post editor screen with title, content, excerpt, featured image,
  slug, and revisions.
- Taxonomy panels for `herstory_tag` and any later Herstory taxonomies.
- Optional custom fields / meta panels for structured data such as sequence,
  date range, location, and attribution.
- List-table columns that help editors see order, tags, and publication state at
  a glance.
- A block-editor content area that keeps the current theme-owned visual language
  while the plugin owns the data model behind it.
- A default block template for new `herstory` posts so every new entry starts
  from the same original layout in the editor.

Out of scope for v1:

- A custom React admin app.
- A frontend-only content editor.
- A no-code template builder inside the plugin.

## Ownership Model

| Layer                  | Owner                                 | Notes                                                                                            |
| ---------------------- | ------------------------------------- | ------------------------------------------------------------------------------------------------ |
| Content model          | `pns-herstories` plugin               | Registers the CPT, taxonomies, ordering, permalink rules, admin metadata, and migration helpers. |
| Front-end presentation | Active PNS theme                      | Owns templates, CSS, responsive layout, and visual regression contracts.                         |
| Editorial content      | WordPress posts in the `herstory` CPT | Stores individual Herstory entries and their metadata.                                           |
| Legacy page namespace  | Current page hierarchy                | `/herstories/*` remains page-owned until deliberate cutover.                                     |
| News/blog              | Standard posts                        | Remains separate from Herstories.                                                                |

## Locked Decisions

- Use a separate `pns-herstories` plugin for functionality.
- Keep the CPT out of the theme.
- Use a `herstory` custom post type.
- Keep the `herstories` URL namespace.
- Keep the existing `/herstories/` page as the v1 landing page; do not enable
  the CPT archive at that route until the migration and theme-owned landing
  experience are ready.
- Expose the CPT archive temporarily at `/herstories-archive/` so the
  theme-owned archive template can be built before cutover.
- Expose CPT singles temporarily at `/herstories-archive/{slug}/` so migrated
  entries can be reviewed without colliding with legacy pages.
- Use `herstory_tag` as the first dedicated taxonomy.
- Drive previous/next from ordered `herstory` queries, not manual page links.
- Keep theme CSS and templates outside the plugin.
- Treat the current page-based route as page-owned until deliberate cutover, not
  as a CPT fallback.

## Target Plugin Shape

```text
app/public/wp-content/plugins/pns-herstories/
  pns-herstories.php
  includes/
    PostTypes.php
    Taxonomies.php
    Admin.php
    Permalinks.php
    Queries.php
    Migration.php
  README.md
```

The exact file split can change, but the responsibilities should not:

- `PostTypes.php` registers the CPT.
- `Taxonomies.php` registers tags and optional future hierarchies.
- `Permalinks.php` owns rewrite/permalink rules and URL helpers.
- `Admin.php` owns list-table columns and editor-facing metadata helpers.
- `Queries.php` owns adjacency, archive, and series ordering helpers.
- `Migration.php` owns page-to-CPT backfill and rollback support.

## DEX Tracking

Dex task state for this rollout should live under the project root tracker:

```text
app/public/wp-content/themes/protestsandsuffragettes/.dex/tasks.jsonl
```

Use the storage path explicitly:

```bash
dex --storage-path /Users/anachronistic/Local\ Sites/pns-stageing/app/public/wp-content/themes/protestsandsuffragettes/.dex list --all
```

Parent task:

```text
h3rs0t00 - PNS Herstories plugin and CPT migration
```

Phase tasks:

| Phase | Dex ID     | Task                                                             |
| ----- | ---------- | ---------------------------------------------------------------- |
| 0     | `h3rs0t01` | Confirm scope, route contract, and plugin/theme ownership split  |
| 1     | `h3rs0t02` | Scaffold `pns-herstories` and register the `herstory` CPT        |
| 2     | `h3rs0t03` | Add taxonomies, ordering, permalink helpers, and admin metadata  |
| 3     | `h3rs0t04` | Build theme-owned templates and archive/single shells            |
| 4     | `h3rs0t05` | Add migration tooling and backfill current page content          |
| 5     | `h3rs0t06` | Validate redirects, prev/next, archives, and cut over namespaces |

Execution guidance:

- Keep DEX task results synchronized with the plan phase before moving to the
  next phase.
- Use one orchestration agent per phase or workstream when the phase can be
  split into independent subtasks.
- Let the orchestration agent assign subagents for bounded work, then integrate
  the evidence and close out the phase only after the explicit acceptance
  checks pass.

## Phase 0 - Confirm Scope, Route Contract, And Ownership Split

Decide the exact functional contract before any code lands.

Decisions to lock:

- The plugin name and install path.
- `/herstories/` remains the existing page landing during v1.
- The CPT archive is available only at the temporary `/herstories-archive/`
  preview route until a theme-owned landing/archive experience is deliberately
  cut over.
- Which metadata fields are first-class in v1 versus deferred to custom fields.
- Whether ordering is purely `menu_order` or a separate numeric sort field.

Acceptance checks:

- The plan has a single authoritative route and ownership split.
- The plugin stays functional-only.
- The theme stays presentation-only.
- The migration path is additive and reversible.

## Phase 1 - Scaffold `pns-herstories` And Register The CPT

Create the plugin shell and the core content model.

Implementation notes:

- Add a project-owned plugin directory at `app/public/wp-content/plugins/pns-herstories/`.
- Register the `herstory` post type.
- Put the CPT archive and single entries under `/herstories-archive/` during the
  build phase; do not claim `/herstories/` until cutover.
- Keep the registration code small and explicit.
- Make the plugin load cleanly even if the theme changes later.
- Ensure the editor UI exposes the normal CPT affordances: title, editor,
  excerpt, featured image, taxonomy panels, and permalink editing.
- Define a default block `template` for new `herstory` posts so the first
  edit experience starts with the Herstories scaffold.
- Leave `template_lock` unset or set it intentionally based on how much layout
  freedom editors should have. Default v1 assumption: editable blocks, not a
  locked layout.
- Register any Herstories-specific admin/menu labels inside the plugin rather
  than through theme code.

Acceptance checks:

- `herstory` exists as a public post type.
- The plugin can be activated independently of the theme.
- No presentation CSS is required for the plugin to load.
- New `herstory` posts open with the default block scaffold and remain
  block-editable.

## Phase 2 - Add Taxonomies, Ordering, Permalink Helpers, And Admin Metadata

Make the new content type usable by editors and predictable in queries.

Implementation notes:

- Register `herstory_tag` first.
- Add any future taxonomy only when the content need is clear.
- Add ordering support for sibling and archive navigation.
- Add admin columns and helper metadata only if they improve editorial flow.
- Keep URL helpers in the plugin so the theme can consume CPT URLs without
  duplicating query logic.

Acceptance checks:

- Herstory entries can be tagged.
- The content order is deterministic.
- The admin UI surfaces the important functional metadata.
- Permalink helpers preserve legacy `/herstories/{slug}/` pages while CPT
  preview singles use `/herstories-archive/{slug}/`.

## Phase 3 - Build Theme-Owned Templates And Archive/Single Shells

Move presentation into the active theme while leaving functionality in the
plugin.

Implementation notes:

- Add the Herstories archive, single, and taxonomy templates in the theme.
- Render prev/next, tags, and archive navigation from the plugin-driven model.
- Keep the current visual language and responsive rules in theme CSS.
- Do not put broad CSS or layout logic into the plugin.

Acceptance checks:

- The theme renders single and archive views without hard-coded page ancestry.
- The archive is readable on desktop and mobile.
- The plugin supplies the data contract; the theme supplies the visuals.

## Phase 4 - Add Migration Tooling And Backfill Current Page Content

Move the existing Herstories content into the new model safely.

Implementation notes:

- Export rollback data before mutating records.
- Convert the current Herstories pages into `herstory` entries.
- Preserve slugs so existing URLs remain stable.
- Map structured data into taxonomies or post meta as appropriate.
- Keep `/herstories/*` page URLs untouched until the migration is proven.

Acceptance checks:

- Existing Herstories entries exist as CPT records.
- The old pages are either redirected at cutover or intentionally retained.
- Rollback data is available for the migration batch.

## Phase 5 - Validate Redirects, Prev/Next, Archives, And Cut Over Namespaces

Finish with browser evidence and remove the temporary page model only after the
new model is stable.

Validation scope:

- Direct single-entry URLs.
- Archive and taxonomy routes.
- Previous/next behavior.
- Editor and frontend rendering.
- Mobile and desktop layout checks.
- Any redirect behavior needed for old page URLs.

Acceptance checks:

- The CPT model is the source of truth.
- The page namespace has a deliberate cutover or retention decision.
- The plan and Dex results document any remaining edge cases.
