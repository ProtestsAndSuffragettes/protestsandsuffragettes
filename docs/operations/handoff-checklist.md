# Theme handoff checklist

Use this checklist when handing the theme to another developer or preparing a
client-environment release. It records durable evidence, not credentials,
database exports, or host-specific configuration.

## Repository and runtime

- [ ] Clone the canonical theme repository into
      `wp-content/themes/protestsandsuffragettes/` and install its locked Node
      dependencies.
- [ ] Confirm the active stylesheet and template are both
      `protestsandsuffragettes`.
- [ ] Record the serving WordPress/PHP versions, active theme commit/version,
      and active required-plugin versions in the access-controlled release record.
- [ ] Open **Tools → Site Health** and confirm the PNS dependency test is good.

## Code and WordPress state

- [ ] Read the [ownership map](../architecture/ownership-and-integrations.md),
      [component catalogue](../reference/component-and-functionality-catalog.md),
      and [template inventory](../reference/templates-parts-patterns.md).
- [ ] Run `pnpm check`, `pnpm check:block-templates`,
      `pnpm check:template-ownership`, `pnpm check:retained-render-bridges`, and
      `pnpm check:release-handoff`.
- [ ] Treat saved navigation, synced patterns, social links, global styles,
      content, and any saved Site Editor record as database state. Capture and
      review it before changing a matching fixture or template file.
- [ ] Confirm the three navigation records and five seeded synced-pattern
      records resolve at their real frontend uses.

## Visual and reconstruction evidence

- [ ] Run the focused visual lane for changed surfaces, then `pnpm test:visual`
      for a significant visual/template release. Investigate a failure before any
      snapshot update.
- [ ] Complete the [page rebuild lab](page-rebuild-lab.md) for any newly
      proposed authoring workflow before formalising it as a template or pattern.
- [ ] Verify desktop/mobile primary navigation, a News post, search results,
      Herstories archive/single, the shop, contact/newsletter forms, and a page
      that uses each changed synced pattern.

## Deployment and recovery

- [ ] Follow the RAN Booster acceptance and release procedure in
      [development and release](development-and-release.md). Do not store PATs,
      webhook secrets, or host configuration in this repository.
- [ ] Record the approved deployment ref, deployed revision, Booster release,
      and result in the environment release record.
- [ ] Keep a tested host backup/recovery path and a recorded last-known-good
      theme revision. Rehearse a rollback on staging before relying on it.

## Known external gates

RAN Booster `0.1.0-alpha.1` declares WordPress 7.0 and PHP 8.4 as its runtime
floor. The current Local acceptance site serves PHP 8.2.29, so it is not a
valid RAN Booster deployment-acceptance environment. Do not map or deploy this
theme through Booster until the deployment environment meets Booster's declared
runtime and its pinned-release, credential, package-mapping, staging-update,
and recovery checks have passed.
