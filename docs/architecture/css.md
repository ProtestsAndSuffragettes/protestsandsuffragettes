# CSS architecture

The theme's authored CSS lives in `styles/`. `style.css` is WordPress metadata
only. Do not edit the compiled files in `styles/dist/` by hand.

## Sources and delivery

| Surface          | Authoring entry point      | Delivered output                                                     |
| ---------------- | -------------------------- | -------------------------------------------------------------------- |
| Frontend         | `styles/frontend.css`      | `styles/dist/frontend.min.css`                                       |
| Block editor     | `styles/editor.css`        | `styles/dist/editor.min.css`                                         |
| Editor canvas    | `styles/editor-canvas.css` | Enqueued by the theme editor setup                                   |
| Block-scoped CSS | `styles/blocks/*.css`      | Registered by `inc/block-styles.php` when the matching block renders |

`inc/assets.php` prefers compiled CSS when it exists and falls back to the
authored entry point. Compile with `pnpm compile:css` after a stylesheet change.

## Ownership layers

The source tree groups the theme's CSS by owner rather than by an imagined
generic framework:

- `shared/` — fonts, settings/tokens, and declared cascade-layer order;
- `base/` — element defaults and form defaults;
- `layout/` — shared page-shell layout rules;
- `blocks/` — Core/Jetpack block contracts and editor-specific support;
- `components/` — named PNS components such as header, hero, cards, buttons,
  section surfaces, and post metadata;
- `page-types/` — route/content-family behaviour such as content rhythm,
  Herstories, layout stability, and shop surfaces;
- `utilities/` — intentionally small opt-in helpers; and
- `vendor-overrides/` — scoped adapters for Ecwid and EmailOctopus.

Project-owned structural blocks belong in the appropriate plugin. The theme
may supply tokens and presentation adapters, but it should not duplicate plugin
rendering code. Third-party runtime output remains an adapter boundary, not an
invitation for broad global selectors.

## Cascade policy

WordPress Global Styles, Core block support, editor runtime styles, and vendor
plugins can be unlayered or inline. Cascade layers are therefore useful for
low-conflict theme defaults, not a guarantee that every rule should be layered.
When an unlayered or `!important` bridge is retained, keep it next to its
actual owner with a comment that records the external pressure and removal
condition.

Allowed `!important` cases are narrow: Core inline block-support output,
unavoidable Core CSS, third-party vendor output, or an explicit local utility.
Avoid a generic priority-override pile; move a rule to its real component,
block, layout, or vendor owner instead.

## Change and validation workflow

1. Inspect the current working tree before touching styles.
2. Change one ownership concern at a time under `styles/`.
3. Run `pnpm compile:css`.
4. Run the focused visual lane for the changed route or component. Use
   `pnpm test:visual:fast` for normal iteration and `pnpm test:visual` for a
   significant visual landing change.
5. Run `pnpm lint:css`; use `pnpm audit:css-assets` to detect duplicate/dormant
   delivery paths.
6. Do not refresh a snapshot until a visual difference is classified and
   approved.

The production site may be used only as a visual-language reference. Automated
regression targets the Local WordPress site.

## Maintenance contract

**Audience:** theme developers.

**Authoritative sources:** `styles/`, `inc/assets.php`,
`inc/block-styles.php`, `package.json`, and rendered Local verification.

**Update this document when:** an entry point, output path, CSS ownership area,
block delivery path, or allowed bridge policy changes.

**Validation:** `pnpm format:check`, `pnpm lint:css`, `pnpm audit:css-assets`,
and the relevant visual lane.
