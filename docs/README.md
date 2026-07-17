# Theme handbook

This handbook explains how to maintain, validate, and hand off the Protests
and Suffragettes theme. It is written for another developer taking over the
client site, rather than as public-theme documentation.

Begin with the repository [README](../README.md). It establishes the canonical
repository, WordPress installation path, and support boundary.

## Available guidance

- [Development and release](operations/development-and-release.md) — local
  setup, validation gates, WordPress-state checks, and the RAN GitHub Booster
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
- [Production readiness and handoff plan](plans/2026-07-16-production-readiness-and-handoff-compendium-plan.md)
  — current implementation sequence, open evidence requirements, and the
  intended handbook catalogue.
- [Navigation fixtures](../navigation/README.md) — ownership and lifecycle of
  the default navigation records.
- [Synced pattern fixtures](../synced-patterns/README.md) — lifecycle and
  switch-away rules for theme-owned synced patterns.

The template/component inventory is deliberately provisional: it reports the
current code-backed surface but does not treat the set as frozen. The
administrator guide and compatibility policy remain evidence-led work. The
page-rebuild proof is the decision point for formalising authoring guidance,
new templates, or new patterns.

## Documentation boundaries

Keep durable theme knowledge in this repository. Do not commit Local-specific
configuration, database dumps, credentials, GitHub PATs, webhook secrets, or
private hosting access details. The Local wrapper repository is an integration
environment and may retain historical evidence, but it is not the theme
handbook.

Completed planning records are evidence, not the operating manual. Link to a
current handbook page when it exists instead of repeatedly copying historical
decisions.

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
