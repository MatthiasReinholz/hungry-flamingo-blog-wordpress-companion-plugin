<!-- wp-plugin-base:agents-start -->
# Agent Operating Contract

This project consumes `wp-plugin-base` as vendored foundation source under `.wp-plugin-base/`.

## Working Rules

- Treat `.wp-plugin-base/` as generated/vendor foundation code. Prefer fixing reusable behavior upstream in `wp-plugin-base`, then resync this project.
- Managed files are listed by `bash .wp-plugin-base/scripts/ci/list_managed_files.sh --mode validate`. Do not permanently patch those files in place unless you are intentionally diverging from the foundation.
- Project-specific test bootstrap code belongs in `tests/wp-plugin-base/bootstrap-child.php`, not in managed `tests/bootstrap.php`.
- Public endpoint suppressions belong in `.wp-plugin-base-security-suppressions.json` and must keep the exact scanner-reported `kind`, `identifier`, and repo-relative `path`.

## Validation

After foundation or template updates, run:

```bash
bash .wp-plugin-base/scripts/update/sync_child_repo.sh
bash .wp-plugin-base/scripts/ci/validate_project.sh
```

For release changes, also run the release preparation workflow or local release checks documented in `CONTRIBUTING.md` before merging.
<!-- wp-plugin-base:agents-end -->

## Project Scope

This repository is the Hungry Flamingo Blog companion plugin. It owns functionality that belongs outside the theme:

- continuous-reading post stack and `hfb/post-stack` dynamic block
- public `/wp-json/hfb/v1/next-posts` REST endpoint for already-public published posts
- optional `hfb/related-posts` and `hfb/reader-cta` dynamic blocks
- reading progress and share/copy front-end enhancements
- local Tools-screen editorial report
- block metadata and small editor inspector controls for plugin-owned dynamic blocks

The companion theme lives at:

`/Users/matthias/DEV/wordpress/themes/hungry-flamingo-blog-wordpress-theme`

Keep presentation-only templates, WooCommerce styling, block patterns, and theme support declarations in the theme. Keep custom blocks, REST endpoints, durable data, forms, analytics, newsletter capture, and admin tools in this plugin or another plugin.

## Privacy And Security Boundaries

- Do not add tracking, analytics beacons, remote font loading, or third-party service calls without updating `README.md`, `readme.txt`, and the relevant docs.
- The public REST endpoint must only expose public, published, non-password-protected `post` objects.
- REST parameters must stay bounded and sanitized. Public permission callbacks must remain documented and intentionally narrow.
- Admin screens must stay capability-gated. Do not add admin actions without nonce and capability checks.
- The plugin intentionally does not alter WooCommerce products, carts, checkout, orders, or account pages.

## Project Working Rules

- For child-only changes, `git diff --name-only -- .wp-plugin-base .wp-plugin-base-security-pack` should be empty before staging. If it is not empty, confirm the task is an intentional foundation update.
- Keep child-owned PHPUnit/bootstrap additions under `tests/wp-plugin-base/`.
- Keep block metadata in `blocks/*/block.json` aligned with PHP render callbacks and `assets/js/blocks.js`.
- Local PHP CLIs with `memory_limit=128M` can fail PHPStan before analysis completes. Keep `PHPSTAN_MEMORY_LIMIT=1G` in `.wp-plugin-base.env` rather than weakening the quality pack.

Before handing off any plugin change, run:

```bash
bash .wp-plugin-base/scripts/ci/validate_project.sh
bash .wp-plugin-base/scripts/ci/validate_wordpress_readiness.sh
bash .wp-plugin-base/scripts/ci/build_zip.sh
```
