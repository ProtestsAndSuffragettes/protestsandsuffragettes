# Search Experience Plan

## Superseded Direction

As of 2026-07-03, the custom `/find/` route and `pns/editorial-search` block
are being retired. Native WordPress search is the source of truth: use
`templates/search.html`, the core Search block, and the theme's native search
query filter for editorial post-type scope.

## Purpose

Add a dedicated site-search experience for editorial content in the live
`protestsandsuffragettes-standalone` theme. The search flow should be easy to
discover from navigation, use native WordPress search as the baseline contract,
exclude store/catalog data for now, and stay ready for a future editorial
custom post type whether that content type is created by plugin or code.

## Current Baseline

- The active local theme is `protestsandsuffragettes-standalone`.
- The live theme already has a filesystem search template at
  `templates/search.html`.
- The current search UI is effectively a news search:
  - the search block placeholder is `Search news`
  - the query loop is hard-coded to `post`
- The current header template part exposes the site logo and primary navigation
  but no obvious search entry point.
- Store/catalog content is powered separately and should remain out of native
  site search in this phase.

## Current Implementation Constraints

- Keep this plan scoped to search UX, entry points, and future CPT readiness.
- Do not expand scope into store/Ecwid search.
- Do not assume the future editorial custom post type exists yet.
- Wire the primary navigation last, after concurrent visual-diff-sensitive work
  is clear.
- Prefer a normal WordPress GET-based search flow as the baseline so result
  URLs remain shareable, crawlable, and accessible.
- Progressive enhancement is allowed, but a JavaScript-only SPA is not required
  for v1.

## Current Implementation Notes

- The public search landing route is `/find/`, not a literal page slug of
  `/search/`.
- The baseline user flow is:
  1. Enter search from the `/find/` route. Navigation wiring is deferred.
  2. Land on a dedicated search screen with a prominent field.
  3. Submit a native WordPress search query.
  4. View thumbnail-forward results below the field.
- The v1 entry point label is `Search` in the primary navigation.
- The v1 interaction model is a dedicated landing page with server-rendered
  results below the field after submit. Live-updating search is deferred.
- The v1 editorial scope is `post` and `page`.
- The results contract should be ready to include `post`, `page`, and a future
  editorial CPT without redesigning the result card model.
- The v1 result card format is thumbnail, title, excerpt, and content-type
  label, with date shown only for time-based editorial types such as posts.
- The v1 pagination model is standard pagination, not load-more or infinite
  scroll.
- The future CPT may be registered either by a premium plugin or by project
  code; search should target the resulting public post type slug rather than
  care how it was created.

## Locked Decisions

- Search covers editorial content only, not store/catalog surfaces.
- The dedicated public landing route is `/find/`.
- The primary navigation label is `Search`.
- The v1 search flow is submit-driven and server-rendered, with results below
  the field on the dedicated search screen.
- The v1 query scope is `post` and `page`.
- The implementation should remain ready to include one future editorial custom
  post type without redesigning the search experience.
- The v1 result card format is thumbnail, title, excerpt, and content-type
  label.
- Dates should display only for time-based editorial types.
- The v1 empty/default experience should show intro copy before search and
  explicit no-results copy after search.
- The v1 pagination model is standard pagination.
- Progressive enhancement can come later, but live search is not part of the
  initial implementation baseline.

## Dex Tracking

Dex task state for this rollout is stored under the standalone theme root:

```text
app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex/tasks.jsonl
```

Use the tracker with the storage path explicitly:

```bash
dex --storage-path /Users/anachronistic/Local\ Sites/pns-stageing/app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex list --all
```

Parent task:

```text
fu4nbfp4 - Add dedicated editorial search experience
```

Phase tasks:

| Phase | Dex ID     | Task                                                     |
| ----- | ---------- | -------------------------------------------------------- |
| 0     | `iy3wmehf` | Confirm search scope, route, and future CPT contract     |
| 1     | `1fw5n3c9` | Add a clear search entry point in site navigation        |
| 2     | `k1gu53kh` | Build the dedicated search landing experience            |
| 3     | `7em8d1y4` | Broaden native search query scope for editorial content  |
| 4     | `dwz3j6vb` | Add mixed-type result cards and empty states             |
| 5     | `vv3btp5s` | Add progressive enhancement without breaking native flow |
| 6     | `aza5509t` | Validate UX, accessibility, and regression coverage      |

## Phase 0 - Confirm Scope, Route, And Future CPT Contract

Status: complete.

Locked decisions:

- Search covers editorial content only, not store/catalog surfaces.
- Search includes `post` and `page` at launch.
- Search remains ready to include one future editorial CPT.
- The dedicated public search route is `/find/`.
- The v1 interaction model is submit-driven and server-rendered.
- The future CPT assumption is documented without committing to a CPT
  implementation path yet.

## Phase 1 - Add A Clear Search Entry Point In Site Navigation

Status: deferred until the route and search page are complete, and until
concurrent visual-diff-sensitive work can tolerate navigation changes.

Make search discoverable before tuning the results experience.

Implementation notes:

- Add a `Search` nav item or equivalent header affordance that routes to the
  dedicated search landing page.
- Keep the interaction simple and legible on desktop and mobile.
- Prefer a stable, content-style entry point over a modal-only interaction.

Acceptance checks:

- Search is visible from primary navigation.
- The entry point works on desktop and mobile layouts.
- The route name and label match the agreed user-facing language.

## Phase 2 - Build The Dedicated Search Landing Experience

Status: complete for the `/find/` route slice.

Create the main search screen that users reach from the nav.

Implementation notes:

- Published local WordPress page `5526` owns the `/find/` route.
- `templates/page-find.html` renders the route-specific block template.
- The route uses the `pns/editorial-search` dynamic block so search results
  render server-side without shortcode paragraph artifacts.

Template requirements:

- Prominent search field at the top of the page.
- Search prompt/help text that explains what is covered.
- Results render below the field after submit.
- Empty initial and empty-result states are intentional, not broken-looking.
- The experience works without JavaScript.

Acceptance checks:

- The landing route renders cleanly with no query.
- Submitting a search produces results on the same screen or search route.
- The layout supports thumbnails and mixed content types.

## Phase 3 - Broaden Native Search Query Scope For Editorial Content

Status: complete for the `/find/` route slice.

Align the query contract with the agreed editorial scope and future CPT
readiness.

Implementation notes:

- Replace the current `post`-only assumption with an editorial content list
  such as `post`, `page`, and a future CPT slug when that exists.
- `inc/search.php` defaults the editorial search post-type list to `post` and
  `page`.
- The future CPT insertion point is the
  `pns_standalone_editorial_search_post_types` filter.
- Use main-query search hooks or equivalent WordPress-native query controls.
- Keep the store/catalog system out of this query contract.

Acceptance checks:

- Search can include posts and pages immediately.
- The implementation has a clear insertion point for a future CPT.
- No shop/catalog results leak into the editorial search experience.

## Phase 4 - Add Mixed-Type Result Cards And Empty States

Status: complete for the `/find/` route slice.

Give results a consistent, useful presentation across current and future
editorial types.

Implementation notes:

- Result cards render thumbnail or no-thumbnail media, title link, content-type
  label, excerpt, and date only for time-based editorial types.
- `styles/components/search.css` owns the scoped `.pns-search-*` presentation.
- Focused Playwright assertions cover initial, results, and no-results states
  without adding or refreshing visual snapshots.

Result card requirements:

- Thumbnail when available.
- Title linked to the item.
- Content-type label where useful.
- Short excerpt or summary.
- Graceful behavior when no thumbnail is present.

Acceptance checks:

- Results remain readable with posts, pages, and future CPT items.
- Empty-result copy is specific and helpful.
- The layout does not assume every result is a news post.

## Phase 5 - Add Progressive Enhancement Without Breaking Native Flow

Only after the server-rendered flow is solid, consider lightweight enhancement.

Enhancement candidates:

- Update results in place after submit.
- Optional delayed live search after typing.
- Content-type chips or filters if the editorial surface grows.

Guardrails:

- Preserve the plain GET-based search contract.
- Do not require JavaScript for core search usage.
- Do not build a separate custom search backend for v1.

Acceptance checks:

- Native search URLs still work directly.
- Progressive enhancement does not break keyboard or screen-reader flow.
- The enhanced experience degrades cleanly when scripts fail.

## Phase 6 - Validate UX, Accessibility, And Regression Coverage

Finish with evidence, not assumption.

Validation scope:

- Header/nav route access on desktop and mobile.
- Search landing page with no query.
- Search with matching results.
- Search with no results.
- Result cards with and without thumbnails.
- Accessibility pass on labels, focus order, and submit behavior.
- Visual regression coverage for the new search surfaces.

Acceptance checks:

- Browser validation covers desktop and mobile behavior.
- Any Playwright or local-browser blockers are documented if they occur.
- The plan and Dex task results capture any admin/database steps or future CPT
  follow-up decisions.
