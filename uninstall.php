<?php
/**
 * Cleanup for Hungry Flamingo Blog Companion.
 *
 * @package HFB_Companion
 */

declare( strict_types=1 );

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'hfb_companion_cr_cache_ver' );
