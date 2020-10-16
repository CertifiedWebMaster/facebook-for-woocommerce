<?php
/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

namespace SkyVerge\WooCommerce\Facebook\Admin;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\Facebook\AJAX;
use SkyVerge\WooCommerce\Facebook\Products as Products_Handler;
use SkyVerge\WooCommerce\PluginFramework\v5_5_4 as Framework;

/**
 * General handler for product admin functionality.
 *
 * @since 2.1.0-dev.1
 */
class Products {


	/** @var string Commerce enabled field */
	const FIELD_COMMERCE_ENABLED = 'wc_facebook_commerce_enabled';

	/** @var string Google Product category ID field */
	const FIELD_GOOGLE_PRODUCT_CATEGORY_ID = 'wc_facebook_google_product_category_id';

	/** @var string gender field */
	const FIELD_GENDER = 'wc_facebook_gender';

	/** @var string color field */
	const FIELD_COLOR = 'wc_facebook_color';

	/** @var string size field */
	const FIELD_SIZE = 'wc_facebook_size';

	/** @var string pattern field */
	const FIELD_PATTERN = 'wc_facebook_pattern';

	public static function render_google_product_category_fields_and_enhanced_attributes( \WC_Product $product ) {
		$facebook_product = new \WC_Facebook_Product( $product->get_id() );
		?>
		<div class='wc_facebook_commerce_fields'>
			<?php \SkyVerge\WooCommerce\Facebook\Admin\Enhanced_Catalog_Attribute_Fields::render_hidden_input_can_show_attributes(); ?>
			<?php self::render_google_product_category_fields( $product ); ?>
			<?php
			self::render_enhanced_catalog_attributes_fields(
				Products_Handler::get_google_product_category_id( $product ),
				$facebook_product
			);
			?>
		 </div>
		<?php
	}

	public static function render_enhanced_catalog_attributes_fields( $category_id, \WC_Facebook_Product $product ) {
		$enhanced_attribute_fields = new Enhanced_Catalog_Attribute_Fields( \SkyVerge\WooCommerce\Facebook\Admin\Enhanced_Catalog_Attribute_Fields::PAGE_TYPE_EDIT_PRODUCT );
		$category_handler          = facebook_for_woocommerce()->get_facebook_category_handler();

		if ( $category_handler->get_category_depth( $category_id ) < 2 ) {
			// show nothing
			return;
		}

		?>
			<p class="form-field wc-facebook-enhanced-catalog-attribute-row">
				<label for="<?php echo esc_attr( \SkyVerge\WooCommerce\Facebook\Admin\Enhanced_Catalog_Attribute_Fields::FIELD_ENHANCED_CATALOG_ATTRIBUTES_ID ); ?>">
					<?php echo esc_html( self::render_enhanced_catalog_attributes_title() ); ?>
					<?php self::render_enhanced_catalog_attributes_tooltip(); ?>
				</label>
			</p>
			<?php $enhanced_attribute_fields->render( $category_id ); ?>
		<?php
	}

	/**
	 * Renders the common tooltip markup.
	 *
	 * @internal
	 *
	 * @since 2.1.0-dev.1
	 */
	public static function render_enhanced_catalog_attributes_tooltip() {

		$tooltip_text = __( 'Select values for enhanced attributes for this product', 'facebook-for-woocommerce' );

		?>
			<span class="woocommerce-help-tip" data-tip="<?php echo esc_attr( $tooltip_text ); ?>"></span>
		<?php
	}

	/**
	 * Gets the common field title.
	 *
	 * @internal
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @return string
	 */
	public static function render_enhanced_catalog_attributes_title() {

		return __( 'Enhanced Catalog Attributes', 'facebook-for-woocommerce' );
	}

	/**
	 * Renders the Google product category fields.
	 *
	 * @internal
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @param \WC_Product $product product object
	 */
	public static function render_google_product_category_fields( \WC_Product $product ) {

		$field = new Google_Product_Category_Field();

		$field->render( self::FIELD_GOOGLE_PRODUCT_CATEGORY_ID );

		?>
		<p class="form-field">
			<label for="<?php echo esc_attr( self::FIELD_GOOGLE_PRODUCT_CATEGORY_ID ); ?>">
				<?php esc_html_e( 'Google product category', 'facebook-for-woocommerce' ); ?>
				<?php echo wc_help_tip( __( 'Choose the Google product category and (optionally) sub-categories associated with this product.', 'facebook-for-woocommerce' ) ); ?>
			</label>
			<input
				id="<?php echo esc_attr( self::FIELD_GOOGLE_PRODUCT_CATEGORY_ID ); ?>"
				type="hidden"
				name="<?php echo esc_attr( self::FIELD_GOOGLE_PRODUCT_CATEGORY_ID ); ?>"
				value="<?php echo esc_attr( Products_Handler::get_google_product_category_id( $product ) ); ?>"
			/>
		</p>
		<?php
	}


	/**
	 * Renders the attribute fields.
	 *
	 * @internal
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @param \WC_Product $product product object
	 */
	public static function render_attribute_fields( \WC_Product $product ) {

		woocommerce_wp_select(
			array(
				'id'          => self::FIELD_GENDER,
				'label'       => __( 'Gender', 'facebook-for-woocommerce' ),
				'description' => __( "Select the product's gender for sizing.", 'facebook-for-woocommerce' ),
				'desc_tip'    => true,
				'options'     => array(
					'unisex' => __( 'Unisex', 'facebook-for-woocommerce' ),
					'female' => __( 'Female', 'facebook-for-woocommerce' ),
					'male'   => __( 'Male', 'facebook-for-woocommerce' ),
				),
				'value'       => Products_Handler::get_product_gender( $product ),
			)
		);

		woocommerce_wp_select(
			array(
				'id'                => self::FIELD_COLOR,
				'label'             => __( 'Color attribute', 'facebook-for-woocommerce' ),
				'description'       => __( "Optionally select the attribute associated with the product's colors.", 'facebook-for-woocommerce' ),
				'desc_tip'          => true,
				'class'             => 'sv-wc-enhanced-search select short',
				'style'             => 'width: 50%',
				'options'           => self::filter_available_product_attribute_names( $product, array( 'color', 'colour', __( 'color', 'facebook-for-woocommerce' ) ) ),
				'value'             => Products_Handler::get_product_color_attribute( $product ),
				'custom_attributes' => array(
					'data-allow_clear'  => true,
					'data-placeholder'  => __( 'Search attributes...', 'facebook-for-woocommerce' ),
					'data-action'       => AJAX::ACTION_SEARCH_PRODUCT_ATTRIBUTES,
					'data-nonce'        => wp_create_nonce( AJAX::ACTION_SEARCH_PRODUCT_ATTRIBUTES ),
					'data-request_data' => $product->get_id(),
				),
			)
		);

		woocommerce_wp_select(
			array(
				'id'                => self::FIELD_SIZE,
				'label'             => __( 'Size attribute', 'facebook-for-woocommerce' ),
				'description'       => __( "Optionally select the attribute associated with the product's sizes.", 'facebook-for-woocommerce' ),
				'desc_tip'          => true,
				'class'             => 'sv-wc-enhanced-search select short',
				'style'             => 'width: 50%',
				'options'           => self::filter_available_product_attribute_names( $product, array( 'size', __( 'size', 'facebook-for-woocommerce' ) ) ),
				'value'             => Products_Handler::get_product_size_attribute( $product ),
				'custom_attributes' => array(
					'data-allow_clear'  => true,
					'data-placeholder'  => __( 'Search attributes...', 'facebook-for-woocommerce' ),
					'data-action'       => AJAX::ACTION_SEARCH_PRODUCT_ATTRIBUTES,
					'data-nonce'        => wp_create_nonce( AJAX::ACTION_SEARCH_PRODUCT_ATTRIBUTES ),
					'data-request_data' => $product->get_id(),
				),
			)
		);

		woocommerce_wp_select(
			array(
				'id'                => self::FIELD_PATTERN,
				'label'             => __( 'Pattern attribute', 'facebook-for-woocommerce' ),
				'description'       => __( "Optionally select the attribute associated with the product's patterns.", 'facebook-for-woocommerce' ),
				'desc_tip'          => true,
				'class'             => 'sv-wc-enhanced-search select short',
				'style'             => 'width: 50%',
				'options'           => self::filter_available_product_attribute_names( $product, array( 'pattern', __( 'pattern', 'facebook-for-woocommerce' ) ) ),
				'value'             => Products_Handler::get_product_pattern_attribute( $product ),
				'custom_attributes' => array(
					'data-allow_clear'  => true,
					'data-placeholder'  => __( 'Search attributes...', 'facebook-for-woocommerce' ),
					'data-action'       => AJAX::ACTION_SEARCH_PRODUCT_ATTRIBUTES,
					'data-nonce'        => wp_create_nonce( AJAX::ACTION_SEARCH_PRODUCT_ATTRIBUTES ),
					'data-request_data' => $product->get_id(),
				),
			)
		);

		Framework\SV_WC_Helper::render_select2_ajax();
	}


	/**
	 * Gets a list of attribute names and labels that match any of the given words.
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @param \WC_Product $product the product object
	 * @param array       $words a list of words used to filter attributes
	 * @return array
	 */
	private static function filter_available_product_attribute_names( \WC_Product $product, $words ) {

		$attributes = array();

		foreach ( self::get_available_product_attribute_names( $product ) as $name => $label ) {

			foreach ( $words as $word ) {

				if ( Framework\SV_WC_Helper::str_exists( wc_strtolower( $label ), $word ) || Framework\SV_WC_Helper::str_exists( wc_strtolower( $name ), $word ) ) {
					$attributes[ $name ] = $label;
				}
			}
		}

		return $attributes;
	}


	/**
	 * Gets a indexed list of available product attributes with the name of the attribute as key and the label as the value.
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @param \WC_Product $product the product object
	 * @return array
	 */
	public static function get_available_product_attribute_names( \WC_Product $product ) {

		return array_map(
			function( $attribute ) use ( $product ) {
				return wc_attribute_label( $attribute->get_name(), $product );
			},
			Products_Handler::get_available_product_attributes( $product )
		);
	}


	/**
	 * Renders the Commerce settings fields.
	 *
	 * @internal
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @param \WC_Product $product product object
	 */
	public static function render_commerce_fields( \WC_Product $product ) {

		?>
		<p class="form-field <?php echo esc_attr( self::FIELD_COMMERCE_ENABLED ); ?>_field">
			<label for="<?php echo esc_attr( self::FIELD_COMMERCE_ENABLED ); ?>">
				<?php echo esc_html_e( 'Sell on Instagram', 'facebook-for-woocommerce' ); ?>
				<span class="woocommerce-help-tip"
					  data-tip="<?php echo esc_attr_e( 'Enable to sell this product on Instagram. Products that are hidden in the Facebook catalog can be synced, but won’t be available for purchase.', 'facebook-for-woocommerce' ); ?>"></span>
			</label>
			<input type="checkbox" class="enable-if-sync-enabled"
				   name="<?php echo esc_attr( self::FIELD_COMMERCE_ENABLED ); ?>"
				   id="<?php echo esc_attr( self::FIELD_COMMERCE_ENABLED ); ?>" value="yes"
				   checked="<?php echo Products_Handler::is_commerce_enabled_for_product( $product ) ? 'checked' : ''; ?>">
		</p>

		<div id="product-not-ready-notice" style="display:none;">
			<p>
				<?php esc_html_e( 'This product does not meet the requirements to sell on Instagram.', 'facebook-for-woocommerce' ); ?>
				<a href="#" id="product-not-ready-notice-open-modal"><?php esc_html_e( 'Click here to learn more.', 'facebook-for-woocommerce' ); ?></a>
			</p>
		</div>

		<div id="variable-product-not-ready-notice" style="display:none;">
			<p>
			<?php
			echo sprintf(
				/* translators: Placeholders %1$s - strong opening tag, %2$s - strong closing tag */
				esc_html__( 'To sell this product on Instagram, at least one variation must be synced to Facebook. You can control variation sync on the %1$sVariations%2$s tab with the %1$sFacebook Sync%2$s setting.', 'facebook-for-woocommerce' ),
				'<strong>',
				'</strong>'
			);
			?>
			</p>
		</div>

		<?php
		// TODO: REMOVE
		// <div class='wc_facebook_commerce_fields'>
		// <?php self::render_google_product_category_fields( $product ); ? >
		// <?php self::render_attribute_fields( $product ); ? >
		// </div>
	}


	/**
	 * Saves the Commerce settings.
	 *
	 * @internal
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @param \WC_Product $product product object
	 */
	public static function save_commerce_fields( \WC_Product $product ) {

		$commerce_enabled           = wc_string_to_bool( Framework\SV_WC_Helper::get_posted_value( self::FIELD_COMMERCE_ENABLED ) );
		$google_product_category_id = wc_clean( Framework\SV_WC_Helper::get_posted_value( self::FIELD_GOOGLE_PRODUCT_CATEGORY_ID ) );
		$gender                     = wc_clean( Framework\SV_WC_Helper::get_posted_value( self::FIELD_GENDER ) );
		$color_attribute            = wc_clean( Framework\SV_WC_Helper::get_posted_value( self::FIELD_COLOR ) );
		$size_attribute             = wc_clean( Framework\SV_WC_Helper::get_posted_value( self::FIELD_SIZE ) );
		$pattern_attribute          = wc_clean( Framework\SV_WC_Helper::get_posted_value( self::FIELD_PATTERN ) );

		Products_Handler::update_commerce_enabled_for_product( $product, $commerce_enabled );

		if ( $google_product_category_id !== Products_Handler::get_google_product_category_id( $product ) ) {

			Products_Handler::update_google_product_category_id( $product, $google_product_category_id );
		}

		Products_Handler::update_product_gender( $product, $gender );

		try {

			Products_Handler::update_product_color_attribute( $product, $color_attribute );
			Products_Handler::update_product_size_attribute( $product, $size_attribute );
			Products_Handler::update_product_pattern_attribute( $product, $pattern_attribute );

		} catch ( Framework\SV_WC_Plugin_Exception $e ) {

			$message = sprintf(
				/* translators: Placeholders %1$s - product ID, %2$s - exception message */
				__( 'There was an error trying to save the product attributes for product %1$s: %2$s' ),
				$product->get_id(),
				$e->getMessage()
			);

			facebook_for_woocommerce()->log( $message );
		}
	}


}
