# Priority And DB Compatibility Debt Plan

Created: 2026-07-07.

All paths are relative to the project root.

## Status

Superseded and stale as an active job.

Later targeted cleanup plans handled the saved-content compatibility work that
this plan originally grouped together: legacy spacing, font preset fallback,
public token cleanup, and retained rhythm/navigation compatibility decisions.
The remaining useful priority-rule review belongs inside the open cascade-layer
migration plan, especially `mlqbujkv` Cut 0, where current `!important` families
can be classified against the actual cascade strategy.

Do not create a new Dex parent from this document. Treat it as historical
context only.

## Purpose

Re-evaluate remaining `!important` rules and saved-content compatibility
bridges without treating them as a single cleanup target.

The goal is to classify priority rules and compatibility aliases family by
family, then remove only the rules whose owner boundary is already fixed and
whose saved-content risk has been checked.

## Related Work

- Parent backlog evidence:
  `docs/jobs/2026-07-06-theme-css-control-health-remediation-plan.md`
- Completed vendor override re-evaluation plan:
  `docs/jobs/__completed/2026-07-06-vendor-override-debt-final-reevaluation-plan.md`
- Core selector classification plan:
  `docs/jobs/2026-07-07-core-block-selector-classification-plan.md`

## Current Evidence

The current source scan still finds authored `!important` declarations outside
compiled `styles/dist` output. A source-anchor inspection found 71 current
declarations, concentrated around:

- Herstories bio image constraints;
- button/group gap overrides;
- cross-site banner CTA;
- footer layout column widths;
- split-section media and Jetpack slideshow sizing;
- EmailOctopus vendor overrides;
- Navigation drawer and desktop rules;
- Social Links and other core block priority exceptions;
- other vendor or block-specific boundaries.

The parent plan explicitly allows priority rules where they cross real
boundaries:

- vendor runtime CSS;
- WordPress inline block-support styles;
- serialized layout conflicts;
- documented core Navigation or Social Links priority exceptions.

DB/token risk anchors include `theme.json`, source templates, template parts,
synced-pattern fixtures, and live DB records that may still reference palette
slugs, spacing presets, `pnsRefSlug`, or old compatibility classes.

## Non-Goals

- Do not run a broad remove-all-`!important` pass.
- Do not retire palette slugs, spacing presets, block classes, or serialized
  values without a DB-backed migration plan.
- Do not treat vendor/plugin overrides as theme cleanup unless the plugin or
  account-side owner is understood.
- Do not retire `white`, `black`, `foreground`, `pnsRefSlug`, or any serialized
  value solely from a filesystem scan.
- Do not include `styles/dist/**` in source debt counts.
- Do not start Herstories client-approval work through this plan.

## Guardrails

- Every retained project-owned priority rule should have a local comment naming
  the boundary it crosses.
- Vendor overrides can remain when the theme cannot control the source output.
- DB compatibility aliases need a removal trigger and owner if retained.
- Any serialized migration requires backup, dry run, apply, and post-apply scan.

## Dex Tracking

This plan is not queued yet. If accepted, create one parent task first:

`Classify remaining priority and DB compatibility debt`

Do not queue removal children until the family inventory has been reviewed.

## Execution Plan

### Cut 0 - Refresh Priority Inventory

Objective: produce a current family-by-family `!important` inventory.

Steps:

- Run `git status --short`.
- Scan authored CSS excluding `styles/dist/**`.
- Record the current count in the plan closeout if it changes from the
  source-anchor inspection.
- Group findings by owner family: core block, component, vendor, saved-content
  compatibility, or temporary migration bridge.
- Compare against the completed vendor override re-evaluation plan.

Acceptance:

- The count is current and excludes compiled output.
- Each priority family has an owner and proposed disposition.

### Cut 1 - Classify DB Compatibility Risk

Objective: identify any saved-content dependency before removal is considered.

Steps:

- Search filesystem templates, patterns, fixtures, and tests for candidate
  classes/tokens.
- Use WP-CLI to scan live `wp_template`, `wp_template_part`, `wp_block`,
  `wp_navigation`, `wp_global_styles`, and content records when serialized
  values are at risk.
- Record backup and rollback requirements for any future mutation.

Acceptance:

- No candidate removal proceeds without affected-record evidence.
- Compatibility aliases are either current, obsolete, or unknown.

### Cut 2 - Remove Or Document One Family At A Time

Objective: make small reversible cleanups.

Allowed outcomes:

- remove a priority rule after source and DB scans prove it is obsolete;
- replace it with a narrower selector when owner boundaries are clear;
- retain it with a comment naming the boundary;
- split to a plugin/account follow-up when the source is not theme-owned.

Acceptance:

- Each commit touches one coherent family.
- Retained debt has an owner and removal gate.

## Validation

Compile and check:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone check
```

Targeted visual lanes depend on the family touched:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:layout
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:shop
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:ecwid
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:emailoctopus
```

DB-backed compatibility gates:

```bash
wp theme list --status=active
wp option get stylesheet
wp option get template
wp post list --post_type=wp_global_styles,wp_template,wp_template_part,wp_block,wp_navigation --fields=ID,post_type,post_name,post_status
```

## Done When

- Remaining priority rules are classified, removed, or documented.
- Compatibility aliases have owners and removal triggers.
- No live DB content references retired tokens or classes unless a bridge is
  intentionally retained.
