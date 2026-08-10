# Frontend CSS metrics baseline

This is the theme-local record of compiled frontend CSS trend measurements.
It describes the global bundle only; block styles registered separately by
WordPress are outside these figures. Use the [CSS architecture
guide](../architecture/css.md) for ownership and change workflow.

## Command

Run from the theme repository:

```sh
pnpm compile:css
pnpm analyze:css
```

JSON output is available with `pnpm analyze:css:json`.

## Baseline

Target: `styles/dist/frontend.min.css`.

| Metric                             |        Baseline |
| ---------------------------------- | --------------: |
| Source lines of code               |             530 |
| File size                          |         12.1 KB |
| Rules                              |             113 |
| Selectors                          |             165 |
| Unique selectors                   |             134 |
| Selector uniqueness                |           81.2% |
| Declarations                       |             346 |
| Unique declarations                |             164 |
| Declaration uniqueness             |           47.4% |
| Compiled `!important` declarations |              25 |
| Average selectors per rule         |            1.46 |
| Maximum selectors per rule         |               7 |
| Average selector complexity        |            2.77 |
| Maximum selector complexity        |              14 |
| Average specificity                | 0.0 / 1.4 / 0.6 |
| Maximum specificity                |       1 / 1 / 0 |
| Media queries                      |              16 |
| Unique media queries               |               8 |
| Custom properties defined          |               9 |
| Vendor-prefixed declarations       |              12 |

## Limits

Wallace analyzes compiled CSS, so its `!important` count can differ from
authored-source counts and from block-owned styles loaded outside the bundle.
Use it for trend tracking, not as proof that a selector is unused. Deletion
still requires source/content searches, rendered-page coverage, and focused
visual regression tests.

## Trend log

| Date       | Batch                       | Rules | Selectors | Declarations | `!important` | Max specificity | Max complexity | Visual result                    |
| ---------- | --------------------------- | ----: | --------: | -----------: | -----------: | --------------- | -------------: | -------------------------------- |
| 2026-06-22 | After Batch 4               |   172 |       244 |          454 |           96 | 2 / 4 / 2       |             14 | 18 passed                        |
| 2026-06-22 | After Batch 5               |   167 |       238 |          449 |           93 | 2 / 4 / 2       |             14 | 21 passed                        |
| 2026-06-22 | After Batch 7               |   167 |       238 |          449 |           92 | 2 / 4 / 2       |             14 | 21 passed                        |
| 2026-06-22 | After Batch 8               |   165 |       236 |          445 |           90 | 2 / 4 / 2       |             14 | 21 passed                        |
| 2026-06-22 | After Batch 11              |   161 |       232 |          437 |           78 | 2 / 4 / 2       |             14 | 21 passed                        |
| 2026-06-22 | After Batch 12              |   161 |       232 |          437 |           71 | 2 / 4 / 2       |             14 | 21 passed                        |
| 2026-06-22 | After Batch 14              |   161 |       232 |          437 |           68 | 2 / 4 / 2       |             14 | 21 passed                        |
| 2026-06-23 | Design-system pivot refresh |   113 |       165 |          346 |           25 | 1 / 1 / 0       |             14 | Not run for metrics-only refresh |
