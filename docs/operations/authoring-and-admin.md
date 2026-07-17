# Authoring and administrator controls

This guide covers stable content and administration controls. It deliberately
does not prescribe templates, layouts, or reusable-pattern recipes while the
page rebuild proof is still in progress. Use the
[page rebuild lab](page-rebuild-lab.md) to record and test those decisions
before treating them as a theme contract.

## Before changing a global surface

Work in a non-public draft or staging environment when testing a global
change. Capture the current state and check the affected desktop and mobile
routes before and after saving. For navigation, template parts, synced
patterns, or global styles, source control is not a rollback for the saved
WordPress record; use the WordPress revision/history or an exported database
record as appropriate.

Do not use the Code Editor to recreate hidden wrapper markup. Use the normal
block editor and its controls for editorial content. Escalate an unexpected
layout requirement to the rebuild proof rather than creating a one-off
template or pattern.

## Ordinary pages, posts, and media

Create and edit ordinary content through the normal WordPress editor:

- **Pages → Add New** creates a page.
- **Posts → Add New** creates a news/post entry.
- **Media → Add New** uploads an asset independently of a post or page.

Keep a significant reconstruction as a draft until the selected template,
blocks, required plugins, and desktop/mobile preview have been reviewed. Do
not copy a published page's serialized block markup into a proof draft: rebuild
with the inserter so that the proof identifies missing controls or components.

Before publishing, confirm the title, permalink, featured image or focal point
where relevant, excerpt/metadata where used by the chosen route, links, and
desktop/mobile preview. Publishing changes WordPress database content; it is
not released by committing the theme repository.

Herstories are a plugin-owned content type. Their new-entry scaffold is
theme-supplied, but its exact composition is still under proof. Do not change
the scaffold or establish a new Herstory recipe from this guide.

## Navigation

The site uses native WordPress Navigation records, not a custom menu walker.
The theme supplies defaults named **Top Nav**, **Banner CTA Nav**, and
**Footer Nav**, but saved `wp_navigation` records are the live editable state.

Use the Site Editor navigation management surface to make an editorial link or
label change. The exact Site Editor navigation entry point may vary with the
installed WordPress version, so verify that the selected record is one of the
three named records above before saving.

Do not edit `navigation/*.html` to make a routine live menu change. Those files
seed missing records and serve as versioned defaults; they do not automatically
replace a navigation record that an administrator has already saved. Treat a
navigation restructure, new menu region, or change to the header/footer shell
as template work and defer it to the rebuild proof and ownership review.

After a navigation change, test the affected route at desktop and mobile
widths, including keyboard access, submenu behaviour, the mobile drawer, and
the banner CTA where relevant.

## Footer social links

Administrators manage footer social URLs at:

**Appearance → Footer Social Links**

This control accepts complete HTTPS URLs only. Leaving a service blank hides
it; an invalid non-empty value preserves the previous valid URL and displays an
administrator error. The available services and their display order are fixed
by the theme, so use this screen to maintain URLs—not to add a new network or
rearrange the footer.

Changing the social-link catalogue, display order, or footer markup is theme
work. Verify the footer at desktop and mobile widths after changing URLs.

## Site logo

The theme uses the core Site Logo value in both the Header and Footer. On first
activation it seeds `assets/images/logo.png` into the Media Library only when
no valid Site Logo is already configured; a later administrator selection is
kept.

Use **Appearance → Editor**, then open the Header template part and select its
Site Logo block to replace the logo through the normal Media Library flow. The
same global Site Logo value is rendered in the footer, so verify both locations
after saving. Do not replace the theme's source image merely to update the live
logo: that would change a versioned fallback rather than the configured site
state.

The active logo is database state. In particular, do not select the clean
uninstall policy in **Appearance → PNS Theme Setup** merely to update a logo:
that policy can remove the seeded logo and synced patterns when the theme is
switched away.

## Synced patterns

The supplied synced sections are native WordPress `wp_block` records:

- PNS - Contact Form
- PNS - Connect Social
- PNS - Read All About It
- PNS - Read All About It - Workshops
- PNS - Shop Intro

Use the Site Editor's synced-pattern management surface to make a deliberate
shared-content update. Because each use can change together, first identify
the pages that rely on the pattern and review them after saving. Use a regular
block group instead if the change must apply to one page only.

Do not treat files in `synced-patterns/` as the live editor surface. They seed
missing records on activation and provide a versioned reference, while the
saved `wp_block` record can be newer. Editing a fixture neither proves nor
updates the live pattern. New pattern recipes and structural changes remain
provisional until the rebuild proof has demonstrated a repeatable need.

## File defaults versus Site Editor state

This theme has two valid layers of state:

| Need                                                                         | Likely live owner                  | Safe first action                                                                          |
| ---------------------------------------------------------------------------- | ---------------------------------- | ------------------------------------------------------------------------------------------ |
| A page/post's text, media, or publishing state                               | WordPress content records          | Use the normal post/page editor.                                                           |
| A live menu, synced pattern, template part, template, or global-style change | Saved Site Editor database records | Inspect the corresponding Site Editor record and preserve a rollback point.                |
| A default fixture, CSS, PHP behaviour, or theme asset                        | Versioned theme source             | Change source in a reviewed theme change, then verify whether a saved record overrides it. |

Saved Site Editor records include `wp_template`, `wp_template_part`,
`wp_navigation`, `wp_block`, and `wp_global_styles`. A same-named saved record
can override a file in `templates/`, `parts/`, `navigation/`, or
`synced-patterns/`. Therefore, before deploying or editing one of those files,
compare it with the live record and decide which source is authoritative. Never
overwrite a saved record simply to make it match a file.

Theme activation creates missing navigation and synced-pattern records but
preserves existing matches. The lifecycle options at **Appearance → PNS Theme
Setup** are administrative migration controls, not routine authoring controls.
