<?php
/**
 * Exact approved disposable acquisition proof. Never installs or loads artifacts.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fixed reviewed native-CA capability DATA, not a network or execution grant.
 *
 * @param mixed $os Observed platform.
 * @param mixed $version Observed cURL version.
 * @param mixed $ssl Observed TLS backend.
 * @param mixed $native_ca Observed option availability.
 * @return bool No fallback for unreviewed backends or missing capability.
 */
function adbmin_native_ca_profile( $os, $version, $ssl, $native_ca ) {
	return 'Windows' === $os && '8.17.0' === $version && 'OpenSSL/3.5.4' === $ssl && true === $native_ca;
}

/**
 * Fixed HTTPS request, no redirects/proxies/authentication/fallback/retry.
 *
 * @param string $kind Fixed artifact.
 * @return string
 * @throws RuntimeException Fixed request refused.
 */
function adbmin_download( $kind ) {
	if ( ! in_array( $kind, array( 'php', 'core', 'tree' ), true ) || ! extension_loaded( 'curl' ) ) {
		throw new RuntimeException( 'download-preflight-refused' );
	}
	$backend = curl_version();
	if ( ! adbmin_native_ca_profile( PHP_OS_FAMILY, $backend['version'], $backend['ssl_version'], defined( 'CURLSSLOPT_NATIVE_CA' ) ) ) {
		throw new RuntimeException( 'native-ca-profile-refused' );
	}
	$url             = adbmin_catalog()[ $kind . '_url' ];
	$curl            = curl_init( $url );
	$bytes           = '';
	$limit           = 'tree' === $kind ? 2097152 : 67108864;
	$protocol_option = defined( 'CURLOPT_PROTOCOLS_STR' ) ? constant( 'CURLOPT_PROTOCOLS_STR' ) : CURLOPT_PROTOCOLS;
	$protocol_value  = defined( 'CURLOPT_PROTOCOLS_STR' ) ? 'https' : CURLPROTO_HTTPS;
	$configured      = curl_setopt_array(
		$curl,
		array(
			CURLOPT_FOLLOWLOCATION  => false,
			CURLOPT_MAXREDIRS       => 0,
			$protocol_option        => $protocol_value,
			CURLOPT_PROXY           => '',
			CURLOPT_NETRC           => CURL_NETRC_IGNORED,
			CURLOPT_SSL_VERIFYPEER  => true,
			CURLOPT_SSL_VERIFYHOST  => 2,
			CURLOPT_SSL_OPTIONS     => constant( 'CURLSSLOPT_NATIVE_CA' ),
			CURLOPT_CONNECTTIMEOUT  => 10,
			CURLOPT_TIMEOUT         => 120,
			CURLOPT_LOW_SPEED_LIMIT => 1,
			CURLOPT_LOW_SPEED_TIME  => 30,
			CURLOPT_USERAGENT       => 'Alynt-owned-minimum-static-proof/1',
			CURLOPT_WRITEFUNCTION   => static function ( $handle, $chunk ) use ( &$bytes, $limit ) {
				if ( strlen( $bytes ) + strlen( $chunk ) > $limit ) {
					return 0;
				}
				$bytes .= $chunk;
				return strlen( $chunk );
			},
		)
	);
	if ( true !== $configured ) {
		throw new RuntimeException( 'fixed-request-options-refused' );
	}
	try {
		$ok = curl_exec( $curl );
		if ( false === $ok || 200 !== curl_getinfo( $curl, CURLINFO_RESPONSE_CODE ) || curl_getinfo( $curl, CURLINFO_EFFECTIVE_URL ) !== $url || '' === $bytes ) {
			throw new RuntimeException( 'fixed-download-refused', curl_errno( $curl ) ? curl_errno( $curl ) : 1000 + curl_getinfo( $curl, CURLINFO_RESPONSE_CODE ) );
		}
	} finally {
		if ( PHP_VERSION_ID < 80000 ) {
			curl_close( $curl ); // phpcs:ignore Generic.PHP.DeprecatedFunctions.Deprecated -- Resource cleanup on PHP7.4 only; object release on PHP8.
		} else {
			unset( $curl );
		}
	}
	return $bytes;
}

/**
 * Fixed approved body, source/binary freeze and finally-cleanup.
 *
 * @return array
 * @throws RuntimeException Warning handler converts diagnostics to fixed safe codes.
 */
function adbmin_acquisition_proof() {
	$stage   = 'preflight';
	$owned   = null;
	$report  = array(
		'status'                   => 'refused',
		'runtime_loading'          => false,
		'site_database_operations' => false,
	);
	$started = microtime( true );
	$image   = array();
	foreach ( array( __FILE__, __DIR__ . '/source-policy-minimum-owned.php', __DIR__ . '/source-policy-minimum-archive.php', __DIR__ . '/source-policy-minimum-static.php', __DIR__ . '/source-policy-minimum-core-modes.php', __DIR__ . '/source-policy-minimum-artifacts.php', __DIR__ . '/source-policy-minimum-tree.php', __DIR__ . '/source-policy-minimum-pe.php', __DIR__ . '/source-policy-supported-core-pins.php', PHP_BINARY ) as $path ) {
		$image[ $path ] = hash_file( 'sha256', $path );
	}
	try {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Owned CLI proof redacts warnings, restored in finally.
		set_error_handler(
			static function () {
				throw new RuntimeException( 'static-tool-warning' );
			}
		); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Owned CLI proof redacts warnings, restored in finally.
		if ( 'Windows' !== PHP_OS_FAMILY || '8.5.1' !== PHP_VERSION || str_replace( '\\', '/', realpath( PHP_BINARY ) ) !== 'C:/php/php.exe'
			|| '768M' !== ini_get( 'memory_limit' ) || ! extension_loaded( 'zip' ) || ! extension_loaded( 'curl' ) ) {
			throw new RuntimeException( 'trusted-tool-preflight-refused' );
		}
		$owned                   = new Alynt_Drime_Backups_Dashboard_Minimum_Owned_Files();
		$report['trusted_tools'] = array(
			'php'                => PHP_VERSION,
			'libzip'             => ZipArchive::LIBZIP_VERSION,
			'curl'               => curl_version()['version'],
			'ssl_backend'        => curl_version()['ssl_version'],
			'tls_trust'          => 'windows-native-ca-per-request',
			'child_memory_limit' => '768M',
		);
		$report['owned_child']   = basename( $owned->root() );
		$stage                   = 'core-mode-manifest';
		$tree_bytes              = adbmin_download( 'tree' );
		$owned->write( 'tree.json', $tree_bytes );
		$tree  = json_decode( $tree_bytes, true, 32 );
		$modes = is_array( $tree ) ? Alynt_Drime_Backups_Dashboard_Minimum_Core_Modes::from_metadata( $tree ) : false;
		if ( false === $modes ) {
			throw new RuntimeException( 'pinned-core-modes-refused' );
		}
		$report['core_mode_manifest'] = $modes->summary() + array(
			'bytes'              => strlen( $tree_bytes ),
			'sha256_correlation' => hash( 'sha256', $tree_bytes ),
		);
		foreach ( array( 'php', 'core' ) as $kind ) {
			$stage = 'download-' . $kind;
			$bytes = adbmin_download( $kind );
			if ( 'php' === $kind && ! adbmin_php_zip( $bytes ) ) {
				throw new RuntimeException( 'publisher-digest-refused' );
			}
			$owned->write( $kind . '.zip', $bytes );
			$stage             = 'archive-' . $kind;
			$archives[ $kind ] = adbmin_archive( $owned, $kind, 'core' === $kind ? $modes : null );
			$report[ $kind ]   = array(
				'zip_bytes'  => strlen( $bytes ),
				'zip_sha256' => $archives[ $kind ]['zip_sha256'],
				'inventory'  => $archives[ $kind ]['inventory'],
			);
		}
		$stage               = 'static-identities';
		$report['static']    = adbmin_static_identity( $archives['php'], $archives['core'] );
		$stage               = 'extraction-readback';
		$report['extracted'] = adbmin_extract_static( $owned, $archives['php'], $archives['core'] );
		if ( microtime( true ) - $started >= 600 ) {
			throw new RuntimeException( 'provisioning-budget-refused' );
		}
		foreach ( $image as $path => $expected ) {
			if ( hash_file( 'sha256', $path ) !== $expected ) {
				throw new RuntimeException( 'trusted-image-changed' );
			}
		}
		$report['status'] = 'static-verified';
	} catch ( Throwable $error ) {
		$report['failed_stage'] = $stage;
		$report['failure_code'] = is_int( $error->getCode() ) && $error->getCode() >= 0 && $error->getCode() <= 65535 ? $error->getCode() : 0;
		$report['failure']      = $error instanceof RuntimeException && preg_match( '/\A[a-z-]{1,64}\z/D', $error->getMessage() ) ? $error->getMessage() : 'unexpected-static-failure';
	} finally {
		try {
			if ( null !== $owned ) {
				$owned->cleanup();
			}
			$report['cleanup'] = isset( $report['failure'] ) && 'owned-constructor-cleanup-unconfirmed' === $report['failure'] ? 'unconfirmed' : 'confirmed';
		} catch ( Throwable $cleanup_error ) {
			$report['cleanup']       = 'unconfirmed';
			$report['retained_root'] = $owned->root();
			$report['status']        = 'refused';
		}
		restore_error_handler();
	}
	$report['elapsed_ms']           = (int) round( ( microtime( true ) - $started ) * 1000 );
	$report['trusted_image_sha256'] = hash( 'sha256', implode( "\n", array_values( $image ) ) );
	return $report;
}

require_once __DIR__ . '/source-policy-minimum-owned.php';
require_once __DIR__ . '/source-policy-minimum-archive.php';
require_once __DIR__ . '/source-policy-minimum-static.php';

// Sole network entry; including this source for tests never starts the proof.
if ( isset( $argv ) && 2 === count( $argv ) && '--confirmed-owned-minimum-artifacts' === $argv[1] && realpath( $argv[0] ) === __FILE__ ) {
	$result = adbmin_acquisition_proof();
	echo json_encode( $result, JSON_UNESCAPED_SLASHES ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed non-secret CLI evidence only.
	exit( 'static-verified' === $result['status'] && 'confirmed' === $result['cleanup'] ? 0 : 1 );
}
