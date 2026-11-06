<?php
/**
 * Closed pre-connect/active join; no factory, secret getter or native executor.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once dirname( __DIR__ ) . '/class-source-policy-production-expectation.php';

/** The retained receiver/Query_Context, never caller DATA, complete this join. */
trait Alynt_Drime_Backups_Dashboard_Source_Policy_Production_Join {
	/** Privately bound query context before capture.
	 *
	 * @var object|null
	 */
	private $production_query = null;
	/** Immutable process observations; modeled by the private isolated IO fixture.
	 *
	 * @var array|null
	 */
	private $production_runtime = null;
	/** One expectation selected before server observations.
	 *
	 * @var array|null
	 */
	private $production_expected = null;
	/** Non-secret frame identity, never resealed after change.
	 *
	 * @var array|null
	 */
	private $production_projection = null;

	/** Pre-connect consistency only; owner execution and valid() still refuse.
	 *
	 * @return bool
	 */
	public function capture_bootstrap() {
		if ( 'fresh' !== $this->state ) {
			return false;
		}
		$this->state = 'capturing';
		try {
			$core   = $GLOBALS['wp_version'] ?? null;
			$policy = $this->policy ?? Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Source::policy( $core );
			if ( ! is_array( $policy ) || $core !== $policy['core'] || PHP_VERSION !== $policy['php'] || ! $this->checked_declarations( $policy )
				|| ! $this->production_query instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context || ! $this->production_query->owns( $this->receiver ) ) {
				return false;
			}
			$this->policy  = $policy;
			$this->ambient = $GLOBALS['wpdb'];
			$this->frame   = $this->settings();
			if ( null === $this->production_runtime ) {
				$this->production_runtime = array(
					'platform' => PHP_OS_FAMILY,
					'arch'     => php_uname( 'm' ),
					'php'      => PHP_VERSION,
					// phpcs:ignore WordPress.DB.RestrictedFunctions.mysql_mysqli_get_client_info -- Source-bound client metadata only, never connection or SQL.
					'client'   => mysqli_get_client_info(),
					'mode'     => Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::report_mode( $this->binding['driver_file'] ),
				);
				if ( '6.0' !== $core ) {
					// Immutable source-owned facts; preserve the legacy five-field snapshot.
					$this->production_runtime['word_size'] = PHP_INT_SIZE;
					$this->production_runtime['nts']       = 0 === PHP_ZTS;
				}
			}
			$this->production_expected = Alynt_Drime_Backups_Dashboard_Source_Policy_Production_Expectation::select( $this->frame, $this->production_runtime );
			if ( false === $this->production_expected ) {
				return false;
			}
			$this->production_projection = $this->projection( $this->frame );
			$this->state                 = 'bootstrap';
			if ( ! $this->production_current() ) {
				return false;
			}
			return null === $this->request_root || $this->receiver->bind_request_frame( $this->request_root, $this, $this->production_query, $this->frame, $this->binding );
		} catch ( Throwable $error ) {
			return false;
		}
	}

	/** Exact private handoff comparison, never a setter/export.
	 *
	 * @param object $receiver Fixed receiver.
	 * @param object $query Bound query context.
	 * @param mixed  $frame Owner's private settings.
	 * @param mixed  $binding Owner's declaration bindings.
	 * @return bool
	 */
	public function bootstrap_matches( $receiver, $query, $frame, $binding ) {
		if ( 'bootstrap' !== $this->state || $this->receiver !== $receiver || $this->production_query !== $query || ! $this->production_current()
			|| ! is_array( $frame ) || ! is_array( $binding ) || ! $this->same_fields( $this->frame, $frame ) ) {
			return false;
		}
		$expected = $this->binding;
		unset( $expected['hook_file'], $binding['hook_file'] );
		return $this->same_fields( $expected, $binding );
	}

	/** Actual completed private initialization, not supplied ready/facts DATA.
	 *
	 * @param object $receiver Fixed receiver.
	 * @param object $query Same query context.
	 * @return bool
	 */
	public function activate( $receiver, $query ) {
		if ( 'bootstrap' !== $this->state || $this->receiver !== $receiver || $this->production_query !== $query || ! $this->production_current()
			|| ! $receiver->production_completed( $this, $query ) || ! $query->valid() ) {
			return false;
		}
		$this->frame = $this->production_projection;
		$this->state = 'production_active';
		return $this->production_valid( $receiver );
	}

	/** Non-secret expectation only for this receiver/query/lifecycle.
	 *
	 * @param object $receiver Fixed receiver.
	 * @param object $query Same query context.
	 * @return array|false
	 */
	public function production_expectation( $receiver, $query ) {
		if ( ! in_array( $this->state, array( 'bootstrap', 'production_active' ), true ) || $this->receiver !== $receiver || $this->production_query !== $query
			|| ! $this->production_current() || ! $query->owns( $receiver ) || ( 'production_active' === $this->state && ! $this->production_valid( $receiver ) ) ) {
			return false;
		}
		return $this->production_expected + array( 'context' => spl_object_hash( $query ) );
	}

	/** Revoke before driver/callback cleanup, retaining no secret or expectation.
	 *
	 * @param object $receiver Exact receiver.
	 * @return void
	 */
	public function revoke_production( $receiver ) {
		if ( $this->receiver === $receiver ) {
			$this->state = 'revoked';
			$this->frame = null;
			$this->clear_production();
		}
	}

	/** Reset join bindings; finish() separately retains owned cleanup.
	 *
	 * @return void
	 */
	private function clear_production() {
		$this->production_query      = null;
		$this->production_runtime    = null;
		$this->production_expected   = null;
		$this->production_projection = null;
	}

	/** Active validation neither reconstructs credentials nor executes SQL.
	 *
	 * @param object $receiver Exact receiver.
	 * @return bool
	 */
	private function production_valid( $receiver ) {
		if ( 'production_active' !== $this->state || $this->receiver !== $receiver || ! $this->production_current()
			|| ! $receiver->production_completed( $this, $this->production_query ) || ! $this->production_query->valid()
			|| 'utf8mb4' !== $receiver->charset || $this->production_expected['collation'] !== $receiver->collate ) {
			return false;
		}
		$facts = $receiver->profile();
		foreach ( array( 'core', 'database', 'table', 'server' ) as $key ) {
			if ( ( $facts[ $key ] ?? null ) !== $this->production_expected[ $key ] ) {
				return false;
			}
		}
		return $receiver->options === $this->production_expected['table'];
	}

	/** Rechecks the frozen source/runtime/configuration, never learns drift.
	 *
	 * @return bool
	 */
	private function production_current() {
		try {
			return is_array( $this->production_projection ) && is_array( $this->policy ) && ( $GLOBALS['wpdb'] ?? null ) === $this->ambient
				&& ( $GLOBALS['wp_version'] ?? null ) === $this->policy['core'] && $this->checked_declarations( $this->policy )
				&& $this->production_projection === $this->settings( false )
				&& Alynt_Drime_Backups_Dashboard_Source_Policy_Production_Expectation::matches( $this->production_projection, $this->production_runtime, $this->production_expected )
				// phpcs:ignore WordPress.DB.RestrictedFunctions.mysql_mysqli_get_client_info,WordPress.PHP.YodaConditions -- Exact frozen observation comparison, not connection or SQL.
				&& $this->production_runtime['client'] === mysqli_get_client_info()
				&& 0 === Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::report_mode( $this->binding['driver_file'] );
		} catch ( Throwable $error ) {
			return false;
		}
	}

	/** Private non-secret projection, never an exported credential DTO.
	 *
	 * @param array $frame Complete frame.
	 * @return array
	 */
	private function projection( array $frame ) {
		unset( $frame['user'], $frame['password'] );
		return $frame;
	}

	/** Exact key/value identity; private map insertion order is not authority.
	 *
	 * @param array $expected Frozen fields.
	 * @param array $actual Owner fields.
	 * @return bool
	 */
	private function same_fields( array $expected, array $actual ) {
		if ( count( $expected ) !== count( $actual ) ) {
			return false;
		}
		foreach ( $expected as $key => $value ) {
			if ( ! array_key_exists( $key, $actual ) || $actual[ $key ] !== $value ) {
				return false;
			}
		}
		return true;
	}
}
