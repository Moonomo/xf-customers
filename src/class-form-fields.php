<?php

namespace XFrontend\Customers;

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

class FormFields {

	/**
	 * Nonce field key.
	 */
	const NONCE_NAME = 'xf_customer_form_nonce';

	/**
	 * API Endpoint URL for datetime.
	 */
	const API_DATE_ENDPOINT = 'http://worldclockapi.com/api/json/utc/now';

	/**
	 * Name for Scope.
	 */
	const FIELD_SCOPE = 'xf_customer_scope';

	/**
	 * Name for date field.
	 */
	const FIELD_DATE = 'xf_customer_api_date';

	/**
	 * @var \WP_Error
	 */
	private $errors;

	/**
	 * @var string
	 */
	private $nonce;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @var array
	 */
	private $fields;

	/**
	 * FormFields constructor.
	 *
	 * @param array $fields
	 */
	public function __construct( $fields = array() ) {

		$this->errors = new \WP_Error();
		$this->nonce  = self::NONCE_NAME . '_' . get_current_blog_id();
		$this->fields = $fields;
	}

	/**
	 * @return array
	 */
	public function get_fields() {

		$fields = array(
			'fullname' => array(
				'type'              => 'text',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
				'label'             => esc_html__( 'Name', 'xf-customers' ),
				'required'          => true,
				'required_msg'      => esc_html__( 'Name is required.', 'xf-customers' ),
			),
			'phone'    => array(
				'type'              => 'text',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
				'label'             => esc_html__( 'Phone Number', 'xf-customers' ),
				'required'          => true,
				'required_msg'      => esc_html__( 'Phone number is required.', 'xf-customers' ),
			),
			'email'    => array(
				'type'              => 'email',
				'sanitize_callback' => 'sanitize_email',
				'default'           => '',
				'label'             => esc_html__( 'Email Address.', 'xf-customers' ),
				'required'          => true,
				'required_msg'      => esc_html__( 'Email address is required.', 'xf-customers' ),
			),
			'budget'   => array(
				'type'              => 'text',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
				'label'             => esc_html__( 'Desired Budget', 'xf-customers' ),
				'required'          => true,
				'required_msg'      => esc_html__( 'Desired budget is required.', 'xf-customers' ),

			),
			'message'  => array(
				'type'              => 'text',
				'sanitize_callback' => array( $this, 'sanitize_html' ),
				'default'           => '',
				'label'             => esc_html__( 'Message', 'xf-customers' ),
				'required'          => true,
				'required_msg'      => esc_html__( 'Message is required.', 'xf-customers' ),

			),
		);

		return $this->parse_args( $this->fields, $fields );
	}

	/**
	 * @param string $field
	 *
	 * @return \stdClass
	 */
	public function get_field( $field ) {

		$fields = $this->get_fields();

		$field_data = array_key_exists( $field, $fields ) ? $fields[ $field ] : array();

		$data = array_merge(
			array(
				'name'      => $field,
				'id'        => $field,
				'label'     => '',
				'type'      => '',
				'default'   => '',
				'maxlength' => '',
				'required'  => false,
			),
			$field_data
		);

		return (object) $data;
	}

	/**
	 * @return array
	 */
	public function get_fields_data() {

		if ( is_array( $this->data ) ) {
			return $this->data;
		}

		$data   = array();
		$fields = $this->get_fields();

		$scope   = (string) filter_input( INPUT_POST, self::FIELD_SCOPE, FILTER_SANITIZE_STRING );
		$is_post = (bool) $scope;

		if ( $is_post ) {
			// Getting data for our listed fields from $_POST, we'll sanitize the data a bit later.
			// @codingStandardsIgnoreLine
			$data          = wp_array_slice_assoc( wp_unslash( $_POST ), array_keys( $fields ) );
			$data['scope'] = $scope;
		}

		$empty_data = array(
			'fullname' => '',
			'phone'    => '',
			'email'    => '',
			'budget'   => '',
			'message'  => '',
		);

		$data = array_merge( $empty_data, $data );

		// Sanitize always, validate on post
		$data = $this->sanitize_data( $fields, $data );
		if ( $is_post ) {
			$data = $this->validate_data( $fields, $data );
		}

		$this->data = $data;

		return $data;
	}

	/**
	 * @return \WP_Error object.
	 */
	public function get_request_errors() {

		// process data from request.
		$this->get_fields_data();

		return $this->errors;
	}

	/**
	 * @return string
	 */
	public function get_date_via_rest_api() {

		$response = wp_remote_get( self::API_DATE_ENDPOINT, array(
			'timeout' => 30,
		) );
		$code     = (int) wp_remote_retrieve_response_code( $response );

		if ( 200 !== $code || is_wp_error( $response ) ) {
			return 'void';
		}

		$object = json_decode( wp_remote_retrieve_body( $response ) );

		return $object->currentDateTime ?: 'void';
	}

	/**
	 *  Returns the blog timezone
	 *
	 * Gets timezone settings from the db. If a timezone identifier is used just turns
	 * it into a DateTimeZone. If an offset is usd, it tries to find a suitable timezone.
	 * If all else fails it uses UTC.
	 *
	 * @return \DateTimeZone The blog timezone
	 */
	function get_blog_timezone() {

		$tzstring = get_option( 'timezone_string' );
		$offset   = get_option( 'gmt_offset' );

		//We should descourage manual offset
		//@see http://us.php.net/manual/en/timezones.others.php
		//@see https://bugs.php.net/bug.php?id=45543
		//@see https://bugs.php.net/bug.php?id=45528
		//IANA timezone database that provides PHP's timezone support uses (i.e. reversed) POSIX style signs
		if ( empty( $tzstring ) && 0 !== $offset && floor( $offset ) === $offset ) {
			$offset_st = $offset > 0 ? "-$offset" : '+' . absint( $offset );
			$tzstring  = 'Etc/GMT' . $offset_st;
		}

		//Issue with the timezone selected, set to 'UTC'
		if ( empty( $tzstring ) ) {
			$tzstring = 'UTC';
		}

		if ( $tzstring instanceof \DateTimeZone ) {
			return $tzstring;
		}

		return new \DateTimeZone( $tzstring );
	}

	/**
	 * @return bool
	 */
	public function is_request_valid() {

		$this->get_fields_data();

		return ! $this->errors->get_error_codes();
	}

	/**
	 * @return bool
	 */
	public function is_nonce_valid() {

		$nonce = (string) filter_input( INPUT_POST, self::NONCE_NAME, FILTER_SANITIZE_STRING );

		return $nonce && wp_verify_nonce( $nonce, $this->nonce );
	}

	/**
	 * Print none field.
	 *
	 * @see wp_nonce_field()
	 */
	public function nonce_field() {

		return wp_nonce_field( $this->nonce, self::NONCE_NAME, false, false );
	}

	/**
	 * Merge multidimensional arrays.
	 *
	 * @param $settings
	 * @param $defaults
	 *
	 * @return array
	 *
	 * @see wp_parse_args()
	 */
	public function parse_args( &$settings, $defaults = '' ) {
		$settings = (array) $settings;
		$defaults = (array) $defaults;
		$return   = $defaults;

		foreach ( $settings as $key => &$value ) {
			if ( is_array( $value ) && isset( $return[ $key ] ) ) {
				$return[ $key ] = self::parse_args( $value, $return[ $key ] );
			} else {
				$return[ $key ] = $value;
			}
		}

		return $return;
	}

	/**
	 * Sanitizes an email address.
	 *
	 * @param $email
	 *
	 * @return string
	 */
	public function sanitize_email( $email ) {
		$email = trim( $email );

		return ! empty( $email ) ? sanitize_email( $email ) : '';
	}

	/**
	 * Sanitizes and converts an email address to link w/ HTML entities to block spam bots.
	 *
	 * @param $email
	 *
	 * @return string
	 */
	public function sanitize_email_output( $email ) {
		$email = snowbird_sanitize_email( $email );

		return ! empty( $email ) ? antispambot( 'mailto:' . $email ) : '';
	}

	/**
	 * Sanitize content for allowed HTML tags for post content.
	 *
	 * @param $input
	 *
	 * @return string
	 */
	public function sanitize_html( $input ) {
		return wp_kses_post( balanceTags( $input, true ) );
	}

	/**
	 * @param $fields
	 * @param $data
	 *
	 * @return array
	 */
	private function sanitize_data( array $fields, array $data ) {

		foreach ( $fields as $key => $field ) {

			$value   = $data[ $key ];
			$default = $field['default'] ?: '';

			$data[ $key ] = ( '' === $value || null === $value ) ? $default : $value;

			if ( ! empty( $field['sanitize_callback'] ) ) {
				add_filter( "xf_customers_sanitize_{$key}", $field['sanitize_callback'], 10, 2 );
			} else {
				add_filter( "xf_customers_sanitize_{$key}", 'sanitize_text_field', 10, 2 );
			}

			$type = $field['type'] ?: '';

			if ( in_array( $type, array( 'select', 'multiselect', 'radio' ), true ) ) {
				$value = apply_filters( "xf_customers_sanitize_{$key}", $value, $field );
			} else {
				$value = apply_filters( "xf_customers_sanitize_{$key}", $value );
			}

			$data[ $key ] = $value;
		}

		return $data;
	}

	/**
	 * @param array $fields
	 * @param array $data
	 *
	 * @return array
	 */
	private function validate_data( array $fields, array $data ) {

		foreach ( $fields as $key => $field ) {

			$value = $data[ $key ] ?: '';

			if ( ! $this->validate_required( $value, $field ) ) {
				$this->add_error( $key, $field['required_msg'] );
			}
		}

		// Validate date
		$date = (string) filter_input( INPUT_POST, self::FIELD_DATE, FILTER_SANITIZE_STRING );

		if ( 'void' === $date || empty( $date ) ) {
			$this->add_error( self::FIELD_DATE, esc_html__( 'Something went worng, please try again.', 'xf-customers' ) );
		} else {
			$data['date'] = $date;
		}

		return $data;
	}

	/**
	 * @param string $key
	 * @param string $error
	 */
	private function add_error( $key, $error ) {

		if ( $this->errors->get_error_message( "{$key}_{$error}" ) ) {
			return;
		}

		$this->errors->add(
			"{$key}_error",
			sprintf( '<strong>%s</strong> %s', esc_html__( 'ERROR:', 'xf-customers' ), $error )
		);
	}

	/**
	 * @param $value
	 * @param array $field
	 *
	 * @return bool
	 */
	private function validate_required( $value, array $field ) {

		$required = $field['required'] ?: false;

		if ( ! $required ) {
			return true;
		}

		if ( in_array( $value, array( '', null ), true ) ) {
			return false;
		}

		return true;
	}
}
