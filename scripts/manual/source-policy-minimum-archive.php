<?php
/**
 * Read-only libzip consistency, complete inventory and bounded CRC byte decoding.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Decodes an owned archive as data only; never extractTo or executable loading.
 *
 * @param Alynt_Drime_Backups_Dashboard_Minimum_Owned_Files $owned Exact child owner.
 * @param string                                            $kind Fixed php/core archive role.
 * @return array Validated inventory and in-memory files.
 * @throws RuntimeException Unsupported archive or changed bytes.
 */
function adbmin_archive( Alynt_Drime_Backups_Dashboard_Minimum_Owned_Files $owned, $kind ) {
	$owned->budget();
	if ( ! in_array( $kind, array( 'php', 'core' ), true ) || ! class_exists( 'ZipArchive', false ) || ! defined( 'ZipArchive::RDONLY' ) ) {
		throw new RuntimeException( 'archive-runtime-refused' );
	}
	$bytes  = $owned->read( $kind . '.zip', 67108864 );
	$digest = hash( 'sha256', $bytes );
	if ( 'php' === $kind && ! adbmin_php_zip( $bytes ) ) {
		throw new RuntimeException( 'publisher-digest-refused' );
	}
	$zip = new ZipArchive();
	if ( true !== $zip->open( $owned->path( $kind . '.zip' ), ZipArchive::RDONLY | ZipArchive::CHECKCONS ) ) {
		throw new RuntimeException( 'zip-consistency-refused' );
	}
	try {
		return adbmin_zip_contents( $zip, $kind, strlen( $bytes ), strlen( $owned->root() ) + 8 ) + array( 'zip_sha256' => $digest );
	} finally {
		$zip->close();
		if ( hash( 'sha256', $owned->read( $kind . '.zip', 67108864 ) ) !== $digest ) {
			throw new RuntimeException( 'archive-changed' );
		}
	}
}

/**
 * Complete metadata validation precedes all expansion. Does not write files.
 *
 * @param ZipArchive $zip Existing read-only archive.
 * @param string     $kind Fixed candidate kind.
 * @param int        $packed Actual archive length.
 * @param int        $root_length Owned output-root length.
 * @return array
 * @throws RuntimeException Metadata, resource or content integrity refusal.
 */
function adbmin_zip_contents( ZipArchive $zip, $kind, $packed, $root_length ) {
	$count = $zip->numFiles; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Native libzip API.
	if ( $count < 1 || $count > 8192 ) {
		throw new RuntimeException( 'zip-entry-limit' );
	}
	$inventory = array();
	$rows      = array();
	for ( $index = 0; $index < $count; ++$index ) {
		$row = $zip->statIndex( $index, ZipArchive::FL_UNCHANGED );
		if ( false === $row || ! isset( $row['name'], $row['size'], $row['comp_size'], $row['comp_method'], $row['encryption_method'], $row['crc'] )
			|| ! in_array( $row['comp_method'], array( 0, 8 ), true ) || 0 !== $row['encryption_method']
			|| ! $zip->getExternalAttributesIndex( $index, $host, $attributes, ZipArchive::FL_UNCHANGED ) || ! in_array( $host, array( 0, 3, 10 ), true ) ) {
			throw new RuntimeException( 'zip-metadata-refused' );
		}
		$mode      = ( $attributes >> 16 ) & 0xffff;
		$type      = $mode & 0170000;
		$directory = '/' === substr( $row['name'], -1 );
		if ( ( $attributes & 0x400 ) || ( 3 === $host && ( ! in_array( $type, array( 0100000, 0040000 ), true ) || 0 !== ( $mode & 07000 ) || ( 0040000 === $type ) !== $directory ) )
			|| ( 3 !== $host && ( 0 !== ( $attributes & 0x10 ) ) !== $directory ) ) {
			throw new RuntimeException( 'zip-link-type-refused' );
		}
		if ( 'core' === $kind && 3 !== $host ) {
			throw new RuntimeException( 'core-file-mode-refused' );
		}
		$name        = $directory ? substr( $row['name'], 0, -1 ) : $row['name'];
		$inventory[] = array(
			'path'       => $name,
			'kind'       => $directory ? 'directory' : 'file',
			'size'       => $row['size'],
			'compressed' => $row['comp_size'],
		);
		$rows[]      = array( $row, $directory, $mode );
	}
	$plan = adbmin_inventory( $kind, $inventory, $packed, $root_length );
	if ( false === $plan ) {
		throw new RuntimeException( 'zip-inventory-refused' );
	}
	$files = array();
	foreach ( $rows as $entry ) {
		list( $row, $directory, $mode ) = $entry;
		if ( $directory ) {
			continue;
		}
		$stream = $zip->getStream( $row['name'] );
		if ( false === $stream ) {
			throw new RuntimeException( 'zip-stream-refused' );
		}
		$content = '';
		$length  = 0;
		try {
			while ( ! feof( $stream ) && $length <= $row['size'] ) {
				$chunk = fread( $stream, min( 1048576, $row['size'] - strlen( $content ) + 1 ) );
				if ( false === $chunk || ( '' === $chunk && ! feof( $stream ) ) ) {
					throw new RuntimeException( 'zip-stream-read-refused' );
				}
				$content .= $chunk;
				$length   = strlen( $content );
			}
		} finally {
			fclose( $stream );
		}
		if ( strlen( $content ) !== $row['size'] || hash( 'crc32b', $content ) !== sprintf( '%08x', $row['crc'] ) ) {
			throw new RuntimeException( 'zip-crc-size-refused' );
		}
		$name    = 'core' === $kind ? substr( $row['name'], strlen( 'WordPress-' . adbmin_catalog()['core_commit'] . '/' ) ) : $row['name'];
		$files[] = array(
			'path'  => $name,
			'mode'  => ( $mode & 0111 ) ? '100755' : '100644',
			'bytes' => $content,
		);
	}
	return array(
		'inventory' => $plan,
		'files'     => $files,
	);
}

require_once __DIR__ . '/source-policy-minimum-artifacts.php';
