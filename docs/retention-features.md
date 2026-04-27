# Reader Retention Features

Hungry Flamingo Blog Companion keeps retention features local to WordPress.

## Continuous Reading

The plugin appends a continuous-reading stack to singular public posts. The public REST endpoint returns only already-public, published, non-password-protected posts.

## Related Posts

The `hfb/related-posts` dynamic block renders local related articles for the current post. It uses shared categories and tags first, then falls back to recent public posts.

## Reader CTA

The `hfb/reader-cta` dynamic block provides a provider-neutral post-end call-to-action slot. It does not submit forms, store subscribers, or call external services.

## Reading Progress

The front-end script adds a visual reading progress bar on singular public posts. It stores nothing and sends no events.

## Editorial Report

Tools > Hungry Flamingo shows a local report for recent published posts. It counts words, internal links, and related-post candidates without sending data outside WordPress.
