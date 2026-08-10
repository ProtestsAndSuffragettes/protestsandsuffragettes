# Pattern Inserter Allowlist Cleanup Plan

Created: 2026-07-07.

All paths are relative to the project root.

## Purpose

Review the editor pattern inserter policy so editors see approved starter
patterns without blocking required plugin patterns or native synced patterns.

This is a product/editor policy task, not visual polish. It should be queued
only if the current inserter is noisy, misleading, or blocking legitimate
editor workflows.

## Closeout - 2026-07-10

No active Dex task was created from this plan, and no current editor workflow
problem is open against the pattern inserter policy. The current allowlist
therefore remains the accepted policy until one of the review triggers below is
observed.

This document is retained as completed/deferred policy context, not as active
implementation work.

## Review Triggers

Do not reopen this plan just because the allowlist exists. Reopen it only when
there is a concrete editor workflow problem, such as:

- editors cannot find an approved PNS starter pattern they reasonably expect to
  insert;
- required plugin patterns, such as contact/form patterns, disappear from the
  inserter;
- remote/demo/default WordPress patterns reappear and crowd out the PNS pattern
  library;
- internal compatibility/scaffold patterns are visible and tempting editors into
  deprecated layouts;
- native synced patterns stop appearing through WordPress' supported "My
  patterns" surface;
- Herstories-specific pattern visibility needs a product decision after client
  approval.

If none of those symptoms is present, leave the current allowlist alone.

## Related Work

- Parent backlog evidence:
  `docs/jobs/2026-07-06-theme-css-control-health-remediation-plan.md`
- Pattern registration source:
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/inc/patterns.php`
- Synced pattern fixtures:
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/synced-patterns/`

## Current Evidence

`inc/patterns.php` currently:

- registers PNS pattern categories;
- registers code-backed PNS patterns through
  `pns_standalone_get_code_patterns()`;
- seeds and maintains synced `wp_block` patterns from the theme fixture
  manifest;
- disables remote WordPress.org block patterns;
- enforces a blessed pattern library by allowing registered PNS code patterns
  plus `ran-forms/contact-form`;
- allows pattern categories `pns-layout`, `pns-quotes`, `pns-herstories`,
  `default`, and `ran-forms`.
- keeps `pns/entry-herstory-navigation` public by default through the
  code-backed pattern registry.

The code comments state that native synced patterns appear under WordPress'
"My patterns" surface and are not hidden by registry cleanup.

## Non-Goals

- Do not remove remote/demo pattern suppression without an explicit editorial
  decision.
- Do not mutate synced pattern content in this policy pass.
- Do not change public Herstories pattern availability as a back door for
  `h3rs0t00`; Herstories migration remains client-approval pending.
- Do not remove `pns/entry-herstory-navigation` from the inserter unless a
  separate Herstories owner task or explicit editor-policy decision scopes it.
- Do not block required plugin patterns such as form patterns.
- Do not treat this as CSS cleanup.

## Guardrails

- Verify the current editor inserter before changing policy.
- Preserve WordPress native synced-pattern behavior.
- Keep an explicit allowlist, but document why each non-PNS plugin pattern or
  category is admitted.
- If visibility changes affect saved patterns or templates, scan `wp_block`,
  templates, template parts, and current content first.

## Dex Tracking

This plan is not queued yet. If accepted, create one parent task first:

`Review PNS pattern inserter allowlist and editor policy`

Do not pre-create removal tasks. The first cut should prove whether there is an
actual editor workflow problem.

## Execution Plan

### Cut 0 - Verify Current Inserter Behavior

Objective: inspect the actual editor experience and registry state.

Steps:

- Reconfirm active theme and active plugins.
- Inspect the inserter in the editor, including PNS code patterns, plugin form
  patterns, and native synced patterns.
- List registered pattern names and categories after
  `pns_standalone_enforce_blessed_pattern_library()` runs.
- Record the exact reason for any allowed non-PNS pattern or category.
- Confirm remote/demo patterns remain suppressed.

Acceptance:

- The current visible pattern set is documented.
- Any noisy, missing, or misleading pattern is named.
- Required plugin patterns are identified before policy changes.

### Cut 1 - Decide The Allowlist Contract

Objective: record what the inserter should expose.

Questions:

- Which PNS code-backed patterns are true starter patterns?
- Which compatibility or scaffold patterns should stay hidden?
- Which plugin patterns are required for editors?
- Should any category remain visible only because WordPress needs it for native
  synced patterns?

Acceptance:

- The allowlist has an editor-facing rationale.
- Herstories client-approval scope remains excluded.

### Cut 2 - Implement Minimal Policy Changes

Objective: adjust only the policy proven by Cut 0 and Cut 1.

Possible changes:

- update `inserter` flags for code-backed patterns;
- add comments for plugin pattern/category allowlist entries;
- refine `pns_standalone_allowed_block_patterns` or category defaults;
- add tests or a registry assertion script if the policy keeps drifting.

Acceptance:

- The inserter exposes only patterns editors should reasonably start from.
- Required plugin patterns remain available.
- Native synced patterns still appear through WordPress' supported surface.

## Validation

Template validation:

```bash
php app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-block-templates.php
```

Editor validation:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
```

If DB-backed pattern records are touched, add WP-CLI scans and backups before
mutation.

## Done When

- The current inserter policy is documented.
- Any allowlist change has an editor-facing reason.
- Required plugin and synced pattern paths still work.
- No client-approval-pending Herstories work is advanced by this task.
