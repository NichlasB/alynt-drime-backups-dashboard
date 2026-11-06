<?php
/**
 * Genuine session/cookie/nonce fixtures, called only inside the owned child.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ADB_M_REQUEST_PROOF' ) ) {
	exit( 1 );
}

/**
 * Stock validator first; never establish a trusted principal from a supplied ID.
 *
 * @param string $cookie Owned RAM-only cookie.
 * @return int|false Validated ID.
 */
function adbg_authenticate( $cookie ) {
	$_COOKIE = array( LOGGED_IN_COOKIE => $cookie );
	$id      = wp_validate_auth_cookie( $cookie, 'logged_in' );
	if ( false !== $id ) {
		// phpcs:ignore WordPress.PHP.YodaConditions -- Exact identity of the already validated principal.
		adbg_check( (int) $id === (int) wp_set_current_user( $id )->ID, 'validated principal established by stock API' );
	}
	return $id;
}

/**
 * Clear only private process caches/principal, not installed hooks or state.
 *
 * @return void
 */
function adbg_clear_identity() {
	unset( $GLOBALS['current_user'], $GLOBALS['wp_roles'], $GLOBALS['wp_user_roles'] );
	adbg_check( true === wp_cache_flush(), 'owned core process cache flush' );
}

/**
 * Build genuine session/cookie/nonce DATA, then authenticate a fresh final state.
 *
 * @param string $scenario Fixed case.
 * @return array Validated actor and explicit command DATA; never exported outside child.
 */
function adbg_request( $scenario ) {
	$action                     = 'alynt_drime_backups_dashboard_update_source_policy';
	$user                       = 'incapable' === $scenario ? 3 : 1;
	$GLOBALS['adbg_auth_phase'] = 'setup';
	$expiration                 = time() + HOUR_IN_SECONDS;
	$token                      = WP_Session_Tokens::get_instance( $user )->create( $expiration );
	$cookie                     = wp_generate_auth_cookie( $user, $expiration, 'logged_in', $token );
	adbg_check( is_string( $cookie ) && strlen( $cookie ) < 512 && WP_Session_Tokens::get_instance( $user )->verify( $token ), 'genuine persisted owned session' );
	$nonce = 'not-used-on-denial';
	if ( 0 !== strpos( $scenario, 'cookie-' ) ) {
		$nonce_cookie = $cookie;
		if ( in_array( $scenario, array( 'nonce-user', 'nonce-session' ), true ) ) {
			$nonce_user   = 'nonce-user' === $scenario ? 2 : $user;
			$other        = WP_Session_Tokens::get_instance( $nonce_user )->create( $expiration );
			$nonce_cookie = wp_generate_auth_cookie( $nonce_user, $expiration, 'logged_in', $other );
		}
		adbg_clear_identity();
		adbg_check( false !== adbg_authenticate( $nonce_cookie ), 'nonce creator genuinely authenticated' );
		$nonce = wp_create_nonce( 'nonce-action' === $scenario ? 'other-owned-action' : $action );
		if ( 'nonce-expired' === $scenario ) {
			$nonce = substr( wp_hash( ( wp_nonce_tick() - 2 ) . '|' . $action . '|' . wp_get_current_user()->ID . '|' . wp_get_session_token(), 'nonce' ), -12, 10 );
		}
	}
	if ( 'cookie-missing' === $scenario ) {
		$cookie = '';
	}
	if ( 'cookie-malformed' === $scenario ) {
		$cookie = 'malformed';
	}
	if ( 'cookie-expired' === $scenario ) {
		$cookie = wp_generate_auth_cookie( $user, time() - DAY_IN_SECONDS, 'logged_in', $token );
	}
	if ( 'cookie-tampered' === $scenario ) {
		$cookie = substr( $cookie, 0, -1 ) . ( 'a' === substr( $cookie, -1 ) ? 'b' : 'a' );
	}
	if ( 'cookie-user' === $scenario ) {
		$cookie = preg_replace( '/\Aowned_one\|/', 'owned_two|', $cookie );
	}
	if ( 'cookie-destroyed' === $scenario ) {
		WP_Session_Tokens::get_instance( $user )->destroy( $token );
	}
	adbg_clear_identity();
	$GLOBALS['adbg_auth_phase'] = 'auth';
	$id                         = adbg_authenticate( $cookie );
	if ( 0 === strpos( $scenario, 'cookie-' ) ) {
		adbg_check( false === $id && ! isset( $GLOBALS['current_user'] ), 'invalid cookie establishes no trusted principal' );
	} else {
		adbg_check( $user === (int) $id, 'final request actor bound to its genuine session' );
	}
	if ( 'nonce-empty' === $scenario ) {
		$nonce = '';
	}
	if ( 'nonce-compound' === $scenario ) {
			$nonce = array( 'invalid' );
	}
		$request = array(
			'_wpnonce'               => $nonce,
			'dashboard_site_id'      => '7',
			'source_key'             => 'wpvivid',
			'source_mode'            => 'external_optional',
			'source_policy_revision' => str_repeat( 'a', 64 ),
		);
		if ( 'site-compound' === $scenario ) {
			$request['dashboard_site_id'] = array( 7 );
		}
		if ( 'site-padded' === $scenario ) {
			$request['dashboard_site_id'] = '007';
		}
		if ( 'revision-bad' === $scenario ) {
			$request['source_policy_revision'] = 'invalid';
		}
		if ( 'mode-compound' === $scenario ) {
			$request['source_mode'] = array();
		}
		return array( $id, $request );
}
