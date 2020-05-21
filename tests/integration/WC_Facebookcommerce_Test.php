<?php

use SkyVerge\WooCommerce\Facebook\API;
use SkyVerge\WooCommerce\Facebook\Handlers\Connection;
use SkyVerge\WooCommerce\Facebook\Products\Sync;
use SkyVerge\WooCommerce\Facebook\Products\Sync\Background;

/**
 * Tests the WC_Facebookcommerce class.
 */
class WC_Facebookcommerce_Test extends \Codeception\TestCase\WPTestCase {


	/** @var \IntegrationTester */
	protected $tester;


	public function _before() {

		parent::_before();

		if ( ! class_exists( API::class ) ) {
			require_once 'includes/API.php';
		}
	}


	/** Test methods **************************************************************************************************/


	/** @see \WC_Facebookcommerce::get_api() */
	public function test_get_api() {

		$this->assertInstanceOf( API::class, facebook_for_woocommerce()->get_api() );
	}


	/** @see \WC_Facebookcommerce::get_connection_handler() */
	public function test_get_connection_handler() {

		$this->assertInstanceOf( Connection::class, facebook_for_woocommerce()->get_connection_handler() );
	}


	/** @see WC_Facebookcommerce::get_products_sync_handler() */
	public function test_get_products_sync_handler() {

		$this->assertInstanceOf( Sync::class, facebook_for_woocommerce()->get_products_sync_handler() );
	}


	/** @see WC_Facebookcommerce::get_products_sync_background_handler() */
	public function test_get_products_sync_background_handler() {

		$this->assertInstanceOf( Background::class, facebook_for_woocommerce()-> get_products_sync_background_handler() );
	}


	/** @see \WC_Facebookcommerce::get_support_url() */
	public function test_get_support_url() {

		$this->assertEquals( 'https://wordpress.org/support/plugin/facebook-for-woocommerce/', facebook_for_woocommerce()->get_support_url() );
	}


}
