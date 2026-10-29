<?php
/**
 * Fixed pinned PHP ZIP import observations only; no extraction or admission.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * One bounded diagnostic acquisition, before evidence-based classification.
 *
 * @return array Non-secret observations and exact cleanup status.
 * @throws RuntimeException Tool warnings become safe fixed codes.
 */
function adbmin_import_diagnostic() {
	$owner   = null;
	$stage   = 'preflight';
	$started = microtime( true );
	$report  = array(
		'status'                   => 'refused',
		'admission'                => false,
		'extraction'               => false,
		'runtime_loading'          => false,
		'site_database_operations' => false,
	);
	$image   = array();
	foreach ( array( __FILE__, __DIR__ . '/source-policy-minimum-acquire.php', __DIR__ . '/source-policy-minimum-owned.php', __DIR__ . '/source-policy-minimum-archive.php', __DIR__ . '/source-policy-minimum-static.php', __DIR__ . '/source-policy-minimum-pe.php', __DIR__ . '/source-policy-minimum-artifacts.php', __DIR__ . '/source-policy-minimum-core-modes.php', PHP_BINARY ) as $path ) {
		$image[ $path ] = hash_file( 'sha256', $path );
	}
	try {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Owned CLI diagnostic redacts warnings; restored in finally.
		set_error_handler(
			static function () {
				throw new RuntimeException( 'import-tool-warning' );
			}
		);
		if ( 'Windows' !== PHP_OS_FAMILY || '8.5.1' !== PHP_VERSION || str_replace( '\\', '/', realpath( PHP_BINARY ) ) !== 'C:/php/php.exe'
			|| '768M' !== ini_get( 'memory_limit' ) || ! extension_loaded( 'zip' ) || ! extension_loaded( 'curl' ) ) {
			throw new RuntimeException( 'trusted-tool-preflight-refused' );
		}
		$owner                 = new Alynt_Drime_Backups_Dashboard_Minimum_Owned_Files();
		$report['owned_child'] = basename( $owner->root() );
		$stage                 = 'download-php';
		$bytes                 = adbmin_download( 'php' );
		if ( ! adbmin_php_zip( $bytes ) ) {
			throw new RuntimeException( 'publisher-digest-refused' );
		}
		$owner->write( 'php.zip', $bytes );
		$stage              = 'archive-php';
		$archive            = adbmin_archive( $owner, 'php' );
		$report['php']      = array(
			'zip_bytes'  => strlen( $bytes ),
			'zip_sha256' => $archive['zip_sha256'],
			'inventory'  => $archive['inventory'],
		);
		$stage              = 'selected-imports';
		$report['selected'] = adbmin_selected_pe( $archive );
		$owner->budget();
		$report['status'] = 'imports-observed-not-qualified';
	} catch ( Throwable $error ) {
		$report['failed_stage'] = $stage;
		$report['failure']      = $error instanceof RuntimeException && preg_match( '/\A[a-z-]{1,64}\z/D', $error->getMessage() ) ? $error->getMessage() : 'import-diagnostic-refused';
	} finally {
		try {
			if ( null !== $owner ) {
				$owner->cleanup();
			}
			$report['cleanup'] = isset( $report['failure'] ) && 'owned-constructor-cleanup-unconfirmed' === $report['failure'] ? 'unconfirmed' : 'confirmed';
		} catch ( Throwable $cleanup_error ) {
			$report['status']        = 'refused';
			$report['cleanup']       = 'unconfirmed';
			$report['retained_root'] = $owner->root();
		}
		restore_error_handler();
	}
	foreach ( $image as $path => $expected ) {
		if ( hash_file( 'sha256', $path ) !== $expected ) {
			$report['status']  = 'refused';
			$report['failure'] = 'trusted-image-changed';
		}
	}
	$report['elapsed_ms']           = (int) round( ( microtime( true ) - $started ) * 1000 );
	$report['trusted_image_sha256'] = hash( 'sha256', implode( "\n", array_values( $image ) ) );
	return $report;
}

require_once __DIR__ . '/source-policy-minimum-acquire.php';

// Including this helper never downloads; only the exact owned diagnostic CLI does.
if ( isset( $argv ) && 2 === count( $argv ) && '--confirmed-owned-selected-imports' === $argv[1] && realpath( $argv[0] ) === __FILE__ ) {
	$report = adbmin_import_diagnostic();
	echo json_encode( $report, JSON_UNESCAPED_SLASHES ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed selected names/hashes only, never source contents.
	exit( 'imports-observed-not-qualified' === $report['status'] && 'confirmed' === $report['cleanup'] ? 0 : 1 );
}
