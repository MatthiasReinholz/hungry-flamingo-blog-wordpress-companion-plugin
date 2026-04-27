<?php
declare(strict_types=1);

/**
 * Child-owned PHPUnit bootstrap overlay.
 *
 * This file is loaded from tests/bootstrap.php before the managed plugin load
 * hook runs. In WP test mode it is loaded after includes/functions.php, so
 * tests_add_filter() and similar helpers are available for repo-specific test
 * hooks or optional integration bootstrap code. The managed bootstrap scope
 * exposes $plugin_file and $tests_dir when you need them.
 */

if ( ! class_exists( 'WP_Post' ) ) {
	class WP_Post {
		public int $ID = 0;
		public string $post_type = 'post';
		public string $post_status = 'publish';
		public string $post_password = '';
		public string $post_content = '';
		public int $post_author = 1;

		public function __construct( $post = [] ) {
			foreach ( (array) $post as $key => $value ) {
				$this->{$key} = $value;
			}
		}
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( $maybeint ): int {
		return abs( (int) $maybeint );
	}
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
	function wp_strip_all_tags( $text, $remove_breaks = false ): string {
		unset( $remove_breaks );

		return trim( strip_tags( (string) $text ) );
	}
}

if ( ! function_exists( 'is_post_publicly_viewable' ) ) {
	function is_post_publicly_viewable( $post ): bool {
		return $post instanceof WP_Post
			&& 'post' === $post->post_type
			&& 'publish' === $post->post_status
			&& '' === (string) $post->post_password;
	}
}
