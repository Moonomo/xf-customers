<?php

namespace XFrontend\Customers;

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

class Shortcodes {

	/**
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * @var array
	 */
	private $registered_shortcodes = array();

	/**
	 * @var \stdClass
	 */
	private $data;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_version The plugin version.
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;
		$this->data   = new \stdClass();
	}

	/**
	 * Settings for All XF Customers shortcodes.
	 *
	 * @return array
	 */
	public function settings() {

		return (array) apply_filters( 'xf_customers_shortcodes', array(
			'xf_customer_form' => array(
				'name'     => esc_html__( 'Customer Lead Gen Form', 'xf-customers' ),
				'callback' => array( $this, 'the_form' ),
			),
		) );
	}

	/**
	 * Returns settings for a XF Customers Shortcode.
	 *
	 * @param $shortcode
	 *
	 * @return bool|array
	 */
	public function get_setting( $shortcode ) {

		$settings = $this->settings();

		return isset( $settings[ $shortcode ] ) ? $settings[ $shortcode ] : false;
	}

	/**
	 * Helper function to cleanup and do_shortcode on content.
	 *
	 * @see do_shortcode()
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function callback_content( $content ) {
		$array = array(
			'<p>['       => '[',
			']</p>'      => ']',
			'<br /></p>' => '</p>',
			']<br />'    => ']',
		);

		$content = shortcode_unautop( balanceTags( trim( $content ), true ) );
		$content = strtr( $content, $array );

		return do_shortcode( $content );
	}

	/**
	 * Register XF Customers shortcodes.
	 */
	public function setup() {

		foreach ( $this->settings() as $shortcode => $setting ) {

			if ( ! $setting || ! $setting['callback'] ) {
				return;
			}

			$this->registered_shortcodes[ $shortcode ] = $shortcode;

			remove_shortcode( $shortcode );
			add_shortcode( $shortcode, $setting['callback'] );
		}
	}

	/**
	 * Prints the customers lead gen form.
	 *
	 * @param $attr
	 * @param string $content
	 * @param string $shortcode
	 *
	 * @return string
	 */
	public function the_form( $attr, $content = '', $shortcode ) {

		$setting = $this->get_setting( $shortcode );

		if ( ! $setting || empty( $this->registered_shortcodes[ $shortcode ] ) ) {
			return '';
		}

		// Parse attributes from the shortcode.
		$attr = (object) shortcode_atts( array(
			'id'                 => 'xf-customer-form',
			'class'              => '',
			'name-label'         => esc_html__( 'Name', 'xf-customers' ),
			'name-max-length'    => 64,
			'phone-label'        => esc_html__( 'Phone Number', 'xf-customers' ),
			'phone-max-length'   => 32,
			'email-label'        => esc_html__( 'Email Address', 'xf-customers' ),
			'email-max-length'   => 128,
			'budget-label'       => esc_html__( 'Desired Budget', 'xf-customers' ),
			'budget-max-length'  => 64,
			'message-cols'       => 64,
			'message-label'      => esc_html__( 'Message', 'xf-customers' ),
			'message-max-length' => 1500,
			'message-rows'       => 8,

		), $attr, $shortcode );

		$fields = new FormFields( array(
			'fullname' => array(
				'maxlength' => (int) $attr->{'name-max-length'},
				'label'     => $attr->{'name-label'},
			),
			'phone'    => array(
				'maxlength' => (int) $attr->{'phone-max-length'},
				'label'     => $attr->{'phone-label'},
			),
			'email'    => array(
				'maxlength' => (int) $attr->{'email-max-length'},
				'label'     => $attr->{'email-label'},
			),
			'budget'   => array(
				'maxlength' => (int) $attr->{'budget-max-length'},
				'label'     => $attr->{'budget-label'},
			),
			'message'  => array(
				'cols'      => (int) $attr->{'message-cols'},
				'rows'      => (int) $attr->{'message-rows'},
				'maxlength' => (int) $attr->{'message-max-length'},
				'label'     => $attr->{'message-label'},
			),
		) );

		// Prepare Data for the form.
		$data         = new \stdClass();
		$data->id     = $attr->id;
		$data->class  = array_merge( array( 'xf-customer-form' ), preg_split( '#\s+#', $attr->class ) );
		$data->class  = implode( ' ', array_map( 'sanitize_html_class', array_unique( $data->class ) ) );
		$data->fields = $fields;

		// Enqueue JS for the shortcode.
		wp_enqueue_script(
			'xf-customers',
			$this->plugin->plugin_url . '/assets/js/functions.js',
			array( 'jquery' ),
			$this->plugin->get_plugin_version(),
			true
		);
		wp_localize_script( 'xf-customers', 'xfCustomersi18n', array(
			'ajax_url'        => admin_url( 'admin-ajax.php?action=' . Service::AJAX_HANDLER ),
			'nonce_field_key' => $fields::NONCE_NAME,
			'scope_field_key' => $fields::FIELD_SCOPE,
			'date_field_key'  => $fields::FIELD_DATE,
		) );

		// Enqueue CSS for the shortcode.
		wp_enqueue_style(
			'xf-customers',
			$this->plugin->plugin_url . '/assets/css/style.css',
			array(),
			$this->plugin->get_plugin_version()
		);

		// Render the form template and return.
		return $this->plugin->render( $data, 'the_form' );
	}
}
