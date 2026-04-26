# Repository Instructions for AI Coding Agents

## Scope

This repository is the Hungry Flamingo Blog Companion plugin. It owns functionality that belongs outside the theme: the continuous-reading stack, the public next-posts REST endpoint, and the optional `hfb/post-stack` dynamic block.

The companion theme lives at:

`/Users/matthias/DEV/wordpress/themes/hungry-flamingo-blog-wordpress-theme`

## Architecture Notes

- This child repository is managed with `wp-plugin-base`; run the foundation sync before validation when foundation-managed files may be stale.
- Keep custom blocks and REST endpoints in the plugin, not in the theme.
- The public REST endpoint may expose only already-public, published, non-password-protected `post` objects.
- Do not add WooCommerce product/cart/checkout/order behavior here. The plugin auto-append path must remain limited to singular `post` content.
- Cache selected post IDs, not rendered HTML, so filters that produce request-specific markup cannot leak through a shared transient or object cache.
- Do not add tracking, remote assets, telemetry, or third-party calls without explicit opt-in and readme privacy documentation.
- Keep theme coupling soft through filters and documented behavior. The plugin should degrade outside the Hungry Flamingo Blog theme.

## Required Checks

Run these before handing off plugin changes:

```sh
bash .wp-plugin-base/scripts/update/sync_child_repo.sh
bash .wp-plugin-base/scripts/ci/validate_project.sh
bash .wp-plugin-base/scripts/ci/build_zip.sh
```

When checking whitespace, ignore vendored foundation files unless the task is specifically to patch the foundation:

```sh
git diff --cached --check -- . ':!.wp-plugin-base/**'
```

## Release Hygiene

- Keep `hungry-flamingo-blog-companion.php`, `readme.txt`, `README.md`, `CHANGELOG.md`, `LICENSE`, and `languages/hungry-flamingo-blog-companion.pot` aligned.
- Check the generated ZIP contents after foundation packaging.
- Do not patch `.wp-plugin-base` vendored files in this child repo unless the user explicitly asks for a local foundation fork. Prefer upstream fixes in `MatthiasReinholz/wp-plugin-base`.
