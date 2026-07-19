# Current template and component inventory

This is a code-backed inventory of the current theme surface. It is a
maintenance reference, not a declaration that every current template or
pattern is final. Confirm a route against the live WordPress database before
changing it: Site Editor records can override the matching file.

Use the [page rebuild lab](../operations/page-rebuild-lab.md) before promoting
an editorial composition into a new pattern or template. Add a permanent
authoring recommendation only after the same need is demonstrated in more
than one proof.

## Templates and parts

Theme templates are versioned defaults in `templates/`. The active Site Editor
template may instead be a `wp_template` database record with the same slug.

| File                                      | Intended route or purpose                            |
| ----------------------------------------- | ---------------------------------------------------- |
| `404.html`                                | Not-found response.                                  |
| `archive.html`                            | General archive fallback.                            |
| `archive-herstory.html`                   | Herstory archive.                                    |
| `home.html`                               | Posts page/home composition.                         |
| `index.html`                              | Block-theme fallback.                                |
| `page.html`                               | Default Page.                                        |
| `page-no-contact-form.html`               | Page without the standard contact-form section.      |
| `page-light-surface.html`                 | Page with the light-surface treatment.               |
| `page-light-surface-wide-content.html`    | Light-surface page with wider content.               |
| `page-light-surface-no-contact-form.html` | Light-surface page without the contact-form section. |
| `page-search.html`                        | Search landing page.                                 |
| `page-suffragette.html`                   | Legacy/page-based Suffragette layout.                |
| `search.html`                             | Search-results route.                                |
| `single.html`                             | Standard post fallback.                              |
| `single-full-width-news.html`             | Full-width news posts.                               |
| `single-herstory.html`                    | Herstory single route.                               |

The shared parts are `parts/header.html` and `parts/footer.html`. Treat them
as high-impact global surfaces: verify desktop and mobile navigation, footer
links, forms, and social links whenever either part changes.

## Code-backed patterns

The theme registers the following patterns through `inc/patterns.php`. Unless
noted, they appear in the relevant PNS pattern category in the editor.

| Pattern                         | Use                                                                   | Inserter status |
| ------------------------------- | --------------------------------------------------------------------- | --------------- |
| `pns/page-hero`                 | Full-width page hero.                                                 | Available       |
| `pns/basic-centred-content`     | Centred editorial content section.                                    | Available       |
| `pns/two-columns`               | Compatibility two-column section; prefer split sections for new work. | Hidden          |
| `pns/split-section-image`       | Copy with edge-aligned image.                                         | Available       |
| `pns/split-section-slideshow`   | Copy with Jetpack slideshow.                                          | Available       |
| `pns/text-only-section`         | Constrained text section.                                             | Available       |
| `pns/entry-post-navigation`     | Standard post previous/back/next controls.                            | Available       |
| `pns/news-hero`                 | Enhanced Cover news hero; align its poster and featured image.        | Available       |
| `pns/post-card`                 | Shared vertical query-loop card.                                      | Hidden          |
| `pns/post-card-horizontal`      | Shared horizontal search-result card.                                 | Hidden          |
| `pns/image-strip`               | Full-width visual-break image strip.                                  | Available       |
| `pns/blockquote-cover`          | Image-backed quote cover.                                             | Available       |
| `pns/blockquote-with-red-line`  | Quote with red-keyline treatment.                                     | Available       |
| `pns/suffragette-facts`         | Herstory facts section.                                               | Available       |
| `pns/suffragette-image-strip`   | Herstory visual-break image strip.                                    | Available       |
| `pns/suffragette-hero`          | Herstory profile hero.                                                | Available       |
| `pns/entry-herstory-navigation` | Herstory previous/back/next controls.                                 | Available       |
| `pns/suffragette-stats`         | Herstory statistics layout.                                           | Available       |

“Hidden” patterns are implementation components for templates/query loops or
compatibility content. Do not expose them through the inserter without an
editorial need and an authoring guide.
Authors should use the PNS Split Section block's YouTube variation for new
YouTube embeds; it is the canonical authoring workflow.

The Herstory plugin asks this theme for a new-entry scaffold. The scaffold is
`pns/suffragette-hero`, `pns/split-section-image`,
`pns/suffragette-image-strip`, `pns/suffragette-facts`, followed by
`pns/entry-herstory-navigation`. The plugin owns the post type; the theme owns
this visual arrangement.

## Seeded database-backed components

The theme ships fixtures for records that are created and then live in the
WordPress database. Their files are defaults and installation aids, not a
promise that production is using the file unchanged.

| Record type     | Fixture directory  | Current supplied records                                                |
| --------------- | ------------------ | ----------------------------------------------------------------------- |
| Navigation      | `navigation/`      | Top Nav, Banner CTA Nav, Footer Nav.                                    |
| Synced patterns | `synced-patterns/` | Contact form, Connect social, Read all about it, Workshops, Shop intro. |

The lifecycle/seed path preserves existing records by default. Inspect the
matching `wp_navigation` or `wp_block` record before changing its fixture,
then decide whether the file or the saved editor record is authoritative for
the intended release.

## Functional components and where to look

| Concern                                            | Main theme location                                      | Boundary to verify                                                                                 |
| -------------------------------------------------- | -------------------------------------------------------- | -------------------------------------------------------------------------------------------------- |
| Design tokens and block defaults                   | `theme.json`                                             | Editor global styles may override saved settings.                                                  |
| Frontend and editor CSS                            | `styles/frontend.css`, `styles/editor.css`               | Compiled files in `styles/dist/` are generated delivery assets.                                    |
| CSS architecture                                   | `styles/` and [CSS architecture](../architecture/css.md) | Check the affected frontend and editor surface.                                                    |
| Navigation presentation and seeds                  | `inc/navigation.php`, `navigation/`                      | Native Navigation records are database state.                                                      |
| Herstory scaffold and archive/navigation behaviour | `inc/herstories.php`                                     | PNS Herstories plugin owns the content model.                                                      |
| Dependency health contract                         | `inc/dependencies.php`                                   | Plugins are listed in [ownership and integrations](../architecture/ownership-and-integrations.md). |
| Footer social-link administration                  | `inc/footer-social-links.php`                            | Confirm the configured social URLs in WordPress admin.                                             |
| Theme lifecycle/setup                              | `inc/theme-lifecycle.php`                                | Setup choices can create or retain database records.                                               |
| Ecwid and EmailOctopus compatibility bridges       | `functions.php` and `inc/`                               | Treat vendor feature changes as plugin/API work first.                                             |

## Catalogue maintenance

When a template, pattern, component, or dependency changes, update this page
in the same change. For route-level work, include the selected template, live
ownership state, and relevant visual test lane in the pull request or release
record. Do not make a versioned file look authoritative when a saved Site
Editor record is the active source.
