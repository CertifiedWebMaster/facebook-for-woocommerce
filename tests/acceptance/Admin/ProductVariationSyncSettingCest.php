<?php

use SkyVerge\WooCommerce\Facebook\Handlers\Connection;
use SkyVerge\WooCommerce\Facebook\Products;

class ProductVariationSyncSettingCest {


	/** @var string base selector for the Facebook image source field */
	const FIELD_IMAGE_SOURCE = '.variable_fb_product_image_source%d_field input';


	/** @var WC_Product|null product object created for the test */
	private $variable_product;

	/** @var WC_Product_Variation|null product variation object created for the test */
	private $product_variation;


	/**
	 * Runs before each test.
	 *
	 * @param AcceptanceTester $I tester instance
	 * @throws \Exception
	 */
    public function _before( AcceptanceTester $I ) {

		/**
		 * Set these in the database so that the product processing hooks are properly set
		 * @see \WC_Facebookcommerce_Integration::__construct()
		 */
		$I->haveOptionInDatabase( Connection::OPTION_ACCESS_TOKEN, '1234' );
		$I->haveOptionInDatabase( WC_Facebookcommerce_Integration::OPTION_PRODUCT_CATALOG_ID, '1234' );

		$I->haveOptionInDatabase( \WC_Facebookcommerce_Integration::SETTING_FACEBOOK_PAGE_ID, '1234' );

		$product_objects = $I->haveVariableProductInDatabase();

		$this->variable_product  = $product_objects['product'];
		$this->product_variation = $product_objects['variations']['product_variation'];

		// always log in
		$I->loginAsAdmin();
    }


	/**
	 * Tests that the field is unchecked if sync is disabled in parent.
	 *
	 * @param AcceptanceTester $I
	 * @throws \Exception
	 */
	public function try_select_if_sync_disabled_in_parent( AcceptanceTester $I ) {

		// \SkyVerge\WooCommerce\Facebook\Products::disable_sync_for_products() won't set a meta for variable products, only for the variations
		// This tests the behavior for variable products modified using an older version that can still have the meta set
		$this->variable_product->update_meta_data( \SkyVerge\WooCommerce\Facebook\Products::SYNC_ENABLED_META_KEY, 'no' );
		$this->variable_product->save();

		$index = $I->amEditingProductVariation( $this->product_variation );

		$I->wantTo( 'Test that the field is correct if sync is disabled in parent' );

		$I->seeOptionIsSelected( "#variable_facebook_sync_mode{$index}", 'Do not sync' );
	}


	/**
	 * Tests that the field is checked if sync is enabled in parent.
	 *
	 * @param AcceptanceTester $I
	 * @throws \Exception
	 */
	public function try_select_if_sync_enabled_in_parent( AcceptanceTester $I ) {

		// \SkyVerge\WooCommerce\Facebook\Products::enable_sync_for_products() won't set a meta for variable products, only for the variations
		// This tests the behavior for variable products modified using an older version that can still have the meta set
		$this->variable_product->update_meta_data( \SkyVerge\WooCommerce\Facebook\Products::SYNC_ENABLED_META_KEY, 'yes' );
		$this->variable_product->save();

		$index = $I->amEditingProductVariation( $this->product_variation );

		$I->wantTo( 'Test that the field is correct if sync is enabled in parent' );

		$I->seeOptionIsSelected( "#variable_facebook_sync_mode{$index}", 'Sync and show in catalog' );
	}


	/**
	 * Tests that the field is checked if sync is enabled in parent.
	 *
	 * @param AcceptanceTester $I
	 * @throws \Exception
	 */
	public function try_select_if_sync_enabled_and_hidden_in_parent( AcceptanceTester $I ) {

		// \SkyVerge\WooCommerce\Facebook\Products::enable_sync_for_products() won't set a meta for variable products, only for the variations
		// This tests the behavior for variable products modified using an older version that can still have the meta set
		$this->variable_product->update_meta_data( \SkyVerge\WooCommerce\Facebook\Products::SYNC_ENABLED_META_KEY, 'yes' );
		$this->variable_product->update_meta_data( \SkyVerge\WooCommerce\Facebook\Products::VISIBILITY_META_KEY, 'no' );
		$this->variable_product->save();

		$index = $I->amEditingProductVariation( $this->product_variation );

		$I->wantTo( 'Test that the field is correct if sync is enabled but hidden in parent' );

		$I->seeOptionIsSelected( "#variable_facebook_sync_mode{$index}", 'Sync and hide in catalog' );
	}


	/**
	 * Tests that the field is unchecked when sync is disabled.
	 *
	 * @param AcceptanceTester $I
	 * @throws \Exception
	 */
	public function try_select_sync_disabled( AcceptanceTester $I ) {

		\SkyVerge\WooCommerce\Facebook\Products::disable_sync_for_products( [ $this->product_variation ] );

		$index = $I->amEditingProductVariation( $this->product_variation );

		$I->wantTo( 'Test that the field is correct when sync is disabled' );

		$I->seeOptionIsSelected( "#variable_facebook_sync_mode{$index}", 'Do not sync' );
	}


	/**
	 * Tests that the field is checked when sync is enabled.
	 *
	 * @param AcceptanceTester $I
	 * @throws \Exception
	 */
	public function try_select_sync_enabled( AcceptanceTester $I ) {

		\SkyVerge\WooCommerce\Facebook\Products::enable_sync_for_products( [ $this->product_variation ] );

		$index = $I->amEditingProductVariation( $this->product_variation );

		$I->wantTo( 'Test that the field is correct when sync is enabled' );

		$I->seeOptionIsSelected( "#variable_facebook_sync_mode{$index}", 'Sync and show in catalog' );
	}


	/**
	 * Tests that the field is checked when sync is enabled.
	 *
	 * @param AcceptanceTester $I
	 * @throws \Exception
	 */
	public function try_select_sync_enabled_hidden( AcceptanceTester $I ) {

		\SkyVerge\WooCommerce\Facebook\Products::enable_sync_for_products( [ $this->product_variation ] );
		\SkyVerge\WooCommerce\Facebook\Products::set_product_visibility( $this->product_variation, false );

		$index = $I->amEditingProductVariation( $this->product_variation );

		$I->wantTo( 'Test that the field is correct when sync is enabled but hidden' );

		$I->seeOptionIsSelected( "#variable_facebook_sync_mode{$index}", 'Sync and hide in catalog' );
	}


	/**
	 * Tests that the settings fields are disabled if sync is disabled for this variation.
	 *
	 * @param AcceptanceTester $I
	 * @throws \Exception
	 */
	public function try_settings_fields_are_disabled_if_sync_is_disabled( AcceptanceTester $I ) {

		\SkyVerge\WooCommerce\Facebook\Products::disable_sync_for_products( [ $this->product_variation ] );

		$index = $I->amEditingProductVariation( $this->product_variation );

		$I->wantTo( 'Test that the settings fields are disabled if sync is disabled for this variation' );

		$I->waitForElementVisible( sprintf( '#variable_%s%s', WC_Facebookcommerce_Integration::FB_PRODUCT_DESCRIPTION, $index ), 5 );

		$I->seeElement( sprintf( '#variable_%s%s:disabled', WC_Facebookcommerce_Integration::FB_PRODUCT_DESCRIPTION, $index ) );
		$I->seeElement( sprintf( self::FIELD_IMAGE_SOURCE . ':disabled', $index ) );
		$I->seeElement( sprintf( '#variable_%s%s:disabled', WC_Facebook_Product::FB_PRODUCT_PRICE, $index ) );
	}


	/**
	 * Tests that the settings fields are enabled if sync is enabled for this variation.
	 *
	 * @param AcceptanceTester $I
	 * @throws \Exception
	 */
	public function try_settings_fields_are_enabled_if_sync_is_enabled( AcceptanceTester $I ) {

		\SkyVerge\WooCommerce\Facebook\Products::enable_sync_for_products( [ $this->product_variation ] );

		$index = $I->amEditingProductVariation( $this->product_variation );

		$I->wantTo( 'Test that the settings fields are enabled if sync is enabled for this variation' );

		$I->waitForElementVisible( sprintf( '#variable_%s%s', WC_Facebookcommerce_Integration::FB_PRODUCT_DESCRIPTION, $index ), 5 );

		$I->seeElement( sprintf( '#variable_%s%s:not(:disabled)', WC_Facebookcommerce_Integration::FB_PRODUCT_DESCRIPTION, $index ) );
		$I->seeElement( sprintf( self::FIELD_IMAGE_SOURCE . ':not(:disabled)', $index ) );
		$I->seeElement( sprintf( '#variable_%s%s:not(:disabled)', WC_Facebook_Product::FB_PRODUCT_PRICE, $index ) );
	}


	/**
	 * Tests that the sync can be enabled for a variation.
	 *
	 * @param AcceptanceTester $I
	 * @throws \Exception
	 */
	public function try_sync_can_be_enabled_for_a_variation( AcceptanceTester $I ) {

		\SkyVerge\WooCommerce\Facebook\Products::disable_sync_for_products( [ $this->product_variation ] );

		$index = $I->amEditingProductVariation( $this->product_variation );

		$I->wantTo( 'Test that sync can be enabled for a variation' );

		$I->waitForElementVisible( "#variable_facebook_sync_mode{$index}", 5 );

		$I->selectOption( "#variable_facebook_sync_mode{$index}", 'Sync and show in catalog' );
		$I->fillField( sprintf( '#variable_%s%s', WC_Facebookcommerce_Integration::FB_PRODUCT_DESCRIPTION, $index ), 'Test description.' );
		$I->selectOption( sprintf( self::FIELD_IMAGE_SOURCE, $index ), Products::PRODUCT_IMAGE_SOURCE_PARENT_PRODUCT );
		$I->fillField( sprintf( '#variable_%s%s', WC_Facebook_Product::FB_PRODUCT_PRICE, $index ), '12.34' );

		$I->click( [ 'css' => '.save-variation-changes' ] );

		$I->wait( 4 );

		$index = $I->openVariationMetabox( $this->product_variation );

		$I->waitForElementVisible( "#variable_facebook_sync_mode{$index}", 5 );

		$I->seeOptionIsSelected( "#variable_facebook_sync_mode{$index}", 'Sync and show in catalog' );
		$I->seeInField( sprintf( '#variable_%s%s', WC_Facebookcommerce_Integration::FB_PRODUCT_DESCRIPTION, $index ), 'Test description.' );
		$I->seeOptionIsSelected( sprintf( self::FIELD_IMAGE_SOURCE, $index ), 'Use parent image' );
		$I->seeInField( sprintf( '#variable_%s%s', WC_Facebook_Product::FB_PRODUCT_PRICE, $index ), '12.34' );
	}


	/**
	 * Tests that the sync can be enabled but hidden for a variation.
	 *
	 * @param AcceptanceTester $I
	 * @throws \Exception
	 */
	public function try_sync_can_be_enabled_and_hidden_for_a_variation( AcceptanceTester $I ) {

		\SkyVerge\WooCommerce\Facebook\Products::disable_sync_for_products( [ $this->product_variation ] );

		$index = $I->amEditingProductVariation( $this->product_variation );

		$I->wantTo( 'Test that sync can be enabled but hidden for a variation' );

		$I->waitForElementVisible( "#variable_facebook_sync_mode{$index}", 5 );

		$I->selectOption( "#variable_facebook_sync_mode{$index}", 'Sync and hide in catalog' );

		$I->click( [ 'css' => '.save-variation-changes' ] );

		$I->wait( 4 );

		$index = $I->openVariationMetabox( $this->product_variation );

		$I->waitForElementVisible( "#variable_facebook_sync_mode{$index}", 5 );

		$I->seeOptionIsSelected( "#variable_facebook_sync_mode{$index}", 'Sync and hide in catalog' );
	}


	/**
	 * Tests that the sync can be disabled for a variation.
	 *
	 * @param AcceptanceTester $I
	 * @throws \Exception
	 */
	public function try_sync_can_be_disabled_for_a_variation( AcceptanceTester $I ) {

		\SkyVerge\WooCommerce\Facebook\Products::enable_sync_for_products( [ $this->product_variation ] );

		$index = $I->amEditingProductVariation( $this->product_variation );

		$I->wantTo( 'Test that sync can be disabled for a variation' );

		$I->waitForElementVisible( "#variable_facebook_sync_mode{$index}", 5 );

		$I->selectOption( "#variable_facebook_sync_mode{$index}", 'Do not sync' );

		$I->click( [ 'css' => '.save-variation-changes' ] );

		$I->wait( 4 );

		$index = $I->openVariationMetabox( $this->product_variation );

		$I->waitForElementVisible( "#variable_facebook_sync_mode{$index}", 5 );

		$I->seeOptionIsSelected( "#variable_facebook_sync_mode{$index}", 'Do not sync' );
		$I->seeElement( sprintf( '#variable_%s%s:disabled', WC_Facebookcommerce_Integration::FB_PRODUCT_DESCRIPTION, $index ) );
		$I->seeElement( sprintf( self::FIELD_IMAGE_SOURCE . ':disabled', $index ) );
		$I->seeElement( sprintf( '#variable_%s%s:disabled', WC_Facebook_Product::FB_PRODUCT_PRICE, $index ) );
	}


	/**
	 * Tests that settings fields are empty by default.
	 *
	 * @param AcceptanceTester $I
	 * @throws \Exception
	 */
	public function try_fields_are_empty_by_default( AcceptanceTester $I ) {

		$index = $I->amEditingProductVariation( $this->product_variation );

		$I->wantTo( 'Test that settings fields are empty by default' );

		$I->waitForElementVisible( sprintf( '#variable_%s%s', WC_Facebookcommerce_Integration::FB_PRODUCT_DESCRIPTION, $index ), 5 );

		$I->seeInField( sprintf( '#variable_%s%s', WC_Facebookcommerce_Integration::FB_PRODUCT_DESCRIPTION, $index ), '' );
		$I->seeOptionIsSelected( sprintf( self::FIELD_IMAGE_SOURCE, $index ), 'Use variation image' );
		$I->seeInField( sprintf( '#variable_%s%s', WC_Facebook_Product::FB_PRODUCT_PRICE, $index ), '' );
	}


	/**
	 * Test that the sync is automatically disabled when saving virtual variations.
	 *
	 * @param AcceptanceTester $I tester instance
	 *
	 * @throws Exception
	 */
	public function try_sync_disabled_saving_virtual_variations( AcceptanceTester $I ) {

		$index = $I->amEditingProductVariation( $this->product_variation );

		$I->wantTo( 'Test that the sync is automatically disabled when saving virtual variations' );

		$I->waitForElementVisible( "#variable_description{$index}" );

		$I->click( "[name='variable_is_virtual[{$index}]']" );

		$I->click( [ 'css' => '.save-variation-changes' ] );

		$I->wait( 4 );

		$index = $I->openVariationMetabox( $this->product_variation );

		$I->waitForElementVisible( "#variable_description{$index}" );

		// uncheck the Virtual checkbox just so we can see the value of the sync enabled checkbox
		$I->click( "[name='variable_is_virtual[{$index}]']" );

		$I->seeElement( "#variable_facebook_sync_mode{$index}" );
		$I->seeOptionIsSelected( "#variable_facebook_sync_mode{$index}", 'Do not sync' );
	}


}
