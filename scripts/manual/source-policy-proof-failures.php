<?php
/**
 * Definite native syntax failure and explicit cache-completion fault injection.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ADB_POLICY_PROOF' ) ) {
	exit( 1 );
}

/**
 * Proves errors are not all duplicate/conflict and cache faults are not success.
 *
 * @param array $base Primitive fixture map.
 * @return void
 */
function adbp_failure_cases( array $base ) {
	global $wpdb, $wp_object_cache, $groups;
	adbp_seed( $base );
	$before = adbp_observe();
	$fault  = function ( $sql ) {
		return 0 === strpos( $sql, 'UPDATE ' ) ? $sql . ' ADB_OWNED_INVALID_SYNTAX' : $sql;
	};
	add_filter( 'query', $fault, 20 );
	try {
		adbp_error( adbp_save( $before['model']->revision(), 7, 'external_optional' ), 'source_policy_storage_error' );
		adbp_check( adbp_observe()['map'] === $base, 'genuine failed SQL did not mutate fixture' );
	} finally {
		remove_filter( 'query', $fault, 20 );
	}
	$original_cache = $wp_object_cache;
	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Owned process-local fault double, always restored; not a qualified installed/external cache backend.
	$wp_object_cache = new class() extends WP_Object_Cache {
		/**
		 * Retains a primed key to model unconfirmed cache completion.
		 *
		 * @param int|string $key Cache key.
		 * @param string     $group Cache group.
		 * @param bool       $deprecated Deprecated parameter.
		 * @return bool Always false for this explicit fault.
		 */
		public function delete( $key, $group = 'default', $deprecated = false ) {
			unset( $key, $group, $deprecated ); // Signature parity; every key intentionally retained in this fault double.
			return false;
		}
	};
	try {
		wp_cache_set( 'alloptions', array( 'sentinel' => 'owned' ), 'options' );
		adbp_error( adbp_save( $before['model']->revision(), 7, 'external_optional' ), 'source_policy_write_uncertain' );
		adbp_check( adbp_observe()['map'] === $base + array( 7 => $base[9] ), 'cache fault does not fabricate rollback of committed native row' );
	} finally {
		$wp_object_cache = $original_cache; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Exact owned cache restore, no global flush.
		adbp_check( adbp_invalidate(), 'genuine private cache restored' );
	}
	$groups[] = 'native-definite-error-and-injected-cache-uncertainty';
}
