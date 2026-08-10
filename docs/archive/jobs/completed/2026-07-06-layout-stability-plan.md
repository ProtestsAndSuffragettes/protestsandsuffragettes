# Layout Stability And Content-Height Polish Plan

Created: 2026-07-06

Related plans:

- `docs/jobs/2026-06-28-theme-json-css-structure-rationalization-plan.md`
- `docs/jobs/2026-07-06-motion-perceived-loading-plan.md`

## Goal

Reduce avoidable visual jumps and abrupt page-length changes without weakening
the current standalone theme layout contract.

This plan covers:

- Short readable pages such as Search, Thank You, 404, and empty result states.
- The Contact Us to Thank You transition.
- Shop/Ecwid hydration growth.
- Search landing/results, News, Herstories, and other template surfaces that may
  need stable content regions before motion polish lands.

## Non-Goals

- Do not add broad global `main` height rules that compete with the existing
  site shell.
- Do not animate layout dimensions as a substitute for reserving space.
- Do not target Ecwid, EmailOctopus, or other vendor internals broadly.
- Do not change saved page content until filesystem templates, saved template
  overrides, and live page assignments have been verified.
- Do not refresh Playwright visual baselines until drift is understood and
  explicitly accepted.

## Current Evidence

### Implementation Refresh - 2026-07-06

Live checks before edits confirmed:

- Active theme: `protestsandsuffragettes-standalone`.
- `/contact-us/` is page `6236` using
  `page-light-surface-no-contact-form`.
- `/contact-success/` is page `6253` using
  `page-light-surface-no-contact-form`.
- `/search/` is page `6023` using `page-search`.
- `/shop/` is page `565` using `page-light-surface`.
- No saved `wp_template` override currently exists for
  `page-light-surface` or `page-light-surface-no-contact-form`.
- Saved `wp_template` rows do exist for `page`, `page-search`, `search`, and
  `404`, so validation must use rendered output rather than filesystem
  assumptions alone.

Pre-edit layout lane:

- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout`
  passed 18 desktop and 6 mobile tests.

Implemented scoped floors in
`app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/page-types/layout-stability.css`:

- `main.pns-template-page-light-surface-no-contact-form`:
  `clamp(42rem, 82vh, 52rem)`.
- `main.pns-template-page-search`,
  `main.pns-template-search`, and `main.pns-template-404`:
  `clamp(28rem, 52vh, 36rem)`.
- `.pns-shop-storefront`:
  conservative Ecwid reservation of
  `min(120rem, max(72rem, 180vh, 115vw))`, with a mobile reservation of
  `min(150rem, max(90rem, 240vh, 400vw))`.

Post-edit spot measurements:

| Viewport | Route                        | Main before | Main after | Notes                                  |
| -------- | ---------------------------- | ----------- | ---------- | -------------------------------------- |
| Desktop  | `/contact-success/`          | 697px       | 738px      | Shared no-contact template floor.      |
| Desktop  | `/search/`                   | 353px       | 468px      | Search landing floor.                  |
| Desktop  | `/?s=zzzz-no-results-zzzz`   | 395px       | 468px      | Empty search floor.                    |
| Desktop  | `/shop/` at DOMContentLoaded | 610px       | 1748px     | Store wrapper floor reduces jump.      |
| Desktop  | `/shop/` settled             | 2163px      | 2163px     | Settled Ecwid output unchanged.        |
| Mobile   | `/contact-success/`          | 508px       | 692px      | Shared no-contact template floor.      |
| Mobile   | `/search/`                   | 292px       | 448px      | Search landing floor.                  |
| Mobile   | `/?s=zzzz-no-results-zzzz`   | 374px       | 448px      | Empty search floor.                    |
| Mobile   | `/shop/` at DOMContentLoaded | 1065px      | 2154px     | Mobile reservation remains partial.    |
| Mobile   | `/shop/` settled             | 4452px      | 4452px     | Full grid height not reserved by plan. |

The Shop change follows locked decision 3: reserve a conservative partial
height, not the full settled Ecwid grid. Mobile still expands when the full
single-column product grid lands; a future loading/skeleton decision would need
a reliable Ecwid load-state signal.

Validation after implementation:

- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone exec prettier --check styles/page-types/index.css styles/page-types/layout-stability.css tests/visual/frontend.spec.ts ../../../../../docs/jobs/2026-07-06-layout-stability-plan.md`
- `git diff --check`
- Focused new assertions:
  - `short template surfaces reserve scoped content height`
  - `shop storefront reserves space before Ecwid hydration`
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout`
  passed 20 desktop and 8 mobile tests.
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates`
  passed 22 desktop tests.
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:shop`
  passed 27 desktop/tablet/mobile tests.
- `pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual`
  passed the lean landing gate: 37 desktop and 10 mobile tests.

Active Local theme:

```text
protestsandsuffragettes-standalone
```

Current shell contract:

- `styles/layout/index.css` already sets `html, body` to `min-block-size: 100%`.
- `body > .wp-site-blocks` already uses a column flex shell with
  `min-block-size: 100vh` and `100dvh`.
- `.wp-site-blocks > main` is the flexing content item and clips page-level
  horizontal overflow.

Measured desktop route heights on a 1366 by 900 viewport:

| Route               | Header | Main   | Footer | Body   | Notes                                     |
| ------------------- | ------ | ------ | ------ | ------ | ----------------------------------------- |
| `/contact-us/`      | 143px  | 911px  | 404px  | 1458px | Uses `page-light-surface-no-contact-form` |
| `/contact-success/` | 143px  | 697px  | 404px  | 1244px | Same page template, shorter content       |
| `/search/`          | 143px  | 353px  | 404px  | 900px  | Shell fills viewport, content is short    |
| `/?s=protest`       | 143px  | 3925px | 404px  | 4472px | Long search results page                  |
| `/shop/`            | 143px  | 3036px | 404px  | 3583px | Long after Ecwid hydration                |
| `/news/`            | 143px  | 2445px | 404px  | 2993px | News archive page                         |

Shop hydration sampling on `/shop/`:

| Time after DOMContentLoaded | Main height |
| --------------------------- | ----------- |
| 0ms                         | 1461px      |
| 250ms                       | 1461px      |
| 750ms                       | 2157px      |
| 1500ms                      | 3036px      |
| 3000ms                      | 3036px      |

Interpretation:

- Short-page polish is mostly a content-region rhythm problem, not a missing
  viewport shell.
- The Contact Us to Thank You shift is real but should be solved on the shared
  light-surface/page-content contract, not with a global hard-coded page height.
- Shop shift is vendor hydration growth. It needs a reserved store region or a
  stable loading state around the site-owned Shop surface.

## Guardrails

- Preserve the existing flex shell as the top-level layout authority.
- Prefer `min-block-size` over fixed `height`.
- Scope floors to semantic surfaces such as `pns-light-surface`,
  `pns-template-page-light-surface-no-contact-form`, `pns-template-page-search`,
  `pns-template-page-light-surface`, and `pns-shop-storefront`.
- Keep vendor-specific layout reservation in `styles/page-types/shop.css` or
  `styles/vendor-overrides/ecwid.css`, depending on whether the owner is the
  page frame or Ecwid output.
- Treat saved templates and DB-backed content as possible live sources. Use
  WP-CLI before implementation.
- Use targeted Playwright/computed checks while iterating and the full visual
  suite before landing broad layout behavior.

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
ur561p95 - Plan layout stability and content-height polish
```

Phase tasks:

| Phase | Dex ID     | Task                                            |
| ----- | ---------- | ----------------------------------------------- |
| 0     | `vrlg161r` | Verify live template and height baseline        |
| 1     | `dif4p51a` | Define scoped content floor contract            |
| 2     | `9qmhgu4y` | Reserve Shop and vendor-hydrated regions        |
| 3     | `wf1sgdly` | Validate responsive and visual-regression gates |

## Phase 0: Verify Live Template And Height Baseline

Objective: update the evidence immediately before implementation.

Steps:

1. Confirm the active theme with WP-CLI.
2. Confirm page template assignments for Contact Us, Thank You, Search, Shop,
   and any planned test pages.
3. Check for saved `wp_template` overrides that may beat filesystem templates.
4. Measure header, main, footer, body, and scroll heights at desktop, tablet,
   and mobile widths.
5. Sample `/shop/` height at DOMContentLoaded, 750ms, 1500ms, and 3000ms.
6. Classify each target as one of:
   - already stable shell, no floor needed
   - short content surface, scoped content floor useful
   - vendor hydration surface, reservation/loading state needed
   - saved-content issue, migration or template assignment needed

Acceptance criteria:

- The plan evidence table is refreshed before CSS changes.
- The surfaces needing floor versus reservation are named explicitly.
- Existing unrelated worktree dirt is not touched.

## Phase 1: Scoped Content Floor Contract

Objective: add a narrow content-height contract for short readable pages.

Recommended implementation direction:

- Keep the existing `.wp-site-blocks` flex shell unchanged.
- Add a scoped floor to short readable templates or their content frame, not to
  every `main`.
- Start with the shared light-surface no-contact-form template because both
  Contact Us and Thank You use it.
- Include Search landing and 404 only if measurement proves the shell is not
  enough for perceived continuity.

Candidate surfaces:

- `main.pns-template-page-light-surface-no-contact-form`
- `main.pns-template-page-light-surface`
- `main.pns-template-page-search`
- `main.pns-template-search`
- `main.pns-template-404`
- `.pns-shop-storefront`, but only for Shop-owned intro/frame concerns; keep
  Ecwid runtime reservation in the vendor adapter if the vendor output is the
  owner.

Acceptance criteria:

- Contact Us and Thank You feel related in vertical rhythm.
- Search landing remains compact enough to be usable while avoiding a collapsed
  middle band.
- 404 and empty states keep the footer anchored by the shell and avoid awkward
  short content.
- Long routes such as Search results, News, Herstories, and Shop are not made
  artificially taller.

## Phase 2: Shop And Vendor-Hydrated Regions

Objective: reduce the late Shop layout expansion caused by Ecwid hydration.

Recommended implementation direction:

- Treat Shop separately from normal page min-height work.
- Reserve space around the site-owned Shop/Ecwid wrapper based on measured
  settled height and responsive breakpoints.
- Prefer a visible neutral loading state or skeleton only if the Ecwid load
  state can be detected reliably without racing vendor markup.
- Keep fallback product-grid work separate from the Ecwid storefront widget.

Possible tactics:

- Add `min-block-size` to a site-owned Shop store wrapper.
- Add a Shop-specific loading region before Ecwid hydrates, removed only when
  the store widget has meaningful content.
- Use `content-visibility` or `contain-intrinsic-size` only after testing
  browser behavior and accessibility; do not make it the first move.

Acceptance criteria:

- `/shop/` has less visible vertical jump during the first 1500ms.
- Ecwid remains usable if scripts are slow or blocked.
- No vendor selector becomes a broad dependency for normal page layout.

## Phase 3: Validation

Objective: prove that stability improved without hiding regressions.

Targeted checks:

- `/contact-us/`
- `/contact-success/`
- `/search/`
- `/?s=protest`
- an empty search result URL
- `/shop/`
- `/news/`
- `/herstories/`
- `/404` or an unknown route

Computed assertions:

- No horizontal overflow.
- Footer remains visible or sensibly placed on short pages.
- Main/content frame min height is applied only to intended surfaces.
- Shop main height delta is reduced or intentionally documented.
- Mobile does not get excessive blank space above the footer.

Commands:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone compile:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone lint:css
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:shop
```

Run the full visual suite before landing a broad layout-stability change:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual
```

## Locked Decisions

### 1. How broad should the content floor be?

Options:

- A. Apply to every `main.pns-template`.
- B. Apply only to readable/light-surface templates.
- C. Apply only to named short-page templates.

Locked decision: C first, with B as the maximum planned scope. Avoid A because
Home, Herstories, News, Shop, and ordinary page-builder content already have
their own vertical rhythm.

### 2. Should Thank You get a page-specific class or share the template floor?

Options:

- A. Add a page-specific class or template.
- B. Use the existing shared light-surface no-contact-form template.
- C. Leave Thank You alone and only add motion.

Locked decision: B first. Contact Us and Thank You already share
`page-light-surface-no-contact-form`, so a shared floor is less brittle than a
page-specific exception. Use A only if other pages on the same template become
too tall.

### 3. What should the Shop reserved height target be?

Options:

- A. Reserve the full measured settled height.
- B. Reserve a conservative partial height that prevents the worst jump.
- C. Show no reserved region and rely on motion only.

Locked decision: B. Reserving the full settled height risks excessive blank
space on mobile and product-count changes. A conservative floor plus
measurement-based breakpoints is safer.

### 4. Should `contain-intrinsic-size` be used for Shop?

Options:

- A. Yes, use it as the main reservation mechanism.
- B. Use normal `min-block-size` first and revisit containment later.
- C. Avoid containment entirely.

Locked decision: B. It may help, but Ecwid accessibility and browser behavior
should be tested before it becomes the first-line fix.

### 5. Should this be implemented before motion?

Options:

- A. Yes, stabilize layout first.
- B. Implement motion first.
- C. Implement both in one combined batch.

Locked decision: A. Motion can hide the symptom but cannot fix layout shift.
Stability should land first or at least provide the baseline gates for motion.
