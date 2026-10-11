<?php
/**
 * Poller HTTP response fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Builds HTTP response payload fixtures for poller tests.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Poller_Http_Response_Fixtures {
	/**
	 * Creates a successful status HTTP response fixture.
	 *
	 * @param array<string,mixed> $payload_overrides Payload overrides.
	 * @return array<string,mixed>
	 */
	private function successful_http_response( array $payload_overrides = array() ) {
		return array(
			'response' => array(
				'code' => 200,
			),
			'body'     => wp_json_encode(
				array_merge(
					$this->payload(),
					$payload_overrides
				)
			),
		);
	}
}
