<?php
/**
 * Exact disposable file ownership, exclusive writes and non-recursive cleanup.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Owned temporary child only; no caller-selected root or recursive deletion. */
final class Alynt_Drime_Backups_Dashboard_Minimum_Owned_Files {
	/**
	 * Exact generated child.
	 *
	 * @var string
	 */
	private $root;
	/**
	 * Random ownership marker, never a capability for another root.
	 *
	 * @var string
	 */
	private $marker;
	/**
	 * Exact created regular files.
	 *
	 * @var array
	 */
	private $files = array();
	/**
	 * Exact created directories.
	 *
	 * @var array
	 */
	private $dirs = array();
	/**
	 * Original root identity.
	 *
	 * @var array
	 */
	private $identity;
	/**
	 * Whole disposable operation start.
	 *
	 * @var float
	 */
	private $started;

	/**
	 * Allocates one new private child; never reuses an existing target.
	 *
	 * @throws RuntimeException Allocation or partial cleanup is refused.
	 * @throws Throwable Original initialization failure after owned cleanup.
	 */
	public function __construct() {
		$this->started = microtime( true );
		$base          = realpath( sys_get_temp_dir() );
		if ( false === $base || is_link( $base ) || ! is_dir( $base ) ) {
			throw new RuntimeException( 'temporary-parent-refused' );
		}
		$this->root = $base . DIRECTORY_SEPARATOR . 'alynt-minimum-' . bin2hex( random_bytes( 16 ) );
		if ( strlen( $this->root ) > 160 || ! mkdir( $this->root, 0700 ) ) {
			throw new RuntimeException( 'owned-root-refused' );
		}
		$this->identity = lstat( $this->root );
		$this->marker   = 'ADBMA1|' . basename( $this->root ) . '|' . bin2hex( random_bytes( 16 ) );
		try {
			$this->write( '.owner', $this->marker );
		} catch ( Throwable $error ) {
			$this->assert_root();
			$partial = $this->root . DIRECTORY_SEPARATOR . '.owner';
			if ( ( file_exists( $partial ) && ( ! isset( $this->files[ $partial ] ) || is_link( $partial ) || ! unlink( $partial ) ) ) || ! rmdir( $this->root ) ) {
				throw new RuntimeException( 'owned-constructor-cleanup-unconfirmed' );
			}
			throw $error;
		}
	}

	/**
	 * Checks the outer provisioning budget; cleanup remains permitted after expiry.
	 *
	 * @throws RuntimeException Provisioning ceiling exceeded.
	 */
	public function budget() {
		if ( microtime( true ) - $this->started >= 600 ) {
			throw new RuntimeException( 'owned-provisioning-budget-refused' );
		}
	}

	/** Returns the exact owned child for non-secret proof correlation. @return string */
	public function root() {
		return $this->root;
	}

	/**
	 * Refuses replacement or links before destructive work.
	 *
	 * @throws RuntimeException Root identity changed.
	 */
	private function assert_root() {
		clearstatcache();
		$now = lstat( $this->root );
		if ( is_link( $this->root ) || ! is_dir( $this->root ) || false === $now || $now['ino'] !== $this->identity['ino'] || $now['dev'] !== $this->identity['dev']
			|| str_replace( '\\', '/', realpath( $this->root ) ) !== str_replace( '\\', '/', $this->root ) ) {
			throw new RuntimeException( 'owned-root-changed' );
		}
	}

	/**
	 * Resolves one relative path beneath the verified child; no link traversal.
	 *
	 * @param string $relative Owned relative name.
	 * @param bool   $create Create missing parent directories only.
	 * @return string
	 * @throws RuntimeException Unsafe path or directory creation failure.
	 */
	public function path( $relative, $create = false ) {
		$this->assert_root();
		if ( ! adbmin_path( $relative ) || strlen( $this->root ) + strlen( $relative ) + 1 > 240 ) {
			throw new RuntimeException( 'owned-path-refused' );
		}
		$parts = explode( '/', $relative );
		$path  = $this->root;
		foreach ( $parts as $index => $part ) {
			$path .= DIRECTORY_SEPARATOR . $part;
			if ( is_link( $path ) ) {
				throw new RuntimeException( 'owned-alias-refused' );
			}
			if ( $index < count( $parts ) - 1 && ! file_exists( $path ) && $create ) {
				if ( ! mkdir( $path, 0700 ) ) {
					throw new RuntimeException( 'owned-parent-create-refused' );
				}
				$this->dirs[ $path ] = true;
			}
			if ( is_link( $path ) || ( file_exists( $path ) && str_replace( '\\', '/', realpath( $path ) ) !== str_replace( '\\', '/', $path ) )
				|| ( $index < count( $parts ) - 1 && ! is_dir( $path ) ) ) {
				throw new RuntimeException( 'owned-alias-refused' );
			}
		}
		return $path;
	}

	/**
	 * Exclusive binary file write, never overwrite.
	 *
	 * @param string $relative Owned name.
	 * @param string $bytes Bounded contents.
	 * @throws RuntimeException Unsafe write or resource failure.
	 */
	public function write( $relative, $bytes ) {
		$this->budget();
		if ( ! is_string( $bytes ) || strlen( $bytes ) > 268435456 || count( $this->files ) >= 16400 ) {
			throw new RuntimeException( 'owned-write-limit' );
		}
		$path = $this->path( $relative, true );
		if ( file_exists( $path ) ) {
			throw new RuntimeException( 'owned-overwrite-refused' );
		}
		$stream = fopen( $path, 'xb' );
		if ( false === $stream ) {
			throw new RuntimeException( 'owned-create-refused' );
		}
		$this->files[ $path ] = true;
		$length               = strlen( $bytes );
		try {
			for ( $offset = 0; $offset < $length; $offset += $written ) {
				$written = fwrite( $stream, substr( $bytes, $offset, 1048576 ) );
				if ( false === $written || 0 === $written ) {
					throw new RuntimeException( 'owned-write-refused' );
				}
			}
		} finally {
			fclose( $stream );
		}
	}

	/**
	 * Bounded regular-file read of a tracked payload.
	 *
	 * @param string $relative Owned name.
	 * @param int    $limit Byte ceiling.
	 * @return string
	 * @throws RuntimeException Unsafe or over-budget read.
	 */
	public function read( $relative, $limit ) {
		$path = $this->path( $relative );
		if ( ! isset( $this->files[ $path ] ) || ! is_file( $path ) || filesize( $path ) > $limit ) {
			throw new RuntimeException( 'owned-read-refused' );
		}
		$bytes = file_get_contents( $path, false, null, 0, $limit + 1 );
		if ( false === $bytes || strlen( $bytes ) > $limit ) {
			throw new RuntimeException( 'owned-read-limit' );
		}
		return $bytes;
	}

	/**
	 * Exact tracked cleanup; validates every target before deleting anything.
	 *
	 * @throws RuntimeException Ownership or exact deletion cannot be confirmed.
	 */
	public function cleanup() {
		$this->assert_root();
		if ( $this->read( '.owner', 256 ) !== $this->marker ) {
			throw new RuntimeException( 'owned-marker-changed' );
		}
		foreach ( array_merge( array( $this->root ), array_keys( $this->dirs ) ) as $dir ) {
			if ( is_link( $dir ) || str_replace( '\\', '/', realpath( $dir ) ) !== str_replace( '\\', '/', $dir ) ) {
				throw new RuntimeException( 'owned-cleanup-alias-refused' );
			}
			foreach ( new DirectoryIterator( $dir ) as $entry ) {
				if ( $entry->isDot() ) {
					continue;
				}
				$path = $entry->getPathname();
				if ( $entry->isLink() || ( ! isset( $this->files[ $path ] ) && ! isset( $this->dirs[ $path ] ) ) ) {
					throw new RuntimeException( 'owned-cleanup-inventory-refused' );
				}
			}
		}
		foreach ( array_keys( $this->files ) as $path ) {
			if ( is_link( $path ) || ! is_file( $path ) ) {
				throw new RuntimeException( 'owned-cleanup-type-refused' );
			}
		}
		foreach ( array_reverse( array_keys( $this->files ) ) as $path ) {
			$this->path( str_replace( DIRECTORY_SEPARATOR, '/', substr( $path, strlen( $this->root ) + 1 ) ) );
			if ( ! unlink( $path ) ) {
				throw new RuntimeException( 'owned-unlink-refused' );
			}
		}
		foreach ( array_reverse( array_keys( $this->dirs ) ) as $dir ) {
			if ( is_link( $dir ) || str_replace( '\\', '/', realpath( $dir ) ) !== str_replace( '\\', '/', $dir ) || ! rmdir( $dir ) ) {
				throw new RuntimeException( 'owned-rmdir-refused' );
			}
		}
		if ( ! rmdir( $this->root ) || file_exists( $this->root ) ) {
			throw new RuntimeException( 'owned-root-cleanup-unconfirmed' );
		}
	}
}

require_once __DIR__ . '/source-policy-minimum-artifacts.php';
