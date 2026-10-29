<?php
/**
 * Bounded complete Git-tree calculation on supplied bytes only; no source loading.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Calculates DATA, not source admission. Symlinks/gitlinks/unknown modes refused.
 *
 * @param array $entries Complete flat files with path/mode/bytes keys only.
 * @return string|false Git tree object ID, never an execution grant.
 */
function adbmin_tree_oid( array $entries ) {
	if ( count( $entries ) > 8192 ) {
		return false;
	}
	$tree  = array();
	$seen  = array();
	$total = 0;
	foreach ( $entries as $entry ) {
		if ( ! is_array( $entry ) || array_keys( $entry ) !== array( 'path', 'mode', 'bytes' ) || ! adbmin_path( $entry['path'] )
			|| ! in_array( $entry['mode'], array( '100644', '100755' ), true ) || ! is_string( $entry['bytes'] ) || strlen( $entry['bytes'] ) > 134217728 ) {
			return false;
		}
		$total += strlen( $entry['bytes'] );
		if ( $total > 134217728 ) {
			return false;
		}
		$node   =& $tree;
		$prefix = '';
		$parts  = explode( '/', $entry['path'] );
		foreach ( $parts as $index => $part ) {
			$prefix = '' === $prefix ? $part : $prefix . '/' . $part;
			$key    = strtolower( $prefix );
			if ( isset( $seen[ $key ] ) && $seen[ $key ] !== $prefix ) {
				return false;
			}
			$seen[ $key ] = $prefix;
			$leaf         = count( $parts ) - 1 === $index;
			if ( isset( $node[ $part ] ) && ( $leaf || '40000' !== $node[ $part ]['mode'] ) ) {
				return false;
			}
			if ( $leaf ) {
				$node[ $part ] = array(
					'mode' => $entry['mode'],
					'oid'  => sha1( 'blob ' . strlen( $entry['bytes'] ) . "\0" . $entry['bytes'] ),
				);
			} else {
				if ( ! isset( $node[ $part ] ) ) {
					$node[ $part ] = array(
						'mode'     => '40000',
						'children' => array(),
					);
				}
				$node =& $node[ $part ]['children'];
			}
		}
		unset( $node );
	}
	// Encoder is private to this validated, depth/size-bounded input tree.
	$encode = static function ( array $node ) use ( &$encode ) {
		$ordered = array();
		foreach ( $node as $name => $entry ) {
			$ordered[ $name . ( '40000' === $entry['mode'] ? '/' : '' ) ] = array( $name, $entry );
		}
		uksort( $ordered, 'strcmp' );
		$bytes = '';
		foreach ( $ordered as $item ) {
			list( $name, $entry ) = $item;
			$oid                  = '40000' === $entry['mode'] ? $encode( $entry['children'] ) : $entry['oid'];
			$bytes               .= $entry['mode'] . ' ' . $name . "\0" . pack( 'H*', $oid );
		}
		return sha1( 'tree ' . strlen( $bytes ) . "\0" . $bytes );
	};
	return $encode( $tree );
}

/**
 * Complete fixed minimum tree plus independent source pins, DATA only.
 *
 * @param array $entries All supplied files.
 * @return bool
 */
function adbmin_core_tree( array $entries ) {
	$oid = adbmin_tree_oid( $entries );
	if ( false === $oid || ! hash_equals( adbmin_catalog()['core_tree'], $oid ) ) {
		return false;
	}
	$profiles = require __DIR__ . '/source-policy-supported-core-pins.php';
	$pins     = array();
	foreach ( $profiles['6.0']['pins'] as $name => $digest ) {
		$pins[ 'wp-includes/' . $name ] = $digest;
	}
	$pins['wp-admin/includes/schema.php'] = '788cb6b37c80d9eff4c15863d34fb8e2a2d30207c7a455575ca1ec4e49614a3a';
	foreach ( $entries as $entry ) {
		if ( isset( $pins[ $entry['path'] ] ) ) {
			if ( ! adbmin_digest( $entry['bytes'], $pins[ $entry['path'] ] ) ) {
				return false;
			}
			unset( $pins[ $entry['path'] ] );
		}
	}
	return array() === $pins;
}

// Fixed sibling declarations only; no runtime/source artifact is loaded.
require_once __DIR__ . '/source-policy-minimum-artifacts.php';
