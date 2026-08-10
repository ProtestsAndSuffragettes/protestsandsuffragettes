# Blog Route Implementation Plan

## Purpose

Add a real blog/news route to the site and make the expected blog interface
complete enough to support normal publishing. The plan assumes the active child
theme remains `protestsandsuffragettes`, with site-specific structure and
styling owned by the child theme instead of relying on inherited eStory demo
templates.

## Current Baseline

- WordPress uses a static front page.
- `page_for_posts` is not configured.
- There are no published posts.
- `/blog/` and `/news/` currently return `404`.
- Search renders, but current results are page-driven rather than blog-driven.
- Category archives render through the inherited parent template, but empty
  archive output lacks a useful empty state.
- The child theme has no blog/index/archive/search/single block templates of
  its own.
- eStory provides inherited archive/search pagination, but single posts do not
  include previous/next post navigation.
- The active header has a `News` submenu, but it currently links only to a
  hiring page rather than to a blog index.

## Current Implementation Constraints

- The canonical blog route is `/news/`.
- Do not update header/footer navigation while the separate CSS audit is in
  progress.
- Do not adjust blog CSS until the CSS audit is complete.
- Blog-specific templates may be added, but broad/global markup changes should
  wait.

## Current Implementation Notes

- Page ID `5190` is the published `News` page.
- `page_for_posts` is set to `5190`.
- The saved database `Home` block template, post ID `1029`, previously overrode
  `templates/home.html` and displayed inherited eStory demo content on
  `/news/`.
- Post ID `1029` has been updated to match the child theme's
  `templates/home.html` content so the posts index renders the intended News
  listing.
- Header and footer navigation were deliberately left unchanged.
- No CSS files were changed for this phase.
- Local fixture content is seeded by:

  ```bash
  wp eval-file app/public/wp-content/themes/protestsandsuffragettes/scripts/seed-news-fixtures.php
  ```

- The fixture script is intentionally smaller than the official WordPress Theme
  Test Data import. It is modeled on that project's edge-case coverage, but it
  avoids importing unrelated pages, menus, media, and demo content.
- Fixture content currently includes 12 published posts, enough to force
  `/news/page/2/`, with categories, tags, manual excerpts, featured-image and
  no-featured-image cases, an open-comments post with nested replies, and a
  closed-comments post with an approved historical comment.

Fixture posts:

| Slug | Purpose |
| --- | --- |
| `fixture-community-update-with-comments` | Open comments and nested replies |
| `fixture-very-long-news-title-that-wraps-across-multiple-lines` | Long title wrapping |
| `fixture-block-coverage-headings-lists-table-buttons` | Common block content |
| `fixture-no-featured-image` | Listing/single behavior without thumbnail |
| `fixture-comments-closed` | Closed comments with an existing comment |
| `fixture-manual-excerpt` | Manual excerpt output |
| `fixture-category-and-tag-links` | Multiple category/tag links |
| `fixture-pagination-01` through `fixture-pagination-05` | Pagination coverage |

## Dex Tracking

Dex task state has been added under the child theme project root:

```text
app/public/wp-content/themes/protestsandsuffragettes/.dex/tasks.jsonl
```

Use the tracker from the child theme root:

```bash
cd /Users/anachronistic/Local\ Sites/pns-stageing/app/public/wp-content/themes/protestsandsuffragettes
dex list --all
```

If a global or local Dex config points somewhere stale, use the storage path
explicitly:

```bash
dex --storage-path /Users/anachronistic/Local\ Sites/pns-stageing/app/public/wp-content/themes/protestsandsuffragettes/.dex list --all
```

Parent task:

```text
4wpjf3d1 - Add blog route and complete blog UI baseline
```

Phase tasks:

| Phase | Dex ID | Task |
| --- | --- | --- |
| 0 | `3xwuwvc5` | Confirm blog route, naming, and content policy |
| 1 | `apig9494` | Configure WordPress blog landing route |
| 2 | `ikxohpv6` | Add child-theme blog templates and parts |
| 3 | `gseojzz1` | Fill blog UI element gaps |
| 4 | `rp8b5oce` | Style blog surfaces in child CSS |
| 5 | `3jcqjste` | Seed, verify, and document blog behavior |

## Phase 0 - Confirm Route And Content Policy

Decision: use `/news/` as the canonical blog route. The site already has a
`News` navigation label, but navigation updates are deferred while the CSS audit
is running.

Decisions to record:

- Canonical route: `/news/`.
- Whether the existing `News` menu becomes the blog index link after the CSS
  audit.
- Whether hiring announcements remain under `News` or move elsewhere.
- Initial category/tag taxonomy shape.
- Whether comments should be open, closed, or selectively enabled.
- Whether the launch needs real posts only, temporary local fixtures, or both.

Done when:

- Route name and menu behavior are agreed.
- Starter content requirements are documented.
- Phase 1 can proceed without guessing editorial intent.

## Phase 1 - Configure WordPress Blog Landing Route

Create or select the Posts page and assign it as `page_for_posts`. Defer
header/footer navigation changes until the CSS audit is complete.

Implementation notes:

- Use WP-CLI for `show_on_front`, `page_on_front`, and `page_for_posts` checks.
- Use elevated local-service access if sandboxed WP-CLI cannot reach Local's
  database.
- Keep database/admin changes documented because they are not represented as
  child-theme files.
- Verify permalink behavior after assignment.

Acceptance checks:

- Chosen route returns `200`.
- Old expected route behavior is intentional: either redirected, left 404, or
  not linked.
- Header/footer navigation updates are either complete or explicitly deferred.
- Rollback notes are recorded.

## Phase 2 - Add Child-Theme Blog Templates And Parts

Add child-owned block templates so blog behavior is not dependent on eStory's
generic demo layout.

Expected child-theme files:

- `templates/home.html` or equivalent posts-index template.
- `templates/archive.html`.
- `templates/search.html`.
- `templates/single.html` or `templates/single-post.html`.
- Optional reusable `patterns/` or `parts/` files if repeated markup emerges.

Template requirements:

- Use the active site header/footer template parts.
- Provide a clean blog index query loop.
- Provide archive title/context.
- Provide search form and search-results context.
- Avoid inherited eStory demo ads, profile/sidebar content, and stock imagery.
- Keep markup block-native where practical.

Acceptance checks:

- WordPress resolves blog index, archive, search, and single post views through
  child-theme-owned templates.
- The templates render cleanly with no posts, one post, and many posts.

## Phase 3 - Fill Blog UI Element Gaps

Cover the elements a normal blog needs.

Required elements:

- Blog index listing with title, date, excerpt, featured image when present,
  category/tag links where useful, and readable "read more" behavior.
- Query pagination with previous, page numbers, and next.
- Single post layout with title, featured image, date, author or publisher
  attribution, categories/tags, and body content.
- Single-post previous/next post navigation.
- Search form and search results.
- Category and tag archive links.
- Explicit no-results states for empty blog, empty archive, and empty search.
- Comments/comment form behavior if comments remain part of the publishing
  model.

Acceptance checks:

- Each required element is present where expected.
- Any omitted element has a documented editorial reason.
- Empty states are human-readable and do not look broken.

## Phase 4 - Style Blog Surfaces In Child CSS

Before changing CSS, read the local CSS regression skill:

```text
.agents/skills/pns-frontend-css-regression/SKILL.md
```

Style scope:

- Blog cards/list rows.
- Pagination controls.
- Single-post previous/next navigation.
- Search form and result count/context.
- Post meta and term links.
- Comments, if enabled.
- Empty-state panels/messages.

Acceptance checks:

- `pnpm compile:css` succeeds from the child theme root.
- `pnpm check` succeeds or any failure is documented.
- New selectors are child-theme-owned and not tightly coupled to eStory demo
  artifacts unless intentionally preserving inherited compatibility.
- Desktop and mobile layouts are verified.

## Phase 5 - Seed, Verify, And Document Behavior

Create enough local content to exercise the real behavior before calling the
blog route complete.

Fixture/content needs:

- More posts than the per-page limit to force pagination.
- At least two categories.
- At least two tags.
- Posts with and without featured images.
- Searchable post titles/body text.
- Adjacent posts to verify previous/next navigation.
- Comment-enabled and comment-disabled examples if comments are retained.

Verification:

- Browser check the blog/news index.
- Browser check page 2 pagination.
- Browser check category and tag archives.
- Browser check search with results and no results.
- Browser check a single post on desktop and mobile.
- Add or update Playwright visual coverage for the new surfaces.

Acceptance checks:

- Local browser checks pass.
- `pnpm test:visual` passes or a new baseline/update decision is documented.
- Database/admin setup notes are captured for repeatability.

## Recommended Implementation Order

1. Complete Phase 0 before editing templates.
2. Configure the route in Phase 1 with the smallest possible database/admin
   change.
3. Add child templates in Phase 2 with plain, robust markup first.
4. Fill missing interface elements in Phase 3 before polishing.
5. Apply CSS in Phase 4 after the structural markup is stable and the current
   CSS audit is complete.
6. Seed and verify in Phase 5, then complete the Dex parent task when all phase
   acceptance checks are satisfied.
