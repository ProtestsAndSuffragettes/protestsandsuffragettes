# CSS architecture

This is the canonical CSS guide for the `protestsandsuffragettes` theme.
Author styles in `styles/`; `style.css` contains WordPress metadata only. Do
not edit generated files in `styles/dist/` by hand.

## Sources and delivery

| Surface          | Authoring entry point      | Delivered output                                                     |
| ---------------- | -------------------------- | -------------------------------------------------------------------- |
| Frontend         | `styles/frontend.css`      | `styles/dist/frontend.min.css`                                       |
| Block editor     | `styles/editor.css`        | `styles/dist/editor.min.css`                                         |
| Editor canvas    | `styles/editor-canvas.css` | Enqueued by the theme editor setup                                   |
| Block-scoped CSS | `styles/blocks/*.css`      | Registered by `inc/block-styles.php` when the matching block renders |

`inc/assets.php` prefers compiled CSS when it exists and falls back to the
authored entry point. Compile with `pnpm compile:css` after stylesheet changes.
The compiled frontend bundle is measured in the [CSS metrics
baseline](../reference/css-metrics-baseline.md).

## Ownership map

The source tree groups CSS by the system or component that owns the behaviour:

- `shared/` — fonts, tokens, and cascade-layer declarations;
- `base/` — plain element and form defaults;
- `layout/` — shared site-shell and width primitives;
- `blocks/` — Core/Jetpack block contracts and editor-specific support;
- `components/` — named theme components such as header, hero, cards, buttons,
  surfaces, and post metadata;
- `page-types/` — route/content-family behaviour such as Herstories, content
  rhythm, layout stability, and shop surfaces;
- `utilities/` — intentionally small opt-in authoring helpers; and
- `vendor-overrides/` — scoped Ecwid and EmailOctopus adapters.

Project-owned structural blocks belong in their plugin. The theme supplies
tokens, composition, and presentation adapters; it does not duplicate plugin
rendering. Third-party output remains vendor-owned and must not be targeted by
broad global selectors.

Use `theme.json` first for WordPress-supported typography, palette, layout,
spacing, and block settings. Use CSS only for presentation that the theme
actually owns or for a documented compatibility boundary.

## Cascade policy

WordPress Global Styles, Core block support, editor runtime styles, and vendor
plugins can arrive unlayered or inline. Layers are therefore useful for
low-conflict theme defaults, not a guarantee that every rule should be layered.

Layer low-conflict settings, base defaults, block defaults, reusable component
defaults, and explicit utilities. Keep site-shell layout, Core conflict tails,
editor parity shims, vendor adapters, and project-block compatibility rules
unlayered when they must beat generated WordPress or vendor output.

Allowed `!important` cases are narrow: Core inline block-support output,
unavoidable Core CSS, third-party vendor output, or an explicit local utility.
Avoid a generic priority pile. Move a rule to its real component, block,
layout, editor, or vendor owner instead. Every retained bridge must document
the external pressure, current owner, and removal condition beside the rule.

True block defaults should prefer `wp_enqueue_block_style()` through
`inc/block-styles.php`. Do not duplicate a default in both a native block
stylesheet and the global bundle unless the temporary duplication is recorded.
Contextual page and component composition remains theme-owned CSS.

## Change and validation workflow

1. Inspect the working tree and identify the actual CSS owner.
2. Change one ownership concern at a time under `styles/`.
3. Run `pnpm compile:css` and `pnpm lint:css`.
4. Run the focused visual lane for the changed route or component. Use
   `pnpm test:visual:fast` for normal iteration and `pnpm test:visual` for a
   significant visual landing change.
5. Run `pnpm audit:css-assets` when delivery paths or block styles change.
6. Use the Local WordPress site for automated regression. Production is only a
   visual-language reference; do not point the suite at production.

Do not refresh snapshots until the visual difference is classified and
approved. Selector removal requires content/template searches and rendered
coverage; CSS metrics do not prove that a selector is unused.

## Maintenance contract

**Audience:** theme developers and reviewers.

**Authoritative sources:** `styles/`, `theme.json`, `inc/assets.php`,
`inc/block-styles.php`, `package.json`, and rendered Local verification.

**Update this document when:** an entry point, output path, CSS ownership area,
block delivery path, or bridge policy changes.

**Validation:** `pnpm format:check`, `pnpm lint:css`, `pnpm audit:css-assets`,
and the relevant visual lane.
