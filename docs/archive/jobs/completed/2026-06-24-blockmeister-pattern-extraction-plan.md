# BlockMeister Pattern Extraction Plan

Plan started on 2026-06-24.

All paths are relative to the project root.

## Goal

Reduce reliance on BlockMeister-managed click-ops for canonical site structures
by moving selected pattern sources into deterministic, reviewable standalone
theme code.

This is not a blanket removal of BlockMeister. Some structures are editorial
starter content and can remain client-editable. Others are canonical layout
scaffolds that should be owned in Git.

## Current Ownership Model

| Source                    | Storage                                                                          | Behavior                                                                         | Migration stance                                                        |
| ------------------------- | -------------------------------------------------------------------------------- | -------------------------------------------------------------------------------- | ----------------------------------------------------------------------- |
| BlockMeister patterns     | `blockmeister_pattern` posts                                                     | Plugin registers inserter patterns at runtime; inserted content is copied markup | Selectively replace stable scaffolds with theme-owned `pns/*` patterns. |
| Native synced patterns    | `wp_block` posts                                                                 | `core/block` refs update globally                                                | Keep when global update behavior is valuable.                           |
| Standalone theme patterns | `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/*.php` | Code-backed inserter patterns registered by the standalone theme                 | Preferred target for canonical reusable starter layouts.                |
| Template parts            | `parts/*.html` plus possible saved DB overrides                                  | Structural chrome                                                                | Prefer template governance, not inserter patterns, for header/footer.   |

## Selection Criteria

Move to standalone theme code when:

- the pattern is a stable reusable scaffold;
- the content is not expected to vary client-to-client after insertion;
- the structure is better reviewed through Git than plugin UI;
- plugin dependencies are minimal or explicitly acceptable.

Keep in BlockMeister or defer when:

- the pattern is a page-specific snapshot;
- the content is stale, draft, or placeholder-heavy;
- the pattern depends heavily on plugin blocks that need separate ownership
  decisions;
- the object should be a template part, custom block, or synced pattern instead
  of an inserter pattern.

## First Extraction Slice

Completed on 2026-06-24:

| BlockMeister source                  | Code-backed replacement        | Category     |
| ------------------------------------ | ------------------------------ | ------------ |
| `Welcome Header` (`#1445`)           | `pns/welcome-header`           | `pns-layout` |
| `Blockquote Cover` (`#1452`)         | `pns/blockquote-cover`         | `pns-quotes` |
| `Blockquote with red line` (`#2611`) | `pns/blockquote-with-red-line` | `pns-quotes` |

Implementation files:

- `app/public/wp-content/themes/protestsandsuffragettes-standalone/functions.php`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/welcome-header.php`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/blockquote-cover.php`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/blockquote-with-red-line.php`

Runtime verification:

```text
pns/welcome-header | PNS - Welcome Header | pns-layout
pns/blockquote-cover | PNS - Blockquote Cover | pns-quotes
pns/blockquote-with-red-line | PNS - Blockquote With Red Line | pns-quotes
```

The visible `PNS -` title prefix is intentional. It lets editors distinguish
theme-owned code-backed patterns from similarly named BlockMeister legacy
patterns while keeping the stable slugs under the `pns/` namespace.

2026-06-25 QA verification:

- Added `scripts/seed-pattern-qa-page.php`.
- Seeded page `PNS Pattern QA` (`#5265`) at `/pns-pattern-qa/`.
- Added desktop/tablet/mobile Playwright snapshot coverage for the QA route.
- Added a computed contract that verifies the welcome header, both quote
  patterns, the red keyline image, loaded images, and horizontal overflow.
- Full visual suite passed with the QA route included.

The BlockMeister originals remain in place for now. This avoids breaking editor
workflows while the team confirms the code-backed `pns/*` replacements in the
inserter.

2026-06-25 live adoption trial:

- Selected published test-like page `Store BLOCK TEST` (`#3228`) at
  `/store-block-test/`.
- Exported the page before and after the edit under
  `docs/jobs/live-adoption-db-backups/`.
- Replaced one copied quote-cover section with the registered
  `pns/blockquote-with-red-line` pattern content.
- Verified through WP-CLI that the adopted section serializes exactly to the
  registered pattern content.
- Added focused Playwright coverage for the adopted quote section across
  desktop, tablet, and mobile.

This proves the replacement workflow without touching high-value biography
pages or global chrome. It does not yet mean the matching BlockMeister pattern
can be deleted.

2026-06-25 additional safe starter extraction:

- Added `pns/basic-centred-content`, `pns/suffragette-stats`, and
  `pns/previous-next` to the standalone theme.
- Updated Pattern QA to render all six `pns/*` patterns.
- Unpublished the matching BlockMeister source posts by setting them to
  `draft`:
  - `Basic Centred Content` (`#1451`);
  - `Suffragette Stats` (`#2960`);
  - `Previous Next` (`#2965`).
- Kept `Two Columns`, `Individual Activist Page`, `Mary Barbour TEMPLATE PAGE
-- June2023`, `Header w/nav`, and `Footer w/nav` out of this extraction
  slice because they require plugin-dependency, template, or global-chrome
  decisions.

2026-06-25 editor library cleanup:

- The unresolved BlockMeister source patterns were exported during the initial
  cleanup and then restored to `publish` after review, because they have not
  yet been adopted into code or explicitly retired:
  - `Mary Barbour TEMPLATE PAGE -- June2023` (`#3227`);
  - `Footer w/nav` (`#2850`);
  - `Header w/nav` (`#2848`);
  - `Individual Activist Page` (`#2267`);
  - `Two Columns` (`#1506`).
- The standalone theme now enforces a blessed registered pattern library after
  BlockMeister completes its category/pattern registration. The active registry
  allows `pns/*` theme-owned starter patterns plus those unresolved
  BlockMeister transition items, while continuing to hide generic core/plugin
  pattern noise.
- Native `wp_block` synced patterns still appear under `My patterns`; that is a
  separate WordPress content surface, not BlockMeister.

## Current Triage

Completed code-backed starter patterns:

- `Welcome Header`
- `Basic Centred Content`
- `Blockquote Cover`
- `Blockquote with red line`
- `Suffragette Stats`
- `Previous Next`

Published BlockMeister transition sources:

- `Two Columns`: contains `jetpack/slideshow`; only revisit after deciding
  whether PNS wants a Jetpack-backed or core-media replacement.
- `Individual Activist Page`: page/template-scale structure; revisit under a
  dedicated template or page-scaffold phase, not as a generic pattern.
- `Mary Barbour TEMPLATE PAGE -- June2023`: page-specific snapshot; use only as
  reference material.
- `Header w/nav` and `Footer w/nav`: govern through standalone template parts,
  not editor inserter patterns.

Drafted BlockMeister duplicates with code-backed replacements:

- `Welcome Header`
- `Basic Centred Content`
- `Blockquote Cover`
- `Blockquote with red line`
- `Suffragette Stats`
- `Previous Next`

## Rollout Steps

1. Confirm the three `pns/*` patterns appear in the editor inserter.
2. Ask editors to use the `PNS Layouts` and `PNS Quotes` categories for new
   content instead of the matching BlockMeister entries.
3. After one editing pass, rename BlockMeister duplicates with a clear `Legacy`
   prefix or remove them after export.
4. Continue with one next candidate at a time, starting with `Suffragette Stats`
   only after its content is generalized.

## Validation Commands

```bash
php -l app/public/wp-content/themes/protestsandsuffragettes-standalone/functions.php
php -l app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/welcome-header.php
php -l app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/blockquote-cover.php
php -l app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/blockquote-with-red-line.php
wp eval '$patterns = WP_Block_Patterns_Registry::get_instance()->get_all_registered(); foreach ( $patterns as $pattern ) { $name = $pattern["name"] ?? ""; if ( 0 === strpos( $name, "pns/" ) ) { echo $name . " | " . ( $pattern["title"] ?? "" ) . " | " . implode( ",", $pattern["categories"] ?? array() ) . "\n"; } }'
```
