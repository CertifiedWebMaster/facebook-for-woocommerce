<?php
/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

namespace SkyVerge\WooCommerce\Facebook\Admin\Settings_Screens;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\Facebook\Admin;

/**
 * The Messenger settings screen object.
 */
class Product_Sync extends Admin\Abstract_Settings_Screen {


	/** @var string screen ID */
	const ID = 'product_sync';


	/**
	 * Connection constructor.
	 */
	public function __construct() {

		$this->id    = self::ID;
		$this->label = __( 'Product sync', 'facebook-for-woocommerce' );
		$this->title = __( 'Product sync', 'facebook-for-woocommerce' );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		add_action( 'woocommerce_admin_field_product_sync_title', [ $this, 'render_title' ] );
	}


	/**
	 * Enqueues the assets.
	 *
	 * @internal
	 *
	 * @since 2.0.0-dev.1
	 */
	public function enqueue_assets() {

		wp_enqueue_script( 'wc-backbone-modal', null, [ 'backbone' ] );
		wp_enqueue_script( 'facebook-for-woocommerce-modal', plugins_url( '/facebook-for-woocommerce/assets/js/facebook-for-woocommerce-modal.min.js' ), [ 'jquery', 'wc-backbone-modal', 'jquery-blockui' ], \WC_Facebookcommerce::PLUGIN_VERSION );
		wp_enqueue_script( 'facebook-for-woocommerce-settings-sync', plugins_url( '/facebook-for-woocommerce/assets/js/admin/facebook-for-woocommerce-settings-sync.min.js' ), [ 'jquery', 'wc-backbone-modal', 'jquery-blockui', 'facebook-for-woocommerce-modal' ], \WC_Facebookcommerce::PLUGIN_VERSION );

		/* translators: Placeholders %1$s - opening <strong> html tag, %2$s closing </strong> html tag, {count} number of remaining items */
		$sync_remaining_items_string = _n_noop( '%1$sProgress:%2$s {count} item remaining.', '%1$sProgress:%2$s {count} items remaining.', 'facebook-for-woocommerce' );

		wp_localize_script( 'facebook-for-woocommerce-settings-sync', 'facebook_for_woocommerce_settings_sync', [
			'ajax_url'                        => admin_url( 'admin-ajax.php' ),
			'set_excluded_terms_prompt_nonce' => wp_create_nonce( 'set-excluded-terms-prompt' ),
			'excluded_category_ids'           => facebook_for_woocommerce()->get_integration()->get_excluded_product_category_ids(),
			'excluded_tag_ids'                => facebook_for_woocommerce()->get_integration()->get_excluded_product_tag_ids(),
			'i18n'                            => [
				/* translators: Placeholders %s - html code for a spinner icon */
				'confirm_resync'                => esc_html__( 'Your products will now be resynced to Facebook, this may take some time.', 'facebook-for-woocommerce' ),
				'confirm_sync'                  => esc_html__( "Facebook for WooCommerce automatically syncs your products on create/update. Are you sure you want to force product resync?\n\nThis will query all published products and may take some time. You only need to do this if your products are out of sync or some of your products did not sync.", 'facebook-for-woocommerce' ),
				'sync_in_progress'              => sprintf( esc_html__( 'Syncing... Keep this browser open until sync is complete. %s', 'facebook-for-woocommerce' ), '<span class="spinner is-active"></span>' ),
				'sync_remaining_items_singular' => sprintf( esc_html( translate_nooped_plural( $sync_remaining_items_string, 1 ) ), '<strong>', '</strong>', '<span class="spinner is-active"></span>' ),
				'sync_remaining_items_plural'   => sprintf( esc_html( translate_nooped_plural( $sync_remaining_items_string, 2 ) ), '<strong>', '</strong>', '<span class="spinner is-active"></span>' ),
				'general_error'                 => esc_html__( 'There was an error trying to sync the products to Facebook.', 'facebook-for-woocommerce' ),
				'feed_upload_error'             => esc_html__( 'Something went wrong while uploading the product information, please try again.', 'facebook-for-woocommerce' ),
			],
		] );
	}


	/**
	 * Renders the custom title.
	 *
	 * @internal
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param array $field field data
	 */
	public function render_title( $field ) {

		?>

		<h2>

			<?php esc_html_e( 'Product sync', 'facebook-for-woocommerce' ); ?>

			<?php if ( facebook_for_woocommerce()->get_connection_handler()->is_connected() ) : ?>
				<a
					id="woocommerce-facebook-settings-sync-products"
					class="button product-sync-field"
					href="#"
					style="vertical-align: middle; margin-left: 20px;"
				><?php esc_html_e( 'Sync products', 'facebook-for-woocommerce' ); ?></a>
			<?php endif; ?>

		</h2>
		<div><p id="sync_progress" style="display: none"></p></div>
		<table class="form-table">

		<?php
	}


	/**
	 * Gets the screen settings.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return array
	 */
	public function get_settings() {

		$term_query = new \WP_Term_Query( [
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'fields'     => 'id=>name',
		] );

		$product_categories = $term_query->get_terms();

		$term_query = new \WP_Term_Query( [
			'taxonomy'     => 'product_tag',
			'hide_empty'   => false,
			'hierarchical' => false,
			'fields'       => 'id=>name',
		] );

		$product_tags = $term_query->get_terms();

		return [

			[
				'type'  => 'product_sync_title',
				'title' => __( 'Product sync', 'facebook-for-woocommerce' ),
			],

			[
				'id'      => \WC_Facebookcommerce_Integration::SETTING_ENABLE_PRODUCT_SYNC,
				'title'   => __( 'Enable product sync', 'facebook-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => ' ',
				'default' => 'yes',
			],

			[
				'id'                => \WC_Facebookcommerce_Integration::SETTING_EXCLUDED_PRODUCT_CATEGORY_IDS,
				'title'             => __( 'Exclude categories from sync', 'facebook-for-woocommerce' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select product-sync-field',
				'css'               => 'min-width: 300px;',
				'desc_tip'          => __( 'Products in one or more of these categories will not sync to Facebook.', 'facebook-for-woocommerce' ),
				'default'           => [],
				'options'           => is_array( $product_categories ) ? $product_categories : [],
				'custom_attributes' => [
					'data-placeholder' => __( 'Search for a product category&hellip;', 'facebook-for-woocommerce' ),
				],
			],

			[
				'id'                => \WC_Facebookcommerce_Integration::SETTING_EXCLUDED_PRODUCT_TAG_IDS,
				'title'             => __( 'Exclude tags from sync', 'facebook-for-woocommerce' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select product-sync-field',
				'css'               => 'min-width: 300px;',
				'desc_tip'          => __( 'Products with one or more of these tags will not sync to Facebook.', 'facebook-for-woocommerce' ),
				'default'           => [],
				'options'           => is_array( $product_tags ) ? $product_tags : [],
				'custom_attributes' => [
					'data-placeholder' => __( 'Search for a product tag&hellip;', 'facebook-for-woocommerce' ),
				],
			],

			[
				'id'       => \WC_Facebookcommerce_Integration::SETTING_PRODUCT_DESCRIPTION_MODE,
				'title'    => __( 'Product description sync', 'facebook-for-woocommerce' ),
				'type'     => 'select',
				'class'    => 'product-sync-field',
				'desc_tip' => __( 'Choose which product description to display in the Facebook catalog.', 'facebook-for-woocommerce' ),
				'default'  => \WC_Facebookcommerce_Integration::PRODUCT_DESCRIPTION_MODE_STANDARD,
				'options'  => [
					\WC_Facebookcommerce_Integration::PRODUCT_DESCRIPTION_MODE_STANDARD => __( 'Standard description', 'facebook-for-woocommerce' ),
					\WC_Facebookcommerce_Integration::PRODUCT_DESCRIPTION_MODE_SHORT    => __( 'Short description', 'facebook-for-woocommerce' ),
				],
			],

			[ 'type' => 'sectionend' ],

		];
	}


	/**
	 * Gets the "disconnected" message.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return string
	 */
	public function get_disconnected_message() {

		return sprintf(
			/* translators: Placeholders: %1$s - <a> tag, %2$s - </a> tag */
			__( 'Please %1$sconnect to Facebook%2$s to enable and manage product sync.', 'facebook-for-woocommerce' ),
			'<a href="' . esc_url( facebook_for_woocommerce()->get_connection_handler()->get_connect_url() ) . '">', '</a>'
		);
	}


}
