<?php

namespace XFrontend\Customers;

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

class Service {

	const AJAX_HANDLER = 'xf_customer_form';

	/**
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Service constructor.
	 *
	 * @param $plugin
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;
	}

	/**
	 * Setup the actions/filters.
	 */
	public function setup() {
		add_action( 'wp', array( $this, 'listen' ), 11 );

		$action = self::AJAX_HANDLER;

		add_action( "wp_ajax_nopriv_$action", array( $this, 'handle_ajax' ) );
		add_action( "wp_ajax_$action", array( $this, 'handle_ajax' ) );
	}

	/**
	 * Listen, if we want to process customer lead gen form.
	 */
	public function listen() {

		add_action( Notice::ACTION_PRINT, array( Notice::instance(), 'print_messages' ) );

		$fields = new FormFields();

		$scope   = (string) filter_input( INPUT_POST, $fields::FIELD_SCOPE, FILTER_SANITIZE_STRING );
		$is_post = (bool) $scope;

		if ( ! $is_post ) {
			return;
		}

		if ( ! $fields->is_request_valid() ) {
			Notice::instance()->add_errors( $fields->get_request_errors() );

			return;
		}

		if ( ! $fields->is_nonce_valid() ) {
			Notice::instance()->send_not_allowed();
		}

		// We got valid data, now save it.
		$post_id = $this->save_customer_data();

		if ( is_wp_error( $post_id ) ) {

			Notice::instance()->add_message(
				esc_html__( 'Could not create customer post-type.', 'xf-customers' ),
				'error'
			);

			return;
		}

		Notice::instance()->add_message(
			esc_html__( 'We received your submission. Thank you!', 'xf-customers' ),
			'success'
		);
	}

	/**
	 * Process AJAX Request.
	 */
	public function handle_ajax() {

		// Set response data type.
		header( 'Content-Type: application/json' );

		$fields = new FormFields();

		if ( ! $fields->is_nonce_valid() ) {
			die( wp_json_encode( array(
				'type' => 'error',
				'data' => sprintf(
					'<div class="alert alert-danger"><uL><li>%s</li></uL></div>',
					esc_html__( 'Sorry, you are not allowed to access.', 'xf-customers' )
				),
			) ) );
		}

		if ( ! $fields->is_request_valid() ) {
			Notice::instance()->add_errors( $fields->get_request_errors() );

			ob_start();
			Notice::instance()->print_messages();
			$message = ob_get_contents();
			ob_end_clean();

			die( wp_json_encode( array(
				'type' => 'error',
				'data' => $message,
			) ) );
		}

		// We got valid data, now save it.
		$post_id = $this->save_customer_data();

		if ( is_wp_error( $post_id ) ) {
			die( wp_json_encode( array(
				'type' => 'error',
				'data' => sprintf(
					'<div class="alert alert-danger"><uL><li>%s</li></uL></div>',
					esc_html__( 'Could not create customer post-type.', 'xf-customers' )
				),
			) ) );
		}

		die( wp_json_encode( array(
			'type' => 'success',
			'data' => sprintf(
				'<div class="alert alert-success"><uL><li>%s</li></uL></div>',
				esc_html__( 'We received your submission. Thank you!', 'xf-customers' )
			),
		) ) );
	}

	/**
	 * @return int|\WP_Error
	 */
	private function save_customer_data() {

		/**
		 * @var \DateTime $date_object
		 */
		$fields      = new FormFields();
		$date        = (string) filter_input( INPUT_POST, $fields::FIELD_DATE, FILTER_SANITIZE_STRING );
		$date_object = new \DateTime( $date, $fields->get_blog_timezone() );
		$data        = $fields->get_fields_data();

		$post_id = wp_insert_post( array(
			'post_date'      => $date_object->format( 'c' ),
			'post_content'   => $data['message'],
			'post_title'     => $data['fullname'],
			'post_name'      => sanitize_title_with_dashes( $data['fullname'] . '_' . $date_object->format( 'c' ) ),
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_type'      => $this->plugin->post_type,
		), true );

		if ( ! is_wp_error( $post_id ) ) {
			unset( $data['scope'] );

			$plugin = $this->plugin;

			update_post_meta( $post_id, $plugin::CUSTOMER_SETTINGS_KEY, $data );
		}

		return $post_id;
	}

}
