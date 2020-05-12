<?php

use SkyVerge\WooCommerce\Facebook\Handlers\Connection;

/**
 * Tests the Connection class.
 */
class ConnectionTest extends \Codeception\TestCase\WPTestCase {


	/** @var \IntegrationTester */
	protected $tester;


	public function _before() {

		parent::_before();

		require_once 'includes/Handlers/Connection.php';
	}


	/** Test methods **************************************************************************************************/


	/** @see Connection::__construct() */
	public function test_constructor() {

        $connection = $this->get_connection();

        $this->assertInstanceOf( Connection::class, $connection );
	}


	/** @see Connection::handle_connect() */
	public function test_handle_connect() {

		$this->assertNull( $this->get_connection()->handle_connect() );
	}


	/** @see Connection::handle_disconnect() */
	public function test_handle_disconnect() {

		$this->assertNull( $this->get_connection()->handle_disconnect() );
	}


	/** @see Connection::create_system_user_token() */
	public function test_create_system_user_token() {

		$user_token = 'user token';

		$this->assertEquals( $user_token, $this->get_connection()->create_system_user_token( $user_token ) );
	}


	/** @see Connection::get_access_token() */
	public function test_get_access_token() {

		$access_token = 'access token';

		$this->get_connection()->update_access_token( $access_token );

		$this->assertSame( $access_token, $this->get_connection()->get_access_token() );
	}


	/** @see Connection::get_connect_url() */
	public function test_get_connect_url() {

		$this->assertIsString( $this->get_connection()->get_connect_url() );
	}


	/** @see Connection::get_disconnect_url() */
	public function test_get_disconnect_url() {

		$this->assertIsString( $this->get_connection()->get_disconnect_url() );
	}


	/**
	 * @see Connection::get_scopes()
	 *
	 * @param string $scope an API scope that should be included
	 *
	 * @dataProvider provider_get_scopes
	 */
	public function test_get_scopes( $scope ) {

		$scopes = $this->get_connection()->get_scopes();

		$this->assertContains( $scope, $scopes );
	}


	/** @see test_get_scopes() */
	public function provider_get_scopes() {

		return [
			'manage_business_extension' => [ 'manage_business_extension' ],
			'catalog_management'        => [ 'catalog_management' ],
			'business_management'       => [ 'business_management' ],
		];
	}


	/** @see Connection::get_scopes() */
	public function test_get_scopes_filter() {

		add_filter( 'wc_facebook_connection_scopes', function() {

			return [ 'filtered' ];
		} );

		$this->assertSame( [ 'filtered' ], $this->get_connection()->get_scopes() );
	}


	/** @see Connection::get_external_business_id() */
	public function test_get_external_business_id() {

		update_option( Connection::OPTION_EXTERNAL_BUSINESS_ID, 'external business id' );

		$this->assertSame( 'external business id', $this->get_connection()->get_external_business_id() );
	}


	/** @see Connection::get_external_business_id() */
	public function test_get_external_business_id_generation() {

		// force the generation of a new ID
		delete_option( Connection::OPTION_EXTERNAL_BUSINESS_ID );

		$connection           = $this->get_connection();
		$external_business_id = $connection->get_external_business_id();

		$this->assertNotEmpty( $external_business_id );
		$this->assertIsString( $external_business_id );

		$this->assertEquals( $external_business_id, $connection->get_external_business_id() );
		$this->assertEquals( $external_business_id, get_option( Connection::OPTION_EXTERNAL_BUSINESS_ID ) );
	}


	/** @see Connection::get_external_business_id() */
	public function test_get_external_business_id_filter() {

		add_filter( 'wc_facebook_external_business_id', function() {

			return 'filtered';
		} );

		$this->assertEquals( 'filtered', $this->get_connection()->get_external_business_id() );
	}


	/**
	 * @see Connection::get_business_name()
	 *
	 * @dataProvider provider_get_business_name
	 *
	 * @param string $option_value the option value to set
	 */
	public function test_get_business_name( $option_value ) {

		update_option( 'blogname', $option_value );

		$this->assertSame( $option_value, $this->get_connection()->get_business_name() );
	}


	/** @see test_get_business_name() */
	public function provider_get_business_name() {

		return [
			[ 'Test Store' ],
			[ 'Tèst Store' ],
			[ "Test's Store" ],
			[ 'Test "Store"' ],
			[ 'Test & Store' ]
		];
	}


	/** @see Connection::get_business_name() */
	public function test_get_business_name_filtered() {

		$option_value = 'Test Store';

		update_option( 'blogname', $option_value );

		add_filter( 'wc_facebook_connection_business_name', function() {
			return 'Filtered Test Store';
		} );

		$this->assertSame( 'Filtered Test Store', $this->get_connection()->get_business_name() );
	}


	/** @see Connection::get_business_manager_id() */
	public function test_get_business_manager_id() {

		$business_manager_id = 'business manager id';

		$this->get_connection()->update_business_manager_id( $business_manager_id );

		$this->assertSame( $business_manager_id, $this->get_connection()->get_business_manager_id() );
	}


	/** @see Connection::get_redirect_url() */
	public function test_get_redirect_url() {

		$redirect_url = $this->get_connection()->get_redirect_url();

		$this->assertIsString( $redirect_url );
		$this->assertStringContainsString( home_url(), $redirect_url );
		$this->assertStringContainsString( 'wc-api=' . Connection::ACTION_CONNECT, $redirect_url );
		$this->assertStringContainsString( 'nonce=', $redirect_url );
	}


	/** @see Connection::get_redirect_url() */
	public function test_get_redirect_url_filter() {

		add_filter( 'wc_facebook_connection_redirect_url', function() {

			return 'filtered';
		} );

		$this->assertEquals( 'filtered', $this->get_connection()->get_redirect_url() );
	}


	/** @see Connection::get_connect_parameters() */
	public function test_get_connect_parameters() {

		$this->assertIsArray( $this->get_connection()->get_connect_parameters() );
	}


	/** @see Connection::update_business_manager_id() */
	public function test_update_business_manager_id() {

		$business_manager_id = 'business manager id';

		$this->get_connection()->update_business_manager_id( $business_manager_id );

		$this->assertSame( $business_manager_id, $this->get_connection()->get_business_manager_id() );
	}


	/** @see Connection::update_access_token() */
	public function test_update_access_token() {

		$access_token = 'access token';

		$this->get_connection()->update_access_token( $access_token );

		$this->assertSame( $access_token, $this->get_connection()->get_access_token() );
	}


	/** @see Connection::is_connected() */
	public function test_is_connected() {

		$this->assertIsBool( $this->get_connection()->is_connected() );
	}


	/** Helper methods **************************************************************************************************/


	/**
	 * Gets the connection instance.
	 *
	 * @return Connection
	 */
	private function get_connection() {

		return new Connection();
	}


}
