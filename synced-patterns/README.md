# Synced Pattern Fixtures

These files are theme-owned snapshots of native WordPress synced patterns
(`wp_block` posts). They are not registered as normal theme block patterns.

When the theme is activated, missing synced patterns are created automatically
from these fixtures. Existing synced patterns with matching slugs are kept.

The uninstall/switch-away policy is controlled in WordPress admin under:

`Appearance > PNS Theme Setup`

The default is to keep synced patterns/templates. If clean uninstall is selected,
the synced patterns listed in `manifest.json` are deleted when the theme is
switched away.

The theme lifecycle hooks create missing synced patterns on activation while
keeping existing synced patterns with matching slugs.

The manifest does not contain source database IDs; synced patterns are matched
by slug.
