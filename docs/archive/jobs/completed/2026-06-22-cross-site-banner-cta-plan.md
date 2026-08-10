# Cross-Site Banner CTA Plan

Planning document created on 2026-06-22.

All paths are relative to the project root.

## 2026-06-25 Ownership Update

This plan's original recommendation to use a synced pattern for the banner has
been superseded by the template/component extraction plan:

```text
docs/jobs/2026-06-24-template-component-extraction-plan.md
```

Current implementation stance:

- Banner placement/wrapper is code-owned in the standalone theme header.
- CTA labels and URLs are editable through `wp_navigation` `5259`,
  `Banner CTA Nav`.
- Banner CSS is theme-owned in
  `app/public/wp-content/themes/protestsandsuffragettes-standalone/styles/components/cross-site-banner-cta.css`.

## Goal

Add a cross-site call-to-action banner directly under the header navigation. The
client needs to update the banner text and links occasionally, so the first
implementation should prioritize editor convenience while keeping a clear path
to code formalization later.

The requested visual direction is a full-width yellow banner with red linked CTA
text and a cut/notched lower edge, matching the supplied screenshot reference:

```text
/Users/anachronistic/Desktop/Screenshot 2026-06-16 at 14.06.06.png
```

## Current Baseline

The site header is a database-backed Site Editor template part:

| Object | Type | ID | Slug | Title | Status | Last modified |
| --- | --- | ---: | --- | --- | --- | --- |
| Header | `wp_template_part` | `1027` | `header` | `Header` | `publish` | `2023-05-05 09:46:29` |

The current `Header` template part contains a white wrapper, the site logo, and
the `Top Nav` Navigation block:

```html
<!-- wp:site-logo {"width":150,"shouldSyncIcon":false,"className":"pands-logo"} /-->
<!-- wp:navigation {"ref":1035,"layout":{"type":"flex","orientation":"horizontal","flexWrap":"wrap","justifyContent":"right"},"style":{"spacing":{"blockGap":"1.6em"}}} /-->
```

There is no existing synced pattern or reusable block for this banner. Existing
`wp_block` items are `Contact Form`, `Connect Social`, `Read all about it`,
`Shop Intro`, `Shop Intro (Using Ecwid Blocks)`, and
`Contact Form (original) (Copy)`.

## Recommended Architecture

Use a native synced pattern / reusable block for the banner content, inserted
below the Navigation block in `wp_template_part #1027`.

This means:

- Banner placement lives in the Header template part.
- Banner content lives in a `wp_block` synced pattern, for example
  `Cross Site Banner CTA`.
- Banner styling lives in child-theme CSS.
- Editors can update text and links in WordPress without touching code.

This is deliberately not a custom registered block type in Phase 1. A custom
block would add build and maintenance cost without solving the main client need,
which is occasional editing of simple text/links.

## Source Of Truth Decision

| Concern | Phase 1 owner | Later code owner |
| --- | --- | --- |
| Banner placement under nav | `wp_template_part #1027` | Child-theme `parts/header.html` or a template-part migration if header governance moves to code |
| Banner editable content | Native `wp_block` synced pattern | Keep as `wp_block` if client update convenience remains important |
| Banner visual styling | Child-theme CSS | Child-theme CSS |
| Banner starter markup | Created once in editor or WP-CLI | Optional child-theme pattern in `patterns/` if a canonical starter is needed |

## Proposed Markup Shape

Use core blocks plus a stable custom class. Avoid inline styles except where the
editor stores standard block attributes.

Conceptual block markup:

```html
<!-- wp:group {"className":"pns-cross-site-banner-cta","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"}} -->
<div class="wp-block-group pns-cross-site-banner-cta">
	<!-- wp:paragraph {"className":"pns-cross-site-banner-cta__item"} -->
	<p class="pns-cross-site-banner-cta__item"><a href="/shop/">Shop P&amp;S</a></p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"className":"pns-cross-site-banner-cta__item"} -->
	<p class="pns-cross-site-banner-cta__item"><a href="/support-us/">Support P&amp;S</a></p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
```

Use real destination URLs once confirmed. The screenshot labels are currently
`SHOP P&S` and `SUPPORT P&S`; the rough notes also mention `Shop for goodies`
and `Support Us, Get SWAG`.

## CSS Direction

The supplied rough CSS should be cleaned up before implementation:

- Use `space-around`, not `space-arround`.
- Use `#fff`, not `#ffff`.
- Avoid invalid/stray inline declarations such as `margin: 1` and escaped
  fragments from copied editor HTML.
- Prefer child-theme CSS over inline `style` attributes.

Suggested starting CSS:

```css
.pns-cross-site-banner-cta {
	width: 100%;
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
	align-items: baseline;
	justify-content: center;
	gap: 1rem 2rem;
	margin: 0;
	padding: 1rem var(--wp--preset--spacing--40) 1.35rem;
	background: #eec414;
	color: var(--wp--preset--color--red);
	clip-path: polygon(0 0, 100% 0, 100% 85%, 50% 100%, 0 85%);
	font-family: var(--wp--preset--font-family--libre-franklin);
}

.pns-cross-site-banner-cta__item {
	margin: 0;
	font-size: 1.25rem;
	font-weight: 700;
	line-height: 1.2;
	text-transform: uppercase;
}

.pns-cross-site-banner-cta a {
	color: var(--wp--preset--color--red);
	text-decoration: underline;
	text-underline-offset: 0.12em;
}

.pns-cross-site-banner-cta a:focus-visible {
	outline: 3px solid var(--wp--preset--color--red);
	outline-offset: 0.25rem;
}
```

The final selector location should follow the active CSS structure in the child
theme. If the current split CSS architecture is retained, prefer a component
file such as:

```text
app/public/wp-content/themes/protestsandsuffragettes/styles/components/cross-site-banner-cta.css
```

and import it through the existing component/index entrypoint.

## Phase 0 - Confirm Content And Destinations

Decisions to record:

- Final CTA labels.
- Final CTA URLs.
- Whether the banner is always visible, campaign-based, or temporarily hidden
  when no campaign is active.
- Whether editors need two links only, or an arbitrary number of CTA links.
- Whether the banner should appear above or below the logo/nav wrapper on mobile.
- Whether the notched edge should point down into the hero on all pages, not only
  the home page.

Done when:

- Copy and URLs are confirmed.
- The edit model is agreed: synced pattern in Phase 1.
- Any campaign on/off requirement is documented.

## Phase 1 - Create The Editable Synced Pattern

Create a native synced pattern / reusable block named
`Cross Site Banner CTA`.

Implementation options:

- Use the WordPress editor UI for the most client-aligned workflow.
- Or use WP-CLI to create a `wp_block` post if markup is prepared and reviewed.

Required content:

- A wrapper Group block with class `pns-cross-site-banner-cta`.
- One child paragraph or heading per CTA link, each with class
  `pns-cross-site-banner-cta__item`.
- Real links and accessible link text.

Acceptance checks:

- `wp post list --post_type=wp_block` shows the new synced pattern.
- The synced pattern can be edited from WordPress admin.
- The content uses core blocks only, unless a later decision requires plugin
  blocks.

## Phase 2 - Insert The Banner Below Header Navigation

Insert the synced pattern into `wp_template_part #1027` immediately after the
current logo/navigation flex row.

Target structure:

```text
Header wrapper
- Logo + Navigation row
- Cross Site Banner CTA synced pattern reference
```

Acceptance checks:

- The banner appears below the nav on all templates that use the active Header
  template part.
- The banner is visible on the home page and at least two interior pages.
- Header spacing still works with the logo overlapping/near the yellow band as
  shown in the screenshot.
- The Navigation block still opens and closes correctly on mobile.

Rollback:

- Remove the `core/block` reference from `wp_template_part #1027`.
- Keep the `wp_block` synced pattern available for reinsertion unless the banner
  is abandoned.

## Phase 3 - Add Child-Theme Styling

Add the banner CSS in the child theme. Avoid editing parent theme files and avoid
inline style sprawl in the Header template part.

Implementation notes:

- Scope styles to `.pns-cross-site-banner-cta`.
- Verify desktop, tablet, and mobile wrapping.
- Keep the clip-path notch from interfering with following content.
- Confirm contrast for red-on-yellow link text.
- Preserve visible focus states.

Acceptance checks:

- Links remain readable and tappable at mobile widths.
- The banner does not overlap the nav, hero, cookie banner, or page content.
- Text wraps cleanly if CTA labels are longer than the screenshot labels.
- No horizontal overflow is introduced.

## Phase 4 - Editor Handoff

Document the editor workflow for the client:

- Where to edit the synced pattern.
- Which text can be changed.
- Which links can be changed.
- What not to change, such as the wrapper class names if styling depends on
  them.

Optional guardrails:

- Keep the synced pattern name stable: `Cross Site Banner CTA`.
- Add a short internal note in project docs if the banner is campaign-driven.
- If editors repeatedly break classes, consider moving more structure into a
  coded pattern or a small custom block later.

Acceptance checks:

- A non-developer can find and edit the banner content.
- The update affects all pages after one edit.
- The implementation source of truth is clear in the audit/docs.

## Phase 5 - Optional Code Formalization

If the banner becomes a permanent design system element, formalize the starter
pattern into code while keeping editable content synced if needed.

Possible code-backed assets:

| Asset | Path | Purpose |
| --- | --- | --- |
| Coded pattern starter | `app/public/wp-content/themes/protestsandsuffragettes/patterns/cross-site-banner-cta.php` | Provides a versioned starter pattern for rebuilding the banner. |
| CSS component | `app/public/wp-content/themes/protestsandsuffragettes/styles/components/cross-site-banner-cta.css` | Owns visual styling. |
| Header template part | `app/public/wp-content/themes/protestsandsuffragettes/parts/header.html` | Only if the team decides header structure should become code-governed. |

Do not build a custom registered block type unless the banner needs structured
fields, validation, scheduling, per-page display rules, or editor controls that
core blocks and a synced pattern cannot provide.

## Validation Checklist

- `wp post list --post_type=wp_block` confirms the synced pattern exists.
- `wp post get 1027 --field=post_content` confirms the Header references it.
- Browser check at `http://localhost:10008` confirms the banner below nav.
- Browser check on at least one interior page confirms cross-site visibility.
- Mobile viewport check confirms wrapping and nav interaction.
- Keyboard check confirms visible focus on both CTA links.
- CSS lint/compile passes if child-theme CSS tooling is touched.

## Documentation Updates

After implementation, update:

- `docs/2026-06-22-custom-blocks-patterns-audit.md` to list the new
  `wp_block` synced pattern and Header placement.
- `docs/2026-06-22-navigation-behavior-plan.md` if the Header structure changes
  materially.
- `AGENTS.md` only if the banner introduces durable workflow guidance future
  agents must know immediately.
