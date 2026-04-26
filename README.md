# Hungry Flamingo Blog Companion

Companion plugin for the Hungry Flamingo Blog WordPress theme. It owns functionality that should not be bundled in a theme:

- Continuous-reading post stack.
- Public `/wp-json/hfb/v1/next-posts` REST endpoint.
- Optional `hfb/post-stack` dynamic block for manual placement.
- Share/copy behavior for stacked articles.
- Repository-specific AI coding-agent instructions in `AGENTS.md`.

## Requirements

- WordPress 6.4 or newer; tested through WordPress 6.9.
- PHP 8.2 or newer.

## Development

This repository is managed with `wp-plugin-base` v1.7.9.

```sh
bash .wp-plugin-base/scripts/update/sync_child_repo.sh
bash .wp-plugin-base/scripts/ci/validate_project.sh
bash .wp-plugin-base/scripts/ci/validate_wordpress_readiness.sh
```

## Release

The distributable ZIP is built through the foundation packaging flow and excludes development-only files.

```sh
bash .wp-plugin-base/scripts/ci/build_zip.sh
```

## Privacy and Data Exposure

The plugin does not set cookies, track users, or make server-side calls to external services. Its public REST endpoint returns rendered markup only for already-public, published, non-password-protected posts. The endpoint caches selected post IDs in the WordPress object cache for the normal no-exclusion request path; it does not write public REST responses to database transients and does not cache rendered HTML.

Rendered author avatars use WordPress' configured avatar system. On a default WordPress install, visitors' browsers may request avatar images from Gravatar when stacked articles include author avatars.

The endpoint accepts `after`, `count`, and optional `seen[]` query parameters. For compatibility with early pre-release builds, `exclude[]` is also accepted as an alias for `seen[]`.

## WooCommerce Boundary

The plugin intentionally does not alter WooCommerce products, carts, checkout, orders, or account pages. Auto-append runs only on singular `post` content; WooCommerce compatibility is owned by the theme and WooCommerce itself.

## REST Contract

See [docs/rest-contract.md](docs/rest-contract.md) for request parameters, response shape, cache behavior, and privacy boundaries.

## License

GNU General Public License v3. See [LICENSE](LICENSE).
