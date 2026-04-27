<?php
/**
 * Adds the continuous-reading stack after core post content.
 *
 * @package HFB_Companion
 */

declare( strict_types=1 );

namespace HFB_Companion;

defined( 'ABSPATH' ) || exit;

final class Content_Integration {

	private $did_append = false;

	public function register(): void {
		add_filter( 'render_block', array( $this, 'append_stack_after_post_content' ), 20, 2 );
	}

	/**
	 * @param string              $block_content Rendered block HTML.
	 * @param array<string,mixed> $block         Block data.
	 */
	public function append_stack_after_post_content( string $block_content, array $block ): string {
		if ( $this->did_append || ( $block['blockName'] ?? '' ) !== 'core/post-content' ) {
			return $block_content;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return $block_content;
		}

		if ( ! is_singular( 'post' ) || ! in_the_loop() ) {
			return $block_content;
		}

		$post = get_post();
		if (
			! $post instanceof \WP_Post
			|| has_block( 'hfb/post-stack', $post )
			|| Stack_Renderer::has_rendered_for_post( (int) $post->ID )
			|| $this->template_contains_post_stack_block()
		) {
			return $block_content;
		}

		$should_append = apply_filters( 'hfb_companion_should_auto_append_stack', true, $post );
		if ( true !== $should_append ) {
			return $block_content;
		}

		$this->did_append = true;

		$renderer = new Stack_Renderer();
		return $block_content . $renderer->render_placeholders( $post, (int) HFB_COMPANION_STACK_SIZE );
	}

	private function template_contains_post_stack_block(): bool {
		static $contains = null;

		if ( null !== $contains ) {
			return $contains;
		}

		$contains = false;
		$slugs    = array( 'single-post', 'single', 'singular', 'index' );

		if ( function_exists( 'get_block_template' ) ) {
			foreach ( $slugs as $slug ) {
				$template = get_block_template( get_stylesheet() . '//' . $slug, 'wp_template' );
				if ( $template && ! empty( $template->content ) && has_block( 'hfb/post-stack', $template->content ) ) {
					$contains = true;
					break;
				}
			}
		}

		if ( ! $contains && function_exists( 'get_block_templates' ) ) {
			$template_parts = get_block_templates(
				array(
					'theme' => get_stylesheet(),
				),
				'wp_template_part'
			);

			foreach ( $template_parts as $template_part ) {
				if ( has_block( 'hfb/post-stack', $template_part->content ) ) {
					$contains = true;
					break;
				}
			}
		}

		if ( ! $contains ) {
			foreach ( $slugs as $slug ) {
				$template_path = trailingslashit( get_stylesheet_directory() ) . 'templates/' . $slug . '.html';
				if ( ! is_readable( $template_path ) ) {
					continue;
				}

				$template_content = (string) file_get_contents( $template_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				if ( has_block( 'hfb/post-stack', $template_content ) ) {
					$contains = true;
					break;
				}
			}
		}

		if ( ! $contains ) {
			$part_paths = glob( trailingslashit( get_stylesheet_directory() ) . 'parts/*.html' );
			if ( is_array( $part_paths ) ) {
				foreach ( $part_paths as $part_path ) {
					if ( ! is_readable( $part_path ) ) {
						continue;
					}

					$template_part_content = (string) file_get_contents( $part_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					if ( has_block( 'hfb/post-stack', $template_part_content ) ) {
						$contains = true;
						break;
					}
				}
			}
		}

		/**
		 * Allows integrations to override template-level post stack detection.
		 *
		 * @param bool $contains Whether the current theme template contains the stack block.
		 */
		$contains = (bool) apply_filters( 'hfb_companion_template_contains_post_stack_block', $contains );

		return $contains;
	}
}
