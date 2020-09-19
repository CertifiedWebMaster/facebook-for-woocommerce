/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

jQuery( document ).ready( function( $ ) {

	const pagenow = window.pagenow.length ? window.pagenow : '',
	      typenow = window.typenow.length ? window.typenow : '';


	// products list edit screen
	if ( 'edit-product' === pagenow ) {

		// handle bulk actions
		let submitProductBulkAction = false;

		$( 'input#doaction, input#doaction2' ).on( 'click', function( e ) {

			if ( ! submitProductBulkAction ) {
				e.preventDefault();
			} else {
				return true;
			}

			let $submitButton    = $( this ),
				chosenBulkAction = $submitButton.prev( 'select' ).val();

			if ( 'facebook_include' === chosenBulkAction ) {

				let products = [];

				$.each( $( 'input[name="post[]"]:checked' ), function() {
					products.push( parseInt( $( this ).val(), 10 ) );
				} );

				$.post( facebook_for_woocommerce_products_admin.ajax_url, {
					action:   'facebook_for_woocommerce_set_product_sync_bulk_action_prompt',
					security: facebook_for_woocommerce_products_admin.set_product_sync_bulk_action_prompt_nonce,
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

					} else {

						// no modal displayed: submit form as normal
						submitProductBulkAction = true;
						$submitButton.trigger( 'click' );
					}
				} );

			} else {

				// no modal displayed: submit form as normal
				submitProductBulkAction = true;
				$submitButton.trigger( 'click' );
			}
		} );
	}


	// individual product edit screen
	if ( 'product' === pagenow ) {

		/**
		 * Toggles (enables/disables) Facebook setting fields.
		 *
		 * @since 1.10.0
		 *
		 * @param {boolean} enabled whether the settings fields should be enabled or not
		 * @param {jQuery} $container a common ancestor of all the elements that can be enabled/disabled
		 */
		function toggleFacebookSettings( enabled, $container ) {

			$container.find( '.enable-if-sync-enabled' ).prop( 'disabled', ! enabled );
		}


		/**
		 * Toggles (shows/hides) Sync and show option and changes the select value if needed.
		 *
		 * @since 2.0.0
		 *
		 * @param {boolean} show whether the Sync and show option should be displayed or not
		 * @param {jQuery} $select the sync mode select
		 */
		function toggleSyncAndShowOption( show, $select ) {

			if ( ! show ) {

				// hide Sync and show option
				$select.find( 'option[value=\'sync_and_show\']' ).hide();

				if ( 'sync_and_show' === $select.val() ) {
					// change selected option to Sync and hide
					$select.val( 'sync_and_hide' );
				}

			} else {

				// show Sync and Show option
				$select.find( 'option[value=\'sync_and_show\']' ).show();

				// restore originally selected option
				if ( $select.prop( 'original' ) ) {
					$select.val( $select.prop( 'original' ) );
				}
			}
		}


		/**
		 * Toggles (shows/hides) the visibility of Facebook Commerce setting fields.
		 *
		 * @since 2.1.0-dev.1
		 *
		 * @param {boolean} enabled whether the settings fields should be enabled or not
		 * @param {jQuery} $container a common ancestor of all the elements that can be shown/hidden
		 */
		function toggleFacebookCommerceSettings( enabled, $container ) {

			let $group = $container.find( '.wc-facebook-commerce-options-group' );

			if ( enabled ) {
				$group.show();
			} else {
				$group.hide();
			}
		}


		/**
		 * Disables and changes the checked status of the Sell on Instagram setting field.
		 *
		 * Additionally, shows/hides messages explaining that the product is not ready for Commerce.
		 *
		 * @param {boolean} enabled whether the setting field should be enabled or not
		 * @param {jQuery} $container a common ancestor of all the elements that need to modified
		 */
		function toggleFacebookSellOnInstagramSetting( enabled, $container ) {

			let $field = $container.find( '#wc_facebook_commerce_enabled' );
			let checked = $field.prop( 'original' );

			$field.prop( 'checked', enabled ? checked : false ).prop( 'disabled', ! enabled );

			// trigger change to hide fields based on the new state
			$field.trigger( 'change' );

			// restore previously stored value so that we can later restore the field to the status it had before we disabled it here
			$field.prop( 'original', checked );

			if ( $( 'select#product-type' ).val().match( /variable/ ) ) {

				if ( enabled ) {
					$container.find( '#variable-product-not-ready-notice' ).hide();
				} else {
					$container.find( '#variable-product-not-ready-notice' ).show();
				}

			} else {

				// TODO: toggle product not ready message

			}
		}


		/**
		 * Determines whether the current product has synced variations.
		 *
		 * @returns {boolean}
		 */
		function isSyncEnabledForVariableProduct() {

			let $fields = $( '.js-variable-fb-sync-toggle' );

			// fallback to the value at page load if the variation fields haven't been loaded
			if ( 0 === $fields.length ) {
				return !! facebook_for_woocommerce_products_admin.is_sync_enabled_for_product;
			}

			// returns true if any of the Facebook Sync settings is set to a value other than 'sync_disabled'
			return !! $fields.map( ( i, element ) => $( element ).val() !== 'sync_disabled' ? element : null ).length
		}


		// handle change events for the Sell on Instagram checkbox field
		$( '#facebook_options #wc_facebook_commerce_enabled' ).on( 'change', function() {
			console.log( 'setting original to ', $( this ).prop( 'checked' ) );
			$( this ).prop( 'original', $( this ).prop( 'checked' ) );
		} ).trigger( 'change' );

		// toggle Facebook settings fields for simple products
		const syncModeSelect   = $( '#wc_facebook_sync_mode' );
		const facebookSettingsPanel = syncModeSelect.closest( '.woocommerce_options_panel' );

		syncModeSelect.on( 'change', function() {

			let syncEnabled = $( this ).val() !== 'sync_disabled';

			toggleFacebookSettings( syncEnabled, facebookSettingsPanel );
			toggleFacebookCommerceSettings( syncEnabled, facebookSettingsPanel );

			syncModeSelect.prop( 'original', $( this ).val() );

		} ).trigger( 'change' );

		$( '#_virtual' ).on( 'change', function () {
			toggleSyncAndShowOption( ! $( this ).prop( 'checked' ), syncModeSelect );
		} ).trigger( 'change' );

		// toggle Facebook settings fields for variations
		$( '.woocommerce_variations' ).on( 'change', '.js-variable-fb-sync-toggle', function() {

			toggleFacebookSettings( $( this ).val() !== 'sync_disabled', $( this ).closest( '.wc-metabox-content' ) );
			toggleFacebookSellOnInstagramSetting( isSyncEnabledForVariableProduct(), $( '#facebook_options' ) );

			$( this ).prop( 'original', $( this ).val() );
		} );

		$( '#woocommerce-product-data' ).on( 'woocommerce_variations_loaded', function () {

			$( '.js-variable-fb-sync-toggle' ).each( function () {
				toggleFacebookSettings( $( this ).val() !== 'sync_disabled', $( this ).closest( '.wc-metabox-content' ) );
				$( this ).prop( 'original', $( this ).val() );
			} );

			$( '.variable_is_virtual' ).on( 'change', function () {
				const jsSyncModeToggle = $( this ).closest( '.wc-metabox-content' ).find( '.js-variable-fb-sync-toggle' );
				toggleSyncAndShowOption( ! $( this ).prop( 'checked' ), jsSyncModeToggle );
			} );

			toggleFacebookSellOnInstagramSetting( isSyncEnabledForVariableProduct(), $( '#facebook_options' ) );
		} );

		// show/hide Custom Image URL setting
		$( '#woocommerce-product-data' ).on( 'change', '.js-fb-product-image-source', function() {

			let $container  = $( this ).closest( '.woocommerce_options_panel, .wc-metabox-content' );
			let imageSource = $( this ).val();

			$container.find( '.product-image-source-field' ).closest( '.form-field' ).hide();
			$container.find( `.show-if-product-image-source-${imageSource}` ).closest( '.form-field' ).show();
		} );

		$( '.js-fb-product-image-source:checked:visible' ).trigger( 'change' );

		// trigger settings fields modifiers when variations are loaded
		$( '#woocommerce-product-data' ).on( 'woocommerce_variations_loaded', function() {
			$( '.js-variable-fb-sync-toggle:visible' ).trigger( 'change' );
			$( '.js-fb-product-image-source:checked:visible' ).trigger( 'change' );
			$( '.variable_is_virtual:visible' ).trigger( 'change' );
		} );


		// TODO: store original value for the Sell on Instagram checkbox

		// toggle Sell on Instagram checkbox on page load
		// TODO: replace isSyncEnabledForVariableProduct() with a isProductReadyForCommerce() function that uses isSyncEnabledForVariableProduct() to determine whether the product is ready
		toggleFacebookSellOnInstagramSetting( isSyncEnabledForVariableProduct(), facebookSettingsPanel );

		let submitProductSave = false;

		$( 'form#post input[type="submit"]' ).on( 'click', function( e ) {

			if ( ! submitProductSave ) {
				e.preventDefault();
			} else {
				return true;
			}

			let $submitButton    = $( this ),
				productID        = parseInt( $( 'input#post_ID' ).val(), 10 ),
				productCat       = [],
				// this query will get tags when not using checkboxes
				productTag       = $( 'textarea[name="tax_input[product_tag]"]' ).length ? $( 'textarea[name="tax_input[product_tag]"]' ).val().split( ',' ) : [],
				syncEnabled      = $( '#wc_facebook_sync_mode' ).val() !== 'sync_disabled',
				varSyncEnabled   = $( '.js-variable-fb-sync-toggle' ).val() !== 'sync_disabled';

			$( '#taxonomy-product_cat input[name="tax_input[product_cat][]"]:checked' ).each( function() {
				productCat.push( parseInt( $( this ).val(), 10 ) );
			} );

			// this query will get tags when using checkboxes
			$( '#taxonomy-product_tag input[name="tax_input[product_tag][]"]:checked' ).each( function() {
				productTag.push( parseInt( $( this ).val(), 10 ) );
			} );

			if ( productID > 0 ) {

				$.post( facebook_for_woocommerce_products_admin.ajax_url, {
					action:           'facebook_for_woocommerce_set_product_sync_prompt',
					security:         facebook_for_woocommerce_products_admin.set_product_sync_prompt_nonce,
					sync_enabled:     syncEnabled    ? 'enabled' : 'disabled',
					var_sync_enabled: varSyncEnabled ? 'enabled' : 'disabled',
					product:          productID,
					categories:       productCat,
					tags:             productTag
				}, function( response ) {

					// open modal if visibility checkbox is checked or if there are conflicting terms set for sync exclusion
					if ( response && ! response.success && syncEnabled ) {

						// close existing modals
						$( '#wc-backbone-modal-dialog .modal-close' ).trigger( 'click' );

						// open new modal, populate template with AJAX response data
						new $.WCBackboneModal.View( {
							target: 'facebook-for-woocommerce-modal',
							string: response.data
						} );

					} else {

						// no modal displayed: submit form as normal
						submitProductSave = true;
						$submitButton.trigger( 'click' );
					}
				} );

			} else {

				// no modal displayed: submit form as normal
				submitProductSave = true;
				$submitButton.trigger( 'click' );
			}

		} );
	}


} );
