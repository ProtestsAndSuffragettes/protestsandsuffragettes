# Visual Column Layout Remediation Plan

Plan started on 2026-06-27.

All paths are relative to the project root.

## Implementation Status

Implemented on 2026-06-27 for
`app/public/wp-content/themes/protestsandsuffragettes-standalone`.

Source changes:

- Added `pns-section-inner` and `pns-copy-column` to the targeted code-owned
  patterns and synced-pattern sources.
- Added the layout-role CSS utilities to `styles/utilities/index.css`.
- Added a scoped PNS Cover inner-container rule in `styles/blocks/core-cover.css`
  and imported it into both frontend and editor CSS entrypoints.
- Kept semantic `pns-*` section classes and existing structural classes such as
  `m-auto`, `grid`, `alignfull`, `vw-100`, and `no-gap`.

Migration:

- Added
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/migrate-visual-column-layout-classes.php`.
- Applied the migration to published records only.
- Backup:
  `docs/jobs/live-adoption-db-backups/2026-06-27-160139-visual-column-layout-classes-before.json`.
- Report:
  `docs/jobs/live-adoption-db-backups/2026-06-27-160139-visual-column-layout-classes-after-report.json`.
- Post-apply dry-run result: `0` matching records.

Verification:

- PHP syntax check passed for the migration script.
- Block template validation passed for 13 files.
- Frontend and editor CSS compiled with the local Lightning CSS binary.
- Direct local Stylelint passed after selector-order cleanup.
- Frontend geometry probe at 1920px confirmed:
  - front-page Cover inner container: `1920px`;
  - front-page `.pns-section-inner`: `1664px`;
  - front-page `.pns-copy-column`: `704px`;
  - footer bottom bar: `1920px`;
  - footer inner content: `1664px`.

Editor verification note:

- The compiled editor CSS includes the same scoped Cover rule.
- Live in-app editor verification was blocked because the in-app browser session
  was redirected to WordPress login after reload. Manual editor refresh after
  login remains the final visual check.

## Follow-Up: Welcome Header Inset Correction

Implemented on 2026-06-27 after screenshot review showed the Welcome Header copy
was inset farther than the "Our work with Wikipedia" section.

Cause:

- The Welcome Header used the legacy structure
  `cover > columns.pns-section-inner > column(50vw/70vw) > grid > copy`.
- Applying `pns-section-inner` to that columns scaffold made the copy align
  inside a viewport-width column, and `.m-auto` then centered it too far right.

Correction:

- Changed `patterns/welcome-header.php` to use
  `cover > group.grid.pns-section-inner > group.pns-hero-copy.pns-copy-column`.
- Extended the visual-column migration to normalize existing saved Welcome
  Header content to the same neutral inner-frame structure.
- Added `pns-hero-copy` to legacy saved Welcome Header copy groups so `.m-auto`
  no longer recenters the hero text inside the wide grid.

Migration:

- Backup:
  `docs/jobs/live-adoption-db-backups/2026-06-27-172233-visual-column-layout-classes-before.json`.
- Report:
  `docs/jobs/live-adoption-db-backups/2026-06-27-172233-visual-column-layout-classes-after-report.json`.
- Post-apply dry-run result: `0` matching records.

Verification:

- At a 2048px viewport, the Welcome Header heading and "Our work with
  Wikipedia" heading both start at `x=192`; delta `0`.
- PHP syntax, block-template validation, direct Stylelint, and `git diff --check`
  passed.

## Goal

Make the standalone theme's layout model explicit and repeatable:

- `theme.json` `settings.layout.contentSize` defines the narrow visual content
  column.
- Full-width PNS sections still own full-bleed backgrounds, covers, images, and
  media compositions.
- Written copy inside those sections aligns to the same visual column where the
  design calls for it.
- Media/image blocks are allowed to remain wide or full-bleed when they are the
  visual owner.
- Existing saved page content is migrated to the same class contract used by
  code-owned templates, template parts, patterns, and synced patterns.

This plan is a focused follow-up to
`docs/jobs/2026-06-26-semantic-wrapper-class-migration-plan.md`. It does not
replace that plan's semantic section taxonomy; it adds a layout-role vocabulary
inside those semantic section wrappers.

## Current Diagnosis

The current implementation is close, but inconsistent.

Working patterns:

- Header, footer, and shop sections already mostly separate full-width section
  ownership from constrained inner content.
- The shop section uses a full-width outer wrapper and a constrained inner
  wrapper, which matches the intended model.
- Sections such as "Our work with Wikipedia" show the right overall concept:
  text is placed inside the visual column while media can occupy a wider visual
  area.

Current mismatch:

- The Welcome Header text can sit flush to the viewport edge instead of aligning
  to the inner visual column.
- `.m-auto` mixes several concepts: copy measure, centering, padding, and
  vertical alignment.
- `.pns-hero-copy` currently expresses hero-copy ownership, but not visual
  column alignment.
- Some saved page content predates the latest source patterns and will not
  receive new classes unless migrated.

## Layout Class Contract

Keep existing semantic section classes, and add layout-role classes where a
wrapper has a specific layout job.

| Class | Owner | Purpose |
| ----- | ----- | ------- |
| `pns-section` | Existing outer PNS section wrapper | Full-width section/background ownership. Already used widely. |
| `pns-section-inner` | Inner wrapper inside a `pns-section` | Visual column frame. Constrains section content to the site visual column while allowing the outer section to remain full width. |
| `pns-copy-column` | Group around written copy | Written-copy alignment and measure inside a section. Should not force media/image width. |
| `pns-media-breakout` | Optional media/image wrapper | Explicit marker for media allowed to exceed the copy column. Use only where `alignfull`, `alignwide`, `vw-100`, or full-width columns are not already clear enough. |

Existing semantic classes remain the route/pattern identity:

- `pns-welcome-header`
- `pns-activist-hero`
- `pns-activist-text-media`
- `pns-shop-intro`
- `pns-connect-social`
- `pns-read-all-about-it`
- `pns-blockquote-with-red-line`
- `pns-blockquote-cover`
- other classes already listed in the semantic wrapper migration plan.

## CSS Contract

`theme.json` remains the source of truth for the visual content column:

```json
"layout": {
  "contentSize": "44rem",
  "wideSize": "min(100%, var(--pns--layout--wide-breakout-size))"
}
```

CSS should derive from WordPress' generated variables:

```css
:root {
	--pns--layout--content-size: var(--wp--style--global--content-size, 44rem);
	--pns--layout--site-max-inline-size: calc(
		50vw + var(--pns--layout--content-size)
	);
	--pns--layout--wide-breakout-size: calc(
		var(--pns--layout--site-max-inline-size) - 4rem
	);
}
```

Proposed role styles:

```css
.pns-section-inner {
	inline-size: 100%;
	max-inline-size: var(--pns--layout--site-max-inline-size);
	margin-inline: auto;
}

.pns-copy-column {
	max-inline-size: var(--pns--layout--content-size);
}
```

Important rule:

- Do not apply `contentSize` blindly to every paragraph inside a full-width
  section.
- Use `pns-copy-column` only where the written content itself should be bound to
  the visual column.
- Let images, covers, image strips, and full-width media blocks keep their
  existing `alignfull`, `alignwide`, `vw-100`, or no-gap layout unless a
  specific section needs correction.

## Phase 0 - Focused Survey

Survey existing code-owned and saved-content section structures specifically for
visual-column ownership.

Filesystem scope:

- `patterns/*.php`
- `synced-patterns/*.html`
- `parts/*.html`
- `templates/*.html`

Database scope:

- Published pages only.
- Published posts only if they contain copied PNS sections.
- `wp_block` synced pattern sources.
- `wp_template` and `wp_template_part` records for the active standalone theme.
- Skip drafts by default.

Survey outputs:

- Table of sections with:
  - source path or post ID;
  - section semantic class;
  - current outer wrapper;
  - current inner visual-column wrapper, if any;
  - current copy-column wrapper, if any;
  - current media-breakout wrapper, if any;
  - proposed class additions;
  - migration risk.
- Specific identification of the Welcome Header wrapper that should receive
  `pns-section-inner` and the copy wrapper that should receive
  `pns-copy-column`.
- List of sections that are already correct and should not be changed, such as
  shop/header/footer structures unless the survey proves a missing class.

Gate before Phase 1:

- No content mutation has happened.
- Every target has a deterministic matcher that does not depend on transient
  database IDs.

## Phase 1 - Source Pattern And Template Updates

Update versioned sources first.

Expected source changes:

- Add `pns-section-inner` to inner visual-column wrappers in code-owned
  sections.
- Add `pns-copy-column` to written copy groups, especially hero/text-media
  copy groups.
- Keep existing classes such as `pns-hero-copy`, `m-auto`, `grid`, `alignfull`,
  `alignwide`, `vw-100`, and `no-gap` during the first pass unless a class is
  proven harmful.
- Add `pns-media-breakout` only when the media wrapper lacks any existing clear
  full-width/breakout marker.

Likely first targets:

| Source | Likely update |
| ------ | ------------- |
| `patterns/welcome-header.php` | Add a visual-column wrapper/class so hero copy aligns to the central column. Add `pns-copy-column` to the intro copy group. |
| `patterns/activist-hero.php` | Keep current media behavior. Add/confirm `pns-copy-column` on copy wrapper if it should follow the same model. |
| `patterns/activist-text-media.php` | Add/confirm `pns-copy-column` on text column; keep image side wide. |
| `patterns/activist-facts.php` | Add/confirm `pns-copy-column` where written facts occupy the visual column. |
| `synced-patterns/connect-social.html` | Add/confirm copy-column role for the text column; leave media/image area wide. |
| `synced-patterns/shop-intro.html` | Treat as baseline/reference; only add role classes if they document existing behavior without changing layout. |
| `parts/header.html` | Treat as baseline/reference; add role classes only if they do not change current behavior. |
| `parts/footer.html` | Treat as baseline/reference; add role classes only if they do not change current behavior. |

Gate before Phase 2:

- Filesystem block markup validates.
- Frontend and editor CSS compile.
- The Welcome Header no longer places copy flush to the viewport edge.

## Phase 2 - Migration Script And Dry Run

Create a WP-CLI migration script for existing saved content.

Requirements:

- Use `parse_blocks()` and `serialize_blocks()`.
- Support `dry-run` and `apply`.
- Write timestamped before/after reports under
  `docs/jobs/live-adoption-db-backups/`.
- Published pages only unless a later explicit decision includes posts or
  templates.
- Skip drafts.
- Skip plugin-owned blocks unless they are inside an approved PNS section
  wrapper and only class additions are needed on PNS-owned wrapper groups.
- Add missing classes idempotently.
- Do not remove legacy classes in this phase.

Initial deterministic matchers:

- `pns-welcome-header` cover sections.
- `pns-activist-hero` cover sections.
- `pns-activist-text-media` sections.
- `pns-activist-facts` sections.
- `pns-connect-social` synced/source sections.
- `pns-shop-intro` synced/source sections, if the survey finds role classes
  missing from a saved source.

Dry-run report should include:

- post ID;
- post type;
- title;
- matched section class;
- block path;
- classes added;
- whether content would change.

Gate before Phase 3:

- Dry-run matches exactly the expected published content.
- Dry-run includes the homepage Welcome Header if it is saved as copied blocks.
- Dry-run does not match unrelated content.

## Phase 3 - Apply Existing Content Migration

Apply the migration only after the dry-run is reviewed.

Order:

1. Export rollback JSON.
2. Migrate `wp_block` synced pattern sources where applicable.
3. Migrate published page content.
4. Migrate active standalone `wp_template` / `wp_template_part` records only if
   the survey proves a saved DB override is active and missing classes.
5. Re-run dry-run to prove no expected targets remain.

Gate before Phase 4:

- Existing saved content and code-owned sources use the same role classes.
- No excluded draft/plugin-owned content was mutated.

## Phase 4 - CSS Adoption And Legacy Compatibility

Move layout CSS from generic utilities toward role classes.

Rules:

- Keep `.m-auto` working as a legacy compatibility class initially.
- Prefer `.pns-copy-column` for new copy-width rules.
- Prefer `.pns-section-inner` for visual-column rules.
- Avoid broad descendant selectors like `.pns-section p` that would squeeze
  all copy indiscriminately.
- Do not make media/image blocks obey `contentSize` unless the section design
  explicitly requires it.

Potential transition:

```css
.m-auto,
.pns-copy-column {
	max-inline-size: var(--pns--layout--content-size);
}
```

Later, after migration evidence proves `pns-copy-column` is present everywhere:

- reduce `.m-auto` to spacing/legacy compatibility only, or deprecate it with a
  documented deletion condition.

Gate before Phase 5:

- CSS compiles.
- Editor previews compile.
- No new horizontal overflow.

## Phase 5 - Verification

Required checks:

- `theme.json` parses.
- Block templates validate.
- Frontend CSS compiles.
- Editor CSS compiles.
- Browser checks at exaggerated `contentSize`, such as `20rem`, prove:
  - section backgrounds remain full width;
  - images/media remain wide where intended;
  - written copy aligns to the visual column;
  - Welcome Header copy no longer starts flush to viewport edge;
  - shop/header/footer remain correct.
- Browser checks at normal `contentSize`, such as `44rem`, prove:
  - homepage remains visually coherent;
  - Mary Barbour remains coherent;
  - shop remains coherent;
  - footer bottom bar remains full width with constrained inner content.

Suggested routes:

- `/`
- `/herstories/mary-barbour/`
- `/shop/`
- `/pns-pattern-qa/`

Closeout evidence:

- Migration backup/report paths.
- Validation commands and results.
- Screenshots or geometry JSON for the Welcome Header before/after.
- Any retained legacy aliases and their deletion criteria.

## Non-Goals

- Do not redesign all sections.
- Do not force every paragraph to `contentSize`.
- Do not remove existing `alignfull`, `alignwide`, `vw-100`, `grid`, `no-gap`,
  `m-auto`, or `pns-hero-copy` classes in the first migration.
- Do not migrate drafts unless explicitly requested.
- Do not depend on historical source IDs.

## Decisions

- `pns-copy-column` is width/alignment only. It does not own padding. Existing
  utilities and section-specific rules keep spacing ownership during the first
  pass.
- `pns-section-inner` uses `--pns--layout--site-max-inline-size`, matching the
  current header, footer, and shop visual frame.
- Migration is additive only. Keep existing semantic `pns-*` classes so PNS
  templates and sections remain targetable by stable project-owned hooks.
- Keep existing structural/legacy classes such as `m-auto`, `pns-hero-copy`,
  `grid`, `alignfull`, `alignwide`, `vw-100`, and `no-gap` unless a later
  phase proves one is harmful and documents a safe removal path.
- Add `pns-media-breakout` sparingly. Existing media markers are usually clear
  enough.
