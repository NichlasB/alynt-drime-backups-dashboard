<?php
/**
 * Authenticated fixed Git mode DATA; no inferred regular modes or execution grant.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Read-only mode/blob map constructed only after every Git tree hashes correctly. */
final class Alynt_Drime_Backups_Dashboard_Minimum_Core_Modes {
	/**
	 * Authenticated files.
	 *
	 * @var array
	 */
	private $files;
	/**
	 * Authenticated directories.
	 *
	 * @var array
	 */
	private $directories;

	/**
	 * Fixed factory only.
	 *
	 * @param array $facts Verified graph DATA.
	 */
	private function __construct( array $facts ) {
		$this->files       = $facts['files'];
		$this->directories = $facts['directories'];
	}

	/**
	 * Validates graph integrity for isolated algorithm tests; never creates authority.
	 *
	 * @param array $metadata Complete recursive Git API DATA.
	 * @return array|false Self-consistent graph, not fixed-root admission.
	 */
	public static function inspect( array $metadata ) {
		if ( ! isset( $metadata['sha'], $metadata['tree'], $metadata['truncated'] ) || false !== $metadata['truncated'] || ! is_array( $metadata['tree'] )
			|| count( $metadata['tree'] ) > 8192 || ! is_string( $metadata['sha'] ) || ! preg_match( '/\A[a-f0-9]{40}\z/D', $metadata['sha'] ) ) {
			return false;
		}
		$trees    = array( '' => $metadata['sha'] );
		$children = array( '' => array() );
		$files    = array();
		$names    = array();
		foreach ( $metadata['tree'] as $row ) {
			if ( ! is_array( $row ) || ! isset( $row['path'], $row['mode'], $row['type'], $row['sha'] ) || ! adbmin_path( $row['path'] )
				|| ! is_string( $row['sha'] ) || ! preg_match( '/\A[a-f0-9]{40}\z/D', $row['sha'] ) || ! in_array( $row['mode'], array( '040000', '100644', '100755' ), true )
				|| ( '040000' === $row['mode'] ? 'tree' : 'blob' ) !== $row['type'] || isset( $names[ strtolower( $row['path'] ) ] ) ) {
				return false;
			}
			$names[ strtolower( $row['path'] ) ] = true;
			$slash                               = strrpos( $row['path'], '/' );
			$parent                              = false === $slash ? '' : substr( $row['path'], 0, $slash );
			$name                                = false === $slash ? $row['path'] : substr( $row['path'], $slash + 1 );
			$directory                           = '040000' === $row['mode'];
			$children[ $parent ][ $name . ( $directory ? '/' : '' ) ] = array( $directory ? '40000' : $row['mode'], $name, $row['sha'] );
			if ( $directory ) {
				$trees[ $row['path'] ]    = $row['sha'];
				$children[ $row['path'] ] = $children[ $row['path'] ] ?? array();
			} else {
				$files[ $row['path'] ] = array(
					'mode' => $row['mode'],
					'sha'  => $row['sha'],
				);
			}
		}
		foreach ( $children as $directory => $rows ) {
			if ( ! isset( $trees[ $directory ] ) ) {
				return false;
			}
			uksort( $rows, 'strcmp' );
			$bytes = '';
			foreach ( $rows as $row ) {
				$bytes .= $row[0] . ' ' . $row[1] . "\0" . pack( 'H*', $row[2] );
			}
			if ( ! hash_equals( $trees[ $directory ], sha1( 'tree ' . strlen( $bytes ) . "\0" . $bytes ) ) ) {
				return false;
			}
		}
		ksort( $files );
		ksort( $trees );
		return array(
			'root'        => $metadata['sha'],
			'files'       => $files,
			'directories' => $trees,
		);
	}

	/**
	 * Fixed pinned root only; no caller-provided trust root.
	 *
	 * @param array $metadata Complete Git DATA.
	 * @return self|false
	 */
	public static function from_metadata( array $metadata ) {
		$facts = self::inspect( $metadata );
		return false !== $facts && hash_equals( adbmin_catalog()['core_tree'], $facts['root'] ) ? new self( $facts ) : false;
	}

	/**
	 * Authenticated regular-file mode/blob, no default.
	 *
	 * @param string $path Relative path.
	 * @return array|false
	 */
	public function file( $path ) {
		return $this->files[ $path ] ?? false;
	}

	/**
	 * Authenticated directory membership.
	 *
	 * @param string $path Relative path or root.
	 * @return bool
	 */
	public function directory( $path ) {
		return isset( $this->directories[ $path ] );
	}

	/** Non-secret correlation only. @return array */
	public function summary() {
		return array(
			'root'            => adbmin_catalog()['core_tree'],
			'files'           => count( $this->files ),
			'directories'     => count( $this->directories ) - 1,
			'runtime_loading' => false,
		);
	}
}

require_once __DIR__ . '/source-policy-minimum-artifacts.php';
