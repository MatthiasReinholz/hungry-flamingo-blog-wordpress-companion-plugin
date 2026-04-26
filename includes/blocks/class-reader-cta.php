<?php
/**
 * Dynamic reader call-to-action block.
 *
 * @package HFB_Companion
 */

declare( strict_types=1 );

namespace HFB_Companion\Blocks;

defined( 'ABSPATH' ) || exit;

final class Reader_Cta {

	public function register(): void {
		add_action( 'init', [ $this, 'register_block' ] );
	}

	public function register_block(): void {
		register_block_type(
			'hfb/reader-cta',
			[
				'api_version'     => 2,
				'title'           => __( 'Reader CTA', 'hungry-flamingo-blog-companion' ),
				'description'     => __( 'Adds a provider-neutral post-end call-to-action slot.', 'hungry-flamingo-blog-companion' ),
				'category'        => 'widgets',
				'icon'            => 'megaphone',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'eyebrow'    => [
						'type'    => 'string',
						'default' => __( 'Keep reading', 'hungry-flamingo-blog-companion' ),
					],
					'title'      => [
						'type'    => 'string',
						'default' => __( 'Find the next useful article', 'hungry-flamingo-blog-companion' ),
					],
					'body'       => [
						'type'    => 'string',
						'default' => __( 'Use this slot for a local editorial prompt, a series landing page, or an RSS follow link.', 'hungry-flamingo-blog-companion' ),
					],
					'buttonText' => [
						'type'    => 'string',
						'default' => __( 'Browse articles', 'hungry-flamingo-blog-companion' ),
					],
					'url'        => [
						'type'    => 'string',
						'default' => '',
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
		$eyebrow     = isset( $attributes['eyebrow'] ) ? wp_strip_all_tags( (string) $attributes['eyebrow'] ) : '';
		$title       = isset( $attributes['title'] ) ? wp_strip_all_tags( (string) $attributes['title'] ) : '';
		$body        = isset( $attributes['body'] ) ? wp_strip_all_tags( (string) $attributes['body'] ) : '';
		$button_text = isset( $attributes['buttonText'] ) ? wp_strip_all_tags( (string) $attributes['buttonText'] ) : '';
		$url         = isset( $attributes['url'] ) ? esc_url_raw( (string) $attributes['url'] ) : '';
		$url         = $url ?: get_post_type_archive_link( 'post' );
		$url         = $url ?: home_url( '/' );

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
