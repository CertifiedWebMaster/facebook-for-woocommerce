<?php

use SkyVerge\WooCommerce\Facebook\Admin;
use SkyVerge\WooCommerce\Facebook\Commerce\Orders;
use SkyVerge\WooCommerce\PluginFramework\v5_5_4\SV_WC_Plugin_Exception;

/**
 * Tests the Admin\Orders class.
 */
class OrdersTest extends \Codeception\TestCase\WPTestCase {


	/** @var \IntegrationTester */
	protected $tester;


	/**
	 * Runs before each test.
	 */
	protected function _before() {

		if ( ! class_exists( Admin\Orders::class ) ) {
			require_once 'includes/Admin/Orders.php';
		}

		if ( ! class_exists( \SkyVerge\WooCommerce\Facebook\Commerce\Orders::class ) ) {
			require_once 'includes/Commerce/Orders.php';
		}
	}


	/**
	 * Runs after each test.
	 */
	protected function _after() {

	}


	/** Test methods **************************************************************************************************/


	// TODO: add test for enqueue_assets()

	// TODO: add test for add_notices()

	// TODO: add test for maybe_remove_order_metaboxes()

	// TODO: add test for render_refund_reason_field()


	/**
	 * @see Admin\Orders::handle_refund()
	 *
	 * @throws SV_WC_Plugin_Exception
	 */
	public function test_handle_refund() {

		// the API cannot be instantiated if an access token is not defined
		facebook_for_woocommerce()->get_connection_handler()->update_access_token( 'access_token' );

		$_POST[ 'wc_facebook_refund_reason' ] = Orders::REFUND_REASON_QUALITY_ISSUE;

		$order = new \WC_Order_Refund();
		$order->set_status( 'pending' );
		$order->save();

		$this->get_orders_handler()->handle_refund( $order->get_id() );
	}


	// TODO: add test for handle_bulk_update()


	/**
	 * @see Admin\Orders::maybe_stop_order_email()
	 *
	 * @param bool $is_enabled
	 * @param \WC_Order|string|null $order
	 * @param bool $expected
	 *
	 * @dataProvider provider_maybe_stop_order_email
	 */
	public function test_maybe_stop_order_email( $is_enabled, $order, $expected ) {

		$orders_handler = $this->get_orders_handler();

		$this->assertEquals( $expected, $orders_handler->maybe_stop_order_email( $is_enabled, $order ) );
	}


	/**
	 * @see test_maybe_stop_order_email
	 *
	 * @throws WC_Data_Exception
	 */
	public function provider_maybe_stop_order_email() {

		$commerce_order = new \WC_Order();
		$commerce_order->set_created_via( 'instagram' );

		return [
			[ false, null,                     false ],
			[ true,  null,                     true ],
			[ true,  'a non \WC_Order object', true ],
			[ true,  new \WC_Order(),          true ],
			[ true,  $commerce_order,          false ],
		];
	}


	/**
	 * @see Admin\Orders::maybe_stop_order_email()
	 *
	 * @param bool $is_enabled
	 * @param bool $expected
	 * @param \WC_Order|string|null $order
	 *
	 * @dataProvider provider_maybe_stop_order_email_filter
	 */
	public function test_maybe_stop_order_email_filter( $is_enabled, $order, $expected ) {

		add_filter( 'wc_facebook_commerce_send_woocommerce_emails', function( $is_enabled ) {

			return ! $is_enabled;
		} );

		$orders_handler = $this->get_orders_handler();

		$this->assertEquals( $expected, $orders_handler->maybe_stop_order_email( $is_enabled, $order ) );
	}


	/** @see test_maybe_stop_order_email_filter */
	public function provider_maybe_stop_order_email_filter() {

		$commerce_order = new \WC_Order();
		$commerce_order->set_created_via( 'instagram' );

		return [
			[ false, null,                     false ],
			[ true,  null,                     true ],
			[ true,  'a non \WC_Order object', true ],
			[ false, 'a non \WC_Order object', false ],
			[ true,  new \WC_Order(),          true ],
			[ false, new \WC_Order(),          false ],
			[ true,  $commerce_order,          true ],
			[ false, $commerce_order,          false ],
		];
	}


	/**
	 * @see Admin\Orders::is_order_editable()
	 *
	 * @param bool $maybe_editable
	 * @param string $created_via
	 * @param string $status
	 * @param bool $expected
	 *
	 * @dataProvider provider_is_order_editable
	 *
 	 * @throws WC_Data_Exception
	 */
	public function test_is_order_editable( $maybe_editable, $created_via, $status, $expected ) {

		$order = new \WC_Order();
		$order->set_created_via( $created_via );
		$order->set_status( $status );
		$order->save();

		$this->assertEquals( $expected, $this->get_orders_handler()->is_order_editable( $maybe_editable, $order ) );
	}


	/** @see test_is_order_editable */
	public function provider_is_order_editable() {

		return [
			[ false, 'checkout',  'pending',    false ],
			[ true,  'checkout',  'pending',    true ],
			[ true,  'instagram', 'pending',    false ],
			[ true,  'instagram', 'processing', true ],
			[ true,  'facebook',  'pending',    false ],
			[ true,  'facebook',  'processing', true ],
		];
	}


	/** Utility methods ***********************************************************************************************/


	/**
	 * Gets an orders handler instance.
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @return Admin\Orders
	 */
	private function get_orders_handler() {

		return new Admin\Orders();
	}


}
