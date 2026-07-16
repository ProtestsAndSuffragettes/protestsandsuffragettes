# Navigation Fixtures

These files define the default `wp_navigation` records used by the standalone
theme. Templates reference the records by stable slug through `pnsRefSlug`; the
theme resolves the current numeric IDs at render time.

Missing records are created from these fixtures by the theme lifecycle hooks.

Primary submenu overview links are saved Navigation content. Keep the first
child overview links in `primary.html` aligned with the live
`pns-primary-navigation` record. The former PHP submenu overview bridge was
removed after the saved Navigation content proved stable; rollback should use
the backed-up Navigation record or this fixture, not render-time markup surgery.
