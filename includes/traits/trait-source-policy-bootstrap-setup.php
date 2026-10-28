<?php
/**
 * Dormant finite private setup steps; no public executor or production caller.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.RestrictedFunctions -- Dormant private bounded bootstrap cannot use admission-dependent wpdb dispatch; this slice executes synthetic PHP -n APIs only.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Only the exact fixed connection's private initializer can use these methods. */
trait Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Setup {
	/**
	 * Consumes the next exact step before effects.
	 *
	 * @param string $step Internal literal.
	 * @param int    $credits Worst-case command effects.
	 * @return void
	 * @throws RuntimeException On an invalid step or exhausted budget.
	 */
	private function bootstrap_step( $step, $credits = 1 ) {
		$steps = array( 'identity', 'limits', 'mode', 'helpers', 'session', 'table', 'columns', 'indexes' );
		$this->bootstrap_check();
		if ( 'connected_unqualified' !== $this->native_state || ( $steps[ $this->bootstrap_position ] ?? null ) !== $step ) {
			throw new RuntimeException( 'Bootstrap step unavailable' );
		}
		++$this->bootstrap_position;
		$this->bootstrap_credits += $credits;
		$this->bootstrap_check();
	}

	/**
	 * Finite catalog only; never a caller SQL argument. No production invocation.
	 *
	 * @param string $step Internal literal name.
	 * @return array Bounded metadata DATA.
	 * @throws RuntimeException On unconfirmed command or metadata.
	 */
	private function bootstrap_probe( $step ) {
		$this->bootstrap_step( $step );
		list( $sql, $maximum, $table ) = Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Evidence::catalog( $step );
		if ( $table ) {
			$sql = $this->prepare( $sql, $this->bootstrap_frame['table'] );
		}
		$this->bootstrap_check();
		$result = null;
		try {
			// phpcs:ignore WordPress.DB.RestrictedFunctions.mysql_mysqli_query -- Dormant private finite catalog; this approval runs synthetic PHP -n APIs only.
			$result = mysqli_query( $this->bootstrap_handle, $sql );
			if ( 0 !== mysqli_errno( $this->bootstrap_handle ) || ( 0 === $maximum ? true !== $result : ! $result instanceof mysqli_result ) ) {
				throw new RuntimeException( 'Bootstrap command unavailable' );
			}
			$this->bootstrap_check();
			if ( 0 === $maximum ) {
				return array();
			}
			$count = mysqli_num_rows( $result );
			if ( ! is_int( $count ) || $count < 0 || $count > $maximum ) {
				throw new RuntimeException( 'Bootstrap result bound unavailable' );
			}
			$rows = array();
			for ( $index = 0; $index < $count; ++$index ) {
				$row = mysqli_fetch_assoc( $result );
				if ( ! is_array( $row ) || count( $row ) > 11 ) {
					throw new RuntimeException( 'Bootstrap result shape unavailable' );
				}
				foreach ( $row as $key => $value ) {
					if ( ! is_string( $key ) || strlen( $key ) > 64 || ( null !== $value && ( ! is_string( $value ) || strlen( $value ) > 1024 ) ) ) {
						throw new RuntimeException( 'Bootstrap scalar bound unavailable' );
					}
				}
				$rows[] = $row;
			}
			$this->bootstrap_check();
			return $rows;
		} finally {
			if ( $result instanceof mysqli_result ) {
				mysqli_free_result( $result );
			}
		}
	}

	/**
	 * Uses genuine inherited helpers under fixed private orchestration.
	 *
	 * @param array $modes Bounded observed mode tokens.
	 * @return void
	 */
	private function bootstrap_helpers( array $modes ) {
		$this->bootstrap_step( 'helpers', 5 );
		parent::init_charset();
		$this->bootstrap_helper_done();
		parent::set_charset( $this->bootstrap_handle );
		$this->bootstrap_helper_done();
		parent::set_sql_mode( $modes );
		$this->bootstrap_helper_done();
	}

	/**
	 * Checks native status before any following command can clear it.
	 *
	 * @return void
	 * @throws RuntimeException On a known helper error.
	 */
	private function bootstrap_helper_done() {
		if ( 0 !== mysqli_errno( $this->bootstrap_handle ) ) {
			throw new RuntimeException( 'Bootstrap helper unavailable' );
		}
		$this->bootstrap_check();
	}
}
