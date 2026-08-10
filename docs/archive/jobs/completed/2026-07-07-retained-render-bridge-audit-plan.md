# Retained Render Bridge Audit Plan

Created: 2026-07-07.

All paths are relative to the project root.

## Purpose

Audit retained runtime bridges that still affect CSS, layout, editor controls,
or ownership clarity after the recent render-filter and Navigation work.

This is not a mandate to convert every render filter to a block. Some runtime
bridges are honest owners in this project. The audit should identify which
bridges are still justified, which need comments/tests, and which should be
split to separate follow-up work.

## Related Work

- Parent backlog evidence:
  `docs/jobs/2026-07-06-theme-css-control-health-remediation-plan.md`
- Render-filter/template remediation plan:
  `docs/jobs/2026-07-06-render-filter-template-remediation-plan.md`
- Core Navigation drawer commit:
  `3b7aa2260 refactor(nav): use core responsive drawer`

## Current Evidence

Likely files:

- `app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/block-filters.php`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/navigation.php`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/search.php`
- `app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/template-tags.php`
- `docs/jobs/2026-07-06-render-filter-template-remediation-plan.md`

Known retained bridge families include:

- Ecwid and EmailOctopus output adaptation;
- legacy single-post content cleanup;
- archive featured-image fallback;
- search result date formatting;
- template and navigation ref resolution;
- Navigation spacing and primary-class bridges;
- sparse section hiding and stable pagination;
- Herstory entry navigation bridge, which is excluded from active work while
  `h3rs0t00` is awaiting client approval.
- route/body-context hooks for search, Herstories body classes, and the
  rollback-gated template reveal.

## Non-Goals

- Do not reopen completed Shop/template remediation.
- Do not replace legitimate runtime glue just to reduce PHP hook count.
- Do not convert every render bridge to a custom block.
- Do not mutate saved templates or content during the audit.
- Do not start Herstories migration or owner-decision work without client
  approval.
- Do not remove runtime bridges that adapt third-party output the theme cannot
  control through normal block supports.

## Guardrails

- Distinguish honest runtime ownership from frontend replacement that makes the
  editor source misleading.
- Tests should assert ownership behavior, not incidental class strings.
- Missing comments or tests can be fixed under this task; behavior changes
  should usually be split to a separate task.
- Any DB-backed template or synced-pattern mutation requires backup,
  rollback criteria, and post-apply verification.

## Dex Tracking

This plan is queued in the standalone tracker:

- Parent: `4kcr7xve` - Audit retained render bridges after Navigation and
  template cleanup
- Cut 0: `4wk2glck` - Render bridge audit Cut 0 - inventory retained runtime
  bridges

Do not pre-create conversion tasks. The audit output should decide which, if
any, conversions are worth doing.

## Execution Plan

### Cut 0 - Build The Bridge Inventory

Objective: list every retained render/data bridge and classify its owner.

Classification categories:

- third-party adapter;
- WordPress core output gap;
- DB-backed ref resolver;
- route/query policy;
- editor/frontend honesty risk;
- temporary compatibility bridge;
- client-approval-blocked Herstories item.

Acceptance:

- Every retained bridge in the likely files has an owner category.
- The baseline classification from the render-filter remediation plan has been
  reconciled with current source.
- Completed Shop/template remediation is cited as input evidence, not reopened.

### Cut 1 - Add Comments Or Tests For Retained Bridges

Objective: make retained runtime behavior legible.

Steps:

- Add short why-comments where the owner is not obvious.
- Add focused tests for retained behavior where the blast radius is meaningful.
- Avoid changing markup unless the audit finds a current defect.

Acceptance:

- A future maintainer can tell why each retained bridge exists.
- Tests cover behavior that would break if the bridge were removed.

### Cut 2 - Split Residual Work

Objective: create follow-up plans only for bridges that are truly unhealthy.

Possible follow-ups:

- replace a misleading static pattern with an honest dynamic block;
- remove a stale bridge after proving saved content no longer needs it;
- move plugin-owned behavior into a project plugin;
- leave a temporary bridge with a named deletion gate.

## Validation

Template validation:

```bash
php app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-block-templates.php
```

Targeted visual lanes if behavior changes:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
```

Editor-content validation only with explicit exported fixtures:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check:editor-content -- path/to/exported-post-content.html
```

Do not list `check:editor-content` as a no-argument gate.

## Done When

- Retained bridges are classified and documented.
- Any behavior-changing work is split to its own task.
- Herstories migration remains parked until client approval.
- No PHP bridge remains solely because a template or saved content source is
  silently wrong.

## Cut 0 Evidence - 2026-07-07

Cut 0 produced a scan-only audit inventory:

`docs/jobs/2026-07-07-retained-render-bridge-audit-inventory.md`

No PHP behavior, templates, saved content, CSS, or visual baselines were
changed.
