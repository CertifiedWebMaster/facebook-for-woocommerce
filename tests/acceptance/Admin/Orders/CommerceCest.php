<?php

use SkyVerge\WooCommerce\Facebook\Commerce\Orders;
use SkyVerge\WooCommerce\Facebook\Handlers\Connection;

class CommerceCest {


	/**
	 * Runs before each test.
	 *
	 * @param AcceptanceTester $I tester instance
	 * @throws \Exception
	 */
	public function _before( AcceptanceTester $I ) {

		$I->haveOptionInDatabase( Connection::OPTION_ACCESS_TOKEN, '1234' );
		$I->haveOptionInDatabase( WC_Facebookcommerce_Integration::OPTION_PRODUCT_CATALOG_ID, '1234' );

		$I->haveOptionInDatabase( Connection::OPTION_PAGE_ACCESS_TOKEN, '1234' );
		$I->haveOptionInDatabase( Connection::OPTION_COMMERCE_MANAGER_ID, '1234' );

		// always log in
		$I->loginAsAdmin();
	}


	/**
	 * Test that an order can be cancelled using the Cancel Order modal.
	 *
	 * @param AcceptanceTester $I tester instance
	 */
	public function try_successfully_cancelling_an_order( AcceptanceTester $I ) {

		$remote_id = '1234';

		$order = $this->get_order_to_cancel( $I, $remote_id );

		$this->prepare_request_response( $I, 'POST', "/{$remote_id}/cancellations", [
			'response_body' => json_encode( [ 'id' => '7890' ] ),
		] );

		$I->amEditingPostWithId( $order->get_id() );

		$I->wantTo( 'test that an order can be successfully canceled using the Cancel Order modal' );

		$I->amGoingTo( 'set the order status to Cancelled and update the order' );

		$I->executeJS( "jQuery( '#order_status' ).val( 'wc-cancelled' ).trigger( 'change' )" );
		$I->click( 'button[name="save"]' );

		$I->see( 'Select a reason for cancelling this order:', '.facebook-for-woocommerce-modal' );

		$I->amGoingTo( 'set the cancel reason and confirm the modal action' );

		$I->selectOption( '#wc_facebook_cancel_reason', 'CUSTOMER_REQUESTED' );
		$I->click( '.facebook-for-woocommerce-modal #btn-ok' );

		$I->expect( 'the order to be updated' );

		$I->waitForText( 'Order updated.', 15 );
		$I->assertEquals( 'wc-cancelled', $I->executeJS( "return jQuery( '#order_status' ).val()" ) );
	}


	/**
	 * Gets a new order object to be used in the Cancel Order modal tests.
	 *
	 * @param AcceptanceTester $I tester instance
	 * @param string $remote_id Facebook order ID
	 * @return \WC_Order
	 */
	private function get_order_to_cancel( AcceptanceTester $I, string $remote_id ) {

		$item = new \WC_Order_Item_Product();
		$item->set_name( 'Test' );
		$item->set_quantity( 2 );
		$item->set_total( 1.00 );
		$item->set_product( $I->haveProductInDatabase() );
		$item->save();

		$order = new \WC_Order();
		$order->set_billing_first_name( 'John' );
		$order->set_billing_last_name( 'Doe' );
		$order->set_status( 'processing' );
		$order->add_item( $item );
		$order->update_meta_data( Orders::REMOTE_ID_META_KEY, $remote_id );
		$order->set_created_via( 'facebook' );
		$order->save();

		return $order;
	}


	/**
	 * Creates a must use plugin that overwrites the response for the given HTTP request.
	 *
	 * @param AcceptanceTester $I tester instance
	 * @param string $method request method
	 * @param string $path partial path to match against HTTP request URLs
	 * @param array $args response parameters
	 */
	private function prepare_request_response( AcceptanceTester $I, $method, $path, $args = [] ) {

		$args = wp_parse_args( $args, [
			'request_method'   => $method,
			'request_path'     => $path,
			'response_headers' => [],
			'response_cookies' => [],
			'response_body'    => [],
			'response_code'    => 200,
			'response_message' => 'Ok',
		] );

		$args['response_headers'] = json_encode( $args['response_headers'] );
		$args['response_cookies'] = json_encode( $args['response_cookies'] );
		$args['response_body']    = json_encode( $args['response_body'] );

		$code = <<<PHP
		add_filter( 'pre_http_request', function( \$response, \$args, \$url ) {

			if ( false !== strpos( \$url, '{$args['request_path']}' ) && '{$args['request_method']}' === \$args['method'] ) {

				\$response = [
					'headers'       => json_decode( '{$args['response_headers']}' ),
					'cookies'       => json_decode( '{$args['response_cookies']}' ),
					'body'          => json_decode( '{$args['response_body']}' ),
					'response'      => [
						'code'    => '{$args['response_code']}',
						'message' => '{$args['response_message']}',
					],
					'http_response' => null,
				];
			}

			return \$response;

		}, 10, 3 );
		PHP;

		$I->haveMuPlugin( sprintf( 'pre-http-request-%s.php', sanitize_file_name( $args['request_path'] ) ), $code );
	}


	/**
	 * Test that merchants can decide not to cancel the order through the Cancel Order modal.
	 *
	 * @param AcceptanceTester $I tester instance
	 */
	public function try_deciding_not_to_cancel_the_order( AcceptanceTester $I ) {

		$remote_id = '1234';

		$order = $this->get_order_to_cancel( $I, $remote_id );

		$I->amEditingPostWithId( $order->get_id() );

		$I->wantTo( 'test that I can close the Cancel Order modal without cancelling the order' );

		$I->amGoingTo( 'set the order status to Cancelled and update the order' );

		$I->executeJS( "jQuery( '#order_status' ).val( 'wc-cancelled' ).trigger( 'change' )" );
		$I->click( 'button[name="save"]' );

		$I->see( 'Select a reason for cancelling this order:', '.facebook-for-woocommerce-modal' );

		$I->amGoingTo( 'click the Cancel button' );

		$I->click( '.wc-facebook-modal-cancel-button' );

		$I->expect( 'the modal to disappear' );

		$I->dontSeeElement( '.facebook-for-woocommerce-modal' );
	}


}
