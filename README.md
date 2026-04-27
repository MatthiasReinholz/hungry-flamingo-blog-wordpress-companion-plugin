# Hungry Flamingo Blog Companion

Companion plugin for the Hungry Flamingo Blog WordPress theme. It owns functionality that should not be bundled in a theme:

- Continuous-reading post stack.
- Public `/wp-json/hfb/v1/next-posts` REST endpoint.
- Optional `hfb/post-stack` dynamic block for manual placement.
- Optional `hfb/related-posts` and `hfb/reader-cta` dynamic blocks.
- Block metadata and editor inspector controls for the companion blocks.
- Local reading progress indicator on singular public posts.
- Tools-screen editorial report for internal links and related-post candidates.
- Share/copy behavior for stacked articles.
- Repository-specific AI coding-agent instructions in `AGENTS.md`.

## Requirements

- WordPress 6.4 or newer; tested through WordPress 6.9.
- PHP 8.2 or newer.

## Development

This repository is managed with `wp-plugin-base` v1.7.11.

```sh
bash .wp-plugin-base/scripts/update/sync_child_repo.sh
bash .wp-plugin-base/scripts/ci/validate_project.sh
bash .wp-plugin-base/scripts/ci/validate_wordpress_readiness.sh
```

Readiness validation includes the managed WordPress quality pack: PHP syntax, PHPCS/WPCS, PHPStan, PHPUnit bootstrap tests, Plugin Check, the security pack, and strict PHP runtime smoke checks across PHP 8.2, 8.3, and 8.4.

This project sets `PHPSTAN_MEMORY_LIMIT=1G` in `.wp-plugin-base.env` because PHPStan's WordPress stubs can exceed a local PHP CLI's default `128M` memory cap even for this small plugin.

## Release

The distributable ZIP is built through the foundation packaging flow and excludes development-only files.

```sh
bash .wp-plugin-base/scripts/ci/build_zip.sh
```

Stable releases should use the managed `prepare-release` and `finalize-release` workflows from `wp-plugin-base`. Beta releases can use prerelease semver tags such as `v1.0.0-beta.1`; the `publish-tag-release` workflow creates or repairs the GitHub prerelease and attaches the installable ZIP, SBOM, and Sigstore bundle so prerelease tags do not remain tag-only. Stable `x.y.z` tags are published by the release PR/finalize flow, not by tag push.

Prerelease tags package the matching stable WordPress metadata version. For example, `v1.0.0-beta.1` installs as plugin version `1.0.0` while GitHub marks the artifact as a prerelease for tester distribution.

Install the verified release ZIP or the extracted release directory in WordPress. The source repository root contains development tooling and foundation metadata and should not be installed directly on production sites.

## Privacy and Data Exposure

The plugin does not set cookies, track users, or make server-side calls to external services. Its public REST endpoint returns rendered markup only for already-public, published, non-password-protected posts. The endpoint caches selected post IDs in the WordPress object cache for the normal no-exclusion request path; it does not write public REST responses to database transients and does not cache rendered HTML.

Rendered author avatars use WordPress' configured avatar system. On a default WordPress install, visitors' browsers may request avatar images from Gravatar when stacked articles include author avatars.

The endpoint accepts `after`, `count`, and optional `seen[]` query parameters. For compatibility with early pre-release builds, `exclude[]` is also accepted as an alias for `seen[]`.

See [docs/retention-features.md](docs/retention-features.md) for the first-party retention features.

## WooCommerce Boundary

The plugin intentionally does not alter WooCommerce products, carts, checkout, orders, or account pages. Auto-append runs only on singular `post` content; WooCommerce compatibility is owned by the theme and WooCommerce itself.

## REST Contract

See [docs/rest-contract.md](docs/rest-contract.md) for request parameters, response shape, cache behavior, and privacy boundaries.

## License

GNU General Public License v3. See [LICENSE](LICENSE).
