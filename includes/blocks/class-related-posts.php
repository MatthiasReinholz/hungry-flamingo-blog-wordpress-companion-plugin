<?php
/**
 * Dynamic related-posts block.
 *
 * @package HFB_Companion
 */

declare( strict_types=1 );

namespace HFB_Companion\Blocks;

use HFB_Companion\Assets;
use HFB_Companion\Related_Posts as Related_Posts_Renderer;

defined( 'ABSPATH' ) || exit;

final class Related_Posts {

	public function register(): void {
		add_action( 'init', [ $this, 'register_block' ] );
	}

	public function register_block(): void {
		register_block_type(
			'hfb/related-posts',
			[
				'api_version'     => 2,
				'title'           => __( 'Related Posts', 'hungry-flamingo-blog-companion' ),
				'description'     => __( 'Shows local related articles for the current public post.', 'hungry-flamingo-blog-companion' ),
				'category'        => 'widgets',
				'icon'            => 'admin-links',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'heading'     => [
						'type'    => 'string',
						'default' => __( 'Read next', 'hungry-flamingo-blog-companion' ),
					],
					'count'       => [
						'type'    => 'number',
						'default' => 3,
						'minimum' => 1,
						'maximum' => 12,
					],
					'showExcerpt' => [
						'type'    => 'boolean',
						'default' => true,
					],
				],
				'supports'        => [
					'align' => [ 'wide', 'full' ],
				],
			]
		);
	}

	/**
	 * @param array<string,mixed> $attributes Block attributes.
	 */
	public function render( array $attributes ): string {
		Assets::enqueue_block_styles();

		if ( ! is_singular( 'post' ) ) {
			return current_user_can( 'edit_posts' )
				? '<div class="hfb-related-posts hfb-related-posts--empty">' . esc_html__( 'Related Posts appears on singular public posts.', 'hungry-flamingo-blog-companion' ) . '</div>'
				: '';
		}

		$post = get_post();
		if ( ! $post instanceof \WP_Post ) {
			return current_user_can( 'edit_posts' )
				? '<div class="hfb-related-posts hfb-related-posts--empty">' . esc_html__( 'Related Posts appears on singular public posts.', 'hungry-flamingo-blog-companion' ) . '</div>'
				: '';
		}

		$renderer = new Related_Posts_Renderer();
		$html     = $renderer->render(
			$post,
			[
				'heading'      => $attributes['heading'] ?? __( 'Read next', 'hungry-flamingo-blog-companion' ),
				'count'        => $attributes['count'] ?? 3,
				'show_excerpt' => $attributes['showExcerpt'] ?? true,
			]
		);

		if ( '' === $html && current_user_can( 'edit_posts' ) ) {
			return '<div class="hfb-related-posts hfb-related-posts--empty">' . esc_html__( 'No related posts found yet.', 'hungry-flamingo-blog-companion' ) . '</div>';
		}

		return $html;
	}
}
