<?php
/**
 * Plugin Name: Hungry Flamingo Blog Companion
 * Plugin URI: https://github.com/MatthiasReinholz/hungry-flamingo-blog-wordpress-companion-plugin
 * Description: Adds continuous reading, related-post blocks, reader CTAs, reading progress, and a local editorial report for Hungry Flamingo Blog.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.2
 * Author: Matthias Reinholz
 * Author URI: https://matthiasreinholz.com/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: hungry-flamingo-blog-companion
 * Domain Path: /languages
 *
 * @package HFB_Companion
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

define( 'HFB_COMPANION_VERSION', '1.0.0' );
define( 'HFB_COMPANION_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'HFB_COMPANION_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'HFB_COMPANION_STACK_SIZE', 5 );

if ( ! defined( 'HFB_COMPANION_TEXT_DOMAIN' ) ) {
	define( 'HFB_COMPANION_TEXT_DOMAIN', 'hungry-flamingo-blog-companion' );
}

spl_autoload_register(
	static function ( string $class ): void {
		if ( strpos( $class, 'HFB_Companion\\' ) !== 0 ) {
			return;
		}

		$relative = substr( $class, 14 );
		$parts    = explode( '\\', $relative );
		$file     = array_pop( $parts );
		$path     = array_map(
			static function ( string $segment ): string {
				return strtolower( str_replace( '_', '-', $segment ) );
			},
			$parts
		);

		$file_name = 'class-' . strtolower( str_replace( '_', '-', $file ) ) . '.php';
		$full_path = HFB_COMPANION_DIR . 'includes/' . ( $path ? implode( '/', $path ) . '/' : '' ) . $file_name;

		if ( is_readable( $full_path ) ) {
			require_once $full_path;
		}
	}
);

add_action(
	'plugins_loaded',
	static function (): void {
		( new HFB_Companion\Plugin() )->boot();
	}
);
