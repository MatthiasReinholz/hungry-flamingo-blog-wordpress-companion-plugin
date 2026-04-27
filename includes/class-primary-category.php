<?php
/**
 * Primary category helper.
 *
 * @package HFB_Companion
 */

declare( strict_types=1 );

namespace HFB_Companion;

defined( 'ABSPATH' ) || exit;

final class Primary_Category {

	public static function id_for_post( int $post_id ): int {
		$filtered = (int) apply_filters( 'hfb_companion_primary_category_id', 0, $post_id );
		if ( self::category_exists( $filtered ) ) {
			return $filtered;
		}

		$categories = wp_get_post_categories( $post_id, array( 'fields' => 'ids' ) );
		if ( ! is_array( $categories ) || array() === $categories ) {
			return 0;
		}

		return (int) $categories[0];
	}

	public static function term_for_post( int $post_id ): ?\WP_Term {
		$category_id = self::id_for_post( $post_id );
		if ( ! $category_id ) {
			return null;
		}

		$term = get_term( $category_id, 'category' );
		if ( $term instanceof \WP_Term ) {
			return $term;
		}

		return null;
	}

	private static function category_exists( int $term_id ): bool {
		if ( $term_id <= 0 ) {
			return false;
		}

		return (bool) term_exists( $term_id, 'category' );
	}
}
