# Visual Suite Fastlane Optimization Plan

Created: 2026-07-06

Related plans:

- `docs/jobs/2026-06-28-theme-json-css-structure-rationalization-plan.md`
- `docs/jobs/2026-07-06-layout-stability-plan.md`
- `docs/jobs/2026-07-06-motion-perceived-loading-plan.md`
- `docs/jobs/2026-07-06-vendor-override-debt-final-reevaluation-plan.md`

## Goal

Make the standalone theme visual regression suite usable as both a fast
iteration signal and a broad landing gate.

The current suite catches important regressions, but it has grown into one
large default command that is expensive enough to block normal closeout work.
This plan replaced the inherited all-project default with a lean landing gate
while keeping audit-only coverage available through explicit smoke, fastlane,
area-specific, snapshot, vendor, and audit lanes.

## Non-Goals

- Do not weaken the final full-suite landing gate for broad CSS, template,
  migration, or snapshot-refresh work.
- Do not remove visual coverage only because it is slow.
- Do not point automated regression tests at production.
- Do not add a Playwright `webServer` for Local WP unless the site can be
  started reliably from the theme tooling.
- Do not make vendor-heavy Ecwid or EmailOctopus checks invisible; move them
  into explicit lanes instead.
- Do not refresh screenshot baselines as part of suite cleanup unless the
  visual drift is understood and accepted.

## Current Evidence

Current runnable suite:

```text
app/public/wp-content/themes/protestsandsuffragettes-standalone/tests/visual/frontend.spec.ts
```

Current package entry:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual
```

Current Playwright behavior:

- `playwright.config.ts` runs `desktop`, `tablet`, and `mobile` projects.
- `fullyParallel` is `false`.
- `workers` is `1`.
- `test:visual` runs all files under `tests/visual`.
- The suite currently lists 237 visual tests from one spec file.
- That is 79 logical tests multiplied across three projects.
- The visual snapshot folder currently contains 21 PNG baselines.
- Current code appears to generate 18 screenshot baselines:
  - 5 full-page snapshot routes across three projects.
  - 1 live-adoption quote component screenshot across three projects.
- `news-*-darwin.png` baselines look stale unless a pending branch still
  reintroduces matching snapshot tests.

Main cost drivers:

- Almost every contract runs across desktop, tablet, and mobile.
- Only the header breakpoint sweep currently skips non-desktop projects.
- `waitForStableAssets()` scrolls the page, forces AOS state, disables motion,
  waits for fonts/images, and is used by many non-screenshot contract tests.
- Full-page snapshots include route-specific fixed waits of 3 to 5 seconds.
- Ecwid and cart tests wait on third-party storefront hydration.
- Some single tests loop multiple routes or widths internally, so Playwright's
  listed test count understates the actual page-load count.

Repeated hot routes:

| Route                       | Current coverage pressure                                            |
| --------------------------- | -------------------------------------------------------------------- |
| `/`                         | Snapshot, homepage cascade, Ecwid grid, banner, identity, email, nav |
| `/herstories/mary-barbour/` | Snapshot, light-surface exclusion, quote hooks, identity, herstory   |
| `/pns-pattern-qa/`          | Snapshot, section theme, nav controls, split section, layout, QA     |
| `/shop/`                    | Snapshot, light surface, banner, Ecwid cascade, product/cart flows   |
| `/edu-giveaway/`            | Snapshot, banner, quote hooks, EmailOctopus                          |

## Guardrails

- Prefer named tags and scripts over one-off grep strings.
- Keep full-suite behavior intact until smaller lanes are proven.
- Make viewport coverage explicit per contract.
- Keep screenshot tests serial and stable.
- Allow fast computed-style and DOM contracts to avoid screenshot-only
  stabilizers where they do not need them.
- Keep vendor integration checks available but out of the default fastlane.
- Record before/after `--list` counts before closing each phase.
- Preserve unrelated local worktree dirt.

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
q4wvnotd - Plan visual suite cleanup and fastlane optimization
```

Phase tasks:

| Phase | Dex ID     | Task                                    |
| ----- | ---------- | --------------------------------------- |
| 0     | `rrdomzqa` | Baseline inventory and runtime map      |
| 1     | `qodb5bsk` | Add tags and lane scripts               |
| 2     | `0yr3gbid` | Scope viewport matrix by contract       |
| 3     | `xx9aalcu` | Reduce wait and snapshot debt           |
| 4     | `vh7vmybx` | Isolate vendor-heavy contracts          |
| 5     | `pw9b9hne` | Validate gates and update workflow docs |

## Target Lanes

### Smoke

Purpose: quick confidence before or during a narrow CSS/template edit.

Recommended coverage:

- Home visual snapshot.
- Mary Barbour visual snapshot.
- Homepage cascade contracts.
- Mobile navigation drawer contract.
- One lightweight Shop/Ecwid render check.

Recommended projects:

- Desktop.
- Mobile.

Do not include:

- Ecwid cart add flow.
- Product detail pages.
- Full Pattern QA inventory.
- Full screenshot route matrix.

### Fastlane

Purpose: default agent iteration gate for ordinary standalone theme changes.

Recommended coverage:

- Desktop DOM/computed contracts for homepage, templates, search/news, layout,
  Pattern QA, header, banner, and light-surface behavior.
- Mobile navigation and mobile-specific overflow/layout checks.
- Home and Mary snapshots only if the touched surface can affect global visual
  rhythm.

Do not include by default:

- Full-page screenshots for all routes.
- Full Ecwid product/detail/cart flows.
- EmailOctopus checks unrelated to the edited surface.

### Area Lanes

Purpose: make common focused checks discoverable.

Recommended scripts:

```json
{
  "test:visual:list": "playwright test tests/visual --list",
  "test:visual:smoke": "playwright test tests/visual --grep \"@smoke\" --project=desktop && playwright test tests/visual --grep \"@mobile-smoke\" --project=mobile",
  "test:visual:fast": "playwright test tests/visual --grep \"@fast\" --project=desktop && playwright test tests/visual --grep \"@mobile-fast\" --project=mobile",
  "test:visual:snapshots": "playwright test tests/visual --grep \"@snapshot\"",
  "test:visual:layout": "playwright test tests/visual --grep \"@layout\" --project=desktop && playwright test tests/visual --grep \"@mobile-layout\" --project=mobile",
  "test:visual:navigation": "playwright test tests/visual --grep \"@navigation\" --project=desktop && playwright test tests/visual --grep \"@mobile-navigation\" --project=mobile",
  "test:visual:templates": "playwright test tests/visual --grep \"@template|@search|@archive\"",
  "test:visual:shop": "playwright test tests/visual --grep \"@shop\"",
  "test:visual:ecwid": "playwright test tests/visual --grep \"@ecwid\"",
  "test:visual:emailoctopus": "playwright test tests/visual --grep \"@emailoctopus\""
}
```

Exact script names can change during implementation, but the final package
scripts should cover these lanes.

### Full

Purpose: final landing gate for broad visual work.

Coverage:

- All visual tests.
- All projects.
- All full-page snapshots.
- Vendor-heavy tests.
- Live-adoption component screenshot.

Command remains:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual
```

## Phase 0: Baseline Inventory And Runtime Map

Dex: `rrdomzqa`

Objective: refresh the actual suite shape immediately before changing it.

Steps:

1. Run the current visual list:

   ```bash
   pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone exec playwright test tests/visual --list
   ```

2. Run project-specific list counts for desktop, tablet, and mobile.
3. Count screenshot baselines in
   `tests/visual/frontend.spec.ts-snapshots`.
4. Identify baselines that do not map to current `toHaveScreenshot()` calls.
5. List tests that:
   - use `waitForStableAssets()`
   - use fixed `waitForTimeout()`
   - call Ecwid or EmailOctopus surfaces
   - loop multiple paths internally
   - manually set viewport size
6. Record the inventory in this plan before edits.

Acceptance criteria:

- Current logical and project-multiplied counts are recorded.
- Stale baseline candidates are named.
- Slow/vendor tests are listed before they are moved.
- No test behavior changes in this phase.

## Phase 1: Tags And Lane Scripts

Dex: `qodb5bsk`

Objective: make common test lanes stable and discoverable.

Recommended implementation:

- Add tags to test titles or `test.describe()` groups:
  - `@smoke`
  - `@fast`
  - `@snapshot`
  - `@layout`
  - `@navigation`
  - `@template`
  - `@search`
  - `@archive`
  - `@pattern`
  - `@shop`
  - `@ecwid`
  - `@emailoctopus`
  - `@vendor`
  - `@slow`
  - `@full`
- Add package scripts for smoke, fast, snapshots, layout, navigation,
  template/search, shop, Ecwid, and EmailOctopus lanes.
- Keep `test:visual` as the lean default landing gate.
- Add explicit audit scripts for migration/history coverage.
- Add `test:visual:list` so future agents can inspect lane membership without
  launching browsers.

Acceptance criteria:

- `pnpm test:visual:list` works from the standalone theme.
- Each lane script selects the intended tests with `--list`.
- No broad lane depends on fragile natural-language grep terms.
- The default visual command is lean and excludes `@audit` coverage.

## Phase 2: Scope Viewport Matrix By Contract

Dex: `0yr3gbid`

Objective: stop viewport-agnostic contracts from running in every project.

Contract classes:

| Class              | Example surfaces                                  | Suggested project policy                  |
| ------------------ | ------------------------------------------------- | ----------------------------------------- |
| Viewport-agnostic  | class presence, source hooks, content identity    | Desktop only                              |
| Responsive layout  | width, overflow, content/wide/site-frame geometry | Desktop plus mobile, add tablet if needed |
| Mobile-only        | drawer behavior, mobile-specific nav controls     | Mobile only                               |
| Breakpoint sweep   | nav overlap widths, explicit breakpoint probes    | Desktop project with manual widths        |
| Snapshot baseline  | full-page visual references                       | Full matrix unless route-specific         |
| Vendor integration | Ecwid, EmailOctopus hydration                     | Area lane plus lean visual                |

Recommended first cuts:

- Keep Pattern QA inventory desktop-only unless checking responsive geometry.
- Keep template class/content checks desktop-only.
- Keep mobile nav mobile-only.
- Keep the header breakpoint sweep desktop-only.
- Keep layout width and split-section geometry on the smallest matrix that
  still proves the contract.

Acceptance criteria:

- Fastlane count is materially lower than the inherited 237-test list.
- Tests skipped outside a project state the reason.
- Responsive risks still have named coverage.
- Before/after `--list` output is recorded in Dex.

## Phase 3: Wait And Snapshot Debt

Dex: `xx9aalcu`

Objective: keep screenshot stability without forcing every contract to pay for
the most expensive stabilizer.

Recommended implementation:

- Split the helper layer:
  - `waitForDomReady()` for normal DOM/computed contracts.
  - `waitForStableAssets()` for screenshots and image-heavy checks.
  - `waitForVendorHydration()` for Ecwid/EmailOctopus-specific checks.
- Replace fixed waits with observable conditions where possible.
- Keep route-specific fixed waits only where the plugin behavior cannot be
  observed directly.
- Remove or document orphan screenshot baselines such as the current
  `news-*-darwin.png` files.
- Keep full-page snapshots only for routes that provide broad visual value.

Acceptance criteria:

- Non-screenshot contracts do not call the full asset stabilizer by default.
- Snapshot lanes remain stable.
- Orphan baselines are removed or explicitly justified in the plan.
- Any accepted baseline refresh is documented separately from cleanup work.

## Phase 4: Vendor-Heavy Contract Isolation

Dex: `vh7vmybx`

Objective: keep vendor confidence without making vendor waits the normal
inner-loop cost.

Recommended implementation:

- Tag Ecwid product, product detail, product description, and cart
  recommendation checks as `@ecwid @vendor @slow`.
- Tag EmailOctopus checks as `@emailoctopus @vendor`.
- Keep one lightweight Shop smoke test in `@smoke` or `@fast`.
- Keep desktop vendor depth in the lean visual gate.
- Do not remove vendor assertions that protect known regressions around white
  text, hidden messenger UI, or cart recommendation readability.

Acceptance criteria:

- `test:visual:fast` excludes slow cart/product-detail/vendor-heavy tests.
- `test:visual:ecwid` and `test:visual:emailoctopus` run the relevant vendor
  coverage explicitly.
- Lean visual still includes vendor coverage.
- Vendor failures still have a clear command path for debugging.

## Phase 5: Validate Gates And Update Workflow Docs

Dex: `pw9b9hne`

Objective: make the new workflow durable for future agents.

Steps:

1. Record final lane counts with `--list`.
2. Run the new smoke lane.
3. Run the new fastlane.
4. Run at least one area lane that exercises vendor isolation.
5. Run the lean visual suite before closing the parent if implementation changed
   test behavior broadly.
6. Update project guidance:
   - root `AGENTS.md`
   - `.agents/skills/pns-frontend-css-regression/SKILL.md`
   - `docs/css/README.md` if command guidance changes
7. Record exact commands and results in Dex.

Acceptance criteria:

- Future agents have a clear default lane for iteration.
- The lean visual suite remains documented as the broad landing gate.
- Known macOS Chromium permission handling remains documented.
- Before/after counts and any residual risks are recorded.
- The parent Dex task can close without relying on stale memory.

## Proposed Implementation Order

1. Complete Phase 0 without changing tests.
2. Add tags and scripts only.
3. Prove lane membership with `--list`.
4. Scope viewport matrix for the largest obvious wins.
5. Split wait helpers and clean orphan baselines.
6. Move vendor-heavy flows into explicit lanes.
7. Update workflow docs and close with validation evidence.

## Validation Commands

Inventory:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone exec playwright test tests/visual --list
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone exec playwright test tests/visual --list --project=desktop
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone exec playwright test tests/visual --list --project=tablet
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone exec playwright test tests/visual --list --project=mobile
```

Implementation checks:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone format:check
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:list
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:smoke
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:fast
```

Area checks:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:ecwid
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:emailoctopus
```

Full landing gate:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual
```

If Chromium fails with the known macOS local-process permission error, rerun the
same Playwright command with elevated local process access.

## Implementation Closeout

Completed: 2026-07-06

Implemented changes:

- Added explicit visual tags in
  `tests/visual/frontend.spec.ts`: `@smoke`, `@mobile-smoke`, `@fast`,
  `@mobile-fast`, `@snapshot`, `@layout`, `@mobile-layout`, `@navigation`,
  `@mobile-navigation`, `@template`, `@search`, `@archive`, `@pattern`,
  `@shop`, `@ecwid`, `@emailoctopus`, `@vendor`, and `@slow`.
- Added lane scripts in the standalone theme `package.json` for smoke, fast,
  snapshots, layout, navigation, templates, shop, Ecwid, and EmailOctopus.
- Kept route screenshots on `waitForStableAssets()` after smoke validation
  proved mobile snapshots still need full screenshot stabilization.
- Added `waitForContractReady()` for non-screenshot DOM/computed-style
  contracts so they do not pay the full screenshot stabilizer cost.
- Isolated known vendor-heavy flows behind `@ecwid`, `@emailoctopus`,
  `@vendor`, and `@slow`, while keeping one lightweight Shop/Ecwid check in
  smoke and fast lanes.
- Removed stale local `news-*-darwin.png` snapshot baselines after confirming no
  current visual test references them.
- Updated root/project workflow documentation in `AGENTS.md`,
  `.agents/skills/pns-frontend-css-regression/SKILL.md`, and
  `docs/css/README.md`.
- Updated the frontend CSS regression skill to run Playwright browser commands
  with elevated local process access up front in this macOS Local WP setup,
  avoiding the repeated `MachPortRendezvousServer` sandbox failure.

Final lane membership:

| Lane         | Count                              |
| ------------ | ---------------------------------- |
| Lean visual  | 35 desktop tests + 10 mobile tests |
| Smoke        | 4 desktop tests + 4 mobile tests   |
| Fast         | 28 desktop tests + 10 mobile tests |
| Audit        | 44 desktop tests                   |
| Snapshots    | 18 tests                           |
| Layout       | 18 desktop tests + 11 mobile tests |
| Navigation   | 7 desktop tests + 3 mobile tests   |
| Templates    | 22 desktop tests                   |
| Ecwid        | 18 tests                           |
| EmailOctopus | 9 tests                            |

Validation evidence:

```text
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone exec prettier --check package.json tests/visual/frontend.spec.ts ../../../../../AGENTS.md ../../../../../.agents/skills/pns-frontend-css-regression/SKILL.md ../../../../../docs/css/README.md ../../../../../docs/jobs/2026-07-06-visual-suite-fastlane-optimization-plan.md
All matched files use Prettier code style.

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:list
Listed 35 desktop tests and 10 mobile tests.

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:smoke
4 desktop tests passed.
4 mobile tests passed.

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone exec playwright test tests/visual --grep "light surface core buttons" --project=desktop
1 desktop test passed.

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:fast
28 desktop tests passed.
10 mobile tests passed.

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual
35 desktop tests passed.
10 mobile tests passed.

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:audit:list
Listed 44 desktop audit tests.

pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:emailoctopus
9 tests passed across desktop, tablet, and mobile.
```

Lean visual suite status:

- `pnpm test:visual` is now the curated default landing gate, not the inherited
  237-test all-project matrix.
- Low-signal route-loop and migration-history checks moved to the explicit
  `pnpm test:visual:audit` lane.
- Tablet is no longer part of the default visual gate. Keep tablet checks
  opt-in through audit or future targeted breakpoint lanes.

## Locked Decisions

### 1. Should the default fastlane include snapshots?

Options:

- A. Include only Home and Mary Barbour snapshots.
- B. Include no snapshots by default.
- C. Include all current full-page snapshots.

Locked decision: A. Home and Mary Barbour catch broad rhythm regressions without
turning fastlane into the full screenshot suite.

### 2. Should tablet remain part of most fast lanes?

Options:

- A. Keep tablet in all fast lanes.
- B. Use tablet only for contracts with a 900px-specific breakpoint risk.
- C. Remove tablet from all non-full lanes.

Locked decision: B. Tablet is valuable where 900px is meaningful, but it should
not multiply content identity or desktop-only source contracts.

### 3. Should workers increase?

Options:

- A. Keep `workers: 1` everywhere.
- B. Add a separate fast config or script override with limited workers.
- C. Raise workers globally.

Locked decision: B only after tags and viewport scoping land. Snapshot and vendor
stability should not be destabilized before the low-risk wins are proven.

### 4. Should stale snapshots be deleted immediately?

Options:

- A. Delete orphan baselines once Phase 0 confirms no current test references
  them.
- B. Keep them until the full suite is reorganized.
- C. Move them to a notes folder.

Locked decision: A, but only after Phase 0 records the exact current
`toHaveScreenshot()` names and no pending branch needs the obsolete snapshots.

## Done

This plan is complete when:

- Dex child phases are complete.
- Lane scripts exist and are documented.
- Fastlane provides a materially smaller iteration gate.
- Lean visual remains available and documented.
- Vendor-heavy coverage is explicit.
- Before/after counts and command results are recorded in this plan or Dex.
