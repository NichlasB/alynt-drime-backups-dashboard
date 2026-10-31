<?php
/**
 * Dormant private first-connect orchestration; factories remain unavailable.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.RestrictedFunctions -- Dormant private owned initialization/cleanup, not public SQL or production wiring; isolated PHP -n API doubles only in this scope.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** No production caller/provider. Isolated tests bind synthetic APIs under PHP -n. */
trait Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Bootstrap {
	/** Trusted settings composition remains absent.
	 *
	 * @var array|null
	 */
	private $bootstrap_frame = null;
	/** Expected executable/observer bindings, absent by default.
	 *
	 * @var array|null
	 */
	private $bootstrap_binding = null;
	/** Consumed before object-only initialization.
	 *
	 * @var bool
	 */
	private $bootstrap_attempted = false;
	/** Exact internally allocated handle, never imported.
	 *
	 * @var object|null
	 */
	private $bootstrap_handle = null;
	/** Monotonic start in nanoseconds.
	 *
	 * @var int
	 */
	private $bootstrap_started = 0;
	/** Fixed native-command effect credits.
	 *
	 * @var int
	 */
	private $bootstrap_credits = 0;
	/** Next private step; consumed before effects.
	 *
	 * @var int
	 */
	private $bootstrap_position = 0;

	/**
	 * Inaccessible from factories; no actual provider or production wiring exists.
	 *
	 * @return true|WP_Error Never a handle or public native receipt.
	 * @throws RuntimeException Scoped diagnostics are mapped to unavailable internally.
	 */
	private function bootstrap() {
		if ( $this->bootstrap_attempted || $this->close_attempted || 'never_opened' !== $this->native_state || null !== $this->dbh ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		}
		$this->bootstrap_attempted = true;
		$this->bootstrap_started   = hrtime( true );
		$success                   = false;
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Scoped warning-to-refusal conversion, never suppress-and-continue; exact prior handler restored.
		set_error_handler(
			static function () {
				throw new RuntimeException( 'Bootstrap native diagnostic' );
			}
		);
		try {
			$this->bootstrap_check( false ); // Null default frame refuses before any native primitive.
			$frame               = $this->bootstrap_frame;
			$this->report_source = $this->bootstrap_binding['driver_file'];
			$this->report_mode   = $this->observed_report_mode();
			if ( ! in_array( $this->report_mode, array( 0, 3 ), true ) || ( 'unix_socket' === $frame['transport'] && 0 !== $this->report_mode )
				|| array( $frame['host'], $frame['port'], $frame['socket'], '::1' === $frame['host'] ) !== $this->parse_db_host( $frame['db_host'] ) ) {
				throw new RuntimeException( 'Transport or reporting context unavailable' );
			}
			parent::__construct( $frame['user'], $frame['password'], $frame['database'], $frame['db_host'] );
			if ( true !== $this->use_mysqli || false !== $this->ready || null !== $this->dbh || 0 !== $this->num_queries ) {
				throw new RuntimeException( 'Object-only initialization unavailable' );
			}
			if ( ! $this->query_context->begin() ) {
				throw new RuntimeException( 'Bootstrap callback context unavailable' );
			}
			$this->bootstrap_check();
			$this->native_state     = 'allocating';
			$this->bootstrap_handle = mysqli_init();
			$this->dbh              = $this->bootstrap_handle;
			if ( ! $this->dbh instanceof mysqli || ( isset( $GLOBALS['wpdb'] ) && $GLOBALS['wpdb']->dbh === $this->dbh ) ) {
				throw new RuntimeException( 'Owned allocation unavailable' );
			}
			$this->native_state = 'allocated';
			foreach ( array(
				MYSQLI_OPT_CONNECT_TIMEOUT => 2,
				MYSQLI_OPT_READ_TIMEOUT    => 3,
				MYSQLI_OPT_LOCAL_INFILE    => 0,
			) as $option => $value ) {
				$this->bootstrap_check();
				if ( true !== mysqli_options( $this->dbh, $option, $value ) ) {
					throw new RuntimeException( 'Bounded driver option unavailable' );
				}
			}
			$this->bootstrap_check();
			$this->native_state = 'connecting';
			$host               = '::1' === $frame['host'] ? '[::1]' : $frame['host']; // mysqlnd's explicit IPv6 representation; never a socket conversion.
			if ( true !== mysqli_real_connect( $this->dbh, $host, $frame['user'], $frame['password'], $frame['database'], $frame['port'], $frame['socket'], 0 ) || 0 !== mysqli_errno( $this->dbh ) ) {
				throw new RuntimeException( 'Initial connection unavailable' );
			}
			$this->native_state = 'connected_unqualified';
			$this->is_mysql     = true;
			$this->bootstrap_check();
			if ( $this->bootstrap_expected_server( $frame['core'] ) !== mysqli_get_server_info( $this->dbh ) ) {
				throw new RuntimeException( 'Server profile unavailable' );
			}
			$identity = $this->bootstrap_probe( 'identity' );
			if ( array(
				array(
					'db' => $frame['database'],
					'ac' => '1',
					'tx' => '0',
				),
			) !== $identity ) {
				throw new RuntimeException( 'Selected session identity unavailable' );
			}
			$this->bootstrap_probe( 'limits' );
			$mode  = $this->bootstrap_probe( 'mode' );
			$modes = 1 === count( $mode ) ? Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Evidence::modes( $mode[0]['mode'] ?? null ) : false;
			if ( false === $modes ) {
				throw new RuntimeException( 'SQL mode profile unavailable' );
			}
			$this->bootstrap_helpers( $modes );
			$session = $this->bootstrap_probe( 'session' );
			$table   = $this->bootstrap_probe( 'table' );
			$columns = $this->bootstrap_probe( 'columns' );
			$indexes = $this->bootstrap_probe( 'indexes' );
			$this->bootstrap_check();
			if ( ! Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Evidence::session( $session, $frame, $this->charset, $this->collate, array_values( array_diff( $modes, $this->incompatible_modes ) ) )
				|| ! Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Evidence::schema( $table, $columns, $indexes ) ) {
				throw new RuntimeException( 'Observed bootstrap evidence unavailable' );
			}
			$facts = array(
				'core'            => $frame['core'],
				'server'          => mysqli_get_server_info( $this->dbh ),
				'database'        => $session[0]['db'],
				'table'           => $frame['table'],
				'schema'          => 'options4-unique-name-InnoDB',
				'autocommit'      => true,
				'single_site'     => true,
				'dropins'         => false,
				'cache'           => 'core-process',
				'bounded_session' => true,
				'hooks'           => Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::fingerprint(),
			);
			$this->bootstrap_check();
			if ( Alynt_Drime_Backups_Dashboard_Source_Policy_Error::is_error( $this->admit_bound_profile( $facts, $frame['table'] ) ) ) {
				throw new RuntimeException( 'Final profile unavailable' );
			}
			$this->options       = $frame['table'];
			$this->qualification = $facts;
			$this->native_state  = 'active';
			$this->ready         = true;
			$success             = true;
			return true;
		} catch ( Throwable $error ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		} finally {
			$this->bootstrap_frame   = null;
			$this->bootstrap_binding = null;
			$this->dbpassword        = '';
			try {
				if ( ! $success ) {
					$this->close();
				}
			} finally {
				restore_error_handler();
			}
		}
	}

	/**
	 * Immutable admission, never a fingerprint refresh or default grant.
	 *
	 * @param bool $sealed Whether registration must already be sealed.
	 * @return void
	 * @throws RuntimeException When unavailable.
	 */
	private function bootstrap_check( $sealed = true ) {
		if ( $this->close_attempted || $this->ready || ( hrtime( true ) - $this->bootstrap_started ) > 20000000000 || $this->bootstrap_credits > 16
			|| ! Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::settings( $this->bootstrap_frame )
			|| ! Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::source( $this, $this->bootstrap_binding )
			|| ( null !== $this->minimum_lab_scope && ! is_array( $this->minimum_lab_profile() ) )
			|| ( null !== $this->report_mode && $this->observed_report_mode() !== $this->report_mode )
			|| ! $this->query_context instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context || ! $this->query_context->owns( $this )
			|| ( $sealed && ! $this->query_context->valid() ) || ( null !== $this->bootstrap_handle && $this->dbh !== $this->bootstrap_handle ) ) {
			throw new RuntimeException( 'Bootstrap admission unavailable' );
		}
	}

	/**
	 * Exact owned partial/active release, never global or borrowed cleanup.
	 *
	 * @return bool Confirmed release only.
	 */
	private function bootstrap_release() {
		$handle = $this->bootstrap_handle;
		if ( ! $handle instanceof mysqli || ( isset( $GLOBALS['wpdb'] ) && $GLOBALS['wpdb']->dbh === $handle ) ) {
			return false;
		}
		$matched = $this->dbh === $handle;
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Native cleanup warnings are unconfirmed release, never raw output or false success.
		set_error_handler(
			static function () {
				throw new RuntimeException( 'Bootstrap cleanup diagnostic' );
			}
		);
		try {
			$closed = true === mysqli_close( $handle );
		} finally {
			restore_error_handler();
		}
		if ( $closed && $matched ) {
			$this->dbh = null;
		}
		return $closed && $matched;
	}
}
