<?php

use SkyVerge\WooCommerce\Facebook\Admin;

/**
 * Tests the Admin\Google_Product_Category_Field class.
 */
class GoogleProductCategoryFieldTest extends \Codeception\TestCase\WPTestCase {


	/** @var \IntegrationTester */
	protected $tester;


	/**
	 * Runs before each test.
	 */
	protected function _before() {

		require_once 'includes/Admin/Google_Product_Category_Field.php';
	}


	/**
	 * Runs after each test.
	 */
	protected function _after() {

	}


	/** Test methods **************************************************************************************************/


	/** @see Admin\Google_Product_Category_Field::render() */
	public function test_render() {
		global $wc_queued_js;

		$field = new Admin\Google_Product_Category_Field();
		$field->render( 'this-input' );

		$this->assertStringContainsString( 'new WC_Facebook_Google_Product_Category_Fields', $wc_queued_js );
	}


	/** @see \SkyVerge\WooCommerce\Facebook\Admin\Google_Product_Category_Field::get_categories() */
	public function test_get_categories_transient() {

		$transient_value = [
			'1' => [
				'label'   => 'Animals & Pet Supplies',
				'parent'  => '',
				'options' => [
					'3237' => 'Live Animals',
					'2'    => 'Pet Supplies',
				],
			],
		];

		set_transient( Admin\Google_Product_Category_Field::OPTION_GOOGLE_PRODUCT_CATEGORIES, $transient_value, HOUR_IN_SECONDS );

		$field = new Admin\Google_Product_Category_Field();

		$this->assertEquals( $transient_value, $field->get_categories() );
	}


	/** @see \SkyVerge\WooCommerce\Facebook\Admin\Google_Product_Category_Field::get_categories() */
	public function test_get_categories_error() {

		$field = new Admin\Google_Product_Category_Field();

		add_filter( 'pre_http_request', static function() {

			return new WP_Error();
		} );

		$this->assertEquals( [], $field->get_categories() );
	}


	/** @see \SkyVerge\WooCommerce\Facebook\Admin\Google_Product_Category_Field::get_categories() */
	public function test_get_categories_error_option_set() {

		$option_value = [
			'1' => [
				'label'   => 'Animals & Pet Supplies',
				'parent'  => '',
				'options' => [
					'3237' => 'Live Animals',
					'2'    => 'Pet Supplies',
				],
			],
		];

		update_option( Admin\Google_Product_Category_Field::OPTION_GOOGLE_PRODUCT_CATEGORIES, $option_value );

		$field = new Admin\Google_Product_Category_Field();

		add_filter( 'pre_http_request', static function() {

			return new WP_Error();
		} );

		$this->assertEquals( $option_value, $field->get_categories() );
	}


	/** @see \SkyVerge\WooCommerce\Facebook\Admin\Google_Product_Category_Field::get_categories() */
	public function test_get_categories_success() {

		$field     = new Admin\Google_Product_Category_Field();
		$test_body = $this->get_test_categories_response_body();

		add_filter( 'pre_http_request', static function () use ( $test_body ) {

			return [
				'body' => $test_body,
			];
		} );

		$this->assertEquals( $this->get_test_category_list(), $field->get_categories() );
		$this->assertEquals( $this->get_test_category_list(), get_option( Admin\Google_Product_Category_Field::OPTION_GOOGLE_PRODUCT_CATEGORIES ) );
		$this->assertEquals( $this->get_test_category_list(), get_transient( Admin\Google_Product_Category_Field::OPTION_GOOGLE_PRODUCT_CATEGORIES ) );
	}


	/**
	 * @see \SkyVerge\WooCommerce\Facebook\Admin\Google_Product_Category_Field::parse_categories_response()
	 *
	 * @param array $response test response
	 * @param array $expected expected categories
	 *
	 * @dataProvider provider_parse_categories_response
	 */
	public function test_parse_categories_response( $response, $expected ) {

		$field = new Admin\Google_Product_Category_Field();

		$parse_categories_response = IntegrationTester::getMethod( Admin\Google_Product_Category_Field::class, 'parse_categories_response' );

		$this->assertEquals( $expected, $parse_categories_response->invokeArgs( $field, [ $response ] ) );
	}


	/** @see test_parse_categories_response */
	public function provider_parse_categories_response() {

		return [
			'error response'           => [ new WP_Error(), [] ],
			'response without body'    => [ [], [] ],
			'response with empty body' => [ [ 'body' => '' ], [] ],
			'response with valid body' => [
				[
					'body' => $this->get_test_categories_response_body(),
				],
				$this->get_test_category_list(),
			],
		];
	}


	/**
	 * @see \SkyVerge\WooCommerce\Facebook\Admin\Google_Product_Category_Field::get_category_options()
	 *
	 * @param array $categories full category list
	 * @param string $category_id category ID
	 * @param array $expected expected options
	 *
	 * @dataProvider provider_get_category_options
	 */
	public function test_get_category_options( $categories, $category_id, $expected ) {

		$field = new Admin\Google_Product_Category_Field();

		$this->assertEquals( $expected, $field->get_category_options( $category_id, $categories ) );
	}


	/** @see test_get_category_options */
	public function provider_get_category_options() {

		return [

			'top level category' => [
				$this->get_test_category_list(),
				'1',
				[
					'3237' => 'Live Animals',
					'2'    => 'Pet Supplies',
				],
			],
			'2nd level category' => [
				$this->get_test_category_list(),
				'2',
				[
					'3' => 'Bird Supplies',
				],
			],
			'3rd level category' => [
				$this->get_test_category_list(),
				'3',
				[
					'7385' => 'Bird Cage Accessories',
					'4989' => 'Bird Cages & Stands',
				],
			],
			'4th level category' => [
				$this->get_test_category_list(),
				'7385',
				[
					'499954' => 'Bird Cage Bird Baths',
					'7386'   => 'Bird Cage Food & Water Dishes',
				],
			],
			'5th level category' => [
				$this->get_test_category_list(),
				'499954',
				[],
			],
		];
	}



	/** Helper methods **************************************************************************************************/


	/**
	 * Gets a test categories response body.
	 *
	 * @return string
	 */
	private function get_test_categories_response_body() {

		return '# Google_Product_Taxonomy_Version: 2019-07-10
1 - Animals & Pet Supplies
3237 - Animals & Pet Supplies > Live Animals
2 - Animals & Pet Supplies > Pet Supplies
3 - Animals & Pet Supplies > Pet Supplies > Bird Supplies
7385 - Animals & Pet Supplies > Pet Supplies > Bird Supplies > Bird Cage Accessories
499954 - Animals & Pet Supplies > Pet Supplies > Bird Supplies > Bird Cage Accessories > Bird Cage Bird Baths
7386 - Animals & Pet Supplies > Pet Supplies > Bird Supplies > Bird Cage Accessories > Bird Cage Food & Water Dishes
4989 - Animals & Pet Supplies > Pet Supplies > Bird Supplies > Bird Cages & Stands';
	}


	/**
	 * Gets a test category list.
	 *
	 * @return array
	 */
	private function get_test_category_list() {

		return [
			'1'      => [
				'label'   => 'Animals & Pet Supplies',
				'parent'  => '',
				'options' => [
					'3237' => 'Live Animals',
					'2'    => 'Pet Supplies',
				],
			],
			'3237'   => [
				'label'   => 'Live Animals',
				'parent'  => '1',
				'options' => [],
			],
			'2'      => [
				'label'   => 'Pet Supplies',
				'parent'  => '1',
				'options' => [
					'3' => 'Bird Supplies',
				],
			],
			'3'      => [
				'label'   => 'Bird Supplies',
				'parent'  => '2',
				'options' => [
					'7385' => 'Bird Cage Accessories',
					'4989' => 'Bird Cages & Stands',
				],
			],
			'7385'   => [
				'label'   => 'Bird Cage Accessories',
				'parent'  => '3',
				'options' => [
					'499954' => 'Bird Cage Bird Baths',
					'7386'   => 'Bird Cage Food & Water Dishes',
				],
			],
			'499954' => [
				'label'   => 'Bird Cage Bird Baths',
				'parent'  => '7385',
				'options' => [],
			],
			'7386'   => [
				'label'   => 'Bird Cage Food & Water Dishes',
				'parent'  => '7385',
				'options' => [],
			],
			'4989'   => [
				'label'   => 'Bird Cages & Stands',
				'parent'  => '3',
				'options' => [],
			],
		];
	}


}
