<?php
/**
 * Synthetic dependency/layout/profile consistency only. No IO or load authority.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Claims supplied here are never actual platform, filesystem or process evidence. */
final class Alynt_Drime_Backups_Dashboard_Minimum_Launch_Data {
	/** Exact application comparison references from the authenticated ZIP. @return array */
	public static function application_hashes() {
		return array(
			'php.exe'            => '4b06073dd8230421ba701a677dd8b1aa23ed55706663d3fdf996702b557f83b2',
			'php7.dll'           => '51a5c862da428391c4864d862ca4fffbc608362cd66ada757b84d991a65bd035',
			'ext/php_mysqli.dll' => '1c69a4336a2a42fef76c0d840543b8cd0a1aae4eb6f9111dfb5f2e6ed6a46891',
		);
	}

	/** Exact direct-name classification DATA; labels are not provenance verification. @return array */
	public static function dependencies() {
		$names = array( 'advapi32.dll', 'bcrypt.dll', 'crypt32.dll', 'dnsapi.dll', 'imagehlp.dll', 'iphlpapi.dll', 'kernel32.dll', 'ole32.dll', 'shell32.dll', 'vcruntime140.dll', 'ws2_32.dll' );
		foreach ( array( 'convert', 'environment', 'filesystem', 'heap', 'locale', 'math', 'runtime', 'stdio', 'string', 'time', 'utility' ) as $name ) {
			$names[] = 'api-ms-win-crt-' . $name . '-l1-1-0.dll';
		}
		$result = array();
		foreach ( $names as $name ) {
			$result[ $name ] = 'vcruntime140.dll' === $name ? 'installed-microsoft-runtime' : 'platform-profile';
		}
		ksort( $result );
		return $result;
	}

	/**
	 * Strict lowercase digest syntax, not authentication.
	 *
	 * @param mixed $value Supplied DATA.
	 * @return bool
	 */
	public static function digest( $value ) {
		return is_string( $value ) && 64 === strlen( $value ) && 1 === preg_match( '/\A[a-f0-9]{64}\z/D', $value );
	}

	/**
	 * Exact bounded synthetic layout. No path is resolved or file inspected.
	 *
	 * @param array $facts Supplied primitive claims.
	 * @return array|false Non-authorizing consistency summary.
	 */
	public static function inspect( array $facts ) {
		if ( 8 !== count( $facts ) || array_keys( $facts ) !== array( 'run', 'root', 'platform', 'artifacts', 'files', 'cwd_entries', 'environment', 'dependencies' )
			|| ! is_string( $facts['run'] ) || 32 !== strlen( $facts['run'] ) || ! preg_match( '/\A[a-f0-9]{32}\z/D', $facts['run'] ) || ! is_string( $facts['root'] )
			|| strlen( $facts['root'] ) > 160 || ! preg_match( '/\A[A-Z]:\/(.+)\/alynt-minimum-' . $facts['run'] . '\z/D', $facts['root'] ) || ! adbmin_path( substr( $facts['root'], 3 ) )
			|| ! is_array( $facts['platform'] ) || ! is_array( $facts['artifacts'] ) || ! is_array( $facts['files'] ) || ! is_array( $facts['environment'] ) || ! is_array( $facts['dependencies'] ) || array() !== $facts['cwd_entries'] ) {
			return false;
		}
		$p = $facts['platform'];
		if ( 4 !== count( $p ) || array_keys( $p ) !== array( 'root', 'system', 'build', 'provenance' ) || ! is_string( $p['root'] ) || ! preg_match( '/\A[A-Z]:\/Windows\z/D', $p['root'] )
			|| $p['system'] !== $p['root'] . '/System32' || ! is_string( $p['build'] ) || ! preg_match( '/\A[0-9]{1,5}\.[0-9]{1,5}\.[0-9]{1,6}\z/D', $p['build'] )
			|| 'reviewed-platform-profile' !== $p['provenance'] || 0 === strpos( strtolower( $facts['root'] . '/' ), strtolower( $p['root'] . '/' ) ) ) {
			return false;
		}
		$catalog = adbmin_catalog();
		if ( array(
			'php_sha256'  => $catalog['php_sha256'],
			'core_tree'   => $catalog['core_tree'],
			'source_pins' => 12,
		) !== $facts['artifacts']
			|| 5 !== count( $facts['files'] ) || array_keys( $facts['files'] ) !== array( 'php.exe', 'php7.dll', 'ext/php_mysqli.dll', 'worker.php', 'producer.php' ) || self::dependencies() !== $facts['dependencies'] ) {
			return false;
		}
		foreach ( self::application_hashes() as $role => $hash ) {
			if ( $facts['files'][ $role ] !== $hash ) {
				return false;
			}
		}
		if ( ! self::digest( $facts['files']['worker.php'] ) || ! self::digest( $facts['files']['producer.php'] )
			|| array(
				'SystemRoot' => $p['root'],
				'WINDIR'     => $p['root'],
				'PATH'       => $p['system'],
				'TEMP'       => $facts['root'] . '/tmp',
				'TMP'        => $facts['root'] . '/tmp',
			) !== $facts['environment'] ) {
			return false;
		}
		$parts = array( $facts['run'], $facts['root'], $p['root'], $p['system'], $p['build'], $p['provenance'], $catalog['php_sha256'], $catalog['core_tree'], '12' );
		foreach ( array( $facts['files'], $facts['environment'], $facts['dependencies'] ) as $rows ) {
			foreach ( $rows as $key => $value ) {
				$parts[] = $key . '|' . $value;
			}
		}
		return array(
			'synthetic_consistent' => true,
			'manifest_sha256'      => hash( 'sha256', implode( "\n", $parts ) ),
			'source_sha256'        => hash( 'sha256', $facts['files']['worker.php'] . "\n" . $facts['files']['producer.php'] ),
			'execution'            => false,
			'load_authorized'      => false,
			'filesystem_verified'  => false,
			'platform_verified'    => false,
			'native_qualified'     => false,
		);
	}

	/**
	 * Fixed expected profile DATA, not observed runtime facts.
	 *
	 * @param mixed $stage Fixed synthetic stage.
	 * @param mixed $source Frozen source digest claim.
	 * @param mixed $base Compiled base-module manifest digest claim.
	 * @return array|false
	 */
	public static function runtime_template( $stage, $source, $base ) {
		if ( ! in_array( $stage, array( 'base', 'mysqli' ), true ) || ! self::digest( $source ) || ! self::digest( $base ) ) {
			return false;
		}
		$mysqli = 'mysqli' === $stage;
		return array(
			'os'              => 'Windows',
			'sapi'            => 'cli',
			'php'             => '7.4.33',
			'int_size'        => 8,
			'zts'             => false,
			'ini_loaded'      => false,
			'ini_scanned'     => false,
			'prepend'         => '',
			'append'          => '',
			'include_count'   => 2,
			'source_sha256'   => $source,
			'base_sha256'     => $base,
			'mysqli_internal' => $mysqli,
			'symbols'         => $mysqli ? array(
				'mysqli_get_client_info' => array(
					'internal'  => true,
					'extension' => 'mysqli',
				),
				'mysqli_driver'          => array(
					'internal'  => true,
					'extension' => 'mysqli',
				),
			) : array(),
			'mysqlnd'         => $mysqli ? 'mysqlnd 7.4.33' : null,
			'mode_before'     => $mysqli ? 0 : null,
			'mode_after'      => $mysqli ? 0 : null,
		);
	}

	/**
	 * Checks exact supplied DATA without executing or observing anything.
	 *
	 * @param mixed $stage Fixed stage.
	 * @param array $facts Supplied facts.
	 * @param mixed $source Expected source digest claim.
	 * @param mixed $base Expected compiled-module digest claim.
	 * @return bool Consistency only.
	 */
	public static function runtime_data( $stage, array $facts, $source, $base ) {
		$expected = self::runtime_template( $stage, $source, $base );
		return false !== $expected && $expected === $facts;
	}

	/**
	 * Complete expected/observed module DATA; no actual Windows module enumeration.
	 *
	 * @param mixed $stage Fixed stage.
	 * @param array $layout Supplied layout.
	 * @param array $expected Supplied reviewed-set claims, not actual provenance.
	 * @param array $observed Supplied observation claims, not physical evidence.
	 * @return array|false
	 */
	public static function module_data( $stage, array $layout, array $expected, array $observed ) {
		if ( ! in_array( $stage, array( 'base', 'mysqli' ), true ) || false === self::inspect( $layout ) ) {
			return false;
		}
		$want = self::module_rows( $stage, $layout, $expected );
		$have = self::module_rows( $stage, $layout, $observed );
		if ( false === $want || false === $have || $want !== $have ) {
			return false;
		}
		$parts = array();
		foreach ( $have as $row ) {
			$parts[] = implode( '|', $row );
		}
		return array(
			'sha256'               => hash( 'sha256', implode( "\n", $parts ) ),
			'count'                => count( $have ),
			'synthetic_consistent' => true,
			'execution'            => false,
			'load_authorized'      => false,
			'platform_verified'    => false,
			'native_qualified'     => false,
		);
	}

	/**
	 * Bounds and canonicalizes primitive rows before comparison/hashing.
	 *
	 * @param string $stage Fixed validated stage.
	 * @param array  $layout Validated layout DATA.
	 * @param array  $rows Untrusted primitive rows.
	 * @return array|false
	 */
	private static function module_rows( $stage, array $layout, array $rows ) {
		if ( count( $rows ) < 4 || count( $rows ) > 128 || array_keys( $rows ) !== range( 0, count( $rows ) - 1 ) ) {
			return false;
		}
		$roles = self::application_hashes();
		if ( 'base' === $stage ) {
			unset( $roles['ext/php_mysqli.dll'] );
		}
		$found = array();
		$map   = array();
		$total = 0;
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) || 5 !== count( $row ) || array_keys( $row ) !== array( 'name', 'path', 'sha256', 'size', 'origin' ) || ! is_string( $row['name'] )
				|| ! preg_match( '/\A[a-z0-9][a-z0-9._-]{0,74}\.(?:dll|exe)\z/D', $row['name'] ) || isset( $map[ $row['name'] ] ) || ! is_string( $row['path'] ) || strlen( $row['path'] ) > 240
				|| ! preg_match( '/\A[A-Z]:\//D', $row['path'] ) || ! adbmin_path( substr( $row['path'], 3 ) ) || ! self::digest( $row['sha256'] ) || ! is_int( $row['size'] ) || $row['size'] < 1 || $row['size'] > 33554432 ) {
				return false;
			}
			$total += $row['size'];
			if ( $total > 268435456 ) {
				return false;
			}
			if ( 'publisher' === $row['origin'] ) {
				$role = 'php_mysqli.dll' === $row['name'] ? 'ext/php_mysqli.dll' : $row['name'];
				$path = $layout['root'] . '/runtime/' . $role;
				if ( ! isset( $roles[ $role ] ) || $roles[ $role ] !== $row['sha256'] || $path !== $row['path'] ) {
					return false;
				}
				$found[ $role ] = true;
			} else {
				$origin = 'vcruntime140.dll' === $row['name'] ? 'installed-microsoft-runtime' : 'platform-profile';
				$system = $layout['platform']['system'] . '/' . $row['name'];
				$sxs    = $layout['platform']['root'] . '/WinSxS/';
				$tail   = '/' . $row['name'];
				$actual = substr( $row['path'], -strlen( $row['name'] ) - 1 );
				if ( isset( self::application_hashes()[ $row['name'] ] ) || 'php_mysqli.dll' === $row['name'] || '.dll' !== substr( $row['name'], -4 ) || $origin !== $row['origin']
					|| ( $system !== $row['path'] && ( 0 !== strpos( $row['path'], $sxs ) || $tail !== $actual ) ) ) {
					return false;
				}
			}
			$map[ $row['name'] ] = $row;
		}
		if ( count( $found ) !== count( $roles ) || ! isset( $map['kernel32.dll'], $map['vcruntime140.dll'] ) ) {
			return false;
		}
		ksort( $map );
		return $map;
	}
}

// Pure fixed sibling declarations only, no installed source or executable loading.
require_once __DIR__ . '/source-policy-minimum-artifacts.php';
