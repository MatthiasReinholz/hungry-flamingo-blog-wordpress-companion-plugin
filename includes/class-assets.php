<?php
/**
 * Companion asset registration and enqueue helpers.
 *
 * @package HFB_Companion
 */

declare( strict_types=1 );

namespace HFB_Companion;

defined( 'ABSPATH' ) || exit;

final class Assets {

	private static $localized_post_id = 0;

	public function register(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'maybe_enqueue_continuous_reading' ], 20 );
	}

	public function register_assets(): void {
		wp_register_style(
			'hfb-companion-continuous-reading',
			HFB_COMPANION_URL . 'assets/css/continuous-reading.css',
			[],
			$this->asset_version( 'assets/css/continuous-reading.css' )
		);

		wp_register_script(
			'hfb-companion-continuous-reading',
			HFB_COMPANION_URL . 'assets/js/continuous-reading.js',
			[],
			$this->asset_version( 'assets/js/continuous-reading.js' ),
			true
		);
	}

	public static function enqueue_continuous_reading( int $post_id ): void {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return;
		}

		wp_enqueue_style( 'hfb-companion-continuous-reading' );
		wp_enqueue_script( 'hfb-companion-continuous-reading' );

		if ( self::$localized_post_id === $post_id ) {
			return;
		}

		wp_localize_script(
			'hfb-companion-continuous-reading',
			'HFB_CR',
			[
				'endpoint'  => esc_url_raw( rest_url( 'hfb/v1/next-posts' ) ),
				'postId'    => $post_id,
				'title'     => wp_strip_all_tags( get_the_title( $post_id ) ),
				'stackSize' => (int) HFB_COMPANION_STACK_SIZE,
				'strings'   => [
					'linkCopied' => __( 'Link copied', 'hungry-flamingo-blog-companion' ),
				],
			]
		);

		self::$localized_post_id = $post_id;
	}

	public function maybe_enqueue_continuous_reading(): void {
		if ( ! is_singular( 'post' ) ) {
			return;
		}

		$post = get_queried_object();
		if ( ! $post instanceof \WP_Post || ! $this->is_public_post( $post ) ) {
			return;
		}

		self::enqueue_continuous_reading( (int) $post->ID );
	}

	private function asset_version( string $path ): string {
		$full_path = HFB_COMPANION_DIR . ltrim( $path, '/' );
		$mtime     = is_readable( $full_path ) ? filemtime( $full_path ) : false;

		return false !== $mtime ? HFB_COMPANION_VERSION . '.' . $mtime : HFB_COMPANION_VERSION;
	}

	private function is_public_post( \WP_Post $post ): bool {
		return 'post' === $post->post_type
			&& 'publish' === $post->post_status
			&& '' === (string) $post->post_password
			&& is_post_publicly_viewable( $post );
	}
}
