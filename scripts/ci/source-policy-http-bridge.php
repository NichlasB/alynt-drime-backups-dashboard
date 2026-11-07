<?php
/**
 * Definition-only private bridge preparation; never registers or bootstraps.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Exact parsed request metadata only; not a raw HTTP parser or authorization.
 *
 * @param mixed $meta Parsed bounded DATA.
 * @param mixed $challenge Private owner challenge, not a WordPress nonce.
 * @return string|false Fixed route enum, never PHP-router delegation false.
 */
function adbh_route( $meta, $challenge ) {
	$keys = array( 'host', 'method', 'target', 'challenge', 'header_bytes', 'body_bytes', 'content_length', 'transfer_encoding', 'content_type' );
	if ( ! is_array( $meta ) || count( $meta ) !== count( $keys ) || array_diff( $keys, array_keys( $meta ) )
		|| ! is_string( $challenge ) || 1 !== preg_match( '/\A[a-f0-9]{32}\z/', $challenge ) || $meta['challenge'] !== $challenge
		|| '127.0.0.1:18777' !== $meta['host'] || ! is_int( $meta['header_bytes'] ) || $meta['header_bytes'] <= 0 || $meta['header_bytes'] > 32768
		|| ! is_int( $meta['body_bytes'] ) || $meta['body_bytes'] < 0 || $meta['body_bytes'] > 16384 || null !== $meta['transfer_encoding'] ) {
		return false;
	}
	if ( 'GET' === $meta['method'] ) {
		if ( 0 !== $meta['body_bytes'] || null !== $meta['content_length'] || null !== $meta['content_type'] ) {
			return false;
		}
		$routes = array(
			'/wp-login.php'             => 'login-form',
			'/_adb-owned-source-policy' => 'view',
			'/_adb-owned-source-policy?control=wrong-action' => 'nonce-control',
		);
		return is_string( $meta['target'] ) ? ( $routes[ $meta['target'] ] ?? false ) : false;
	}
	if ( 'POST' !== $meta['method'] || 0 === $meta['body_bytes'] || (string) $meta['body_bytes'] !== $meta['content_length'] || 'application/x-www-form-urlencoded' !== $meta['content_type'] ) {
		return false;
	}
	return '/wp-login.php' === $meta['target'] ? 'login-submit' : ( '/_adb-owned-source-policy' === $meta['target'] ? 'submit' : false );
}

/**
 * Private result projection, never a handle/error-message exporter.
 *
 * @param string $code Fixed safe result.
 * @return array Fixed response DATA; not an HTTP emitter.
 */
function adbh_result( $code ) {
	$statuses = array(
		'view'                          => 200,
		'saved'                         => 200,
		'unchanged'                     => 200,
		'source_policy_conflict'        => 409,
		'source_policy_invalid'         => 400,
		'source_policy_forbidden'       => 403,
		'source_policy_unavailable'     => 503,
		'source_policy_storage_error'   => 503,
		'source_policy_write_uncertain' => 503,
		'source_policy_save_failed'     => 500,
	);
	$code     = isset( $statuses[ $code ] ) ? $code : 'source_policy_unavailable';
	return array(
		'status'   => $statuses[ $code ],
		'result'   => $code,
		'mode'     => null,
		'revision' => null,
		'nonce'    => null,
	);
}

/**
 * Forward raw parsed command to the unchanged fixed entry. No caller callback.
 * No includes, global request reads, user selection, endpoint or storage wiring.
 *
 * @param mixed $meta Parsed request DATA.
 * @param mixed $request Raw parsed fields; malformed types stay malformed.
 * @param mixed $challenge Private test ingress challenge.
 * @return array Private response DATA only; nonce/revision must never be logged.
 */
function adbh_bridge( $meta, $request, $challenge ) {
	$kind = adbh_route( $meta, $challenge );
	if ( ! in_array( $kind, array( 'view', 'submit', 'nonce-control' ), true ) ) {
		return adbh_result( 'source_policy_invalid' );
	}
	try {
		$class  = 'Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry';
		$result = 'submit' === $kind ? $class::request_submit( $request ) : $class::request_view( 7, 'wpvivid' );
		if ( is_object( $result ) && 'WP_Error' === get_class( $result ) ) {
			$code = $result->get_error_code();
			return adbh_result( is_string( $code ) && 0 === strpos( $code, 'source_policy_' ) ? $code : 'source_policy_unavailable' );
		}
		if ( 'submit' === $kind ) {
			return adbh_result( in_array( $result, array( 'saved', 'unchanged' ), true ) ? $result : 'source_policy_unavailable' );
		}
		if ( ! is_array( $result ) || array( 'mode', 'revision' ) !== array_keys( $result ) || ! in_array( $result['mode'], array( 'required', 'external_optional' ), true )
			|| ! is_string( $result['revision'] ) || 1 !== preg_match( '/\A[a-f0-9]{64}\z/', $result['revision'] ) ) {
			return adbh_result( 'source_policy_unavailable' );
		}
		$action = 'nonce-control' === $kind ? 'adb_http_wrong_action' : 'alynt_drime_backups_dashboard_update_source_policy';
		$nonce  = wp_create_nonce( $action ); // Only after the ordinary guarded view succeeds.
		if ( ! is_string( $nonce ) || '' === $nonce || strlen( $nonce ) > 128 ) {
			return adbh_result( 'source_policy_unavailable' );
		}
		return array(
			'status'   => 200,
			'result'   => 'view',
			'mode'     => $result['mode'],
			'revision' => $result['revision'],
			'nonce'    => $nonce,
		);
	} catch ( Throwable $error ) {
		return adbh_result( 'source_policy_unavailable' ); // No private diagnostic/partial view escapes.
	}
}
