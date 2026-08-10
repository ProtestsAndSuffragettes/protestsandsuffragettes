# Retained Render Bridge Remediation Plan

Created: 2026-07-07.

All paths are relative to the project root.

## Purpose

Queue narrow remediation work from the retained render-bridge audit without
turning it into a broad PHP hook removal pass.

The audit found that most retained runtime bridges are legitimate ownership.
The useful remediation is targeted: settle one navigation markup-surgery risk,
retire one temporary compatibility bridge if the DB is clean, make Navigation
editor controls honest, and add focused tests around retained bridge contracts.

Accepted decisions:

- Navigation submenu overview links are owned by saved Navigation content. The
  PHP injection bridge was removed after the saved-navigation trial proved
  stable; rollback should restore the backed-up Navigation record or fixture.
- Legacy Back-to-News cleanup should follow the recommended scan/fix/delete
  path.
- Navigation overlay and CTA render guards should stay while editor controls
  are made honest, with code comments for future developers.

## Related Work

- Audit plan:
  `docs/jobs/2026-07-07-retained-render-bridge-audit-plan.md`
- Audit inventory:
  `docs/jobs/2026-07-07-retained-render-bridge-audit-inventory.md`
- Render-filter/template remediation baseline:
  `docs/jobs/2026-07-06-render-filter-template-remediation-plan.md`
- Core Navigation drawer migration:
  `3b7aa2260 refactor(nav): use core responsive drawer`

## Dex Tracking

- Parent: `u65cklke` - Remediate retained render bridge follow-up risks
- Child: `qcyanp8p` - Resolve navigation submenu overview ownership
- Child: `ozdezqa6` - Retire legacy single-post Back-to-News bridge safely
- Child: `x0812o5j` - Make navigation overlay and CTA controls honest
- Child: `2gsu9ryb` - Add focused tests for retained render bridges

## Non-Goals

- Do not remove retained bridges just to reduce hook count.
- Do not reopen completed Shop surface ownership.
- Do not redesign the core Navigation drawer.
- Do not advance Herstories validation, namespace cutover, or dynamic-block
  conversion until client approval is received for `h3rs0t00`.
- Do not mutate DB-backed navigation, templates, synced blocks, or content
  without backup, dry run, apply, and post-apply verification.
- Do not refresh visual baselines before drift is classified and accepted.

## Execution Plan

### 1. Add Focused Tests For Retained Render Bridges

Dex: `2gsu9ryb`

Risk:

Several retained bridges are legitimate but under-tested. They should be
protected by behavior tests before later cleanup attempts touch them.

Scope:

- `pnsRefSlug` / `ref` resolver:
  - missing refs;
  - primary, footer, and banner Navigation refs;
  - synced block refs.
- Query pagination:
  - first page;
  - middle page;
  - last page.
- No-thumbnail fallback:
  - archive cards;
  - search cards.
- Search routes:
  - enabled route behavior;
  - disabled route behavior;
  - mixed post/page result date metadata.
- Template reveal rollback:
  - filter or constant disables the body class.
- Planned remediation bridges:
  - primary Navigation saved content contains the expected parent overview
    links without relying on render-time submenu injection;
  - legacy Back-to-News suppression prevents duplicate single-post navigation
    until the bridge is retired.

Acceptance:

- Tests assert behavior, not incidental class churn.
- Coverage does not advance `h3rs0t00` Herstories approval work.
- The retained bridges become safer to refactor later.

Validation:

Use the narrowest matching lanes:

```bash
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:shop
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:ecwid
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:emailoctopus
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
```

### 2. Resolve Navigation Submenu Overview Ownership

Dex: `qcyanp8p`

Risk:

The former PHP submenu overview bridge injected clickable parent overview links
into rendered submenu markup. Saved Navigation content now owns those links so
core Navigation output is not patched at render time.

Scope:

- Prove whether current WordPress Navigation content can express the overview
  item without PHP injection.
- If saved navigation content is the correct owner, plan DB-backed navigation
  mutation with backup, dry run, apply, post-apply verification, and rollback.
- After the saved-navigation trial proves stable, remove the PHP bridge rather
  than keeping a second owner for the same submenu overview behavior.
- If PHP remains the correct owner, keep the bridge but add focused tests and
  comments that name the reason.
- Keep visual restyling out of this task.

Acceptance:

- Desktop submenu overview behavior is either source-owned or explicitly
  retained as a named bridge.
- The ownership decision names one of: saved Navigation content/model,
  dedicated component, or intentionally retained named PHP bridge.
- If saved Navigation ownership is accepted, rollback is available through the
  backed-up Navigation record or `navigation/primary.html` fixture.
- Core drawer behavior still works.
- Generic Navigation blocks are not accidentally changed.

Validation:

```bash
wp eval-file app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-retained-render-bridges.php
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
```

### 3. Retire Legacy Single-Post Back-To-News Bridge Safely

Dex: `ozdezqa6`

Risk:

`pns_standalone_remove_legacy_single_post_back_to_news_button_block()` removes
legacy inline "Back to news" buttons from rendered single post content. This is
a temporary compatibility bridge and should not live forever.

Scope:

- Scan DB and source content for legacy inline Back-to-News button blocks.
- Include `wp_posts.post_content` for non-revision posts/pages, DB-backed
  `wp_template`, `wp_template_part`, `wp_block`, and `wp_navigation` rows, plus
  theme source templates, patterns, and synced-pattern fixtures.
- Report matches before mutation.
- If live legacy matches are found, fix the saved content through a backed-up,
  dry-run, apply, and post-apply scan flow so the bridge can be deleted.
- If the scan finds no live non-revision matches requiring suppression, or the
  cleanup pass makes the post-apply scan clean, remove the render bridge with
  focused tests.
- If content is not clean, keep the bridge and document the exact cleanup path.
- Do not remove the bridge before the scan.

Acceptance:

- No duplicate single-post navigation.
- No saved content mutation occurs without backup, dry run, apply, and
  post-apply scan.
- The bridge is either removed safely or retained with a clear deletion gate.

Validation:

```bash
wp eval-file app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-retained-render-bridges.php
php app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-block-templates.php
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:templates
```

### 4. Make Navigation Overlay And CTA Controls Honest

Dex: `x0812o5j`

Risk:

The render guards
`pns_standalone_strip_navigation_overlay_template_block_data()` and
`pns_standalone_normalize_cta_navigation_block_data()` prevent unsupported
frontend output, but the editor can still imply unsupported Navigation controls
are meaningful in primary and CTA contexts.

Scope:

- Scope unsupported overlay/icon/color controls for primary Navigation and
  banner CTA Navigation contexts.
- Preserve generic Navigation controls.
- Keep runtime guards until editor controls prove honest.
- Add short code comments near the runtime guards explaining that they remain
  intentional until primary and CTA editor controls can no longer save
  unsupported attrs.
- Do not redesign the drawer.
- Do not remove support for ordinary author-created Navigation blocks.

Acceptance:

- Primary and CTA unsupported attrs cannot be saved through normal editing.
- Generic Navigation overlay/icon/color attrs survive when authors set them.
- CTA remains inline.
- Core drawer behavior remains accepted.

Validation:

```bash
wp eval-file app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/validate-retained-render-bridges.php
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:editor
pnpm --dir app/public/wp-content/themes/protestsandsuffragettes-standalone test:visual:navigation
```

## Recommended Order

1. `2gsu9ryb` - Add focused tests for retained render bridges.
2. `qcyanp8p` - Resolve navigation submenu overview ownership.
3. `ozdezqa6` - Retire legacy single-post Back-to-News bridge safely.
4. `x0812o5j` - Make navigation overlay and CTA controls honest.

The coverage task comes first because it protects the bridge behavior before
ownership changes. The overlay/CTA editor-control task should wait until the
accepted core drawer work has settled, meaning the navigation visual lane has
passed for the accepted drawer behavior and there is no open drawer follow-up
blocking editor-control work.

## Done When

- Every child task has either landed or been explicitly deferred with a current
  reason.
- No broad render-bridge removal is attempted without source/DB evidence.
- Herstories client-approval work remains parked outside this queue.
