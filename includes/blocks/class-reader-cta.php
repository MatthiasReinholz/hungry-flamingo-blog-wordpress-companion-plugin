<?php
/**
 * Dynamic reader call-to-action block.
 *
 * @package HFB_Companion
 */

declare( strict_types=1 );

namespace HFB_Companion\Blocks;

use HFB_Companion\Assets;

defined( 'ABSPATH' ) || exit;

final class Reader_Cta {

	public function register(): void {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	public function register_block(): void {
		register_block_type_from_metadata(
			HFB_COMPANION_DIR . 'blocks/reader-cta',
			array(
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/**
	 * @param array<string,mixed> $attributes Block attributes.
	 */
	public function render( array $attributes ): string {
		Assets::enqueue_block_styles();

		$eyebrow     = array_key_exists( 'eyebrow', $attributes ) ? wp_strip_all_tags( (string) $attributes['eyebrow'] ) : __( 'Keep reading', 'hungry-flamingo-blog-companion' );
		$title       = array_key_exists( 'title', $attributes ) ? wp_strip_all_tags( (string) $attributes['title'] ) : __( 'Find the next useful article', 'hungry-flamingo-blog-companion' );
		$body        = array_key_exists( 'body', $attributes ) ? wp_strip_all_tags( (string) $attributes['body'] ) : __( 'Use this slot for a local editorial prompt, a series landing page, or an RSS follow link.', 'hungry-flamingo-blog-companion' );
		$button_text = array_key_exists( 'buttonText', $attributes ) ? wp_strip_all_tags( (string) $attributes['buttonText'] ) : __( 'Browse articles', 'hungry-flamingo-blog-companion' );
		$url         = isset( $attributes['url'] ) ? esc_url_raw( (string) $attributes['url'] ) : '';
		$url         = '' !== $url ? $url : get_post_type_archive_link( 'post' );
		$url         = $url ? $url : home_url( '/' );

		ob_start();
		?>
		<section class="hfb-reader-cta">
			<?php if ( $eyebrow ) : ?>
				<p class="hfb-reader-cta__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<?php if ( $title ) : ?>
				<h2><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>
			<?php if ( $body ) : ?>
				<p><?php echo esc_html( $body ); ?></p>
			<?php endif; ?>
			<?php if ( $button_text ) : ?>
				<a class="hfb-reader-cta__button" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $button_text ); ?></a>
			<?php endif; ?>
		</section>
		<?php

		return (string) ob_get_clean();
	}
}
