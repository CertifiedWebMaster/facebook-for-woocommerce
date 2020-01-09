<?php 

class ProductSyncColumnCest {


	/** @var \WC_Product|null product object created for the test */
	private $product;


	/**
	 * Runs before each test.
	 *
	 * @param AcceptanceTester $I tester instance
	 */
	public function _before( AcceptanceTester $I ) {

		// save a generic product
		$this->product = $I->haveProductInDatabase();

		// always log in
		$I->loginAsAdmin();
	}


	/**
	 * Test that the column is present.
	 *
	 * @param AcceptanceTester $I tester instance
	 */
	public function try_column_present( AcceptanceTester $I ) {

		$I->amOnProductsPage();

		$I->wantTo( 'Test that the column is present' );

		$I->see( 'FB Sync Enabled', 'table.wp-list-table th' );
	}


	/**
	 * Test that the column shows the correct status for a product that is synced.
	 *
	 * @param AcceptanceTester $I tester instance
	 */
	public function try_column_displays_sync_enabled( AcceptanceTester $I ) {

		// enable sync for the product before viewing the Products page
		\SkyVerge\WooCommerce\Facebook\Products::enable_sync_for_products( [ $this->product ] );

		$I->amOnProductsPage();

		$I->wantTo( 'Test that the column displays the correct status for a sync-enabled product' );

		$this->seeColumnHasValue( $I, 'Enabled' );
	}


	/**
	 * Test that the column shows the correct status for a product that is synced.
	 *
	 * @param AcceptanceTester $I tester instance
	 */
	public function try_column_displays_sync_disabled( AcceptanceTester $I ) {

		// enable sync for the product before viewing the Products page
		\SkyVerge\WooCommerce\Facebook\Products::disable_sync_for_products( [ $this->product ] );

		$I->amOnProductsPage();

		$I->wantTo( 'Test that the column displays the correct status for a sync-disabled product' );

		$this->seeColumnHasValue( $I, 'Disabled' );
	}


	/**
	 * Test that the column defaults to "Not synced"
	 *
	 * @param AcceptanceTester $I tester instance
	 */
	public function try_column_defaults_to_not_synced( AcceptanceTester $I ) {

		$I->amOnProductsPage();

		$I->wantTo( 'Test that the column displays the correct status for a product with no sync status set' );

		$this->seeColumnHasValue( $I, 'Enabled' );
	}


	/**
	 * See that the column has a specific value.
	 *
	 * @param AcceptanceTester $I tester instance
	 * @param string $value value to check
	 */
	private function seeColumnHasValue( AcceptanceTester $I, string $value ) {

		$I->see( $value, 'table.wp-list-table td' );
	}


}
