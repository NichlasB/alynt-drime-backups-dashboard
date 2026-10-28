<?php
/**
 * Minimal WordPress error class shim for pure unit tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! class_exists( 'WP_Error' ) ) {
	/**
	 * Minimal WP_Error shim.
	 */
	class WP_Error {
		/**
		 * Core-compatible public error-code/message map.
		 *
		 * @var array
		 */
		public $errors = array();

		/**
		 * Core-compatible public error data map.
		 *
		 * @var array
		 */
		public $error_data = array();

		/**
		 * Constructor.
		 *
		 * @param string $code Code.
		 * @param string $message Message.
		 * @param mixed  $data Data.
		 */
		public function __construct( $code = '', $message = '', $data = '' ) {
			if ( ! empty( $code ) ) {
				$this->errors[ $code ] = array( $message );
				if ( ! empty( $data ) ) { $this->error_data[ $code ] = $data; }
				if ( defined( 'ADB_POLICY_ISOLATED' ) && function_exists( 'do_action' ) ) {
					do_action( 'wp_error_added', $code, $message, $data, $this );
				}
			}
		}

		/**
		 * Gets the error code.
		 *
		 * @return string
		 */
		public function get_error_code() {
			return array_keys( $this->errors )[0] ?? '';
		}

		/**
		 * Gets the error message.
		 *
		 * @return string
		 */
		public function get_error_message() {
			return $this->errors[ $this->get_error_code() ][0] ?? '';
		}

		/**
		 * Gets the error data.
		 *
		 * @return mixed
		 */
		public function get_error_data() {
			return $this->error_data[ $this->get_error_code() ] ?? null;
		}
	}
}
