# Visual Column Layout Tracker

Created on 2026-06-28 for `protestsandsuffragettes-standalone`.

## Layout Contract

- `pns-section-inner`: max-width-only frame for full-bleed Cover sections.
- `pns-section-frame`: max-width plus responsive inline padding for regular sections.
- `pns-copy-column`: written-content measure from `theme.json` `contentSize`.
- `pns-hero-copy`: hero copy placement inside a full-bleed Cover.
- `pns-header__inner`: header-specific visual frame that keeps logo and
  navigation inside the same wide visual column.

## Migration Evidence

- Script:
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/normalize-visual-column-layout.php`
- Backup:
  `docs/jobs/live-adoption-db-backups/2026-06-28-122508-visual-column-normalize-before.json`
- Report:
  `docs/jobs/live-adoption-db-backups/2026-06-28-122508-visual-column-normalize-after-report.json`
- Post-apply dry-run:
  `0` matching records.

## Main Navigation Tracker

| Route                     | Source                    | Status          | Notes                                                                       |
| ------------------------- | ------------------------- | --------------- | --------------------------------------------------------------------------- |
| `/`                       | Page `49`                 | Updated earlier | Homepage hero is canonical reference; geometry checked.                     |
| `/artworks/`              | Page `1066`               | Updated         | Legacy hero frame collapsed to `pns-section-inner` / `pns-hero-copy`.       |
| `/about/`                 | Page `1789`               | Updated         | Legacy hero frame collapsed to `pns-section-inner` / `pns-hero-copy`.       |
| `/herstories/`            | Page `1783`               | Updated         | Legacy hero frame collapsed to `pns-section-inner` / `pns-hero-copy`.       |
| `/shenanigans/`           | Page `2363`               | Updated         | Legacy hero frame collapsed to `pns-section-inner` / `pns-hero-copy`.       |
| `/educational-resources/` | Page `1786`               | Updated         | Legacy hero frame collapsed to `pns-section-inner` / `pns-hero-copy`.       |
| `/news/`                  | Page `5190` / posts route | Skipped         | No matching legacy hero frame in migration scope.                           |
| `/shop/`                  | Page `565`                | Skipped         | No matching legacy hero frame; contact/footer geometry checked.             |
| `/#contact`               | Template part `4666`      | Updated         | Saved DB template part replaced from code-owned `pns-section-frame` source. |

## Additional Published Content Updated

| Route                                          | Source              | Status  | Notes                                                   |
| ---------------------------------------------- | ------------------- | ------- | ------------------------------------------------------- |
| `/herstories/mary-barbour/`                    | Page `42`           | Updated | Legacy activist hero frame collapsed; geometry checked. |
| `/store-block-test/`                           | Page `3228`         | Updated | Legacy activist hero frame collapsed.                   |
| `/glasgow-herstory-workshops/`                 | Page `3677`         | Updated | Legacy hero frame collapsed.                            |
| `/workshop-unleashing-the-suffragette-spirit/` | Page `4501`         | Updated | Legacy hero frame collapsed.                            |
| `/edu-giveaway/`                               | Page `4629`         | Updated | Legacy hero frame collapsed.                            |
| `wp_block:contact-form`                        | Synced block `1493` | Updated | Replaced from code-owned synced-pattern source.         |
| `wp_block:contact-form-original-copy`          | Synced block `4654` | Updated | Replaced from code-owned synced-pattern source.         |

## Verification

- PHP syntax passed for `normalize-visual-column-layout.php`.
- Block template validation passed for 13 files.
- Direct Lightning CSS compile passed for frontend and editor bundles.
- Stylelint passed for authored CSS.
- `git diff --check` passed.
- Frontend geometry probe checked `/`, `/herstories/`, `/about/`,
  `/artworks/`, `/educational-resources/`, `/shop/`, and
  `/herstories/mary-barbour/` at `1440px` and `390px`.
- Authenticated editor Playwright harness was attempted but skipped because no
  editor login URL or credentials are configured in this shell.

## 2026-06-29 Header And Editor Pattern Follow-Up

- Header wrapper correction added `pns-header__inner` so the logo and primary
  navigation are governed by the visual column instead of sitting against the
  browser edge at medium/small widths.
- Navigation breakpoint coverage now verifies the desktop navigation switches
  before it can overlap the logo.
- Editor stylesheet loading was corrected so the compiled editor bundle is
  actually available in the block-editor canvas.
- Editor-only canvas parity now lets the Pattern QA wrapper and full-width
  PNS sections expand across the editor canvas instead of being held to the
  `contentSize` column.
- `theme.json` now declares uppercase `core/heading` typography so the editor
  and frontend share the same block-level heading default.
- Authenticated editor Playwright harness now passes against the private editor
  fixture plus real page smokes for `/`, `/herstories/mary-barbour/`,
  `/edu-giveaway/`, `/shop/`, and `/pns-pattern-qa/`.
- Verification on 2026-06-29:
  - Editor Playwright: `6 passed`.
  - Frontend visual Playwright: `103 passed`, `2 skipped`.
  - Stylelint passed for authored CSS.
  - Block template validation passed for 13 files.
  - PHP syntax passed for `inc/assets.php` and `scripts/seed-editor-fixture.php`.
  - Prettier passed for touched CSS, JSON, and TS files.
  - `git diff --check` passed.
