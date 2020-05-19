<?php

use Codeception\Stub;
use SkyVerge\WooCommerce\Facebook\Products\Sync;
use SkyVerge\WooCommerce\Facebook\Products\Sync\Background;

/**
 * Tests the Sync\Background class.
 */
class BackgroundTest extends \Codeception\TestCase\WPTestCase {


	/** @var \IntegrationTester */
	protected $tester;


	public function _before() {

		parent::_before();

		require_once 'vendor/skyverge/wc-plugin-framework/woocommerce/utilities/class-sv-wp-async-request.php';
		require_once 'vendor/skyverge/wc-plugin-framework/woocommerce/utilities/class-sv-wp-background-job-handler.php';
		require_once 'includes/Products/Sync/Background.php';
	}


	/** Test methods **************************************************************************************************/


	/**
	 * @see Background::process_job()
	 *
	 * @dataProvider provider_process_job_calls_process_items
	 */
	public function test_process_job_calls_process_items( $requests ) {

		$background = Stub::make( Background::class, [
			'process_items' => \Codeception\Stub\Expected::exactly( empty( $requests ) ? 0 : 1 ),
		], $this );

		$job = (object) [
			'id'       => uniqid(),
			'status'   => 'queued',
			'requests' => $requests,
		];

		$background->process_job( $job );
	}


	/** @see test_process_job_calls_process_items() */
	public function provider_process_job_calls_process_items() {

		return [
			[ [] ],
			[ [
				Sync::PRODUCT_INDEX_PREFIX . '1' => Sync::ACTION_UPDATE
			] ],
			[ [
				Sync::PRODUCT_INDEX_PREFIX . '1' => Sync::ACTION_UPDATE,
				Sync::PRODUCT_INDEX_PREFIX . '2' => Sync::ACTION_UPDATE,
				Sync::PRODUCT_INDEX_PREFIX . '3' => Sync::ACTION_DELETE,
			] ],
		];
	}


	/** Helper methods **************************************************************************************************/


	/**
	 * Gets a test job object.
	 *
	 * @return object
	 */
	private function get_test_job( array $props = [] ) {

		$defaults = [
			'id'       => uniqid(),
			'status'   => 'queued',
			'requests' => [],
			'progress' => 0,
		];

		return (object) array_merge( $defaults, $props );
	}


}

