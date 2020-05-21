<?php

namespace SkyVerge\WooCommerce\Facebook\Tests\API\Pages\Read;

use SkyVerge\WooCommerce\Facebook\API\Pages\Read\Request;

/**
 * Tests the API\Pages\Read\Request class.
 */
class RequestTest extends \Codeception\TestCase\WPTestCase {


	/** @var \IntegrationTester */
	protected $tester;


	public function _before() {

		parent::_before();

		require_once 'includes/API/Request.php';
		require_once 'includes/API/Pages/Read/Request.php';
	}


	/** Test methods **************************************************************************************************/


	/** @see Request::__construct() */
	public function test_constructor() {

		$request = new Request( '1234', null, 'GET' );

		$this->assertEquals( '/1234', $request->get_path() );
		$this->assertEquals( 'GET', $request->get_method() );
	}


	/** @see Request::get_params() */
	public function test_get_params() {

		$request = new Request( '1234', null, null );

		$this->assertEquals( [ 'fields' => 'name,link' ], $request->get_params() );
	}


}
