# Page Rebuild Proof

Date: 2026-06-29

Dex task: `01n5msi3`

## Purpose

Before Phase 5 utility retirement, prove whether key live pages can be rebuilt
from core blocks, available plugin blocks, registered standalone patterns, and
synced patterns.

This is a proof step, not a live content replacement. Live page markup should
not be replaced until the rebuilt draft has been visually compared and reviewed.

## Scope

Pages under proof:

- Mary Barbour
- Front page

The proof must record where a rebuild uses:

- one existing code-backed pattern;
- one existing synced pattern;
- a combination of basic blocks and patterns;
- a missing reusable pattern;
- an intentional one-off.

Repeated combinations should become candidate prebuilt patterns before Phase 5
removes supporting utility classes.

## Mary Barbour Draft

Seed command:

```bash
wp eval-file app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/seed-rebuild-proof-pages.php mary
```

Draft page:

```text
ID: 5421
slug: pns-rebuild-proof-mary-barbour
title: PNS Rebuild Proof - Mary Barbour
status: draft
```

Audit result after seeding:

```text
Page pattern audit checked 232 section candidate(s); 0 need review.
```

Mary rebuild section map:

| Rebuild section    | Source used                                               | Current proof status                                                                                                                                           |
| ------------------ | --------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Hero               | `pns/activist-hero` with Mary content/image substitutions | Pattern exists; needs visual comparison against live hero focal point and copy density.                                                                        |
| Leading Women      | `pns/activist-text-media` with text substitutions         | Pattern exists, but live section is closer to a text-focused or empty-media variant.                                                                           |
| Image strip        | `pns/activist-image-strip`                                | Pattern exists, but live Mary uses an uneven two-image strip while current pattern is a three-image strip.                                                     |
| Fun Facts          | `pns/activist-facts` with text substitutions              | Pattern exists, but live Mary places media before copy; current pattern places copy before media.                                                              |
| Background         | `pns/activist-text-media` with text substitutions         | Pattern exists, but live section uses edge-to-edge media/right-column behavior.                                                                                |
| Full-width image   | `pns/image-strip` edited down to one image                 | Pattern exists; single-image instances now use the canonical image-strip wrapper rather than a separate pattern.                                                |
| Quote              | `pns/blockquote-with-red-line`                            | Pattern exists.                                                                                                                                                |
| More about Mary    | Composed core group/columns/paragraphs                    | Candidate pattern. The live section has old `shop-intro` classes, but semantically it is biography/further-reading content, not the synced shop-intro pattern. |
| Gray quote wrapper | Composed wrapper around `pns/blockquote-with-red-line`    | Candidate pattern. The red-line quote pattern exists, but live Mary wraps one quote in a gray background section.                                              |
| Previous/Next      | `pns/previous-next`                                       | Pattern exists; links/content still need final substitution.                                                                                                   |
| Final shop intro   | synced `shop-intro`                                       | Pattern exists.                                                                                                                                                |

## Candidate Pattern Gaps

The Mary and front-page proofs are rebuildable in broad structure, but not yet
editor-proof for exact page recreation. The important naming conclusion is that
most gaps are not activist-only. They are section-layout primitives reused by
the home page, `/artworks/`, `/educational-resources/`, `/shenanigans/`, and
profile pages.

General/site-wide primitives to consider:

| Candidate                         | Replaces earlier name                                      | Why                                                                                                                                                                      |
| --------------------------------- | ---------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `pns/text-only-section`           | `pns/activist-text-only`                                   | Live `Leading Women` style sections can be text-first with an empty/absent media column, but the same plain text band shape can appear outside Herstory/profile content. |
| `pns/split-media-text-left`       | `pns/activist-text-media-edge-left`                        | Front-page `Herstories`, `/artworks/`, and other listing-style pages need image-left/text-right section variants.                                                        |
| `pns/split-media-text-right`      | `pns/activist-text-media-edge-right`                       | Live background/Wikipedia-style sections need text-left/media-right variants without relying on ad hoc utility stacks.                                                   |
| `pns/split-media-text-edge-left`  | edge-left variant of `pns/activist-text-media-edge-left`   | Edge-to-edge sections use structural classes such as `vw-100`, `no-gap`, `lh0`, and `w-100`; this should be an explicit primitive when media bleeds to the section edge. |
| `pns/split-media-text-edge-right` | edge-right variant of `pns/activist-text-media-edge-right` | Front-page `Our work with wikipedia` proves the edge media treatment is site-wide, not activist-specific.                                                                |
| `pns/split-slideshow-text-left`   | new front-page gap                                         | Front-page `What we do` is a Jetpack slideshow plus copy/button section; no current registered pattern covers the whole editor affordance.                               |
| `pns/image-strip-uneven`          | `pns/activist-image-strip-uneven`                          | Live Mary uses a two-column 33/66 image strip, but uneven image strips are a general media primitive.                                                                    |
| `pns/quote-band-muted`            | `pns/blockquote-with-gray-wrapper`                         | A gray or muted wrapper around the red-line quote is a general quote/layout treatment, not profile-specific.                                                             |

Pattern versus block-style decision:

- `pns/split-media-text-left`, `pns/split-media-text-right`,
  `pns/split-media-text-edge-left`, and `pns/split-media-text-edge-right` may
  indicate inconsistent historical implementation rather than four genuinely
  different reusable patterns.
- Treat those four as a normalization target first. The next implementation
  pass should compare the required block tree, image behavior, responsive
  stacking, and editor controls before deciding whether they become:
  - one base split-section pattern with theme-published block styles;
  - two orientation patterns plus edge/non-edge block styles;
  - four explicit patterns because the nested markup materially differs.
- Block styles are acceptable here because they can be registered and shipped
  by the theme, exposed in the editor style picker, and backed by theme CSS.
  Use them when the same block structure remains valid and only presentation or
  layout variant changes.
- Use a full pattern when editors need a prebuilt nested structure, required
  plugin block, starter copy, fixed semantic hook, or block arrangement that is
  easy to break when assembled from standard blocks.
- `pns-two-columns` should be deprecated as a future editor-facing identity and
  removed or migrated from saved content after replacements exist. It currently
  hides too many different layouts behind one class, which makes CSS cleanup and
  editor recreation fragile.

Implementation slice started in Dex task `cynp3u0k`:

- Added theme-owned `core/group` block styles for PNS media-left, media-right,
  edge-media-left, and edge-media-right split sections.
- Added code-backed starter patterns for `pns/split-section-image`,
  `pns/split-section-slideshow`, and `pns/text-only-section`.
- New patterns use semantic split-section classes instead of the legacy
  `grid`, `m-auto`, `no-gap`, `vw-100`, `lh0`, and `w-100` utility stack.
- `pns/two-columns` remains registered for compatibility, but its pattern
  description now directs new content to the split-section patterns.
- The Mary and front-page proof drafts were reseeded so text-only and split
  sections use the new patterns.
- Completed source-API child task `or591vkb`.
- Completed saved-content migration child task `6ck02c9j`: the front page,
  about, artworks, herstories, educational resources, shenanigans, the two
  shenanigans child pages, edu giveaway, and local pattern QA page now use
  `pns-split-section` plus explicit normal/edge media-left/media-right block
  styles instead of `pns-two-columns`. `/news/` remains excluded from this audit
  and migration.
- Migration rollback/report evidence:
  `docs/jobs/live-adoption-db-backups/2026-06-29-153156-split-section-classes-before.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-153156-split-section-classes-after-report.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-153501-split-section-classes-before.json`,
  and
  `docs/jobs/live-adoption-db-backups/2026-06-29-153501-split-section-classes-after-report.json`.
- Verification after migration: split-section migration dry-run reported
  `0` records and `0` section candidates; direct DB query found no non-news
  `pns-two-columns` page content; parsed split-section wrapper check found only
  one false positive, editorial text `Yardworks GRID`; page-pattern audit
  reported `232` section candidates and `0` needing review.
- Visual verification: full Playwright suite passed after accepting the expected
  mobile height/spacing change from replacing the legacy utility stack with the
  split-section component (`142` passed, `2` skipped). Only the mobile `home`
  and `edu-giveaway` baselines were refreshed.

Recognized template-draft alignment and original-content draft rollback in Dex
task `bt71qkpe`:

- Fixed vertical rhythm for the live `Connect With Us` and `Stay in Touch`
  sections with a scoped CSS owner on `pns-connect-social`,
  `pns-contact-form`, and `pns-contact-form-octopus`. The top-level semantic
  section classes were preserved.
- Bulk-copied only the disposable `**TEMPLATE*` draft pages from canonical
  pattern-backed pages: `1758 <- 49` front page, `1761 <- 1066` ArtWorks, and
  `1828 <- 42` Mary Barbour. The `**Template Placeholder` parent was left as an
  admin sorting stub.
- Initial template-draft migration evidence:
  `docs/jobs/live-adoption-db-backups/2026-06-29-170456-recognized-draft-pages-before.json`
  and
  `docs/jobs/live-adoption-db-backups/2026-06-29-170456-recognized-draft-pages-after-report.json`.
- The original-content draft pages `2874`, `1833`, `1848`, `1855`, `1869`, and
  `1797` were accidentally included in the first pass, then immediately
  restored from the pre-copy backup. Restore evidence:
  `docs/jobs/live-adoption-db-backups/2026-06-29-172437-recognized-draft-pages-original-content-restore-before.json`
  and
  `docs/jobs/live-adoption-db-backups/2026-06-29-172437-recognized-draft-pages-original-content-restore-after-report.json`.
- Corrected verification: the align script now targets only the three
  `**TEMPLATE*` drafts, and post-restore dry-run reported those three targets
  `changed=no`. The six restored original-content drafts remain a manual
  alignment queue where text and images must be preserved while section wrappers
  are brought onto the new pattern identities.
- Dex task `xvr5eg5y` records the rollback correction. Dex task `97p9lsc4`
  tracks the pending manual alignment pass for restored original-content draft
  pages `2874`, `1833`, `1848`, `1855`, `1869`, and `1797`.
- Targeted Playwright checks passed (`12` passed) after accepting the expected
  homepage tablet/mobile snapshot height change from the added synced-section
  rhythm.

Manual original-content draft alignment in Dex task `97p9lsc4`:

- Applied a parser-backed, ID-scoped content migration for restored draft pages
  `2874`, `1833`, `1848`, `1855`, `1869`, and `1797`. The migration preserves
  existing text, media, links, draft status, and editorial anomalies while
  normalizing wrapper identities.
- Profile drafts now use `pns/activist-hero`-equivalent cover structure with
  `jumbo-header pns-section pns-herstories pns-activist-hero`.
- The Wikipedia draft now uses the `pns/page-hero` cover identity and preserves
  its hero focal point `82% 35%`.
- Body sections were aligned to the closest current identities:
  `pns-activist-text-media`, `pns-activist-facts`,
  `pns-activist-image-strip`, `pns-image-strip`,
  `pns-previous-next`, `pns-shop-intro`, and for the Wikipedia draft,
  `pns-split-section` with media-left/edge-media-right styles.
- Migration evidence:
  `docs/jobs/live-adoption-db-backups/2026-06-29-174655-original-draft-pattern-identities-before.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-174655-original-draft-pattern-identities-after-report.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-175104-original-draft-pattern-identities-before.json`,
  and
  `docs/jobs/live-adoption-db-backups/2026-06-29-175104-original-draft-pattern-identities-after-report.json`.
- Verification: final dry-run reported all six targets `changed=no`; page
  pattern audit checked `224` section candidates with `0` needing review; PHP
  lint and `git diff --check` passed.
- Remaining content cleanup is intentionally not folded into this migration:
  five profile drafts still contain stale unresolved reusable block reference
  `1391`, which prior audit guidance classifies as "clean up, do not migrate."
  The draft content also retains editorial anomalies such as the
  Lila/Mary-body mismatch, blank stats, and placeholder copy where present.
- Follow-up Dex task `rnl1f0yo` fixed sections that had the correct identity
  class but still used old wide/full nested layout wrappers. Source patterns
  `pns/suffragette-stats` and `pns/previous-next` were first moved to
  `pns-section-inner`, but user review clarified that this was still too wide
  because `pns-section-inner` is the site-frame wrapper, not the `44rem`
  `contentSize` column. Follow-up Dex task `h4s3yx7w` corrects that contract:
  stat/control frames now use `pns-content-frame`, while Herstories
  text/media and facts sections stay on the wider Herstories layout contract so
  they visually match the live profile pages.
- Restored draft text-media sections and facts sections now keep
  `pns-section-inner`; `SUFFRAGETTE STATS` sections are mapped to
  `pns-suffragette-stats` with `pns-content-frame`; previous/next controls are
  framed by `pns-content-frame`; and the Wikipedia draft split sections now
  include `pns-split-section__copy`/copy-column/media-column structure.
- Content-size correction evidence:
  `docs/jobs/live-adoption-db-backups/2026-06-29-180814-original-draft-pattern-identities-before.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-180814-original-draft-pattern-identities-after-report.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-184200-original-draft-pattern-identities-before.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-184200-original-draft-pattern-identities-after-report.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-185815-original-draft-pattern-identities-before.json`,
  and
  `docs/jobs/live-adoption-db-backups/2026-06-29-185815-original-draft-pattern-identities-after-report.json`.
  A later shape-based correction moved copy-only profile sections to
  `pns-text-only-section`, moved copy+media profile sections to
  `pns-split-section is-style-pns-media-right`, kept facts/stats/image strips
  on their own pattern identities, and rebuilt stale static group shells so the
  new wrappers actually enclose their inner blocks. Additional evidence:
  `docs/jobs/live-adoption-db-backups/2026-06-29-190816-original-draft-pattern-identities-before.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-190816-original-draft-pattern-identities-after-report.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-191226-original-draft-pattern-identities-before.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-191226-original-draft-pattern-identities-after-report.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-191651-original-draft-pattern-identities-before.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-191651-original-draft-pattern-identities-after-report.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-192301-original-draft-pattern-identities-before.json`,
  and
  `docs/jobs/live-adoption-db-backups/2026-06-29-192301-original-draft-pattern-identities-after-report.json`.
  A final serialization pass rebuilt migrated split-section column shells so
  saved HTML matches the reusable `pns/split-section-image` contract: direct
  spacer blocks were removed, columns are `alignfull`, and inherited `66/33`
  column widths were cleared. Final evidence:
  `docs/jobs/live-adoption-db-backups/2026-06-29-193923-original-draft-pattern-identities-before.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-193923-original-draft-pattern-identities-after-report.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-194615-original-draft-pattern-identities-before.json`,
  and
  `docs/jobs/live-adoption-db-backups/2026-06-29-194615-original-draft-pattern-identities-after-report.json`.
  Final dry-run reported all six targets `changed=no`. Authenticated evidence
  on draft `2874` showed `The Dundee Heckler` and `More about Mary` as
  `pns-text-only-section` with a `704px` inner column, `Background` as
  `pns-split-section is-style-pns-media-right` with a `2048px` full split
  columns wrapper, `704px` copy well, and `1024px` media column, and
  previous/next on `pns-content-frame`.
- User review of draft `1833` then exposed remaining stale facts/stats hacks:
  the Agnes facts section still had direct spacer/empty paragraph blocks,
  inherited `66/33` column widths, and no `m-auto pns-copy-column` wrapper; the
  "quote here? - as no suffragette stats?" section was neither valid stats nor
  a quote pattern. Follow-up Dex task `lcg0ij6i` updated the migration to
  normalize restored draft facts sections to the `pns/activist-facts` source
  shape, remove empty spacer/`<br>` blocks, preserve real images, and remove the
  invalid Agnes placeholder section. An initial apply exposed a serializer bug
  for empty column attrs; the five changed posts were restored from
  `docs/jobs/live-adoption-db-backups/2026-06-29-215935-original-draft-pattern-identities-before.json`
  before the corrected migration was reapplied. Final follow-up evidence:
  `docs/jobs/live-adoption-db-backups/2026-06-29-220600-original-draft-pattern-identities-before.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-220600-original-draft-pattern-identities-after-report.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-221014-original-draft-pattern-identities-before.json`,
  and
  `docs/jobs/live-adoption-db-backups/2026-06-29-221014-original-draft-pattern-identities-after-report.json`.
  Final dry-run again reported all six targets `changed=no`; saved block
  inspection for `1833` showed `More about agnes` as `pns-split-section` and
  `Fun Facts about agnes` as `pns-activist-facts` with a direct
  `no-gap pns-section-inner` columns frame, `m-auto pns-copy-column` copy
  wrapper, and preserved image column.
  User editor review then showed the touched Agnes sections as invalid in
  Gutenberg. Follow-up serializer cleanup removed stale generated attrs from
  migrated sections (`wp-block-heading` comment attrs, empty
  `style.color` arrays, empty `animationsForBlocks`, stale split-column style
  attrs), fixed the nested empty-paragraph remover, and rebuilt normalized hero
  heading/active-date markup so saved comments and HTML match. Evidence:
  `docs/jobs/live-adoption-db-backups/2026-06-29-224451-original-draft-pattern-identities-before.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-224451-original-draft-pattern-identities-after-report.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-224758-original-draft-pattern-identities-before.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-224758-original-draft-pattern-identities-after-report.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-225102-original-draft-pattern-identities-before.json`,
  and
  `docs/jobs/live-adoption-db-backups/2026-06-29-225102-original-draft-pattern-identities-after-report.json`.
  Independent verifier review then found two remaining wrapper-shell issues:
  nested `pns-split-section__copy` and `pns-text-only-section__inner` groups had
  block comments but no saved wrapper `<div>`. The migration now rebuilds nested
  `core/group`, `core/columns`, and `core/column` shells for migrated sections,
  with column widths preserved as `flex-basis`. Final wrapper evidence:
  `docs/jobs/live-adoption-db-backups/2026-06-29-225713-original-draft-pattern-identities-before.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-225713-original-draft-pattern-identities-after-report.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-225833-original-draft-pattern-identities-before.json`,
  and
  `docs/jobs/live-adoption-db-backups/2026-06-29-225833-original-draft-pattern-identities-after-report.json`.
  Final verification: migration dry-run reported all six targets `changed=no`;
  Agnes saved-content checks found the split copy wrapper, text-only inner
  wrapper, facts copy wrapper, and corrected `h1` hero heading present;
  targeted WP-CLI scan checked `46` migrated draft sections with `0` stale
  serialization markers. A final safety pass applied the same serialization
  cleanup to the profile-draft `pns-shop-intro` sections so the editor page is
  not left with stale generated attrs in the legacy shop block. Evidence:
  `docs/jobs/live-adoption-db-backups/2026-06-29-230632-original-draft-pattern-identities-before.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-230632-original-draft-pattern-identities-after-report.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-29-230844-original-draft-pattern-identities-before.json`,
  and
  `docs/jobs/live-adoption-db-backups/2026-06-29-230844-original-draft-pattern-identities-after-report.json`.
  Final post-shop verification: migration dry-run reported all six targets
  `changed=no`; Agnes raw saved content had no stale heading-class,
  empty-style, empty-animation, or empty-`br` paragraph markers; targeted WP-CLI
  scan checked `51` migrated/shop-intro draft sections with `0` stale
  serialization markers; page-pattern audit checked `223` candidates with `0`
  needing review.
- 2026-06-30 correction: the PHP parser/serializer checks above were not a
  valid proxy for Gutenberg editor validity. User editor review showed that
  touched sections still opened as "unexpected or invalid content." A
  JavaScript validator using WordPress core's bundled
  `block-serialization-spec-parser`, `wp.blocks.parse()`, registered core
  blocks, and `wp.blocks.validateBlock()` showed the current six restored
  drafts had `159` invalid blocks. The six drafts were backed up to
  `docs/jobs/live-adoption-db-backups/2026-06-30-restore-editor-invalid-drafts-current-before.json`
  and restored to the cleaner
  `docs/jobs/live-adoption-db-backups/2026-06-29-174655-original-draft-pattern-identities-before.json`
  snapshot, reducing editor-invalid blocks to `32`.
- The unsafe PHP migration
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/migrate-original-draft-pattern-identities.php`
  is now retired and fails closed. Future saved-content migrations must use the
  WordPress JavaScript parser/validator workflow before any DB write. Reusable
  validation tooling now lives at
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-editor-block-content.mjs`
  and can be run through `pnpm check:editor-content`.
- Restored editor-validation evidence:
  `docs/jobs/live-adoption-db-backups/2026-06-30-restored-draft-editor-validation.json`.
  Remaining invalids are inherited legacy block-shape issues such as cover
  save-output drift, `data-aos` group markup, empty list wrappers, embeds, and
  quote cover markup; they are no longer the empty child headings/images/lists
  produced by the late wrapper-rebuild passes.
- 2026-06-30 parser-backed repair pass: source patterns
  `pns/activist-hero`, `pns/page-hero`, and `pns/activist-facts` were corrected
  to match Gutenberg save output, and
  `scripts/repair-editor-block-content.mjs` repaired the inherited legacy
  serialization drift without rebuilding text-bearing blocks. The six restored
  draft pages `2874`, `1833`, `1848`, `1855`, `1869`, and `1797` then
  validated at `0` invalid blocks.
- 2026-06-30 layout/class repair pass: `scripts/repair-draft-layout-classes.mjs`
  uses WordPress core's JS parser/serializer to backfill recognized pattern
  classes and normalize existing wrappers. It maps profile heroes, text-only
  sections, text/media sections, facts, stats, previous/next rows, image strips,
  and Wikipedia split sections to their current semantic wrappers while
  preserving text. Live DB validation after apply reports `0` invalid blocks
  across all six drafts. Evidence:
  `docs/jobs/live-adoption-db-backups/2026-06-30-editor-valid-repair-before.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-30-editor-valid-repair-after-validation.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-30-draft-layout-class-repair-before.json`,
  `docs/jobs/live-adoption-db-backups/2026-06-30-draft-layout-class-repair-report.json`,
  and
  `docs/jobs/live-adoption-db-backups/2026-06-30-draft-layout-class-repair-after-validation.json`.
- Remaining manual judgement items from the parser-backed report: one Helen
  Fraser legacy text-only candidate still has a non-empty side column, and one
  Wikipedia split section lacks a separate inner copy group. Both are valid in
  the editor but should be reviewed visually before Phase 5 utility retirement.
- Transitional media caveat: `pns/activist-text-media` and
  `pns/activist-facts` still represent old text-plus-media shapes. They should
  either become explicit split-section variants or be replaced by more generic
  profile/text-media primitives before Phase 5 removes the supporting utility
  classes.

Profile-oriented primitives to consider:

| Candidate                      | Replaces earlier name           | Why                                                                                                                                                                     |
| ------------------------------ | ------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `pns/profile-facts-media-left` | `pns/activist-facts-media-left` | Live Mary facts place media before copy; this is likely profile/bio-oriented even though the internal facts layout should not be hard-coded to activism.                |
| `pns/profile-further-reading`  | `pns/activist-further-reading`  | Live `More about Mary` is a biography/further-reading section. It currently carries `shop-intro` classes, but should not be treated as the synced shop/product pattern. |

## Mary Proof Caveat

The audit now proves the draft rebuild is classifiable, not pixel-identical.
Structural gaps remain:

- `Leading Women` is a no-image text section in live content, while
  `pns/activist-text-media` currently expects a media column.
- `Background` uses full-bleed split media behavior with utility classes such
  as `vw-100`, `no-gap`, `lh0`, and `w-100`.
- `Fun Facts` and the image strip have live left/right and ratio variants that
  are not exact matches for the current generic Herstory patterns.
- `More about Mary` should become its own further-reading pattern or remain an
  intentional one-off; it should not piggyback on synced `shop-intro`.

## Front Page Draft

Seed command:

```bash
wp eval-file app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/seed-rebuild-proof-pages.php front
```

Draft page:

```text
ID: 5424
slug: pns-rebuild-proof-front-page
title: PNS Rebuild Proof - Front Page
status: draft
```

Front-page rebuild section map:

| Rebuild section         | Source used                                       | Current proof status                                                                                                  |
| ----------------------- | ------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------- |
| Welcome header          | `pns/welcome-header`                              | Pattern exists and is the closest match for the front-page hero.                                                      |
| What we do              | Composed Jetpack slideshow + text/button section  | Candidate `pns/split-slideshow-text-left`; this is a general home/content primitive, not activist-only.               |
| Herstories              | Composed image-left/text-right section            | Candidate `pns/split-media-text-left`; current `pns/two-columns` is too broad to describe the orientation/affordance. |
| Quote                   | `pns/blockquote-with-red-line` with content swaps | Pattern exists.                                                                                                       |
| Our work with wikipedia | Composed text-left/edge-media-right section       | Candidate `pns/split-media-text-edge-right`; confirms edge-media sections are site-wide primitives.                   |
| Final shop intro        | synced `shop-intro`                               | Pattern exists.                                                                                                       |

Front-page proof caveat:

- The proof deliberately does not replace the live front page.
- `pns-two-columns` previously mapped the live sections, but it did too much
  semantic work. It covered ordinary split sections, slideshow split sections,
  and edge-to-edge media sections.
- The live published front-page split sections now use `pns-split-section` with
  explicit media-left/media-right and edge variants. `pns/two-columns` should
  remain compatibility-only unless an older saved draft still needs it.
