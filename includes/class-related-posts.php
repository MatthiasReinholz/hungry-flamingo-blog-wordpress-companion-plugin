<?php
/**
 * Related-post selection and rendering.
 *
 * @package HFB_Companion
 */

declare( strict_types=1 );

namespace HFB_Companion;

defined( 'ABSPATH' ) || exit;

final class Related_Posts {

	/**
	 * @param int[] $exclude Post IDs to exclude.
	 * @return int[]
	 */
	public function ids( int $post_id, int $count = 3, array $exclude = array() ): array {
		$post = get_post( $post_id );
		if ( ! Public_Posts::is_public_post( $post ) ) {
			return array();
		}

		$count     = max( 1, min( 12, $count ) );
		$exclude   = array_values( array_unique( array_filter( array_map( 'absint', array_merge( array( $post_id ), $exclude ) ) ) ) );
		$collected = array();

		$categories = wp_get_post_categories( $post_id );
		if ( $categories ) {
			$related = $this->query_candidates(
				array(
					'category__in' => array_map( 'absint', $categories ),
				),
				$count,
				array_merge( $exclude, $collected )
			);

			$collected = array_merge( $collected, $related );
		}

		if ( count( $collected ) < $count ) {
			$tags = wp_get_post_tags( $post_id, array( 'fields' => 'ids' ) );
			if ( $tags ) {
				$related = $this->query_candidates(
					array(
						'tag__in' => array_map( 'absint', $tags ),
					),
					$count - count( $collected ),
					array_merge( $exclude, $collected )
				);

				$collected = array_merge( $collected, $related );
			}
		}

		if ( count( $collected ) < $count ) {
			$fill_exclude = array_values( array_unique( array_merge( $exclude, $collected ) ) );
			$fill         = $this->query_candidates( array(), $count - count( $collected ), $fill_exclude );

			$collected = array_merge( $collected, $fill );
		}

		return array_slice( array_values( array_unique( $collected ) ), 0, $count );
	}

	/**
	 * @param array<string,mixed> $args Rendering options.
	 */
	public function render( \WP_Post $post, array $args = array() ): string {
		if ( ! Public_Posts::is_public_post( $post ) ) {
			return '';
		}

		$count        = max( 1, min( 12, (int) ( $args['count'] ?? 3 ) ) );
		$heading      = isset( $args['heading'] ) ? (string) $args['heading'] : __( 'Read next', 'hungry-flamingo-blog-companion' );
		$show_excerpt = isset( $args['show_excerpt'] ) ? (bool) $args['show_excerpt'] : true;
		$ids          = $this->ids( (int) $post->ID, $count );

		if ( ! $ids ) {
			return '';
		}

		ob_start();
		?>
		<section class="hfb-related-posts" aria-labelledby="hfb-related-posts-<?php echo (int) $post->ID; ?>">
			<h2 id="hfb-related-posts-<?php echo (int) $post->ID; ?>" class="hfb-related-posts__title"><?php echo esc_html( $heading ); ?></h2>
			<div class="hfb-related-posts__grid">
				<?php foreach ( $ids as $related_id ) : ?>
					<?php echo $this->render_item( $related_id, $show_excerpt ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endforeach; ?>
			</div>
		</section>
		<?php

		return (string) ob_get_clean();
	}

	private function render_item( int $post_id, bool $show_excerpt ): string {
		$post = get_post( $post_id );
		if ( ! Public_Posts::is_public_post( $post ) ) {
			return '';
		}

		$title     = get_the_title( $post );
		$permalink = get_permalink( $post );
		$excerpt   = wp_strip_all_tags( get_the_excerpt( $post ) );
		$reading   = Reading_Time::for_post( $post );

		ob_start();
		?>
		<article class="hfb-related-posts__item">
			<?php if ( has_post_thumbnail( $post ) ) : ?>
				<a class="hfb-related-posts__thumb" href="<?php echo esc_url( $permalink ); ?>" aria-hidden="true" tabindex="-1">
					<?php echo wp_kses_post( get_the_post_thumbnail( $post, 'medium_large', array( 'loading' => 'lazy' ) ) ); ?>
				</a>
			<?php endif; ?>
			<div class="hfb-related-posts__body">
				<h3><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h3>
				<p class="hfb-related-posts__meta">
					<time datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>"><?php echo esc_html( get_the_date( '', $post ) ); ?></time>
					<span aria-hidden="true">·</span>
					<?php
					printf(
						/* translators: %d: estimated reading time in minutes. */
						esc_html__( '%d min read', 'hungry-flamingo-blog-companion' ),
						(int) $reading
					);
					?>
				</p>
				<?php if ( $show_excerpt && $excerpt ) : ?>
					<p class="hfb-related-posts__excerpt"><?php echo esc_html( $excerpt ); ?></p>
				<?php endif; ?>
			</div>
		</article>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * @param array<string,mixed> $query_args Additional WP_Query args.
	 * @param int[]               $exclude    Post IDs to exclude after fetching.
	 * @return int[]
	 */
	private function query_candidates( array $query_args, int $count, array $exclude ): array {
		$count   = max( 1, min( 12, $count ) );
		$exclude = array_values( array_unique( array_filter( array_map( 'absint', $exclude ) ) ) );
		$posts   = get_posts(
			array_merge(
				array(
					'posts_per_page'         => min( 50, $count + count( $exclude ) ),
					'post_type'              => 'post',
					'post_status'            => 'publish',
					'has_password'           => false,
					'ignore_sticky_posts'    => true,
					'orderby'                => 'date',
					'order'                  => 'DESC',
					'no_found_rows'          => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
				),
				$query_args
			)
		);

		$ids = array_map(
			static function ( \WP_Post $post ): int {
				return absint( $post->ID );
			},
			$posts
		);
		return array_slice( array_values( array_diff( $ids, $exclude ) ), 0, $count );
	}
}
