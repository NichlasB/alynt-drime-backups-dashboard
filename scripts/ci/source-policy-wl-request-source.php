<?php
/**
 * Fixed source/module inventory consistent with existing W/L trust model, not attestation.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ADB_WL_REQUEST_PROOF' ) ) {
	exit( 1 ); }

/** Shared case algorithm's source-reader contract, not M job eligibility.
 *
 * @return array
 */
function adbm_source_rows() {
	$root    = str_replace( '\\', '/', realpath( dirname( __DIR__, 2 ) ) );
	$core    = str_replace( '\\', '/', realpath( ABSPATH . WPINC ) );
	$catalog = adbwl_catalog();
	$allowed = array_merge( array_keys( $catalog['pins'] ), array_keys( $catalog['auth'] ) );
	$loaded  = get_included_files();
	$files   = array();
	foreach ( $loaded as $path ) {
		$file = str_replace( '\\', '/', realpath( $path ) );
		$code = strpos( $file, $root . '/includes/' ) === 0 || strpos( $file, $root . '/scripts/ci/' ) === 0 || strpos( $file, $root . '/scripts/manual/' ) === 0;
		adbg_check( ( $code || ( dirname( $file ) === $core && in_array( basename( $file ), $allowed, true ) ) ) && filesize( $file ) <= 1048576, 'finite actual include closure' );
		$files[] = $file;
	}
	if ( isset( $GLOBALS['adbwl_included'] ) ) {
		adbg_check( $GLOBALS['adbwl_included'] === $loaded, 'no late included code' );
	} else {
		$GLOBALS['adbwl_included'] = $loaded;
	}
	$w     = 'w' === getenv( 'WL_REQUEST_PROFILE' );
	$extra = $w ? array( '/scripts/manual/source-policy-ambient-proof-controller.ps1', '/scripts/manual/source-policy-wl-request-controller.ps1', '/scripts/manual/source-policy-cleanup-process.ps1' ) : array( '/scripts/ci/source-policy-linux-qualification-controller.mjs', '/scripts/ci/source-policy-wl-request-contract.mjs', '/scripts/ci/source-policy-linux-qualification.sh', '/scripts/ci/source-policy-linux-core-pins.json', '/.github/workflows/source-policy-linux-qualification.yml' );
	foreach ( $extra as $path ) {
		$files[] = $root . $path;
	}
	$files[] = PHP_BINARY;
	$files[] = ini_get( 'extension_dir' ) . ( $w ? '/php_mysqli.dll' : '/mysqli.so' );
	$files[] = $w ? dirname( PHP_BINARY ) . '/php8.dll' : ini_get( 'extension_dir' ) . '/mysqlnd.so';
	$rows    = array();
	foreach ( array_unique( $files ) as $path ) {
		$file = realpath( $path );
		adbg_check( false !== $file && is_file( $file ), 'fixed source/runtime/controller readback' );
		$rows[ str_replace( '\\', '/', $file ) ] = hash_file( 'sha256', $file );
	}
	ksort( $rows, SORT_STRING );
	adbg_check( count( $rows ) > 30 && count( $rows ) < 512, 'finite complete image' );
	return $rows;
}

/** Module inventory and genuine native declarations, not independent origins.
 *
 * @return string
 */
function adbm_modules() {
	$names = get_loaded_extensions();
	sort( $names, SORT_STRING );
	adbg_check( count( $names ) <= 64 && false === php_ini_loaded_file() && false === php_ini_scanned_files() && ! extension_loaded( 'pdo_mysql' ), 'fixed no-ini module profile' );
	foreach ( array( 'mysqli_init', 'mysqli_real_connect', 'mysqli_query', 'mysqli_close' ) as $name ) {
		$function = new ReflectionFunction( $name );
		adbg_check( $function->isInternal() && 'mysqli' === $function->getExtensionName(), 'internal native function source' );
	}
	return hash( 'sha256', implode( "\n", $names ) );
}
