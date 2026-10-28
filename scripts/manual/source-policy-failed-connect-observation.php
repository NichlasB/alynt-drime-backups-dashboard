<?php
/**
 * Isolated API observation only. No native implementation or entry-point wiring.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** A finite test-only IO seam, not a connector/provider/SQL channel. */
interface Alynt_Drime_Backups_Dashboard_Cleanup_Observation_IO {
	/** Allocate an explicitly synthetic object.
	 *
	 * @return object|false
	 */
	public function allocate();
	/** Compare exact fixture identity.
	 *
	 * @param object $handle Synthetic object only.
	 * @return bool
	 */
	public function owns( $handle );
	/** Exercise a synthetic failed-connect API.
	 *
	 * @param object $handle Synthetic object only.
	 * @return mixed
	 */
	public function connect( $handle );
	/** Exercise an independent once-only release.
	 *
	 * @param object $handle Synthetic object only.
	 * @return bool
	 */
	public function close( $handle );
	/** Test tripwire: failure observation must never call this getter.
	 *
	 * @param object $handle Synthetic object only.
	 * @return int
	 */
	public function errno( $handle );
}

// phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound -- Cohesive test-only protocol and its sole observer; no native implementation.
/** No shipped native implementation; method itself refuses outside isolation. */
final class Alynt_Drime_Backups_Dashboard_Failed_Connect_Observation {
	// phpcs:enable Generic.Files.OneObjectStructurePerFile.MultipleFound
	/** Consumed before any fallible fixture method.
	 *
	 * @var bool
	 */
	private $consumed = false;
	/** Cached scalar report only.
	 *
	 * @var array|null
	 */
	private $result = null;

	/**
	 * Captures the native-shaped exception before any invalid object getter.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Cleanup_Observation_IO $io Explicit synthetic API fixture only.
	 * @return array Scalar DATA, never a handle or runtime authorization.
	 */
	public function run( Alynt_Drime_Backups_Dashboard_Cleanup_Observation_IO $io ) {
		$report = array(
			'observed' => false,
			'code'     => null,
			'primary'  => 'unconfirmed',
			'cleanup'  => 'unconfirmed',
		);
		if ( $this->consumed ) {
			return $this->result ?? $report;
		}
		$this->consumed = true;
		if ( ! defined( 'ADB_POLICY_ISOLATED' ) || true !== ADB_POLICY_ISOLATED || extension_loaded( 'mysqli' ) || extension_loaded( 'pdo_mysql' ) ) {
			return $report;
		}
		$handle = null;
		$owned  = false;
		try {
			$report['primary'] = 'allocation';
			$handle            = $io->allocate();
			$report['primary'] = 'ownership';
			if ( ! is_object( $handle ) || true !== $io->owns( $handle ) ) {
				$report['primary'] = 'ownership';
			} else {
				$owned             = true;
				$report['primary'] = 'connect';
				try {
					$io->connect( $handle );
				} catch ( Throwable $error ) {
					if ( 'mysqli_sql_exception' === get_class( $error ) && 1049 === $error->getCode() ) {
						$report['primary'] = 'expected-connect';
						$report['code']    = 1049;
					}
				}
			}
		} catch ( Throwable $error ) {
			$report['primary'] .= '-throw'; // Fixed stage only, never collaborator message/trace.
		} finally {
			if ( $owned ) {
				$report['cleanup'] = 'release';
				try {
					if ( true !== $io->owns( $handle ) ) {
						$report['cleanup'] = 'ownership';
					} elseif ( true === $io->close( $handle ) ) {
						$report['cleanup'] = 'acknowledged';
					}
				} catch ( Throwable $cleanup_error ) {
					$report['cleanup'] = 'throw';
				}
			}
		}
		$report['observed'] = 'expected-connect' === $report['primary'] && 'acknowledged' === $report['cleanup'];
		$this->result       = $report;
		return $report;
	}
}
