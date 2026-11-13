<?php
/**
 * Private fixed-table HTTP effect accounting; never an application SQL executor.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Bounded transient SQL skeleton; no literal/SQL is retained in evidence.
 *
 * @param mixed $sql Genuine core statement.
 * @return array|null
 */
function adbh_owned_tokens( $sql ) {
	if ( ! is_string( $sql ) || strlen( $sql ) > 65536 || false !== strpos( $sql, "\0" ) ) {
		return null;
	}
	$shape  = '';
	$values = array();
	$length = strlen( $sql );
	for ( $i = 0; $i < $length; ++$i ) {
		if ( "'" !== $sql[ $i ] ) {
			$shape .= $sql[ $i ];
			continue; }
		$value  = '';
		$closed = false;
		while ( ++$i < $length ) {
			if ( "'" === $sql[ $i ] ) {
				$closed = true;
				break; }
			if ( '\\' === $sql[ $i ] && ++$i >= $length ) {
				return null; }
			$value .= $sql[ $i ];
		}
		if ( ! $closed || count( $values ) >= 2048 ) {
			return null; }
		$values[] = $value;
		$shape   .= '?';
	}
	$shape = rtrim( preg_replace( '/\s+/', ' ', trim( str_replace( '`', '', $shape ) ) ), ';' );
	if ( preg_match( '/[;\x00-\x08\x0b\x0c\x0e-\x1f]|--|\/\*|#|\b(?:OUTFILE|DUMPFILE|LOAD_FILE|SLEEP|BENCHMARK|UNION|GRANT|REVOKE|CALL)\b/i', $shape ) ) {
		return null; }
	return array( $shape, $values );
}

/** Finite table/verb scope; only explicit setup may create owned core fixtures.
 *
 * @param mixed  $sql Stock statement.
 * @param string $prefix Owned prefix.
 * @param bool   $setup Setup stage.
 * @param bool   $login Exact login POST stage.
 * @return string|null
 */
function adbh_owned_sql_class( $sql, $prefix, $setup, $login ) {
	if ( ! is_string( $prefix ) || 1 !== preg_match( '/\Aadbh_[a-f0-9]{12}_\z/', $prefix ) ) {
		return null; }
	$tokens = adbh_owned_tokens( $sql );
	if ( null === $tokens ) {
		return null; }
	list( $shape, $values ) = $tokens;
	if ( in_array( $shape, array( 'SELECT @@SESSION.sql_mode', 'SELECT VERSION()', 'SELECT FOUND_ROWS()' ), true ) ) {
		return 'bootstrap-read'; }
	if ( 'SHOW TABLES' === $shape || 'SHOW TABLES LIKE ?' === $shape ) {
		return $setup && ( ! $values || ( 1 === count( $values ) && 0 === strpos( $values[0], $prefix ) ) ) ? 'bootstrap-read' : null; }
	if ( ! preg_match( '/\A(?:SELECT\b|SHOW (?:FULL )?(?:COLUMNS|INDEX|INDEXES) FROM\b|DESCRIBE\b|CREATE TABLE\b|ALTER TABLE\b|INSERT INTO\b|UPDATE\b|DELETE FROM\b)/i', $shape ) ) {
		return null; }
	$tables = array();
	foreach ( array( 'commentmeta', 'comments', 'links', 'options', 'postmeta', 'posts', 'term_relationships', 'term_taxonomy', 'termmeta', 'terms', 'usermeta', 'users', 'alynt_drime_dashboard_sites' ) as $name ) {
		$tables[] = $prefix . $name; }
	preg_match_all( '/\b(?:FROM|JOIN|INTO)\s+([A-Za-z0-9_.]+)/i', $shape, $matches );
	if ( preg_match( '/\A(?:UPDATE|(?:CREATE|ALTER) TABLE(?: IF NOT EXISTS)?|DESCRIBE)\s+([A-Za-z0-9_.]+)/i', $shape, $first ) ) {
		$matches[1][] = $first[1]; }
	if ( ! $matches[1] || preg_match( '/\bREFERENCES\b/i', $shape ) ) {
		return null; }
	foreach ( $matches[1] as $table ) {
		if ( ! in_array( $table, $tables, true ) ) {
			return null; }
	}
	$write = 1 === preg_match( '/\A(?:CREATE|ALTER|INSERT|UPDATE|DELETE)\b/i', $shape );
	$auth  = in_array( $prefix . 'users', $matches[1], true ) || in_array( $prefix . 'usermeta', $matches[1], true );
	if ( ! $write ) {
		return $auth ? 'auth-read' : 'bootstrap-read'; }
	if ( $setup ) {
		return 'setup-write'; }
	if ( $login && array( $prefix . 'usermeta' ) === $matches[1] ) {
		$p = preg_quote( $prefix, '/' );
		if ( preg_match( '/\AINSERT INTO ' . $p . 'usermeta \(user_id, meta_key, meta_value\) VALUES \(([1-3]|\?), \?, \?\)\z/', $shape, $m ) ) {
			$offset = '?' === $m[1] ? 1 : 0;
			if ( count( $values ) === 2 + $offset && ( ! $offset || in_array( $values[0], array( '1', '2', '3' ), true ) ) && 'session_tokens' === $values[ $offset ] ) {
				return 'session-write'; }
		}
		if ( preg_match( '/\AUPDATE ' . $p . 'usermeta SET meta_value = \? WHERE user_id = ([1-3]|\?) AND meta_key = \?\z/', $shape, $m ) ) {
			if ( 'session_tokens' === end( $values ) && ( ( '?' !== $m[1] && 2 === count( $values ) ) || ( '?' === $m[1] && 3 === count( $values ) && in_array( $values[1], array( '1', '2', '3' ), true ) ) ) ) {
				return 'session-write'; }
		}
	}
	return null;
}

/** Same-byte query observer; it never returns a replacement statement.
 *
 * @param string $sql Stock statement.
 * @return string
 */
function adbh_owned_observe( $sql ) {
	$setup = 'setup' === ADB_HTTP_WORKER;
	$login = $GLOBALS['adbh_route'] ?? '';
	$class = adbh_owned_sql_class( $sql, $GLOBALS['adbh_private']['prefix'], $setup, 'login-submit' === $login );
	adbh_check( null !== $class, 'sql-shape' );
	$phase = $setup ? 'setup' : ( 0 === strpos( $class, 'auth-' ) || 'session-write' === $class ? 'auth' : 'bootstrap' );
	++$GLOBALS['adbh_ledger'][ $phase ];
	adbh_check( $GLOBALS['adbh_ledger'][ $phase ] <= ( $setup ? 1024 : 256 ), 'sql-budget' );
	adbh_check( $setup || false === strpos( $sql, 'alynt_drime_backups_dashboard_source_policies' ), 'pre-entry-policy-read' );
	return $sql;
}
