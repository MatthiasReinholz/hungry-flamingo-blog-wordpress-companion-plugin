<?php
/**
 * REST endpoint for continuous reading.
 *
 * @package HFB_Companion
 */

declare( strict_types=1 );

namespace HFB_Companion;

defined( 'ABSPATH' ) || exit;

final class Continuous_Reading {

	private const NS    = 'hfb/v1';
	private const ROUTE = '/next-posts';

	public function register(): void {
		add_action( 'rest_api_init', [ $this, 'register_route' ] );
		add_action( 'save_post_post', [ $this, 'invalidate_cache' ] );
		add_action( 'deleted_post', [ $this, 'invalidate_cache' ] );
	}

	public function register_route(): void {
		register_rest_route(
			self::NS,
			self::ROUTE,
			[
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => [ $this, 'permission_check' ],
				'args'                => [
					'after'   => [
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					],
					'count'   => [
						'required'          => false,
						'type'              => 'integer',
						'default'           => (int) HFB_COMPANION_STACK_SIZE - 1,
						'minimum'           => 1,
						'maximum'           => 10,
						'sanitize_callback' => 'absint',
					],
					'seen'    => [
						'required'          => false,
						'type'              => 'array',
						'default'           => [],
						'items'             => [
							'type'    => 'integer',
							'minimum' => 1,
						],
						'maxItems'          => 100,
						'sanitize_callback' => static function ( $value ): array {
							return array_slice( array_map( 'absint', (array) $value ), 0, 100 );
						},
					],
				],
				'callback'            => [ $this, 'handle' ],
			]
		);
	}

	/**
	 * This endpoint is intentionally public. The request handler still validates
	 * that the source and returned posts are public, published blog posts.
	 */
	public function permission_check(): bool {
		return true;
	}

	/**
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle( \WP_REST_Request $request ) {
		$after  = (int) $request->get_param( 'after' );
		$count  = max( 1, min( 10, (int) $request->get_param( 'count' ) ) );
		$seen   = $request->get_param( 'seen' );
		$seen   = null !== $seen ? $seen : $request->get_param( 'exclude' );
		$seen   = array_slice( array_map( 'absint', (array) $seen ), 0, 100 );
		$source = get_post( $after );

		if ( ! $this->is_public_post( $source ) ) {
			return new \WP_Error( 'hfb_companion_invalid_post', __( 'Unknown source post.', 'hungry-flamingo-blog-companion' ), [ 'status' => 404 ] );
		}

		$ids = null;
		if ( [] === $seen ) {
			$cache_version = (int) get_option( 'hfb_companion_cr_cache_ver', 0 );
			$cache_key     = 'hfb_companion_cr_ids_' . $cache_version . '_' . md5( (string) wp_json_encode( [ $after, $count ] ) );
			$cache_group   = 'hfb_companion';
			$cache_ttl     = 5 * MINUTE_IN_SECONDS;
			$cached        = wp_cache_get( $cache_key, $cache_group );

			if ( is_array( $cached ) ) {
				$ids = array_values( array_filter( array_map( 'absint', $cached ) ) );
			}
		}

		if ( null === $ids ) {
			$excluded = array_unique( array_merge( [ $after ], $seen ) );
			$ids      = $this->pick_posts( $after, $count, $excluded );

			if ( [] === $seen ) {
				wp_cache_set( $cache_key, $ids, $cache_group, $cache_ttl );
			}
		}

		$items = [];

		foreach ( $ids as $id ) {
			$items[] = $this->serialize_post( $id );
		}

		$payload = [ 'items' => array_values( array_filter( $items ) ) ];

		return rest_ensure_response( $payload );
	}

	public function invalidate_cache( $post_id ): void {
		unset( $post_id );

		if ( function_exists( 'wp_cache_flush_group' ) ) {
			wp_cache_flush_group( 'hfb_companion' );
		}

		update_option( 'hfb_companion_cr_cache_ver', time() );
	}

	/**
	 * @param int[] $excluded Post IDs already seen by the client.
	 * @return int[]
	 */
	private function pick_posts( int $source_id, int $count, array $excluded ): array {
		$collected        = [];
		$primary_category = $this->primary_category( $source_id );
		$source_date      = get_the_date( 'Y-m-d H:i:s', $source_id );

		if ( $primary_category ) {
			$same = get_posts(
				[
					'category'               => $primary_category,
					'posts_per_page'         => $this->query_limit( $count, $excluded ),
					'post_status'            => 'publish',
					'has_password'           => false,
					'ignore_sticky_posts'    => true,
					'fields'                 => 'ids',
					'orderby'                => 'date',
					'order'                  => 'DESC',
					'date_query'             => [
						[
							'before' => $source_date,
						],
					],
					'no_found_rows'          => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
				]
			);

			$collected = array_merge( $collected, $this->remove_excluded( $same, $excluded, $count ) );
		}

		if ( count( $collected ) < $count ) {
			$need    = $count - count( $collected );
			$exclude = array_unique( array_merge( $excluded, $collected ) );
			$fill    = get_posts(
				[
					'posts_per_page'         => $this->query_limit( $need, $exclude ),
					'post_status'            => 'publish',
					'has_password'           => false,
					'ignore_sticky_posts'    => true,
					'fields'                 => 'ids',
					'orderby'                => 'date',
					'order'                  => 'DESC',
					'date_query'             => [
						[
							'before' => $source_date,
						],
					],
					'no_found_rows'          => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
				]
			);

			$collected = array_merge( $collected, $this->remove_excluded( $fill, $exclude, $need ) );
		}

		return array_slice( array_values( array_unique( $collected ) ), 0, $count );
	}

	/**
	 * @param int[] $excluded Post IDs already seen by the client.
	 */
	private function query_limit( int $count, array $excluded ): int {
		return min( 110, max( $count, $count + count( $excluded ) ) );
	}

	/**
	 * @param int[] $ids      Candidate post IDs.
	 * @param int[] $excluded Post IDs already seen by the client.
	 * @return int[]
	 */
	private function remove_excluded( array $ids, array $excluded, int $count ): array {
		$ids = array_map( 'absint', $ids );
		return array_slice( array_values( array_diff( $ids, $excluded ) ), 0, $count );
	}

	private function primary_category( int $post_id ): int {
		$yoast_primary = (int) get_post_meta( $post_id, '_yoast_wpseo_primary_category', true );
		if ( $yoast_primary && term_exists( $yoast_primary, 'category' ) ) {
			return $yoast_primary;
		}

		$categories = wp_get_post_categories( $post_id );
		return $categories ? (int) $categories[0] : 0;
	}

	/**
	 * @return array<string,mixed>|null
	 */
	private function serialize_post( int $post_id ): ?array {
		$post = get_post( $post_id );
		if ( ! $this->is_public_post( $post ) ) {
			return null;
		}

		$previous_post = $GLOBALS['post'] ?? null;
		$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );

		try {
			$renderer = new Article_Renderer();
			$html     = $renderer->render( $post, [ 'context' => 'stack' ] );

			return [
				'id'        => $post_id,
				'permalink' => get_permalink( $post_id ),
				'title'     => get_the_title( $post_id ),
				'html'      => $html,
			];
		} catch ( \Throwable $throwable ) {
			do_action( 'hfb_companion_continuous_reading_render_failed', $post_id, $throwable );
			return null;
		} finally {
			wp_reset_postdata();

			if ( null !== $previous_post ) {
				$GLOBALS['post'] = $previous_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $previous_post );
			} else {
				unset( $GLOBALS['post'] );
			}
		}
	}

	private function is_public_post( $post ): bool {
		if ( ! $post instanceof \WP_Post ) {
			return false;
		}

		return 'post' === $post->post_type
			&& 'publish' === $post->post_status
			&& '' === (string) $post->post_password
			&& is_post_publicly_viewable( $post );
	}
}
