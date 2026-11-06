<?php
/**
 * Setup/auth-only finite SQL classification. Never installed in native dispatch.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Remove quoted values into bounded local DATA, never retain SQL in the ledger.
 *
 * @param mixed $sql Core query.
 * @return array|null Skeleton and local values, not an executor.
 */
function adbg_sql_tokens( $sql ) {
	if ( ! is_string( $sql ) || strlen( $sql ) > 16384 || false !== strpos( $sql, "\0" ) ) {
		return null;
	}
	$shape  = '';
	$values = array();
	$length = strlen( $sql );
	for ( $i = 0; $i < $length; ++$i ) {
		if ( "'" !== $sql[ $i ] ) {
			$shape .= $sql[ $i ];
			continue;
		}
		$value  = '';
		$closed = false;
		while ( ++$i < $length ) {
			if ( "'" === $sql[ $i ] ) {
				$closed = true;
				break;
			}
			if ( '\\' === $sql[ $i ] ) {
				if ( ++$i >= $length ) {
					return null;
				}
			}
			$value .= $sql[ $i ];
			if ( strlen( $value ) > 4096 ) {
				return null;
			}
		}
		if ( ! $closed || count( $values ) >= 4 ) {
			return null;
		}
		$values[] = $value;
		$shape   .= '?';
	}
	return array( preg_replace( '/\s+/', ' ', trim( str_replace( '`', '', $shape ) ) ), $values );
}

/**
 * Exact selected core forms only; no unknown option/DDL/cross-table acceptance.
 *
 * @param mixed  $sql Source-bound core SQL, returned unchanged by its observer.
 * @param string $prefix Owned prefix.
 * @return string|null Fixed classification.
 */
function adbg_sql_class( $sql, $prefix ) {
	if ( ! is_string( $prefix ) || 1 !== preg_match( '/\Aadbm_[a-f0-9]{12}_\z/', $prefix ) ) {
		return null;
	}
	$tokens = adbg_sql_tokens( $sql );
	if ( null === $tokens ) {
		return null;
	}
	list( $shape, $values ) = $tokens;
	$p                      = preg_quote( $prefix, '/' );
	if ( "SELECT option_name, option_value FROM {$prefix}options WHERE autoload = ?" === $shape && array( 'yes' ) === $values ) {
		return 'role-read';
	}
	if ( "SELECT option_value FROM {$prefix}options WHERE option_name = ? LIMIT 1" === $shape && array( $prefix . 'user_roles' ) === $values ) {
		return 'role-read';
	}
	if ( preg_match( '/\ASELECT \* FROM ' . $p . 'users WHERE (ID|user_login) = (\?|[1-3]) LIMIT 1\z/', $shape, $match ) ) {
		$allowed = 'ID' === $match[1] ? array( '1', '2', '3' ) : array( 'owned_one', 'owned_two', 'owned_low' );
		if ( ( 'ID' === $match[1] && ! $values ) || ( 1 === count( $values ) && in_array( $values[0], $allowed, true ) ) ) {
			return 'user-read';
		}
	}
	if ( preg_match( '/\ASELECT user_id, meta_key, meta_value FROM ' . $p . 'usermeta WHERE user_id IN \([1-3](?:,[1-3]){0,2}\) ORDER BY umeta_id ASC\z/', $shape ) && ! $values ) {
		return 'metadata-read';
	}
	if ( preg_match( '/\ASELECT umeta_id FROM ' . $p . 'usermeta WHERE meta_key = \? AND user_id = ([1-3]|\?)\z/', $shape, $match ) && 'session_tokens' === ( $values[0] ?? null ) && ( ( '?' !== $match[1] && 1 === count( $values ) ) || ( '?' === $match[1] && 2 === count( $values ) && in_array( $values[1], array( '1', '2', '3' ), true ) ) ) ) {
		return 'metadata-read';
	}
	if ( preg_match( '/\AINSERT INTO ' . $p . 'usermeta \(user_id, meta_key, meta_value\) VALUES \(([1-3]|\?), \?, \?\)\z/', $shape, $match ) ) {
		$offset = '?' === $match[1] ? 1 : 0;
		if ( count( $values ) === 2 + $offset && ( ! $offset || in_array( $values[0], array( '1', '2', '3' ), true ) ) && 'session_tokens' === $values[ $offset ] ) {
			return 'session-write';
		}
	}
	if ( preg_match( '/\AUPDATE ' . $p . 'usermeta SET meta_value = \? WHERE user_id = ([1-3]|\?) AND meta_key = \?\z/', $shape, $match ) ) {
		if ( 'session_tokens' === end( $values ) && ( ( '?' !== $match[1] && 2 === count( $values ) ) || ( '?' === $match[1] && 3 === count( $values ) && in_array( $values[1], array( '1', '2', '3' ), true ) ) ) ) {
			return 'session-write';
		}
	}
	if ( preg_match( '/\ADELETE FROM ' . $p . 'usermeta WHERE umeta_id IN\( [1-9][0-9]{0,5}(?:,[1-9][0-9]{0,5}){0,11} \)\z/', $shape ) && ! $values ) {
		return 'session-write';
	}
	return null;
}

/**
 * Owned source-bound observer: no retained raw statement or payload.
 *
 * @param string $sql Query from genuine core.
 * @return string Exact same bytes.
 */
function adbg_observe_query( $sql ) {
	$class = adbg_sql_class( $sql, $GLOBALS['wpdb']->prefix );
	adbg_check( null !== $class, 'unexpected auth SQL' );
	$phase = $GLOBALS['adbg_auth_phase'];
	adbg_check( in_array( $phase, array( 'setup', 'auth' ), true ), 'observer phase' );
	$GLOBALS['adbg_ledger'][ $phase ] = ( $GLOBALS['adbg_ledger'][ $phase ] ?? 0 ) + 1;
	adbg_check( $GLOBALS['adbg_ledger'][ $phase ] <= 256, 'finite auth calls within unchanged outer deadline' );
	adbg_check( 'auth' !== $phase || 'session-write' !== $class, 'request authentication has no metadata writes' );
	return $sql;
}

/**
 * Empty selected registries, with only fixed ambient/owned proof query entries.
 *
 * @return void
 */
function adbg_auth_hooks() {
	foreach ( $GLOBALS['wp_filter'] ?? array() as $name => $registry ) {
		adbg_check( $registry instanceof WP_Hook && 'WP_Hook' === get_class( $registry ), 'genuine hook registry' );
		if ( 'query' !== $name ) {
			adbg_check( array() === $registry->callbacks, 'no auth or policy callback overrides' );
			continue;
		}
		$count = 0;
		foreach ( $registry->callbacks as $priority => $entries ) {
			adbg_check( is_array( $entries ) && 1 === count( $entries ), 'one exact query entry per priority' );
			$entry = reset( $entries );
			adbg_check( is_array( $entry ) && array( 'function', 'accepted_args' ) === array_keys( $entry ) && 1 === $entry['accepted_args'], 'fixed query callback shape' );
			adbg_check( ( 0 === $priority && array( $GLOBALS['wpdb'], 'remove_placeholder_escape' ) === $entry['function'] ) || ( PHP_INT_MIN === $priority && 'adbg_observe_query' === $entry['function'] ), 'only ambient escape and setup/auth observer callbacks' );
			++$count;
		}
		adbg_check( $count <= 2, 'finite auth query observers, not a native-policy seal extension' );
	}
}
