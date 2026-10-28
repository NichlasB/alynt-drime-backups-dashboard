<?php
/**
 * Dormant conditional owner. No production provider, native execution or wiring.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once __DIR__ . '/class-source-policy-error.php';

/** Private construction/connection/profile are provisioned only by isolated fixtures. */
final class Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Adapter {
	/**
	 * Owned session, never global wpdb.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection|null
	 */
	private $connection = null;
	/**
	 * Reentry barrier across callbacks and close.
	 *
	 * @var bool
	 */
	private $busy = false;
	/**
	 * Whether one mutation was submitted; unknown completion stays uncertain.
	 *
	 * @var bool
	 */
	private $submitted = false;

	/** Construction opens nothing; no production provider exists in this foundation. */
	private function __construct() {}

	/**
	 * Refuses provisioning without touching credentials, callbacks, cache or storage.
	 *
	 * @return WP_Error
	 */
	public static function create() {
		return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
	}

	/**
	 * Gets a bounded same-observation mode/revision view, never raw storage DATA.
	 *
	 * @param mixed $site_id Dashboard site identity.
	 * @param mixed $source Source key.
	 * @return array|WP_Error
	 */
	public function view_source_mode( $site_id, $source ) {
		return $this->run( 'view', null, $site_id, $source, 'required' );
	}

	/**
	 * Accepts a revision/command, never an observation, plan or caller connection.
	 *
	 * @param mixed $revision Exact lowercase consistency revision.
	 * @param mixed $site_id Site identity.
	 * @param mixed $source Source key.
	 * @param mixed $mode Desired mode.
	 * @return string|WP_Error Literal saved/unchanged only, not DATA model arrays.
	 */
	public function save_source_mode_if_current( $revision, $site_id, $source, $mode ) {
		return $this->run( 'conditional', $revision, $site_id, $source, $mode );
	}

	/**
	 * Models the future legacy bool bridge using the same one-read owner/writer.
	 *
	 * @param mixed $site_id Site identity.
	 * @param mixed $source Source key.
	 * @param mixed $mode Desired legacy scalar mode.
	 * @return bool
	 */
	public function set_source_mode( $site_id, $source, $mode ) {
		return in_array( $this->run( 'legacy', null, $site_id, $source, $mode ), array( 'saved', 'unchanged' ), true );
	}

	/**
	 * Owns one operation and closes only its session, including rejected commands.
	 *
	 * @param string $operation Fixed internal operation, not request-selected.
	 * @param mixed  $revision Expected revision.
	 * @param mixed  $site_id Site identity.
	 * @param mixed  $source Source key.
	 * @param mixed  $mode Desired mode.
	 * @return array|string|WP_Error
	 */
	private function run( $operation, $revision, $site_id, $source, $mode ) {
		if ( $this->busy ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		}
		$this->busy      = true;
		$this->submitted = false;
		try {
			$result = $this->perform( $operation, $revision, $site_id, $source, $mode );
		} catch ( Throwable $error ) {
			$result = Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( $this->submitted ? 'source_policy_write_uncertain' : 'source_policy_unavailable' );
		}
		try {
			if ( $this->connection instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection && true !== $this->connection->close() ) {
				$result = Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( $this->submitted ? 'source_policy_write_uncertain' : 'source_policy_unavailable' );
			}
		} catch ( Throwable $error ) {
			$result = Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( $this->submitted ? 'source_policy_write_uncertain' : 'source_policy_unavailable' );
		} finally {
			$this->busy = false;
		}
		return $result;
	}

	/**
	 * Executes a validated command on the privately provisioned, admitted session.
	 *
	 * @param string $operation Fixed operation.
	 * @param mixed  $revision Expected revision.
	 * @param mixed  $site_id Site identity.
	 * @param mixed  $source Source key.
	 * @param mixed  $mode Desired mode.
	 * @return array|string|WP_Error
	 */
	private function perform( $operation, $revision, $site_id, $source, $mode ) {
		if ( ! is_scalar( $site_id ) || ! is_scalar( $source ) || ! is_scalar( $mode ) || ( 'conditional' === $operation && ( ! is_string( $revision ) || ! preg_match( '/\A[a-f0-9]{64}\z/', $revision ) ) ) ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_invalid' );
		}
		$profile = $this->admit();
		if ( Alynt_Drime_Backups_Dashboard_Source_Policy_Error::is_error( $profile ) ) {
			return $profile;
		}
		$site_id = absint( $site_id );
		$source  = sanitize_key( (string) $source );
		$mode    = sanitize_key( (string) $mode );
		if ( $site_id <= 0 || 'wpvivid' !== $source || ( 'conditional' === $operation && ! in_array( $mode, array( 'required', 'external_optional' ), true ) ) ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_invalid' );
		}
		$this->require_profile();
		$before = $this->observe( $profile );
		if ( Alynt_Drime_Backups_Dashboard_Source_Policy_Error::is_error( $before ) ) {
			return $before;
		}
		$this->require_profile();
		if ( 'view' === $operation ) {
			$normalizer = new Alynt_Drime_Backups_Dashboard_Source_Policy( $before['map'] );
			$view_mode  = $normalizer->source_mode( array( 'id' => $site_id ), $source );
			$this->require_profile();
			return array(
				'mode'     => $view_mode,
				'revision' => $before['model']->revision(),
			);
		}
		$plan = $before['model']->plan( 'legacy' === $operation ? $before['model']->revision() : $revision, $site_id, $source, $mode );
		if ( Alynt_Drime_Backups_Dashboard_Source_Policy_Error::is_error( $plan ) ) {
			return $plan;
		}
		$this->require_profile();
		$name  = Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES;
		$value = sanitize_option( $name, $plan['policies'] );
		$this->require_profile();
		if ( 'insert' !== $plan['operation'] ) {
			$value = apply_filters( "pre_update_option_$name", $value, $before['map'], $name );
			$this->require_profile();
			$value = apply_filters( 'pre_update_option', $value, $name, $before['map'] );
		}
		if ( $value !== $plan['policies'] || Alynt_Drime_Backups_Dashboard_Source_Policy_Error::is_error( $this->admit() ) ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		}
		if ( 'noop' === $plan['operation'] ) {
			return 'unchanged';
		}
		$bytes = maybe_serialize( $value );
		if ( ! is_string( $bytes ) || strlen( $bytes ) > 16384 ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_invalid' );
		}
		if ( 'insert' === $plan['operation'] ) {
			do_action( 'add_option', $name, $value );
		} else {
			do_action( 'update_option', $name, $before['map'], $value );
		}
		if ( Alynt_Drime_Backups_Dashboard_Source_Policy_Error::is_error( $this->admit() ) ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		}
		$sql             = $this->mutation_sql( $plan['operation'], $before['row'], $bytes, $profile['autoload'] );
		$this->submitted = true;
		$ack             = $this->connection->execute( $sql );
		if ( false === $ack['affected'] ) {
			if ( 'insert' === $plan['operation'] && 1062 === $ack['errno'] && true === $this->connection->ready ) {
				$winner = $this->observe( $profile );
				return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( ! Alynt_Drime_Backups_Dashboard_Source_Policy_Error::is_error( $winner ) && null !== $winner['row'] ? 'source_policy_conflict' : 'source_policy_write_uncertain' );
			}
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( in_array( $ack['errno'], array( 1064, 1205, 1213 ), true ) ? 'source_policy_storage_error' : 'source_policy_write_uncertain' );
		}
		if ( 0 === $ack['affected'] ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_conflict' );
		}
		if ( 1 !== $ack['affected'] || Alynt_Drime_Backups_Dashboard_Source_Policy_Error::is_error( $this->admit() ) || ! $this->invalidate() ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_write_uncertain' );
		}
		$this->require_profile();
		if ( 'insert' === $plan['operation'] ) {
			do_action( "add_option_$name", $name, $value );
			$this->require_profile();
			do_action( 'added_option', $name, $value );
		} else {
			do_action( "update_option_$name", $before['map'], $value, $name );
			$this->require_profile();
			do_action( 'updated_option', $name, $before['map'], $value );
		}
		return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::is_error( $this->admit() ) ? Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_write_uncertain' ) : 'saved';
	}

	/**
	 * Admits only private provider facts, never constructor/request DATA.
	 *
	 * @return array|WP_Error
	 */
	private function admit() {
		if ( ! $this->connection instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection || true !== $this->connection->ready ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		}
		return $this->connection->admit_profile();
	}

	/**
	 * Stops a changed profile before the next callback phase; caller maps uncertainty.
	 *
	 * @return void
	 * @throws RuntimeException For stale admission.
	 */
	private function require_profile() {
		if ( Alynt_Drime_Backups_Dashboard_Source_Policy_Error::is_error( $this->admit() ) ) {
			throw new RuntimeException( 'Policy profile changed' );
		}
	}

	/**
	 * Checks the actual SELECT acknowledgement before treating zero rows as absence.
	 *
	 * @param array $profile Private admitted descriptor.
	 * @return array|WP_Error
	 */
	private function observe( array $profile ) {
		$connection = $this->connection;
		$name       = Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Fixed connection table validated by profile admission; values prepared, no request identifier.
		$ack = $connection->execute( $connection->prepare_checked( "SELECT IF(OCTET_LENGTH(option_value)<=16384,option_value,NULL) AS option_value,autoload FROM $connection->options WHERE option_name=%s AND BINARY option_name=BINARY %s LIMIT 1", $name, $name ) );
		if ( ! is_int( $ack['affected'] ) || $ack['affected'] < 0 || $ack['affected'] > 1 || ! is_array( $connection->last_result ) || count( $connection->last_result ) !== $ack['affected'] ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_storage_error' );
		}
		$row = null;
		if ( 1 === $ack['affected'] ) {
			$row = $connection->last_result[0];
			if ( ! is_object( $row ) || 'stdClass' !== get_class( $row ) ) {
				return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_invalid' );
			}
			$row = (array) $row;
		}
		return Alynt_Drime_Backups_Dashboard_Source_Policy_Stored_Map::decode( $row, $profile['owner'], $profile['states'] );
	}

	/**
	 * Prepares one strict insert or exact conditional update, with no retry/upsert.
	 *
	 * @param string     $operation Insert/update.
	 * @param array|null $row Exact internal observation row.
	 * @param string     $bytes Candidate bytes.
	 * @param string     $autoload Qualified physical representation.
	 * @return string
	 */
	private function mutation_sql( $operation, $row, $bytes, $autoload ) {
		$connection = $this->connection;
		$name       = Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES;
		if ( 'insert' === $operation ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Fixed admitted table, prepared values only.
			return $connection->prepare_checked( "INSERT INTO $connection->options (option_name,option_value,autoload) VALUES (%s,%s,%s)", $name, $bytes, $autoload );
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Fixed admitted table, all identity/byte/length values prepared.
		return $connection->prepare_checked( "UPDATE $connection->options SET option_value=%s,autoload=%s WHERE option_name=%s AND BINARY option_name=BINARY %s AND BINARY option_value=BINARY %s AND OCTET_LENGTH(option_value)=%d AND BINARY autoload=BINARY %s AND OCTET_LENGTH(autoload)=%d", $bytes, $autoload, $name, $name, $row['option_value'], strlen( $row['option_value'] ), $row['autoload'], strlen( $row['autoload'] ) );
	}

	/**
	 * Confirms process-local absence only; never seeds a map or flushes globally.
	 *
	 * @return bool
	 */
	private function invalidate() {
		foreach ( array( 'alloptions', 'notoptions', Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES ) as $key ) {
			wp_cache_delete( $key, 'options' );
			$found = null;
			wp_cache_get( $key, 'options', false, $found );
			if ( false !== $found ) {
				return false;
			}
		}
		return true;
	}
}
