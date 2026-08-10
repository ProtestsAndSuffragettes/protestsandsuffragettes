# Bounded Site Frame and Editorial Column Width Plan

Created: 2026-07-13.

Status: Implementation underway. Phases 0–5 are complete, including the live
Mary Barbour body-panel correction. Phase 6 promotion into the normal
regression lanes and snapshot-baseline refresh remains open.

All paths are relative to the project root unless stated otherwise.

## Goal

Give the site a deliberate maximum visual frame while preserving the existing
full-width header and footer colour fields:

```text
viewport-wide header background
  centred header and navigation content, maximum 1500px

viewport-wide page/background surfaces
  centred site frame, maximum 1500px where the component is broad
  scoped editorial reading column, approximately 920px where intended

viewport-wide footer background
  centred footer content, maximum 1500px
```

At the same time, redefine PNS Split Section `edge-media-*` deliberately: the
section background remains full width, but at very large desktop widths its
media must finish at the **site-frame edge**, not at the physical browser edge.
Up to the frame cap, existing geometry remains the control contract.

The initial working values are:

- **site frame:** `1500px` maximum;
- **default editorial reading column:** approximately `920px` (`57.5rem` at
  the current 16px root); and
- **existing global content size:** retain `44rem` unless a later approved
  surface demonstrably needs a different scope.

These are product decisions to be calibrated by the Phase 0 baseline and
accepted visual review, not an instruction to globally change every WordPress
content layout.

## Non-Goals

- Do not turn every `alignwide`, query grid, card grid, form, or editor canvas
  into a 1500px-wide surface.
- Do not globally increase `theme.json` `settings.layout.contentSize` from its
  current `44rem` reading measure.
- Do not remove the full-width colour/background treatment of the header,
  footer, or another surface explicitly approved as global chrome. Body
  covers, quote panels, and image strips remain site-frame consumers unless a
  route-specific decision says otherwise.
- Do not edit the Estory parent theme or third-party plugin code.
- Do not rewrite saved content, templates, patterns, or `wp_block` records
  merely to add wrappers. CSS/layout controls are preferred unless the
  source-of-truth audit proves a structural migration is necessary.
- Do not update Playwright baselines opportunistically before the new geometry
  is accepted; first replace assertions that encode the old contract.
- Do not merge this work with the active template-owned editorial news-hero
  programme (`g1g2iaot`).

## Current Evidence and Ownership

### Existing layout model

- The active standalone theme is
  `app/public/wp-content/themes/protestsandsuffragettes-standalone`.
- `theme.json` currently declares `contentSize: "44rem"` and an uncapped
  `wideSize` formula. The latter grows with viewport width and is therefore not
  a safe site-frame cap by itself.
- `styles/shared/settings.css` already supplies the private
  `--pns--layout--section-frame-size` alias. Its present formula is based on
  half the viewport plus the content measure, so it also grows indefinitely.
- Completed task `6jl8zwha` established aliases between `theme.json` layout
  settings and PNS helper contracts. That compatibility layer should be
  extended, not bypassed with a second unrelated family of magic widths.
- Completed task `l2xonqgq` retains `.alignwide` as a permanent saved-content
  compatibility contract. It is not permission to change the meaning of all
  wide blocks without route and saved-content evidence.

### Shell ownership

- `parts/header.html` supplies the full-width header shell and an existing
  `.pns-header__inner`; `styles/components/header.css` owns its layout.
- `parts/footer.html` supplies the full-width footer shell, existing
  `.pns-footer__inner`, and a bottom-bar inner wrapper;
  `styles/components/footer-layout.css` owns their layout.
- These existing inner elements are the lowest-disruption route for a capped
  header/footer frame. No new wrapper markup should be created before Phase 0
  proves one is missing for a specific child surface.

### Editorial reading measure

- `/news/work-with-us-argyll/` is a standard post using `templates/single.html`.
  Its body currently computes to the inherited `44rem` / `704px` content
  measure on wide desktops.
- The least disruptive way to provide the requested ~920px measure is a scoped
  `layout.contentSize` on the `wp:post-content` instance in the default single
  template (after its live file/DB source is confirmed), not a global
  `contentSize` change.
- `templates/single-full-width-news.html`, Herstory singles, pages, archives,
  search, Shop, form/control surfaces, and block-editor prose should retain
  their existing scope until an explicit route decision says otherwise.

### PNS Split Section ownership

- The project-owned `pns-blocks` plugin owns the Split Section frontend and
  editor geometry in
  `app/public/wp-content/plugins/pns-blocks/blocks/layout/split-section/`.
- `style.css` currently uses browser-width columns for edge variants and a
  three-track grid. In particular, the edge-media variants use `100vw` column
  widths; media then reaches the viewport edge at ultra-wide sizes.
- `editor.css` has the matching old three-track model. Any frontend geometry
  change must change editor geometry and compiled plugin assets together.
- `pns/featured-post` uses the same Split Section style family on home/archive
  surfaces. It is part of this plan's impact scope even though it is not a
  content migration.
- Normal non-edge Split patterns (`split-section-image`, `-slideshow`, and
  `-video`) are a control group: their usual media-right layout must not be
  widened or reflowed by the edge-media change.

### Locked synced-section decision

`PNS - Connect Social` / Connect With Us (`wp_block` `#1494`) and the Contact
Form block headed **Stay in Touch** (`wp_block` `#1493`) are ordinary site-frame
content, not full-bleed visual exceptions. Their rendered section surface,
columns, copy, and media must be centred and capped by the shared site-frame
token. Phase 5 must remove their present viewport-width layout treatment;
neither block may retain a `100vw` columns/media rail above the cap.

## Phase 0 Evidence Record — 2026-07-13

Phase 0 was completed as a read-only live-source and geometry survey. It did
not alter theme CSS, plugin CSS, templates, WordPress records, content, or
visual baselines.

### Active source authority

- Both active `stylesheet` and `template` options are
  `protestsandsuffragettes-standalone`.
- The current DB `wp_template` rows for `page`, `home`, `404`, and
  `page-no-contact-form` are assigned to the retired child-theme taxonomy, so
  they are not active standalone overrides. The standalone files currently own
  the default `single`, `single-full-width-news`, `single-herstory`, and footer
  composition.
- The sole active standalone template-part row is header `#6592`. Its saved
  content is byte-identical to `parts/header.html` (1656 bytes; recorded MD5
  prefix `88752`), so the file is the effective structural source today but the
  saved counterpart must be exported and kept synchronised before a later
  header-markup change. There is no active standalone footer-part override.
- Synced records `#1493` Contact, `#1494` Connect Social, and `#1504` Read All
  About It match their fixtures; `#1509` Shop Intro is equivalent after
  whitespace normalisation. `#6487` Read All About It Workshops is materially
  different from its fixture and was modified on 2026-07-13: it remains
  DB-owned and is explicitly out of scope for an automatic frame migration.

### Saved edge-media inventory

The surveyed published records contain 14 active Split Section consumers:

- Herstories: Mary `#5835` (edge-right and animation), Agnes `#5902`
  (edge-right), Jessie `#5903` (edge-right), Georgiana `#5904`, and Lila
  `#5906` (the latter two need live variant inspection because a
  `layoutVariant` is not serialized in the same form).
- Pages: front page / `about-2` `#49`, Artworks `#1066`, Educational `#1786`,
  About `#1789`, Shenanigans `#2363`, Edu Giveaway `#4629`, and Pattern QA
  `#5265`.
- Full-width news posts: `#3677` and `#4501`.

Mary, Artworks, Educational, About, Shenanigans, and the two full-width news
posts include animation-bearing direct children. Future geometry assertions
must use reduced motion, wait for animation settlement, or inspect an
untransformed structural element; a transformed visible rectangle is not proof
of a bad frame.

### Measured current geometry

The following browser measurements establish the pre-change contract. All
sampled routes had `document.scrollWidth === viewport width` at 1280 and
1500px.

| Viewport | Default-news prose | Header/footer inner | Edge Split columns |                    Edge media | Non-video edge height |
| -------: | -----------------: | ------------------: | -----------------: | ----------------------------: | --------------------: |
|   1280px |  704px (x=288–992) |  1216px (x=32–1248) |             1280px |  640px, reaches viewport edge |                 640px |
|   1500px | 704px (x=398–1102) |  1390px (x=55–1445) |             1500px |  750px, reaches viewport edge |                 750px |
|   1920px | 704px (x=608–1312) | 1600px (x=160–1760) |             1920px |  960px, reaches viewport edge |                 960px |
|   2560px | 704px (x=928–1632) | 1920px (x=320–2240) |             2560px | 1280px, reaches viewport edge |                1280px |

At those widths the current Split Section rule is indeed browser-edge geometry:
columns equal the viewport and each media rail occupies half of it. The
pre-animation copy track is 608px at 1280px and 695px at 1500px, preserving a
32px/55px side gutter respectively. On the animated Mary/standard-page
examples, the direct copy child visibly sits off-frame because its transform
is `translate(±608px)` or `translate(±695px)`; the untransformed grid is the
correct measurement target. At 1920px and 2560px, non-animated home copy is
800px and 960px wide respectively, while animated copy uses the same
pre-transform width but translates by that amount. The 1500px cap is therefore
a meaningful seam: it preserves the measured 1500px layout, then stops header,
footer, and edge-media growth at larger viewports.

### Baseline test status and current blockers

The elevated read-only `pnpm test:visual:layout` run began 29 tests. The first
five passed, including both sampled full-width-news hero routes, 404, and the
two search cases. It then encountered existing route-contract drift before a
green completion:

- archive expects `.pns-template-archive`, which the live route does not now
  expose;
- standard single expects `.pns-template-single`, which the live route does
  not now expose; and
- the Contact route currently renders
  `pns-template-page-light-surface-no-contact-form`, while its test expects
  the default page template.

These are current template/test-source mismatches in the already-dirty
worktree, not evidence caused by this unimplemented site-frame plan. They must
be reconciled by their owning template work before Phase 6 can claim a green
landing gate. The present site-frame-specific tests also still encode the old
browser-edge/uncapped contract; their exact rewrite remains deliberately
deferred to Phase 6.

## Target Layout Contract

### Token hierarchy

Phase 1 should establish one canonical, documented site-frame token in
`theme.json` under `settings.custom` (for example,
`--wp--custom--layout--site-frame-size`) with an initial value of `1500px`.
PNS aliases should consume that token rather than restating the number in each
component. The implementation must keep a clear distinction between:

| Concept                  |                      Initial value | Typical owner/use                                            |
| ------------------------ | ---------------------------------: | ------------------------------------------------------------ |
| Readable content measure |                            `44rem` | global prose and existing narrow copy                        |
| Editorial single body    |               ~`57.5rem` / `920px` | scoped default post content only                             |
| Broad site frame         |                       `1500px` max | header/nav/footer, broad PNS frames, capped wide geometry    |
| Viewport surface         | `100%` / `100vw` where intentional | background colour, cover/hero field, explicit full-bleed art |

`wideSize` should be capped to the accepted site frame or explicitly bridged to
the same cap. It must not be allowed to remain an unrelated uncapped formula.
The exact syntax and browser-safe min/max formulation are an implementation
decision for Phase 1, backed by computed-style tests.

### Shell and background rule

The outer header/footer/background elements remain full width. Their existing
inner content wrappers are capped and centred. This separates the visual field
from its navigation, copy, controls, and footer links without requiring every
full-width background to become boxed.

### Split Section edge-media rule

For `edge-media-left` and `edge-media-right`:

- the root section and any intended background colour remain full width;
- below and at the site-frame threshold, preserve the current accepted
  responsive behaviour unless a baseline proves an existing defect;
- above the threshold, lay out the split inside a centred `1500px` frame with
  two bounded media/copy tracks; media ends at the frame boundary, not the
  browser boundary;
- retain a copy-side gutter (initially at least the existing 32px desktop
  equivalent) so text does not begin at the edge of its track;
- preserve media/copy order for left and right variants;
- do not cap only the height. Current image-backed non-video variants derive a
  `50vw` height, so their aspect/height behaviour must be made consistent with
  bounded inline geometry; and
- keep video, slider, image, and rich-media variants independently verified.

At a 1500px frame cap the expected media half is approximately 750px. Example
desktop geometry, assuming a centred frame:

| Viewport |  Frame | Expected media inline extent | Expected frame position |
| -------: | -----: | ---------------------------: | ----------------------- |
|   1500px | 1500px |                       ~750px | fills viewport frame    |
|   1920px | 1500px |                       ~750px | x=210 to x=1710         |
|   2560px | 1500px |                       ~750px | x=530 to x=2030         |
|   3840px | 1500px |                       ~750px | x=1170 to x=2670        |

Those are layout expectations, not a reason to assert fractional pixels or
animation-transformed positions in tests.

## Survey: Routes and Special Handling

The following is the implementation inventory. A route remains in scope even
when its expected result is **no visual change**, because it protects an
intentional exception.

| Surface / representative route                                                   | Current family / owner                   | Expected treatment                                         | Special handling / risk                                                                      |
| -------------------------------------------------------------------------------- | ---------------------------------------- | ---------------------------------------------------------- | -------------------------------------------------------------------------------------------- |
| `/news/work-with-us-argyll/`                                                     | default `single.html`                    | scoped ~920px post body                                    | primary acceptance route; waits for `g1g2iaot`                                               |
| `/news/work-with-us-past-deadlines/`                                             | default `single.html`                    | same as above                                              | adjacent standard-post control                                                               |
| `/news/glasgow-herstory-workshops/`                                              | `single-full-width-news.html`            | retain full-width-news composition                         | saved edge Split Sections must follow edge-frame rule, not default-post width                |
| `/news/workshop-unleashing-the-suffragette-spirit/`                              | `single-full-width-news.html`            | same                                                       | verify hero/content relationship and mobile                                                  |
| Mary Barbour Herstory                                                            | `single-herstory.html`, saved edge split | edge media capped at site frame                            | saved direct-child animation can transform measured geometry                                 |
| Georgiana (or another non-Mary split Herstory)                                   | `single-herstory.html`                   | edge media capped at site frame                            | control route without Mary's exact animation state                                           |
| Helen Fraser Herstory                                                            | `single-herstory.html`, no edge split    | no unintended broadening                                   | template-level control route                                                                 |
| `/about/`, `/about-2/`, `/artworks/`                                             | standard pages with saved Split Sections | edge variants follow bounded frame                         | retain editor-provided content and background fields                                         |
| `/educational-resources/`, `/edu-giveaway/`, `/shenanigans/`, `/pns-pattern-qa/` | standard pages / pattern QA              | bounded edge variants; inspect normal variants             | Pattern QA is computed-style fixture and should expose token values                          |
| `/privacy-policy/`, `/gender-inclusion-policy/`                                  | full-width / alignfull pages             | preserve intentional full bleed                            | no automatic max-width wrapper around legal-page surfaces                                    |
| `/`                                                                              | `home.html`, Featured Post / edge media  | broad frame and featured split capped                      | `pns/featured-post` style dependency; retained hero full bleed                               |
| `/news/`                                                                         | archive / query layout                   | capped wide helpers only where contract applies            | cards and pagination must not become accidental single-column editorial prose                |
| `/herstories/`                                                                   | `archive-herstory.html`                  | edge featured/splits and quote body panels capped          | query grid and full pagination surface are explicit exceptions                               |
| search and 404                                                                   | archive/search layouts                   | no unintended widening or horizontal scroll                | content and results must remain usable at all breakpoints                                    |
| `/shop/` and Ecwid product surfaces                                              | third-party runtime plus theme bridge    | no change unless evidence identifies a theme-owned wrapper | do not impose editorial/site-frame changes on vendor grid without dedicated proof            |
| contact and success routes                                                       | forms/control surfaces                   | preserve usable control width                              | avoid broad prose selector leaking into inputs, notices, captcha, or buttons                 |
| header/navigation on every route                                                 | `parts/header.html` / component CSS      | full background, capped/centred inner content              | account for mobile menu, logo/nav wrap, admin-bar view                                       |
| footer on every route                                                            | `parts/footer.html` / component CSS      | full background, capped/centred inner content              | bridge saved footer-column markup and bottom bar without squeezing links                     |
| Connect Social / Connect With Us (`wp_block` 1494)                               | `synced-sections.css`                    | centred and capped at the shared site frame                | remove present `100vw` / half-viewport media treatment; preserve content and editor identity |
| Stay in Touch / Contact Form (`wp_block` 1493)                                   | `synced-sections.css`                    | centred and capped at the shared site frame                | do not let form, intro copy, or grid inherit a viewport-width wrapper                        |
| image strips, suffragette strips, quote covers                                   | theme patterns / cover styles            | cap the whole body panel at the site frame                 | only global chrome backgrounds may remain browser-edge                                       |

## Edge Cases and Required Decisions

### Full-bleed is not one category

The implementation must distinguish **full-width surface**, **full-width art**,
and **full-width content**. Header/footer colours, cover backgrounds, and
quoted-image panels may span the viewport while their text stays in a frame.
Image strips and the accepted full-pagination pseudo-surface may remain actual
viewport-width art. An `alignfull` class alone is insufficient evidence that
the whole component should be boxed.

### Saved blocks and DB-backed source of truth

Many affected Split Sections and synced sections are serialized content. Before
editing a template, template part, synced pattern, or global style, Phase 0/5
must compare the active DB record with the file and identify which is
authoritative. Before any serialized update: export the record, take a named
backup, run a dry-run/search, apply only the approved record, and browser-check
the result. The core frame/Split CSS work should not require a mass content
migration.

### Media variants

Image, slideshow/Jetpack slider, video, and rich-media branches are not
interchangeable. The existing slider descendant sizing uses compatibility
`!important` rules; do not remove them as part of geometry work. Video has a
different height contract from non-video media, while image/slideshow edge
variants may currently depend on viewport-derived minimum heights.

### Animation and measurement

At least one live Mary Barbour Split Section has `animationsForBlocks` data
that slides the direct copy child. Geometry tests need reduced motion, a known
post-animation wait, or assertions on the untransformed structural element.
They must not mistake a transitional transform for an off-frame layout bug.

### Editor parity

`pns-blocks` has separate editor CSS. A frontend-only cap would leave editors
seeing obsolete browser-edge geometry and make block authoring misleading.
The Phase 4 acceptance gate requires both frontend and editor visual checks
and rebuilt plugin assets.

## Risks and Mitigations

| Risk                                                                                   | Why it matters here                                                                               | Mitigation / acceptance evidence                                                                                   |
| -------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------ |
| Global `contentSize` change widens every Gutenberg constrained layout                  | Pages, forms, card content, editor prose, and third-party blocks would all change together        | retain `44rem`; apply ~920px only to the approved default `wp:post-content` scope; compare control routes          |
| An uncapped `wideSize` survives beside a new token                                     | Header/helpers/alignwide could still grow past the frame while Split Sections stop                | Phase 1 maps `wideSize` and PNS aliases to one measured cap; test at 1920/2560/3840                                |
| A second hard-coded 1500px value drifts                                                | Future theme.json setting edits would not reach plugin/component CSS                              | one canonical `theme.json` custom token plus documented aliases; computed-style test must expose both              |
| Full-width background becomes boxed                                                    | The site loses its established colour-field/hero language                                         | cap existing inner wrappers only; visual checks compare outer backgrounds to viewport width                        |
| New wrapper changes block markup or DB ownership                                       | A CSS preference could become a risky template/content migration                                  | use existing header/footer inner wrappers first; source/DB audit and backup gate any structural change             |
| `edge-media` still means browser-edge above 1500px                                     | The requested ultra-wide restraint would not be delivered                                         | replace 100vw desktop geometry with centred capped frame tracks; assert media ends at frame boundary at 1920+      |
| Capping inline size but retaining `50vw` height creates giant/tall or distorted panels | Non-video Split Sections derive height from viewport width                                        | define a coherent bounded dimension/aspect behaviour per media type; visual-test image, slider, and video variants |
| Copy begins flush against its new track edge                                           | A two-track frame can lose the existing readable side gutter                                      | retain/measure an explicit minimum copy gutter on both left/right variants                                         |
| Left/right variant order flips or gaps appear                                          | The old three-track geometry encodes order and rails                                              | test both directions for zero unintended gap, correct ordering, centring, and no overflow                          |
| Below-cap desktop/tablet contract regresses                                            | Existing accepted layouts may change before the new cap is even reached                           | use a breakpoint-specific override; test mobile, tablet, 1280, and 1500 as controls                                |
| Animation makes a correct layout appear off-frame                                      | A saved slide transform moves the direct child during screenshots                                 | freeze reduced motion or await settled state; test structural frame rather than transient transformed child        |
| Frontend and editor drift                                                              | Editors would author against a different shape than visitors see                                  | change `style.css` and `editor.css` together; rebuild plugin assets; run editor parity checks                      |
| Compiled assets are stale                                                              | WordPress may serve generated CSS instead of edited source                                        | run the owning build process and verify generated artifact/hash or timestamp; include it in review diff            |
| Current active news-hero work is overwritten                                           | `single.html`, single CSS, and frontend tests overlap                                             | Phase 3 is explicitly blocked by `g1g2iaot`; re-audit source/DB freshness after it lands                           |
| Saved template/part/pattern overrides hide file edits                                  | Browser may still render DB state rather than changed file                                        | WP-CLI DB-vs-file audit before each affected cut; record authority and rollback export                             |
| Connect Social or Stay in Touch retains viewport-width geometry                        | Both synced blocks currently have full-width-compatible layout plumbing, but this is now rejected | Phase 5 must cap the rendered surface/columns/media to the shared token and prove it on all consuming routes       |
| `.alignwide` tests use old viewport arithmetic                                         | Existing tests explicitly expect an uncapped formula                                              | replace magic `viewport/2 + 640` assertions with token-based measured maximum tests; retain compatibility usage    |
| Query grids, Shop, or Ecwid become constrained like prose                              | Broad selectors can alter cards/vendor layout across routes                                       | scope selectors to named structural wrappers/template instances; run archive, Shop, and search controls            |
| Header/footer navigation wraps or clips near cap                                       | Changing available inline width changes nav/link distribution                                     | test 1280, 1500, 1920, desktop zoom, mobile menu, and footer columns; retain local responsive rules                |
| Horizontal overflow is introduced by `100vw`, scrollbar math, or transforms            | A one-pixel overflow degrades all wide layouts and visual tests                                   | assert document scroll width is no wider than viewport within tolerance; inspect at all target widths              |
| Existing Core/plugin `!important` rules are removed as cleanup                         | They may be compatibility bridges for sliders/blocks                                              | treat them as out of scope unless a narrow browser proof demonstrates redundancy                                   |
| Caches mask a layout change                                                            | Local cache/plugin output can make source and browser disagree                                    | purge/bypass only within approved local workflow; verify computed styles in a fresh browser context                |
| Visual baseline churn hides a defect                                                   | Reapproving snapshots before assertions are correct normalises regressions                        | update geometry assertions first, then snapshots; review changed screenshots by route and breakpoint               |
| Fractional pixels make brittle tests                                                   | Centred max widths can be subpixel depending on viewport/device scale                             | use measured tolerances, token comparisons, and no-overflow checks rather than exact transformed coordinates       |
| Accessibility or content density declines                                              | Wider editorial lines can become hard to read and controls too sparse                             | limit 920px to the intended article body; inspect line length, headings, lists, captions, keyboard focus, and zoom |

## Dex Tracking

Standalone tracker:

```bash
dex --storage-path app/public/wp-content/themes/protestsandsuffragettes-standalone/.dex list zv4betao --all
```

Parent:

- `zv4betao` — Adopt bounded site frame and readable editorial columns

| Phase | Dex task   | Dependency           | Purpose                                                                          |
| ----- | ---------- | -------------------- | -------------------------------------------------------------------------------- |
| 0     | `2n0aszzk` | none                 | Freeze live evidence: routes, owners, saved variants, and visual baselines       |
| 1     | `fbufz74e` | Phase 0              | Define token, aliases, capped wide contract, and breakpoint rules                |
| 2     | `0r3y2x9j` | Phase 1              | Cap header/footer/nav and standard broad wrappers while retaining outer surfaces |
| 3     | `rktlhupg` | Phase 1 + `g1g2iaot` | Apply the scoped ~920px default editorial post column                            |
| 4     | `9618qr75` | Phase 1              | Make Split Section edge media terminate at the site frame                        |
| 5     | `zddpx7hj` | Phases 2–4           | Reconcile only approved exceptions and saved overrides                           |
| 6     | `vy7iyqeq` | Phase 5              | Refresh visual contracts, baselines, and release evidence                        |

Phases 2 and 3 may proceed independently after Phase 1, except that Phase 3
must wait for the active news-hero root. Phase 4 may proceed independently of
the shell and editorial changes after Phase 1. Phase 5 intentionally waits for
the three core branches so exception decisions are based on their final shape.

## Phased Implementation Plan

### Phase 0 — Survey, source-of-truth check, and baseline

Dex: `2n0aszzk`

1. Confirm the active theme and active plugin versions/asset registration.
2. Re-run a DB-vs-file freshness audit for every affected template/part/pattern
   and sampled saved Split Section route. Record authoritativeness, not merely
   file presence.
3. Capture computed layout evidence for the survey matrix at mobile, tablet,
   1280, 1500, 1920, and 2560px; add 3840px where the browser can render it
   reliably.
4. Identify exact selector and asset owners for header, footer, wide helpers,
   the Split Section plugin frontend/editor bundle, and the Featured Post
   dependency.
5. Record intentional exemption decisions as **retain full bleed**, **cap
   inner content**, or **cap the entire surface**. Connect Social and Stay in
   Touch are already locked as **cap the entire surface**; do not reopen their
   prior viewport-width treatment as a design option.

Acceptance:

- The route matrix in this document is confirmed or amended with live evidence.
- Every affected DB-backed structural surface has an authority/rollback record.
- Screenshots and computed widths make the present uncapped behaviour explicit.
- No production code, content, DB record, or visual baseline changes.

### Phase 1 — Layout token and breakpoint contract

Dex: `fbufz74e`

1. Add the canonical configurable site-frame token in `theme.json` custom
   settings and document its generated CSS custom-property name.
2. Bridge the PNS private layout aliases and `wideSize` behaviour to the same
   cap without changing global readable content size.
3. Define one breakpoint regime for when the capped frame begins, initially
   1500px, and a sensible fluid/below-cap formula that preserves existing
   small-screen behavior.
4. Add focused, opt-in `@site-frame-next` computed-layout contracts before
   changing production geometry. They must model the new token/frame cap at
   1920px and 2560px while retaining current 1280px/1500px controls. Known-red
   future contracts must not enter the normal green suite until their owning
   implementation slice passes.
5. Add focused computed-style tests for token propagation and `alignwide`
   compatibility. Replace no route-level visual baselines yet.

Acceptance:

- One value controls broad frame maximums; no duplicate unexplained 1500px
  literal remains in production component rules.
- `.alignwide` remains supported but no longer has uncapped ultra-wide growth.
- Global `contentSize` remains `44rem`.
- Computed styles prove the cap at 1920/2560 and preserve below-cap behaviour.
- The opt-in future-contract harness proves it fails against the pre-change
  layout for the intended reason, without making the normal visual suite red.

### Phase 2 — Header, navigation, footer, and standard broad wrappers

Dex: `0r3y2x9j`

1. Apply the Phase 1 cap to existing header/footer inner wrappers and any
   audited standard broad helper that truly represents site-frame content.
2. Preserve full-width outer backgrounds and local mobile/desktop layout
   behaviour.
3. Verify header nav/menu, footer columns, social/navigation data bridge, and
   bottom bar at target widths.

Acceptance:

- Header/footer colour fields remain viewport width.
- Header/nav/footer content is centred and maxes at the accepted frame.
- No wrapped/clipped navigation, squeezed footer links, or horizontal overflow
  occurs in the survey routes.

### Phase 3 — Scoped default editorial post column

Dex: `rktlhupg` (blocked by `g1g2iaot`)

1. After the active news-hero work lands, re-check the file/DB authority of
   `single.html` and associated CSS.
2. Set an approved scoped content layout on the default post-content slot so
   conventional news bodies have an approximately 920px maximum measure.
3. Keep title/hero/meta/container ownership coherent with the already-approved
   editorial hero work; do not duplicate hero/content wrappers.
4. Test typical prose, headings, lists, images, embeds, captions, comments,
   mobile, tablet, zoom, and editor preview.

Acceptance:

- `/news/work-with-us-argyll/` reaches the approved body measure on wide
  screens without globally widening other constrained blocks.
- Full-width news, Herstory, page, archive, Search, Shop, and form routes show
  no unapproved measure change.
- The active news-hero work is neither overwritten nor reimplemented.

### Phase 4 — Bounded Split Section edge media

Dex: `9618qr75`

1. Implement the above-cap centred two-track geometry in the project-owned
   Split Section frontend CSS; retain the full-width root/background.
2. Make image, slideshow, and video dimension rules coherent with the bounded
   inline frame. Preserve normal non-edge Split patterns as controls.
3. Apply matching editor CSS geometry and rebuild the plugin's generated
   frontend/editor assets.
4. Test both edge directions, all media variants, direct animation state, and
   the `pns/featured-post` consumers on home/archive.

Acceptance:

- At 1920px and beyond, edge media reaches the centred site-frame edge, never
  the browser edge; expected media width is about half the capped frame.
- At/below the transition, accepted responsive behaviour survives.
- No internal gap, reversed order, dropped copy gutter, layout shift, or
  horizontal overflow appears.
- Frontend and editor render the same structural geometry.

### Phase 5 — Approved exceptions and saved overrides

Dex: `zddpx7hj`

1. Apply the locked shared site-frame treatment to Connect Social / Connect
   With Us and Stay in Touch / Contact Form. Remove their viewport-width
   columns/media geometry while retaining their saved-content identity,
   background/palette, and editor workflow.
2. Review the remaining survey exceptions against the final core behaviour:
   image strips, quote covers, full-pagination surface, archive/query layouts,
   and vendor/Shop surfaces. Make a route-specific decision for each: retain
   full bleed, cap inner content only, cap the entire surface, or schedule
   separate design work.
3. If a saved record genuinely must change, follow the Phase 0 authority,
   export, backup, dry-run, apply, and post-apply scan procedure.
4. Re-check animated Split Sections after an animation-safe test strategy is in
   place.

Acceptance:

- Connect Social and Stay in Touch render as centred site-frame sections on
  every route that includes them; no `100vw` columns/media rail remains.
- Every remaining exception has an explicit recorded disposition.
- No serialized content or DB override changes without a backup and verified
  source-of-truth decision.
- Third-party Ecwid and compatibility slider behaviour remain intact.

### Phase 5 Evidence Record — 2026-07-13

The source-authority recheck found no saved-content migration to make. The
active standalone stylesheet and template remain selected. Synced blocks
`#1493` (Contact Form / Stay in Touch, MD5 `d2429554…`, 1560 bytes) and `#1494`
(Connect Social, MD5 `4c54b664…`, 2949 bytes) are byte-identical to their
fixtures. Their saved `alignfull` classes are normal block markup; neither
serializes `100vw`, `50vw`, or a conflicting width declaration. The named CSS
compatibility overrides are therefore authoritative, and no export, backup, or
serialized update was needed. The unrelated DB-owned workshop record `#6487`
remains out of scope and untouched.

The following dispositions are locked by live 2560px/3840px route inspection:

| Exception                              | Disposition                                 | Evidence / guard                                                                                                   |
| -------------------------------------- | ------------------------------------------- | ------------------------------------------------------------------------------------------------------------------ |
| Image and suffragette strips           | Cap the whole panel at the site frame       | The live Mary Barbour review found them running to the browser edge; they are body panels, not global chrome.      |
| Quote covers                           | Cap the whole cover at the site frame       | The quote copy was framed but the cover itself reached the browser edge; the panel must follow its wrapping frame. |
| Archive/query grids                    | Retain the 1500px frame and card grid       | Herstories and search remain framed; do not turn cards into editorial prose.                                       |
| Pagination pseudo-surface              | Retain viewport-width decorative rail       | Search controls remain narrow while `::before` deliberately spans the viewport; no overflow.                       |
| Edge Split image, slideshow, and video | Keep the Phase 4 capped geometry            | Root remains full width; columns cap at 1500px; image/slideshow media is 750px and video keeps its 16:9 contract.  |
| Non-edge Split variants                | Preserve as controls                        | They already use bounded columns and must not inherit the edge-media reflow.                                       |
| Animated Mary Barbour Split            | Measure structural grid with reduced motion | The direct copy child has a saved slide transform; its transformed visual rectangle is not frame evidence.         |
| Ecwid / Shop                           | No frame change                             | Treat as a vendor surface; assert only no overflow unless a dedicated Shop decision is made.                       |

`/pns-pattern-qa/` remains a convenient computed-style fixture, but its nested
strip and quote examples sit inside the 704px content column. It is not proof
of the live route geometry; Mary Barbour remains the regression fixture for
the body-panel frame contract.

### Phase 5 Follow-up Evidence — Mary body-panel containment

Live review of `/herstories/mary-barbour/` corrected the earlier exception
assumption. Quote covers, image/suffragette strips, and the Fun Facts panel are
ordinary body content and must end at the site-frame edge. Their outer roots,
Facts Columns child, and Facts copy placement now pass measured 2560px
contracts; Connect With Us copy also passes its left Columns-track gutter
contract. Phase 6 must not promote the old full-bleed exception assertions.

The same live review closed two shared alignment defects: the footer logo now
uses the same 16px top inset within its own inner frame as the header logo, and
quote text/keylines keep the existing 32px desktop rail (16px below 600px)
instead of resetting to the cover edge at 1300px.

### Phase 6 — Visual contract refresh and release evidence

Dex: `vy7iyqeq`

1. Replace old browser-edge and uncapped arithmetic assertions with measured
   site-frame/token assertions, promoting the now-passing `@site-frame-next`
   contracts into normal `@layout` coverage. Do not only update snapshots.
2. Rewrite the existing `frontend.spec.ts` contracts that currently demand
   viewport-edge edge media and uncapped `alignwide` values, including the
   2048px rail tests and Mary Barbour geometry test.
3. Add target-width coverage at mobile, tablet, 1500, 1920, 2560, and where
   stable 3840px. Use tolerances for fractional pixels and animation-safe
   conditions.
4. Run the touched visual lanes, compile/lint/check, then the lean visual
   landing gate. Record any intentional test exclusions and cache handling.

Acceptance:

- Tests prove full outer backgrounds, bounded inner frames, scoped editorial
  width, correct left/right edge-media geometry, and no horizontal overflow.
- The visual matrix covers every route family listed in the survey, with route
  controls for intentional no-change surfaces.
- Generated assets and baseline changes are explainable by accepted geometry.

## Required Verification Matrix

| Check             | Minimum evidence                                                                                                                                                                             |
| ----------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Token propagation | inspect computed `theme.json` custom property, PNS alias, `wideSize`, header/footer frame, and Split frame                                                                                   |
| Viewports         | mobile, tablet, 1280, 1500, 1920, 2560; 3840 when stable                                                                                                                                     |
| Route coverage    | all representative survey routes, particularly current standard news, both full-width-news examples, Mary + non-Mary Herstory, home, archives, Pattern QA, Shop, contact/success, search/404 |
| Edge variants     | left and right image; slideshow/slider; video; rich-media if live/supported; normal non-edge image/slideshow/video as controls                                                               |
| Visual geometry   | full outer background, centred max frame, exact order, preserved copy gutter, expected media boundary, no accidental grid gap                                                                |
| Overflow          | document scroll width, problematic `100vw` children, focus/keyboard navigation, browser zoom and narrow viewport checks                                                                      |
| Editor            | Split Section editor parity, default editorial post authoring preview, no misleading block selection/canvas geometry                                                                         |
| Build             | theme CSS compile/lint/format checks, plugin asset build, generated asset review                                                                                                             |
| Regression gate   | targeted Playwright lane during each cut; lean `pnpm test:visual` landing gate for the final accepted visual behavior                                                                        |

## Test-Led Delivery Model

The site-frame programme is test-led, but the committed normal suite must
remain a trustworthy description of the current released layout. Therefore the
future contract is modelled in an explicitly invoked, opt-in
`@site-frame-next` test group while its expected geometry is intentionally red.
It does not replace the existing browser-edge assertions until the owning
implementation slice passes. Once a slice passes, its future assertion is
promoted into ordinary `@layout` coverage and the superseded current-layout
assertion is removed in the same review.

| Contract slice          | First failing expectation                                                                                                                                                       | Activation / promotion point                            |
| ----------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------- |
| Token and aliases       | At 2560px, a disposable CSS-variable probe resolves the shared site frame to 1500px; `wideSize` and the PNS aliases do not exceed it; global content remains 44rem              | Write in Phase 1; promote when the token contract lands |
| Shell frame             | Header/footer outer surfaces span the viewport while their existing inner wrappers are centred and no wider than the frame                                                      | Write in Phase 1; promote in Phase 2                    |
| Editorial measure       | Default-news post content reaches ~920px on wide desktop without widening excluded template families                                                                            | Write after the source audit; promote in Phase 3        |
| Split edge media        | 1280/1500 retain the current control geometry; at 1920/2560, the full-width section contains a centred 1500px frame with ~750px media ending at the frame edge                  | Write in Phase 1; promote in Phase 4                    |
| Live Split consumers    | Home, archive, standard-page, and Herstory left/right variants consume the same contract; animated routes are measured with reduced motion or untransformed structural elements | Write in Phase 4; promote after live-route proof        |
| Synced sections         | Connect Social and Stay in Touch section surface, columns, copy, and media are centred and capped at 2560px with no `100vw` rail                                                | Write in Phase 1; promote in Phase 5                    |
| Exceptions and overflow | Approved image-strip/cover exceptions remain as documented, and every matrix route has no horizontal overflow                                                                   | Add per exception decision; promote in Phase 5          |

Test implementation rules:

- Prefer rendered rectangle and computed-style measurements over CSS source
  strings. A disposable probe using the custom property is the evidence that
  theme JSON and plugin/theme aliases agree.
- At the 1500px seam, assert a maximum plus centring and responsive gutters;
  do not require the header/footer inner content itself to be exactly 1500px
  wide at a 1500px viewport.
- Keep `pns-pattern-qa` as the forced-variant geometry fixture. Use the live
  routes as consumer proof, not as the sole geometry source.
- Do not update snapshots before the measured contract passes. Snapshot review
  follows promotion of the corresponding geometry assertion.
- Do not assess Mary Barbour or another animated saved block through its
  transformed copy rectangle. Use reduced motion, wait for settlement, or
  assert the columns/media frame.

The known visual test contracts to revise in Phase 6 include the old uncapped
`alignwide` arithmetic around `frontend.spec.ts` lines 3608–3652 and
4824–4835; Split Section browser-edge assertions around 3975–4378; live Mary
Barbour edge assertions around 6152–6480; and supporting homepage/header/no-
overflow checks around 4561–4933, 5397–5538. Line numbers are navigation aids,
not a substitute for reviewing current test intent at implementation time.

## Rollback and Approval Gates

- Each phase is independently releasable only after its stated acceptance
  criteria and targeted visual proof pass.
- Keep implementation commits/patches narrowly phase-scoped. Revert CSS/token
  changes together with their generated assets; do not use content rollback to
  compensate for a CSS defect.
- Before any DB-backed mutation, retain an export that can restore the exact
  record and note its source/DB authority.
- Do not move from Phase 1 to broad rollout merely because tests compile. The
  first 1500/1920/2560 visual review must approve the perceived frame and
  reading measure.
- Do not start Phase 3 until `g1g2iaot` has completed and a new source-of-truth
  audit confirms what it changed.
- If an exception needs a different broad-frame value or a browser-edge media
  treatment, record a named design decision instead of adding an untracked
  local override.

## Recommended First Landing Slice

Begin with Phase 0 only. It is read-only and establishes the final live
route/owner baseline, confirms the current DB/file sources before concurrent
template work lands, and lets the 1500px/920px choices be reviewed against
actual ultra-wide evidence before any style contract changes.
