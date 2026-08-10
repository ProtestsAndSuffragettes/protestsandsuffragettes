# Theme JSON And CSS Structure Rationalization Plan

Plan started on 2026-06-28.

All paths are relative to the project root.

## Purpose

Rationalize the active `protestsandsuffragettes-standalone` theme so block
controls, `theme.json`, CSS variables, utility classes, synced patterns, and
saved live content have a clear source-of-truth model.

This plan is deliberately conservative. The site already has live layouts that
must survive theme adoption. Existing pages, posts, navigation records, synced
patterns, and template parts should keep rendering correctly unless a break is
explicitly accepted and tracked with a migration or realignment task.

## Review Baseline

The adversarial review found these high-value issues:

- Active Local theme is `protestsandsuffragettes-standalone`.
- Filesystem templates and parts are the active template source, but several of
  those files still reference DB records by numeric ID.
- `wp_navigation` and `wp_block` records are part of the live layout surface.
- `wp_global_styles` has a standalone-theme DB record, currently minimal, but
  still an override layer above `theme.json`.
- Global `blockGap: 0` and broad CSS resets make some editor controls misleading.
- Utilities such as `m-auto`, `pns-section-inner`, `pns-copy-column`, `no-gap`,
  `mt0`, `mb05`, `vw-100`, and `w-50-m` may still be used in saved content.
- Some utility classes are already being phased toward semantic wrappers,
  block layout data, and CSS custom properties; deletion must be evidence-based.
- Root `html, body` overflow suppression masks a known Edu Giveaway overflow
  and can hide future layout regressions.
- Content-width note: the approved design intent is that `theme.json` owns the
  canonical content width. The expected value is `44rem`; if the working tree
  shows a different value during Phase 0, treat that as drift to resolve before
  spacing/layout cleanup proceeds.

## Non-Negotiable Guardrails

- Do not remove or meaningfully change a utility class until saved post/page and
  synced-pattern usage has been surveyed.
- Do not assume filesystem templates are the only live source. Check DB-backed
  `wp_navigation`, `wp_block`, `wp_template`, `wp_template_part`, and
  `wp_global_styles` records before implementation.
- Prefer preserving existing live layouts. If a cleaner model must break an
  existing layout, create a migration or follow-up realignment task before the
  breaking change lands.
- Treat author-facing controls as real. If CSS must override a block control so
  the control is meaningless, either scope the override to a specific pattern or
  stop exposing/using that control for that surface.
- Keep store/search/vendor concerns separate unless a phase explicitly includes
  them.
- Compile and visually verify one cleanup batch at a time.
- Published page sections should be reproducible from the code-owned pattern
  library wherever practical. If saved content structurally matches a pattern,
  its wrapper markup should carry the same pattern identity classes that a fresh
  insertion of that pattern would create. One-off content-derived hooks are not
  an architecture by themselves; prefer reusable role classes and pattern
  identity classes over section-title-derived classes.

## Approved Defaults

These decisions are locked before implementation starts:

- Utility classes use the moderate path: survey all filesystem and saved-content
  usage first, then retire low-risk source-only utilities before higher-risk
  saved-content migrations.
- Global spacing should move away from `blockGap: 0` as the permanent model.
  Use meaningful `theme.json` defaults for ordinary rhythm, then opt out in
  specific patterns or block styles where zero gap is intentional.
- `theme.json` should own canonical content and wide sizes. The target content
  width is `44rem`; narrower or wider compositions should be explicit pattern
  roles or semantic classes, not hidden fallback behavior.
- Numeric DB references should be removed or hardened through deterministic
  seeding/repair, not ripped out blindly. Navigation may remain DB-owned if it
  is seedable and repairable by slug.
- Existing live layouts should not break accidentally. Intentional visual
  realignment is allowed only when paired with a migration or follow-up task.
- Root overflow suppression should be removed or narrowed only after the real
  overflowing source is traced and fixed.
- Vendor overrides should be classified before reduction: account setting,
  plugin setting, unavoidable runtime CSS, or dead override.

## Ownership Model To Prove

The desired end state is not "less CSS at any cost." The desired end state is a
small number of honest ownership layers.

| Surface                                                                            | Preferred owner                                                               |
| ---------------------------------------------------------------------------------- | ----------------------------------------------------------------------------- |
| Author-facing palette, font sizes, spacing scale, line heights, content/wide sizes | `theme.json`                                                                  |
| Repeatable section structure                                                       | template, part, pattern, or synced-pattern source                             |
| One-off visual composition that authors intentionally choose                       | named block style or stable semantic class                                    |
| Layout compatibility for existing saved content                                    | migration script or documented compatibility class                            |
| Third-party runtime output                                                         | scoped `vendor-overrides` CSS after plugin/account settings are checked       |
| Editor controls that should not apply                                              | avoid the control, remove the class path, or document the unsupported surface |

Design-token source-of-truth rule:

- `settings.color.palette` is the source of truth for author-selectable named
  colors. Palette entries should be the colors an editor can intentionally pick.
- `settings.custom.color` is only for semantic design roles that are not just
  public palette duplicates and that deliberately need generated
  `--wp--custom--color-*` variables.
- `settings.custom.spacing` and `settings.custom.typography` are only for the
  approved spacing/type/line-height roles that should generate
  `--wp--custom-*` variables. They should not mirror public presets without a
  semantic reason.
- Private `--pns-*` CSS variables are component or pattern aliases. They may
  point to palette/custom tokens, but they are not an independent source of
  truth.
- Serialized templates, patterns, and saved content should use palette slugs or
  approved preset/custom variables, not raw hex or one-off numeric values, unless
  a specific component or vendor exception is documented.

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
511irrlz - Rationalize standalone theme block controls and CSS authority
```

Phase tasks:

| Phase | Dex ID     | Task                                                      |
| ----- | ---------- | --------------------------------------------------------- |
| 0     | `78tagw4u` | Confirm live adoption baseline and rollback gates         |
| 1     | `vzuavyh7` | Survey saved content utility and class usage              |
| 2     | `q9sj9ca7` | Remove template dependence on numeric DB refs             |
| 3     | `jdaven55` | Decide theme.json versus CSS authority                    |
| 4     | `pwt0rcw1` | Normalize spacing and layout without breaking saved pages |
| 5     | `gpi19dat` | Retire or replace legacy utility classes in batches       |
| 6     | `hfux2nkg` | Fix root overflow and vendor override debt at source      |
| 7     | `5koa559i` | Validate frontend, editor, migration, and live adoption   |

Current mapped follow-ups:

| Dex        | Concern                                              | Owner boundary                                                                                                                                                              |
| ---------- | ---------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `to9qfw83` | Legacy generic utility class removal                 | Owns `no-gap`, `mt0`, `mb05`, `vw-100`, `w-50-m`, `grid`, `m-auto`, `pr0`, `rubik`, `mw-intro-text`, and related utility cleanup.                                           |
| `j57qco7z` | Content-shaping CSS to block/template settings       | Owns non-utility CSS that currently shapes specific pages, synced sections, template parts, or page-type patterns.                                                          |
| `anc67n6s` | Explicit dark/light surface alignment                | Owns block/template adoption of explicit surface classes or known palette background classes.                                                                               |
| `sodx2jk4` | Palette token canonicalization and DB migration      | Owns radical color-token cleanup: canonical palette slugs, private CSS aliases, and any required saved-content color migration.                                             |
| `uzku688y` | Spacing typography and rhythm scale canonicalization | Owns the common spacing, type-size, and line-height scale across `theme.json`, CSS aliases, templates, patterns, and saved content.                                         |
| `52311232` | Final vendor override debt re-evaluation             | Owns the final post-rationalization review of Ecwid, EmailOctopus, Jetpack, Messenger, project-owned block, and retained `!important` override debt before parent closeout. |
| `5e6hco3p` | Ecwid product-grid ownership validation              | Owns the `pns/ecwid-product-grid` block becoming plugin-owned, with PNS theme CSS only as optional enhancement.                                                             |
| `6jl8zwha` | Layout width contract consistency                    | Owns the mapping between `theme.json` content/wide sizes, PNS frame helpers, and Split Section component geometry.                                                          |
| `js1yiby8` | Active template layout contract adoption             | Owns the coordinated Search, News/Home, Herstories archive, and 404 filesystem/template-override sync after `6jl8zwha`.                                                     |
| `4p0t11js` | Contact Us light-surface layout lock-down            | Owns the dedicated Contact Us light-surface template frame and rendered Jetpack form contract.                                                                              |

## Follow-Up - Palette Token Canonicalization And DB Migration

Dex: `sodx2jk4`

Subplan:
`docs/jobs/2026-07-06-theme-json-color-token-cleanup-plan.md`

Status: open.

Goal: complete the full cleanup path for standalone theme color tokens. The
target is not a long-term compatibility layer. The target is a smaller,
author-facing palette with one canonical slug per real design color, plus
private CSS aliases only where they express a real component role.

This is intentionally more aggressive than the conservative compatibility path.
Temporary aliases or a two-step migration are allowed only if a direct cleanup
would otherwise break live serialized content or make rollback impractical. Any
temporary bridge must have an explicit removal trigger in the same follow-up.

Current 2026-07-06 audit:

- Active Local theme is `protestsandsuffragettes-standalone`.
- `brand-color` is not an active standalone palette slug and has no filesystem
  or scoped DB content hits.
- `background` and `brand-purple` are separate palette entries with near-brand
  purple values: `#3D207E` and `#392279`.
- `tertiary` and `accent-mint` are separate mint entries with near-identical
  values: `#7FBFA5` and `#7bdcb5`.
- `brand-purple`, `deep-purple`, and `accent-mint` are duplicated between the
  author-facing palette and `settings.custom.color`.
- Filesystem code uses `background` as a palette slug for surfaces and uses
  `brand-purple` / `accent-mint` mainly through private `--pns--color-*`
  aliases.
- `tertiary` is still serialized in code-owned stats pattern markup and is
  consumed by a navigation CSS alias.
- Active `wp_global_styles` row `5256` is still minimal:
  `{"version":3,"isGlobalStylesUserThemeJSON":true}`.
- Current saved-content hits are migration-relevant:
  - `background` appears in live/current `wp_template` `6132`, template part
    `5980`, reusable blocks `1494`, `1504`, `1509`, published pages `49`,
    `1786`, `1789`, `2363`, `4629`, `5028`, and published Herstories `5903`,
    `5904`, `5905`. It also appears in draft/private working pages.
  - `tertiary` appears in published Herstories `5903`, `5904`, `5905`, Pattern
    QA page `5265`, and draft duplicate pages `1848`, `1855`, `1869`.
  - `brand-purple`, `accent-mint`, and `brand-color` have no scoped DB
    `post_content` hits from the current audit.

Canonicalization direction:

- Pick the final public palette slugs before editing. Current likely target:
  keep `brand-purple` for the main purple surface and keep `accent-mint` for
  the mint accent; migrate/remove `background` and `tertiary` if the direct
  migration proves safe.
- Decide whether global body background should still use the brand purple
  palette value or a private surface token. If authors should not choose it as
  generic "Background", do not keep that label as the public slug.
- Collapse duplicated `settings.custom.color` entries only after CSS aliases
  have a single source. Prefer `--pns--color-*` aliases for component semantics,
  but do not mirror every palette entry without a reason.
- Treat the entire `settings.custom` color block as suspect during closeout.
  Any retained custom color must either be a semantic design token that should
  generate a `--wp--custom--color-*` variable or be moved to a private
  `--pns-*` component alias. Do not keep custom colors that merely duplicate the
  public palette.
- Migrate filesystem templates, parts, patterns, synced-pattern source, CSS
  selectors, tests, and scripts before deleting a slug from `theme.json`.
- Migrate live DB content only when a direct slug removal would strand serialized
  block markup. Use backup, dry-run/apply where practical, and post-apply
  verification.
- Leave revisions alone unless a restore/history normalization requirement is
  explicitly accepted. They are non-live and numerous.

Required migration order:

1. Reconfirm active theme, active `wp_global_styles`, and current saved-content
   hits immediately before implementation.
2. Migrate source-owned uses first: `theme.json`, `styles/shared/settings.css`,
   component CSS, template parts, patterns, synced-pattern source, and tests.
3. Migrate sitewide DB records before ordinary content: active/current
   `wp_template`, `wp_template_part`, and `wp_block` rows.
4. Migrate published pages and Herstories.
5. Migrate draft duplicate pages only if they remain useful fixtures or future
   editorial sources.
6. Run a fresh DB scan for old slugs. Remove old palette entries only after live
   hits are zero or after a documented, temporary bridge is in place.
7. Remove any temporary bridge before closeout unless the user explicitly
   accepts it as long-term compatibility debt.

Acceptance checks:

- `theme.json` exposes only the approved public palette colors for the
  standalone theme.
- Removed slugs have zero live/current DB hits in `wp_template`,
  `wp_template_part`, `wp_block`, `wp_navigation`, `page`, `post`, and
  `herstory` content.
- Active standalone `wp_global_styles` is rechecked and either unchanged or
  backed up before mutation.
- Private CSS color aliases have a named component/design role; unused or purely
  mirrored aliases are removed.
- `settings.custom.color` contains no duplicate mirrors of public palette
  values; every retained entry has a documented semantic role.
- Serialized block classes and attributes are internally consistent after
  migration, for example no remaining `has-tertiary-color` when `tertiary` is
  removed.
- `pnpm compile:css`, stylelint or the relevant focused CSS gate, block-template
  validation, and focused visual checks pass after each behavior-changing batch.
- The final full visual suite passes, or any intentional visual change is
  explicitly reviewed and recorded with route and viewport.
- Rollback exports exist for every DB mutation.

## Follow-Up - Spacing Typography And Rhythm Scale Canonicalization

Dex: `uzku688y`

Subplan:
`docs/jobs/2026-07-06-theme-json-rhythm-type-scale-cleanup-plan.md`

Status: open.

Goal: replace the current disjointed collection of spacing, typography, and
line-height values with a coherent rhythm system. The target is one canonical
scale for author-facing values in `theme.json`, with private CSS aliases only
where they express component mechanics or intentionally fixed pattern behavior.

This is broader than Phase 4E. Phase 4E made global `blockGap` meaningful and
proved manual spacing controls can remain honest. This follow-up decides the
actual scale those controls and component aliases should use. It is also
separate from Phase 5 utility deletion: utility migrations may unblock this
work, but the goal here is the rhythm/type system, not class retirement for its
own sake.

Current 2026-07-06 audit:

- `theme.json` exposes a partial spacing ladder:
  `0.25rem`, `0.5rem`, `0.625rem`, `1rem`, `1.25rem`, `1.5rem`, and `2rem`.
- Global `styles.spacing.blockGap` currently maps to
  `var(--wp--custom--spacing--compact)`.
- `theme.json` font presets are sparse and display-oriented:
  `medium` = `clamp(1rem, 2vw, 20px)`, `large` =
  `clamp(1.5rem, 4vw, 45px)`, and `x-large` =
  `clamp(2rem, 4vw, 56px)`.
- `theme.json` also carries raw element/block font sizes outside that public
  preset ladder, including body `0.875rem`, caption `13px`, `h1` `3.6875rem`,
  `h4` `1.6875rem`, `h5` `1.3125rem`, social links `1.375rem`, navigation
  `1rem`, and several `1.125rem` custom/navigation values.
- Line-height values already have multiple roles but are not yet expressed as a
  clean type system: paragraph `1.6`, heading `1.2`, heading compact
  `1.17777778`, display tight `1.03575`, paragraph compact `1.26666667`, quote
  paragraph `1.1`, and product-grid fallback values such as `0.8`.
- Source markup and CSS still carry one-off or near-scale spacing values such as
  `4rem`, `6rem`, `2.5rem`, `1.875em`, `1.875rem`, `2.4375rem`, `64px`,
  `56px`, `20px`, `26px`, navigation drawer values like `25px` / `28px`, and
  form/embed widths such as `32.5rem`.
- Some one-off values are likely real component geometry, for example navigation
  marker sizes, EmailOctopus form width, fixed avatar sizes, and media/query
  layout thresholds. Those should be classified, not blindly forced onto the
  rhythm scale.
- Existing tests currently assert some raw values, for example `20px` rhythm,
  `13px` captions/navigation, `26px` form padding, `45px` / `56px` display
  sizes, and `320px` active-date measure. These assertions must be updated only
  with intentional design decisions.

Canonicalization direction:

- Define a public spacing scale in `theme.json` that covers ordinary author
  rhythm, section padding, template padding, and block gaps. Prefer named roles
  that authors can understand over one-off numeric labels.
- Define a public type scale that covers body, small/caption/meta,
  navigation, medium copy, heading, and display sizes. Use `clamp()` only where
  responsive scaling is part of the design contract.
- Define line-height roles explicitly: body copy, compact copy, heading,
  compact heading, display, and exceptional component values. Do not leave long
  decimals unexplained if they are retained.
- Collapse CSS private aliases back to `theme.json` tokens where they are just
  mirrors. Keep private aliases for component mechanics, such as button padding
  or navigation geometry, only when they protect a named component contract.
- Treat the entire `settings.custom` spacing and typography block as suspect
  during closeout. Any retained custom value must either feed the approved
  public scale, generate a documented semantic `--wp--custom-*` variable, or be
  moved to a private `--pns-*` component alias.
- Migrate filesystem templates, parts, patterns, synced-pattern source, tests,
  and scripts before changing or removing public preset names.
- Migrate DB-backed saved content only when serialized block values would keep
  old rhythm/type choices alive after source cleanup.
- Keep vendor/runtime exceptions separate. EmailOctopus, Ecwid, and other
  plugin-derived sizing should be normalized only after their account/plugin
  settings and runtime constraints are checked.

Required migration order:

1. Reconfirm active theme, active `wp_global_styles`, and current saved-content
   hits for spacing/font-size/line-height values immediately before
   implementation.
2. Produce an inventory table of all source and saved-content spacing, font-size,
   and line-height values, grouped as public preset, private component token,
   serialized block setting, vendor/runtime exception, or deletion candidate.
3. Choose the canonical rhythm and type ladders, including which values are
   editor-facing presets and which are private component roles.
4. Migrate source-owned values first: `theme.json`, `styles/shared/settings.css`,
   base typography CSS, component CSS, template parts, templates, patterns,
   synced-pattern source, and tests.
5. Migrate sitewide DB records and reusable blocks before ordinary published
   pages if serialized values remain.
6. Migrate published pages and Herstories only when the source migration leaves
   stale serialized rhythm/type values in live content.
7. Run fresh source and DB scans for retired values. Remove or deprecate old
   presets only after live hits are zero or after a documented, temporary bridge
   is in place.

Acceptance checks:

- `theme.json` contains the approved public spacing, font-size, and line-height
  scales, with no duplicate or unexplained author-facing presets.
- `settings.custom.spacing` and `settings.custom.typography` contain no
  redundant mirrors of public presets; every retained custom value has a
  documented semantic or component role.
- Private CSS rhythm/type aliases have a named component or pattern role; unused
  or purely mirrored aliases are removed.
- Raw one-off values in templates, parts, patterns, synced patterns, and CSS are
  either migrated to the canonical scale or documented as fixed component/vendor
  geometry.
- Serialized block spacing, font-size, and line-height values are internally
  consistent after any DB migration.
- Active standalone `wp_global_styles` is rechecked and either unchanged or
  backed up before mutation.
- `pnpm compile:css`, stylelint or the relevant focused CSS gate, block-template
  validation, and targeted visual/computed-style checks pass after each
  behavior-changing batch.
- The final full visual suite passes, or any intended rhythm/type visual change
  is explicitly reviewed and recorded with route and viewport.
- Rollback exports exist for every DB mutation.

## Follow-Up - Layout Width Contract Consistency

Dex: `6jl8zwha`

Status: completed on 2026-07-06.

Goal: make the standalone theme's layout width model explicit and testable
before final rationalization closeout. This is a consistency task, not a visual
redesign task.

Current concern:

- `theme.json` owns canonical `contentSize` and `wideSize`, but live layouts use
  a mix of core constrained layout, PNS helper classes, and component-owned
  geometry.
- Historical planning language sometimes used "visual column" where the
  implementation now distinguishes between the readable content column and the
  wider site frame.
- The PNS Split Section block-style family is intentionally not a generic
  constrained layout. Its outer section/media geometry should stay wider than
  `contentSize`; only the copy area should map back to the readable text
  measure.
- Search/Search Results template work, Ecwid product-grid validation, and
  independent video-cover work are adjacent noise and should not be used as the
  acceptance gate for this task unless they have stabilized first.
- Search/Search Results, Herstories CTA/query-grid work, News/blog archives, and
  single-post layouts may still be updated to adopt the contract, but they
  should move as coordinated template/DB migrations after their current design
  work settles. Use stable fixtures such as Pattern QA, homepage sections, and
  accepted Mary Barbour Split Section geometry as proof targets for this task.

Proposed contract to prove:

| Surface                        | Intended meaning                                                                  |
| ------------------------------ | --------------------------------------------------------------------------------- |
| `theme.json` `contentSize`     | Canonical readable text measure, currently expected to remain `44rem`.            |
| `theme.json` `wideSize`        | Canonical wide/editorial measure for wider core-aligned content.                  |
| `.pns-content-frame`           | Content column with gutters, for ordinary vertical copy and compact controls.     |
| `.pns-copy-column`             | Measure-only copy column inside a composed section; not a site-frame wrapper.     |
| `.pns-section-inner`           | Wider site-frame wrapper; not interchangeable with the readable content column.   |
| `.pns-section-frame`           | Wider site-frame wrapper with gutters.                                            |
| `.pns-split-section*` variants | Component-owned split/media geometry; copy sub-area resolves to the text measure. |

Implementation direction:

- Audit current CSS, templates, patterns, synced patterns, and saved-content
  assumptions for places that conflate content column, wide size, and site
  frame.
- Update stale plan/documentation language where it would mislead future work,
  especially references that imply `.pns-section-inner` equals the global
  content column.
- Prefer CSS custom-property aliases and comments that clarify ownership without
  changing computed values.
- Do not collapse PNS Split Sections into generic constrained layout. Preserve
  accepted front-page and Herstories alignment unless a specific visual change is
  reviewed and accepted.
- If a current template or pattern uses a site-frame helper where it really
  means content-frame, migrate that structure only with focused visual evidence.

Acceptance checks:

- `5koa559i` final validation remains blocked until this contract is either
  implemented or deliberately split to a named follow-up.
- Targeted checks prove ordinary content-frame/text-only sections use the
  readable content measure.
- Targeted checks prove normal and edge-media Split Sections keep their
  accepted site-frame/media geometry while copy areas remain readable.
- Front page and Herstories/Mary remain visually aligned with the accepted
  baseline for Split Section behavior.
- Any computed visual change is explicitly classified before the final
  rationalization closeout.

Closeout evidence:

- Added private layout aliases for the canonical width contract:
  `--pns--layout--content-size`, `--pns--layout--wide-size`, and
  `--pns--layout--site-frame-size`.
- Updated PNS layout helpers and section components to consume those aliases
  while keeping current computed visuals aligned with the accepted baseline.
- Added Playwright coverage for the layout width helper contract and Split
  Section layout variants.
- Validation passed: CSS compile, CSS lint, block-template validation, focused
  Playwright coverage for layout width and Split Section contracts.

## Follow-Up - Active Template Layout Contract Adoption

Dex: `js1yiby8`

Status: completed on 2026-07-06.

Goal: apply the proven layout-width contract to the active Search, News/Home,
Herstories archive, and 404 templates, including saved standalone `wp_template`
overrides that would otherwise mask filesystem changes.

Implemented:

- Removed literal `contentSize: "751px"` from 404, Search, and Page Search
  templates so they use the canonical `theme.json` content measure.
- Moved Search results to the wide track with the existing
  `pns-search-results` card/list contract.
- Aligned the News/Home hero with the same `pns-section-inner` and
  `pns-copy-column` hero structure used by other PNS heroes.
- Replaced ad hoc `44rem` constrained intro groups in News/Home and Herstories
  archive sections with the named `pns-content-frame` contract.
- Synced active standalone saved template rows from filesystem after backup:
  404 `6132`, archive-herstory `6138`, home `6186`, page-search `6219`, and
  search `6221`.
- Left inactive old-theme rows `3164` and `1029` untouched; explicit
  `wp_theme` term checks show those belong to `protestsandsuffragettes`, not
  `protestsandsuffragettes-standalone`.
- Contact Us was split to `4p0t11js` because that page was still under active
  content/design changes during this task.

Evidence:

- DB backup:
  `docs/jobs/layout-contract-db-backups/20260706-before-layout-contract-template-alignment.sql`.
- DB sync verification:
  `scripts/sync-layout-contract-templates.php` dry-run reports 5 active
  standalone rows unchanged after apply.
- Block-template validation passed for the five synced templates plus the
  light-surface no-contact page template.
- Focused Playwright passed 33/33 across desktop, tablet, and mobile for native
  Search, News pagination, Herstories archive card thumbnails, generic
  light-surface contracts, and layout width helper contracts.
- CSS compile, CSS lint, touched-file Prettier check, and `git diff --check`
  passed.

## Follow-Up - Contact Us Light-Surface Layout Lock-Down

Dex: `4p0t11js`

Status: completed on 2026-07-06.

Goal: lock the now-stable `/contact-us/` page to the light-surface layout
contract without migrating live page content.

Implemented:

- Confirmed page `6236` uses `_wp_page_template =
page-light-surface-no-contact-form`.
- Confirmed no active saved `wp_template` override exists for
  `page-light-surface-no-contact-form`, so the filesystem template is the live
  source of truth.
- Updated `templates/page-light-surface-no-contact-form.html` so the
  `core/post-content` block carries `pns-content-frame` and uses default layout.
- Did not mutate page `6236` content; no DB backup was required for this batch.
- Locked the current rendered Contact form contract: one
  `#contact-form-6236.jetpack-contact-form-container`, one
  `form.jetpack-contact-form__form.has-jetpack-form-layout`, hidden
  `contact-form-id=6236`, expected Jetpack field groups, and no Contact-page
  EmailOctopus embed output.

Evidence:

- WP-CLI confirmed no saved DB override for the dedicated light-surface page
  template.
- Block-template validation passed for
  `templates/page-light-surface-no-contact-form.html`.
- Focused Playwright passed 3/3 across desktop, tablet, and mobile for the
  Contact Us light-surface/content-frame/form contract.
- CSS compile, CSS lint, touched-file Prettier check, and `git diff --check`
  passed.

## Follow-Up - Content-Shaping CSS Back To Block Settings

Dex: `j57qco7z`

Status: implemented on 2026-07-06.

Goal: reduce theme CSS that paints specific live content into shape when the
same intent can be expressed by block settings, template-part structure, synced
pattern source markup, or a stable reusable component contract.

This follow-up is deliberately separate from legacy utility removal. The utility
classes are already tracked under `to9qfw83`; this task is for non-utility CSS
selectors that still make the theme assume too much about current site content.

Current concerns to classify:

- `styles/page-types/shop.css`
  - `page-id-565` rules set Shop page background, wrapper width, and intro
    paragraph rhythm.
  - Preferred direction: move the light surface, width, and spacing intent into
    the Shop template/page/synced source where practical. Avoid long-term CSS
    keyed to numeric page ID `565`.
- `styles/components/synced-sections.css`
  - `.pns-contact-form`, `.pns-connect-social`, and `.pns-read-all-about-it`
    rules force column gaps, padding, heading margins, and paragraph rhythm.
  - Preferred direction: move ordinary spacing to synced-pattern block settings
    or source markup. Keep only a named synced-section component contract where
    block settings cannot express the design.
- `styles/components/footer-layout.css`
  - Utility-related footer work belongs to `to9qfw83` / `pnl710sq`.
  - This follow-up owns the remaining template-part repairs such as column
    width overrides, contact-group rhythm, separator sizing, and footer-logo
    sizing where those can be represented in the footer template part instead
    of CSS.
- `styles/page-types/herstories-bios.css`
  - Utility-like hero/Herstory classes belong to `to9qfw83` / `umgti392`.
  - This follow-up owns the remaining page-type/pattern CSS such as active-date
    measure/rhythm, facts/stats gap resets, image-strip rules, and custom list
    treatment after utility migration has clarified the live owners.

Classification outcomes:

- Move to block settings, pattern markup, template-part markup, or synced source.
- Keep as semantic component CSS because WordPress controls cannot express the
  contract cleanly.
- Keep as vendor override where plugin/runtime markup is the real owner.
- Document as compatibility debt with a removal trigger.

Acceptance checks:

- Each rule has a named owner and a reason.
- Legacy utility selectors are not duplicated here; they remain under
  `to9qfw83`.
- DB-backed changes use backup, dry-run/apply where practical, and post-apply
  verification.
- Visual output remains aligned to the accepted baseline unless a specific diff
  is explicitly reviewed and accepted.

Implementation result:

| Surface                                                                                          | Current owner                                                                                                                                                                    | Classification                                                                                                                                                                                                                                                                                  | Follow-up decision                                                                                                                                                                                                          |
| ------------------------------------------------------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `styles/page-types/shop.css` `page-id-565` background, wrapper width, and intro paragraph rhythm | Live `/shop/` page `565`, whose saved content now carries `pns-section pns-saved-section pns-shop-storefront` around the Ecwid storefront.                                       | Compatibility debt. The rules are active and should not be deleted opportunistically, but long-term ownership should move from numeric page ID to the saved section's semantic `pns-shop-storefront` contract or block settings.                                                                | Keep for now to avoid destabilising the recently accepted Ecwid/storefront baseline. Retarget away from `page-id-565` during the next Shop/Ecwid cleanup batch with focused storefront, product, cart, and overflow checks. |
| `styles/components/synced-sections.css` contact/social/read-all section rules                    | Code-backed synced-pattern source files under `synced-patterns/`, with matching saved reusable blocks `1493`, `1494`, and `1504`.                                                | Semantic synced-section CSS. The selectors are explicit PNS component contracts, not generic content paint. Full-bleed social media geometry, responsive media sizing, and embedded form/vendor alignment are not reliably expressible as ordinary block settings.                              | Keep. Where ordinary spacing can later be represented in block attributes without changing visuals, migrate it inside a synced-pattern update with DB backup and focused home/contact checks.                               |
| `styles/components/footer-layout.css` remaining footer layout and rhythm                         | Code-backed `parts/footer.html`, seeded navigation `pns-footer-navigation`, and WordPress column/navigation markup.                                                              | Semantic template-part CSS with documented WordPress inline-style exceptions. The compact-tablet `flex-basis` overrides still require `!important` because WordPress serializes column widths inline. Footer contact rhythm and logo sizing are template-part internals, not generic utilities. | Keep. Revisit only if the footer is rebuilt as a dedicated block/component or if column widths move out of inline block attributes.                                                                                         |
| `styles/page-types/herstories-bios.css` active-date, facts, image-strip, and fun-facts rules     | Code-backed Herstory patterns such as `patterns/suffragette-hero.php`, `patterns/suffragette-facts.php`, `patterns/image-strip.php`, and `patterns/suffragette-image-strip.php`. | Semantic page-type/pattern CSS. These rules preserve named Herstory pattern contracts after legacy utility removal.                                                                                                                                                                             | Keep. The `active-dates` max-width `!important` pair is documented compatibility debt; remove it only after the hero pattern can express the compact measure through block structure/settings without visual drift.         |

No DB mutation was performed for this audit. Search and Search Results template
work was active concurrently and excluded from closeout evidence.

## Phase 0 - Confirm Live Adoption Baseline And Rollback Gates

Status: complete on 2026-06-28.

Goal: capture the live source-of-truth state before changing behavior.

Baseline checks:

- Confirm active `stylesheet` and `template` options.
- Confirm filesystem template source using WordPress block-template APIs.
- Inventory `wp_global_styles` for the active standalone theme.
- Inventory `wp_navigation` records referenced by header/footer parts.
- Inventory `wp_block` synced patterns referenced by templates.
- Capture current visual regression baseline for high-risk routes.
- Record current compiled CSS metrics and `!important` counts.

Rollback requirements:

- DB mutations require a timestamped backup in
  `docs/jobs/live-adoption-db-backups/`.
- Saved-content migrations must support dry-run and post-apply dry-run.
- Visual differences must be classified as stable, intended, or requiring
  realignment before the next phase starts.

Acceptance checks:

- Baseline evidence is attached to this plan or linked from it.
- The live layout surfaces are known before any cleanup begins.
- Any unavoidable breaking-risk area has a named rollback path.

Phase 0 result:

- Active Local theme options are
  `stylesheet=protestsandsuffragettes-standalone` and
  `template=protestsandsuffragettes-standalone`.
- WordPress block-template APIs report theme-file-backed templates and parts for
  the standalone theme. The active template source is the filesystem theme, not
  saved `wp_template` overrides.
- Saved DB rows still matter to live rendering: `wp_navigation` records
  `1032`, `1035`, and `5259`; reusable blocks `1493`, `1494`, `1504`, `1509`,
  `3816`, and `4654`; saved template rows `1028`, `1029`, `3164`, `4689`, and
  `4693`; saved template-part row `4666`; and global style rows `1030`,
  `1031`, and `5256`.
- The active standalone global styles row is `5256`
  `wp-global-styles-protestsandsuffragettes-standalone`. Its content is
  currently minimal: `{"version": 3, "isGlobalStylesUserThemeJSON": true }`.
  Legacy row `1030` still contains old DM Sans typography overrides.
- Numeric references remain in code-owned template sources:
  `parts/header.html` references nav IDs `1035` and `5259`;
  `parts/footer.html` references nav ID `1032`; `templates/page.html`
  references reusable block ID `1494`.
- The contact form template part embeds the EmailOctopus form UUID
  `3637e2c8-ff87-11ef-8123-45a2d1a97169`.
- Layout-relevant active plugins include `blockmeister`,
  `ecwid-shopping-cart`, `jetpack`, `jetpack-boost`,
  `animations-for-blocks`, `dynamic-year-block`, `emailoctopus`, `safe-svg`,
  and `wp-fastest-cache`.
- Current `theme.json` content size is the approved `44rem`; the earlier
  30rem concern was not present in the inspected file.

Phase 0 verification:

- Standalone block-template validation passed for 13 files.
- Stylelint passed when run through the local `node_modules` binary.
- Wallace baseline for `styles/dist/frontend.min.css`: 63,225 bytes, 1,159
  source lines, 311 rules, 386 selectors, 744 declarations, 32 compiled
  `!important` declarations, and 41 custom-property declarations.
- Wallace reported several currently unused custom properties, including
  `--pns--layout--wide-breakout-size`, `--pns--color--deep-purple`,
  `--pns--typography--line-height--paragraph-compact`, and
  `--pns--navigation--drawer-z-index`; treat those as Phase 3 review inputs,
  not deletion approval.
- Initial Playwright baseline was red: 24 passed and 27 failed, with stale
  snapshot drift plus contract failures.
- After the footer-focused snapshot refresh on 2026-06-28, route screenshots
  pass across desktop, tablet, and mobile. The suite is now snapshot-green but
  contract-red: 39 passed and 12 failed.
- Remaining failures are the same four contract groups repeated across
  desktop, tablet, and mobile: homepage primary navigation class count,
  cross-site banner CTA lookup, code-backed Pattern QA content, and the Store
  BLOCK TEST live adoption quote warning.
- Follow-up stabilization on 2026-06-28 repaired that red contract baseline:
  header and footer template-part refs now use semantic wrappers, the stale
  Pattern QA fixture page was reseeded, the Store BLOCK TEST/Ecwid synced-block
  warning source was removed, and stale footer/banner test expectations were
  realigned to the current token-backed rendering.
- DB fixture backups were captured before mutation:
  `docs/jobs/live-adoption-db-backups/2026-06-28-pattern-qa-store-block-test-before-repair.json`
  and
  `docs/jobs/live-adoption-db-backups/2026-06-28-wp-block-3816-before-repair.json`.
- Final visual gate after refreshed refs: `tests/visual` passed 51 of 51 across
  desktop, tablet, and mobile.
- The `pnpm --dir ...` wrapper hit a registry signature/version-switch failure
  before running some scripts, so local binaries were used for available checks.
- `compile:css` was intentionally not run because compiled dist files were
  already dirty before this phase, and this baseline should not rewrite them.

Phase 0 gate:

- Later cleanup phases can now use the Playwright visual suite as a clean local
  gate, provided DB fixture state remains aligned with the recorded seeded
  pages and synced blocks.
- Any DB mutation still requires a timestamped backup and dry-run/apply/dry-run
  migration path.

## Phase 1 - Survey Saved Content Utility And Class Usage

Status: complete on 2026-06-28.

Goal: understand where legacy or transitional classes still matter before
retiring them.

Filesystem scope:

- `app/public/wp-content/themes/protestsandsuffragettes-standalone/templates/`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/parts/`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/patterns/`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/synced-patterns/`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/`

Database scope:

- Published pages.
- Published posts.
- `wp_block` synced patterns.
- Active-theme `wp_template` and `wp_template_part` records, if any become
  active overrides.
- `wp_navigation` records referenced by the standalone header/footer.

Classes to survey first:

- `m-auto`
- `pns-section-inner`
- `pns-copy-column`
- `pns-hero-copy`
- `grid`
- `no-gap`
- `mt0`
- `mb05`
- `vw-100`
- `w-100`
- `w-50-m`
- `max-inline-size`
- `mw-intro-text`
- `p1`, `p2-m`, `pl1`, `pr1`, `pr0`
- `rubik`
- `lh0`

Survey output:

- Class name.
- Filesystem hits.
- DB record hits.
- Rendered route or content owner.
- Whether the class is legacy utility, semantic role, layout role, or vendor
  compatibility.
- Proposed disposition: keep, replace, migrate, alias temporarily, or delete.
- Migration risk and required visual checks.

Acceptance checks:

- No utility has been deleted yet.
- Every targeted class has a saved-content usage answer.
- The next phase can distinguish safe source-only cleanup from DB migration
  work.

Phase 1 result:

The survey scanned theme source under `templates/`, `parts/`, `patterns/`,
`synced-patterns/`, and `styles/` excluding `styles/dist/`, then scanned
published `page`, `post`, `wp_block`, `wp_navigation`, `wp_template`, and
`wp_template_part` records with exact class-token matching.

Important interpretation correction:

- `pns-*` classes are theme-owned identity, semantic, and template/block hooks by
  default. Their presence in templates and saved content is not evidence that
  they should be removed from markup.
- The styles attached to `pns-*` hooks can still be simplified, normalized,
  moved, or replaced during later phases, but the hook itself should stay unless
  a specific hook is proven redundant and every source/saved-content use is
  migrated.
- Legacy generic utilities such as `m-auto`, `grid`, `no-gap`, `mt0`, `mb05`,
  `vw-100`, `w-100`, `w-50-m`, `p1`, `p2-m`, `pl1`, `pr1`, `pr0`, `rubik`,
  and `lh0` remain the main retirement/migration candidates.

| Class               | Source hits | Published DB hits | Current disposition                                                                |
| ------------------- | ----------: | ----------------: | ---------------------------------------------------------------------------------- |
| `m-auto`            |          10 |                13 | Legacy utility and migration compatibility; do not delete before migration.        |
| `pns-section-inner` |           9 |                15 | Theme-owned layout/identity hook; keep in markup, normalize only its CSS contract. |
| `pns-copy-column`   |          12 |                18 | Theme-owned semantic/layout hook; keep in markup, normalize only its CSS contract. |
| `pns-hero-copy`     |           6 |                12 | Theme-owned hero-copy hook; keep in markup, reduce only old non-PNS aliases later. |
| `grid`              |          11 |                15 | Layout role in saved content; do not delete as a generic utility yet.              |
| `no-gap`            |          12 |                15 | Legacy spacing utility in saved content; migration-gated.                          |
| `mt0`               |           8 |                11 | Legacy spacing utility in saved content; migration-gated.                          |
| `mb05`              |           4 |                 3 | Legacy spacing utility; lower volume but still DB-backed.                          |
| `vw-100`            |           3 |                 6 | Layout utility tied to full-width saved content; migration-gated.                  |
| `w-100`             |           3 |                 3 | Layout utility in saved content; migration-gated.                                  |
| `w-50-m`            |           3 |                 1 | Layout utility in reusable block content; migration-gated.                         |
| `max-inline-size`   |          12 |                 0 | CSS-only candidate; review source selectors before deletion.                       |
| `mw-intro-text`     |           2 |                 4 | Saved-content typography/measure utility; not source-only.                         |
| `p1`                |           2 |                 0 | Source-only candidate after footer/source replacement check.                       |
| `p2-m`              |           2 |                 0 | Source-only candidate after footer/source replacement check.                       |
| `pl1`               |           1 |                 0 | CSS-only deletion candidate.                                                       |
| `pr1`               |           1 |                 0 | CSS-only deletion candidate.                                                       |
| `pr0`               |           3 |                 3 | Legacy utility in saved content; migration-gated.                                  |
| `rubik`             |           7 |                 2 | Legacy typography utility plus plugin compatibility surface.                       |
| `lh0`               |           2 |                 6 | Legacy line-height utility in saved content; migration-gated.                      |

Highest-risk saved-content and identity classes:

- `pns-copy-column`, `pns-section-inner`, `grid`, `no-gap`, `m-auto`,
  `pns-hero-copy`, and `mt0` appear across many published pages, reusable
  blocks, or template-part content. Removing them would break saved layouts.
- The `pns-*` classes in that list are not utility-retirement targets. They are
  project-owned hooks used to distinguish PNS-authored sections from core,
  generic, and third-party markup.
- The non-PNS utilities in that list remain migration-gated cleanup candidates.

Lowest-risk first cleanup candidates:

- `pl1` and `pr1` are CSS-only in the surveyed source and have zero published
  DB hits.
- `p1` and `p2-m` have zero published DB hits but still appear in source, so
  replace or remove only after checking the footer/source usage.
- `max-inline-size` has zero published DB hits but many source CSS selectors;
  treat it as a source-CSS rationalization target, not as dead code by default.

Phase 1 gate:

- No surveyed class was deleted in this phase.
- Phase 5 should start with the zero-DB-hit candidates and keep each batch
  separate from global `theme.json` spacing changes.
- Classes with published DB hits require a backup-backed migration, temporary
  aliasing, or an explicit decision to retain them as compatibility classes.
- Do not remove `pns-*` hooks from templates, patterns, template parts, or saved
  content merely because their current CSS can be simplified. Treat those hooks
  as stable theme semantics unless a later phase proves a specific replacement.

## Phase 2 - Remove Template Dependence On Numeric DB Refs

Status: complete on 2026-06-28.

Goal: make standalone adoption safer without breaking the current live site.

Known risky refs:

- Header navigation refs in
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/parts/header.html`.
- Footer navigation ref in
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/parts/footer.html`.
- Synced block ref in
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/templates/page.html`.

Preferred approaches:

- Use code-owned template parts or patterns where content should be portable.
- For editable navigation, keep DB ownership but seed/update by slug and record
  a deterministic rewrite path for numeric refs.
- For synced blocks, prefer template parts or unsynced code patterns unless the
  synced behavior is intentional and seedable.

Acceptance checks:

- Fresh-site behavior is defined.
- Existing live navigation and contact layout still render.
- Any remaining numeric ref has a documented reason and seed/repair path.

Phase 2 result:

- Code-owned templates and parts no longer store numeric `wp_navigation` or
  `wp_block` refs.
- Header navigation, banner CTA navigation, footer navigation, and the
  `connect-social` synced block are referenced by stable `pnsRefSlug`
  attributes and resolved to the current numeric ID at render time.
- Live navigation DB records were renamed to stable PNS-owned slugs after DB
  backup:
  `pns-primary-navigation`, `pns-banner-cta-navigation`, and
  `pns-footer-navigation`.
- Navigation fixture files and a manifest now live under
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/navigation/`.
  Existing nav records are kept by default; `--update` is explicit.
- Manual repair/verification command:
  `wp eval-file app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/seed-navigation-refs.php`.
- The synced block path continues to use the existing slug-owned synced-pattern
  manifest and seeder. `connect-social` currently resolves to ID `1494`.
- DB backup before nav slug mutation:
  `docs/jobs/live-adoption-db-backups/2026-06-28-navigation-slugs-before-phase-2.json`.

Phase 2 verification:

- `php -l` passed for `inc/block-filters.php`, `inc/navigation.php`, and
  `scripts/seed-navigation-refs.php`.
- Block template validation passed for 13 files.
- Numeric `ref` search across `templates/` and `parts/` returned no hard-coded
  numeric refs.
- Navigation seeder reported kept records:
  `pns-primary-navigation=1035`, `pns-banner-cta-navigation=5259`, and
  `pns-footer-navigation=1032`.
- Live DOM probe found one primary nav, one banner CTA nav, six footer nav
  links, and the page-level `connect-social` section rendering.
- Full Playwright visual suite passed 51 of 51.

## Phase 3 - Decide Theme JSON Versus CSS Authority

Status: complete on 2026-06-28.

Goal: stop making block controls lie.

Decisions to lock:

- Confirm `theme.json` content size is restored or kept at the approved `44rem`
  target unless Phase 0 proves a deliberate reason to diverge.
- Define any narrower/wider measures as explicit named roles rather than
  competing implicit authorities.
- Lock the default paragraph line-height.
- Make global `blockGap` meaningful by default, then opt out in specific
  patterns or block styles.
- Classify spacing values as author-facing presets or implementation-only
  custom vars.
- Classify colors as author-facing palette options or private CSS tokens.
- Decide whether button colors/padding/shadows are theme defaults, block styles,
  or CSS-only.
- Decide whether separators are author-controlled or always the PNS red rule.
- Identify controls that should be discouraged or removed for special block
  styles when CSS must override them.

Output:

- A table mapping each control/token to one owner:
  `theme.json`, pattern markup, scoped CSS, vendor override, migration
  compatibility, or unsupported.

Acceptance checks:

- Every later CSS simplification can point back to a locked ownership decision.
- Author-facing controls remain meaningful where exposed.
- Any intentionally neutered control has a documented alternative.

Phase 3 result:

- `theme.json` remains the canonical owner for author-facing design defaults:
  palette, font families, named font sizes, spacing presets, default
  typography, content size, and wide size.
- CSS remains the owner for deliberate scoped exceptions: legacy saved-content
  compatibility utilities, component mechanics, vendor/runtime output, and
  pattern-specific display treatments.
- The active standalone `wp_global_styles` row remains minimal. It does not
  currently override filesystem `theme.json`, but it is still a live DB layer
  that must be rechecked before changing global defaults.
- Existing saved `wp_template` and `wp_template_part` rows still exist in the
  database, but Phase 3 rechecked WordPress block-template APIs and found no
  active custom-source template or template-part overrides. Treat those DB
  records as migration/adoption context, not current authority, unless a future
  check proves they are active overrides.
- No global spacing, separator, button, or width behavior changed in this
  phase. Phase 3 only locks the authority map so later cleanup batches can make
  smaller, reviewable behavior changes.

Locked authority map:

| Surface                                            | Current evidence                                                                                                                                  | Locked owner                                                                           | Phase 4/5 rule                                                                                                                                                                                                                          |
| -------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Canonical content width                            | `theme.json` uses `contentSize: 44rem`; CSS copy-width helpers read `--wp--style--global--content-size` with a `44rem` fallback.                  | `theme.json`                                                                           | Keep `44rem` as the default content measure. Use semantic wrappers or patterns for intentional narrower/wider compositions.                                                                                                             |
| Canonical wide width                               | `theme.json` uses `wideSize` directly and no longer routes through a private PNS breakout token.                                                  | `theme.json`                                                                           | Keep wide sizing in `theme.json`; CSS may read the generated wide variable but must not introduce a competing canonical wide size.                                                                                                      |
| `pns-section-inner` and `pns-section-frame` widths | CSS maps both to `--pns--layout--site-frame-size`; Phase 1 found saved-content use.                                                               | scoped CSS / pattern structure                                                         | Keep the `pns-*` hooks. Normalize their CSS contract only after visual coverage proves the wrapper role.                                                                                                                                |
| `pns-copy-column` and copy measure                 | CSS maps it to `--wp--style--global--content-size` with a `44rem` fallback; Phase 1 found saved-content use.                                      | scoped CSS / pattern structure                                                         | Keep the hook. Treat it as a semantic role, not a legacy utility deletion target.                                                                                                                                                       |
| Default `blockGap`                                 | `theme.json` exposes block gap controls but currently sets global `styles.spacing.blockGap` to `0`; CSS restores or overrides gaps in components. | `theme.json`                                                                           | Phase 4 should move toward a meaningful default gap. Preserve intentional zero-gap surfaces with `no-gap`, pattern markup, or scoped CSS.                                                                                               |
| Intentional zero-gap layouts                       | `no-gap`, button groups, navigation, and several patterns intentionally suppress gaps.                                                            | scoped CSS / pattern markup / migration compatibility                                  | Do not delete zero-gap behavior globally. Move each intentional exception to the narrowest owning surface.                                                                                                                              |
| Author-facing spacing scale                        | `theme.json` custom spacing tokens drive CSS via `--wp--custom--spacing-*`.                                                                       | `theme.json`                                                                           | New reusable spacing values should start in `theme.json` only when authors should see/use them.                                                                                                                                         |
| Component spacing mechanics                        | Navigation, buttons, forms, footer, and vendor overrides use private `--pns-*` variables.                                                         | scoped CSS / vendor override                                                           | Keep private CSS vars when they express component mechanics. Do not expose them as presets unless authors should control them.                                                                                                          |
| Paragraph defaults                                 | `theme.json` and CSS both set paragraphs to `1rem` / `1.6`; content-rhythm CSS restores margins for bare content.                                 | `theme.json` for typography, scoped CSS for rhythm compatibility                       | Keep paragraph font size/line height in `theme.json`. Move rhythm changes carefully in Phase 4 because saved content relied on zero-margin resets.                                                                                      |
| Heading defaults                                   | `theme.json` sets Rubik, weight, and line height; CSS adds compact display line heights for `h2`, `h3`, and large font-size classes.              | `theme.json` for defaults, scoped CSS for display treatments                           | Keep ordinary heading controls meaningful. Treat compact display line-height as pattern/style behavior, not the global author default.                                                                                                  |
| Palette colors                                     | `theme.json` exposes author-facing palette colors and semantic custom colors.                                                                     | `theme.json` for currently exposed author choices                                      | Keep currently exposed colors stable until saved-content usage is migrated. New structural colors should stay private unless authors should intentionally choose them.                                                                  |
| Private color aliases                              | CSS aliases palette/custom colors as `--pns--color-*` for components.                                                                             | scoped CSS                                                                             | Keep aliases only when they simplify component CSS or isolate a semantic role. Delete unused aliases only in a later no-behavior cleanup.                                                                                               |
| Buttons                                            | `theme.json` sets basic `core/button` defaults; CSS owns the current PNS shadow, hover, padding, and legacy form/button selector family.          | scoped CSS for current PNS button skin; `theme.json` for baseline core/button defaults | Do not pretend block color/padding controls fully own rendered PNS buttons yet. Later cleanup should either move the default skin into `theme.json` where possible or create a named block style and leave generic controls meaningful. |
| Clear-button variant                               | PHP strips generated background classes for `.clear-button`; CSS makes the button transparent.                                                    | scoped CSS plus render filter                                                          | Keep as a special unsupported-control surface until replaced by a named block style or documented pattern role.                                                                                                                         |
| Separators                                         | `theme.json` has separator defaults, but CSS forces a red 3px rule; current source use is footer-only.                                            | scoped CSS / named block style                                                         | Treat the PNS red rule as the current real owner. Later cleanup should either register/use a named PNS separator style or remove misleading global separator controls/defaults.                                                         |
| Group background padding                           | CSS forces `.wp-block-group.has-background { padding: 0 !important; }` to beat core block support padding.                                        | scoped CSS compatibility                                                               | This intentionally neuters a core support path. Phase 4 should narrow it to known patterns or replace affected markup so group padding controls are meaningful elsewhere.                                                               |
| Root overflow suppression                          | Global `html, body` overflow-x hidden remains as an Edu Giveaway compatibility guard.                                                             | migration compatibility                                                                | Do not remove in Phase 3/4 spacing batches. Trace and fix the real overflow source before narrowing/removing it in Phase 6.                                                                                                             |
| Vendor/runtime output                              | Ecwid, EmailOctopus, Jetpack, and plugin-rendered structures are styled in scoped vendor/block files.                                             | vendor override                                                                        | Keep separate from theme defaults. Prefer plugin/account settings first, then scoped CSS.                                                                                                                                               |
| DB global styles                                   | Active standalone row `5256` is minimal: `{"version": 3, "isGlobalStylesUserThemeJSON": true }`.                                                  | live DB override layer above `theme.json`                                              | Recheck before global-default changes. Back up before mutating.                                                                                                                                                                         |
| Saved template/template-part rows                  | Saved rows exist, but active standalone templates are filesystem-backed.                                                                          | migration/adoption compatibility                                                       | Do not let stale DB rows drive source CSS decisions unless they become active overrides.                                                                                                                                                |

Private token classification:

| Token                                               | Classification                                                              | Follow-up                                                                                         |
| --------------------------------------------------- | --------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------- |
| `--pns--layout--wide-breakout-size`                 | Removed in Phase 4B; the wide-size math now lives directly in `theme.json`. | Do not reintroduce unless a distinct design role is proven.                                       |
| `--pns--layout--site-frame-size`                    | Scoped layout helper for section/frame/footer/shop wrappers.                | Keep while those wrappers need a width intentionally distinct from `alignwide`.                   |
| `--pns--color--deep-purple`                         | Currently no source consumer found.                                         | Candidate for later no-behavior cleanup after confirming no compiled or saved-content dependency. |
| `--pns--typography--line-height--paragraph-compact` | Currently no source consumer found.                                         | Candidate for later no-behavior cleanup or repurpose into a named compact text style.             |
| `--pns--navigation--drawer-z-index`                 | Live navigation implementation token.                                       | Keep; Phase 0 Wallace flag was a false-positive risk because source CSS consumes it.              |

Phase 3 verification:

- Live `wp_global_styles` check returned active standalone row `5256` with
  content `{"version": 3, "isGlobalStylesUserThemeJSON": true }`.
- WordPress block-template API checks returned `source=theme` for standalone
  templates and template parts, with no `custom` source records currently
  active.
- Theme PHP inspection found no Phase 3 control-disabling layer beyond the
  existing block blacklist, `clear-button` render cleanup, and Phase 2
  slug-based template-ref resolver.
- No CSS or `theme.json` behavior was changed in this phase, so compile and
  Playwright visual runs were not required for this decision-only checkpoint.

## Phase 4 - Normalize Spacing And Layout Without Breaking Saved Pages

Status: accepted no-regression implementation slice complete on 2026-06-28.
Behavior-changing follow-ups remain tracked in Dex.

Goal: turn the Phase 3 authority map into small, reversible spacing/layout
changes while preserving live content.

Phase 4 is not a general CSS cleanup phase. It is limited to layout-token,
content-width, wrapper-width, and block-rhythm work that directly reduces the
current conflict between `theme.json`, WordPress-generated layout variables,
semantic `pns-*` wrappers, and compatibility utilities.

Inputs from completed phases:

- Phase 0 established a clean visual gate: `tests/visual` passed 51 of 51 after
  fixture stabilization. Use that as the regression baseline.
- Phase 0 found the active standalone `wp_global_styles` row is minimal, but it
  remains a live DB layer above filesystem `theme.json`.
- Phase 1 found heavy saved-content use of `pns-section-inner`,
  `pns-copy-column`, `pns-hero-copy`, `grid`, `no-gap`, `m-auto`, `mt0`, and
  related utilities. Saved-content utilities are migration-gated.
- Phase 2 removed numeric refs from code-owned templates and made navigation /
  synced-block refs deterministic, so source-owned template and part changes are
  now safer than they were at the start of the review.
- Phase 3 locked ownership: `theme.json` owns author-facing defaults; scoped CSS
  owns deliberate exceptions, compatibility utilities, component mechanics, and
  vendor/runtime output.

Implementation strategy:

- Work in the sub-batches below, in order. Do not combine batches in one commit
  unless the earlier batch is proven to be a no-behavior refactor.
- Start each batch from a clean worktree and complete Dex task `pwt0rcw1` only
  after all accepted Phase 4 batches are done.
- Before each behavior-changing batch, recheck the active standalone
  `wp_global_styles` row and block-template sources. If a DB layer changed,
  stop and update the plan before editing CSS or `theme.json`.
- Source-owned templates, parts, patterns, and synced-pattern source files may
  be adjusted before saved-content migrations. Saved page/post content should
  not be changed in Phase 4 unless a specific migration is added with DB backup,
  dry-run, apply, and post-apply dry-run.
- Preserve existing visual output by default. If a change intentionally alters a
  layout, record the realignment in this plan and Dex before moving on.
- Keep `pns-*` hooks in markup. Phase 4 may simplify or move CSS declarations
  that target those hooks, but it must not remove the hooks from templates,
  patterns, synced patterns, or saved content.

Sub-batches:

1. Phase 4A - Preflight and baseline refresh.
   Confirm the active standalone global-styles row remains minimal, confirm
   block templates/template parts are still `source=theme`, run CSS compile, and
   run the visual suite before behavior changes. If compile rewrites dist files,
   include that output with the batch that caused it.
2. Phase 4B - Width-token simplification.
   Reduce the current content/wide/site-frame token chain to explicit concepts:
   content, wide, and optional site frame. This should be the first code change
   because later wrapper/rhythm changes need an honest width model.
3. Phase 4C - Source-owned wrapper normalization.
   Normalize source-owned `pns-section-inner`, `pns-section-frame`,
   `pns-copy-column`, and `pns-hero-copy` CSS contracts against the simplified
   width tokens. Prefer changing filesystem patterns/synced-pattern sources
   before any saved-content migration. Keep compatibility for existing saved
   classes.
4. Phase 4D - Broad layout override reduction.
   Revisit broad `.alignwide`, `.is-layout-*`, and wrapper overrides only after
   4B and 4C pass. Any retained broad override must name the live surface that
   still requires it.
5. Phase 4E - `blockGap` and content rhythm.
   Move toward a meaningful default `theme.json` block gap only after the width
   and wrapper model is stable. Preserve intentional zero-gap layouts through
   source markup, `no-gap`, scoped CSS, or compatibility aliases. Do not combine
   this with utility deletion.
6. Phase 4F - Group background padding exception.
   Audit `.wp-block-group.has-background { padding: 0 !important; }`. Either
   narrow it to known PNS section surfaces or create follow-up markup changes so
   group padding controls remain meaningful outside those surfaces.

Layout token simplification result:

- The provisional width-token chain has been reduced. The active model is:
  `theme.json` owns the canonical public `contentSize` and `wideSize`; private
  PNS aliases may bridge to the WordPress-generated variables; and
  `--pns--layout--site-frame-size` remains because PNS section/frame wrappers
  intentionally need a wider width than the readable content column.
- `--pns--layout--wide-breakout-size` and
  `--pns--layout--site-max-inline-size` are not active model tokens and should
  not be reintroduced unless a distinct design role is proven.
- `6jl8zwha` owns the current consistency pass that verifies helpers and Split
  Sections follow this simplified model without changing accepted visuals.

Do-not-combine rules:

- Do not change global `blockGap` in the same batch that removes or renames
  layout utilities.
- Do not remove `no-gap`, `grid`, `m-auto`, `mt0`, `mb05`, `vw-100`, `w-100`,
  `w-50-m`, `p1`, `p2-m`, `pl1`, `pr1`, `pr0`, `rubik`, or `lh0` in Phase 4
  unless a separate Phase 5 migration task is explicitly pulled forward.
- Do not remove root overflow suppression in Phase 4. That remains Phase 6.
- Do not use Wallace "unused custom property" output as deletion proof for
  tokens referenced by `theme.json`, PHP, saved content, or runtime-rendered
  markup.
- Do not mutate DB content for layout cleanup without a timestamped backup in
  `docs/jobs/live-adoption-db-backups/` and a dry-run/apply/dry-run script.

Acceptance checks:

- Each sub-batch has a clear changed surface, a commit, and a Dex note under
  `pwt0rcw1`.
- `pnpm compile:css` or the equivalent local binary path succeeds after every
  CSS or `theme.json` batch.
- The Playwright visual suite passes after every behavior-changing batch. Any
  accepted visual drift is documented with the route, viewport, and reason.
- Existing pages still match baseline or have documented realignment.
- Editor-facing controls remain honest for changed surfaces. If a control is
  still overridden by CSS, the owning surface is narrowed or documented.
- No saved-content utility or `pns-*` hook is removed without migration evidence
  and an explicit Phase 5 linkage.

Phase 4 implemented result:

- Phase 4A preflight passed on 2026-06-28:
  - active standalone global-styles row `5256` remained minimal:
    `{"version": 3, "isGlobalStylesUserThemeJSON": true }`;
  - WordPress block-template API checks returned no active custom
    `wp_template` or `wp_template_part` sources;
  - local Lightning CSS compile succeeded;
  - pre-change Playwright visual suite passed 51 of 51.
- Phase 4B width-token simplification landed:
  - `--pns--layout--wide-breakout-size` was removed;
  - `theme.json` now owns the wide-size math directly;
  - copy-width helpers read `--wp--style--global--content-size` with the same
    `44rem` fallback;
  - section/frame/footer/shop wrappers now use the renamed
    `--pns--layout--site-frame-size` token.
- Phase 4D broad layout override reduction landed for one proven no-behavior
  rule:
  - removed `body .is-layout-flex { position: relative; }`;
  - isolated full visual suite passed 51 of 51 after removal.
- Phase 4F first attempt was audited and rejected:
  - narrowing `.wp-block-group.has-background { padding: 0 !important; }` to
    PNS section/header/footer/shop surfaces caused broad desktop snapshot drift;
  - the attempted change was reverted because the drift was not pre-approved;
  - the failed run proved the selector was missing semantic ownership hooks, not
    that the broad override was correct.
- Phase 4F semantic-hook pass landed on 2026-06-28:
  - added the source-owned `pns-header__surface` hook and removed the header
    surface's inline block-support padding;
  - normalized `shop-intro-copy` source markup with explicit PNS synced-section
    hooks;
  - added `scripts/migrate-background-section-hooks.php` for the specific saved
    background groups on home, Mary Barbour, Shop, Edu Giveaway, and the
    `shop-intro-copy` reusable block;
  - applied the migration with rollback exports and reports in
    `docs/jobs/live-adoption-db-backups/`;
  - replaced the broad `.wp-block-group.has-background` `!important` reset with
    a scoped non-`!important` selector for `.pns-section`,
    `.pns-header__surface`, `.pns-footer`, and `.pns-footer-bottom-bar`.
- Phase 4F missed-page repair landed after user review:
  - the first semantic-hook pass covered the original Playwright routes but
    missed ordinary saved page content on `/herstories/`, `/artworks/`,
    `/shenanigans/`,
    `/shenanigans/workshop-unleashing-the-suffragette-spirit/`,
    `/shenanigans/glasgow-herstory-workshops/`, and
    `/educational-resources/`;
  - those routes contained 21 backgrounded `core/group` sections without
    `pns-*` hooks, causing the scoped reset to leave WordPress-generated
    background padding in place;
  - `scripts/migrate-background-section-hooks.php` now supports exact parsed
    block-path matching for saved-content backfills and adds semantic
    `pns-section pns-saved-section ...` classes for those 21 groups;
  - the repair was applied with rollback export and migration report:
    `docs/jobs/live-adoption-db-backups/2026-06-28-222048-background-section-hooks-before.json`
    and
    `docs/jobs/live-adoption-db-backups/2026-06-28-222048-background-section-hooks-after-report.json`;
  - `tests/visual/frontend.spec.ts` now includes a computed-style regression
    contract requiring saved background groups on those six routes to have a
    `pns-*` hook and zero computed padding.
- Published-page pattern reproducibility follow-up started after the 4F repair:
  - the new standard is that existing published pages should be reproducible
    from the provided standalone patterns and synced patterns;
  - saved sections that clearly match a pattern should carry that pattern's
    identity classes, for example red-line quote covers should carry
    `pns-section pns-quotes pns-blockquote-with-red-line`;
  - `scripts/migrate-semantic-wrapper-classes.php` is the preferred migration
    surface for recognized pattern copies. It now writes both rollback and
    after-report JSON files;
  - the first recognized backfill was applied on 2026-06-28 with rollback and
    report files
    `docs/jobs/live-adoption-db-backups/2026-06-28-230522-semantic-wrapper-classes-before.json`
    and
    `docs/jobs/live-adoption-db-backups/2026-06-28-230522-semantic-wrapper-classes-after-report.json`;
  - the backfill normalized existing recognized pattern copies including
    `pns/blockquote-with-red-line`, `pns/blockquote-cover`,
    `pns/previous-next`, page hero patterns, synced-section wrappers, and the
    saved education-pack template wrapper where the script matched them;
  - remaining anonymous saved content sections, especially generic text/image
    content on `/about/`, `/shenanigans/`, and policy/work-with-us pages, need
    a pattern-catalog decision before migration: map to an existing reusable
    pattern, create a new generic pattern, or mark as an intentional one-off.
- Phase 4G page pattern audit completed on 2026-06-29:
  - `scripts/audit-published-page-patterns.php` now audits `publish`,
    `private`, and `draft` page section candidates against the standalone
    pattern/synced-pattern library, while intentionally skipping the unsettled
    `news` page;
  - `patterns/page-hero.php` and `patterns/full-width-image-strip.php` were
    added so existing `pns-page-hero` and full-width separator image content
    have code-owned source patterns;
  - `scripts/migrate-semantic-wrapper-classes.php` backfilled reusable pattern
    identity classes for basic centred content, generic two-column sections,
    Mary Barbour style Herstory sections, image strips, and full-width image
    strips while preserving existing compatibility hooks;
  - the backup-backed apply wrote
    `docs/jobs/live-adoption-db-backups/2026-06-29-124122-semantic-wrapper-classes-before.json`
    and
    `docs/jobs/live-adoption-db-backups/2026-06-29-124122-semantic-wrapper-classes-after-report.json`;
  - a second backup-backed pass added deterministic pattern classes to
    draft/private page copies and wrote
    `docs/jobs/live-adoption-db-backups/2026-06-29-133236-semantic-wrapper-classes-before.json`
    and
    `docs/jobs/live-adoption-db-backups/2026-06-29-133236-semantic-wrapper-classes-after-report.json`;
  - the post-apply audit reported `215` page section candidates and `0`
    needing review;
  - intentional one-offs still visible in the page audit are the Ecwid
    storefront wrapper, legacy draft/private scaffold sections, and private
    test/editor fixtures.
- Interim proof before Phase 5:
  - Dex task `01n5msi3` tracks a rebuild proof for Mary Barbour and the front
    page before legacy utility retirement begins;
  - create draft/private rebuild fixtures using only core blocks, available
    plugin blocks, registered standalone patterns, and synced patterns;
  - record each section as one of: single existing pattern, synced pattern,
    composed block/pattern combination, missing reusable pattern, or intentional
    one-off;
  - combinations that must be repeated by an editor should become candidate
    prebuilt patterns before Phase 5, especially two-column sections where the
    media visually expands to the section edge;
  - the front-page proof confirmed that several Mary-era gap names were too
    narrow: `text-only`, split media/text left/right, edge media/text
    left/right, slideshow/text, uneven image strip, and muted quote-band
    patterns should be treated as general site-wide primitives, not
    `activist-*` patterns;
  - the four proposed split media variants may indicate inconsistent historical
    implementation rather than four true patterns. Before building them, compare
    the live block tree, responsive behavior, edge-bleed needs, and editor
    controls to decide whether the replacement should be one base split-section
    pattern with theme-published block styles, two orientation patterns plus
    edge/non-edge styles, or four explicit patterns;
  - block styles are allowed as theme-owned deliverables when the block tree is
    stable and only the presentation/layout variant changes. Full patterns are
    required when the editor needs a prebuilt nested block structure, required
    plugin block, starter content, fixed semantic hook, or fragile arrangement;
  - reserve profile-specific naming for genuinely biographical shapes, such as
    `profile-facts-media-left` and `profile-further-reading`;
  - `pns-two-columns` is currently overloaded. It maps ordinary split sections,
    slideshow split sections, and edge-to-edge media sections. Treat it as a
    compatibility class, not the future editor-facing primitive; Phase 5 should
    migrate/remove it only after explicit reusable patterns or block style
    variants exist and saved content has been backfilled;
  - do not replace live page markup during the proof. Live page replacement is a
    separate decision after visual comparison and editor review.
- Phase 4E block-gap/content-rhythm decision landed on 2026-07-01:
  - `theme.json` now sets global `styles.spacing.blockGap` to
    `var(--wp--custom--spacing--compact)`, making the WordPress block-gap
    default meaningful instead of permanently zero;
  - established PNS surfaces preserve their previous rhythm with scoped owners:
    top-level `main`, saved/editor flow content, direct `pns-section` children,
    Pattern QA labels, `.no-gap`, navigation, button groups, social links,
    columns, and split-section columns;
  - `styles/page-types/content-rhythm.css` owns saved/editor flow compatibility
    so generated block-gap margins do not stack on top of explicit paragraph,
    heading, component, and pattern rhythm;
  - stale comments that described this model as "after theme.json blockGap
    moved to zero" were corrected;
  - the homepage cascade contract now asserts the nonzero root/body block gap
    plus zero margins/gaps for preserved PNS surfaces;
  - focused regression passed: `content typography rhythm`, `homepage cascade
contracts`, and `saved background groups use semantic section hooks` passed
    30/30 across desktop, tablet, and mobile;
  - full visual snapshots still fail on existing route baselines/overflow
    surfaces, especially Pattern QA and Edu Giveaway screenshot dimensions, so
    do not treat those snapshot diffs as accepted design drift for Phase 4E.
    The computed-style contracts are the acceptance evidence for this batch.
- Phase 4E manual-control refinement added on 2026-07-01:
  - the compatibility layer that preserves saved/editor rhythm can still make
    some WordPress manual spacing controls misleading where CSS zeros generated
    flow margins or forces flex/grid gaps;
  - highest-risk surfaces are generated flow/constrained margins inside
    `.entry-content` and `.pns-section`, forced zero gaps on buttons, columns,
    social links, and navigation, `theme.json` important margins for spacer and
    query pagination blocks, shop/vendor important spacing, and explicit
    utilities such as `mt0`, `no-gap`, and `mb05`;
  - inline block-support styles usually win over normal CSS, but not over
    `!important` rules and not when WordPress serializes a control to generated
    class/container CSS that a stronger theme selector beats;
  - follow-up task `yyqsfqf5` should prove manual block gap, margin, and
    padding controls remain meaningful outside documented PNS fixed-layout
    surfaces, then narrow or document any intentional overrides.
- Phase 4E documentation note satisfied on 2026-07-01:
  - `docs/css/README.md` now documents the nonzero global block-gap default and
    the saved/editor/PNS-section compatibility owners, so it no longer reads as
    a permanent endorsement of global `blockGap: 0`.
- Dex follow-ups:
  - `0zyaaybo` resolved the group background padding realignment with semantic
    hooks and a scoped non-`!important` reset.
  - `01n5msi3` tracks the Mary Barbour/front-page rebuild proof before Phase 5.
  - `yyqsfqf5` tracks the manual-spacing-control honesty refinement that follows
    the Phase 4E `blockGap` change.
  - `yvdzp2xw` tracks restoration of the full visual snapshot gate after the
    Phase 4E targeted contract pass exposed existing route baseline, fixture,
    and overflow failures.
  - `cynp3u0k` tracks split-section pattern/block-style normalization and the
    eventual `pns-two-columns` compatibility migration. The first implementation
    slice added theme-owned split-section block styles plus
    `pns/split-section-image`, `pns/split-section-slideshow`, and
    `pns/text-only-section` under child task `or591vkb`. Child task `6ck02c9j`
    then migrated the saved `pns-two-columns` sections on the front page,
    about, artworks, herstories, educational resources, shenanigans, the two
    shenanigans child pages, edu giveaway, and the local pattern QA page to
    `pns-split-section` plus explicit normal/edge media-left/media-right block
    styles. `/news/` remains excluded by policy.
  - `6ck02c9j` migration evidence:
    `docs/jobs/live-adoption-db-backups/2026-06-29-153156-split-section-classes-before.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-153156-split-section-classes-after-report.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-153501-split-section-classes-before.json`,
    and
    `docs/jobs/live-adoption-db-backups/2026-06-29-153501-split-section-classes-after-report.json`.
    The second pass repaired saved static block HTML so frontend markup no
    longer retained old `pns-two-columns`, `grid`, `m-auto`, `no-gap`,
    `vw-100`, `w-100`, or `lh0` layout classes inside migrated split-section
    wrappers. Post-apply dry-run reported `0` records and `0` section
    candidates; direct DB query found no non-news `pns-two-columns` page
    content; the page-pattern audit reported `232` checked candidates and
    `0` needing review.
    Full Playwright visual regression passed after accepting the expected mobile
    spacing/height change from the split-section utility cleanup by refreshing
    only `home-mobile-darwin.png` and `edu-giveaway-mobile-darwin.png`
    (`142` passed, `2` skipped).
  - `bt71qkpe` aligns disposable template drafts before Phase 5 utility
    retirement. It preserves top-level semantic `pns-*` owners, fixes the live
    `Connect With Us` and `Stay in Touch` heading-to-paragraph rhythm with a
    scoped synced-section rule, and bulk-copies only the three `**TEMPLATE*`
    drafts from canonical pattern-backed source pages: `1758 <- 49`,
    `1761 <- 1066`, and `1828 <- 42`. Migration evidence:
    `docs/jobs/live-adoption-db-backups/2026-06-29-170456-recognized-draft-pages-before.json`
    and
    `docs/jobs/live-adoption-db-backups/2026-06-29-170456-recognized-draft-pages-after-report.json`.
    The original-content draft pages `2874`, `1833`, `1848`, `1855`, `1869`,
    and `1797` were accidentally included in the first pass, then immediately
    restored from the pre-copy backup. Restore evidence:
    `docs/jobs/live-adoption-db-backups/2026-06-29-172437-recognized-draft-pages-original-content-restore-before.json`
    and
    `docs/jobs/live-adoption-db-backups/2026-06-29-172437-recognized-draft-pages-original-content-restore-after-report.json`.
    The corrected align script targets only the three `**TEMPLATE*` drafts, and
    post-restore dry-run reported those three targets `changed=no`. The six
    restored original-content drafts remain a manual alignment queue where text
    and images must be preserved while section wrappers are brought onto the new
    pattern identities. Dex task `xvr5eg5y` records the rollback correction;
    pending task `97p9lsc4` tracks the preservation-first manual pass. Targeted
    Playwright passed `12` checks after accepting the expected home
    tablet/mobile snapshot height change from the rhythm fix.
  - `97p9lsc4` then aligned the six restored original-content drafts by
    parser-backed, ID-scoped migration rather than bulk-copying source pages.
    Profile drafts `2874`, `1833`, `1848`, `1855`, and `1869` now use
    `pns-activist-hero` cover structure and current Herstories section
    identities; draft `1797` now uses `pns-page-hero` and `pns-split-section`
    identities while preserving the Wikipedia hero focal point `82% 35%`.
    Evidence:
    `docs/jobs/live-adoption-db-backups/2026-06-29-174655-original-draft-pattern-identities-before.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-174655-original-draft-pattern-identities-after-report.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-175104-original-draft-pattern-identities-before.json`,
    and
    `docs/jobs/live-adoption-db-backups/2026-06-29-175104-original-draft-pattern-identities-after-report.json`.
    Final dry-run reported all six targets `changed=no`; page pattern audit
    checked `224` section candidates with `0` needing review. Five profile
    drafts still contain missing reusable block ref `1391`; keep that as a
    separate content cleanup because prior audit guidance says to clean it up,
    not silently migrate it.
  - `rnl1f0yo` corrected restored draft sections that had identity classes but
    still used old wide/full nested wrappers. User review then clarified that
    the first correction was still too wide because `pns-section-inner` is the
    site-frame wrapper, not the canonical `44rem` `contentSize` column.
  - `h4s3yx7w` corrects that content-size framing contract. Source patterns
    `pns/suffragette-stats` and `pns/previous-next` now use
    `pns-content-frame` for stat/control content. Saved restored drafts now
    frame mapped `SUFFRAGETTE STATS` sections and previous/next controls with
    `pns-content-frame`, while Herstories text/media and facts sections keep
    `pns-section-inner` so they match the live profile-page layout contract.
    Evidence:
    `docs/jobs/live-adoption-db-backups/2026-06-29-180814-original-draft-pattern-identities-before.json`
    ,
    `docs/jobs/live-adoption-db-backups/2026-06-29-180814-original-draft-pattern-identities-after-report.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-184200-original-draft-pattern-identities-before.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-184200-original-draft-pattern-identities-after-report.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-185815-original-draft-pattern-identities-before.json`,
    and
    `docs/jobs/live-adoption-db-backups/2026-06-29-185815-original-draft-pattern-identities-after-report.json`.
    A follow-up shape-based pass then split the profile sections more precisely:
    copy-only profile sections now use `pns-text-only-section`; copy+media
    profile sections now use `pns-split-section is-style-pns-media-right`;
    facts, stats, previous/next, image strips, and the Wikipedia split sections
    keep their own pattern contracts. Additional evidence:
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
    saved HTML matches the reusable `pns/split-section-image` contract:
    direct spacer blocks were removed, columns are `alignfull`, and inherited
    `66/33` column widths were cleared. Final evidence:
    `docs/jobs/live-adoption-db-backups/2026-06-29-193923-original-draft-pattern-identities-before.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-193923-original-draft-pattern-identities-after-report.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-194615-original-draft-pattern-identities-before.json`,
    and
    `docs/jobs/live-adoption-db-backups/2026-06-29-194615-original-draft-pattern-identities-after-report.json`.
    Final dry-run reported all six targets `changed=no`; page pattern audit
    checked `224` candidates with `0` needing review before the correction, and
    authenticated draft `2874` evidence showed the text-only sections at
    contentSize and `Background` as the intended full-width split-section image
    shape.
  - `lcg0ij6i` follows up user review of draft `1833`. The Agnes facts section
    still carried saved spacer/empty-paragraph hacks, inherited `66/33` column
    widths, and no `m-auto pns-copy-column` wrapper; the "quote here? - as no
    suffragette stats?" section was neither a valid stats pattern nor a quote
    pattern. The migration now normalizes restored draft facts sections to the
    `pns/activist-facts` source shape, strips empty spacer/`<br>` blocks,
    preserves real images, and removes the invalid Agnes placeholder section.
    An initial apply exposed an empty-attrs serializer bug and was restored from
    `docs/jobs/live-adoption-db-backups/2026-06-29-215935-original-draft-pattern-identities-before.json`
    before the corrected migration was reapplied. Final evidence:
    `docs/jobs/live-adoption-db-backups/2026-06-29-220600-original-draft-pattern-identities-before.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-220600-original-draft-pattern-identities-after-report.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-221014-original-draft-pattern-identities-before.json`,
    and
    `docs/jobs/live-adoption-db-backups/2026-06-29-221014-original-draft-pattern-identities-after-report.json`.
    Final dry-run again reported all six restored draft targets `changed=no`;
    saved block inspection for `1833` showed `More about agnes` as
    `pns-split-section` and `Fun Facts about agnes` as `pns-activist-facts`
    with the expected copy wrapper and preserved image column.
    User editor review then exposed invalid-block warnings across the touched
    Agnes sections. The migration now also removes stale generated block attrs
    from migrated sections (`wp-block-heading` comment attrs, empty
    `style.color` arrays, empty `animationsForBlocks`, stale split-column style
    attrs), preserves nested child cleanup instead of discarding it at the
    parent, and rebuilds normalized hero heading/active-date markup so block
    comments match saved HTML. Evidence:
    `docs/jobs/live-adoption-db-backups/2026-06-29-224451-original-draft-pattern-identities-before.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-224451-original-draft-pattern-identities-after-report.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-224758-original-draft-pattern-identities-before.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-224758-original-draft-pattern-identities-after-report.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-225102-original-draft-pattern-identities-before.json`,
    and
    `docs/jobs/live-adoption-db-backups/2026-06-29-225102-original-draft-pattern-identities-after-report.json`.
    Independent verifier review then found two remaining wrapper-shell issues:
    nested `pns-split-section__copy` and `pns-text-only-section__inner` groups
    had block comments but no saved wrapper `<div>`. The migration now rebuilds
    nested `core/group`, `core/columns`, and `core/column` shells for migrated
    sections, with column widths preserved as `flex-basis`. Final wrapper
    evidence:
    `docs/jobs/live-adoption-db-backups/2026-06-29-225713-original-draft-pattern-identities-before.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-225713-original-draft-pattern-identities-after-report.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-225833-original-draft-pattern-identities-before.json`,
    and
    `docs/jobs/live-adoption-db-backups/2026-06-29-225833-original-draft-pattern-identities-after-report.json`.
    Final dry-run reported all six targets `changed=no`; Agnes saved-content
    checks found the split copy wrapper, text-only inner wrapper, facts copy
    wrapper, and corrected `h1` hero heading present; a targeted WP-CLI scan
    checked `46` migrated draft sections with `0` stale serialization markers;
    page-pattern audit checked `223` candidates with `0` needing review. A
    final safety pass applied the same serialization cleanup to the
    profile-draft `pns-shop-intro` sections so the editor page is not left with
    stale generated attrs in the legacy shop block. Evidence:
    `docs/jobs/live-adoption-db-backups/2026-06-29-230632-original-draft-pattern-identities-before.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-230632-original-draft-pattern-identities-after-report.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-29-230844-original-draft-pattern-identities-before.json`,
    and
    `docs/jobs/live-adoption-db-backups/2026-06-29-230844-original-draft-pattern-identities-after-report.json`.
    Final post-shop verification: migration dry-run reported all six targets
    `changed=no`; Agnes raw saved content had no stale heading-class,
    empty-style, empty-animation, or empty-`br` paragraph markers; targeted
    WP-CLI scan checked `51` migrated/shop-intro draft sections with `0` stale
    serialization markers; page-pattern audit checked `223` candidates with
    `0` needing review.
  - 2026-06-30 correction: the prior PHP `parse_blocks()`/`serialize_blocks()`
    and marker scans did not prove Gutenberg editor validity. User editor
    review showed the touched draft sections still appeared as unexpected or
    invalid content. A JavaScript validator using WordPress core's bundled
    `block-serialization-spec-parser`, `wp.blocks.parse()`, registered core
    blocks, and `wp.blocks.validateBlock()` found `159` invalid blocks across
    the six current restored drafts. The current state was backed up to
    `docs/jobs/live-adoption-db-backups/2026-06-30-restore-editor-invalid-drafts-current-before.json`
    and the six draft pages were restored to
    `docs/jobs/live-adoption-db-backups/2026-06-29-174655-original-draft-pattern-identities-before.json`,
    reducing editor-invalid blocks to `32`.
  - The unsafe PHP migration
    `app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/migrate-original-draft-pattern-identities.php`
    is retired and now fails closed. Future saved-content migrations must use
    the WordPress JavaScript parser/validator workflow before DB writes.
    Reusable validation tooling lives at
    `app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-editor-block-content.mjs`
    and `pnpm check:editor-content`. Restored validation evidence:
    `docs/jobs/live-adoption-db-backups/2026-06-30-restored-draft-editor-validation.json`.
  - Follow-up parser-backed repair corrected source pattern save output for
    `pns/activist-hero`, `pns/page-hero`, and `pns/activist-facts`, then used
    `scripts/repair-editor-block-content.mjs` to remove inherited legacy
    serialization drift without reconstructing text-bearing blocks. The six
    restored drafts reached `0` invalid blocks under
    `pnpm check:editor-content`.
  - `scripts/repair-draft-layout-classes.mjs` then applied the current layout
    contracts with WordPress core's JS parser/serializer: profile/page heroes,
    text-only sections, text/media sections, facts, stats, previous/next rows,
    image strips, and Wikipedia split sections now carry the corresponding
    semantic wrappers. Live DB validation after apply remained `0` invalid
    blocks across `2874`, `1833`, `1848`, `1855`, `1869`, and `1797`.
    Evidence:
    `docs/jobs/live-adoption-db-backups/2026-06-30-editor-valid-repair-before.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-30-editor-valid-repair-after-validation.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-30-draft-layout-class-repair-before.json`,
    `docs/jobs/live-adoption-db-backups/2026-06-30-draft-layout-class-repair-report.json`,
    and
    `docs/jobs/live-adoption-db-backups/2026-06-30-draft-layout-class-repair-after-validation.json`.
  - Remaining manual judgement before Phase 5: one Helen Fraser section has a
    non-empty side column that the script refused to flatten, and one Wikipedia
    split section lacks a separate inner copy group. Both are editor-valid but
    should be visually reviewed before utility retirement.
  - Phase 5 should keep the text-plus-media transition explicit:
    `pns/activist-text-media` and `pns/activist-facts` still behave like wider
    media layouts even though they predate the new `pns-split-section` source
    API. Before retiring supporting utilities, either convert those shapes to
    split-section variants or replace them with clearer generic/profile
    primitives.
  - `ue9g85h7` tracks the global `blockGap` and content-rhythm realignment
    decision.

Initial rejected 4F visual references:

- `[desktop] visual snapshot: home`
  `tests/visual/frontend.spec.ts:148`, screenshot assertion at line `161`.
  Expected `1448x5599`, received `1473x5671`; diff ratio `0.16`.
  Artifact paths from the failed run:
  `test-results/visual-frontend-visual-snapshot-home-desktop/home-actual.png`,
  `home-diff.png`, and `error-context.md`.
- `[desktop] visual snapshot: mary-barbour`
  `tests/visual/frontend.spec.ts:148`, screenshot assertion at line `161`.
  Expected `1448x7535`, received `1473x7710`; diff ratio `0.29`.
  Artifact paths from the failed run:
  `test-results/visual-frontend-visual-snapshot-mary-barbour-desktop/mary-barbour-actual.png`,
  `mary-barbour-diff.png`, and `error-context.md`.
- `[desktop] visual snapshot: edu-giveaway`
  `tests/visual/frontend.spec.ts:148`, screenshot assertion at line `161`.
  Expected `2136x6246`, received `2094x6322`; diff ratio `0.12`.
  Artifact paths from the failed run:
  `test-results/visual-frontend-visual-snapshot-edu-giveaway-desktop/edu-giveaway-actual.png`,
  `edu-giveaway-diff.png`, and `error-context.md`.
- `[desktop] visual snapshot: shop`
  `tests/visual/frontend.spec.ts:148`, screenshot assertion at line `161`.
  Expected `1448x3614`, received `1448x3634`; diff ratio `0.17`.
  Artifact paths from the failed run:
  `test-results/visual-frontend-visual-snapshot-shop-desktop/shop-actual.png`,
  `shop-diff.png`, and `error-context.md`.
- `[desktop] visual snapshot: pattern-qa`
  `tests/visual/frontend.spec.ts:148`, screenshot assertion at line `161`.
  The run was interrupted after this failure started; expected height `8569`,
  received height `8604`.
  Artifact paths from the failed run:
  `test-results/visual-frontend-visual-snapshot-pattern-qa-desktop/pattern-qa-actual.png`,
  `pattern-qa-diff.png`, and `error-context.md`.

Phase 4 verification:

- `./node_modules/.bin/lightningcss --bundle --minify --sourcemap --browserslist`
  succeeded for both `styles/frontend.css` and `styles/editor.css`.
- `./node_modules/.bin/stylelint "style.css" "styles/**/*.css" --ignore-pattern "styles/dist/**"` passed.
- `./node_modules/.bin/prettier --check ...` passed for touched authored CSS
  and `theme.json`.
- Full Playwright visual suite passed 51 of 51 after the retained Phase 4B/4D
  changes.
- Focused 4F retry passed the five previously failed desktop visual snapshots:
  `home`, `mary-barbour`, `edu-giveaway`, `shop`, and `pattern-qa`.
- Full Playwright visual suite passed 51 of 51 after the semantic-hook 4F
  implementation.
- Post-repair dry-run reported `changed=no` for all 33 background-section hook
  targets.
- Rendered probes on the six reported routes found no remaining background
  groups without `pns-*` hooks or nonzero computed padding.
- New saved-background-group regression contract passed 6 of 6 on
  `PNS_BASE_URL=http://localhost:3000` and 6 of 6 on the default
  `http://localhost:10008` target.
- Semantic-wrapper migration post-apply dry-run reported `changed=no` for all
  28 recognized pattern-copy targets.
- A new red-line quote-cover regression contract checks that rendered saved
  quote covers with `Red-Keyline.svg` carry the same
  `pns-blockquote-with-red-line`, `pns-quotes`, and `pns-section` classes as
  the code-owned pattern.

## Phase 5 - Retire Or Replace Legacy Utility Classes In Batches

Status: classification complete; removal/migration scheduled separately before
designer handback.

Goal: phase out transitional utilities without breaking saved content.

Scope correction:

- `pns-*` classes are not legacy utilities by default. Phase 5 may simplify or
  move CSS declarations that target them, but it should not remove those hooks
  from markup unless a specific hook is deliberately retired with migration
  evidence.

Disposition options:

- Keep: class remains useful and honest.
- Rename/reframe: class becomes a semantic or layout-role class.
- Replace in source only: no saved content uses it.
- Migrate saved content: DB backup, dry-run, apply, post-apply dry-run.
- Alias temporarily: old class remains as compatibility while content migrates.
- Delete: no live usage and regression checks pass.

Batching rule:

- One utility family per batch.
- No broad utility deletion combined with `theme.json` spacing changes.
- Each batch gets compile, style, visual, and relevant editor checks.

Acceptance checks:

- Every removed utility has evidence that it is unused or migrated.
- Every retained utility has a current owner and reason.
- CSS complexity decreases without reducing author control.

2026-07-04 handback update:

- The original `gpi19dat` pass completed classification only. Several generic
  utilities are still live in saved content and must not be removed without a
  migration batch.
- Dedicated removal plan:
  `docs/jobs/2026-07-04-legacy-utility-class-removal-plan.md`.
- Dex parent: `to9qfw83` (`Remove legacy utility classes before designer
handback`).
- Current exact DB inventory shows zero saved-content records for `no-gap`,
  `mt0`, `mb05`, `pl1`, `pr1`, and `max-inline-size`, but high-volume usage
  remains for `grid`, `m-auto`, `pr0`, and `rubik`, and live synced/template
  usage remains for `vw-100`, `w-100`, `w-50-m`, `lh0`, `p1`, and `p2-m`.

## Phase 6 - Fix Root Overflow And Vendor Override Debt At Source

Status: implementation complete; final full-suite visual gate blocked by
concurrent shared-block snapshot drift.

Goal: stop masking layout defects with global clamps and reduce plugin override
debt where configuration can own the behavior.

Overflow work:

- Trace the real overflowing element on Edu Giveaway and related routes.
- Inspect `alignfull`, `vw-100`, full-width media, EmailOctopus, Ecwid, and
  navigation drawer behavior.
- Remove or narrow `html, body` overflow hiding only after the source is fixed.

2026-07-01 review update:

- The original Edu Giveaway-specific overflow note is stale. Page `4629`
  `/edu-giveaway/` is currently assigned to the `page-no-contact-form` template,
  and the rendered route uses `pns-template-page-no-contact-form`, not
  `pns-template-education-pack-giveaway`.
- The remaining `#contact` marker on `/edu-giveaway/` is a content heading
  anchor, not the EmailOctopus contact-form template part.
- With the global `html, body` overflow clamp disabled in a browser probe,
  `/edu-giveaway/` showed `0px` document overflow at `390px`, `768px`, and
  `1440px` viewport widths.
- The old filesystem template `templates/education-pack-giveaway.html` still
  exists and should be treated as stale source unless a fresh WordPress template
  source check proves it is still active for another route.
- The clamp is still not proven globally removable. The same clamp-disabled
  smoke test showed remaining document overflow on `/pns-pattern-qa/`
  (`32px` at `768px`, `368px` at `1440px`) and small desktop overflow on `/`,
  `/herstories/mary-barbour/`, and `/shop/` (`8px` at `1440px`).
- Current evidence points to Jetpack slideshow track geometry as the main
  remaining overflow source, with EmailOctopus honeypot fields appearing
  off-screen but not increasing document scroll width in the Edu Giveaway probe.
- Phase 6 should therefore reframe this work from "fix Edu Giveaway overflow" to
  "remove stale Edu Giveaway compatibility, then fix or narrowly contain the
  remaining slideshow/vendor overflow before removing the root clamp."

Vendor work:

- Classify each Ecwid and EmailOctopus override as:
  account setting, plugin setting, unavoidable runtime CSS, or dead override.
- Keep unavoidable CSS scoped to the plugin surface.
- Avoid broad vendor selectors that affect normal WordPress controls.

2026-07-04 result:

- Re-ran the clamp-disabled overflow probe across `/`,
  `/herstories/mary-barbour/`, `/edu-giveaway/`, `/shop/`, and
  `/pns-pattern-qa/` at `390px`, `768px`, and `1440px`.
- Confirmed `/edu-giveaway/` still has `0px` document overflow without the
  root clamp. Jetpack slideshow tracks and EmailOctopus honeypot fields can
  extend outside their local frames, but current rendered wrappers contain them
  and they do not create document scroll width.
- Traced the remaining `8px` desktop document overflow to `.vw-100` sections
  inside the `pns-section-inner` frame. The utility kept `100vw` width but did
  not offset itself inside the slightly narrower design frame.
- Fixed the source by centering `.vw-100` with
  `margin-inline: calc((100% - 100vw) / 2)`, preserving full-viewport visual
  width while removing the right-edge overflow.
- Removed the stale global `html, body` `overflow-x: hidden !important` clamp
  from base element CSS.
- Added structural horizontal containment to `.wp-site-blocks > main` with
  `overflow-x: clip`, matching the Animations for Blocks FAQ guidance that
  off-screen entry transforms need a site-specific parent containment boundary.
  This keeps the header/footer shell outside the clipped ancestor and avoids a
  plugin-specific override for a general page-shell responsibility.
- Removed the stale `.pns-suffragette-facts` animation overflow workaround now
  that content-shell containment covers animated sections site-wide.
- Regression coverage now asserts the body overflow is visible, the main content
  shell clips horizontal overflow, `.vw-100` remains centered, and covered
  routes stay within viewport scroll width.
- Narrowed the broad `.form-control:focus` EmailOctopus override to
  `.emailoctopus-form .form-control:focus`; Ecwid and EmailOctopus runtime
  overrides remain classified as scoped vendor CSS, not Site Editor controls.
- Validation completed:
  - CSS compile passed.
  - CSS lint and Prettier checks passed.
  - Focused overflow probe passed on `/`, `/herstories/mary-barbour/`,
    `/edu-giveaway/`, `/shop/`, `/pns-pattern-qa/`, `/artworks/`,
    `/herstories/`, `/educational-resources/`, and `/shenanigans/` at `390px`,
    `768px`, `900px`, `950px`, `1000px`, and `1440px`.
  - Focused Playwright checks for homepage cascade contracts, header visual
    column, and header navigation overlap passed.
  - The full visual suite is not currently a reliable closeout signal because
    concurrent Our Shop/Ecwid shared-block work is changing shared page
    snapshots during validation. The final full-suite attempt failed on moving
    visual snapshots for shared-block pages and intermittent saved-background
    hook checks that pass on focused rerun/direct DOM probe.

Acceptance checks:

- Root overflow hiding is removed, narrowed, or explicitly justified.
- Vendor `!important` rules are reduced or documented as unavoidable.
- Visual checks cover the affected plugin surfaces.

## Phase 7 - Validate Frontend, Editor, Migration, And Live Adoption

Status: complete.

Goal: finish with evidence that the standalone theme remains adoptable.

Validation commands and checks:

- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check`
- Block-template validation using the existing standalone validator.
- WP-CLI checks for active theme, template source, `wp_global_styles`,
  `wp_navigation`, and `wp_block` records.
- Saved-content migration dry runs return no remaining targets after apply.
- Playwright visual tests on local routes, with elevated local process access if
  Chromium hits the known Mac sandbox issue.
- Targeted editor checks for any changed block-support or pattern surface.

Acceptance checks:

- Dex `52311232` final vendor override debt re-evaluation is complete or any
  retained vendor/plugin override debt is explicitly split to a follow-up with
  owner, evidence, and reason.
- Evidence is captured in this plan, linked reports, or Dex completion results.
- Any intentional visual change is named.
- Full Playwright visual snapshots are green again before closing the parent
  rollout, unless a remaining failure is explicitly blocked by a named fixture
  or external state owner. Do not refresh baselines for Pattern QA, Edu
  Giveaway, `/herstories/`, or other failing routes until each route/viewport is
  classified as intended change, stale baseline, fixture drift, overflow bug, or
  content-source mismatch.
- Any remaining risk has a follow-up Dex task instead of living as a TODO.

Closeout evidence, 2026-07-06:

- `52311232` is complete. The final vendor override ledger is recorded in
  `docs/jobs/2026-07-06-vendor-override-debt-final-reevaluation-plan.md`.
- Theme checks passed:
  - `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone run check`
  - `php app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-block-templates.php`
  - `git diff --check`
- Editor validation passed:
  - `pnpm exec node scripts/validate-editor-block-content.mjs templates/*.html parts/*.html`
    reported `0 invalid` blocks for all 14 filesystem templates and both
    template parts after rerunning elevated for the known Chromium macOS sandbox
    issue.
  - `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone run test:editor`
    passed `8/8`, including the private fixture, light-surface editor contract,
    real page smokes, and split-section editor controls.
- WP-CLI source checks confirmed the active stylesheet and template are both
  `protestsandsuffragettes-standalone`; front page is page `49`; active plugins
  include `animations-for-blocks`, `ecwid-shopping-cart`, `emailoctopus`,
  `jetpack`, `pns-blocks`, and `pns-herstories`; active standalone
  `wp_global_styles`, saved templates, template parts, navigation menus, and
  reusable blocks were recorded in the closeout command output.
- Migration dry-runs are clean for retired color slugs, rhythm token variables,
  `pns/previous-next`, legacy spacing utilities, and retired activist naming.
- Saved template sync found two invalid stale DB overrides, Blog Home
  `wp_template` row `6186` and Search Results row `6221`. Backup:
  `docs/jobs/layout-contract-db-backups/20260706-before-5koa-template-sync.sql`.
  The existing sync script reapplied `templates/home.html` and
  `templates/search.html`; the post-apply dry-run now reports `0` updates and
  exported DB content validates at `0 invalid` blocks for both rows.
- Focused post-sync Playwright coverage passed `18/18` across desktop, tablet,
  and mobile for Home, homepage cascade, native Search, Search no-results, and
  News archive pagination.
- Full Playwright visual suite passed after the DB sync:
  `235 passed`, `2 skipped`.

No baseline refresh was required for this closeout. The saved-template DB sync
was corrective alignment: the live DB copies were invalid serialized block
content, while filesystem source validated cleanly.

## Immediate Next Step

The `511irrlz` rationalization parent is ready for Dex closeout. Remaining open
work belongs to adjacent plans: dark-surface alignment/documentation, Site
Identity and Site Editor control parity, motion polish, and layout-stability
planning.
