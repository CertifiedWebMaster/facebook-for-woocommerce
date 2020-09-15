<?php

use SkyVerge\WooCommerce\Facebook\Admin;

/**
 * Tests the Admin\Product_Categories class.
 */
class ProductCategoriesTest extends \Codeception\TestCase\WPTestCase {


	/** @var \IntegrationTester */
	protected $tester;


	/**
	 * Runs before each test.
	 */
	protected function _before() {

		require_once 'includes/Admin/Product_Categories.php';
	}


	/**
	 * Runs after each test.
	 */
	protected function _after() {

	}


	/** Test methods **************************************************************************************************/


	// TODO: add test for enqueue_assets()

	// TODO: add test for render_add_google_product_category_field()

	// TODO: add test for render_edit_google_product_category_field()


	/** @see Product_Categories::render_google_product_category_tooltip() */
	public function test_render_google_product_category_tooltip() {

		ob_start();
		$this->get_product_categories_handler()->render_google_product_category_tooltip();
		$html = trim( ob_get_clean() );

		$this->assertEquals( '<span class="woocommerce-help-tip" data-tip="Choose a default Google product category for products in this category. Products need at least two category levels defined to be sold on Instagram."></span>', $html );
	}


	/** @see Product_Categories::get_google_product_category_field_title() */
	public function test_get_google_product_category_field_title() {

		$this->assertEquals( 'Default Google product category', $this->get_product_categories_handler()->get_google_product_category_field_title() );
	}


	// TODO: add test for save_google_product_category()


	/** Utility methods ***********************************************************************************************/


	/**
	 * Gets a handler instance.
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @return Admin\Product_Categories
	 */
	private function get_product_categories_handler() {

		return new Admin\Product_Categories();
	}


}
