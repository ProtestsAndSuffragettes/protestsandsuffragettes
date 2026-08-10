# Core Selector Remediation Plan

Created: 2026-07-07.

All paths are relative to the project root.

## Purpose

Queue narrow remediation work from the core-block selector classification
inventory without turning it into a broad CSS deletion pass.

The classification found that most broad selectors have real ownership:
baseline typography, saved-content compatibility, page rhythm, component
surfaces, or control/vendor exclusions. The useful remediation is targeted:
add coverage, separate saved preset fallbacks from base defaults, decide the
`.alignwide` contract, reconcile rhythm ownership, and narrow mixed Navigation
selectors only after generic Navigation behavior is proven.

Accepted decisions:

- Saved font preset fallbacks should be treated as compatibility until proven
  otherwise, but old saved refs should be found and fixed so redundant fallback
  CSS can eventually be deleted.
- `.alignwide` remains a broad theme layout and saved-content compatibility
  contract by default.
- `content-rhythm.css` should be simplified only where stable hooks and clear
  owners exist.
- `.wp-block-quote p` likely belongs in `core-quote.css`, pending load-path
  proof.
- Navigation selector narrowing should wait for coverage and should only move
  obvious primary/header ownership.

Implementation notes:

- The 2026-07-08 read-only DB scan found live non-revision selector usage in
  six `herstory` rows, twenty-nine `page` rows, one `post` quote candidate, five
  active-theme `wp_template` rows, and one active-theme `wp_template_part` row.
  Do not delete saved font preset fallback or `.alignwide` compatibility rules
  from this plan.
- Saved font preset fallback rules are retained but split from base element
  defaults into `styles/base/font-preset-compat.css`.
- `.alignwide` remains a permanent theme layout and saved-content
  compatibility contract.
- `.wp-block-quote p` ownership is resolved to `styles/blocks/core-quote.css`;
  the duplicate line-height rule in `content-rhythm.css` should stay removed as
  long as quote frontend/editor checks pass.
- Navigation submenu border, submenu link padding, submenu icon, hover marker,
  and closed responsive-container hover selectors are scoped to primary
  Navigation after synthetic generic/primary fixture coverage proved the leak.

## Related Work

- Classification plan:
  `docs/jobs/2026-07-07-core-block-selector-classification-plan.md`
- Classification inventory:
  `docs/jobs/2026-07-07-core-block-selector-classification-inventory.md`
- Content rhythm hardening plan:
  `docs/jobs/2026-07-07-content-rhythm-heuristic-hardening-plan.md`
- Core Navigation drawer migration:
  `3b7aa2260 refactor(nav): use core responsive drawer`

## Dex Tracking

- Parent: `bi5bfxec` - Remediate core selector ownership follow-up risks
- Child: `okhojgjd` - Add selector ownership contract coverage
- Child: `c3a5ds8b` - Separate saved font preset fallback ownership
- Child: `l2xonqgq` - Define alignwide layout compatibility contract
- Child: `kotz39ie` - Reconcile content rhythm with block and component owners
- Child: `q6g09dio` - Narrow Navigation selector ownership safely

## Non-Goals

- Do not delete broad selectors just because they are broad.
- Do not retune typography, spacing, or the visual design as part of this
  remediation queue.
- Do not migrate saved classes, spacing presets, palette slugs, alignments, or
  serialized block-support output without DB backup, dry run, apply, and
  post-apply scan.
- Do not reopen the accepted core Navigation drawer migration.
- Do not advance Herstories migration work while `h3rs0t00` is awaiting client
  approval.
- Do not refresh visual baselines before drift is classified and accepted.

## Execution Plan

### 1. Add Selector Ownership Contract Coverage

Dex: `okhojgjd`

Risk:

The inventory identifies several high-risk selectors that are legitimate but
under-protected. Coverage should land before selector ownership changes.

Scope:

- Saved font preset fallbacks:
  - `.has-large-font-size`;
  - `.has-x-large-font-size`;
  - `.has-medium-font-size`.
- `.alignwide` layout behavior across mobile and desktop.
- `content-rhythm.css` root stack, generated flow reset, list exclusions, and
  vendor/control exclusions.
- `.wp-block-quote p` paragraph rhythm versus `core-quote.css` ownership.
- `.pns-light-surface` text, link, separator, button, and section-variable
  bridge behavior.
- High-risk retained contract comments:
  - `.pns-light-surface`;
  - generated flow resets;
  - drawer submenu `!important` overrides.
- Generic versus primary Navigation selectors:
  - generic Navigation item padding;
  - primary Navigation overrides;
  - footer/generic Navigation behavior;
  - desktop submenu hover marker;
  - core drawer submenu priority overrides.

Acceptance:

- Tests assert behavior, not incidental selector strings.
- Coverage does not require CSS behavior changes beyond test fixtures unless
  explicitly scoped.
- Comments/tests land before selector movement.
- Future selector movement has a reliable safety net.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
```

### 2. Separate Saved Font Preset Fallback Ownership

Dex: `c3a5ds8b`

Risk:

`styles/base/elements.css` mixes element defaults with saved preset class
fallbacks:

- `h2`, `h3`, `.has-large-font-size`;
- `.has-x-large-font-size`;
- `.has-medium-font-size`.

Those preset classes may be DB-backed compatibility, not true element defaults.

Scope:

- Prove current `theme.json` and generated preset output.
- Scan source and DB-backed content for saved preset class usage.
- If old saved refs still require fallback classes, fix those refs through a
  backed-up, dry-run, apply, and post-apply scan flow so redundant fallback CSS
  can be deleted.
- If fallbacks are still needed after cleanup, move or document them as
  compatibility rules with a named deletion gate.
- If a fallback is redundant, remove only after source and DB scans prove it.

Acceptance:

- Base element defaults and saved preset compatibility are no longer ambiguous.
- Any retained preset fallback has an owner comment and test coverage.
- No saved-content compatibility is removed without DB proof.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
```

### 3. Define Alignwide Layout Compatibility Contract

Dex: `l2xonqgq`

Risk:

`.alignwide` is high-risk saved-content compatibility. It may be a permanent
theme layout primitive, a compatibility bridge, or both.

Scope:

- Audit source and DB usage of `.alignwide` and `.alignwide.alignfull`.
- Compare current visual contracts and saved template behavior.
- Decide whether `.alignwide` remains a permanent layout primitive or becomes a
  compatibility bridge with an explicit deletion gate.
- Default to retaining broad `.alignwide` unless current evidence strongly
  proves a narrower owner.
- The accepted default is to keep broad `.alignwide` as a permanent theme layout
  and saved-content compatibility contract for now.
- Do not mutate serialized layout output during the decision cut.

Acceptance:

- `.alignwide` has a documented owner and test coverage.
- Any future migration path includes backup, dry run, apply, and post-apply
  scan.
- Mobile `.alignwide` behavior remains stable.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
```

### 4. Reconcile Content Rhythm With Block And Component Owners

Dex: `kotz39ie`

Risk:

`content-rhythm.css` owns real page rhythm, but the inventory found some
component/pattern and block ownership inside it:

- PNS section/template-part/pattern QA resets;
- generated flow compatibility;
- list exclusions for controls and vendors;
- `.wp-block-quote p`, which overlaps with `styles/blocks/core-quote.css`.

Scope:

- Move component-owned rhythm out of `content-rhythm.css` only where stable
  hooks already exist.
- Reconcile `.wp-block-quote p`; `styles/blocks/core-quote.css` is the likely
  owner, but block-registered and bundled load paths must be checked before
  moving or deleting the `content-rhythm.css` rule.
- Move stable `.pns-section`, `.wp-block-template-part`, `.pns-pattern-qa`, and
  known component spacing resets to component owners only when replacement
  hooks are proven.
- Keep generated flow/list/vendor/control exclusions until replacement
  selectors and editor/frontend tests prove safe.
- Prefer simplicity over ideal ownership: leave gnarly but necessary exclusions
  in place until a replacement is clearly safer.
- Coordinate with the dedicated content-rhythm hardening plan rather than
  duplicating it.

Acceptance:

- Content rhythm remains stable for ordinary authored content.
- Vendor/control lists do not regress.
- Quote paragraph ownership is singular or explicitly documented.
- Any remaining generated-class heuristic has a reason and test coverage.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:shop
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:emailoctopus
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
```

### 5. Narrow Navigation Selector Ownership Safely

Dex: `q6g09dio`

Risk:

The split Navigation CSS is acceptable, but some selectors still mix generic
core Navigation behavior with primary/header behavior:

- generic item padding and content padding;
- submenu icon stroke;
- desktop submenu item border and hover marker;
- top-item padding;
- closed responsive-container submenu hover rules currently living in the core
  drawer partial.

Scope:

- Prove generic/footer Navigation behavior before narrowing selectors.
- Preserve accepted primary Navigation and core drawer behavior.
- Move desktop-owned rules out of the drawer partial only after tests prove
  ownership.
- Keep open drawer, modal, breakpoint, and priority overrides in the drawer
  partial.
- Do not disturb font-size or block-support control behavior while scoping
  primary-header selectors.
- Narrow only obvious primary/header rules after coverage exists; do not pursue
  a full Navigation CSS rewrite in this remediation task.
- Do not redesign the drawer or revisit the core drawer migration decision.

Acceptance:

- Generic Navigation blocks are not accidentally shaped as primary/header nav.
- Primary Navigation and drawer behavior remain accepted.
- Navigation CSS file ownership is easier to read.
- `test:visual:navigation` passes for the touched behavior.

Validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
```

## Recommended Order

1. `okhojgjd` - Add selector ownership contract coverage.
2. `c3a5ds8b` - Separate saved font preset fallback ownership.
3. `l2xonqgq` - Define alignwide layout compatibility contract.
4. `kotz39ie` - Reconcile content rhythm with block and component owners.
5. `q6g09dio` - Narrow Navigation selector ownership safely.

Coverage comes first because the classification inventory mostly says "retain
for now." The other tasks should use evidence from coverage and DB/source scans
before changing behavior.

## Done When

- Each child task has landed or been explicitly deferred with a current reason.
- High-risk retained selectors have tests or documented owner comments.
- No broad selector deletion happens without source/DB evidence.
- Herstories client-approval work remains parked outside this queue.
