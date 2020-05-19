<?php
/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

namespace SkyVerge\WooCommerce\Facebook\Handlers;

use SkyVerge\WooCommerce\PluginFramework\v5_5_4\SV_WC_API_Exception;

defined( 'ABSPATH' ) or exit;

/**
 * The connection handler.
 *
 * @since 2.0.0-dev.1
 */
class Connection {


	/** @var string Facebook client identifier */
	const CLIENT_ID = '1234';

	/** @var string Facebook OAuth URL */
	const OAUTH_URL = 'https://facebook.com/dialog/oauth';

	/** @var string WooCommerce connection proxy URL */
	const PROXY_URL = 'https://connect.woocommerce.com/auth/facebook/';

	/** @var string the action callback for the connection */
	const ACTION_CONNECT = 'wc_facebook_connect';

	/** @var string the WordPress option name where the external business ID is stored */
	const OPTION_EXTERNAL_BUSINESS_ID = 'wc_facebook_external_business_id';

	/** @var string the business manager ID option name */
	const OPTION_BUSINESS_MANAGER_ID = 'wc_facebook_business_manager_id';

	/** @var string the access token option name */
	const OPTION_ACCESS_TOKEN = 'wc_facebook_access_token';


	/** @var string|null the generated external merchant settings ID */
	private $external_business_id;


	/**
	 * Constructs a new Connection.
	 *
	 * @since 2.0.0-dev.1
	 */
	public function __construct() {

		add_action( 'woocommerce_api_' . self::ACTION_CONNECT, [ $this, 'handle_connect' ] );
	}


	/**
	 * Processes the returned connection.
	 *
	 * @internal
	 *
	 * @since 2.0.0-dev.1
	 */
	public function handle_connect() {

		// don't handle anything unless the user can manage WooCommerce settings
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		try {

			if ( empty( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], self::ACTION_CONNECT ) ) {
				throw new SV_WC_API_Exception( 'Invalid nonce' );
			}

			$access_token = ! empty( $_GET['access_token'] ) ? sanitize_text_field( $_GET['access_token'] ) : '';

			if ( ! $access_token ) {
				throw new SV_WC_API_Exception( 'Access token is missing' );
			}

			$access_token = $this->create_system_user_token( $access_token );

			$this->update_access_token( $access_token );

			$integration = facebook_for_woocommerce()->get_integration();
			$api         = new \WC_Facebookcommerce_Graph_API( $access_token );

			$asset_ids = $api->get_asset_ids( $this->get_external_business_id() );

			if ( ! empty( $asset_ids['page_id'] ) ) {
				$integration->update_option( \WC_Facebookcommerce_Integration::SETTING_FACEBOOK_PAGE_ID, sanitize_text_field( $asset_ids['page_id'] ) );
			}

			if ( ! empty( $asset_ids['pixel_id'] ) ) {
				$integration->update_option( \WC_Facebookcommerce_Integration::SETTING_FACEBOOK_PIXEL_ID, sanitize_text_field( $asset_ids['pixel_id'] ) );
			}

			if ( ! empty( $asset_ids['catalog_id'] ) ) {
				$integration->update_product_catalog_id( sanitize_text_field( $asset_ids['catalog_id'] ) );
			}

			if ( ! empty( $asset_ids['business_manager_id'] ) ) {
				$this->update_business_manager_id( sanitize_text_field( $asset_ids['business_manager_id'] ) );
			}

			facebook_for_woocommerce()->get_message_handler()->add_message( __( 'Connection successful', 'facebook-for-woocommerce' ) );

		} catch ( SV_WC_API_Exception $exception ) {

			facebook_for_woocommerce()->log( sprintf( 'Connection failed: %s', $exception->getMessage() ) );

			facebook_for_woocommerce()->get_message_handler()->add_error( __( 'Connection unsuccessful. Please try again.', 'facebook-for-woocommerce' ) );
		}

		wp_safe_redirect( facebook_for_woocommerce()->get_settings_url() );
		exit;
	}


	/**
	 * Disconnects the integration using the Graph API.
	 *
	 * @internal
	 *
	 * @since 2.0.0-dev.1
	 */
	public function handle_disconnect() {

	}


	/**
	 * Converts a temporary user token to a system user token via the Graph API.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param string $user_token
	 * @return string
	 */
	public function create_system_user_token( $user_token ) {

		return $user_token;
	}


	/**
	 * Gets the API access token.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return string
	 */
	public function get_access_token() {

		$access_token = get_option( self::OPTION_ACCESS_TOKEN, '' );

		/**
		 * Filters the API access token.
		 *
		 * @since 2.0.0-dev.1
		 *
		 * @param string $access_token access token
		 * @param Connection $connection connection handler instance
		 */
		return apply_filters( 'wc_facebook_connection_access_token', $access_token, $this );
	}


	/**
	 * Gets the URL to start the connection flow.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return string
	 */
	public function get_connect_url() {

		return add_query_arg( rawurlencode_deep( $this->get_connect_parameters() ), self::OAUTH_URL );
	}


	/**
	 * Gets the URL for disconnecting.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return string
	 */
	public function get_disconnect_url() {

		return '';
	}


	/**
	 * Gets the scopes that will be requested during the connection flow.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return string[]
	 */
	public function get_scopes() {

		$scopes = [
			'manage_business_extension',
			'catalog_management',
			'business_management',
		];

		/**
		 * Filters the scopes that will be requested during the connection flow.
		 *
		 * @since 2.0.0-dev.1
		 *
		 * @param string[] $scopes connection scopes
		 * @param Connection $connection connection handler instance
		 */
		return (array) apply_filters( 'wc_facebook_connection_scopes', $scopes, $this );
	}


	/**
	 * Gets the stored external business ID.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return string
	 */
	public function get_external_business_id() {

		if ( ! is_string( $this->external_business_id ) ) {

			$value = get_option( self::OPTION_EXTERNAL_BUSINESS_ID );

			if ( ! is_string( $value ) ) {

				$value = sanitize_title( get_bloginfo( 'name' ) ) . '-' . uniqid();

				update_option( self::OPTION_EXTERNAL_BUSINESS_ID, $value );
			}

			$this->external_business_id = $value;
		}

		/**
		 * Filters the external business ID.
		 *
		 * @since 2.0.0-dev.1
		 *
		 * @param string $external_business_id stored external business ID
		 * @param Connection $connection connection handler instance
		 */
		return (string) apply_filters( 'wc_facebook_external_business_id', $this->external_business_id, $this );
	}


	/**
	 * Gets the site's business name.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return string
	 */
	public function get_business_name() {

		$business_name = html_entity_decode( get_bloginfo( 'name' ), ENT_QUOTES, 'UTF-8' );

		/**
		 * Filters the shop's business name.
		 *
		 * This is passed to Facebook when connecting. Defaults to the site name.
		 *
		 * @since 2.0.0-dev.1
		 *
		 * @param string $business_name the shop's business name
		 */
		return apply_filters( 'wc_facebook_connection_business_name', $business_name );
	}


	/**
	 * Gets the business manager ID value.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return string
	 */
	public function get_business_manager_id() {

		return get_option( self::OPTION_BUSINESS_MANAGER_ID, '' );
	}


	/**
	 * Gets the proxy URL.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return string URL
	 */
	public function get_proxy_url() {

		return self::PROXY_URL;
	}


	/**
	 * Gets the full redirect URL where the user will return to after OAuth.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return string
	 */
	public function get_redirect_url() {

		$redirect_url = add_query_arg( [
			'wc-api' => self::ACTION_CONNECT,
			'nonce'  => wp_create_nonce( self::ACTION_CONNECT ),
		], home_url( '/' ) );

		/**
		 * Filters the redirect URL where the user will return to after OAuth.
		 *
		 * @since 2.0.0-dev.1
		 *
		 * @param string $redirect_url redirect URL
		 * @param Connection $connection connection handler instance
		 */
		return (string) apply_filters( 'wc_facebook_connection_redirect_url', $redirect_url, $this );
	}


	/**
	 * Gets the full set of connection parameters for starting OAuth.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return array
	 */
	public function get_connect_parameters() {

		/**
		 * Filters the connection parameters.
		 *
		 * @since 2.0.0-dev.1
		 *
		 * @param array $parameters connection parameters
		 */
		return apply_filters( 'wc_facebook_connection_parameters', [
			'client_id'     => self::CLIENT_ID,
			'redirect_uri'  => $this->get_proxy_url(),
			'state'         => $this->get_redirect_url(),
			'display'       => 'page',
			'response_type' => 'code',
			'scope'         => implode( ',', $this->get_scopes() ),
			'extras'        => json_encode( $this->get_connect_parameters_extras() ),
		] );
	}


	/**
	 * Gets connection parameters extras.
	 *
	 * @see Connection::get_connect_parameters()
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return array associative array (to be converted to JSON encoded for connection purposes)
	 */
	private function get_connect_parameters_extras() {

		$parameters = [
			'setup' => [
				'external_business_id' => $this->get_external_business_id(),
				'timezone'             => wc_timezone_string(),
				'currency'             => get_woocommerce_currency(),
				'business_vertical'    => 'ECOMMERCE',
			],
			'business_config' => [
				'business' => [
					'name' => $this->get_business_name(),
				],
			],
			'repeat' => false,
		];

		if ( $external_merchant_settings_id = facebook_for_woocommerce()->get_integration()->get_external_merchant_settings_id() ) {
			$parameters['setup']['merchant_settings_id'] = $external_merchant_settings_id;
		}

		return $parameters;
	}


	/**
	 * Stores the given ID value.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param string $value the business manager ID
	 */
	public function update_business_manager_id( $value ) {

		update_option( self::OPTION_BUSINESS_MANAGER_ID, $value );
	}


	/**
	 * Stores the given token value.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param string $value the access token
	 */
	public function update_access_token( $value ) {

		update_option( self::OPTION_ACCESS_TOKEN, $value );
	}


	/**
	 * Determines whether the site is connected.
	 *
	 * A site is connected if there is an access token stored.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return bool
	 */
	public function is_connected() {

		return (bool) $this->get_access_token();
	}


}
