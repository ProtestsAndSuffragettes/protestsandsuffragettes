# Unified Inner Content Rail Plan

Created: 2026-07-14.

Status: Implemented and verified locally on 2026-07-14.

## Delivery record — 2026-07-14

- `settings.layout.wideSize` is now the single `1500px` broad-width authority;
  the duplicate custom `site-frame-size` setting and its consumers are gone.
- `settings.custom.layout.content-rail` provides `roomy` at the base width and
  `section` from `782px`. Theme and project-block CSS resolve this through an
  effective private alias with regular/generous resilience fallbacks.
- Theme frames, shell consumers, body panels, and Split Section frontend/editor
  geometry now consume that rail. Edge Split media retains its frame-edge and
  media-seam flushness; its copy retains only the outer/free rail.
- Code-backed Home and Herstories archive templates no longer serialize a
  competing outer `regular` horizontal inset or obsolete zero copy padding.
- The saved Split Section migration changed 27 Local records / 77 copy
  wrappers, after dry-run, and wrote a rollback export at
  `docs/jobs/content-rail-db-backups/20260714-075809-split-section-copy-padding-before.json`.
  Its immediate second dry run found zero further changes.
- Targeted site-frame visual coverage passed all 11 desktop assertions,
  including `20px` at `390px`, `36px` from `782px`, native `wideSize`, no
  overflow, and edge Split seams. The editor smoke coverage passed for Home,
  Mary Barbour, and Education Pack fixtures; the lean visual gate was run
  against Local as the final regression lane.

## Decision

Treat the **wide/site-frame width** and the **inner content rail** as separate
layout contracts:

| Contract                 |                                                           Initial value | Purpose                                                                  |
| ------------------------ | ----------------------------------------------------------------------: | ------------------------------------------------------------------------ |
| Broad width / site frame |                   native `settings.layout.wideSize`, initially `1500px` | One maximum width for WordPress wide content and PNS broad visual panels |
| Inner content rail       | named `theme.json` custom layout setting: `roomy` base / `section` wide | Minimum inline inset for copy and controls inside that panel             |
| Editorial measure        |                      existing `44rem` / scoped `57.5rem` where approved | Reading width, not a general-purpose gutter                              |

Collapse the current duplicate `site-frame-size` custom token into the native
`settings.layout.wideSize` setting. `wideSize` is the only `1500px` width
authority; PNS panel, section, header/footer, Shop, and Split Section geometry
will consume its generated `--wp--style--global--wide-size` value through a
PNS alias. The PNS classes remain because they describe distinct layout
behaviour, not distinct width values.

The rail must be a first-class `theme.json` custom layout configuration, not a
series of component references to `--wp--preset--spacing--generous`. Name the
semantic token family **content rail**:

```json
"settings": {
  "custom": {
    "layout": {
      "content-rail": {
        "base": "var(--wp--preset--spacing--roomy)",
        "wide": "var(--wp--preset--spacing--section)"
      }
    }
  }
}
```

WordPress will generate
`--wp--custom--layout--content-rail--base` and
`--wp--custom--layout--content-rail--wide`. CSS will derive one effective
private `--pns--layout--content-rail` alias at the relevant breakpoint. The
configured values may instead become one approved fluid `content-rail` leaf if
visual review shows that a continuous scale is preferable. In either form they
fall back to the current spacing presets when configuration is absent. The
accepted configured values are `roomy` / `1.25rem` at the base width and
`section` / `2.25rem` from `782px`; use the preset references rather than
copying those literal values.

This is a theme configuration knob for layout owners; it does not imply an
editor-facing spacing control on every block. A new public spacing preset is
only appropriate if editors genuinely need to choose the rail themselves.

The rail token family is consumed by each named layout family. Do not apply
`.pns-section-frame` globally: it also changes
maximum width, and would incorrectly constrain cards, media, vendor output,
and intentional full-bleed art.

## Current evidence

- The standalone theme is active. It currently has two coupled `1500px`
  inputs: `wideSize` references the custom site-frame value. This plan removes
  that duplicate source of truth and makes `wideSize` canonical.
- `.pns-content-frame` and `.pns-section-frame` already apply `1rem` padding
  at every width and increase it to `2rem` at `782px`. The observed problem is
  therefore inconsistent ownership, not a mobile-only media query.
- The homepage Wikipedia panel is a project-owned `pns/split-section` with the
  `edge-media-right` variant. Its renderer supplies the capped
  `.pns-site-frame-panel`, but its copy rail is separately implemented by the
  block's grid and desktop padding overrides.
- At desktop widths the Split Section deliberately removes the copy padding on
  the side away from the media. Its grid offset is a parallel gutter system.
  This needs to consume the shared rail explicitly rather than relying on a
  visually similar implementation.
- Some project-owned templates/patterns already serialize `regular` inline
  horizontal padding around a nested `.pns-content-frame`. The durable outcome
  is one rail owner: after proof and a DB/file authority check, remove the
  outer inline horizontal padding and retain the inner frame rail. Do not mask
  that double inset with a broad `!important` override.

## Completed public-scope survey — 2026-07-14

This is a complete first-pass survey of the current public route set, not yet
proof that every individual rail is correct at every breakpoint.

- **Published content:** all 15 pages, 4 posts, and 6 Herstories were
  inventoried from the live database, including their template assignment.
- **Shared public routes:** the homepage, News archive, Herstories archive,
  Search, and a 404 route were rendered as separate route families.
- **Rendered desktop check:** every route above was loaded locally at `1920px`.
  Every rendered `pns-site-frame-panel` measured `1500px`, centred at `x=210`,
  and every route had `document.scrollWidth === 1920px`.
- **Split Section scope:** the homepage has three; the standard public pages
  containing them have one to four each; the two full-width news posts have
  five edge variants between them; and three Herstories contain four
  edge-right variants. Other public Herstories use normal Split Section
  variants. These are project-owned plugin consumers.
- **Non-Split scope:** default posts and the Contact/success templates use
  constrained post content without a saved PNS frame; Shop uses a section
  frame; legal pages, search/404, archive/card surfaces, Herstory facts/stats,
  covers, image strips, and synced sections have distinct owners. They are
  controls and/or explicit-exception candidates, not automatic recipients of a
  Split Section rule.

Therefore the proposed fix is consistent as a **shared token and declared
component contract**, but not as a single blanket CSS selector. Phase 0 must
now add breakpoint-level geometry measurements for every route family below
before Phase 1 changes production CSS.

### Required breakpoint acceptance matrix

The detailed rail measurements should use the following representatives at
`390`, `782`, `960`, `1500`, `1920`, and `2560px`, followed by a lightweight
all-public-route overflow sweep:

| Contract family                     | Representative routes                             | What must be measured                                                                           |
| ----------------------------------- | ------------------------------------------------- | ----------------------------------------------------------------------------------------------- |
| Global shell and real edge variants | `/`                                               | Header/footer rail; both edge Split directions; Read All About It and Shop Intro                |
| Saved rich-page variation           | `/artworks/`                                      | Hero, quote, and both edge directions with saved content                                        |
| Normal Split control                | `/shenanigans/`                                   | Non-edge split keeps its intended geometry                                                      |
| Herstory components and archive     | `/herstories/mary-barbour/`, `/herstories/`       | Facts, stats, strips, quote, entry navigation, cards, and animation-safe measurements           |
| News/query/editorial measure        | `/news/`, `/news/work-with-us-argyll/`            | Featured post, news-more frame, pagination, and scoped prose measure                            |
| Synced patterns                     | `/contact-us/` plus a Connect Social host/fixture | Section-frame and section-inner contracts independently of the homepage                         |
| Vendor shell                        | `/shop/` plus a product/cart route                | Theme outer rail only; Ecwid internals remain a deliberate exception                            |
| Simple and generated exceptions     | `/privacy-policy/`, `/search/`, 404               | Basic-centred, query, and no-template-panel behaviour                                           |
| Component/editor fixture            | `/pns-pattern-qa/`                                | Video/slideshow and editor-only/unsaved variant coverage; not a substitute for the public sweep |

## Target semantics

1. **Viewport surface** — a background, cover, or explicitly approved art
   field may remain browser wide.
2. **Wide/site-frame panel** — the broad visual composition is centred and
   capped by native `wideSize` via the existing `pns-site-frame-panel`
   contract.
3. **Inner content rail** — copy, buttons, controls, and non-edge content
   begin/end on the shared responsive inset within their available panel or
   track.
4. **Edge-media composition** — media may meet the approved panel edge; the
   copy still meets the shared rail on its outer/free side. This is a Split
   Section variant, not a generic full-width exemption.

Every affected surface must declare one of those roles. A component may not
invent an unrelated `16px`, `32px`, `frame-padding`, or zero-padding rule
without documenting why it is an intentional visual exception.

## Phased plan

### 0. Freeze an ownership and geometry inventory

Read-only survey the rendered roots at `390`, `782`, `960`, `1500`, `1920`,
and `2560px`. Classify each as viewport surface, site-frame panel, inner-rail
content, or explicit exception. Include homepage Split Sections in both
directions, normal Split Sections, header/footer, quote/cover panels, Connect
Social and Contact, archive/cards, a legal full-bleed control, and Shop/Ecwid
no-change controls. Confirm DB/file authority before proposing any structural
change.

Acceptance: a route/component table records owner, role, expected left/right
rail, and whether the frontend/editor/saved record participates. It also marks
the durable code-backed template/pattern or DB-backed record that must replace
any temporary adapter after proof.

### 1. Collapse width authority and define the `theme.json` rail token

Set native `settings.layout.wideSize` directly to the approved broad maximum
(`1500px` initially), then retire the duplicate custom site-frame setting.
Make the PNS broad-frame alias resolve to WordPress's generated wide-size
variable. Add the named content-rail configuration beneath
`settings.custom.layout` and document its generated custom-property names.
Preserve responsive behaviour with two configured leaves (base/wide) or an
approved fluid value; do not bury the breakpoint values in component CSS.
Theme settings then expose effective private rail aliases with safe preset
fallbacks: `roomy` at the base width and `section` from `782px`. Theme utility
frames consume those aliases directly. Components and project blocks consume
the same variable through local aliases only where their geometry needs
different directional application.

Acceptance: `wideSize` is the only 1500px width authority; no custom
site-frame token remains. The rail has one semantic, theme-configurable source
using `roomy`/`section` preset references; unset values safely fall back to
those presets. No global `alignfull` selector is added. Tests prove the
wide-size bridge, configured rail values, and preset-fallback path.

### 2. Reconcile theme-owned frame consumers

Move `.pns-content-frame`, `.pns-section-frame`, and audited theme component
inners to the shared rail token without changing their distinct max-width
roles. Keep existing full-width backgrounds and explicit panel caps intact.

Acceptance: regular content frames have matching left/right rails at every
target width, with no altered editorial measure, card grid, header/footer,
or vendor layout. Any adapter introduced for proof has a named source migration
or an explicit, documented reason to remain.

### 3. Make Split Section a first-class rail consumer

In the project-owned `pns-blocks` Split Section frontend and editor CSS, derive
the edge grid offset/copy inset from the shared rail. Remove the desktop
directional padding reset only where it defeats the declared copy rail; retain
media flushness at the approved site-frame edge. Cover edge-left, edge-right,
image, slideshow, video, and `pns/featured-post` dependencies, then rebuild
plugin assets.

Acceptance: Wikipedia and another opposite-direction Split Section place copy
and controls on the same visual rail as ordinary frame content while media
still reaches the panel edge; frontend and editor geometry agree. The final
block source and generated assets own the rule without a temporary override.

### 3a. Promote proved adapters into durable sources

After adapter-backed geometry passes, rewrite the relevant code-backed
templates, patterns, synced-pattern fixtures, and block source so their saved
structure declares the durable rail roles. For DB-backed pages, posts, and
reusable blocks, re-check authority, export named backups, dry-run a targeted
migration, apply only approved records, and verify idempotence. Remove every
temporary adapter that merely compensated for obsolete zero padding, missing
semantic classes, legacy nesting, or duplicate outer padding.

Acceptance: no rail behaviour remains dependent on a broad compatibility or
priority override where the owning source can express it directly; each
DB-backed mutation has authority, backup, dry-run, post-apply, and rollback
evidence.

### 4. Decide and document exceptions

Retain real full-bleed art/vendor surfaces only after route-specific evidence.
Do not force a rail onto viewport-wide heroes, legal-page surfaces, image art,
or third-party Shop markup merely because they are `alignfull`.

Acceptance: every exception has an owner and disposition; no saved WordPress
content changes without an authority check, export, dry run, apply, and backup.

### 5. Promote regression contracts

Extend the site-frame tests with measured inner-rail assertions at all target
widths, no-horizontal-overflow checks, and editor parity. Replace old
browser-edge/uncapped assertions only after the new contract passes. Avoid
brittle exact panel counts; assert named roots and geometry tolerances.

Acceptance: the focused site-frame lane, relevant frontend/editor lanes, and
the lean visual gate demonstrate the shared rail without snapshot-only proof.

## Delivery order

This is a follow-up to the existing bounded-site-frame programme. Complete the
width-authority collapse, rail inventory, and shared-token decision before
promoting its remaining visual release task, so the release suite does not
lock in duplicate width sources or mixed inner insets.
