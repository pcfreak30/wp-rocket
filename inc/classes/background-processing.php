<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

require( WP_ROCKET_VENDORS_PATH . 'wp-async-request.php' );
require( WP_ROCKET_VENDORS_PATH . 'wp-background-process.php' );

/**
 * Extends the background process class for the sitemap preload background process.
 *
 * @since 2.7
 *
 * @see WP_Background_Process
 */
class Rocket_Sitemap_Preload_Process extends WP_Background_Process {

	/**
	 * Specific action identifier for sitemap preload.
	 *
	 * @since 2.7
	 * @access protected
	 * @var string Action identifier
	 */
	protected $action = 'rocket_sitemap_preload';

	/**
	 * Preload the URL provided by $item
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return null
	 */
	protected function task( $item ) {
		$args = array(
			'timeout'    => 0.01,
			'blocking'   => false,
			'user-agent' => 'wprocketbot',
			'sslverify'  => false,
		);

		$tmp = wp_remote_get( esc_url_raw( $item ), $args );
		usleep( get_rocket_option( 'sitemap_preload_url_crawl', '500000' ) );

		return false;
	}

	/**
	 * Complete
	 */
	protected function complete() {
		parent::complete();
	}

}

$GLOBALS['rocket_sitemap_background_process'] = new Rocket_Sitemap_Preload_Process();
