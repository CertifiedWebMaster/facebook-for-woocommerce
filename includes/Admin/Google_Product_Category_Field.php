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

/**
 * Google product category field.
 *
 * @since 2.1.0-dev.1
 */
class Google_Product_Category_Field {


	/** @var string the WordPress option name where the full categories list is stored */
	const OPTION_GOOGLE_PRODUCT_CATEGORIES = 'wc_facebook_google_product_categories';


	/**
	 * Instantiates the JS handler for the Google product category field.
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @param string $input_id element that should receive the latest concrete category ID value
	 */
	public function render( $input_id ) {

	}


	/**
	 * Gets the full categories list from Google and stores it.
	 *
	 * @since 2.1.0-dev.1
	 */
	public function get_categories() {

		// only fetch again if not fetched less than one hour ago
		$categories = get_transient( self::OPTION_GOOGLE_PRODUCT_CATEGORIES );

		if ( empty ( $categories ) ) {

			// fetch from the URL
			$categories_response = wp_remote_get( 'https://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt', [ 'timeout' => 1 ] );

			if ( is_array( $categories_response ) && isset( $categories_response['body'] ) ) {

				$categories = $categories_response['body'];

				// TODO: parse categories

				set_transient( self::OPTION_GOOGLE_PRODUCT_CATEGORIES, $categories, HOUR_IN_SECONDS );
				update_option( self::OPTION_GOOGLE_PRODUCT_CATEGORIES, $categories );
			}
		}

		if ( empty( $categories ) ) {

			// get the categories from the saved option
			$categories = get_option( self::OPTION_GOOGLE_PRODUCT_CATEGORIES );
		}

		return $categories;
	}


}
