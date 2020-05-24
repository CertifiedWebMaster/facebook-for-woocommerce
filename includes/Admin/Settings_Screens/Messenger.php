<?php
/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

namespace SkyVerge\WooCommerce\Facebook\Admin\Settings_Screens;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\Facebook\Admin;
use SkyVerge\WooCommerce\PluginFramework\v5_5_4\SV_WC_Helper;

/**
 * The Messenger settings screen object.
 */
class Messenger extends Admin\Abstract_Settings_Screen {


	/** @var string screen ID */
	const ID = 'messenger';


	/**
	 * Connection constructor.
	 */
	public function __construct() {

		$this->id    = self::ID;
		$this->label = __( 'Messenger', 'facebook-for-woocommerce' );
		$this->title = __( 'Messenger', 'facebook-for-woocommerce' );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_action( 'woocommerce_admin_field_messenger_greeting', [ $this, 'render_greeting_field'] );
		add_action( 'woocommerce_admin_settings_sanitize_option_wc_facebook_messenger_greeting', [ $this, 'sanitize_messenger_greeting' ], 10, 3 );
	}


	/**
	 * Enqueues the scripts.
	 *
	 * @since 2.0.0-dev.1
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'wc-facebook-admin-settings-messenger', facebook_for_woocommerce()->get_plugin_url() . '/assets/js/admin/facebook-for-woocommerce-settings-messenger.min.js', [ 'jquery', 'iris' ], \WC_Facebookcommerce::VERSION );
	}


	/**
	 * Renders the custom greeting field.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param array $field field data
	 */
	public function render_greeting_field( $field ) {

		$chars         = max( 0, strlen( $field['value'] ) );
		$max_chars     = self::get_greeting_max_characters();
		$field_id      = $field['id'];
		$counter_class = $field_id . '-characters-count';

		// Custom attribute handling.
		$custom_attributes = [];

		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		// Description handling.
		$field_description = \WC_Admin_Settings::get_field_description( $field );
		$description       = $field_description['description'];
		$tooltip_html      = $field_description['tooltip_html'];

		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
			</th>
			<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>">
				<?php echo $description; // WPCS: XSS ok. ?>

				<textarea
					name="<?php echo esc_attr( $field['id'] ); ?>"
					id="<?php echo esc_attr( $field['id'] ); ?>"
					rows="3"
					columns="20"
					style="<?php echo esc_attr( $field['css'] ); ?>"
					class="<?php echo esc_attr( $field['class'] ); ?>"
					placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
					maxlength="<?php echo esc_attr( self::get_greeting_max_characters() ); ?>"
				><?php echo esc_textarea( $field['value'] ); // WPCS: XSS ok. ?></textarea>

				<span
					style="display: none; font-family: monospace; font-size: 0.9em;"
					class="<?php echo sanitize_html_class( $counter_class ); ?> characters-counter"
				>
					<?php echo esc_html( $chars . ' / ' . $max_chars ); ?>
					<span style="display:none;"><?php echo esc_html( $this->get_messenger_greeting_long_warning_text() ); ?></span>
				</span>

			</td>
		</tr>
		<?php
	}


	/**
	 * Sanitizes the message greeting field value on save.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @param string $value pre-sanitized value
	 * @return string
	 */
	public function sanitize_messenger_greeting( $value ) {

		$value = is_string( $value ) ? trim( sanitize_text_field( wp_unslash( $value ) ) ) : '';

		return SV_WC_Helper::str_truncate( $value, self::get_greeting_max_characters(), '' );
	}


	/**
	 * Gets the screen settings.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return array
	 */
	public function get_settings() {

		$messenger_locales = [];

		// tries matching with WordPress locale, otherwise English, otherwise first available language
		if ( isset( $messenger_locales[ get_locale() ] ) ) {
			$default_locale = get_locale();
		} elseif ( isset( $messenger_locales[ 'en_US' ] ) ) {
			$default_locale = 'en_US';
		} elseif ( ! empty( $messenger_locales ) && is_array( $messenger_locales ) ) {
			$default_locale = key( $messenger_locales );
		} else {
			// fallback to English in case of invalid/empty filtered list of languages
			$messenger_locales = [ 'en_US' => _x( 'English (United States)', 'language', 'facebook-for-woocommerce' ) ];
			$default_locale    = 'en_US';
		}

		$default_messenger_greeting = __( "Hi! We're here to answer any questions you may have.", 'facebook-for-woocommerce' );
		$default_messenger_color    = '#0084ff';

		return [

			[
				'title' => __( 'Messenger', 'facebook-for-woocommerce' ),
				'type'  => 'title',
			],

			[
				'id'       => \WC_Facebookcommerce_Integration::SETTING_ENABLE_MESSENGER,
				'title'    => __( 'Enable Messenger', 'facebook-for-woocommerce' ),
				'type'     => 'checkbox',
				'label'    => ' ',
				'desc_tip' => __( 'Enable and customize Facebook Messenger on your store.', 'facebook-for-woocommerce' ),
				'default'  => 'no',
			],

			 [
			 	'id'      => \WC_Facebookcommerce_Integration::SETTING_MESSENGER_LOCALE,
				'title'   => __( 'Language', 'facebook-for-woocommerce' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select messenger-field',
				'default' => $default_locale,
				'options' => $messenger_locales,
			],

			[
				'id'                => \WC_Facebookcommerce_Integration::SETTING_MESSENGER_GREETING,
				'title'             => __( 'Greeting', 'facebook-for-woocommerce' ),
				'type'              => 'messenger_greeting',
				'class'             => 'messenger-field',
				'default'           => $default_messenger_greeting,
				'css'               => 'width: 100%; max-width: 400px; margin-bottom: 10px',
			],

			[
				'id'                => \WC_Facebookcommerce_Integration::SETTING_MESSENGER_COLOR_HEX,
				'title'             => __( 'Colors', 'facebook-for-woocommerce' ),
				'type'              => 'color',
				'class'             => 'messenger-field ', // the extra space is necessary
				'default'           => $default_messenger_color,
				'css'               => 'width: 6em;',
			],

			[ 'type' => 'sectionend' ],

		];
	}


	/**
	 * Gets the maximum number of characters allowed in the messenger greeting.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public static function get_greeting_max_characters() {

		$default = 80;

		/**
		 * Filters the maximum number of characters allowed in the messenger greeting.
		 *
		 * @since 2.0.0-dev.1
		 *
		 * @param int $max the maximum number of characters allowed in the messenger greeting
		 * @param \WC_Facebookcommerce_Integration $integration Deprecated: the integration instance
		 */
		$max = (int) apply_filters( 'wc_facebook_messenger_greeting_max_characters', $default, facebook_for_woocommerce()->get_integration() );

		return $max < 1 ? $default : $max;
	}


	/**
	 * Gets a warning text to be displayed when the Messenger greeting text exceeds the maximum length.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return string
	 */
	private function get_messenger_greeting_long_warning_text() {

		return sprintf(
			/* translators: Placeholder: %d - maximum number of allowed characters */
			__( 'The Messenger greeting must be %d characters or less.', 'facebook-for-woocommerce' ),
			self::get_greeting_max_characters()
		);
	}


	/**
	 * Gets the "disconnected" message.
	 *
	 * @since 2.0.0-dev.1
	 *
	 * @return string
	 */
	public function get_disconnected_message() {

		return sprintf(
			__( 'Please %1$sconnect to Facebook%2$s to enable and manage Facebook Messenger', 'facebook-for-woocommerce' ),
			'<a href="' . esc_url( facebook_for_woocommerce()->get_connection_handler()->get_connect_url() ) . '">', '</a>'
		);
	}


}
