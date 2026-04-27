<?php
/**
 * Main plugin bootstrap.
 *
 * @package HFB_Companion
 */

declare( strict_types=1 );

namespace HFB_Companion;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	/**
	 * Feature modules.
	 *
	 * @var string[]
	 */
	private $modules = array(
		Assets::class,
		Admin_Report::class,
		Content_Integration::class,
		Continuous_Reading::class,
		Blocks\Post_Stack::class,
		Blocks\Related_Posts::class,
		Blocks\Reader_Cta::class,
	);

	public function boot(): void {
		foreach ( $this->modules as $module_class ) {
			if ( ! class_exists( $module_class ) ) {
				continue;
			}

			$module = new $module_class();
			if ( method_exists( $module, 'register' ) ) {
				$module->register();
			}
		}
	}
}
