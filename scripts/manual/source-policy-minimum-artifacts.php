<?php
/**
 * Minimum candidate and synthetic inventory DATA only: no IO or extraction grant.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Fixed reviewed identities, not caller-supplied provenance. @return array */
function adbmin_catalog() {
	return array(
		'php_url'     => 'https://downloads.php.net/~windows/releases/php-7.4.33-nts-Win32-vc15-x64.zip',
		'php_sha256'  => '14ae3250d4447c8ccfc4c45a70d90adfbcd61e728d85f0be56a7ddf8f9c8aace',
		'core_url'    => 'https://codeload.github.com/WordPress/WordPress/zip/cc101b64012b16d087780657a2b828ccd7794a63',
		'core_commit' => 'cc101b64012b16d087780657a2b828ccd7794a63',
		'core_tree'   => 'a3bd7a4074a4c95c8af3b969346726273d68e167',
		'execution'   => false,
	);
}

/**
 * Strict portable relative archive path. Never touches the filesystem.
 *
 * @param mixed $path Untrusted relative name.
 * @return bool
 */
function adbmin_path( $path ) {
	if ( ! is_string( $path ) || '' === $path || strlen( $path ) > 240 || preg_match( '/[^\x20-\x7e]|[\\\\:<>"|?*]/', $path ) ) {
		return false;
	}
	$parts = explode( '/', $path );
	if ( count( $parts ) > 16 ) {
		return false;
	}
	foreach ( $parts as $part ) {
		if ( '' === $part || '.' === $part || '..' === $part || preg_match( '/[. ]\z|\A(?:con|prn|aux|nul|conin\$|conout\$|clock\$|com[1-9]|lpt[1-9])[ .]*(?:\.|\z)/i', $part ) ) {
			return false;
		}
	}
	return true;
}

/**
 * Derives hypothetical owned paths; not ownership/reparse-point evidence.
 *
 * @param mixed $temp_parent Resolved temporary parent DATA, no user path authority.
 * @param mixed $run Controller-generated run identifier DATA.
 * @return array|false
 */
function adbmin_layout( $temp_parent, $run ) {
	if ( ! is_string( $temp_parent ) || ! preg_match( '/\A[A-Z]:\/(.+)\z/D', $temp_parent, $match ) || ! adbmin_path( $match[1] )
		|| ! is_string( $run ) || ! preg_match( '/\A[a-f0-9]{32}\z/D', $run ) ) {
		return false;
	}
	$root = $temp_parent . '/alynt-minimum-' . $run;
	return strlen( $root ) <= 160 ? array(
		'root'                => $root,
		'runtime'             => $root . '/runtime',
		'source'              => $root . '/source',
		'filesystem_verified' => false,
	) : false;
}

/**
 * Bounded in-memory digest comparison only; caller expectation is not provenance.
 *
 * @param mixed $bytes Synthetic bytes or, in a future approved adapter, observed bytes.
 * @param mixed $expected Expected lowercase digest.
 * @return bool
 */
function adbmin_digest( $bytes, $expected ) {
	return is_string( $bytes ) && strlen( $bytes ) <= 67108864 && is_string( $expected ) && 1 === preg_match( '/\A[a-f0-9]{64}\z/D', $expected ) && hash_equals( $expected, hash( 'sha256', $bytes ) );
}

/**
 * Fixed publisher checksum check; never downloads or loads bytes.
 *
 * @param mixed $bytes Input bytes.
 * @return bool
 */
function adbmin_php_zip( $bytes ) {
	return adbmin_digest( $bytes, adbmin_catalog()['php_sha256'] );
}

/**
 * Complete synthetic entry inventory, not a ZIP decoder or extraction permit.
 * Metadata cannot prove absence of links, existing paths or lying ZIP headers.
 *
 * @param mixed $kind Fixed php/core candidate.
 * @param array $entries Typed metadata with path/kind/size/compressed only.
 * @param mixed $compressed Observed archive byte count DATA.
 * @param mixed $root_length Derived output-root length DATA.
 * @return array|false
 */
function adbmin_inventory( $kind, array $entries, $compressed, $root_length ) {
	if ( ! in_array( $kind, array( 'php', 'core' ), true ) || ! is_int( $compressed ) || $compressed < 1 || $compressed > 67108864
		|| ! is_int( $root_length ) || $root_length < 1 || $root_length > 160 || count( $entries ) < 1 || count( $entries ) > 8192 ) {
		return false;
	}
	$paths   = array();
	$names   = array();
	$total   = 0;
	$packed  = 0;
	$wrapper = 'WordPress-' . adbmin_catalog()['core_commit'];
	foreach ( $entries as $entry ) {
		if ( ! is_array( $entry ) || array_keys( $entry ) !== array( 'path', 'kind', 'size', 'compressed' ) || ! adbmin_path( $entry['path'] )
			|| ! in_array( $entry['kind'], array( 'file', 'directory' ), true ) || ! is_int( $entry['size'] ) || $entry['size'] < 0 || $entry['size'] > 268435456
			|| ! is_int( $entry['compressed'] ) || $entry['compressed'] < 0 || $entry['compressed'] > $compressed
			|| ( 'directory' === $entry['kind'] && ( 0 !== $entry['size'] || 0 !== $entry['compressed'] ) ) ) {
			return false;
		}
		$path = $entry['path'];
		if ( 'core' === $kind ) {
			if ( $wrapper === $path && 'directory' === $entry['kind'] ) {
				$path = '';
			} elseif ( 0 === strpos( $path, $wrapper . '/' ) ) {
				$path = substr( $path, strlen( $wrapper ) + 1 );
			} else {
				return false;
			}
		}
		$key = strtolower( $path );
		if ( isset( $paths[ $key ] ) || $root_length + 1 + strlen( $path ) > 240 ) {
			return false;
		}
		$paths[ $key ] = $entry['kind'];
		$parts         = '' === $path ? array() : explode( '/', $path );
		$prefix        = '';
		foreach ( $parts as $part ) {
			$prefix = '' === $prefix ? $part : $prefix . '/' . $part;
			$folded = strtolower( $prefix );
			if ( isset( $names[ $folded ] ) && $names[ $folded ] !== $prefix ) {
				return false;
			}
			$names[ $folded ] = $prefix;
		}
		$total  += $entry['size'];
		$packed += $entry['compressed'];
		if ( $total > ( 'php' === $kind ? 268435456 : 134217728 ) || $packed > $compressed ) {
			return false;
		}
	}
	foreach ( $paths as $path => $type ) {
		$parent = $path;
		while ( false !== strpos( $parent, '/' ) ) {
			$parent = substr( $parent, 0, strrpos( $parent, '/' ) );
			if ( isset( $paths[ $parent ] ) && 'file' === $paths[ $parent ] ) {
				return false;
			}
		}
	}
	return array(
		'entries'    => count( $entries ),
		'expanded'   => $total,
		'extraction' => false,
	);
}
