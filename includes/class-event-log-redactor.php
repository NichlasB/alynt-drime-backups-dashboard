<?php
/**
 * Structured diagnostics event redaction.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redacts sensitive diagnostics context before persistence or export.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Event_Log_Redactor {
	/**
	 * Sensitive context keys that must be removed or masked.
	 *
	 * @var array<int,string>
	 */
	private $sensitive_key_patterns = array(
		'password',
		'passwd',
		'secret',
		'token',
		'api_key',
		'apikey',
		'authorization',
		'cookie',
		'nonce',
		'salt',
		'ciphertext',
		'payload',
		'body',
		'raw',
		'path',
		'sql',
		'drime',
	);

	/**
	 * Redacts sensitive context before storage/export.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string,mixed> $context Context.
	 * @return array<string,mixed>
	 */
	public function redact_context( array $context ) {
		$redacted = array();

		foreach ( $context as $key => $value ) {
			$key      = sanitize_key( (string) $key );
			$redacts  = $this->key_is_sensitive( $key );
			$safe_key = '' === $key ? 'context' : $key;

			$redacted[ $safe_key ] = $redacts ? '[redacted]' : $this->redact_value( $value );
		}

		return $redacted;
	}

	/**
	 * Truncates scalar text.
	 *
	 * @since 0.1.0
	 *
	 * @param string $value Value.
	 * @param int    $length Length.
	 * @return string
	 */
	public function truncate( $value, $length ) {
		$value = (string) $value;

		if ( strlen( $value ) <= $length ) {
			return $value;
		}

		return substr( $value, 0, max( 0, $length - 3 ) ) . '...';
	}

	/**
	 * Redacts or normalizes a value.
	 *
	 * @param mixed $value Value.
	 * @return mixed
	 */
	private function redact_value( $value ) {
		if ( is_array( $value ) ) {
			return $this->redact_context( $value );
		}

		if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) || null === $value ) {
			return $value;
		}

		return $this->truncate( sanitize_text_field( (string) $value ), 240 );
	}

	/**
	 * Determines whether a context key is sensitive.
	 *
	 * @param string $key Key.
	 * @return bool
	 */
	private function key_is_sensitive( $key ) {
		foreach ( $this->sensitive_key_patterns as $pattern ) {
			if ( false !== strpos( $key, $pattern ) ) {
				return true;
			}
		}

		return false;
	}
}
