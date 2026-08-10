# Current Design Visual Reference

Date captured: 2026-06-23

These images are committed reference artifacts for the CSS design-system pivot.
They are not Playwright test baselines and should not be used as automatic
snapshot expectations.

## Routes

- `home-*`: `/`
- `mary-barbour-*`: `/herstories/mary-barbour/`
- `edu-giveaway-*`: `/edu-giveaway/`
- `shop-*`: `/shop/`

Each route has desktop, tablet, and mobile reference images.

The draft `/news/` route is intentionally excluded.

## How To Use

- Treat these images as the current accepted visual language.
- Use them to judge drift while rebuilding CSS around modern WordPress theming.
- Accept subtle visual changes only when they simplify the system or better
  express the intended design rhythm.
- Keep Playwright visual tests as the regression guard.
- Do not update Playwright baselines casually; update them only after an
  intentional visual decision.
