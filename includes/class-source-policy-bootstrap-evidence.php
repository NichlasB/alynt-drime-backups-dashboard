<?php
/**
 * Finite setup query catalog and bounded metadata/readback DATA validation.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** No IO or activation; callers cannot obtain a connection from these DATA. */
final class Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Evidence {
	/**
	 * Fixed private-step catalog. No arbitrary SQL parameter or identifier input.
	 *
	 * @param string $step Internal step name.
	 * @return array Template, maximum rows and whether table binding is required.
	 * @throws RuntimeException For an unknown step.
	 */
	public static function catalog( $step ) {
		switch ( $step ) {
			case 'identity':
				return array( 'SELECT DATABASE() AS db,@@autocommit AS ac,@@in_transaction AS tx', 1, false );
			case 'limits':
				return array( 'SET SESSION max_statement_time=3, innodb_lock_wait_timeout=1, lock_wait_timeout=1', 0, false );
			case 'mode':
				return array( 'SELECT @@SESSION.sql_mode AS mode', 1, false );
			case 'session':
				return array( 'SELECT DATABASE() AS db,@@autocommit AS ac,@@in_transaction AS tx,@@max_statement_time AS statement_limit,@@innodb_lock_wait_timeout AS row_limit,@@lock_wait_timeout AS metadata_limit,@@character_set_client AS client_charset,@@character_set_connection AS connection_charset,@@character_set_results AS result_charset,@@collation_connection AS collation,@@SESSION.sql_mode AS mode', 1, false );
			case 'table':
				return array( 'SELECT ENGINE AS engine,TABLE_COLLATION AS collation FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=%s LIMIT 2', 2, true );
			case 'columns':
				return array( 'SELECT COLUMN_NAME AS name,COLUMN_TYPE AS type,IS_NULLABLE AS nullable,COLUMN_KEY AS key_kind,EXTRA AS extra FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=%s ORDER BY ORDINAL_POSITION LIMIT 5', 5, true );
			case 'indexes':
				return array( 'SELECT INDEX_NAME AS name,NON_UNIQUE AS non_unique,SEQ_IN_INDEX AS sequence,COLUMN_NAME AS column_name,SUB_PART AS prefix_length FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=%s ORDER BY INDEX_NAME,SEQ_IN_INDEX LIMIT 4', 4, true );
		}
		throw new RuntimeException( 'Unknown bootstrap step' );
	}

	/**
	 * SQL-mode tokens are bounded identifiers before the genuine helper sees them.
	 *
	 * @param mixed $value Raw observed mode string.
	 * @return array|false
	 */
	public static function modes( $value ) {
		if ( ! is_string( $value ) || strlen( $value ) > 1024 || ( '' !== $value && ! preg_match( '/\A[A-Z0-9_]+(?:,[A-Z0-9_]+)*\z/', $value ) ) ) {
			return false;
		}
		$tokens = '' === $value ? array() : explode( ',', $value );
		return count( $tokens ) <= 32 && count( array_unique( $tokens ) ) === count( $tokens ) ? $tokens : false;
	}

	/**
	 * Validates observed own-session state, never settings requested by a caller.
	 *
	 * @param array       $rows Bounded result DATA.
	 * @param array       $frame Private frozen settings.
	 * @param string      $charset Genuine helper-computed charset.
	 * @param string      $collation Genuine helper-computed collation.
	 * @param array       $modes Expected core-processed mode tokens.
	 * @param object|null $receiver Exact private lab receiver, never a collation descriptor.
	 * @return bool
	 */
	public static function session( array $rows, array $frame, $charset, $collation, array $modes, $receiver = null ) {
		$expected = self::session_collation( $frame, $receiver );
		if ( false === $expected || 1 !== count( $rows ) || ! isset( $rows[0] ) || ! is_array( $rows[0] ) || 11 !== count( $rows[0] ) ) {
			return false;
		}
		$row    = $rows[0];
		$actual = self::modes( $row['mode'] ?? null );
		sort( $modes );
		if ( false !== $actual ) {
			sort( $actual );
		}
		return ( $row['db'] ?? null ) === $frame['database'] && '1' === ( $row['ac'] ?? null ) && '0' === ( $row['tx'] ?? null )
			&& in_array( $row['statement_limit'] ?? null, array( '3', '3.000000' ), true ) && '1' === ( $row['row_limit'] ?? null ) && '1' === ( $row['metadata_limit'] ?? null )
			&& 'utf8mb4' === $charset && $expected === $collation
			&& ( $row['client_charset'] ?? null ) === $charset && ( $row['connection_charset'] ?? null ) === $charset && ( $row['result_charset'] ?? null ) === $charset
			&& ( $row['collation'] ?? null ) === $collation && $actual === $modes;
	}

	/**
	 * Selects only the default or the already bound private lab expectation.
	 *
	 * @param array       $frame Fixed private frame, never a profile selector from a request.
	 * @param object|null $receiver Exact optional lab receiver.
	 * @return string|false Code-owned collation or unavailable.
	 */
	private static function session_collation( array $frame, $receiver ) {
		if ( null === $receiver ) {
			return 'utf8mb4_unicode_520_ci';
		}
		if ( ! is_object( $receiver ) || 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection' !== get_class( $receiver )
			|| realpath( __DIR__ . '/class-source-policy-no-reconnect-connection.php' ) !== ( new ReflectionClass( $receiver ) )->getFileName() ) {
			return false;
		}
		$expected = $receiver->minimum_lab_profile();
		if ( ! is_array( $expected ) || ! in_array( $expected['collation'] ?? null, array( 'utf8mb4_unicode_ci', 'utf8mb4_unicode_520_ci' ), true ) ) {
			return false;
		}
		foreach ( array( 'core', 'database', 'table' ) as $key ) {
			if ( ( $frame[ $key ] ?? null ) !== $expected[ $key ] ) {
				return false;
			}
		}
		return $expected['collation'];
	}

	/**
	 * Exact supported options schema; no DDL or repair path.
	 *
	 * @param array $table Bounded table metadata.
	 * @param array $columns Bounded column metadata.
	 * @param array $indexes Bounded index metadata.
	 * @return bool
	 */
	public static function schema( array $table, array $columns, array $indexes ) {
		if ( array(
			array(
				'engine'    => 'InnoDB',
				'collation' => 'utf8mb4_unicode_520_ci',
			),
		) !== $table || 4 !== count( $columns ) || 3 !== count( $indexes ) ) {
			return false;
		}
		$expected = array(
			array( 'option_id', 'bigint(20) unsigned', 'NO', 'PRI', 'auto_increment' ),
			array( 'option_name', 'varchar(191)', 'NO', 'UNI', '' ),
			array( 'option_value', 'longtext', 'NO', '', '' ),
			array( 'autoload', 'varchar(20)', 'NO', 'MUL', '' ),
		);
		foreach ( $columns as $index => $column ) {
			if ( array_keys( $column ) !== array( 'name', 'type', 'nullable', 'key_kind', 'extra' ) || array_values( $column ) !== $expected[ $index ] ) {
				return false;
			}
		}
		$found = array();
		foreach ( $indexes as $index ) {
			if ( array_keys( $index ) !== array( 'name', 'non_unique', 'sequence', 'column_name', 'prefix_length' ) || '1' !== $index['sequence'] || null !== $index['prefix_length'] ) {
				return false;
			}
			$found[ $index['name'] ] = array( $index['non_unique'], $index['column_name'] );
		}
		ksort( $found );
		return array(
			'PRIMARY'     => array( '0', 'option_id' ),
			'autoload'    => array( '1', 'autoload' ),
			'option_name' => array( '0', 'option_name' ),
		) === $found;
	}
}
