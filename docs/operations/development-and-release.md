# Development and release

This runbook covers development and controlled deployment of the Protests and
Suffragettes theme. It assumes a Local WordPress environment for database and
browser checks, and RAN Booster for GitHub-only deployment. It never
stores a repository credential, access token, webhook secret, hosting URL, or
raw plugin export in this repository.

## Scope and ownership

**Audience:** theme developers and approved release operators.

**Authoritative sources:** `package.json` for portable commands, the current
theme code and WordPress records for ownership, and the approved RAN GitHub
Booster configuration in each deployment environment.

**Update this document when:** a package script, quality gate, Local runtime,
GitHub Booster release, deployment lane, rollback/recovery action, or release
responsibility changes.

**Validation:** run `pnpm check`. For a release, complete the relevant static,
WordPress, visual, and deployment-plugin gates in this document.

## Current deployment status

RAN Booster is the intended replacement for the retired deployment
path. Local currently has version `0.1.0-dev` active, but it has no configured
theme package, PAT, or webhook secret. Its installed checkout is not yet a
pinned/released source revision. It is therefore **not production-ready**.

Before it can become the deployment authority, confirm a pinned plugin release
and complete the acceptance checks below on staging. RocketCI remains a future
alternative and is not part of the current release procedure.

## Local setup

From the theme repository root:

```sh
pnpm install
pnpm compile:css
```

Use the project's Local WordPress site for browser and database-backed work.
The current compatibility baseline is Local's serving PHP 8.2.29—not the
shell's default `php`. Use the Local PHP binary explicitly for
runtime-representative WP-CLI checks:

```sh
"/Users/anachronistic/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php" /usr/local/bin/wp …
```

The Local wrapper may provide `wp-cli.yml`; it is an integration aid and is not
part of this theme's release configuration. Never use bare `php` or bare `wp`
as evidence of the serving runtime.

## Validation gates

Run portable checks from the theme root:

```sh
pnpm check
pnpm lint:php:syntax
```

During CSS work, compile the distributable output with `pnpm compile:css`.
Use `pnpm test:visual:fast` for normal visual iteration. Significant visual or
layout changes require the lean landing gate:

```sh
pnpm test:visual
```

Use the narrower visual lane that matches the work (`test:visual:templates`,
`test:visual:navigation`, `test:visual:shop`, `test:visual:ecwid`, or
`test:visual:emailoctopus`) while investigating. Do not update snapshots as a
first response to a failure: first classify it as a regression, an approved
visual change, or an environment/fixture issue.

Browser and database checks require Local services. Clear or account for any
page/cache-plugin cache before interpreting rendered output. If sandboxed
Chromium cannot start because of the macOS Mach port permission error, rerun
the same Playwright command with approved elevated local-process access.

### Database-backed release checks

Saved templates, parts, navigation, and synced patterns can be valid WordPress
data, not disposable cache. Before changing any database-backed structure,
capture the current state and review it rather than syncing or deleting it
blindly.

After the page-rebuild proof and an ownership decision, use these commands
against the intended Local database:

```sh
pnpm check:template-ownership
pnpm check:release-handoff
```

The first reports file/WordPress ownership conflicts. The second is a
non-mutating handoff probe that protects administrator-owned navigation and
social data. Do not use a repository file to overwrite those records without a
reviewed migration and rendered-route verification.

## RAN Booster acceptance gate

Complete this once for each deployment environment before registering the
theme. The plugin can manage GitHub-hosted themes and plugins, link an existing
package, install/update it through WordPress's normal upgrader path, and
optionally handle signed GitHub push webhooks.

1. Install a pinned, reviewable RAN Booster release; record its source
   repository, tag/commit, and WordPress/PHP test result. Do not use the
   current unpinned `0.1.0-dev` Local checkout as production evidence.
2. Create a GitHub fine-grained PAT restricted to only the required repository
   (initially `ProtestsAndSuffragettes/protestsandsuffragettes`) with the
   minimum Content read permission. Do not grant organisation-wide access,
   write access, or repository-listing permissions unless a reviewed workflow
   genuinely needs them.
3. Prefer host-managed constants `RAN_BOOSTER_GITHUB_TOKEN` and
   `RAN_BOOSTER_WEBHOOK_SECRET`. If the plugin sidecar is used instead, protect
   `wp-content/ran-booster-secrets.php` with the plugin's `0600` mode,
   exclude it from Git, backups that are not access-controlled, logs, and
   deploy archives, and verify the host cannot serve PHP source.
4. In **RAN Booster → GitHub**, save/verify credentials without placing
   them in WordPress options or this repository. The plugin keeps its PAT
   server-side and writes sidecar values atomically when constants are absent.
5. In **RAN Booster → Themes**, use **Link installed theme** for the
   existing stylesheet. Register the repository, approved branch/ref, and any
   required subdirectory. Begin with manual updates; leave Push-to-Deploy off.
6. On staging, deploy a known revision, verify the active theme and critical
   routes, then repeat the update to prove that the package mapping is
   repeatable. Record the deployed theme commit/version and plugin revision.

The GitHub repository picker may require broader token visibility than a
single-repository deployment. Use manual `owner/repository` entry when that
avoids unnecessary permissions.

## Release preparation

1. Confirm the release commit, current theme version, intended deployment ref,
   and GitHub Booster/plugin version:

    ```sh
    git status --short
    git rev-parse HEAD
    git describe --tags --always
    ```

2. Run the portable gates and visual lane(s) appropriate to the changed
   surfaces. Resolve PHP notices/warnings rendered in pages before treating a
   visual result as release evidence.
3. Run the Local database checks above when templates, parts, navigation,
   synced patterns, lifecycle code, or related render behaviour changed.
4. Record the Local WordPress/PHP baseline and the production
   WordPress/PHP target with the result. Production's higher PHP version is a
   required tested target; it does not raise the Local minimum or allow
   production-only features.
5. Ensure Release Please has generated or updated the release material. Do not
   invent historical release notes.
6. Obtain normal code review and choose the approved GitHub Booster deployment
   ref. Use a manual staging update until the signed webhook path has passed
   its separate acceptance test.

## Deploy with RAN Booster

Only an approved release operator may deploy. The plugin is GitHub-only; it is
not a general Git-target service.

### Manual staging deployment

1. Confirm the installed theme package is linked to the approved repository
   and intended branch/ref in **RAN Booster → Themes**.
2. Use the theme's update action to fetch the approved revision.
3. Confirm the active theme, deployed commit/version, critical routes, and
   absence of PHP warnings/notices. Record the result in the access-controlled
   environment release record.
4. Stop and investigate if the revision, active theme, or rendered output does
   not match approval.

### Signed Push-to-Deploy (after staging acceptance)

The webhook endpoint is:

```text
/wp-json/ran-booster/v1/github/webhook
```

Configure GitHub to send only `push` events. The plugin requires an
`X-Hub-Signature-256` HMAC-SHA256 signature, checks the configured repository
and branch, ignores deleted refs/tags and unmatched packages, deduplicates
deliveries for 24 hours, and takes a ten-minute deployment lock. It deploys the
webhook's `after` commit SHA rather than resolving a later branch tip.

Keep Push-to-Deploy disabled in production until a staging delivery has proven
all of those controls and the release record captures the actual deployed SHA.

### Production deployment

1. Deploy only the revision that passed staging and is recorded in the release
   record.
2. Use the approved manual update or the accepted signed webhook route.
3. Confirm the active theme, deployed version/revision, critical routes, and
   error-display policy. Record the result without exposing deployment
   configuration or secrets.

### Recovery and rollback

GitHub Booster does not provide a documented automatic rollback history. Keep a
tested host backup/recovery path and a recorded last-known-good theme revision.
Before relying on a manual ref change for rollback, prove on staging that the
plugin resolves the intended known-good artifact and leaves the site active.
Never restore database templates, navigation, synced patterns, or content as
part of a code rollback unless a separate reviewed recovery plan calls for it.

## Handoff checklist

- A new developer can clone the canonical theme repository and locate this
  runbook from its root README.
- The release record identifies the deployed commit, version/ref, WordPress,
  PHP, environment, validation results, GitHub Booster version, and approved
  operator.
- GitHub Booster's package mapping and secret source are verified in the
  relevant environment without copying credentials or raw configuration into
  Git.
- Template ownership and administrator-owned data have been reviewed where
  release work affects database-backed structures.
- The developer can explain manual deployment, signed deployment, and host
  recovery without relying on historical deployment tooling.
