<?php
/**
 * Reading-time helper.
 *
 * @package HFB_Companion
 */

declare( strict_types=1 );

namespace HFB_Companion;

defined( 'ABSPATH' ) || exit;

final class Reading_Time {

	private const WORDS_PER_MINUTE = 225;

	public static function for_post( \WP_Post $post ): int {
		return self::from_content( (string) $post->post_content );
	}

	public static function from_content( string $content ): int {
		$word_count = str_word_count( wp_strip_all_tags( $content ) );
		return max( 1, (int) ceil( $word_count / self::WORDS_PER_MINUTE ) );
	}
}
