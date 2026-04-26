# Continuous Reading REST Contract

The plugin exposes one public endpoint:

`GET /wp-json/hfb/v1/next-posts`

## Request Parameters

- `after` is required and must be the ID of a public, published, non-password-protected `post`.
- `count` is optional, defaults to the configured stack size minus one, and is clamped to `1..10`.
- `seen[]` is optional and accepts up to 100 post IDs already rendered by the client.
- `exclude[]` is accepted as a backwards-compatible alias for `seen[]`.

## Response Shape

```json
{
	"items": [
		{
			"id": 123,
			"permalink": "https://example.test/example-post/",
			"title": "Example post",
			"html": "<article>...</article>"
		}
	]
}
```

`items` contains only public blog posts. Password-protected posts, private posts, drafts, products, pages, and custom post types are excluded.

## Cache Behavior

The normal no-exclusion path caches selected post IDs in the WordPress object cache for a short period. Rendered HTML is not cached by this plugin. Cache entries are versioned and invalidated when posts are saved or deleted.

## Privacy Boundary

The endpoint does not set cookies, collect visitor identifiers, call external services, or expose private post data. Rendered author avatars use the site's configured WordPress avatar behavior; default WordPress installs may load Gravatar images in the visitor's browser.

The reading progress indicator and local editorial report do not store visitor data or call external services.

## WooCommerce Boundary

This endpoint and the auto-append integration are post-only. WooCommerce products, carts, checkout, accounts, and orders are out of scope for the companion plugin.
