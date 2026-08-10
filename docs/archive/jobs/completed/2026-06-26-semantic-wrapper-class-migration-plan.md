# Semantic Wrapper Class Migration Plan

Plan started on 2026-06-26.

All paths are relative to the project root.

## Goal

Give PNS-owned templates, template parts, code-backed patterns, synced patterns,
and migrated page sections stable semantic outer-wrapper classes, then migrate
current saved content so existing pages and future inserts use the same styling
hooks.

The purpose is not to add classes that mirror core block types such as
`core/group` or `core/columns`. The purpose is to add project-owned semantic
hooks that describe the section's role in the PNS design system.

## Proposed Class Model

Use a small, predictable class stack on the outer project-owned wrapper:

| Class type | Example | Purpose |
| ---------- | ------- | ------- |
| Base role | `pns-section` | Generic hook for PNS-authored reusable sections. |
| Family/category | `pns-herstories`, `pns-quotes`, `pns-layout`, `pns-synced-section` | Broad grouping, largely derived from pattern/template category names. |
| Semantic instance | `pns-activist-hero`, `pns-shop-intro`, `pns-blockquote-cover` | Stable section-specific hook for styling, migration matching, and QA. |

Rules:

- Prefer semantic names derived from pattern/template categories and slugs.
- Do not use block implementation names such as `pns-group`, `pns-columns`, or
  `pns-cover` as the primary class.
- Add classes to the outermost meaningful PNS-owned wrapper, not every nested
  block.
- Add template-level classes only where the route/template shell itself owns
  distinct structure, styling, or behavior. Do not add `pns-template-*` classes
  to every template by default.
- Keep WordPress core layout classes intact.
- Decide whether any legacy or non-`pns-*` classes should remain only after the
  Phase 0 survey proves they exist and are used. If legacy classes are not
  present in current content, do not invent compatibility aliases.
- Treat synced patterns specially: editing the `wp_block` source updates every
  `core/block` reference, so they should be migrated at source level when
  possible.

## Phase 0 - Survey

Inventory every current source that may need semantic wrapper classes before
choosing final names or mutating content.

Survey scope:

- Filesystem standalone theme sources:
  - `templates/*.html`
  - `parts/*.html`
  - `patterns/*.php`
  - `synced-patterns/*.html`
- Database-backed WordPress sources:
  - `wp_template`
  - `wp_template_part`
  - `wp_block`
  - `page`
  - `post`
  - `blockmeister_pattern`
  - `wp_navigation` only if wrapper classes are embedded in nav-containing
    content rather than the template part.
- Existing CSS hooks that already imply semantic ownership:
  - `.shop-intro`
  - `.previous-next`
  - `.active-dates`
  - `.fun-facts`
  - `.footer-logo`
  - any other `pns-*`, section, or page-specific selectors.

Survey outputs:

- A table of candidate objects with:
  - source type;
  - file path or post ID;
  - title/slug;
  - current outer block type;
  - current outer `className`;
  - proposed base/family/semantic classes;
  - whether it is copied markup, synced content, template structure, or a
    plugin-owned dependency;
  - migration risk.
- A list of objects to exclude because they are plugin-owned, stale, draft-only,
  or not PNS-authored.
- A rollback/export list for every DB post type that will be touched in later
  phases.
- A list of draft-only fixtures and records that will be excluded from class
  migration.

Gate before Phase 1:

- No content mutation has happened.
- The class taxonomy is reviewed against real filesystem and DB content.
- Every migration candidate has a deterministic matcher that does not depend on
  transient database IDs.

## Phase 1 - Taxonomy And Naming Decisions

Turn the survey into an approved class map.

Expected decisions:

- Final family/category class names.
- Final semantic instance class names.
- Which outer block receives the classes for each pattern/template.
- Which route/template shells, if any, need a template-level `pns-template-*`
  class because the shell itself owns distinct behavior.
- Whether any existing non-`pns-*` classes need temporary alias treatment. This
  must be evidence-based from the Phase 0 survey.

Gate before Phase 2:

- The class map is documented in this plan or a linked implementation note.
- Each class has exactly one owning source category.
- Ambiguous names are resolved before code changes.

## Phase 2 - Source Fixture Updates

Add the approved classes to versioned sources first.

Targets:

- Code-backed inserter patterns in `patterns/*.php`.
- Native synced-pattern fixtures in `synced-patterns/*.html`.
- Template and template-part files in `templates/*.html` and `parts/*.html`
  where the wrapper is source-owned.
- Published fixtures only. Draft-only rollback fixtures are skipped unless they
  are deliberately reactivated in a later plan.

Rules:

- Preserve existing block attributes and layout settings.
- Update only the outer project-owned wrapper for each source.
- Keep synced fixture slugs stable.
- Validate serialized block markup after edits.

Gate before Phase 3:

- Filesystem sources parse with the block-template validator.
- Registered `pns/*` patterns still appear in the runtime registry.
- No DB content has been mutated yet unless explicitly covered by a rollback
  export.

## Phase 3 - Migration Tooling And Dry Run

Create a repeatable WP-CLI migration script that can add the approved classes to
existing saved content.

Tooling requirements:

- Use `parse_blocks()` and `serialize_blocks()` rather than regex mutation.
- Match records by stable slugs, post type, block name, existing classes, and
  block shape.
- Never depend on old export/source IDs.
- Support `--dry-run` and `--apply`.
- Print a per-record report of:
  - post ID;
  - post type;
  - title;
  - matched section;
  - classes added;
  - whether content changed.
- Export rollback JSON before `--apply`.

Gate before Phase 4:

- Dry run has no unexpected matches.
- Dry run reports every intended existing content instance.
- Rollback export location is decided under `docs/jobs/live-adoption-db-backups/`
  or a more specific migration backup directory.

## Phase 4 - Apply Existing Content Migration

Run the migration against current database-backed content.

Order:

1. Export rollback data.
2. Migrate `wp_block` synced patterns by slug.
3. Migrate `wp_template` and `wp_template_part` records.
4. Migrate copied page/post/blockmeister content only where the Phase 0 survey
   approved a deterministic matcher.
5. Re-run the dry-run/report mode to prove no intended records remain
   unmigrated.

Gate before Phase 5:

- All approved existing content has the agreed classes.
- No excluded/plugin-owned content was mutated.
- Canonical synced patterns still update all existing references through normal
  `core/block` behavior.

## Phase 5 - Compatibility And CSS Adoption

Move CSS selectors to the new semantic hooks without breaking old content.

Approach:

- Prefer new `pns-*` selectors for future CSS.
- Keep temporary aliases for old classes only if the Phase 0 survey finds real
  old classes in current content and the migration cannot cover every
  historical instance safely.
- Avoid broad selectors that recreate the current ownership ambiguity.
- Record any retained alias with an owner and deletion condition.

Gate before Phase 6:

- Frontend CSS compiles.
- Editor CSS compiles if editor-visible classes or pattern previews changed.
- Visual checks cover migrated sections on desktop and mobile.

## Phase 6 - QA, Documentation, And Closeout

Prove the new class model is stable and documented.

Checks:

- WP-CLI report of migrated classes across relevant post types.
- Runtime render checks for representative routes.
- Pattern QA route checks for code-backed patterns.
- Synced-pattern insertion/editing checks for published synced patterns.
- Visual regression checks for affected pages.
- `git diff --check`.

Closeout requirements:

- Update this plan with final class map and migration evidence.
- Record rollback export paths.
- Record validation commands and results.
- If any legacy aliases remain, create follow-up tasks with deletion criteria.

## Initial Candidate Classes

Phase 0 confirmed this class model for implementation:

- reusable sections: `pns-section` plus a family class and semantic class;
- synced sections: `pns-section pns-synced-section` plus semantic class;
- template parts: `pns-template-part` plus semantic part class;
- route/template shells: `pns-template` plus semantic template class only where
  an owned wrapper already exists;
- draft-only fixtures and records are skipped.

| Object | Family class | Semantic class |
| ------ | ------------ | -------------- |
| Welcome Header | `pns-layout` | `pns-welcome-header` |
| Basic Centred Content | `pns-layout` | `pns-basic-centred-content` |
| Suffragette Stats | `pns-layout` | `pns-suffragette-stats` |
| Previous Next | `pns-layout` | `pns-previous-next` |
| Two Columns | `pns-layout` | `pns-two-columns` |
| Blockquote Cover | `pns-quotes` | `pns-blockquote-cover` |
| Blockquote With Red Line | `pns-quotes` | `pns-blockquote-with-red-line` |
| Activist Hero | `pns-herstories` | `pns-activist-hero` |
| Activist Text and Media | `pns-herstories` | `pns-activist-text-media` |
| Activist Facts | `pns-herstories` | `pns-activist-facts` |
| Activist Image Strip | `pns-herstories` | `pns-activist-image-strip` |
| Shop Intro | `pns-synced-section` | `pns-shop-intro` |
| Connect Social | `pns-synced-section` | `pns-connect-social` |
| Read All About It | `pns-synced-section` | `pns-read-all-about-it` |
| Contact Form | `pns-synced-section` | `pns-contact-form` |
| Contact Form Original Copy | `pns-synced-section` | `pns-contact-form-original-copy` |
| Contact Form Octopus Template Part | `pns-template-part` | `pns-contact-form-octopus` |
| Header Template Part | `pns-template-part` | `pns-header` |
| Footer Template Part | `pns-template-part` | `pns-footer` |
| 404 Template | `pns-template` | `pns-template-404` |
| Archive Template | `pns-template` | `pns-template-archive` |
| Education Pack Giveaway Template | `pns-template` | `pns-template-education-pack-giveaway` |
| Home Template | `pns-template` | `pns-template-home` |
| Index Template | `pns-template` | `pns-template-index` |
| Default Page Template | `pns-template` | `pns-template-page` |
| Page No Contact Form Template | `pns-template` | `pns-template-page-no-contact-form` |
| Activist Page Template | `pns-template` | `pns-template-activist` |
| Search Template | `pns-template` | `pns-template-search` |
| Single Template | `pns-template` | `pns-template-single` |

## Migration Tracker

Tracker generated from filesystem sources and live WordPress DB state on
2026-06-26.

Status meanings:

- `Updated`: source or saved content has the agreed semantic wrapper classes.
- `Skipped`: intentionally not migrated because it is draft/private or contains
  no copied PNS template section.
- `Pending`: published active content with a copied PNS template section still
  missing its semantic wrapper classes.

Active theme options at tracker time:

```text
stylesheet: protestsandsuffragettes-standalone
template: protestsandsuffragettes-standalone
```

### Filesystem Template Sources

| Source | Status | Notes |
| ------ | ------ | ----- |
| `templates/404.html` | Updated | Has `pns-template pns-template-404`. |
| `templates/archive.html` | Updated | Has `pns-template pns-template-archive`. |
| `templates/education-pack-giveaway-2.html` | Updated | Has `pns-template pns-template-education-pack-giveaway`. |
| `templates/home.html` | Updated | Has `pns-template pns-template-home`. |
| `templates/index.html` | Updated | Has `pns-template pns-template-index`. |
| `templates/page.html` | Updated | Has `pns-template pns-template-page`. |
| `templates/page-activist.html` | Updated | Has `pns-template pns-template-activist`; keeps legacy `pns-activist-template`. |
| `templates/page-no-contact-form.html` | Updated | Has `pns-template pns-template-page-no-contact-form`. |
| `templates/search.html` | Updated | Has `pns-template pns-template-search`. |
| `templates/single.html` | Updated | Has `pns-template pns-template-single`. |
| `parts/contact-form-octopus-tempate-part.html` | Updated | Has `pns-template-part pns-contact-form-octopus`. |
| `parts/footer.html` | Updated | Has `pns-template-part pns-footer`. |
| `parts/header.html` | Updated | Has `pns-template-part pns-header`. |

### Saved Template And Template-Part Records

| ID | Type | Slug | Theme | Status | Notes |
| -- | ---- | ---- | ----- | ------ | ----- |
| `5325` | `wp_template` | `page` | `protestsandsuffragettes-standalone` | Updated | Active saved page template; has `pns-template pns-template-page`. |

### Page Content Records

| ID | Status | Slug | Title | Tracker Status | Notes |
| -- | ------ | ---- | ----- | -------------- | ----- |
| `3` | `publish` | `privacy-policy` | Privacy Policy | Skipped | No copied PNS template section detected. |
| `42` | `publish` | `mary-barbour` | Mary Barbour | Updated | Activist hero, shop intro, quote sections, and previous/next wrappers migrated. |
| `49` | `publish` | `about-2` | Protests and Suffragettes | Updated with pending ancestry review | Homepage welcome header and blockquote-with-red-line wrappers migrated; middle content groups still need source-pattern ancestry classification. |
| `445` | `draft` | `resources` | [ Resources -- may be useful text here to copy over ] | Skipped | Draft content skipped by migration policy. |
| `565` | `publish` | `shop` | Shop | Skipped | No copied PNS template section detected in page content; page uses template/synced sources for structure. |
| `1066` | `publish` | `artworks` | ArtWorks | Updated | Copied quote wrappers migrated. |
| `1524` | `draft` |  | [ Test Page ] | Skipped | Draft content skipped by migration policy. |
| `1758` | `draft` |  | **TEMPLATE A - duplicate of Front Page / Home Page | Skipped | Draft template copy skipped by migration policy. |
| `1761` | `draft` |  | **TEMPLATE B – duplicate of Art Works | Skipped | Draft template copy skipped by migration policy. |
| `1783` | `publish` | `herstories` | Herstories | Updated | Copied quote wrappers migrated. |
| `1786` | `publish` | `educational-resources` | Educational Resources | Updated | Copied quote wrappers migrated. |
| `1789` | `publish` | `about` | About | Updated | Copied quote wrappers migrated. |
| `1797` | `draft` | `our-work-with-wikipedia-going-live-round-2` | Our work with Wikipedia [GOING LIVE ROUND 2] | Skipped | Draft content skipped by migration policy. |
| `1828` | `draft` |  | ** TEMPLATE C – duplicate of Mary Barbour | Skipped | Draft template copy skipped by migration policy. |
| `1833` | `draft` |  | Agnes Dollan | Skipped | Draft activist copy skipped by migration policy. |
| `1848` | `draft` |  | Jessie Soga | Skipped | Draft activist copy skipped by migration policy. |
| `1855` | `draft` |  | Georgiana Solomon | Skipped | Draft activist copy skipped by migration policy. |
| `1861` | `private` | `test-page-2` | [ Test Page 2 – FEEL FREE TO PLAY ABOUT WITH EDITING THIS ONE :) ] | Skipped | Private test content skipped by migration policy. |
| `1869` | `draft` |  | Helen Fraser | Skipped | Draft activist copy skipped by migration policy. |
| `2363` | `publish` | `shenanigans` | Shenanigans | Updated | Copied quote wrapper migrated. |
| `2874` | `draft` |  | Lila Clunas-DRAFT | Skipped | Draft activist copy skipped by migration policy. |
| `3228` | `publish` | `store-block-test` | Store BLOCK TEST | Updated | Activist hero, shop intro, quote sections, and previous/next wrappers migrated. |
| `3677` | `publish` | `glasgow-herstory-workshops` | Glasgow Herstory Workshops | Updated | Copied quote wrappers migrated. |
| `3819` | `draft` |  | TEST ACTIVIST PAGE June 2023 | Skipped | Draft activist test page skipped by migration policy. |
| `4501` | `publish` | `workshop-unleashing-the-suffragette-spirit` | Workshop – Unleashing the Suffragette Spirit | Updated | Copied quote wrappers migrated. |
| `4551` | `draft` |  | TEST PAGE | Skipped | Draft content skipped by migration policy. |
| `4629` | `publish` | `edu-giveaway` | Education Pack Giveaway – SIGN UP for FREE DOWNLOAD and DISCOUNT CODE | Updated | Copied quote wrappers migrated. |
| `4735` | `publish` | `gender-inclusion-policy` | Gender Inclusion Policy Statement | Skipped | No copied PNS template section detected. |
| `5028` | `publish` | `work-with-us-past-deadlines` | Work with Us – Business Development Lead | Skipped | No copied PNS template section detected. |
| `5128` | `publish` | `work-with-us-argyll` | Work with Us – Argyll | Skipped | No copied PNS template section detected. |
| `5190` | `publish` | `news` | News | Skipped | No copied PNS template section detected; route behavior comes from templates/query context. |
| `5265` | `publish` | `pns-pattern-qa` | PNS Pattern QA | Updated | Welcome header, activist hero, quote, and quote-cover wrappers migrated. |
| `5277` | `publish` | `pattern-test` | Pattern Test | Updated | Shop intro wrapper migrated. |

### Homepage Ancestry Example

Front page at tracker time:

```text
ID: 49
slug: about-2
title: Protests and Suffragettes
template shell: wp_template 5325, pns-template pns-template-page
```

| Order | Saved block shape | Confirmed ancestor | Current wrapper state | Tracker Status | Notes |
| ----- | ----------------- | ------------------ | --------------------- | -------------- | ----- |
| 1 | `core/cover.jumbo-header` | `patterns/welcome-header.php`, `pns/welcome-header` | `jumbo-header pns-section pns-layout pns-welcome-header` | Updated | Deterministic ancestry from `jumbo-header` plus homepage header image/shape. |
| 2 | `core/group > core/columns > jetpack/slideshow + text/buttons` | Pending classification | No semantic outer wrapper yet | Pending ancestry review | Likely inherited/custom homepage content, but no stable current pattern marker confirms a source. |
| 3 | `core/group > core/columns > image + text/buttons` | Pending classification | No semantic outer wrapper yet | Pending ancestry review | Similar to a two-column content section, but does not match current `pns/two-columns` source closely enough to migrate without review. |
| 4 | `core/cover` containing `wp:quote`, `Quote_image_1.jpg`, and `Red-Keyline.svg` | `patterns/blockquote-with-red-line.php`, `pns/blockquote-with-red-line` | `pns-section pns-quotes pns-blockquote-with-red-line` | Updated | Deterministic ancestry from quote cover image plus red keyline marker. |
| 5 | `core/group > core/columns.vw-100.no-gap.alignfull` | Pending classification | No semantic outer wrapper yet | Pending ancestry review | Looks like an inherited homepage/media text section, but lacks a confirmed standalone source pattern marker. |
| 6 | `core/block` ref `1509` | Synced pattern `Shop Intro`, `synced-patterns/shop-intro.html` | Source `wp_block` and fixture have `pns-section pns-synced-section pns-shop-intro` | Updated | Referenced synced pattern; edits to `wp_block` `1509` update all references. |

This table is the model for page-level tracking: each saved page should list
its copied sections, confirmed source ancestor, current wrapper state, and
whether any section remains pending because its source cannot be identified
deterministically.

### Main Navigation Page Ancestry Tracker

Main navigation source:

```text
header navigation ref: 1035
navigation title: Top Nav
```

Included pages:

- explicit `core/navigation-link` page items;
- `core/navigation-submenu` parent pages;
- custom nav URLs that resolve to published pages;
- dynamic `core/page-list` children under the Herstories submenu.

Excluded nav entries:

- `Contact` points to `#contact`, not a page.
- `News` is a submenu label without a linked page ID in navigation ref `1035`.
- `We're Hiring` duplicates `/about/work-with-us-argyll/`.

| Nav page | Page ID | URL source | Confirmed ancestors | Updated wrapper state | Pending ancestry review |
| -------- | ------- | ---------- | ------------------- | --------------------- | ----------------------- |
| ArtWorks | `1066` | `/artworks/` | `pns/blockquote-with-red-line` x2; synced `Shop Intro` ref `1509` | Quote wrappers and synced shop source updated. | Hero cover plus four content groups/columns lack confirmed standalone source-pattern classes. |
| About | `1789` | `/about/` submenu parent | `pns/blockquote-with-red-line` x1 | Quote wrapper updated. | Hero cover plus four content groups/columns lack confirmed standalone source-pattern classes. |
| Work with Us – Argyll | `5128` | `/about/work-with-us-argyll/` custom link | None confirmed yet | No migrated copied PNS section detected. | Single `core/group.mt0` content wrapper likely needs classification against `pns/basic-centred-content` or another content template. |
| Gender Inclusion Policy Statement | `4735` | `/about/gender-inclusion-policy/` | None confirmed yet | No migrated copied PNS section detected. | Single `core/group.mt0` content wrapper likely needs classification against `pns/basic-centred-content` or another content template. |
| Herstories | `1783` | `/herstories/` submenu parent | `pns/blockquote-with-red-line` x2; synced `Shop Intro` ref `1509` | Quote wrappers and synced shop source updated. | Hero cover plus four content groups/columns lack confirmed standalone source-pattern classes. |
| Mary Barbour | `42` | Herstories `core/page-list` child | `pns/activist-hero`; `pns/blockquote-with-red-line`; `pns-shop-intro`; `pns/previous-next`; synced `Shop Intro` ref `1509` | Confirmed wrappers updated on hero, quotes, shop intro, previous/next, and synced shop source. | Several middle content/media sections still need ancestry classification against Herstories source sections such as text/media, facts, image strip, or custom content. |
| Shenanigans | `2363` | `/shenanigans/` submenu parent | `pns/blockquote-with-red-line` x1 | Quote wrapper updated. | Hero cover plus four content groups/columns lack confirmed standalone source-pattern classes. |
| Workshop – Unleashing the Suffragette Spirit | `4501` | `/shenanigans/workshop-unleashing-the-suffragette-spirit/` | `pns/blockquote-with-red-line` x2; synced `Shop Intro` ref `1509` | Quote wrappers and synced shop source updated. | Hero cover plus three content groups/columns lack confirmed standalone source-pattern classes. |
| Glasgow Herstory Workshops | `3677` | `/shenanigans/glasgow-herstory-workshops/` | `pns/blockquote-with-red-line` x2; synced `Shop Intro` ref `1509` | Quote wrappers and synced shop source updated. | Hero cover plus three content groups/columns lack confirmed standalone source-pattern classes. |
| Educational Resources | `1786` | `/educational-resources/` | `pns/blockquote-with-red-line` x2; synced `Shop Intro` ref `1509` | Quote wrappers and synced shop source updated. | Hero cover plus four content groups/columns lack confirmed standalone source-pattern classes. |
| Shop | `565` | `/shop/` | None confirmed in page content | No copied PNS page section detected; page uses template/synced sources for structure. | Single `core/group.mt0` wrapper needs classification if it is intended to inherit a standalone source pattern. |

Main-nav ancestry conclusion:

- The migration has covered deterministic quote, activist hero, previous/next,
  and synced shop ancestry.
- It has not yet completed semantic ancestry for generic page hero covers,
  slideshow/text sections, image/text sections, or `mt0` content wrappers
  because those saved blocks do not carry stable standalone source-pattern
  markers.
- The next implementation phase should define semantic classes for those
  remaining section families, then extend the migration using deterministic
  matchers.

### Post Content Records

| ID | Status | Slug | Title | Tracker Status | Notes |
| -- | ------ | ---- | ----- | -------------- | ----- |
| `5192` | `publish` | `fixture-community-update-with-comments` | Fixture: Community update with nested comments | Skipped | Fixture post; no copied PNS template section detected. |
| `5193` | `publish` | `fixture-very-long-news-title-that-wraps-across-multiple-lines` | Fixture: A very long news title that should wrap across multiple lines without breaking the listing layout | Skipped | Fixture post; no copied PNS template section detected. |
| `5194` | `publish` | `fixture-block-coverage-headings-lists-table-buttons` | Fixture: Block coverage for headings, lists, table, and buttons | Skipped | Fixture post; no copied PNS template section detected. |
| `5195` | `publish` | `fixture-no-featured-image` | Fixture: Post without a featured image | Skipped | Fixture post; no copied PNS template section detected. |
| `5196` | `publish` | `fixture-comments-closed` | Fixture: Comments closed example | Skipped | Fixture post; no copied PNS template section detected. |
| `5197` | `publish` | `fixture-manual-excerpt` | Fixture: Manual excerpt should appear on listing views | Skipped | Fixture post; no copied PNS template section detected. |
| `5198` | `publish` | `fixture-category-and-tag-links` | Fixture: Category and tag link coverage | Skipped | Fixture post; no copied PNS template section detected. |
| `5199` | `publish` | `fixture-pagination-01` | Fixture: Pagination item 01 | Skipped | Fixture post; no copied PNS template section detected. |
| `5200` | `publish` | `fixture-pagination-02` | Fixture: Pagination item 02 | Skipped | Fixture post; no copied PNS template section detected. |
| `5201` | `publish` | `fixture-pagination-03` | Fixture: Pagination item 03 | Skipped | Fixture post; no copied PNS template section detected. |
| `5202` | `publish` | `fixture-pagination-04` | Fixture: Pagination item 04 | Skipped | Fixture post; no copied PNS template section detected. |
| `5203` | `publish` | `fixture-pagination-05` | Fixture: Pagination item 05 | Skipped | Fixture post; no copied PNS template section detected. |

### Pending Tracker

| Item | Status | Notes |
| ---- | ------ | ----- |
| Published copied PNS page sections | None pending | Post-apply dry run reports all matching migration targets as `changed=no`. |
| Published copied quote-cover sections | None pending | DB scan reports `missing_quote_cover_classes=0`. |
| Homepage unclassified content groups | Pending ancestry review | Page `49` has three middle content groups that look inherited but do not yet have confirmed standalone source-pattern ancestors. |
| Main-navigation unclassified content groups | Pending ancestry review | Nav pages have generic hero covers, slideshow/text sections, image/text sections, and `mt0` wrappers that need semantic ancestry decisions before migration. |
| Draft/private copied PNS page sections | Skipped | Deliberately skipped per migration policy; revisit only if a draft is promoted to published content. |

## 2026-06-26 Phase 0 Survey And Implementation Evidence

Filesystem survey:

- `patterns/*.php` reusable sections were missing semantic outer-wrapper
  classes or had only utility classes such as `mt0`, `no-gap`, and
  `jumbo-header`.
- Published synced fixtures were missing semantic classes except for
  `shop-intro`.
- `shop-intro-copy.html` is draft-only and was skipped.
- `templates/page.html` and `templates/page-no-contact-form.html` originally
  had no owned outer wrapper; after review, they were given explicit
  `main.pns-template` shells so normal pages are covered by the class model.
- `parts/header.html`, `parts/footer.html`, and
  `parts/contact-form-octopus-tempate-part.html` have owned wrappers and were
  assigned template-part classes.

Database survey:

- Published synced `wp_block` records migrated:
  - `1493` `contact-form`;
  - `1494` `connect-social`;
  - `1504` `read-all-about-it`;
  - `1509` `shop-intro`;
  - `4654` `contact-form-original-copy`;
  - `5323` `shop-ecwid`.
- Saved active DB page template wrapped:
  - `5325` `wp_template/page` now wraps the page body in
    `pns-template pns-template-page`.
- Published copied page sections migrated where deterministic class-based
  matchers existed:
  - `49` `about-2`: welcome header copy;
  - `49` `about-2`: homepage blockquote-with-red-line copy;
  - `42` `mary-barbour`: activist hero, shop intro, previous/next;
  - `42` `mary-barbour`: blockquote-with-red-line copies;
  - `3228` `store-block-test`: activist hero, shop intro copies,
    blockquote-with-red-line copies, previous/next;
  - `5265` `pns-pattern-qa`: welcome header and activist hero copies;
  - `5265` `pns-pattern-qa`: blockquote-with-red-line and blockquote-cover
    copies;
  - `5277` `pattern-test`: shop intro copy.
  - additional published pages with copied quote-cover sections:
    `1066` `artworks`, `1783` `herstories`, `1786`
    `educational-resources`, `1789` `about`, `2363` `shenanigans`, `3677`
    `glasgow-herstory-workshops`, `4501`
    `workshop-unleashing-the-suffragette-spirit`, and `4629` `edu-giveaway`.
- Draft pages, draft BlockMeister records, draft synced fixtures, navigation
  records, and stale missing ref `1391` were not migration targets.

Current known risk:

- Saved standalone `wp_template` `5325` currently references draft reusable
  block `3816` (`Shop Intro (Using Ecwid Blocks)`). This plan did not change
  that reference because it is not a wrapper-class migration concern, but it
  should be handled by the synced-pattern/template ownership work.

Migration tooling:

- Added
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/migrate-semantic-wrapper-classes.php`.
- The script supports:
  - `dry-run`;
  - `apply`;
  - rollback export before DB mutation;
  - deterministic matching by post type, slug, status, block name, existing
    classes, tag name, nested class presence, and stable serialized block
    markers for copied quote patterns;
  - updating both block `attrs.className` and the serialized wrapper HTML.

Rollback export:

```text
docs/jobs/live-adoption-db-backups/2026-06-26-112039-semantic-wrapper-classes-before.json
docs/jobs/live-adoption-db-backups/2026-06-26-142822-page-template-wrapper-before.json
docs/jobs/live-adoption-db-backups/2026-06-26-150434-semantic-wrapper-classes-before.json
docs/jobs/live-adoption-db-backups/2026-06-26-191758-page-template-layout-before.json
docs/jobs/live-adoption-db-backups/2026-06-26-192414-page-template-alignfull-before.json
```

Validation:

```text
php -l app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/migrate-semantic-wrapper-classes.php
php app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-block-templates.php
wp eval-file app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/migrate-semantic-wrapper-classes.php dry-run
wp eval-file app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/migrate-semantic-wrapper-classes.php apply
wp eval-file app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/migrate-semantic-wrapper-classes.php dry-run
Rendered spot checks for /privacy-policy/, /shop/, /edu-giveaway/,
/pns-pattern-qa/, /mary-barbour/, and /herstories/mary-barbour/
```

The first post-apply dry run reported all 18 initial matching records as
`changed=no`. After adding copied quote-section migration targets, the second
post-apply dry run reported all 31 matching records as `changed=no`, proving
the migration is idempotent after application.

After the saved page-template wrapper update, normal published pages render
`pns-template pns-template-page`; the Education Pack Giveaway page renders
`pns-template pns-template-education-pack-giveaway`.

Homepage/front-page content now renders the copied quote section with
`pns-section pns-quotes pns-blockquote-with-red-line`, matching the source
`pns/blockquote-with-red-line` pattern wrapper classes.

The default page template wrapper was corrected from `layout.type=constrained`
to `layout.type=default` after verification showed WordPress rendered the new
`main` group as `is-layout-constrained`, causing a layout shift. The wrapper
still carries `pns-template pns-template-page`, but now renders with flow layout
instead of constrained layout. It was then corrected to `align=full` because a
top-level, non-`alignfull` `main` block can still be constrained by WordPress
root layout rules in the frontend and editor canvas. The rendered homepage now
uses `wp-block-group alignfull pns-template pns-template-page is-layout-flow`.

The page editor still rendered the homepage welcome Cover composition centered
because the editor canvas does not render the page template shell in the same
way as the frontend. Frontend computed style for `.pns-welcome-header` uses
`justify-content:flex-start`; `styles/editor.css` now mirrors that for
`.pns-welcome-header` and `.pns-activist-hero` Cover blocks inside
`.editor-styles-wrapper`.

## Decisions

- Template-level classes are allowed only where the route/template shell owns
  distinct structure, styling, or behavior. They are not added to every template
  by default.
- Legacy alias handling is deferred until Phase 0 survey evidence. If current
  content does not already contain useful legacy classes, aliases are not
  needed.
- Draft-only fixtures and records are skipped by this migration.
