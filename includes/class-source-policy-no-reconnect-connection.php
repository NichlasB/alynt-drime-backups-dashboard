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
	/** Closed private query context; never returned to callers.
	 *
	 * @var object|null
	 */
	private $query_context = null;
	/** One native cleanup attempt only.
	 *
	 * @var bool
	 */
	private $close_attempted = false;
	/** Confirmed independent driver/callback cleanup.
	 *
	 * @var bool
	 */
	private $closed = false;
	/** Known private lifecycle, not inferred from a missing handle after failure.
	 *
	 * @var string
	 */
	private $native_state = 'never_opened';

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
	final public function query( $query ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared -- Fixed dormant owner; reconnect and raw error output are refused above.
		return is_wp_error( $this->admit_profile() ) ? false : parent::query( $query );
	}

	/** Admits only this privately bound single-use receiver.
	 *
	 * @return array|WP_Error
	 */
	final public function admit_profile() {
		if ( $this->close_attempted || 'active' !== $this->native_state || true !== $this->ready || ! $this->query_context instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context || ! $this->query_context->owns( $this ) ) {
			return new WP_Error( 'source_policy_unavailable' );
		}
		return Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::admit( $this->qualification, $this->options, $this->query_context );
	}

	/**
	 * Guards both sides of genuine preparation; never reseals altered metadata.
	 *
	 * @param string $query Fixed owner SQL template.
	 * @param mixed  ...$args Prepared values only.
	 * @return string
	 * @throws RuntimeException If admission changes.
	 */
	final public function prepare_checked( $query, ...$args ) {
		if ( is_wp_error( $this->admit_profile() ) ) {
			throw new RuntimeException( 'Policy preparation unavailable' );
		}
		$prepared = $this->prepare( $query, ...$args );
		if ( ! is_string( $prepared ) || is_wp_error( $this->admit_profile() ) ) {
			throw new RuntimeException( 'Policy preparation changed' );
		}
		return $prepared;
	}

	/** Attempts owned driver and callback cleanup independently, once.
	 *
	 * @return bool
	 */
	final public function close() {
		if ( $this->close_attempted ) {
			return $this->closed;
		}
		$this->close_attempted = true;
		$native                = false;
		try {
			$native = ( $GLOBALS['wpdb'] ?? null ) === $this ? false : ( 'never_opened' === $this->native_state && null === $this->dbh && ! $this->ready );
			if ( ( $GLOBALS['wpdb'] ?? null ) !== $this && 'active' === $this->native_state && null !== $this->dbh ) {
				$native = true === parent::close();
			}
		} catch ( Throwable $error ) {
			$native = false; // Unknown native closure is not converted to success or retried.
		} finally {
			$this->native_state = $native ? 'closed' : 'closure_unknown';
			if ( ( $GLOBALS['wpdb'] ?? null ) !== $this ) {
				$this->ready = false;
			}
		}
		try {
			$callbacks = $this->query_context instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context && $this->query_context->owns( $this )
				? $this->query_context->finish() : false === has_filter( 'query', array( $this, 'remove_placeholder_escape' ) );
		} catch ( Throwable $error ) {
			$callbacks = false; // Do not delete foreign registrations to claim cleanup.
		}
		$this->closed = $native && $callbacks;
		return $this->closed;
	}

	/**
	 * Makes exactly one inherited query call; no caller-selected executor callback.
	 *
	 * @param string $sql Fixed owner-prepared statement.
	 * @return array Internal acknowledgement DATA.
	 * @throws RuntimeException If dispatch admission changed.
	 */
	final public function execute( $sql ) {
		if ( is_wp_error( $this->admit_profile() ) ) {
			throw new RuntimeException( 'Policy dispatch unavailable' );
		}
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
