# Custom Blocks and Patterns Audit

Audit captured on 2026-06-22.

All paths are relative to the project root.

## Summary

This site does not define project-owned custom block types in the child theme.
The active child theme customizes and filters existing blocks, but block type
registration is handled by plugins and database-stored WordPress content.

The practical deployment model is:

- Plugin code registers available non-core block types.
- Native WordPress synced patterns/reusable blocks live in database posts of type
  `wp_block`.
- BlockMeister-managed patterns live in database posts of type
  `blockmeister_pattern`, then BlockMeister registers them into the normal
  WordPress pattern inserter at runtime.
- Site editor templates and template parts live in database posts such as
  `wp_template` and `wp_template_part`.

## Terminology

| Term                                 | Where it lives                                                                                              | How it behaves                                                                                                                                                                                | Example on this site                                                  |
| ------------------------------------ | ----------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------- |
| Block type                           | Registered by PHP/JS/plugin code and stored in content as block markup such as `<!-- wp:namespace/name -->` | If the governing plugin is disabled, the editor/frontend may show missing or degraded blocks                                                                                                  | `ecwid/store-block`, `jetpack/slideshow`, `epico/dynamic-year-block`  |
| Native registered pattern            | Registered into WordPress's pattern registry by core, a theme, a plugin, or another runtime source          | Inserter template. When inserted, it normally copies block markup into the post/page                                                                                                          | Parent theme `estory/*` patterns and BlockMeister-registered patterns |
| Native synced pattern/reusable block | Database post of type `wp_block`, referenced by `core/block` with a `ref` ID                                | Shared content. Editing the `wp_block` updates all live references                                                                                                                            | `wp_block` ID `1509`, `Shop Intro`                                    |
| BlockMeister pattern                 | Database post of type `blockmeister_pattern`, managed by the BlockMeister plugin                            | BlockMeister turns each published pattern post into a normal registered pattern. Inserting it copies the contained blocks into content; it is not the same as a `core/block` synced reference | `blockmeister_pattern` ID `2267`, `Individual Activist Page`          |
| Template/template part               | Database post of type `wp_template` or `wp_template_part`                                                   | Site editor layout content, parsed as blocks at render time                                                                                                                                   | `wp_template_part` ID `1026`, `Footer`                                |

## Pattern Distinctions

WordPress uses the word "pattern" for more than one thing:

- A registered pattern is an inserter template. It may come from core, a theme,
  a plugin, or a database-backed tool. Inserting it usually copies its block
  markup into the page.
- A synced pattern, formerly called a reusable block, is stored as `wp_block`.
  Pages reference it through a `core/block` block with a `ref` ID. Updating the
  `wp_block` changes every place that reference is used.
- A BlockMeister pattern is plugin-managed source content stored as
  `blockmeister_pattern`. BlockMeister registers those posts as inserter
  patterns with `register_block_pattern()`. Existing inserted content is normal
  block markup, while the editable source pattern remains in the BlockMeister
  custom post type.

So `blockmeister_pattern` differs from native `wp_block` patterns mainly in
update semantics and ownership: `wp_block` is WordPress's native shared/synced
content object, while `blockmeister_pattern` is a BlockMeister authoring store
for insertable templates.

## Child Theme Role

The child theme does not register custom block types. No project-owned
`block.json`, `register_block_type`, or `registerBlockType` source was found in
`app/public/wp-content/themes/protestsandsuffragettes`.

The child theme does contain block-related behavior:

| File                                                                               | Behavior                                                                                                        |
| ---------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------- |
| `app/public/wp-content/themes/protestsandsuffragettes/functions.php`               | Filters `render_block` and removes stray `[]` output from the Ecwid `ecwid/store-block` block. See lines 77-82. |
| `app/public/wp-content/themes/protestsandsuffragettes/functions.php`               | Filters `allowed_block_types_all` and removes selected core blocks.                                             |
| `app/public/wp-content/themes/protestsandsuffragettes/theme.json`                  | Defines design settings and block styles for core blocks.                                                       |
| `app/public/wp-content/themes/protestsandsuffragettes/style.css` and editor styles | Style core/plugin block output, but do not define block types.                                                  |

## Governing Plugins

| Plugin                  | Active version | Governs                                                                                       | Deployment role                                                                                  |
| ----------------------- | -------------: | --------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------ |
| `blockmeister`          |       `3.1.12` | `blockmeister_pattern` posts and registered block patterns                                    | Pattern authoring and inserter registration. No direct custom block type registration was found. |
| `ecwid-shopping-cart`   |        `7.0.8` | Ecwid commerce blocks such as `ecwid/store-block`, `ecwid/product-block`, `ec-store/minicart` | Registers dynamic server-rendered commerce block types.                                          |
| `dynamic-year-block`    |        `1.0.0` | `epico/dynamic-year-block`                                                                    | Registers a dynamic year block and hooks it into footer context.                                 |
| `jetpack`               |         `15.9` | Jetpack blocks such as `jetpack/slideshow`, form blocks, and other Jetpack extension blocks   | Registers many block types and block extensions.                                                 |
| `animations-for-blocks` |        `1.2.6` | `anfb/animation-container` plus animation attributes on supported blocks                      | Registers an animation container block and mutates supported block registration/render behavior. |
| `safe-svg`              |        `2.4.0` | `safe-svg/svg-icon`                                                                           | Registers an SVG icon block. No usage was observed in parsed content during this audit.          |
| `emailoctopus`          |       `3.1.10` | `emailoctopus/form-block`                                                                     | Registers an EmailOctopus form block. No usage was observed in parsed content during this audit. |
| `estory` parent theme   |            n/a | `estory/*` registered patterns                                                                | Provides parent-theme pattern library entries, separate from BlockMeister.                       |

## Plugin Code Evidence

| Source                                                                                                      | Evidence                                                                                                                                |
| ----------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------- |
| `app/public/wp-content/plugins/blockmeister/includes/Pattern_Builder/BlockMeister_Pattern_Post_Type.php`    | Registers the `blockmeister_pattern` custom post type with UI and REST support. See lines 33-92.                                        |
| `app/public/wp-content/plugins/blockmeister/includes/Pattern_Builder/Block_Pattern_Registry.php`            | Loads published `blockmeister_pattern` posts and calls `register_block_pattern()`. See lines 78-123.                                    |
| `app/public/wp-content/plugins/ecwid-shopping-cart/includes/gutenberg/class-ecwid-gutenberg.php`            | Defines Ecwid block names and instantiates/registers their classes. See lines 7-37.                                                     |
| `app/public/wp-content/plugins/ecwid-shopping-cart/includes/gutenberg/class-ecwid-gutenberg-block-base.php` | Calls `register_block_type()` for Ecwid block classes. See lines 14-21.                                                                 |
| `app/public/wp-content/plugins/dynamic-year-block/dynamic-year-block.php`                                   | Registers `epico/dynamic-year-block` on `init`. See lines 29-61.                                                                        |
| `app/public/wp-content/plugins/dynamic-year-block/build/dynamic-year-block/block.json`                      | Declares block name `epico/dynamic-year-block` and render file.                                                                         |
| `app/public/wp-content/plugins/animations-for-blocks/animations-for-blocks.php`                             | Registers `anfb/animation-container` and extends supported blocks with animation attributes/context/render mutation. See lines 583-704. |

## Runtime Registered Non-Core Blocks

The live block registry includes many plugin blocks. The most relevant registered
families are:

| Namespace/family                 | Governing plugin        | Notes                                                                                        |
| -------------------------------- | ----------------------- | -------------------------------------------------------------------------------------------- |
| `ecwid/*`, `ec-store/*`          | `ecwid-shopping-cart`   | Store, product, buy-now, category, cart, filters, search, and minicart blocks.               |
| `jetpack/*`, `premium-content/*` | `jetpack`               | Jetpack forms, slideshow, subscriptions, sharing, premium content, and other Jetpack blocks. |
| `epico/dynamic-year-block`       | `dynamic-year-block`    | Dynamic footer year block.                                                                   |
| `anfb/animation-container`       | `animations-for-blocks` | Animation wrapper/container block.                                                           |
| `safe-svg/svg-icon`              | `safe-svg`              | SVG icon block.                                                                              |
| `emailoctopus/form-block`        | `emailoctopus`          | EmailOctopus form block.                                                                     |

## Actual Non-Core Block Usage

Parsed across `post`, `page`, `wp_block`, `wp_template`, `wp_template_part`,
`wp_navigation`, and `blockmeister_pattern` content:

| Block name                 | Count | Example locations                                                                                     |
| -------------------------- | ----: | ----------------------------------------------------------------------------------------------------- |
| `jetpack/slideshow`        |    39 | Page `#1828` draft template, page `#4629` Education Pack Giveaway, BlockMeister pattern `Two Columns` |
| `ecwid/product-block`      |     3 | `wp_block #3816`, `Shop Intro (Using Ecwid Blocks)`                                                   |
| `ecwid/store-block`        |     1 | Page `#565`, `Shop`                                                                                   |
| `ec-store/minicart`        |     1 | Page `#565`, `Shop`                                                                                   |
| `epico/dynamic-year-block` |     1 | `wp_template_part #1026`, `Footer`                                                                    |
| `jetpack/contact-form`     |     1 | `wp_block #4654`, `Contact Form (original) (Copy)`                                                    |
| `jetpack/field-name`       |     1 | `wp_block #4654`, `Contact Form (original) (Copy)`                                                    |
| `jetpack/field-email`      |     1 | `wp_block #4654`, `Contact Form (original) (Copy)`                                                    |
| `jetpack/field-textarea`   |     1 | `wp_block #4654`, `Contact Form (original) (Copy)`                                                    |
| `jetpack/button`           |     1 | `wp_block #4654`, `Contact Form (original) (Copy)`                                                    |

No parsed usage was observed for `anfb/animation-container`,
`safe-svg/svg-icon`, or `emailoctopus/form-block` during this audit.

## Native Synced Patterns / Reusable Blocks

These live in the WordPress database as `wp_block` posts.

|     ID | Title                             | Slug                         | Status    | Last modified         |
| -----: | --------------------------------- | ---------------------------- | --------- | --------------------- |
| `4654` | `Contact Form (original) (Copy)`  | `contact-form-original-copy` | `publish` | `2025-03-12 23:00:05` |
| `3816` | `Shop Intro (Using Ecwid Blocks)` | `shop-intro-copy`            | `publish` | `2023-12-12 14:47:47` |
| `1509` | `Shop Intro`                      | `shop-intro`                 | `publish` | `2025-06-03 11:05:11` |
| `1504` | `Read all about it`               | `read-all-about-it`          | `publish` | `2023-01-26 12:57:54` |
| `1494` | `Connect Social`                  | `connect-social`             | `publish` | `2025-03-14 01:34:29` |
| `1493` | `Contact Form`                    | `contact-form`               | `publish` | `2025-03-12 23:36:27` |

Observed `core/block` references:

| Referenced `wp_block` ID | Title                             | Observed use count | Notes                                                                                                                                                                      |
| -----------------------: | --------------------------------- | -----------------: | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
|                   `1509` | `Shop Intro`                      |                 12 | Used on multiple published pages, including the home page, ArtWorks, Herstories, Educational Resources, Glasgow Herstory Workshops, Workshop, Education Pack Giveaway, and the Mary Barbour template page. |
|                   `1494` | `Connect Social`                  |                  2 | Used in the Page template and one private test page.                                                                                                                       |
|                   `1504` | `Read all about it`               |                  3 | Used in draft/test content.                                                                                                                                                |
|                   `3816` | `Shop Intro (Using Ecwid Blocks)` |                  1 | Used on `Store BLOCK TEST`.                                                                                                                                                |
|                   `1391` | missing                           |                  6 | Stale reference in draft activist/template pages. The referenced `wp_block` no longer exists.                                                                              |

## BlockMeister Patterns

These live in the WordPress database as `blockmeister_pattern` posts. They are
not native synced patterns. BlockMeister registers each published entry as a
normal inserter pattern during runtime.

|     ID | Title                                    | Slug                                  | Status    | Last modified         |
| -----: | ---------------------------------------- | ------------------------------------- | --------- | --------------------- |
| `3227` | `Mary Barbour TEMPLATE PAGE -- June2023` | `mary-barbour-template-page-june2023` | `publish` | `2023-06-01 15:45:07` |
| `2965` | `Previous Next`                          | `previous-next`                       | `publish` | `2023-03-29 14:18:17` |
| `2960` | `Suffragette Stats`                      | `suffragette-stats`                   | `publish` | `2023-03-29 11:31:02` |
| `2850` | `Footer w/nav`                           | `footer-w-nav`                        | `publish` | `2023-03-06 14:19:25` |
| `2848` | `Header w/nav`                           | `header-w-nav`                        | `publish` | `2023-03-06 07:29:27` |
| `2611` | `Blockquote with red line`               | `blockquote-with-red-line`            | `publish` | `2023-02-23 11:00:22` |
| `2267` | `Individual Activist Page`               | `individual-activist-page`            | `draft`   | `2023-01-26 11:48:03` |
| `1506` | `Two Columns`                            | `two-columns`                         | `draft`   | `2022-09-23 08:01:57` |
| `1452` | `Blockquote Cover`                       | `blockquote-cover`                    | `publish` | `2022-09-23 08:22:36` |
| `1451` | `Basic Centred Content`                  | `basic-centred-content`               | `publish` | `2022-09-21 09:15:41` |
| `1445` | `Welcome Header`                         | `welcome-header`                      | `publish` | `2023-03-16 09:40:37` |

Runtime pattern registry names for these were observed as slash-prefixed names
such as `/welcome-header`, `/basic-centred-content`, and
`/individual-activist-page`. That appears to come from BlockMeister's default
namespace handling in this install. The titles and source post IDs above are the
more reliable operational identifiers.

## Site Editor Artifacts

Database-stored templates and template parts also contain deployed block markup.
The most relevant item for non-core block deployment is:

|     ID | Type               | Title    | Status    | Block dependency                     |
| -----: | ------------------ | -------- | --------- | ------------------------------------ |
| `1026` | `wp_template_part` | `Footer` | `publish` | Contains `epico/dynamic-year-block`. |

## Navigation Records

These live in the WordPress database as `wp_navigation` posts. They are not
BlockMeister patterns and are not synced patterns; they store editable menu
content for Navigation blocks.

|     ID | Title            | Role                                                                                   |
| -----: | ---------------- | -------------------------------------------------------------------------------------- |
| `1035` | `Top Nav`        | Header primary navigation.                                                             |
| `1032` | `Navigation`     | Footer navigation.                                                                     |
| `5259` | `Banner CTA Nav` | Cross-site banner CTA labels and URLs; rendered from the code-owned standalone header. |

## Code Migration Recommendations

The goal of moving selected patterns into code would be to make canonical layout
scaffolds version-controlled, reviewable, and easier to reason about without
opening plugin admin UI. This should be done selectively. Moving a pattern into
code removes the BlockMeister/database ownership layer for that layout, but it
does not remove dependencies on any plugin block types contained inside the
pattern.

Good candidates for child-theme pattern files are stable, reusable layout
scaffolds that editors insert as starting points. Poor candidates are synced
content blocks, forms/shop integrations, one-off drafts, or objects that need
global live updates through `core/block` references.

Recommended code target for the standalone theme:

```text
app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/
```

WordPress can auto-register theme patterns from PHP files in that directory.
Use clear project-owned names such as `pns/welcome-header` or
`pns/individual-activist-page` rather than BlockMeister's current slash-prefixed
runtime names.

## 2026-06-24 Standalone Extraction Start

The first standalone extraction slice keeps BlockMeister records in place and
adds code-backed alternatives in the standalone theme. This is intentionally
additive so editors can switch to the `pns/*` pattern names without losing the
existing plugin-managed sources during review.

Theme-owned pattern categories are registered in:

```text
app/public/wp-content/themes/protestsandsuffragettes-standalone/functions.php
```

Code-backed pattern files now live in:

```text
app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/
```

Extracted BlockMeister sources:

| BlockMeister source                  | New code-backed pattern        | Notes                                                  |
| ------------------------------------ | ------------------------------ | ------------------------------------------------------ |
| `Welcome Header` (`#1445`)           | `pns/welcome-header`           | Clean canonical cover/header scaffold.                 |
| `Blockquote Cover` (`#1452`)         | `pns/blockquote-cover`         | Simple reusable quote-cover pattern.                   |
| `Blockquote with red line` (`#2611`) | `pns/blockquote-with-red-line` | Site-specific quote convention with red keyline image. |

Live verification after extraction:

```text
pns/welcome-header | PNS - Welcome Header | pns-layout
pns/blockquote-cover | PNS - Blockquote Cover | pns-quotes
pns/blockquote-with-red-line | PNS - Blockquote With Red Line | pns-quotes
```

The code-backed pattern titles intentionally include the visible `PNS -` prefix
so editors can distinguish them from legacy BlockMeister patterns with similar
names in the inserter.

The matching BlockMeister originals were also title-prefixed on 2026-06-25,
then unpublished after before/after exports:

| ID     | Updated editor title                             | Status  |
| ------ | ------------------------------------------------ | ------- |
| `1445` | `Legacy BlockMeister - Welcome Header`           | `draft` |
| `1452` | `Legacy BlockMeister - Blockquote Cover`         | `draft` |
| `2611` | `Legacy BlockMeister - Blockquote with red line` | `draft` |

Slugs, titles, and content were preserved during the unpublish step. Existing
pages keep rendering their copied core block markup; the draft status only
removes these duplicate BlockMeister sources from future inserter use.

Additional safe BlockMeister starter patterns were extracted on 2026-06-25:

| BlockMeister source               | New code-backed pattern     | BlockMeister status |
| --------------------------------- | --------------------------- | ------------------- |
| `Basic Centred Content` (`#1451`) | `pns/basic-centred-content` | `draft`             |
| `Suffragette Stats` (`#2960`)     | `pns/suffragette-stats`     | `draft`             |
| `Previous Next` (`#2965`)         | `pns/previous-next`         | `draft`             |

`Basic Centred Content` was rewritten with neutral starter copy rather than the
old privacy-policy text. `Suffragette Stats` keeps the starter stats layout but
uses a relative uploads image URL instead of the old local-domain URL.
`Previous Next` was corrected after extraction to keep only the manual
previous/back/next controls with placeholder links. The quote cover from the
original BlockMeister pattern was intentionally removed as inherited design
noise.

## Blessed Editor Pattern Library

The editor inserter is now intentionally strict for registered starter
patterns. As of 2026-06-25, the standalone theme unregisters generic core and
plugin registered patterns after core, Jetpack, and BlockMeister finish their
registration passes. It keeps the PNS code-backed set plus a small transitional
BlockMeister set that still needs explicit adoption or retention decisions.

Allowed registered starter patterns:

| Slug                             | Title                             | Category                | Source of truth                                                                                                  |
| -------------------------------- | --------------------------------- | ----------------------- | ---------------------------------------------------------------------------------------------------------------- |
| `pns/welcome-header`             | `PNS - Welcome Header`            | `pns-layout`            | `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/welcome-header.php`                    |
| `pns/blockquote-cover`           | `PNS - Blockquote Cover`          | `pns-quotes`            | `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/blockquote-cover.php`                  |
| `pns/blockquote-with-red-line`   | `PNS - Blockquote With Red Line`  | `pns-quotes`            | `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/blockquote-with-red-line.php`          |
| `pns/basic-centred-content`      | `PNS - Basic Centred Content`     | `pns-layout`            | `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/basic-centred-content.php`             |
| `pns/suffragette-stats`          | `PNS - Suffragette Stats`         | `pns-layout`            | `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/suffragette-stats.php`                 |
| `pns/previous-next`              | `PNS - Previous Next`             | `pns-layout`            | `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/previous-next.php`                     |
| `pns/two-columns`                | `PNS - Two Columns`               | `pns-layout`            | `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/two-columns.php`                       |
| `pns/activist-hero`              | `PNS - Activist Hero`             | `pns-herstories`        | `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/activist-hero.php`                     |
| `pns/activist-text-media`        | `PNS - Activist Text and Media`   | `pns-herstories`        | `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/activist-text-media.php`               |
| `pns/activist-facts`             | `PNS - Activist Facts`            | `pns-herstories`        | `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/activist-facts.php`                    |
| `pns/activist-image-strip`       | `PNS - Activist Image Strip`      | `pns-herstories`        | `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/activist-image-strip.php`              |

Transitional BlockMeister starter patterns:

| Slug                                    | Title                                  | Category                    | Current source of truth          |
| --------------------------------------- | -------------------------------------- | --------------------------- | -------------------------------- |
| `/mary-barbour-template-page-june2023`  | `Mary Barbour TEMPLATE PAGE -- June2023` | `default`                   | DB `blockmeister_pattern #3227`  |
| `/footer-w-nav`                         | `Footer w/nav`                         | `default`                   | DB `blockmeister_pattern #2850`  |
| `/header-w-nav`                         | `Header w/nav`                         | `default`                   | DB `blockmeister_pattern #2848`  |

Allowed registered pattern categories:

| Slug         | Label         | Source of truth                                                                                     |
| ------------ | ------------- | --------------------------------------------------------------------------------------------------- |
| `pns-layout` | `PNS Layouts` | `app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/patterns.php`                  |
| `pns-quotes` | `PNS Quotes`  | `app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/patterns.php`                  |
| `default`    | `Protests and Suffragettes` | DB BlockMeister `pattern_category` term for unresolved transition items                 |

This cleanup removes generic core/plugin starter pattern categories such as
`Call to action`, `Footers`, `Forms`, `Gallery`, `Headers`, and `Posts` from
the registered pattern inserter surface. BlockMeister's `default` /
`Protests and Suffragettes` category remains visible only because it contains
unresolved BlockMeister transition items that have not yet been adopted into
code or explicitly retired.

`My patterns` remains visible by design. It is not a registered pattern
category. It is WordPress's native synced-pattern/reusable-block surface backed
by `wp_block` database posts. Current `My patterns` source records are:

| ID     | Title                             | Status    | Current source of truth |
| ------ | --------------------------------- | --------- | ----------------------- |
| `1493` | `Contact Form`                    | `publish` | DB `wp_block`           |
| `1494` | `Connect Social`                  | `publish` | DB `wp_block`           |
| `1504` | `Read all about it`               | `publish` | DB `wp_block`           |
| `1509` | `Shop Intro`                      | `publish` | DB `wp_block`           |
| `3816` | `Shop Intro (Using Ecwid Blocks)` | `publish` | DB `wp_block`           |
| `4654` | `Contact Form (original) (Copy)`  | `publish` | DB `wp_block`           |

These entries should be governed under synced-content work before migration.
Moving one to a normal `pns/*` inserter pattern would change its update
semantics from globally synced content to copied starter markup.

## 2026-06-25 Pattern QA

The first three code-backed `pns/*` patterns are now covered by a reproducible
local QA page:

```text
wp eval-file app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/seed-pattern-qa-page.php
```

Seeded page:

|     ID | Title            | Slug             | Status    |
| -----: | ---------------- | ---------------- | --------- |
| `5265` | `PNS Pattern QA` | `pns-pattern-qa` | `publish` |

The QA page is generated from the registered pattern contents for:

- `pns/welcome-header`
- `pns/blockquote-cover`
- `pns/blockquote-with-red-line`
- `pns/basic-centred-content`
- `pns/suffragette-stats`
- `pns/previous-next`

Playwright visual coverage now includes `/pns-pattern-qa/` across desktop,
tablet, and mobile, plus a contract that checks all three patterns render, the
red keyline image loads, and no horizontal overflow is introduced.

## 2026-06-25 Live Adoption Trial

The first live adoption trial used the low-risk published page
`Store BLOCK TEST` (`#3228`) at `/store-block-test/`.

Before editing, the page was exported to:

```text
docs/jobs/live-adoption-db-backups/2026-06-25-page-3228-store-block-test-before-tc3.json
```

After editing, the page was exported to:

```text
docs/jobs/live-adoption-db-backups/2026-06-25-page-3228-store-block-test-after-tc3.json
```

One copied quote-cover section was replaced with the registered
`pns/blockquote-with-red-line` pattern content. WP-CLI verification confirmed
the adopted `core/cover` block serializes exactly to the registered pattern
content.

Visual coverage now includes a focused desktop/tablet/mobile Playwright
screenshot and contract for the adopted quote section on `/store-block-test/`.
The main Mary Barbour biography was intentionally not used for this first trial
because it is a higher-value route even though it contains similar copied
markup.

## BlockMeister Pattern Migration Triage

| BlockMeister pattern                     | Recommendation                                                                              | Reasoning                                                                                                                                                                                            |
| ---------------------------------------- | ------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `Welcome Header`                         | Move to child-theme code                                                                    | Canonical reusable layout scaffold; good fit for a versioned inserter pattern.                                                                                                                       |
| `Basic Centred Content`                  | Move to child-theme code after cleanup                                                      | Generic content scaffold; likely stable, but should use neutral starter copy before becoming a canonical coded pattern.                                                                              |
| `Blockquote Cover`                       | Move to child-theme code                                                                    | Reusable visual layout; simple enough to maintain as code.                                                                                                                                           |
| `Blockquote with red line`               | Move to child-theme code                                                                    | Site-specific visual convention; better versioned with the child theme.                                                                                                                              |
| `Two Columns`                            | Moved to child-theme code as `pns/two-columns`                               | Mostly layout scaffold. The coded pattern intentionally replaces the original `jetpack/slideshow` with a core image placeholder to avoid a plugin dependency in the blessed pattern set.              |
| `Suffragette Stats`                      | Move to child-theme code if this is a recurring page section                                | Appears like a site-specific content module; code is useful if editors reuse the structure.                                                                                                          |
| `Previous Next`                          | Move to child-theme code if still used as a manual navigation section                       | Reusable section pattern; code it if editors still insert it manually. Consider replacing with query/navigation blocks if the behavior should be automatic.                                          |
| `Individual Activist Page`               | Converted to assignable template plus smaller `pns-herstories` section patterns             | The original giant BlockMeister page pattern is too large as one inserter item. The coded replacement separates the page template from reusable hero, text/media, facts, and image-strip sections.     |
| `Mary Barbour TEMPLATE PAGE -- June2023` | Do not move as-is; use only as source material                                              | Looks like a page-specific template snapshot. Fold reusable structure into `Individual Activist Page` instead of preserving a dated duplicate.                                                       |
| `Header w/nav`                           | Prefer template part governance over coded inserter pattern                                 | Header structure should usually live as a template part, not as an editor-inserted content pattern.                                                                                                  |
| `Footer w/nav`                           | Prefer template part governance over coded inserter pattern                                 | Footer structure should usually live as a template part. Current footer also has site-editor state and `epico/dynamic-year-block`.                                                                   |

## Native `wp_block` Migration Triage

| `wp_block` item                             | Recommendation                                                                 | Reasoning                                                                                                                                                |
| ------------------------------------------- | ------------------------------------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `Shop Intro` (`#1509`)                      | Keep as native synced pattern for now                                          | It has 12 observed live references. Global update behavior appears valuable and moving it to a copied coded pattern would change update semantics.       |
| `Connect Social` (`#1494`)                  | Keep as native synced pattern or move into a template/part if it is structural | It is referenced by the Page template and a private test page. If it is site-wide structure, template governance may be better than an inserter pattern. |
| `Read all about it` (`#1504`)               | Review usage before moving                                                     | Observed references are draft/test content. It may be removable, legacy, or a candidate for coded pattern only if still editorially useful.              |
| `Shop Intro (Using Ecwid Blocks)` (`#3816`) | Keep database/plugin-managed unless actively productized                       | Contains `ecwid/product-block` instances and is used only on `Store BLOCK TEST`; keep close to Ecwid/plugin workflow unless this becomes canonical.      |
| `Contact Form` (`#1493`)                    | Keep database/plugin-managed                                                   | Uses shortcode-style form content; form configuration is plugin/content-owned.                                                                           |
| `Contact Form (original) (Copy)` (`#4654`)  | Keep database/plugin-managed short term; consider consolidating or retiring    | Contains Jetpack form blocks and appears duplicative; form behavior remains plugin-owned.                                                                |
| Missing reference `#1391`                   | Clean up, do not migrate                                                       | Stale `core/block` reference in draft content. Replace or remove before reviving those drafts.                                                           |

2026-06-25 note for `#3816`:

- `/store-block-test/` emitted a visible PHP warning before the live pattern
  adoption trial.
- Backtrace showed Ecwid's product shortcode processing received an
  `animationsForBlocks` array attr from the first `ecwid/product-block`.
- `#3816` was exported before and after cleanup under
  `docs/jobs/live-adoption-db-backups/`.
- The cleanup removed only that non-Ecwid animation metadata attr. The Ecwid
  product IDs and display settings were left unchanged.

## Suggested Migration Sequence

1. Export or copy the current BlockMeister pattern markup for the recommended
   migration candidates.
2. Create child-theme pattern files under
   `app/public/wp-content/themes/protestsandsuffragettes/patterns/`.
3. Use a stable project namespace, probably `pns/*`, for new pattern slugs.
4. Keep plugin block dependencies explicit in the pattern description when a
   coded pattern still contains plugin blocks such as `jetpack/slideshow`.
5. Verify the new coded patterns appear in the editor inserter.
6. Mark matching BlockMeister patterns as legacy, or remove them after confirming
   editors no longer need the plugin-managed copies.
7. Leave high-value synced content in `wp_block` when global update behavior is
   intentional.

## Risks and Maintenance Notes

- Do not treat plugin block output as child-theme-owned code. The child theme
  styles and filters block output but does not own the block registrations.
- Plugin dependency matters. Pages using Ecwid, Jetpack, Dynamic Year, or other
  plugin blocks depend on those plugins staying active and compatible.
- Export/import must include database content, not just tracked files. The
  important content objects are `wp_block`, `blockmeister_pattern`,
  `wp_template`, and `wp_template_part`.
- Editing `wp_block #1509` changes all pages that reference `Shop Intro`.
  Editing a BlockMeister pattern changes the inserter source, but generally does
  not update pages where the pattern was already inserted as copied markup.
- The stale `core/block` reference to missing ID `1391` is limited to draft
  content in this audit, but should be cleaned before reviving those drafts.

## 2026-06-25 Closeout Note

- `wp_block #1509` remains the intentionally synced owner for `Shop Intro`.
- Current live counts used for extraction closeout are:
  - `#1509` / `Shop Intro`: 12;
  - `#1494` / `Connect Social`: 2;
  - `#1504` / `Read all about it`: 3;
  - `#3816` / `Shop Intro (Using Ecwid Blocks)`: 1;
  - `#4654` / `Contact Form (original) (Copy)`: 0.
- Plugin dependency reduction is deferred until a later proof-backed task; the
  live dependency map still includes BlockMeister, Ecwid, Jetpack, Dynamic Year,
  and Animations for Blocks.

## Evidence Commands

Run database-backed WP-CLI commands with elevated local-service access from
Codex if the restricted shell cannot reach Local's database socket.

```bash
wp plugin list --status=active --fields=name,version --format=csv
wp post list --post_type=wp_block --post_status=any --fields=ID,post_title,post_name,post_status,post_modified --format=csv
wp post list --post_type=blockmeister_pattern --post_status=any --fields=ID,post_title,post_name,post_status,post_modified --format=csv
```

Runtime block and usage inventories were captured with `wp eval` using
`WP_Block_Type_Registry::get_instance()->get_all_registered()` and
`parse_blocks()` across public content, reusable blocks, templates, template
parts, navigation posts, and BlockMeister pattern posts.
