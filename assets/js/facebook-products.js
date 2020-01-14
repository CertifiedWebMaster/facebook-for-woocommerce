/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

/*
 *  Ajax helper function.
 *  Takes optional payload for POST and optional callback.
 */
function ajax(action, payload = null, cb = null, failcb = null) {
	var data = Object.assign( {},
			{
				'action': action,
			}, payload
		);

	// Since  Wordpress 2.8 ajaxurl is always defined in admin header and
	// points to admin-ajax.php
	jQuery.post(
		ajaxurl,
		data,
		function(response) {
			if (cb) {
				cb( response );
			}
		}
	).fail(
		function(errorResponse){
			if (failcb) {
				failcb( errorResponse );
			}
		}
	);
}

function fb_toggle_visibility(wp_id, published) {
	var buttonId = document.querySelector( "#viz_" + wp_id );
	var tooltip  = document.querySelector( "#tip_" + wp_id );

	if (published) {
		tooltip.setAttribute(
			'data-tip',
			'Product is synced and published (visible) on Facebook.'
		);
		buttonId.setAttribute( 'onclick','fb_toggle_visibility(' + wp_id + ', false)' );
		buttonId.innerHTML = 'Hide';
		buttonId.setAttribute( 'class', 'button' );
	} else {
		tooltip.setAttribute(
			'data-tip',
			'Product is synced but not marked as published (visible) on Facebook.'
		);
		buttonId.setAttribute( 'onclick','fb_toggle_visibility(' + wp_id + ', true)' );
		buttonId.innerHTML = 'Show';
		buttonId.setAttribute( 'class', 'button button-primary button-large' );
	}

	// Reset tooltip
	jQuery(
		function($) {
			$( '.tips' ).tipTip(
				{
					'attribute': 'data-tip',
					'fadeIn': 50,
					'fadeOut': 50,
					'delay': 200
				}
			);
		}
	);

	return ajax(
		'ajax_fb_toggle_visibility',
		{
			'wp_id': wp_id,
			'published': published,
			"_ajax_nonce": wc_facebook_product_jsx.nonce
		}
	);
}

jQuery( document ).ready( function( $ ) {

	let submitBulkAction = false;

	$( 'input#doaction, input#doaction2' ).on( 'click', function( e ) {

		if ( ! submitBulkAction ) {
			e.preventDefault();
		} else {
			return true;
		}

		let $submitButton    = $( this ),
			chosenBulkAction = $submitButton.prev( 'select' ).val();

		if ( 'facebook_exclude' === chosenBulkAction || 'facebook_include' === chosenBulkAction ) {

			let products = [];

			$.each( $( 'input[name="post[]"]:checked' ), function() {
				products.push( parseInt( $( this ).val(), 10 ) );
			} );

			$.post( wc_facebook_product_jsx.admin_url, {
				action:   'facebook_for_woocommerce_set_product_sync_bulk_action',
				security: wc_facebook_product_jsx.set_product_sync_bulk_action_nonce,
				toggle:   chosenBulkAction,
				products: products
			}, function( response ) {

				if ( response && ! response.success ) {

					// close existing modals
					$( '#wc-backbone-modal-dialog .modal-close' ).trigger( 'click' );

					// open new modal, populate template with AJAX response data
					new $.WCBackboneModal.View( {
						target: 'facebook-for-woocommerce-modal',
						string: response.data
					} );

					// exclude from sync: offer to handle product visibility
					$( '.facebook-for-woocommerce-toggle-product-visibility' ).on( 'click', function( e) {

						let $promptButton = $( this );

						$.each( products, function() {

							let $toggle = $( '#post-' + this ).find( 'td.facebook_shop_visibility a' );

							switch ( $toggle.data( 'product-visibility' ) ) {
								case 'visible' :
									if ( $promptButton.hasClass( 'hide-products' ) ) {
										$toggle.trigger( 'click' );
									}
								break;
								case 'hidden' :
									if ( $promptButton.hasClass( 'show-products' ) ) {
										$toggle.trigger( 'click' );
									}
								break;
							}
						} );

						// submit form after modal prompt action
						submitBulkAction = true;
						$submitButton.trigger( 'click' );
					} );

				} else {

					// no modal displayed: submit form as normal
					submitBulkAction = true;
					$submitButton.trigger( 'click' );
				}
			} );
		}
	} );

} );
