=== Hungry Flamingo Blog Companion ===
Contributors: matthiasreinholz
Requires at least: 6.4
Tested up to: 6.9
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Tags: blog, blocks, full-site-editing, reading, editorial

Companion functionality for the Hungry Flamingo Blog theme.

== Description ==

Hungry Flamingo Blog Companion contains functionality that should live outside the theme: the continuous-reading post stack, the public next-posts REST endpoint, optional reader-retention dynamic blocks with editor controls, a reading progress indicator, and a local editorial report.

The plugin is designed for the Hungry Flamingo Blog theme but keeps the feature portable so the theme itself can stay focused on design, templates, and presentation. Developer and AI coding-agent guidance is included in `AGENTS.md` in the source repository.

The plugin intentionally does not alter WooCommerce products, carts, checkout, orders, or account pages. Auto-append runs only on singular `post` content.

== Installation ==

1. Upload the installable release ZIP or extracted release directory to WordPress.
2. Activate Hungry Flamingo Blog Companion in Plugins.
3. Visit a single post. The plugin appends a continuous-reading stack after the post content.
4. Optional: insert the Related Posts or Reader CTA blocks in post templates or individual posts.

== Frequently Asked Questions ==

= Does this plugin require the Hungry Flamingo Blog theme? =

No. It is designed for that theme and inherits its visual language when both are active, but the REST endpoint and post-stack block are plugin-owned.

= What REST endpoint does it add? =

The plugin adds `/wp-json/hfb/v1/next-posts`. The endpoint is public, accepts `after`, `count`, and optional `seen[]` query parameters. `exclude[]` is accepted as a backwards-compatible alias for `seen[]`. The endpoint only returns already-public, published, non-password-protected `post` objects.

= Does this plugin change WooCommerce pages? =

No. WooCommerce compatibility belongs to the theme and WooCommerce itself. This plugin only works with public blog posts.

= Does it collect personal data? =

No. The plugin does not set cookies, track users, or make server-side calls to external services. The public REST endpoint returns rendered markup for public posts only, and its short-lived object-cache entry stores selected post IDs rather than rendered HTML. Rendered author avatars use WordPress' configured avatar system; on default WordPress installs, visitors' browsers may request avatar images from Gravatar.

= What does the editorial report do? =

Tools > Hungry Flamingo reviews recent published posts for word count, internal links, and related-post candidates. The report is local to WordPress and requires `manage_options`.

== Changelog ==

= 1.0.0 =

- Initial beta release.
- Added continuous reading, related-post and reader-CTA blocks, reading progress, and a local editorial report.
