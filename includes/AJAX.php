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

/**
 * AJAX handler.
 *
 * @since x.y.z
 */
class AJAX {


	/**
	 * AJAX handler constructor.
	 *
	 * @since x.y.z
	 */
	public function __construct() {

		// maybe output a modal prompt when toggling product sync in bulk or individual product actions
		add_action( 'wp_ajax_facebook_for_woocommerce_set_product_sync_prompt',             [ $this, 'handle_set_product_sync_prompt' ] );
		add_action( 'wp_ajax_facebook_for_woocommerce_set_product_sync_bulk_action_prompt', [ $this, 'handle_set_product_sync_bulk_action_prompt' ] );

		// set product visibility in Facebook
		add_action( 'wp_ajax_facebook_for_woocommerce_set_products_visibility', [ $this, 'set_products_visibility' ] );
	}


	/**
	 * Maybe triggers a modal warning when the merchant toggles sync enabled status on a product.
	 *
	 * @internal
	 *
	 * @since x.y.z
	 */
	public function handle_set_product_sync_prompt() {

		check_ajax_referer( 'set-product-sync-prompt', 'security' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$product_id   = isset( $_POST['product'] )      ? (int)    $_POST['product']      : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$sync_enabled = isset( $_POST['sync_enabled'] ) ? (string) $_POST['sync_enabled'] : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$product_cats = isset( $_POST['categories'] )   ? (array)  $_POST['categories']   : [];
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$product_tags = isset( $_POST['tags'] )         ? (array)  $_POST['tags']         : [];

		if ( $product_id > 0 && in_array( $sync_enabled, [ 'enabled', 'disabled' ], true ) ) {

			$product = wc_get_product( $product_id );

			if ( $product instanceof \WC_Product ) {

				if ( 'disabled' === $sync_enabled && Products::is_sync_enabled_for_product( $product ) ) {

					ob_start();

					?>
					<button
						id="facebook-for-woocommerce-hide-products"
						class="button button-large button-primary facebook-for-woocommerce-toggle-product-visibility hide-products"
					><?php esc_html_e( 'Hide Product', 'facebook-for-woocommerce' ); ?></button>
					<button
						id="facebook-for-woocommerce-do-not-hide-products"
						class="button button-large button-primary facebook-for-woocommerce-toggle-product-visibility show-products"
					><?php esc_html_e( 'Do Not Hide Product', 'facebook-for-woocommerce' ); ?></button>
					<?php

					$buttons = ob_get_clean();

					wp_send_json_error( [
						'message' => __( 'This product will no longer be updated in your Facebook catalog. Would you like to hide this product from your Facebook shop?', 'facebook-for-woocommerce' ),
						'buttons' => $buttons,
					] );

				} elseif ( 'enabled' === $sync_enabled ) {

					$has_excluded_terms = false;

					if ( $integration = facebook_for_woocommerce()->get_integration() ) {

						// try with categories first, since we have already IDs
						$has_excluded_terms = ! empty( $product_cats ) && array_intersect( $product_cats, $integration->get_excluded_product_category_ids() );

						// try next with tags, but WordPress only gives us tag names
						if ( ! $has_excluded_terms && ! empty( $product_tags ) ) {

							$product_tag_ids = [];

							foreach ( $product_tags as $product_tag_name ) {

								if ( $term = get_term_by( 'name', $product_tag_name, 'product_tag' ) ) {

									$product_tag_ids[] = $term->term_id;
								}
							}

							$has_excluded_terms = ! empty( $product_tag_ids ) && array_intersect( $product_tag_ids, $integration->get_excluded_product_tag_ids() );
						}
					}

					if ( $has_excluded_terms ) {

						ob_start();

						?>
						<a
							id="facebook-for-woocommerce-go-to-settings"
							class="button button-large"
							href="<?php echo esc_url( add_query_arg( 'section', \WC_Facebookcommerce::INTEGRATION_ID, admin_url( 'admin.php?page=wc-settings&tab=integration' ) ) ); ?>"
						><?php esc_html_e( 'Go to Settings', 'facebook-for-woocommerce' ); ?></a>
						<button
							id="facebook-for-woocommerce-cancel-sync"
							class="button button-large button-primary"
							onclick="jQuery( '.modal-close' ).trigger( 'click' )"
						><?php esc_html_e( 'Cancel', 'facebook-for-woocommerce' ); ?></button>
						<?php

						$buttons = ob_get_clean();

						wp_send_json_error( [
							'message' => __( 'This product belongs to a category or tag that is excluded from the Facebook catalog sync. To sync this product to Facebook, please remove the category or tag exclusion from the plugin settings.', 'facebook-for-woocommerce' ),
							'buttons' => $buttons,
						] );
					}
				}
			}
		}

		wp_send_json_success();
	}


	/**
	 * Maybe triggers a modal warning when the merchant toggles sync enabled status in bulk.
	 *
	 * @internal
	 *
	 * @since x.y.z
	 */
	public function handle_set_product_sync_bulk_action_prompt() {

		check_ajax_referer( 'set-product-sync-bulk-action-prompt', 'security' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$product_ids = isset( $_POST['products'] ) ? (array)  $_POST['products'] : [];
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$toggle      = isset( $_POST['toggle'] )   ? (string) $_POST['toggle']   : '';

		if ( ! empty( $product_ids ) && ! empty( $toggle ) ) {

			// merchant wants to exclude products from sync: ask them what they want to do with their visibility status
			if ( 'facebook_exclude' === $toggle ) {

				ob_start();

				?>
				<button
					id="facebook-for-woocommerce-hide-products"
					class="button button-large button-primary facebook-for-woocommerce-toggle-product-visibility hide-products"
				><?php esc_html_e( 'Hide Products', 'facebook-for-woocommerce' ); ?></button>
				<button
					id="facebook-for-woocommerce-do-not-hide-products"
					class="button button-large button-primary facebook-for-woocommerce-toggle-product-visibility show-products"
				><?php esc_html_e( 'Do Not Hide Products', 'facebook-for-woocommerce' ); ?></button>
				<?php

				$buttons = ob_get_clean();

				wp_send_json_error( [
					'message' => __( 'The selected products will no longer be updated in your Facebook catalog. Would you like to hide these products from your Facebook shop?', 'facebook-for-woocommerce' ),
					'buttons' => $buttons,
				] );

			// merchant wants to enable sync in Facebook multiple products: we must check if they belong to excluded categories, and perhaps warn them
			} elseif ( 'facebook_include' === $toggle && ( $integration = facebook_for_woocommerce()->get_integration() ) ) {

				$has_excluded_term = false;

				foreach ( $product_ids as $product_id ) {

					$product = wc_get_product( $product_id );

					// product belongs to at least one excluded term: break the loop
					if ( $product instanceof \WC_Product && Products::is_sync_excluded_for_product_terms( $product ) ) {

						$has_excluded_term = true;
						break;
					}
				}

				// show modal if there's at least one product that belongs to an excluded term
				if ( $has_excluded_term )  {

					ob_start();

					?>
					<a
						id="facebook-for-woocommerce-go-to-settings"
						class="button button-large"
						href="<?php echo esc_url( add_query_arg( 'section', \WC_Facebookcommerce::INTEGRATION_ID, admin_url( 'admin.php?page=wc-settings&tab=integration' ) ) ); ?>"
					><?php esc_html_e( 'Go to Settings', 'facebook-for-woocommerce' ); ?></a>
					<button
						id="facebook-for-woocommerce-cancel-sync"
						class="button button-large button-primary"
						onclick="jQuery( '.modal-close' ).trigger( 'click' )"
					><?php esc_html_e( 'Cancel', 'facebook-for-woocommerce' ); ?></button>
					<?php

					$buttons = ob_get_clean();

					wp_send_json_error( [
						'message' => __( 'One or more of the selected products belongs to a category or tag that is excluded from the Facebook catalog sync. To sync these products to Facebook, please remove the category or tag exclusion from the plugin settings.', 'facebook-for-woocommerce' ),
						'buttons' => $buttons,
					] );
				}
			}
		}

		wp_send_json_success();
	}


	/**
	 * Sets products visibility in Facebook.
	 *
	 * @internal
	 *
	 * @since x.y.z
	 */
	public function set_products_visibility() {

		check_ajax_referer( 'set-products-visibility', 'security' );

		$integration = facebook_for_woocommerce()->get_integration();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$products   = isset( $_POST['products'] ) ? (array) $_POST['products'] : [];

		if ( $integration && ! empty( $products ) ) {

			foreach ( $products as $product_data ) {

				$visibility_meta_value = isset( $product_data['visibility'] ) ? wc_string_to_bool( $product_data['visibility'] ) : null;

				if ( ! is_bool( $visibility_meta_value ) ) {
					continue;
				}

				$visibility_api_value = $visibility_meta_value ? $integration::FB_SHOP_PRODUCT_VISIBLE : $integration::FB_SHOP_PRODUCT_HIDDEN;

				$product_id = isset( $product_data['product_id'] ) ? absint( $product_data['product_id'] ) : 0;
				$product    = $product_id > 0 ? wc_get_product( $product_id ) : null;

				if ( $product instanceof \WC_Product ) {

					// also extend toggle to child variations
					if ( $product->is_type( 'variable' ) ) {

						foreach ( $product->get_children() as $variation_id ) {

							if ( $variation_product = wc_get_product( $variation_id ) ) {

								$fb_item_id = $integration->get_product_fbid( \WC_Facebookcommerce_Integration::FB_PRODUCT_ITEM_ID, $variation_product->get_id() );
								$fb_request = $integration->fbgraph->update_product_item( $fb_item_id, [
									'visibility' => $visibility_api_value,
								] );

								if ( $integration->check_api_result( $fb_request ) ) {
									Products::set_product_visibility( $variation_product, $visibility_meta_value );
								}
							}
						}

						Products::set_product_visibility( $product, $visibility_meta_value );

					} else {

						$fb_item_id = $integration->get_product_fbid( \WC_Facebookcommerce_Integration::FB_PRODUCT_ITEM_ID, $product->get_id() );
						$fb_request = $integration->fbgraph->update_product_item( $fb_item_id, [
							'visibility' => $visibility_api_value,
						] );

						if ( $integration->check_api_result( $fb_request ) ) {
							Products::set_product_visibility( $product, $visibility_meta_value );
						}
					}
				}
			}

			wp_send_json_success();
		}

		wp_send_json_error();
	}


}
