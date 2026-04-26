<?php
/**
 * Renders continuous-reading stack placeholders.
 *
 * @package HFB_Companion
 */

declare( strict_types=1 );

namespace HFB_Companion;

defined( 'ABSPATH' ) || exit;

final class Stack_Renderer {

	/**
	 * @var array<int,bool>
	 */
	private static $rendered_post_ids = [];

	public function render_placeholders( \WP_Post $post, int $stack_size ): string {
		if ( ! $this->is_public_post( $post ) ) {
			return '';
		}

		$post_id = (int) $post->ID;
		if ( self::has_rendered_for_post( $post_id ) ) {
			return '';
		}

		$stack_size   = max( 1, min( 10, $stack_size ) );
		$placeholders = max( 0, $stack_size - 1 );

		if ( 0 === $placeholders ) {
			return '';
		}

		Assets::enqueue_continuous_reading( $post_id );
		self::$rendered_post_ids[ $post_id ] = true;

		ob_start();
		?>
		<div class="hfb-post-stack"
			data-hfb-stack
			data-stack-size="<?php echo (int) $stack_size; ?>"
			data-source-id="<?php echo (int) $post->ID; ?>">
			<div class="hfb-post-stack__items">
				<?php for ( $i = 1; $i <= $placeholders; $i++ ) : ?>
					<section class="hfb-post-stack__slot hfb-post-stack__slot--skeleton" data-hfb-slot="<?php echo (int) $i; ?>" aria-busy="true" aria-label="<?php echo esc_attr__( 'Loading next article', 'hungry-flamingo-blog-companion' ); ?>">
						<div class="hfb-post-stack__slot-skeleton" aria-hidden="true">
							<div class="skeleton skeleton--tag"></div>
							<div class="skeleton skeleton--title"></div>
							<div class="skeleton skeleton--line"></div>
							<div class="skeleton skeleton--line"></div>
							<div class="skeleton skeleton--line short"></div>
						</div>
					</section>
				<?php endfor; ?>
			</div>
		</div>
		<?php

			return (string) ob_get_clean();
	}

	public static function has_rendered_for_post( int $post_id ): bool {
		$post_id = absint( $post_id );
		return $post_id > 0 && ! empty( self::$rendered_post_ids[ $post_id ] );
	}

	private function is_public_post( \WP_Post $post ): bool {
		return 'post' === $post->post_type
			&& 'publish' === $post->post_status
			&& '' === (string) $post->post_password
			&& is_post_publicly_viewable( $post );
	}
}
