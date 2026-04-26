# Changelog

## 1.0.0

- Added tag-push release automation so beta tags create or repair GitHub prereleases with installable ZIP assets.
- Hardened prerelease publication so stable tags stay owned by the release PR flow and prerelease tags must come from trusted history.
- Fixed continuous-reading URL restoration when scrolling back to the source post.
- Added continuous-reading fallback selection when older posts are exhausted.
- Added related-post and reader-CTA dynamic blocks, a local reading progress indicator, and a Tools-screen editorial report.
- Initial beta release.
- Added continuous-reading stack behavior.
- Added `hfb/post-stack` dynamic block.
- Added public next-posts REST endpoint with published-post validation.
