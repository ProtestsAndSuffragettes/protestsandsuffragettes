# SEO metadata review — published pages

Prepared 19 July 2026 for review before any WordPress changes.

## Scope and conventions

- This is the first pass for published public pages, plus the requested exception: the Mary Barbour Herstory profile.
- Proposed titles use the organisation name where it adds useful context. Descriptions are written as unique search-result summaries, rather than copied page text.
- URLs below use the production domain. No SEO metadata has been changed in WordPress.
- Jetpack SEO is the active metadata provider. The Gender Inclusion Policy values below are its existing live values, retained for approval rather than replaced.
- The `Search` page is included as a deliberate **noindex** recommendation. It should not receive a standard SEO title and description.

| Content | Production URL | Proposed SEO title | Proposed SEO description |
| --- | --- | --- | --- |
| Protests and Suffragettes (home) | `https://protestsandsuffragettes.com/` | `Scottish Women’s History & Activism \| Protests & Suffragettes` | `Exploring the stories of Scotland’s women activists through research, workshops, murals, artwalks, educational resources and Wikipedia projects.` |
| Shop | `https://protestsandsuffragettes.com/shop/` | `Shop Scottish Suffrage Gifts & Resources \| Protests & Suffragettes` | `Discover the amazing history of Scottish suffrage through Suffragette Trumps cards, zines, posters and gifts, and support our work in schools with one of our excellent Education Packs.` |
| ArtWorks | `https://protestsandsuffragettes.com/artworks/` | `Scottish Women’s History Artworks \| Protests & Suffragettes` | `Discover murals, artwalks, zines, posters and public art that recover the stories of Scotland’s women activists.` |
| Educational Resources | `https://protestsandsuffragettes.com/educational-resources/` | `Scottish Suffrage Teaching Resources \| Protests & Suffragettes` | `Teaching resources on Scotland’s suffrage history for primary and secondary schools, including lesson plans, worksheets and Suffragette Trumps!` |
| About us | `https://protestsandsuffragettes.com/about-us/` | `About Protests & Suffragettes \| Scottish Women’s History` | `Meet the artists, activists and local historians recovering and sharing the stories of women activists across Scotland.` |
| Shenanigans | `https://protestsandsuffragettes.com/shenanigans/` | `Workshops, Talks & Events \| Protests & Suffragettes` | `Find out about Protests & Suffragettes workshops, talks, events, media features and creative collaborations celebrating women’s history.` |
| Education Pack Giveaway | `https://protestsandsuffragettes.com/educational-resources/edu-giveaway/` | `Free Scottish Suffrage Teaching Resources \| Protests & Suffragettes` | `Teachers and youth-group leaders can sign up for free Scottish suffrage teaching resources and a discount on our Education Pack.` |
| Gender Inclusion Policy Statement | `https://protestsandsuffragettes.com/about-us/gender-inclusion-policy/` | `Gender Inclusion Statement` | `Our policy for gender inclusion: we aim to provide an inclusive, welcoming and supportive environment for everyone regardless of their gender.` |
| News | `https://protestsandsuffragettes.com/news/` | `News & Updates \| Protests & Suffragettes` | `Read the latest news, projects, events and discoveries from Protests & Suffragettes and our work on women’s history in Scotland.` |
| Membership | `https://protestsandsuffragettes.com/membership/` | `Join P&S Membership \| Support Women’s History in Scotland` | `Join Protests & Suffragettes membership to help make Scotland’s women’s history visible in classrooms, archives, murals and public spaces.` |
| Privacy Policy | `https://protestsandsuffragettes.com/about-us/privacy-notice/` | `Privacy Policy \| Protests & Suffragettes CIC` | `Learn how Protests and Suffragettes CIC collects, uses, stores and protects personal information through its website and activities.` |
| Mary Barbour | `https://protestsandsuffragettes.com/herstories/mary-barbour/` | `Mary Barbour & the 1915 Govan Rent Strikes \| Protests & Suffragettes` | `Discover Mary Barbour, the Govan activist who led the 1915 Rent Strikes and campaigned for housing, health and women’s rights.` |

## Search page — recommended noindex

| Content | Production URL | Recommendation |
| --- | --- | --- |
| Search | `https://protestsandsuffragettes.com/search/` | Set `noindex, follow`. Internal search-result pages provide little standalone search value and can create many thin, duplicative URLs. Do not add a normal SEO title or meta description. |

## Intentionally not proposed in this review

| Content | Reason |
| --- | --- |
| Contact Us | Explicitly excluded. |
| Thank you | Explicitly excluded; conversion confirmation pages are normally `noindex`. |
| Other Herstory profiles | Explicitly excluded; Mary Barbour is the sole requested exception. |
| PNS Pattern QA, Template Placeholder, blank page `7247-2` | Published technical or unfinished pages. They should not be optimised; review whether each should be unpublished or set to `noindex` separately. |

## Review decisions needed

1. Confirm the wording and organisational voice for the 12 metadata records.
2. Confirm whether the search, confirmation and technical pages should be set to `noindex` when metadata is implemented.
3. Confirm whether this same title format should be carried into the next pass for normal news posts.

## Implementation note

When approved, enter title and description values through the active Jetpack SEO fields (`jetpack_seo_html_title` and `advanced_seo_description`). Rank Math is installed but inactive, so it should not be used for this pass.
