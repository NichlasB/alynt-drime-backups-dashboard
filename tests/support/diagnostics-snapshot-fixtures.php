<?php
/**
 * Diagnostics snapshot fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared snapshot fixture builders for diagnostics tests.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Snapshot_Fixtures {
	/**
	 * Creates a healthy snapshot.
	 *
	 * @param array<string,mixed> $overrides Payload overrides.
	 * @return array<string,mixed>
	 */
	private function snapshot( array $overrides = array() ) {
		return array(
			'schema_version'  => 1,
			'observed_at'     => gmdate( 'Y-m-d H:i:s' ),
			'decoded_payload' => array_merge(
				array(
					'schema_version'           => 1,
					'server_outbox_configured' => true,
					'failed_count'             => 0,
					'warning_count'            => 0,
					'warnings'                 => array(),
					'cron_status'              => 'ok',
				),
				$overrides
			),
		);
	}

	/**
	 * Creates a retained snapshot summary row.
	 *
	 * @param string $status Snapshot status.
	 * @param string $observed_at Observed time.
	 * @return array<string,mixed>
	 */
	private function snapshot_row( $status, $observed_at ) {
		return array(
			'overall_status' => $status,
			'observed_at'    => $observed_at,
		);
	}
}
