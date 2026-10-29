<?php
/**
 * Static identities, manual regular-file extraction and readback only; no loading.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Parses only the three fixed roles; bounded DATA, never a loader or trust grant.
 *
 * @param array $php Decoded authenticated PHP archive.
 * @return array Selected role/hash/import observations.
 * @throws RuntimeException Missing, ambiguous or malformed selected role.
 */
function adbmin_selected_pe( array $php ) {
	if ( ! isset( $php['files'] ) || ! is_array( $php['files'] ) || count( $php['files'] ) < 3 || count( $php['files'] ) > 8192 ) {
		throw new RuntimeException( 'selected-inventory-refused' );
	}
	$by_name = array();
	foreach ( $php['files'] as $file ) {
		if ( ! is_array( $file ) || ! isset( $file['path'], $file['bytes'] ) || ! is_string( $file['path'] ) || ! is_string( $file['bytes'] ) || isset( $by_name[ strtolower( $file['path'] ) ] ) ) {
			throw new RuntimeException( 'selected-inventory-refused' );
		}
		$by_name[ strtolower( $file['path'] ) ] = $file;
	}
	$pe = array();
	foreach ( array( 'php.exe', 'php7.dll', 'ext/php_mysqli.dll' ) as $role ) {
		if ( ! isset( $by_name[ $role ] ) || $by_name[ $role ]['path'] !== $role ) {
			throw new RuntimeException( 'selected-role-missing' );
		}
		$row = adbmin_pe( $by_name[ $role ]['bytes'], $role );
		if ( false === $row ) {
			throw new RuntimeException( 'selected-pe-structure-refused' );
		}
		$row['sha256'] = hash( 'sha256', $by_name[ $role ]['bytes'] );
		$pe[]          = $row;
	}
	return $pe;
}

/**
 * Classifies parsed direct imports only, not presence/transitive/loader trust.
 *
 * @param array $php Decoded authenticated PHP archive.
 * @return array Non-authorizing observations.
 * @throws RuntimeException Missing/invalid selected image or unknown import.
 */
function adbmin_selected_import_identity( array $php ) {
	$pe      = adbmin_selected_pe( $php );
	$by_name = array();
	foreach ( $php['files'] as $file ) {
		$by_name[ strtolower( $file['path'] ) ] = $file;
	}
	$external = array();
	$bundled  = array();
	// ImageHlp is OS-included per Microsoft; this name classification proves no local DLL presence or trust.
	$os = array( 'kernel32.dll', 'user32.dll', 'advapi32.dll', 'shell32.dll', 'ole32.dll', 'oleaut32.dll', 'ws2_32.dll', 'dnsapi.dll', 'iphlpapi.dll', 'bcrypt.dll', 'crypt32.dll', 'secur32.dll', 'normaliz.dll', 'psapi.dll', 'shlwapi.dll', 'version.dll', 'ntdll.dll', 'rpcrt4.dll', 'winmm.dll', 'mswsock.dll', 'imagehlp.dll', 'vcruntime140.dll', 'msvcp140.dll', 'concrt140.dll', 'ucrtbase.dll' );
	foreach ( $pe as $image ) {
		foreach ( $image['imports'] as $name ) {
			if ( isset( $by_name[ $name ] ) ) {
				$bundled[ $name ] = hash( 'sha256', $by_name[ $name ]['bytes'] );
			} elseif ( in_array( $name, $os, true ) || preg_match( '/\Aapi-ms-win-crt-[a-z0-9-]+-l1-1-0\.dll\z/D', $name ) ) {
				$external[ $name ] = 'presence-and-transitive-closure-unverified';
			} else {
				throw new RuntimeException( 'unexpected-import-refused' );
			}
		}
	}
	ksort( $external );
	ksort( $bundled );
	return array(
		'pe'                           => $pe,
		'bundled_direct_imports'       => $bundled,
		'external_direct_imports'      => $external,
		'dependency_closure_qualified' => false,
		'runtime_loading'              => false,
	);
}

/**
 * Complete fixed core and selected PE structure checks before extraction.
 *
 * @param array $php Decoded authenticated PHP archive.
 * @param array $core Decoded core archive.
 * @return array Non-loading evidence, never dependency closure qualification.
 * @throws RuntimeException Fixed source, binary structure or import refusal.
 */
function adbmin_static_identity( array $php, array $core ) {
	if ( ! adbmin_core_tree( $core['files'] ) ) {
		throw new RuntimeException( 'complete-core-tree-refused' );
	}
	return array(
		'core_tree'          => adbmin_catalog()['core_tree'],
		'source_sha256_pins' => 12,
	) + adbmin_selected_import_identity( $php );
}

/**
 * Writes validated regular payload bytes exclusively, then checks exact readback.
 * This is not an installer and never applies archive permissions or attributes.
 *
 * @param Alynt_Drime_Backups_Dashboard_Minimum_Owned_Files $owned Exact child owner.
 * @param array                                             $php Complete validated PHP archive DATA.
 * @param array                                             $core Complete validated core archive DATA.
 * @return array Payload correlation and counts, not executable authority.
 * @throws RuntimeException Readback differs from validated bytes.
 */
function adbmin_extract_static( Alynt_Drime_Backups_Dashboard_Minimum_Owned_Files $owned, array $php, array $core ) {
	$rows  = array();
	$files = 0;
	$bytes = 0;
	foreach ( array(
		'runtime' => $php,
		'source'  => $core,
	) as $prefix => $archive ) {
		foreach ( $archive['files'] as $file ) {
			$relative = $prefix . '/' . $file['path'];
			$owned->write( $relative, $file['bytes'] );
			$expected = hash( 'sha256', $file['bytes'] );
			if ( hash( 'sha256', $owned->read( $relative, strlen( $file['bytes'] ) ) ) !== $expected ) {
				throw new RuntimeException( 'extracted-readback-refused' );
			}
			$rows[] = $relative . ':' . $expected;
			++$files;
			$bytes += strlen( $file['bytes'] );
		}
	}
	sort( $rows );
	return array(
		'files'           => $files,
		'bytes'           => $bytes,
		'payload_sha256'  => hash( 'sha256', implode( "\n", $rows ) ),
		'runtime_loading' => false,
	);
}

require_once __DIR__ . '/source-policy-minimum-tree.php';
require_once __DIR__ . '/source-policy-minimum-pe.php';
