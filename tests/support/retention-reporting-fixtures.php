<?php
/**
 * API-only retention caller fixtures; no WordPress, SQL or cleanup executor.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/event-log-test-harness.php';

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		// Constructor hook registration is outside this isolated reporting proof.
	}
}

trait Alynt_Drime_Backups_Dashboard_Test_Retention_Reporting_Fixtures {
	private $saved_globals;
	private $plugin;
	private $snapshots;
	private $actions;

	protected function setUp(): void {
		$this->saved_globals = array();
		foreach ( array( 'alynt_drime_backups_dashboard_test_options', 'alynt_drime_backups_dashboard_test_autoload', 'alynt_drime_backups_dashboard_test_unchanged_update_fails', 'wpdb' ) as $key ) {
			$this->saved_globals[ $key ] = array( array_key_exists( $key, $GLOBALS ), $GLOBALS[ $key ] ?? null );
		}
		$GLOBALS['alynt_drime_backups_dashboard_test_options'] = array(
			Alynt_Drime_Backups_Dashboard_Storage::OPTION_SCHEMA_VERSION => '8',
			Alynt_Drime_Backups_Dashboard_Event_Log::OPTION_SETTINGS => array( 'enabled' => true ),
		);
		$GLOBALS['alynt_drime_backups_dashboard_test_autoload'] = array();
		$GLOBALS['alynt_drime_backups_dashboard_test_unchanged_update_fails'] = false;
		$GLOBALS['wpdb'] = new class() {
			public function __call( $name, $args ) {
				throw new RuntimeException( 'Unexpected SQL/cleanup API in reporting test: ' . $name );
			}
		};
	}

	protected function tearDown(): void {
		foreach ( $this->saved_globals as $key => $saved ) {
			if ( $saved[0] ) {
				$GLOBALS[ $key ] = $saved[1];
			} else {
				unset( $GLOBALS[ $key ] );
			}
		}
	}

	private function repository( $result ) {
		return new class( $result ) {
			public $result;
			public $calls = array();
			public function __construct( $result ) { $this->result = $result; }
			public function cleanup_retention() {
				$this->calls[] = func_get_args();
				return $this->result;
			}
		};
	}

	private function wire( $snapshot_result, $action_result ) {
		// Exercise the actual constructor and logger wiring, bypassing only destructive repositories.
		$this->plugin = new Alynt_Drime_Backups_Dashboard_Plugin();
		$this->snapshots = $this->repository( $snapshot_result );
		$this->actions = $this->repository( $action_result );
		$this->replace( 'snapshots', $this->snapshots );
		$this->replace( 'remote_actions', $this->actions );
	}

	private function replace( $property, $value ) {
		$reflection = new ReflectionProperty( $this->plugin, $property );
		if ( PHP_VERSION_ID < 80100 ) { $reflection->setAccessible( true ); }
		$reflection->setValue( $this->plugin, $value );
	}

	private function events() {
		return get_option( Alynt_Drime_Backups_Dashboard_Event_Log::OPTION_EVENTS, array() );
	}
}
