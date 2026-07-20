# Protests and Suffragettes theme

This is the standalone block theme for the Protests and Suffragettes client
site. It is a maintained client theme, not a general-purpose public theme.

The canonical source is this repository:

- Repository: <https://github.com/ProtestsAndSuffragettes/protestsandsuffragettes>
- Default branch: `main`
- WordPress installation path: `wp-content/themes/protestsandsuffragettes/`

The former `protestsandsuffragettes-standalone` path is historical. A Local
WordPress checkout may contain this repository as an integration environment,
but it is not the canonical home for theme documentation or releases.

## Start here

1. Clone this repository into the WordPress themes directory at the path above.
2. Activate **Protests and Suffragettes** in WordPress.
3. Install the development dependencies with `pnpm install`.
4. Read the [theme handbook](docs/README.md), then follow the
   [development and release runbook](docs/operations/development-and-release.md).

The active repository revision, release version, and deployment target must be
recorded for every release. Do not rely on this document as evidence of which
commit a running site uses:

```sh
git rev-parse HEAD
git describe --tags --always
```

`style.css` is the WordPress-visible version source. Release Please updates it
and maintains the intended release history; RAN Booster is the selected
GitHub-only deployment basis. Its beta validation gate and deployment procedure
are deliberately separate from release-note generation.

## What belongs here

The theme owns its templates, template parts, regular block patterns, theme
CSS and JavaScript, lifecycle code, navigation fixtures, and synced-pattern
fixtures. It also contains narrow adapters for client-site functionality that
has an explicit theme presentation contract.

WordPress administrator data remains administrator-owned. In particular, do
not overwrite saved navigation, footer social links, ordinary page/post
content, or synced patterns merely because a similarly named repository file
exists. The template-ownership and release-handoff checks are the required
review surfaces before changing database-backed structures.

The separately maintained client plugins and external services are integration
boundaries, not theme source. This theme must not be used as a place to copy
plugin implementation or private deployment configuration.

## Development checks

Run commands from this repository root:

```sh
pnpm format:check
pnpm audit:css-assets
pnpm lint:css
pnpm lint:php:syntax
```

`pnpm test:visual:fast` is the normal visual iteration gate. Significant visual
changes must also pass the lean `pnpm test:visual` gate. The browser and
database-backed checks need a working Local WordPress environment; see the
[runbook](docs/operations/development-and-release.md) for the required order
and runtime caveats.

## Support boundary

This is a client-site theme, not a general-purpose hosting product. The
supported baseline is WordPress 6.6 or later with PHP 8.2 or later; the current
accepted environment is WordPress 7.0.2 and PHP 8.2.29. The theme has been
tested through WordPress 7.0.

Deploy only to a maintained client environment at or above that baseline. For
each release, record the serving WordPress/PHP versions and the validation
result in the access-controlled release record. The enduring policy and its
current Local evidence are in the
[compatibility policy](docs/operations/compatibility-evidence.md).

## Documentation maintenance

**Audience:** theme developers and release operators.

**Authoritative sources:** the current implementation, `package.json`,
WordPress state inspected through the documented checks, and approved client
deployment settings.

**Update this README when:** the repository/install boundary, release process,
runtime support policy, or entry-point commands change.

**Validation:** `pnpm format:check`; use the relevant static, WordPress, and
visual gate described in the handbook for any accompanying code change.
