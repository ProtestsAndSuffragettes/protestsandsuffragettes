# Compatibility and support policy

The Protests and Suffragettes theme is supported as a client-site theme, not as
a general-purpose theme for arbitrary hosting environments.

Its declared compatibility policy is:

| Metadata            | Policy        |
| ------------------- | ------------- |
| `Requires at least` | WordPress 6.6 |
| `Tested up to`      | WordPress 7.0 |
| `Requires PHP`      | PHP 8.2       |

The supported deployment boundary is a maintained client environment at or
above those floors. A release operator must record the exact serving
WordPress/PHP versions and passing validation result for the deployed
environment. This repository does not promise a broad third-party-hosting
matrix.

## Scope and ownership

**Audience:** theme developers and approved release operators.

**Authoritative sources:** the serving Local runtime, the client staging or
production runtime, theme source requirements, and recorded validation output.

**Update this document when:** the declared WordPress/PHP policy, a required
plugin API, or block-editor markup changes in a supported environment.

**Validation:** run the relevant checks from the
[development and release runbook](development-and-release.md) and retain the
result in the environment's access-controlled release record.

## Current evidence

| Environment                  | WordPress | PHP    | Theme | Evidence                                                                                                                                                              |
| ---------------------------- | --------- | ------ | ----- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Local acceptance environment | 7.0.2     | 8.2.29 | 0.3.0 | Active theme and required plugin bundle verified through Local's serving PHP runtime; `pnpm check`, PHP syntax, and desktop/mobile visual smoke passed on 2026-07-20. |

The WordPress 6.6 floor is source-led: the theme uses `theme.json` schema
version 3 and related block-theme APIs. WordPress 7.0.2 is the current tested
Local client target, so the public header uses the conventional `7.0` value.

## Release record

Record one row per environment for each release candidate in the
access-controlled release record. Do not put credentials, private hostnames,
or database exports in this repository.

| Environment | WordPress | PHP    | Theme commit/version | Required plugins verified | Static checks | Visual/routes checked | Date/operator | Result |
| ----------- | --------- | ------ | -------------------- | ------------------------- | ------------- | --------------------- | ------------- | ------ |
| Local       |           | 8.2.29 |                      |                           |               |                       |               |        |
| Staging     |           |        |                      |                           |               |                       |               |        |
| Production  |           |        |                      |                           |               |                       |               |        |

Do not paste PATs, webhook secrets, host credentials, database dumps, or
private URLs into this worksheet.

## Release validation

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
   record; this policy retains only the non-secret Local evidence.

If a release needs a lower WordPress/PHP floor, or a higher `Tested up to`
value, establish that evidence first and update `style.css`, `readme.txt`,
this policy, and the release record together.
