<?php
/**
 * Synthetic PE32+ header/import decoder, never a loader or DLL trust policy.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Maps a bounded RVA span into exactly one backed, nonoverlapping section.
 *
 * @param array $sections Validated VA/size/raw rows.
 * @param int   $rva Requested RVA.
 * @param int   $size Requested bytes.
 * @return int|false
 */
function adbmin_pe_offset( array $sections, $rva, $size ) {
	foreach ( $sections as $section ) {
		if ( $rva >= $section[0] && $rva - $section[0] <= $section[1] - $size ) {
			return $section[2] + $rva - $section[0];
		}
	}
	return false;
}

/**
 * Decodes structure only. Imports, NTS, signatures and runtime dependencies still
 * require separate artifact/runtime admission; matching headers cannot load code.
 *
 * @param mixed $bytes Entire synthetic image bytes, maximum16MiB.
 * @param mixed $role Fixed executable/module role.
 * @return array|false Non-authorizing observations.
 */
function adbmin_pe( $bytes, $role ) {
	if ( ! is_string( $bytes ) || strlen( $bytes ) < 64 || strlen( $bytes ) > 16777216 || 'MZ' !== substr( $bytes, 0, 2 )
		|| ! in_array( $role, array( 'php.exe', 'php7.dll', 'ext/php_mysqli.dll' ), true ) ) {
		return false;
	}
	$pe = unpack( 'Vvalue', substr( $bytes, 60, 4 ) )['value'];
	if ( $pe < 64 || $pe > 1048576 || $pe + 24 > strlen( $bytes ) || "PE\0\0" !== substr( $bytes, $pe, 4 ) ) {
		return false;
	}
	$coff     = unpack( 'vmachine/vsections/Vtime/Vsymbols/Vcount/voptional/vflags', substr( $bytes, $pe + 4, 20 ) );
	$optional = $pe + 24;
	$table    = $optional + $coff['optional'];
	if ( 0x8664 !== $coff['machine'] || $coff['sections'] < 1 || $coff['sections'] > 16 || $coff['optional'] < 240 || $coff['optional'] > 512
		|| $table + 40 * $coff['sections'] > strlen( $bytes ) || 0 === ( $coff['flags'] & 2 ) || ( 'php.exe' !== $role ) !== ( 0 !== ( $coff['flags'] & 0x2000 ) )
		|| 0x20b !== unpack( 'vvalue', substr( $bytes, $optional, 2 ) )['value'] || 16 !== unpack( 'Vvalue', substr( $bytes, $optional + 108, 4 ) )['value'] ) {
		return false;
	}
	$delay = unpack( 'Vrva/Vsize', substr( $bytes, $optional + 216, 8 ) );
	if ( 0 !== $delay['rva'] || 0 !== $delay['size'] ) {
		return false;
	}
	$sections = array();
	for ( $index = 0; $index < $coff['sections']; ++$index ) {
		$row = unpack( 'Vvirtual/Vva/Vsize/Vraw', substr( $bytes, $table + 40 * $index + 8, 16 ) );
		if ( $row['size'] < 1 || $row['raw'] < $table + 40 * $coff['sections'] || $row['raw'] > strlen( $bytes ) - $row['size']
			|| $row['va'] < 4096 || $row['va'] + $row['size'] > 0xffffffff ) {
			return false;
		}
		foreach ( $sections as $previous ) {
			if ( ( $row['va'] < $previous[0] + $previous[1] && $previous[0] < $row['va'] + $row['size'] )
				|| ( $row['raw'] < $previous[2] + $previous[1] && $previous[2] < $row['raw'] + $row['size'] ) ) {
				return false;
			}
		}
		$sections[] = array( $row['va'], $row['size'], $row['raw'] );
	}
	$imports = unpack( 'Vrva/Vsize', substr( $bytes, $optional + 120, 8 ) );
	if ( $imports['size'] < 40 || $imports['size'] > 1300 ) {
		return false;
	}
	$start = adbmin_pe_offset( $sections, $imports['rva'], $imports['size'] );
	if ( false === $start ) {
		return false;
	}
	$names = array();
	for ( $index = 0; $index < 65 && ( $index + 1 ) * 20 <= $imports['size']; ++$index ) {
		$descriptor = substr( $bytes, $start + $index * 20, 20 );
		if ( str_repeat( "\0", 20 ) === $descriptor ) {
			return count( $names ) > 0 ? array(
				'role'            => $role,
				'machine'         => 'x64',
				'format'          => 'PE32+',
				'imports'         => array_keys( $names ),
				'load_authorized' => false,
			) : false;
		}
		if ( $index >= 64 ) {
			return false;
		}
		$name_rva = unpack( 'Vvalue', substr( $descriptor, 12, 4 ) )['value'];
		$name     = '';
		for ( $char = 0; $char < 81; ++$char ) {
			$offset = adbmin_pe_offset( $sections, $name_rva + $char, 1 );
			if ( false === $offset ) {
				return false;
			}
			if ( "\0" === $bytes[ $offset ] ) {
				break;
			}
			$name .= $bytes[ $offset ];
		}
		$name = strtolower( $name );
		if ( $char >= 81 || ! preg_match( '/\A[a-z0-9][a-z0-9._-]{0,74}\.dll\z/D', $name ) || isset( $names[ $name ] ) ) {
			return false;
		}
		$names[ $name ] = true;
	}
	return false;
}
