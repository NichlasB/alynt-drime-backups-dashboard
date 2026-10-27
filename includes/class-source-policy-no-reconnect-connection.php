<?php
/**
 * Dormant, construction-closed policy connection with no reconnect or raw logging.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'wpdb', false ) ) {
	return;
}

/** Native construction remains closed pending separately approved qualification. */
class Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection extends wpdb {
	/**
	 * Private qualification DATA, never accepted by an application command.
	 *
	 * @var array|null
	 */
	private $qualification = null;

	/** No parent constructor, credentials, socket or native session is opened. */
	protected function __construct() {
		$this->ready = false;
	}

	/**
	 * Refuses production provisioning until a genuine fixed provider is qualified.
	 *
	 * @return WP_Error
	 */
	public static function create() {
		return new WP_Error( 'source_policy_unavailable' );
	}

	/**
	 * Prevents initial/repeated native connection attempts, including core retries.
	 *
	 * @param bool $allow_bail Unused core-compatible parameter.
	 * @return false
	 */
	final public function db_connect( $allow_bail = true ) {
		unset( $allow_bail );
		return false;
	}

	/**
	 * Stops inherited query() before a reconnect/reissue or bail/sleep path.
	 *
	 * @param bool $allow_bail Unused core-compatible parameter.
	 * @return false
	 */
	final public function check_connection( $allow_bail = true ) {
		unset( $allow_bail );
		return false;
	}

	/**
	 * Never prints/logs the native SQL or raw error; callers get fixed codes only.
	 *
	 * @param string $str Unused raw error.
	 * @return false
	 */
	final public function print_error( $str = '' ) {
		unset( $str );
		return false;
	}

	/**
	 * Reads closed-by-default profile DATA, not a native authorization receipt.
	 *
	 * @return array|null
	 */
	final public function profile() {
		return $this->qualification;
	}

	/**
	 * Retains core query semantics without allowing a subclass executor override.
	 *
	 * @param string $query Fixed prepared statement.
	 * @return int|bool
	 */
	final public function query( $query ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod -- final prevents subclass executor replacement; core behavior is not copied.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared -- Fixed dormant owner; reconnect and raw error output are refused above.
		return parent::query( $query );
	}

	/**
	 * Makes exactly one inherited query call; no caller-selected executor callback.
	 *
	 * @param string $sql Fixed owner-prepared statement.
	 * @return array Internal acknowledgement DATA.
	 */
	final public function execute( $sql ) {
		try {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared -- Fixed dormant owner prepares the statement; no native constructor/caller is connected.
			$affected = parent::query( $sql );
			$errno    = false === $affected ? $this->driver_error_number() : 0;
			if ( false === $affected && ! in_array( $errno, array( 1062, 1064, 1205, 1213 ), true ) ) {
				$this->ready = false;
			}
			return array(
				'affected' => $affected,
				'errno'    => $errno,
			);
		} catch ( Throwable $error ) {
			$this->ready = false;
			return array(
				'affected' => false,
				'errno'    => null,
			);
		}
	}

	/**
	 * Fixed genuine driver observation; isolated subclasses override this seam only.
	 *
	 * @return int|null Unknown if no usable native connection remains.
	 */
	protected function driver_error_number() {
		// phpcs:ignore WordPress.DB.RestrictedFunctions.mysql_mysqli_errno -- Error-code read only, never infer failure class from raw SQL/error text.
		return $this->dbh instanceof mysqli ? mysqli_errno( $this->dbh ) : null;
	}
}
