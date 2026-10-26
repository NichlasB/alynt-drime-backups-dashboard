<?php
/**
 * Scheduled retention error reporting through the actual caller and event logger.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/support/retention-reporting-fixtures.php';

class RetentionFailureReportingTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Test_Retention_Reporting_Fixtures;

	/** @dataProvider outcomes */
	public function test_each_cleanup_is_attempted_once_and_only_errors_are_reported( $snapshot, $action, $codes ) {
		$error = new WP_Error( 'UNSAFE_PRIVATE_CODE', 'PRIVATE_SQL SELECT secret FROM private_table', array( 'last_error' => 'PRIVATE_PASSWORD' ) );
		$this->wire( 'error' === $snapshot ? $error : $snapshot, 'error' === $action ? $error : $action );
		$this->assertNull( $this->plugin->cleanup_retention() );
		$this->assertSame( array( array() ), $this->snapshots->calls );
		$this->assertSame( array( array() ), $this->actions->calls );
		$events = array_reverse( $this->events() );
		$this->assertSame( $codes, array_column( $events, 'code' ) );
		foreach ( $events as $event ) {
			$this->assertSame( 'error', $event['level'] );
			$this->assertSame( 'storage', $event['category'] );
			$this->assertSame( array(), $event['context'] );
			$this->assertSame( 'snapshot_cleanup_failed' === $event['code'] ? 'The dashboard could not clean up old status snapshots.' : 'The dashboard could not clean up old remote action records.', $event['message'] );
		}
		$this->assertStringNotContainsString( 'PRIVATE_', json_encode( $events ) );
		$this->assertStringNotContainsString( 'UNSAFE_', json_encode( $events ) );
		$expected_autoload = $codes ? array( Alynt_Drime_Backups_Dashboard_Event_Log::OPTION_EVENTS => false ) : array();
		$this->assertSame( $expected_autoload, $GLOBALS['alynt_drime_backups_dashboard_test_autoload'] );
	}

	/** @dataProvider loggingPolicies */
	public function test_existing_diagnostics_opt_in_and_threshold_are_not_bypassed( $settings ) {
		$GLOBALS['alynt_drime_backups_dashboard_test_options'][ Alynt_Drime_Backups_Dashboard_Event_Log::OPTION_SETTINGS ] = $settings;
		$this->wire( new WP_Error( 'failed', 'Private' ), new WP_Error( 'failed', 'Private' ) );
		$before = $GLOBALS['alynt_drime_backups_dashboard_test_options'];
		$this->plugin->cleanup_retention();
		$this->assertSame( $before, $GLOBALS['alynt_drime_backups_dashboard_test_options'] );
		$this->assertSame( array(), $this->events() );
		$this->assertCount( 1, $this->snapshots->calls );
		$this->assertCount( 1, $this->actions->calls );
	}

	public function test_logging_failure_does_not_repeat_or_prevent_either_cleanup_attempt() {
		$this->wire( new WP_Error( 'failed', 'Private' ), new WP_Error( 'failed', 'Private' ) );
		$logger = new class( $this->snapshots, $this->actions ) {
			public $attempts = 0;
			private $repositories;
			public function __construct( ...$repositories ) { $this->repositories = $repositories; }
			public function log( ...$args ) {
				foreach ( $this->repositories as $repository ) {
					if ( 1 !== count( $repository->calls ) ) { throw new RuntimeException( 'Logging preceded a cleanup attempt.' ); }
				}
				++$this->attempts;
				return false;
			}
		};
		$this->replace( 'event_log', $logger );
		$this->assertNull( $this->plugin->cleanup_retention() );
		$this->assertSame( 2, $logger->attempts );
		$this->assertCount( 1, $this->snapshots->calls );
		$this->assertCount( 1, $this->actions->calls );
	}

	public function test_next_healthy_run_keeps_history_without_adding_success_noise() {
		$this->wire( new WP_Error( 'failed', 'Private' ), 0 );
		$this->plugin->cleanup_retention();
		$this->assertCount( 1, $this->events() );
		$before = $this->events();
		$this->snapshots->result = 0;
		$this->plugin->cleanup_retention();
		$this->assertSame( $before, $this->events() );
		$this->assertCount( 2, $this->snapshots->calls );
		$this->assertCount( 2, $this->actions->calls );
	}

	public static function outcomes(): array {
		return array(
			'positive' => array( 17, 2, array() ),
			'no-op' => array( 0, 0, array() ),
			'snapshot error' => array( 'error', 2, array( 'snapshot_cleanup_failed' ) ),
			'action error' => array( 17, 'error', array( 'remote_action_cleanup_failed' ) ),
			'both errors' => array( 'error', 'error', array( 'snapshot_cleanup_failed', 'remote_action_cleanup_failed' ) ),
		);
	}

	public static function loggingPolicies(): array {
		return array( 'disabled' => array( array( 'enabled' => false ) ), 'threshold' => array( array( 'enabled' => true, 'minimum_level' => 'critical' ) ) );
	}
}
