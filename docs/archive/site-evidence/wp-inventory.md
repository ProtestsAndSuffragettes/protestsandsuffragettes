# WordPress Inventory

Initial inventory captured on 2026-06-22.

All paths are relative to the project root.

## Runtime

- Local site URL: `http://localhost:10008`
- WordPress path: `app/public`
- WordPress version: `7.0`
- WP-CLI project config: `wp-cli.yml`

## Theme

- Active stylesheet: `protestsandsuffragettes`
- Parent template: `estory`
- Active child theme path: `app/public/wp-content/themes/protestsandsuffragettes`
- Parent theme path: `app/public/wp-content/themes/estory`

## Active Plugins

| Plugin | Version |
| --- | --- |
| `admin-bar-user-switching` | `1.4` |
| `akismet` | `5.7` |
| `all-in-one-wp-security-and-firewall` | `5.4.9` |
| `animations-for-blocks` | `1.2.6` |
| `blockmeister` | `3.1.12` |
| `cookie-law-info` | `3.5.1` |
| `duplicate-page` | `4.5.9` |
| `dynamic-year-block` | `1.0.0` |
| `ecwid-shopping-cart` | `7.0.8` |
| `emailoctopus` | `3.1.10` |
| `jetpack` | `15.9` |
| `jetpack-boost` | `4.6.1` |
| `mcp-adapter` | `0.5.0` |
| `redirection` | `5.8.0` |
| `regenerate-thumbnails` | `3.1.6` |
| `safe-svg` | `2.4.0` |
| `simple-page-ordering` | `2.8.0` |
| `updraftplus` | `1.26.5` |
| `user-switching` | `1.12.0` |
| `wp-cli-login-server` | `1.5` |
| `wp-fastest-cache` | `1.4.9` |

## Repo Scope

The repository intentionally tracks only project-owned files:

- `AGENTS.md`
- `wp-cli.yml`
- `docs/`
- `app/public/wp-content/themes/protestsandsuffragettes/`

WordPress core, uploads, caches, logs, Local configuration, database state, parent themes, and third-party plugins are intentionally untracked unless a future task proves a specific file is project-owned custom code.
