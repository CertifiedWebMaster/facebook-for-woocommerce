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

use SkyVerge\WooCommerce\PluginFramework\v5_5_4 as Framework;

/**
 * General handler for order admin functionality.
 *
 * @since 2.1.0-dev.1
 */
class Orders {


	/**
	 * Handler constructor.
	 *
	 * @since 2.1.0-dev.1
	 */
	public function __construct() {

		$this->add_hooks();
	}


	/**
	 * Adds the necessary action & filter hooks.
	 *
	 * @since 2.1.0-dev.1
	 */
	public function add_hooks() {

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		add_action( 'admin_notices', [ $this, 'add_notices' ] );

		add_filter( 'wc_order_is_editable', [ $this, 'is_order_editable' ], 10, 2 );

		add_action( 'admin_footer', [ $this, 'render_modal_templates' ] );

		add_action( 'admin_footer', [ $this, 'render_refund_reason_field' ] );

		add_action( 'woocommerce_refund_created', [ $this, 'handle_refund' ] );

		add_action( 'woocommerce_email_enabled_customer_completed_order', [ $this, 'maybe_stop_order_email' ], 10, 2 );
		add_action( 'woocommerce_email_enabled_customer_processing_order', [ $this, 'maybe_stop_order_email' ], 10, 2 );
		add_action( 'woocommerce_email_enabled_customer_refunded_order', [ $this, 'maybe_stop_order_email' ], 10, 2 );

		add_action( 'admin_menu', [ $this, 'maybe_remove_order_metaboxes' ] );
	}


	/**
	 * Enqueue the assets.
	 *
	 * @internal
	 *
	 * @since 2.1.0-dev.1
	 */
	public function enqueue_assets() {

	}


	/**
	 * Adds admin notices.
	 *
	 * @internal
	 *
	 * @since 2.1.0-dev.1
	 */
	public function add_notices() {

	}


	/**
	 * Removes order metaboxes if the order is a Commerce pending order.
	 *
	 * @internal
	 *
	 * @since 2.1.0-dev.1
	 */
	public function maybe_remove_order_metaboxes() {

	}


	/**
	 * Renders the Complete, Refund, & Cancel modals templates markup.
	 *
	 * @internal
	 *
	 * @since 2.1.0-dev.1
	 */
	public function render_modal_templates() {

	}


	/**
	 * Renders the refund reason field.
	 *
	 * @internal
	 *
	 * @since 2.1.0-dev.1
	 */
	public function render_refund_reason_field() {

	}


	/**
	 * Sends a refund request to the Commerce API when a WC refund is created.
	 *
	 * @internal
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @param int $refund_id refund ID
	 */
	public function handle_refund( $refund_id ) {

	}


	/**
	 * Sets a transient to display a notice regarding bulk updates for Commerce orders' statuses.
	 *
	 * @internal
	 *
	 * @since 2.1.0-dev.1
	 */
	public function handle_bulk_update() {

	}


	/**
	 * Prevents sending emails for Commerce orders.
	 *
	 * @internal
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @param bool $is_enabled whether the email is enabled in the first place
	 * @param \WC_Order $order order object
	 * @return bool
	 */
	public function maybe_stop_order_email( $is_enabled, $order ) {

		// will decide whether to allow $is_enabled to be filtered
		$is_previously_enabled = $is_enabled;

		// checks whether or not the order is a Commerce order
		$is_commerce_order = $order instanceof \WC_Order && \SkyVerge\WooCommerce\Facebook\Commerce\Orders::is_commerce_order( $order );

		// decides whether to disable or to keep emails enabled
		$is_enabled = $is_enabled && ! $is_commerce_order;

		if ( $is_previously_enabled && $is_commerce_order ) {

			/**
			 * Filters the flag used to determine whether the email is enabled.
			 *
			 * @param bool $is_enabled whether the email is enabled
			 * @param Orders $this admin orders instance
			 * @since 2.1.0-dev.1
			 *
			 */
			$is_enabled = (bool) apply_filters( 'wc_facebook_commerce_send_woocommerce_emails', $is_enabled, $this );
		}

		return $is_enabled;
	}


	/**
	 * Determines whether or not the order is editable.
	 *
	 * @internal
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @param bool $maybe_editable whether the order is editable in the first place
	 * @param \WC_Order $order order object
	 * @return bool
	 */
	public function is_order_editable( $maybe_editable, \WC_Order $order ) {

		return $maybe_editable;
	}


	/**
	 * Determines whether or not the current screen is an orders screen.
	 *
	 * @internal
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @return bool
	 */
	public function is_orders_screen() {

		return Framework\SV_WC_Helper::is_current_screen( 'edit-shop_order' ) ||
		       Framework\SV_WC_Helper::is_current_screen( 'shop_order' );
	}


	/**
	 * Determines whether or not the current screen is an order edit screen.
	 *
	 * @internal
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @return bool
	 */
	public function is_edit_order_screen() {

		return Framework\SV_WC_Helper::is_current_screen( 'shop_order' );
	}


}
