# Standalone Theme Ownership Matrix

Created: 2026-07-13.

Status: Cuts 0 through 7 are implemented locally. The Cut 7 release-handoff,
editor, layout, and lean visual landing gates pass. Mary Barbour retains its
intentional `edge-media-right` Split Section selection; the shared responsive
contract is code-owned by PNS Blocks.

Companion plan:
[`2026-07-13-standalone-template-ownership-and-health-remediation-plan.md`](2026-07-13-standalone-template-ownership-and-health-remediation-plan.md)

Dex: `h7o92y3q` — Ownership Cut 0 — establish the live ownership matrix.

## Purpose

This is the tracked ownership record for the standalone theme. It separates
settled structural presentation from ordinary author content and the named site
data that Administrators must continue to control.

The decisions below are the starting contract for Cut 0. Cut 0 must append the
live WordPress record inventory and verify the proposed editor control paths;
it must not silently change these decisions or mutate live records. A change to
one of the decisions needs an explicit follow-up decision in this document and
in the companion plan.

## Ownership vocabulary

| Class                  | Authoritative source                                                       | Release behaviour                                                                                                              |
| ---------------------- | -------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| **Code**               | Theme or project-plugin files                                              | A matching DB override is drift unless it has been explicitly approved and promoted into source.                               |
| **Editor data**        | The named WordPress database record or ordinary content record             | Code provides a stable reference or recovery seed only; releases never overwrite the live data.                                |
| **Administrator data** | A named WordPress record or project setting controlled by an Administrator | Code provides a stable reference or renderer only; normal WordPress Editors have no edit path.                                 |
| **Managed fixture**    | A versioned file plus an explicitly reviewed database record               | The file may seed or recover a missing record. Promotion in either direction needs an export, review, and documented decision. |

## Initial decisions

| Surface                                | Class                                                                     | Code owns                                                                                                                                                     | Editors own                                                                                                                     | Current and intended control path                                                                                                                 | Release rule                                                                                                                                    | Cut 0 evidence to record                                                                                                       |
| -------------------------------------- | ------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| Template structure                     | **Code**                                                                  | `templates/*.html`: header/footer placement, page chrome, hero/query/card composition, pagination, surface classes, and the `post-content` slot               | Page, post, and Herstory body content in `post-content`                                                                         | Authors edit the content item; template editing is not the normal authoring route                                                                 | Reconcile approved template source before removing its matching DB override; later DB overrides are drift                                       | Template slug, assigned pages, DB ID, file and DB fingerprints, current override status, backup export                         |
| Header shell                           | **Code**                                                                  | `parts/header.html`: logo placement, header layout, primary-navigation reference, and banner-CTA reference                                                    | The referenced navigation record, through the Administrator-only Navigation UI                                                  | Normal Editors have no header-part or Navigation edit path                                                                                        | Do not replace the file-backed shell when a menu changes                                                                                        | Part DB ID/fingerprint; `pns-primary-navigation` and `pns-banner-cta-navigation` references; capability proof                  |
| Footer shell                           | **Code**                                                                  | `parts/footer.html`: footer layout, logo placement, contact/copyright presentation, footer-navigation reference, and the hidden social-data block declaration | The referenced navigation record and the Administrator-only social-links setting                                                | Normal Editors have no footer-part or social-data edit path                                                                                       | The footer part must not become a side effect of an Administrator social-settings edit                                                          | Part DB ID/fingerprint; `pns-footer-navigation` reference; confirmation that a social edit does not create a part override     |
| Primary, footer, and banner navigation | **Administrator data**                                                    | Stable `wp:navigation` references and the shell around them                                                                                                   | Administrators: labels, destinations, ordering, submenu structure, and CTA text                                                 | Administrator-only Site Editor Navigation panel; normal WordPress Editors remain denied                                                           | `navigation/*.html` is seed/recovery material only; a release must never overwrite live navigation                                              | Navigation IDs/slugs, DB fingerprints, `edit_theme_options` proof, REST/UI path, recovery export                               |
| Footer social links                    | **Administrator data**                                                    | The footer's location, wrapper, classes, hidden `pns/footer-social-links` declaration, fixed service catalog, and display order                               | Administrators: enabled services and their HTTPS URLs                                                                           | Theme-owned `pns_footer_social_links` at **Appearance → Footer Social Links**, restricted to `manage_options`; not a synced `wp_block`            | Never overwrite social links from `parts/footer.html`; the dynamic block reads the setting without creating a footer-part override              | Setting contract; Administrator capability proof; recovery defaults/export; regression check                                   |
| Vertical query card                    | **Code**                                                                  | `pns/post-card` structure, responsive styling, metadata and taxonomy presentation                                                                             | Each listed post/Herstory's title, featured image, excerpt, date, author, taxonomy, and publication state                       | No card inserter entry. Editors update the source post or Herstory.                                                                               | Keep the pattern reference in template source; do not copy the markup into saved templates or ordinary pages                                    | Pattern file fingerprint; templates that reference it; inserter remains disabled; visual/card fallback checks                  |
| Horizontal search/archive card         | **Code**                                                                  | `pns/post-card-horizontal` structure, responsive styling, and metadata presentation                                                                           | The listed post's data                                                                                                          | No card inserter entry. Editors update the source post.                                                                                           | Keep the pattern reference in archive/search template source; do not clone it into DB templates                                                 | Pattern file fingerprint; archive/search references; inserter remains disabled; focused result-list checks                     |
| Featured News / Featured Herstory      | **Code composition + plugin runtime**                                     | The template placement and fixed query/layout attributes; `pns-blocks` owns dynamic rendering and its assets                                                  | The selected content's post data and the editorial order/date that the approved query deliberately uses                         | Hide the generic `pns/featured-post` block from normal insertion when it is used solely by templates; template source holds the chosen attributes | A release must retain the template block reference and must not permit a saved template copy to drift through editor-only query/layout controls | All template usages; block attributes and inserter state; dependency/style contract; query selection rule; visual/editor check |
| Future editorial callout card          | **Editor data through a limited block** — only if a real use case appears | A separately designed block's fixed markup, approved variants, and styling                                                                                    | Selected content or image, eyebrow, short copy, link, and CTA label within defined limits                                       | A dedicated, constrained "Feature content" block; not the archive-card pattern and not an open-ended nested layout                                | Do not expose arbitrary query controls, layout variants, nested blocks, or spacing controls merely to create a campaign callout                 | Approved use case, allowed attributes, insertion locations, editor preview, and focused visual checks                          |
| Ecwid product cards                    | **Plugin runtime**                                                        | The project-owned commerce runtime/Ecwid rendering contract and baseline component styling; the theme supplies site tokens and narrowly scoped adapters       | Approved product-grid settings where the saved block is editor-owned (for example category and limit) and product data in Ecwid | Use the project-owned product-grid integration and supported Ecwid controls; do not rebuild product-card markup in a template                     | Keep vendor/runtime ownership separate from template migration; do not edit Ecwid plugin files                                                  | Current plugin/source owner, saved locations, allowed settings, Shop/cart/static-grid regression coverage                      |

## Decisions on the current card components

1. The vertical and horizontal post cards are not ordinary editor blocks. They
   are code-owned template components which use WordPress post blocks to render
   editor-owned content data. Their existing `inserter: false` status is part of
   the contract.
2. Featured Post is also a template component for the current Home and
   Herstories uses. Its query, offset, ordering, text labels, and layout are
   template policy, not page-editor controls. Cut 0 confirmed that its only
   current non-revision uses are `home` and `archive-herstory`; no ordinary
   page/post/Herstory content use was found before insertion is restricted.
3. A future manually curated promotional card is a different component. It may
   become a deliberately small block, but only after an explicit use case and
   attribute contract are recorded. It must not reuse `pns/post-card` as an
   editable layout.
4. Product cards are not part of this template-ownership migration. They are
   produced by the project-owned commerce runtime and Ecwid integration; Cut 0
   records the current package owner without altering the in-progress commerce
   work.

## Known migration boundaries

- Cut 1 has replaced only the direct Social Links declaration in
  `parts/footer.html` with hidden dynamic `pns/footer-social-links`, backed by
  the Administrator-only `pns_footer_social_links` theme setting. The
  surrounding group, heading, Core classes, and footer layout remain code.
- Footer social links do not use a synced pattern. `connect-social` remains a
  separate managed page/single-Herstory callout; normal Editors must not gain a
  broad synced-pattern surface merely to change footer URLs.
- `parts/header.html` and `parts/footer.html` already reference navigation by
  stable `pnsRefSlug` values. This is the model to preserve for
  Administrator-owned navigation.
- `pns/post-card` and `pns/post-card-horizontal` are already hidden from the
  inserter and referenced from code templates. The migration protects and
  verifies this pattern; it does not replace it with a new custom block.
- `pns/featured-post` currently exposes query and layout controls in the normal
  block editor. That is acceptable only while proven editorial uses exist;
  otherwise Cut 2/Cut 4 should make it template-only and repair its asset
  contract.

## Live baseline and scope

Verified read-only on 2026-07-13:

- The active stylesheet and template are both
  `protestsandsuffragettes-standalone`.
- WordPress uses a static front page: `page_on_front=49` (the default
  file-backed `page` template) and `page_for_posts=5190` (the `home` template).
- This inventory includes standalone-theme records and fixture-backed shared
  records only. The legacy `protestsandsuffragettes`, `estory`, and `carbon`
  template/global-style records remain outside this migration.
- Hashes below are `SHA-256(rtrim(serialized content))`; equality means the
  current saved record and its canonical fixture have identical meaningful
  serialized content under that method.
- `Cut 2 pre-mutation export` means one JSON export per record at
  `docs/jobs/standalone-template-ownership-db-backups/<timestamp>-before-<type>-<id>-<slug>.json`.
  Cut 0 deliberately creates no such record export or DB mutation.

### Code templates and parts

| Record                                                  | Assigned route or use                                              | Canonical source                                    | File fingerprint                                                   | DB fingerprint                                                     | Relationship and Cut 2 disposition                                                                                             | Rollback export                                             |
| ------------------------------------------------------- | ------------------------------------------------------------------ | --------------------------------------------------- | ------------------------------------------------------------------ | ------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------ | ----------------------------------------------------------- |
| `wp_template#6132` `404`                                | 404 hierarchy                                                      | `templates/404.html`                                | `6b9fb434ce52de685f4400f5e66b5776b32fc2c043af08b5001fe00da17a1726` | `6b9fb434ce52de685f4400f5e66b5776b32fc2c043af08b5001fe00da17a1726` | Byte-equal Code shadow; back up and remove only after route check                                                              | Cut 2 pre-mutation export                                   |
| `wp_template#6278` `archive`                            | Native post archive hierarchy                                      | `templates/archive.html`                            | `32cf2ae798d9f6ef5ac8f315c175ac4024a7c3f41e3cdafc55b139e1ca2de193` | `32cf2ae798d9f6ef5ac8f315c175ac4024a7c3f41e3cdafc55b139e1ca2de193` | Byte-equal Code shadow; back up and remove only after route check                                                              | Cut 2 pre-mutation export                                   |
| `wp_template#6138` `archive-herstory`                   | `/herstories/` archive                                             | `templates/archive-herstory.html`                   | `31422a333d8d419a81a04d88e8b252e2ae3b99ef29adea211aaa23575a3d0042` | `5732517a37a383e7e474a52af1f3041b189d3c54c5f2f4362810619c2d6434c4` | **Code wins.** DB expands `pns/post-card`, contains serialization/token noise and malformed escaped values; do not promote     | Cut 2 pre-mutation export                                   |
| `wp_template#6186` `home`                               | News posts page `#5190`                                            | `templates/home.html`                               | `c6cac45710f3c77f7b6b81838fdabb9323dffe244064c59d97b999a66c0c2d71` | `a04ea0c96e01e7d3ae7fd6f5a842ad874a4f012df97c2453ae1be55ff026f3a2` | **Code wins.** DB expands `pns/post-card` and contains serialization/malformed-escape noise; Featured Post policy is unchanged | Cut 2 pre-mutation export                                   |
| `wp_template#6383` `page-light-surface`                 | Shop `#565`; private Style Guide `#6340`                           | `templates/page-light-surface.html`                 | `4a9a3eaf8f54e6c1e2dce5e70af9feaa276fb61ac8c9da43a87a8675786cb9c8` | `939a07cde02caf0777e91befd7ed1e51492332f95cd5f169b934bcda3d4928f6` | **Code wins.** DB reserializes zero spacing tokens only; file is newer and expresses the same rendered intent                  | Cut 2 pre-mutation export                                   |
| `wp_template#6390` `page-light-surface-no-contact-form` | Contact Success `#6253`; Contact Us `#6236` currently uses default | `templates/page-light-surface-no-contact-form.html` | `6015be564a787182e4d4cbc161d24141f077b1fc40828d2423b5d4d0c76bad38` | `6015be564a787182e4d4cbc161d24141f077b1fc40828d2423b5d4d0c76bad38` | Byte-equal Code shadow; back up and remove only after route check                                                              | Cut 2 pre-mutation export                                   |
| `wp_template#6219` `page-search`                        | Search page `#6023`                                                | `templates/page-search.html`                        | `952547aae8402cafde7d6e7feb17b6b185b6bee9cd8717e95d6b93bc6ee5b70d` | `952547aae8402cafde7d6e7feb17b6b185b6bee9cd8717e95d6b93bc6ee5b70d` | Byte-equal Code shadow; back up and remove only after route check                                                              | Cut 2 pre-mutation export                                   |
| `wp_template#6221` `search`                             | Native search-results hierarchy                                    | `templates/search.html`                             | `88bcc188ff2ab8fd0f7c78d9d94126c149f4b2efd51b2a1c2688ba951d0480a8` | `88bcc188ff2ab8fd0f7c78d9d94126c149f4b2efd51b2a1c2688ba951d0480a8` | Byte-equal Code shadow; back up and remove only after route check                                                              | Cut 2 pre-mutation export                                   |
| `wp_template#5990` `single`                             | Standard-post hierarchy                                            | `templates/single.html`                             | `efc0304819d9a08240c3d801e9acbfeb78dd594b9793e491ab89d6bbab8b51e4` | `b7bacd061736b1af7a21522bfef4abb90748e9b5835672c75c30b73fbfbea48d` | **Code wins.** DB expands `pns/entry-post-navigation`; retain the clean source pattern reference                               | Cut 2 pre-mutation export                                   |
| `wp_template_part#5936` `header`                        | Shared header shell                                                | `parts/header.html`                                 | `f4e5a41b77832768cf51f5287b61c4c926cc7d736392d8deab652371d6819612` | `f4e5a41b77832768cf51f5287b61c4c926cc7d736392d8deab652371d6819612` | Byte-equal Code shadow; preserve named navigation references                                                                   | Cut 2 pre-mutation export                                   |
| `wp_template_part#5980` `footer`                        | Shared footer shell                                                | `parts/footer.html`                                 | `7c4376d671cbbfd5639960795150b18d0e7e36d377493e11b324a112dca95fc0` | `7c4376d671cbbfd5639960795150b18d0e7e36d377493e11b324a112dca95fc0` | Byte-equal Code shadow after Cut 1. It now contains only the hidden social-data declaration; Cut 2 may remove the DB override. | `20260713T105646Z-before-wp_template_part-5980-footer.json` |

The remaining standalone template files — `index`, `page`,
`page-no-contact-form`, `page-suffragette`, `single-full-width-news`, and
`single-herstory` — have no active standalone `wp_template` record. They are
already file-authoritative. In particular, front page `#49` currently resolves
through file-backed `page`; Education Pack Giveaway `#4629` uses the
file-backed `page-no-contact-form` template.

### Cut 2 outcome

- On 2026-07-13, the eleven records above were exported before mutation to
  `docs/jobs/standalone-template-ownership-db-backups/` with prefix
  `20260713T114131Z-`, then permanently deleted. The earlier Cut 1 footer
  rollback manifest remains intact; this set independently captures the
  current footer row before its removal.
- Post-delete runtime assertions confirmed each target now resolves from its
  filesystem source (`source: theme`, `has_theme_file: true`) with the
  expected SHA-256. No navigation, synced-pattern/block, post-content, global
  style, or footer-social setting was changed.
- The Contact Us assignment correction above was observed during validation.
  It is editor-owned data and therefore deliberately outside the code-authority
  mutation: the default template is current live state, while the dedicated
  no-contact-form template remains file-authoritative for Contact Success.

### Cut 3 release guard and recovery runbook

The guard is intentionally a report, not a synchronizer. It discovers every
standalone `templates/*.html` and `parts/*.html` file, reports all saved
standalone `wp_template` and `wp_template_part` rows across every post status,
and checks that each file resolves from WordPress as `source: theme` with a
theme file. A byte-equal saved row is still a failure because it silently
shadows the file.

Run it from the standalone theme directory:

```sh
pnpm audit:template-ownership # local investigation: warn, exit 0
pnpm check:template-ownership # release gate: strict, non-zero on drift
```

The root-equivalent commands are:

```sh
wp eval-file app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/audit-template-ownership.php warn
wp eval-file app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/audit-template-ownership.php strict
wp eval-file app/public/wp-content/themes/protestsandsuffragettes-standalone/scripts/audit-template-ownership.php strict json
```

For a release that includes code-backed templates, capture the Administrator
data before deployment and verify it afterwards from the standalone theme
directory:

```sh
pnpm capture:release-handoff
pnpm check:template-ownership
# deploy the reviewed theme files; do not import or overwrite navigation/social data
pnpm check:template-ownership
pnpm verify:release-handoff
pnpm check:release-handoff
```

`capture:release-handoff` writes the ignored
`.cache/release-handoff.json` snapshot. It stores record identity, state, and
hashes only—never navigation markup or social-link URLs. `verify` compares the
post-deployment values with that snapshot. `check:release-handoff` then proves
the file-backed shell still uses stable references and that the normal
non-overwriting seed path returns `kept` without altering existing
Administrator data.

`strict` is the required pre-deploy and post-deploy policy. It blocks on a
saved Code template/part override, an unclassified active-theme structural
record, missing named Administrator navigation, or a non-neutral standalone
global-styles payload. `warn` shows the same result for local investigation but
does not fail the command. The root pre-commit hook deliberately does not run
this DB-aware check: it remains a release/local-service gate, rather than a
static source check.

The report lists these surfaces without treating normal data edits as template
drift:

- Administrator data: the three named `wp_navigation` records and the
  `pns_footer_social_links` setting are informational. Navigation content is
  never compared to its recovery fixture, and social URLs are never dumped or
  overwritten.
- Managed fixtures: `contact-form`, `connect-social`, `read-all-about-it`,
  `read-all-about-it-workshops`, and `shop-intro` are review-only. The known
  workshop difference is not a release failure. `connect-social` is not footer
  social data.
- Editor data: Page, post, Herstory, campaign, and other ordinary content is
  excluded entirely.
- Global Styles: the generated neutral record is expected; a real user
  settings/styles payload is surfaced for explicit review rather than reset.

If strict mode fails, stop the deployment and choose one reviewed path:

1. Back up the saved record before changing it. Record its post fields, all
   metadata, `wp_theme` term, raw `post_content` encoded as base64, and the
   `SHA-256(rtrim(content))` in a new manifest under
   `docs/jobs/standalone-template-ownership-db-backups/`. The Cut 2 manifests
   are the recovery format to follow.
2. Compare the exported content with the Git file. If the DB layout is
   approved, promote that layout to the code file through normal review, then
   validate the parser and affected route. Do not copy editor-expanded card or
   navigation markup into source merely to silence the guard.
3. If code is approved, clear the Site Editor customization at **Appearance →
   Editor → Templates** or **Template Parts** using the template's reset/delete
   customization action. The reviewed CLI equivalent is `wp post delete <ID>
--force`; do not trash it, because trashed templates can still shadow the
   file.
4. Flush the relevant cache, rerun strict mode, then run
   `php scripts/validate-block-templates.php` and focused browser coverage.
   Restoring a reviewed DB backup is the inverse, explicit operation; the guard
   itself never restores anything.

After a targeted template/part rollback, rerun
`pnpm check:template-ownership`, `pnpm verify:release-handoff`, and
`pnpm check:release-handoff`. A template rollback must not touch navigation or
footer-social data; a failed release-handoff check is evidence to stop and
restore the administrator-data backup before continuing.

Navigation recovery stays in the Administrator Site Editor Navigation panel.
Footer social recovery stays at **Appearance → Footer Social Links**; deleting
the `pns_footer_social_links` option restores the versioned seven-link default.
Neither recovery path creates or edits a header/footer template override.

### Cut 7 release and editor handoff record

- `capture:release-handoff` writes an ignored, hash-only local snapshot;
  `verify:release-handoff` matches the later Administrator data; and
  `check:release-handoff` confirms the file-backed header/footer references
  and non-overwriting navigation seed path. All three pass against the active
  standalone site.
- Normal WordPress Editors are denied template, template-part, navigation, and
  footer-social writes; Administrators retain the intentionally narrow
  Navigation and **Appearance → Footer Social Links** control paths.
- The Contact Us visual test now correctly protects its current default-template
  assignment and editor-owned Jetpack form. Contact Success alone owns the
  file-backed `page-light-surface-no-contact-form` assignment.
- The PNS Blocks Split Section editor stylesheet now keeps Jetpack slideshow
  media cropped and non-zero after hydration. The full editor suite passes six
  real tests; two private-fixture tests remain intentionally skipped.
- Mary Barbour's editor-owned `edge-media-right` selection is the approved
  content state and was not mutated. PNS Blocks now restores code-owned copy
  padding despite legacy saved zero values, keeps non-video media at full row
  height with `object-fit: cover`, and stacks all four variants below 60rem.
  The 900px tablet layout stacks; 960px and the reviewed 1054px reference use
  rows. Focused breakpoint, full layout, editor, and lean visual landing gates
  pass, and only the intentionally affected local snapshots were refreshed.

### Administrator data, editor data, and managed fixtures

| Record                                                                        | Class              | Canonical fixture / reference                               | File fingerprint                                                   | DB fingerprint                                                     | Relationship and release treatment                                                                                                                                                                               | Rollback export                                          |
| ----------------------------------------------------------------------------- | ------------------ | ----------------------------------------------------------- | ------------------------------------------------------------------ | ------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------- |
| `wp_navigation#1035` `pns-primary-navigation`                                 | Administrator data | `navigation/primary.html`; `parts/header.html` reference    | `3d878245c9e0bef287312399733119d4581ce07176a1740b40463a1c9e3daf50` | `42d2af7d3f8c8e1cfb4ebc6db1084234bef40f8bc8f820049a83c16d51af4edb` | Different is expected Administrator data. Keep DB content; fixture is recovery only.                                                                                                                             | Recovery export before any explicit navigation migration |
| `wp_navigation#5259` `pns-banner-cta-navigation`                              | Administrator data | `navigation/banner-cta.html`; `parts/header.html` reference | `eb00475f2d31bab987dc31f232fe707df84f9036d23e77ab94c44b2bc302caa7` | `eb00475f2d31bab987dc31f232fe707df84f9036d23e77ab94c44b2bc302caa7` | Equal now, but still Administrator data; releases never overwrite it                                                                                                                                             | Recovery export before any explicit navigation migration |
| `wp_navigation#1032` `pns-footer-navigation`                                  | Administrator data | `navigation/footer.html`; `parts/footer.html` reference     | `4d1b3ebfe10f515bc1835335cd5df5346228b3318c41d13fb676713f4de65aab` | `4d1b3ebfe10f515bc1835335cd5df5346228b3318c41d13fb676713f4de65aab` | Equal now, but still Administrator data; releases never overwrite it                                                                                                                                             | Recovery export before any explicit navigation migration |
| `wp_block#1493` `contact-form`                                                | Managed fixture    | `synced-patterns/contact-form.html`                         | `313456c510b2435793fd59855bb93a04c1e5b7969d305a745fd2c524e4d7857b` | `313456c510b2435793fd59855bb93a04c1e5b7969d305a745fd2c524e4d7857b` | Equal; file may recover a missing record, not overwrite live edits                                                                                                                                               | Explicit promotion/export required                       |
| `wp_block#1494` `connect-social`                                              | Managed fixture    | `synced-patterns/connect-social.html`                       | `0958da1b26c998186e88c8823a7a852f2c26a809aebeee485aa2a0a4e99443b9` | `0958da1b26c998186e88c8823a7a852f2c26a809aebeee485aa2a0a4e99443b9` | Equal; whole-page CTA used by page/single-Herstory content, never footer-social data                                                                                                                             | Explicit promotion/export required                       |
| `wp_block#1504` `read-all-about-it`                                           | Managed fixture    | `synced-patterns/read-all-about-it.html`                    | `e886dd40ceb35597f92c1ab9310fb789758145c96e04ff3f8e04d3285ae1b47c` | `e886dd40ceb35597f92c1ab9310fb789758145c96e04ff3f8e04d3285ae1b47c` | Equal; file may recover a missing record, not overwrite live edits                                                                                                                                               | Explicit promotion/export required                       |
| `wp_block#6487` `read-all-about-it-workshops`                                 | Managed fixture    | `synced-patterns/read-all-about-it-workshops.html`          | `295d767ffdf25325ce77dc3ba0294531dbdb21a59635b57c22a0a4f88142304a` | `605c60974738e55e88317a907383b3137409e5427200c4af7fe37110e84842a2` | Different. DB has an expanded saved query and lacks the fixture's taxonomy-slug constraint; do not promote or delete in Cut 2 without a separate approved workshop check                                         | Explicit promotion/export required                       |
| `wp_block#1509` `shop-intro`                                                  | Managed fixture    | `synced-patterns/shop-intro.html`                           | `9e724a8bf2e3aec829a154a5fbd72c799877ac6ff4caf9538c60361029eaaf30` | `9e724a8bf2e3aec829a154a5fbd72c799877ac6ff4caf9538c60361029eaaf30` | Equal; file may recover a missing record, not overwrite live edits                                                                                                                                               | Explicit promotion/export required                       |
| `wp_global_styles#5256` `wp-global-styles-protestsandsuffragettes-standalone` | Code configuration | `theme.json`                                                | `f3c855b0e98ba8e8561bd23a190992620e25199dd959baf8a3844d7347f3344a` | `b34f03399f3dbf3d9e2b85a9493107bd3bcfbfc063c46c049125323f71139e71` | Semantic review: DB contains only `{"version":3,"isGlobalStylesUserThemeJSON":true}` with no user settings. Treat this generated neutral record as expected; a future user settings/style payload is Code drift. | Export before any explicit global-style reset            |

## Control-path verification

The desired ownership split is valid, but the current WordPress permissions do
not make navigation editable by the normal `editor` role.

| Surface                                | Tested current path                                                                                                                     | Normal Editor result                                                             | Administrator result                            | Cut 1 constraint                                                                                                                                      |
| -------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------- | ----------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| Primary, footer, and banner navigation | `/wp-admin/site-editor.php?p=/navigation`; for example `/wp-admin/site-editor.php?p=/wp_navigation/1035&canvas=edit`                    | Denied: `wp_navigation` maps to `edit_theme_options`; REST edit context is `403` | Allowed                                         | **Locked:** Navigation is Administrator-only. Do not grant `edit_theme_options` to Editors, because it also opens templates and Global Styles.        |
| Footer social links — former state     | Direct Social Links markup in `parts/footer.html`                                                                                       | Not a separate record, therefore no safe data-only edit path                     | Part editing is intentionally Site Editor-gated | Replaced by Cut 1; the prior part content is recoverable from the recorded rollback manifest.                                                         |
| Footer social links — implemented path | **Appearance → Footer Social Links**: Administrator-only `pns_footer_social_links` setting rendered by hidden `pns/footer-social-links` | No edit path                                                                     | Allowed with `manage_options`                   | **Locked:** do not use a synced `wp_block`; fixed services/order stay code-owned and the footer renders the setting without becoming editor-editable. |

The existing `connect-social` record (`wp_block#1494`) is not a safe shortcut:
it is a complete page/single-Herstory callout with copy, image, and social
links. It must remain separate from footer social data.

**Locked Cut 1 decision:** navigation remains Administrator data through core
Navigation. Footer social links become Administrator data through an
Administrator-only theme setting and hidden dynamic block. Normal WordPress
Editors cannot edit navigation, footer social data, header parts, or footer
parts.

## Cut 1 implementation record

Implemented locally on 2026-07-13:

- `parts/footer.html` now has exactly one hidden
  `pns/footer-social-links` declaration and no direct Social Links data.
- `inc/footer-social-links.php` owns the Settings API screen, HTTPS whitelist,
  fixed seven-service catalog/default order, and theme-local dynamic block
  registration. The block is hidden from the inserter and server-renders Core
  Social Links markup, retaining the current CSS/class contract.
- `wp_template_part#5980` was exported through the source-equivalent rollback
  manifest noted above, then updated to the approved new file content. Its
  source and DB `rtrim` SHA-256 now match:
  `7c4376d671cbbfd5639960795150b18d0e7e36d377493e11b324a112dca95fc0`.
- A saved empty setting renders no services. Deleting the option restores the
  seven code defaults. An invalid URL preserves its prior valid value, while an
  empty field intentionally disables its service.
- Focused validation passed: PHP syntax, block-template parser, Settings API
  capability/REST/sanitization proof, temporary save/render/restore proof,
  live browser markup inspection, homepage footer contract, and all 12
  desktop/mobile navigation tests. The Mary Barbour mismatch that started
  above the footer was unrelated to this cut and is closed by the Split Section
  follow-up recorded in the Cut 7 handoff section.

Recovery:

1. An Administrator uses **Appearance → Footer Social Links** to change a URL
   or leave it empty to hide that fixed service.
2. To reset all links to the versioned defaults, delete the
   `pns_footer_social_links` option; do not edit the footer template part.
3. To roll back the Cut 1 footer serialization before Cut 2, restore the exact
   Git blob named in
   `standalone-template-ownership-db-backups/20260713T105646Z-before-wp_template_part-5980-footer.json`
   to `wp_template_part#5980`. This affects only the footer part and not the
   navigation records or social setting.

## Cut 0 acceptance record

Complete on 2026-07-13:

- Every active standalone shared record and relevant fixture-backed record is
  classified above, with an exact source/DB relationship and pre-mutation
  rollback destination.
- Navigation and social control paths were tested through the current
  Administrator and WordPress Editor capabilities plus their REST permissions.
  The result is a constraint for Cut 1, not a role/capability change in Cut 0.
- Current card use is verified: query cards stay code-only, and Featured Post
  is used only by the two code templates noted above.
- No content, template, part, navigation, global-style, role, or capability
  was mutated to create this inventory.
