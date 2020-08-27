<?php
/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

namespace SkyVerge\WooCommerce\Facebook\API\Orders\Refund;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\Facebook\API;

/**
 * Orders API refund request object.
 *
 * @since 2.1.0-dev.1
 */
class Request extends API\Request  {


	use API\Traits\Idempotent_Request;


	/** @var string buyer's remorse refund reason */
	const REASON_BUYERS_REMORSE = 'BUYERS_REMORSE';

	/** @var string damaged goods refund reason */
	const REASON_DAMAGED_GOODS = 'DAMAGED_GOODS';

	/** @var string not as described refund reason */
	const REASON_NOT_AS_DESCRIBED = 'NOT_AS_DESCRIBED';

	/** @var string quality issue refund reason */
	const REASON_QUALITY_ISSUE = 'QUALITY_ISSUE';

	/** @var string wrong item refund reason */
	const REASON_WRONG_ITEM = 'WRONG_ITEM';

	/** @var string other refund reason */
	const REASON_OTHER = 'REFUND_REASON_OTHER';


	/**
	 * API request constructor.
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @param string $remote_id remote order ID
	 * @param array $data refund data
	 */
	public function __construct( $remote_id, $data ) {

		parent::__construct( "/{$remote_id}/refunds", 'POST' );

		$data['idempotency_key'] = $this->get_idempotency_key();

		$this->set_data( $data );
	}


}
