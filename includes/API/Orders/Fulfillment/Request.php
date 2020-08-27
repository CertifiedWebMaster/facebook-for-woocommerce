<?php
/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

namespace SkyVerge\WooCommerce\Facebook\API\Orders\Fulfillment;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\Facebook\API;

/**
 * Orders API fulfillment request object.
 *
 * @since 2.1.0-dev.1
 */
class Request extends API\Request  {


	use API\Traits\Idempotent_Request;


	/**
	 * API request constructor.
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @param string $remote_id remote order ID
	 * @param array $fulfillment_data fulfillment data
	 */
	public function __construct( $remote_id, $fulfillment_data ) {

		parent::__construct( "/{$remote_id}/shipments", 'POST' );

		$fulfillment_data['idempotency_key'] = $this->get_idempotency_key();

		$this->set_data( $fulfillment_data );
	}


}
