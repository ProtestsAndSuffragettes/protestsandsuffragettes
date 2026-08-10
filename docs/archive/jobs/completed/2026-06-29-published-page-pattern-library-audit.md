# Page Pattern Library Audit

Date: 2026-06-29

Dex task: `56e3r256`

## Result

Page sections are now classified against the standalone theme pattern library
with a rerunnable WP-CLI audit. The audit covers `publish`, `private`, and
`draft` pages, and intentionally skips the unsettled `news` page.

Command:

```bash
wp eval-file app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/audit-published-page-patterns.php
```

Current result:

```text
Page pattern audit checked 215 section candidate(s); 0 need review.
```

## Migration Evidence

The semantic class backfill was applied with the existing backup-backed
migration surface:

```bash
wp eval-file app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/migrate-semantic-wrapper-classes.php apply
```

Rollback and report files:

- `docs/jobs/live-adoption-db-backups/2026-06-29-124122-semantic-wrapper-classes-before.json`
- `docs/jobs/live-adoption-db-backups/2026-06-29-124122-semantic-wrapper-classes-after-report.json`
- `docs/jobs/live-adoption-db-backups/2026-06-29-133236-semantic-wrapper-classes-before.json`
- `docs/jobs/live-adoption-db-backups/2026-06-29-133236-semantic-wrapper-classes-after-report.json`

Post-apply idempotence:

```text
Dry-run semantic wrapper class migration checked 87 matching record(s).
All reported changed=no.
```

## Pattern Library Additions

Source patterns were added so existing saved content no longer points at
class names without a code-owned pattern behind them:

| Pattern                               | Classes                                             | Purpose                                                                                                              |
| ------------------------------------- | --------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------- |
| `patterns/page-hero.php`              | `pns-section pns-layout pns-page-hero`              | Generic full-width saved page hero used by ArtWorks, Herstories, About, Shenanigans, workshops, and education pages. |

## Backfilled Pattern Classes

The migration added reusable pattern identity classes while preserving existing
compatibility hooks such as `pns-saved-section` and page-specific
`pns-*` affordances.

| Mapping                               | Pages / sections                                                                                                                                                              |
| ------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `patterns/basic-centred-content.php`  | `privacy-policy`, `gender-inclusion-policy`, `work-with-us-past-deadlines`, `work-with-us-argyll` top-level content wrappers.                                                 |
| `patterns/two-columns.php`            | Generic media/text sections on home, ArtWorks, Herstories, Educational Resources, About, Shenanigans, Glasgow workshops, Unleashing the Suffragette Spirit, and Edu Giveaway. |
| `patterns/activist-text-media.php`    | Mary Barbour and Store BLOCK TEST `Leading Women` and `Background` sections.                                                                                                  |
| `patterns/activist-facts.php`         | Mary Barbour and Store BLOCK TEST `Fun Facts about Mary` sections.                                                                                                            |
| `patterns/activist-image-strip.php`   | Mary Barbour and Store BLOCK TEST image-strip sections.                                                                                                                       |
| `patterns/image-strip.php`            | Mary Barbour style full-width separator image sections, with single-image instances represented as the canonical image-strip pattern edited down to one image.                  |

The second backup-backed pass also applied matching reusable classes to
draft/private pages where their saved sections were deterministic copies of
known patterns. That includes old activist drafts, test pages, and template
drafts with quote, Herstory image-strip, facts, image-strip,
previous/next, and synced-section copies.

Existing recognized mappings remain in place for:

- `patterns/welcome-header.php`
- `patterns/page-hero.php`
- `patterns/activist-hero.php`
- `patterns/blockquote-with-red-line.php`
- `patterns/blockquote-cover.php`
- `patterns/previous-next.php`
- synced `wp_block` patterns such as `shop-intro`, `contact-form`,
  `connect-social`, and `read-all-about-it`

## Page-Level Audit Summary

| Page                                         | Outcome                                                                                                                                  |
| -------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| `privacy-policy`                             | Top-level content wrapper maps to `basic-centred-content`.                                                                               |
| `mary-barbour`                               | Hero, text/media, image strip, facts, full-width image strip, red-line quote, shop intro, previous/next, and synced shop ref are mapped. |
| `about-2`                                    | Welcome header, three two-column sections, red-line quote, and synced shop ref are mapped.                                               |
| `shop`                                       | Storefront wrapper is explicitly classified as an intentional Ecwid one-off.                                                             |
| `artworks`                                   | Page hero, four two-column sections, two red-line quotes, and synced shop ref are mapped.                                                |
| `herstories`                                 | Page hero, four two-column sections, two red-line quotes, and synced shop ref are mapped.                                                |
| `educational-resources`                      | Page hero, four two-column sections, two red-line quotes, and synced shop ref are mapped.                                                |
| `about`                                      | Page hero, four two-column sections, and red-line quote are mapped.                                                                      |
| `shenanigans`                                | Page hero, four two-column sections, and red-line quote are mapped.                                                                      |
| `store-block-test`                           | Mary Barbour fixture copy maps to the same Herstory, quote, shop intro, previous/next, and image-strip patterns.                         |
| `glasgow-herstory-workshops`                 | Page hero, three two-column sections, two red-line quotes, and synced shop ref are mapped.                                               |
| `workshop-unleashing-the-suffragette-spirit` | Page hero, three two-column sections, two red-line quotes, and synced shop ref are mapped.                                               |
| `edu-giveaway`                               | Page hero, four two-column sections, two red-line quotes, and synced shop ref are mapped.                                                |
| `gender-inclusion-policy`                    | Top-level content wrapper maps to `basic-centred-content`.                                                                               |
| `work-with-us-past-deadlines`                | Top-level content wrapper maps to `basic-centred-content`.                                                                               |
| `work-with-us-argyll`                        | Top-level content wrapper maps to `basic-centred-content`.                                                                               |
| `pns-pattern-qa`                             | Fixture content is mapped by nested pattern sections and is retained as a test fixture.                                                  |
| Draft/private legacy pages                   | Deterministic pattern copies are mapped; remaining scaffold sections are classified as intentional legacy draft/private content.         |
| `pns-editor-css-fixture`, `test-page-2`      | Private fixture content is classified as intentional fixture content.                                                                    |
| `news`                                       | Skipped while the News design remains unsettled.                                                                                         |

## One-Off Policy

`pns-saved-section` and page-specific `pns-*` classes are compatibility hooks,
not the preferred styling contract. New CSS should target the reusable pattern
identity classes where they exist:

- `pns-basic-centred-content`
- `pns-two-columns`
- `pns-page-hero`
- `pns-activist-*`
- `pns-blockquote-*`
- `pns-image-strip`
- synced-section classes such as `pns-shop-intro`

Intentional one-offs in the page audit are:

- the Ecwid storefront wrapper on `/shop/`, because the underlying output is
  plugin-owned;
- legacy draft/private scaffold sections that are not current source-pattern
  contracts;
- private test/editor fixture sections;
- the skipped `news` page while that design remains unsettled.

## Regression Coverage

`tests/visual/frontend.spec.ts` now asserts that representative published
routes expose the reusable pattern identity classes after migration:

- `basic-centred-content`: privacy policy, gender inclusion policy, work with
  us Argyll
- `two-columns`: About and Shenanigans
- Herstory section identities: Mary Barbour text/media, facts, image strip, and
  full-width image strip
