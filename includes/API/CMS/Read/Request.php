<?php
/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

namespace SkyVerge\WooCommerce\Facebook\API\CMS\Read;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\Facebook\API;

/**
 * Commerce Merchant Settings API request object.
 *
 * @since 2.3.0
 */
class Request extends API\Request  {


	/**
	 * API request constructor.
	 *
	 * @since 2.3.0
	 *
	 * @param string $commerce_manager_id Commerce Manager ID
	 */
	public function __construct( $commerce_manager_id ) {

		parent::__construct( "/{$commerce_manager_id}", 'GET' );
	}


	/**
	 * Gets the request parameters.
	 *
	 * @since 2.3.0
	 *
	 * @return array
	 */
	public function get_params() {

		return [ 'fields' => 'cta,display_name,instagram_channel,facebook_channel,has_onsite_intent,setup_status' ];
	}


}
