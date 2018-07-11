<?php

namespace XFrontend\Customers;

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

class Notice {

	const ACTION_PRINT = 'xf-customer-form-print-messages';

	/**
	 * @var Notice
	 */
	private static $instance;

	/**
	 * @var array
	 */
	private $messages = array();

	/**
	 * @var \WP_Error[]
	 */
	private $errors = array();

	/**
	 * @return Notice
	 */
	public static function instance() {

		if ( ! self::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * @param string $message
	 * @param string $type
	 *
	 * @return Notice
	 */
	public function add_message( $message, $type = 'success' ) {

		if ( ! array_key_exists( $type, $this->messages ) ) {
			$this->messages[ $type ] = array();
		}

		$this->messages[ $type ][] = wp_strip_all_tags( $message );

		return $this;
	}

	/**
	 * @param \WP_Error $error
	 *
	 * @return Notice
	 */
	public function add_errors( \WP_Error $error ) {

		$this->errors[] = $error;

		return $this;
	}

	/**
	 * @param string $message
	 *
	 * @return void
	 */
	public function send_not_allowed( $message = '' ) {

		if ( ! $message ) {
			$message = __( 'Sorry, you are not allowed to access.', 'xf-customers' );
		}

		wp_die(
			'<h1>' . esc_html__( 'Cheatin&#8217; uh?', 'xf-customers' ) . '</h1>' .
			'<p>' . esc_html( $message ) . '</p>',
			403
		);
	}

	/**
	 * @return void
	 */
	public function print_messages() {

		foreach ( $this->errors as $error ) {
			if ( $error->get_error_code() ) {
				$this->print_errors( $error );
			}
		}

		$this->print_all_messages( $this->messages );
	}

	/**
	 * @param array $messages
	 */
	private function print_all_messages( array $messages ) {

		if ( ! $messages ) {
			return;
		}

		foreach ( $messages as $type => $message_strings ) {
			$message_strings = array_map( 'esc_html', $message_strings );
			$_messages       = '<li>' . implode( '</li><li>', $message_strings ) . '</li>';

			// @codingStandardsIgnoreLine Everything is escaped.
			printf( '<div class="alert alert-%s role="alert"><ul>%s</ul></div>', sanitize_html_class( $type ), $_messages );
		}
	}

	/**
	 * @param \WP_Error $errors
	 */
	private function print_errors( \WP_Error $errors ) {

		if ( ! $errors->get_error_code() ) {
			return;
		}

		echo '<div class="alert alert-danger" role="alert"><ul>';

		foreach ( $errors->get_error_messages() as $message ) {
			printf( '<li>%s</li>', esc_html( wp_strip_all_tags( $message ) ) );
		}

		echo '</ul></div>';
	}
}
