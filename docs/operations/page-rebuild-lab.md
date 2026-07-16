# Page rebuild lab

Use this procedure to decide whether the theme's current templates, patterns,
and project blocks can reproduce real PNS pages through the normal WordPress
editor. It is an evidence exercise, not a way to replace or migrate live
content.

## Scope and safety

**Audience:** theme developers and reviewers working in a disposable Local or
staging WordPress environment.

**Authoritative sources:** the selected published reference page, current theme
files, registered blocks/patterns, and the normal WordPress editor.

**Never do this in the lab:** alter a published reference page; copy/paste its
entire serialized block content; modify `wp_template`, template parts,
navigation, synced patterns, or existing `**TEMPLATE*` drafts; or promote a
proof draft without a separate review.

Create a new draft named `PNS Rebuild Proof — <reference> — <date>` with a
unique non-public slug. Capture the source page's status and content hash
before beginning so the proof can show that the source was not changed.

## Build method

1. Select the intended theme template through the normal editor.
2. Rebuild through the inserter using current PNS patterns, project/plugin
   blocks, and ordinary Core blocks. Use editor controls rather than the code
   editor to reconstruct hidden wrapper markup.
3. Record the chosen template, blocks/patterns, plugin dependencies, and any
   editor action that is unexpectedly difficult or impossible.
4. Compare the draft preview with the reference at desktop and mobile. Assess
   hierarchy, ordering, media crop, CTA behaviour, and meaningful spacing—not
   literal content identity.
5. Classify every difference as one of: authoring guidance, one-off editorial
   composition, pattern/variation gap, integration gap, or route/type-level
   template gap.
6. Leave the proof as a draft for review. Do not turn it into a file template
   or synced pattern automatically.

## Current proof sequence

| Reference                     | What it proves                           | Current starting contract                                                                                                        |
| ----------------------------- | ---------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------- |
| ArtWorks                      | Ordinary wide editorial page composition | Default page template; covers, split sections, Jetpack slideshows, quotes, buttons, and synced sections.                         |
| A recent full-width news post | Editorial news workflow                  | `single-full-width-news`; enhanced cover, featured image/focal point, post metadata, and entry navigation.                       |
| Mary Barbour                  | Herstory stress test                     | `single-herstory` route plus the Herstory starter scaffold: hero, image split section, image strip, facts, and entry navigation. |

Defer homepage, shop, forms, search/archive, and 404 from the initial lab.
They rely on site options, query/runtime behaviour, or external services and do
not first answer whether an editor can assemble ordinary content from scratch.

## Decision rule

Add or change a reusable pattern only when the same composition gap appears in
two or more proofs. Propose a new template only when the need is genuinely
route/type-level and cannot be expressed with an existing selected template
plus blocks. Record a one-off solution as authoring guidance, rather than
turning it into new theme API.

## Evidence to retain

For each draft keep a concise record of:

- selected template and required plugins;
- inserted components and necessary editor settings;
- desktop and mobile preview comparison;
- blockers and their classification; and
- the unchanged published reference status/hash.

The proof result is a short evidence table and prioritised gap list. It is not
a final component catalogue; stable handbook entries follow only after the
evidence is reviewed.
