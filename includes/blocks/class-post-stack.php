<?php
/**
 * Optional dynamic block for manual post-stack placement.
 *
 * @package HFB_Companion
 */

declare( strict_types=1 );

namespace HFB_Companion\Blocks;

use HFB_Companion\Stack_Renderer;

defined( 'ABSPATH' ) || exit;

final class Post_Stack {

	public function register(): void {
		add_action( 'init', [ $this, 'register_block' ] );
	}

	public function register_block(): void {
		register_block_type(
			'hfb/post-stack',
			[
				'api_version'     => 2,
				'title'           => __( 'Post Stack', 'hungry-flamingo-blog-companion' ),
				'description'     => __( 'Appends the continuous-reading stack for the current post.', 'hungry-flamingo-blog-companion' ),
				'category'        => 'widgets',
				'icon'            => 'welcome-write-blog',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'stackSize' => [
						'type'    => 'number',
						'default' => (int) HFB_COMPANION_STACK_SIZE,
						'minimum' => 1,
						'maximum' => 10,
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
		$post = get_post();
		if ( ! $post instanceof \WP_Post ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return '<div class="hfb-post-stack hfb-post-stack--empty">'
					. esc_html__( 'Post Stack appears when viewing a public post.', 'hungry-flamingo-blog-companion' )
					. '</div>';
			}

			return '';
		}

		$stack_size = max( 1, min( 10, (int) ( $attributes['stackSize'] ?? HFB_COMPANION_STACK_SIZE ) ) );
		$renderer   = new Stack_Renderer();

		return $renderer->render_placeholders( $post, $stack_size );
	}
}
