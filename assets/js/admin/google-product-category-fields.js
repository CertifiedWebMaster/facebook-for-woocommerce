/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

jQuery( document ).ready( ( $ ) => {

	'use strict';

	/**
	 * Google product category field handler.
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @type {WC_Facebook_Google_Product_Category_Fields} object
	 */
	window.WC_Facebook_Google_Product_Category_Fields = class WC_Facebook_Google_Product_Category_Fields {


		/**
		 * Handler constructor.
		 *
		 * @since 2.1.0-dev.1
		 *
		 * @param {Object[]} categories The full categories list, indexed by the category ID
		 * @param {string} categories[].label The category label
		 * @param {string[]} categories[].options The category's child categories' IDs
		 * @param {string} categories[].parent The category's parent category ID
		 * @param {string} input_id The element that should receive the latest concrete category ID
		 */
		constructor(categories, input_id) {

			this.categories = categories;

			this.input_id = input_id;

			$( '<div id="wc-facebook-google-product-category-fields"></div>' )
				.insertBefore( $( '#' + this.input_id ) )
				.on( 'change', 'select.wc-facebook-google-product-category-select', ( event ) => {
					this.onChange( $( event.target ) );
				} );

			var options = this.getOptions();

			this.addSelect( options );
			this.addSelect( {} );
		}


		/**
		 * Updates the subsequent selects whenever one of the selects changes.
		 *
		 * @since 2.1.0-dev.1
		 */
		onChange(element) {

			// remove following select fields if their options depended on the value of the current select field
			if ( element.hasClass( 'locked' ) ) {
				element.closest( '.wc-facebook-google-product-category-field' ).nextAll().remove();
			}

			var categoryId = element.val();

			$( '#' + this.input_id ).val( categoryId );

			var options = this.getOptions( categoryId );

			if ( Object.keys( options ).length ) {
				this.addSelect( options );
			}
		}


		/**
		 * Adds a new select with the given options.
		 *
		 * @since 2.1.0-dev.1
		 *
		 * @param {Object.<string, string>} options an object with option IDs as keys and option labels as values
		 */
		addSelect( options ) {

			var $container = $( '#wc-facebook-google-product-category-fields' );
			var $otherSelects = $container.find( '.wc-facebook-google-product-category-select' );
			var $select = $( '<select class="wc-enhanced-select wc-facebook-google-product-category-select"></select>' );

			$otherSelects.addClass( 'locked' );

			$container.append( $( '<div class="wc-facebook-google-product-category-field" style="margin-bottom: 16px">' ).append( $select ) );

			$select.attr( 'data-placeholder', this.getSelectPlaceholder( $otherSelects, options ) ).append( $( '<option value=""></option>' ) );

			Object.keys( options ).forEach( ( key ) => {
				$select.append( $( '<option value="' + key + '">' + options[ key ] + '</option>' ) );
			} );

			$select.select2();
		}


		/**
		 * Gets the placeholder string for a select field based on the number of existing select fields.
		 *
		 * @since 2.1.0-dev.1
		 *
		 * @param {jQuery} $otherSelects a jQuery object matching existing select fields
		 * @param {Object.<string, string>} options an object with option IDs as keys and option labels as values
		 * @return {string}
		 */
		getSelectPlaceholder( $otherSelects, options ) {

			if ( 0 === $otherSelects.length ) {
				return facebook_for_woocommerce_google_product_category.i18n.top_level_dropdown_placeholder;
			}

			if ( 1 === $otherSelects.length && 0 === Object.keys( options ).length ) {
				return facebook_for_woocommerce_google_product_category.i18n.second_level_empty_dropdown_placeholder;
			}

			return facebook_for_woocommerce_google_product_category.i18n.general_dropdown_placeholder;
		}


		/**
		 * Gets an array of options for the given category ID.
		 *
		 * @since 2.1.0-dev.1
		 *
		 * @param {string} category_id The given category ID
		 * @return {Object.<string, string>} an object with option IDs as keys and option labels as values
		 */
		getOptions(category_id) {

			if ( 'undefined' === typeof category_id ) {
				return this.getTopLevelOptions();
			}

			if ( 'undefined' === typeof this.categories[ category_id ] ) {
				return [];
			}

			if ( 'undefined' === typeof this.categories[ category_id ]['options'] ) {
				return [];
			}

			return this.categories[ category_id ]['options'];
		}


		/**
		 * Gets an array of top level category options.
		 *
		 * @since 2.1.0-dev.1
		 *
		 * @return {Object.<string, string>} an object with option IDs as keys and option labels as values
		 */
		getTopLevelOptions() {

			let options = {};

			Object.keys( this.categories ).forEach( ( key ) => {

				if ( this.categories[ key ].parent ) {
					return;
				}

				options[ key ] = this.categories[ key ].label;
			} );

			return options;
		}


	}


} );
