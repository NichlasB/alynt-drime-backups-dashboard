<?php
/**
 * Admin diagnostics overview stub methods.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * No-op renderers for focused diagnostics overview tests.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Stub_Methods {
	/**
	 * Stubs settings diagnostics for this focused overview test.
	 *
	 * @param array<string,mixed> $logging Logging diagnostics.
	 * @return void
	 */
	private function render_diagnostics_settings( array $logging ) {
		unset( $logging );
	}

	/**
	 * Stubs status-count diagnostics for this focused overview test.
	 *
	 * @param array<string,int> $statuses Status counts.
	 * @return void
	 */
	private function render_status_count_table( array $statuses ) {
		unset( $statuses );
	}

	/**
	 * Stubs recent polling diagnostics for this focused overview test.
	 *
	 * @param array<int,array<string,mixed>> $recent Recent outcomes.
	 * @return void
	 */
	private function render_recent_poll_outcomes( array $recent ) {
		unset( $recent );
	}

	/**
	 * Stubs event-log diagnostics for this focused overview test.
	 *
	 * @param array<string,mixed> $logging Logging diagnostics.
	 * @return void
	 */
	private function render_event_log_diagnostics( array $logging ) {
		unset( $logging );
	}

	/**
	 * Stubs support-copy diagnostics for this focused overview test.
	 *
	 * @param array<string,mixed> $support Support diagnostics.
	 * @return void
	 */
	private function render_support_copy_output( array $support ) {
		unset( $support );
	}
}
