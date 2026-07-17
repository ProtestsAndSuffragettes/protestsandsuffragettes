# Compatibility evidence worksheet

Use this worksheet to record the environment facts needed before changing the
theme's WordPress or PHP metadata. It is evidence capture, not a support
policy: `style.css` and `readme.txt` remain unchanged until the listed checks
pass.

## Scope and ownership

**Audience:** theme developers and approved release operators.

**Authoritative sources:** the serving Local runtime, the client staging or
production runtime, theme source requirements, and recorded validation output.

**Update this document when:** WordPress, PHP, a required plugin API, or
block-editor markup changes in a supported environment.

**Validation:** run the relevant checks from the
[development and release runbook](development-and-release.md) and retain the
result in the environment's access-controlled release record.

## Current known boundary

| Item                    | Current position                                                            | Evidence still needed                                                                                     |
| ----------------------- | --------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------- |
| Local serving PHP       | 8.2.29 is the intended declared floor.                                      | Run the release checks under Local's serving PHP binary for the release candidate.                        |
| Production PHP          | A higher client-host version is a separate tested target.                   | Record exact version and a dated passing production/staging result.                                       |
| WordPress source floor  | 6.6, because the theme uses `theme.json` schema version 3 and related APIs. | Confirm the target release has no lower unsupported runtime path.                                         |
| Tested WordPress target | Must follow the client host, not an arbitrary broad matrix.                 | Record the exact production WordPress version and passing checks.                                         |
| Declared metadata       | Current headers must not be treated as reliable support claims.             | Update `style.css`, `readme.txt`, README, and the final policy together after this worksheet is complete. |

## Release-candidate matrix

Complete one row per environment for each release candidate.

| Environment | WordPress | PHP    | Theme commit/version | Required plugins verified | Static checks | Visual/routes checked | Date/operator | Result |
| ----------- | --------- | ------ | -------------------- | ------------------------- | ------------- | --------------------- | ------------- | ------ |
| Local       |           | 8.2.29 |                      |                           |               |                       |               |        |
| Staging     |           |        |                      |                           |               |                       |               |        |
| Production  |           |        |                      |                           |               |                       |               |        |

Do not paste PATs, webhook secrets, host credentials, database dumps, or
private URLs into this worksheet.

## Evidence collection

1. Record the active theme commit and version.
2. Confirm WordPress and PHP from the serving runtime. For Local, use the
   explicit Local PHP binary from the development/release runbook rather than
   the shell default.
3. Run `pnpm check`, the appropriate visual lane, and the database-backed
   release checks when the change affects templates, parts, navigation, synced
   patterns, or lifecycle code.
4. Confirm the required plugin bundle through Site Health and record material
   plugin-version changes that affect blocks or frontend output.
5. Record the exact result in the environment's access-controlled release
   record; this worksheet should retain only the non-secret summary.

## Metadata change gate

Only update the public metadata when all are true:

- Local PHP 8.2.29 passes the release-candidate checks.
- The client staging/production WordPress and PHP versions are recorded and
  pass their required checks.
- The declared WordPress minimum is no lower than the APIs used by the current
  theme source.
- The declared `Tested up to` value is the client-host WordPress target with
  current evidence.
- The release notes identify the support-policy change without claiming a
  general public compatibility matrix.

After the gate passes, replace this worksheet with the concise enduring
compatibility policy and retain the dated release record as the evidence.
