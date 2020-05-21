<?php
/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

namespace SkyVerge\WooCommerce\Facebook;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_5_4 as Framework;


/**
 * API handler.
 *
 * @since 2.0.0-dev.1
 */
class API extends Framework\SV_WC_API_Base {


	/** @var string URI used for the request */
	protected $request_uri = 'https://graph.facebook.com/v7.0';

	/** @var string the configured access token */
	protected $access_token;


	/**
	 * Constructor.
	 *
	 * @since 2.0.0-dev.1
	 */
	public function __construct( $access_token ) {

		$this->access_token = $access_token;

		$this->request_headers = [
			'Authorization' => "Bearer {$access_token}",
		];
	}


	/**
	 * Gets a Page object from Facebook.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param $page_id page ID
	 * @return
	 */
	public function get_page( $page_id ) {

		// TODO: Implement get_page() method.
	}


	/**
	 * Uses the Catalog Batch API to update or remove items from catalog.
	 *
	 * @see Sync::create_or_update_products()
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param array $requests array of prefixed product IDs to create, update or remove
	 * @param bool $allow_upsert
	 */
	public function send_item_updates( $requests, $allow_upsert ) {

		// TODO: Implement send_item_updates() method.
	}


	/**
	 * Creates a Product Group object.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param string $catalog_id
	 * @param array $data
	 */
	public function create_product_group( $catalog_id, $data ) {

		// TODO: Implement create_product_group() method.
	}


	/**
	 * Updates the default product item and the available variation attributes of a product group.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param string $product_group_id
	 * @param array $data
	 */
	public function update_product_group( $product_group_id, $data ) {

		// TODO: Implement update_product_group() method.
	}


	/**
	 * Deletes a Product Group object.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param string $product_group_id
	 */
	public function delete_product_group( $product_group_id ) {

		// TODO: Implement delete_product_group() method.
	}


	/**
	 * Finds a Product Item using the Catalog ID and the Retailer ID of the product or product variation.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param string $catalog_id
	 * @param string $retailer_id
	 */
	public function find_product_item( $catalog_id, $retailer_id ) {

		// TODO: Implement find_product_item() method.
	}


	/**
	 * Creates a Product Item object.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param string $product_group_id
	 * @param array $data
	 */
	public function create_product_item( $product_group_id, $data ) {

		// TODO: Implement create_product_item() method.
	}


	/**
	 * Updates a Product Item object.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param string $product_group_id
	 * @param array $data
	 */
	public function update_product_item( $product_group_id, $data ) {

		// TODO: Implement update_product_item() method.
	}


	/**
	 * Deletes a Product Item object.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param string $product_group_id
	 */
	public function delete_product_item( $product_group_id ) {

		// TODO: Implement delete_product_item() method.
	}


	/**
	 * Stores an option with the delay, in seconds, for requests with the given rate limit ID.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param string $rate_limit_id
	 * @param int $delay
	 */
	public function set_rate_limit_delay( $rate_limit_id, $delay ) {

		// TODO: Implement set_rate_limit_delay() method.
	}


	/**
	 * Gets the number of seconds before a new request with the given rate limit ID can be made again
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param string $rate_limit_id
	 * @return int
	 */
	public function get_rate_limit_delay( $rate_limit_id ) {

		// TODO: Implement get_rate_limit_delay() method.
	}


	/**
	 * Uses the information in a Rate_Limited_Response object to calculate the next delay for requests of the same type.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param Rate_Limited_Response $response
	 * @return int
	 */
	protected function calculate_rate_limit_delay( $response ) {

		// TODO: Implement calculate_rate_limit_delay() method.
	}


	/**
	 * Returns a new request object.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param array $args optional request arguments
	 * @return \SkyVerge\WooCommerce\Facebook\API\Request
	 */
	protected function get_new_request( $args = [] ) {

		// TODO: Implement get_new_request() method.
	}


	/**
	 * Returns the plugin class instance associated with this API.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return \WC_Facebookcommerce
	 */
	protected function get_plugin() {

		return facebook_for_woocommerce();
	}


}
