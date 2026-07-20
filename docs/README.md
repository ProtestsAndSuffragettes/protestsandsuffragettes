# Theme handbook

This handbook explains how to maintain, validate, and hand off the Protests
and Suffragettes theme. It is written for another developer taking over the
client site, rather than as public-theme documentation.

Begin with the repository [README](../README.md). It establishes the canonical
repository, WordPress installation path, and support boundary.

## Available guidance

- [Development and release](operations/development-and-release.md) — local
  setup, validation gates, WordPress-state checks, and the RAN Booster
  beta acceptance, deployment, and recovery procedure.
- [Page rebuild lab](operations/page-rebuild-lab.md) — draft-only method for
  proving editor reconstruction before formalising templates or components.
- [CSS architecture](architecture/css.md) — authored/compiled delivery paths,
  ownership boundaries, bridge policy, and validation workflow.
- [Ownership and integrations](architecture/ownership-and-integrations.md) —
  theme, plugin, vendor, and WordPress-database responsibilities; dependency
  contract; and safe handoff routing.
- [Current template and component inventory](reference/templates-parts-patterns.md)
  — code-backed template, pattern, seed-fixture, and functional-component map;
  it identifies where database ownership still needs checking.
- [Navigation fixtures](../navigation/README.md) — ownership and lifecycle of
  the default navigation records.
- [Synced pattern fixtures](../synced-patterns/README.md) — lifecycle and
  switch-away rules for theme-owned synced patterns.

The template/component inventory reports the current code-backed surface but
does not freeze future additions. Compatibility claims remain evidence-led;
use the compatibility policy and release runbook when validating a higher
WordPress/PHP target. The page-rebuild lab remains the decision point for
formalising new authoring guidance, templates, or patterns.

## Documentation boundaries

Keep durable theme knowledge in this repository. Do not commit Local-specific
configuration, database dumps, credentials, GitHub PATs, webhook secrets, or
private hosting access details. The Local wrapper repository is an integration
environment and may retain historical evidence, but it is not the theme
handbook.

Historical plans and migration records do not belong in the handbook. Record
only the current operating contract and link to its maintained owner.

## Handbook maintenance contract

**Audience:** theme developers, reviewers, and release operators.

**Authoritative sources:** current theme files, `package.json`, verified
WordPress state, and approved deployment settings.

**Update this index when:** a handbook page is added, retired, renamed, or its
audience/scope changes. Update the relevant reference page in the same change
as any new or materially changed template, component, integration, or release
step.

**Validation:** run `pnpm format:check`; run the relevant implementation gate
from the [development and release runbook](operations/development-and-release.md)
when documentation accompanies code or deployment work.
