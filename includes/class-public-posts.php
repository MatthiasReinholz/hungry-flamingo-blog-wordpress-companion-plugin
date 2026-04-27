<?php
/**
 * Public post visibility helper.
 *
 * @package HFB_Companion
 */

declare( strict_types=1 );

namespace HFB_Companion;

defined( 'ABSPATH' ) || exit;

final class Public_Posts {

	public static function is_public_post( $post ): bool {
		if ( ! $post instanceof \WP_Post ) {
			return false;
		}

		return 'post' === $post->post_type
			&& 'publish' === $post->post_status
			&& '' === (string) $post->post_password
			&& ( ! function_exists( 'is_post_publicly_viewable' ) || is_post_publicly_viewable( $post ) );
	}
}
