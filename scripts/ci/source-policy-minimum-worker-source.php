<?php
/**
 * Fixed source/inventory readbacks inside the trusted candidate, not attestation.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'adbb_check' ) ) {
	exit( 1 );
}
/** Checks the parent-frozen image, including all actually included files.
 *
 * @return array Non-secret hashes.
 */
function adbm_source_rows() {
	$root = realpath( dirname( __DIR__, 2 ) );
	$wire = file_get_contents( $root . '/cohort.sha256' );
	adbb_check( is_string( $wire ) && strlen( $wire ) < 65536, 'bounded source cohort' );
	$rows = array();
	foreach ( explode( "\n", rtrim( $wire, "\n" ) )as$line ) {
		adbb_check( 1 === preg_match( '/\A([a-f0-9]{64})  ([A-Za-z0-9_.\/-]{1,191})\z/', $line, $match ), 'finite source row' );
		$file = realpath( $root . '/' . $match[2] );
		adbb_check( false !== $file && strpos( $file, $root . '/' ) === 0 && ! isset( $rows[ $file ] ) && is_file( $file ) && hash_equals( $match[1], hash_file( 'sha256', $file ) ), 'parent source readback' );
		$rows[ $file ] = $match[1];
	}
	adbb_check( count( $rows ) > 30 && count( $rows ) < 512, 'complete finite cohort' );
	$loaded = get_included_files();
	foreach ( $loaded as$file ) {
		adbb_check( isset( $rows[ realpath( $file ) ] ), 'no unknown included code' );
	}
	if ( isset( $GLOBALS['adbm_included'] ) ) {
		adbb_check( $loaded === $GLOBALS['adbm_included'], 'no late includes' );
	} else {
		$GLOBALS['adbm_included'] = $loaded;
	}
	return $rows;
}
/** Checks static roots and candidate-reported module DATA, not loaded origins.
 *
 * @return string Inventory hash.
 */
function adbm_modules() {
	adbb_check( PHP_BINARY === '/usr/local/bin/php' && hash_file( 'sha256', PHP_BINARY ) === '51810786a5d7ab1e43e7d43077493d7dfced717cc81fc08270cde58b32731d29', 'selected static PHP root readback, not loaded-object attestation' );
	adbb_check( hash_file( 'sha256', '/usr/local/lib/php/extensions/no-debug-non-zts-20190902/mysqli.so' ) === 'aa91cdc28133fac497aade6a658e83c219fcbbbfb809069969dd32c749a8657e', 'selected static mysqli root readback' );
	$names = get_loaded_extensions();
	sort( $names, SORT_STRING );
	adbb_check( count( $names ) <= 64 && false === php_ini_loaded_file() && ! php_ini_scanned_files(), 'no donor or host INI' );
	foreach ( $names as$name ) {
		adbb_check( 1 === preg_match( '/\A[A-Za-z0-9_ ]{1,64}\z/', $name ), 'bounded module identity' );
	}
	adbb_check( ! extension_loaded( 'pdo_mysql' ) && ! extension_loaded( 'mbstring' ) && '' === ini_get( 'auto_prepend_file' ) && '' === ini_get( 'auto_append_file' ), 'fixed dynamic module profile' );
	foreach ( array( 'mysqli_init', 'mysqli_real_connect', 'mysqli_query', 'mysqli_close' )as$name ) {
		$function = new ReflectionFunction( $name );
		adbb_check( $function->isInternal() && 'mysqli' === $function->getExtensionName(), 'internal native declarations' );}
	return hash( 'sha256', implode( "\n", $names ) );
}
