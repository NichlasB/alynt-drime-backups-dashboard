<?php
/**
 * Bounded core ZIP metadata diagnosis only; no payload expansion or admission.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Summarizes all entry kinds/hosts and bounded examples, never source contents.
 *
 * @param ZipArchive $zip Read-only consistency-checked core ZIP.
 * @return array Non-authorizing observations.
 * @throws RuntimeException Unsupported or over-budget metadata.
 */
function adbmin_core_metadata( ZipArchive $zip ) {
	$count = $zip->numFiles; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Native libzip property.
	if ( $count < 1 || $count > 8192 ) {
		throw new RuntimeException( 'metadata-count-refused' );
	}
	$groups   = array();
	$examples = array();
	for ( $index = 0; $index < $count; ++$index ) {
		$row = $zip->statIndex( $index, ZipArchive::FL_UNCHANGED );
		if ( false === $row || ! isset( $row['name'], $row['size'], $row['comp_size'] ) || ! $zip->getExternalAttributesIndex( $index, $host, $attributes, ZipArchive::FL_UNCHANGED ) ) {
			throw new RuntimeException( 'metadata-read-refused' );
		}
		$directory = '/' === substr( $row['name'], -1 );
		$mode      = ( $attributes >> 16 ) & 0xffff;
		$key       = $host . ':' . ( $directory ? 'directory' : 'file' ) . ':' . decoct( $mode ) . ':' . ( $attributes & 0xffff );
		if ( count( $groups ) >= 128 && ! isset( $groups[ $key ] ) ) {
			throw new RuntimeException( 'metadata-group-limit' );
		}
		$groups[ $key ] = ( $groups[ $key ] ?? 0 ) + 1;
		if ( 3 !== $host && count( $examples ) < 6 ) {
			$name       = $directory ? substr( $row['name'], 0, -1 ) : $row['name'];
			$examples[] = array(
				'index'      => $index,
				'name'       => adbmin_path( $name ) ? $name : 'unsafe-name-not-emitted',
				'host'       => $host,
				'mode'       => decoct( $mode ),
				'attributes' => $attributes,
				'directory'  => $directory,
				'size'       => $row['size'],
				'packed'     => $row['comp_size'],
			);
		}
	}
	ksort( $groups );
	return array(
		'entries'           => $count,
		'groups'            => $groups,
		'non_unix_examples' => $examples,
		'payload_expansion' => false,
		'admission'         => false,
	);
}

/**
 * Exact disposable diagnostic body; never calls normal admission/extraction.
 *
 * @return array
 * @throws RuntimeException Diagnostic warnings become fixed safe codes.
 */
function adbmin_core_diagnostic() {
	$owner  = null;
	$zip    = null;
	$opened = false;
	$report = array(
		'status'          => 'refused',
		'runtime_loading' => false,
	);
	try {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Disposable CLI diagnostic; restored in finally.
		set_error_handler(
			static function () {
				throw new RuntimeException( 'metadata-tool-warning' );
			}
		);
		$owner                 = new Alynt_Drime_Backups_Dashboard_Minimum_Owned_Files();
		$report['owned_child'] = basename( $owner->root() );
		$bytes                 = adbmin_download( 'core' );
		$owner->write( 'core.zip', $bytes );
		$report['zip_bytes']                   = strlen( $bytes );
		$report['zip_sha256_correlation_only'] = hash( 'sha256', $bytes );
		$zip                                   = new ZipArchive();
		if ( true !== $zip->open( $owner->path( 'core.zip' ), ZipArchive::RDONLY | ZipArchive::CHECKCONS ) ) {
			throw new RuntimeException( 'metadata-zip-refused' );
		}
		$opened             = true;
		$report['metadata'] = adbmin_core_metadata( $zip );
		$report['status']   = 'metadata-observed-not-qualified';
	} catch ( Throwable $error ) {
		$report['failure'] = $error instanceof RuntimeException && preg_match( '/\A[a-z-]{1,64}\z/D', $error->getMessage() ) ? $error->getMessage() : 'metadata-diagnostic-refused';
	} finally {
		try {
			if ( $opened ) {
				$zip->close();
			}
		} catch ( Throwable $close_error ) {
			$report['status'] = 'refused';
			$report['close']  = 'unconfirmed';
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
		}
		restore_error_handler();
	}
	return $report;
}

require_once __DIR__ . '/source-policy-minimum-acquire.php';

if ( isset( $argv ) && 2 === count( $argv ) && '--confirmed-owned-core-metadata' === $argv[1] && realpath( $argv[0] ) === __FILE__ ) {
	$report = adbmin_core_diagnostic();
	echo json_encode( $report, JSON_UNESCAPED_SLASHES ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Bounded non-secret CLI DATA, no source contents.
	exit( 'metadata-observed-not-qualified' === $report['status'] ? 0 : 1 );
}
