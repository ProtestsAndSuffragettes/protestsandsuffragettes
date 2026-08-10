# Previous Next Native Pattern Extraction Plan

Plan started on 2026-07-06.

All paths are relative to the project root.

## Purpose

Retire the custom `pns/previous-next` block as an implementation dependency.
The current block solved two different problems:

- archive/search pagination visual treatment
- single post and Herstory previous/back/next navigation

Those should not stay bundled together. Query pagination should use native
WordPress Query Pagination blocks so URL behavior, inherited query state, and
search parameters remain core-owned. Single-entry navigation should use the
fewest possible block patterns and native dynamic blocks, with custom behavior
kept only where Herstory ordering truly requires it.

The desired end state is:

- no active templates, scaffolds, or current saved content require
  `pns/previous-next`
- the PNS visual treatment survives as theme CSS over native/core block markup
- block patterns own reusable layout composition
- plugin code owns only durable content-model behavior, such as Herstory
  adjacency if native post navigation cannot match it

## Current Ownership

Current `pns/previous-next` ownership is theme-local:

| Surface                                   | Current file                                                                                            |
| ----------------------------------------- | ------------------------------------------------------------------------------------------------------- |
| PHP block registration                    | `app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/assets.php`                        |
| Editor block and variation                | `app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/editor-blocks.js`              |
| Dynamic render and pagination URL helpers | `app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/block-filters.php`                 |
| Herstory new-entry scaffold insertion     | `app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/herstories.php`                    |
| Theme visual styling                      | `styles/components/buttons.css`, `styles/components/section-theme.css`, and supporting layout utilities |

`pns-blocks` does not currently define this block. `pns-herstories` owns the
Herstory CPT and query helpers; the standalone theme currently consumes
`\PNS\Herstories\Queries::adjacent()` when rendering Herstory previous/next
links.

## Source Usage Baseline

Filesystem usages found at plan time:

| Context                     | Current source                    |
| --------------------------- | --------------------------------- |
| Search results pagination   | `templates/search.html`           |
| News/blog home pagination   | `templates/home.html`             |
| Herstory archive pagination | `templates/archive-herstory.html` |
| Single post navigation      | `templates/single.html`           |
| New Herstory CPT scaffold   | `inc/herstories.php`              |

`templates/archive.html` already uses native `core/query-pagination`; use it as
a reference for the native direction, not as a final visual model.

No direct `pns/previous-next` source-file usages were found in active
`patterns/`, `parts/`, `synced-patterns/`, or PNS plugin source.

## Saved Content Baseline

WP-CLI DB audit on 2026-07-06 found current non-revision content using
`wp:pns/previous-next` in these active surfaces:

- published Herstory CPTs: `5835`, `5902`, `5903`, `5904`, `5905`, `5906`
- published page: `42` Mary Barbour
- draft legacy/fixture pages: `1828`, `1833`, `1848`, `1855`, `1869`, `2874`,
  `3819`, `5421`
- published Pattern QA page: `5265`
- saved templates: `1029` Home, `5990` Single Posts, `6138` PNS - Herstory
  Archive, `6186` Blog Home, `6221` Search Results

Revisions also contain historical copies. They are rollback context, not the
primary migration target, unless a later implementation phase deliberately
chooses to clean revisions.

## Locked Decisions

These decisions are accepted for implementation and should not be reopened
unless implementation evidence proves they are impossible:

- Query pagination uses native `core/query-pagination` blocks. The custom
  `pns/previous-next` pagination mode is retired rather than moved into
  `pns-blocks`.
- Native query pagination receives the PNS visual treatment through an explicit
  theme CSS hook, preferably `pns-query-pagination`. Do not style every
  `.wp-block-query-pagination` globally, and do not keep `pns-previous-next` as
  the pagination styling name.
- Use one non-synced theme-owned navigation pattern for the previous/back/next
  layout wherever possible. The pattern should be reusable, inserted into
  templates/content as needed, and not stored as a synced reusable block.
- Herstories should use that same pattern if possible, with the smallest
  Herstories-local adjustment needed for the center Back to Herstories link or
  adjacency behavior. Only add a Herstory-specific bridge if native post
  navigation cannot match the current editorial order.
- Migrate current non-revision content and saved `wp_template` rows only. Leave
  revisions as rollback history unless they actively break editor workflows.
- Pattern QA should show the supported native pagination/pattern examples, not
  preserve `pns/previous-next` as a legacy fixture.
- If a dynamic bridge is unavoidable, it belongs with the content model:
  Herstory adjacency in `pns-herstories`; query pagination remains native; theme
  owns CSS and pattern markup.

## Target Model

### Query Pagination

Use native blocks:

- `core/query-pagination`
- `core/query-pagination-previous`
- `core/query-pagination-numbers`
- `core/query-pagination-next`

Apply PNS visual treatment with theme-owned CSS on an explicit wrapper or block
class, preferably `pns-query-pagination`, applied to the native pagination
block. The key rule is that URLs stay core-owned.

Acceptance examples:

- `/?s=Protest` page number `2` points to `/page/2/?s=Protest`
- `/?s=Protest` Next points to `/page/2/?s=Protest`
- archive query-id pagination continues to use the correct native query state

### Single Post Navigation

Create one non-synced theme-owned pattern built from native blocks:

- previous post navigation link
- centered archive/back link, such as Back to News or Back to Herstories
- next post navigation link

The pattern should carry only the classes needed for PNS theme styling. It
should not register a custom dynamic block and should not be stored as a synced
reusable block.

### Herstory Navigation

Use the same non-synced previous/back/next pattern if Herstories can be handled
with native post navigation plus local label/link changes. If native ordering
cannot match the current editorial order, keep the custom behavior as small as
possible:

- `pns-herstories` owns the adjacency/order API
- the theme owns only pattern markup, local Herstory pattern adjustment, and CSS
- any dynamic bridge should be Herstory-specific, not a generic replacement for
  `pns/previous-next`

The current `inc/herstories.php` scaffold must also be updated so new Herstory
entries no longer receive `pns/previous-next`.

## Guardrails

- Do not unregister `pns/previous-next` until current saved content and saved
  templates are migrated or known to be irrelevant.
- Do not migrate saved content without a timestamped DB backup under
  `docs/jobs/previous-next-db-backups/`.
- Use parsed-block migration rather than raw string replacement for DB content.
- Keep archive/search/store concerns separate. This plan owns editorial
  pagination and post/Herstory navigation only.
- Preserve current visual output unless a specific drift is reviewed and
  accepted.
- Prefer one non-synced native-pattern solution overall. Do not create many
  near-copy patterns if one pattern plus a small Herstories-local variation is
  enough.

## Dex Tracking

Dex task state is stored under:

```text
app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex/tasks.jsonl
```

Parent task:

```text
2z2bjurx - Retire custom Previous Next block in favor of native blocks and theme styles
```

Phase tasks:

| Phase | Dex ID     | Task                                                                     |
| ----- | ---------- | ------------------------------------------------------------------------ |
| 0     | `6x1u4lq2` | Previous Next Phase 0 - freeze inventory and migration baseline          |
| 1     | `j1b9904t` | Previous Next Phase 1 - style native query pagination as PNS controls    |
| 2     | `n0a2vj50` | Previous Next Phase 2 - replace single navigation with native pattern    |
| 3     | `qgrhpqit` | Previous Next Phase 3 - resolve Herstory navigation without theme block  |
| 4     | `q1jke7w2` | Previous Next Phase 4 - migrate saved content and template overrides     |
| 5     | `qs6amt0m` | Previous Next Phase 5 - remove custom block registration and render code |
| 6     | `w2sqg60c` | Previous Next Phase 6 - validate native navigation and visual parity     |

## Implementation Phases

### Phase 0 - Freeze Inventory And Migration Baseline

Confirm the baseline before edits:

- source search for `pns/previous-next`, `wp-block-pns-previous-next`,
  `pnsMode`, and `pns-previous-next`
- DB search for current non-revision saved content and `wp_template` records
- route coverage list for search, news archive, Herstory archive, single post,
  published Herstory, and Pattern QA
- backup command shape and rollback path

Done when the implementation task records exact owners and test routes.

### Phase 1 - Style Native Query Pagination As PNS Controls

Replace pagination-mode usages in filesystem templates with native
`core/query-pagination` markup:

- `templates/search.html`
- `templates/home.html`
- `templates/archive-herstory.html`

Move the PNS visual treatment onto native pagination markup via theme CSS.
Avoid reimplementing pagination URL logic.

Done when search and archive pagination use native URLs and still render the
accepted PNS control layout.

### Phase 2 - Replace Single Navigation With Native Pattern

Create one non-synced pattern for:

- previous post link
- Back to News or Back to Herstories link
- next post link

Apply it to filesystem `templates/single.html` and saved Single Posts template
override if present.

Done when single posts no longer depend on `pns/previous-next`.

### Phase 3 - Resolve Herstory Navigation Without Theme Block

Audit whether native `core/post-navigation-link` can match Herstory ordering.
If it can, reuse the same previous/back/next pattern with the smallest local
Herstory adjustment for Back to Herstories.

If it cannot, isolate the custom bit:

- keep Herstory order/adjacency in `pns-herstories`
- avoid a generic `pns/previous-next` block
- use the smallest Herstory-specific bridge needed for the pattern/template

Update the Herstory new-entry scaffold in `inc/herstories.php`.

Done when published Herstories and newly scaffolded Herstories no longer need
the retired block.

### Phase 4 - Migrate Saved Content And Template Overrides

Write a backup-backed migration for current non-revision records containing
`wp:pns/previous-next`.

Convert by context:

- query/archive/search templates -> native query pagination
- single post template/content -> single navigation pattern/native blocks
- Herstory content/drafts -> Herstory pattern/native or approved minimal bridge
- Pattern QA -> representative native/pattern fixtures

Done when dry-run is idempotent and current non-revision DB search finds no
active `wp:pns/previous-next` blocks.

### Phase 5 - Remove Custom Block Registration And Render Code

After Phase 4 is clean:

- remove `pns/previous-next` PHP registration from `inc/assets.php`
- remove editor block registration and variation from `scripts/editor-blocks.js`
- remove `render_block_pns/previous-next` and related helpers from
  `inc/block-filters.php`
- remove the animation unsupported-block entry for the retired block
- remove tests that assert the retired custom block as desired behavior

Done when active source search has no implementation dependency on
`pns/previous-next`.

### Phase 6 - Validate Native Navigation And Visual Parity

Minimum checks:

- `php -l` on changed PHP files
- block-template validation for changed templates
- CSS compile/lint when CSS changes land
- DB usage search proving no current non-revision `wp:pns/previous-next`
- focused Playwright coverage for:
  - `/search/`
  - `/?s=Protest`
  - `/news/` and page 2
  - `/herstories/` and page 2 if present
  - a representative single post
  - `/herstories/mary-barbour/`
  - Pattern QA

Run the full visual suite as the landing gate if CSS/template changes are broad.

Done when Dex records exact command evidence and any accepted visual drift.
