<?php
/**
 * Poller HTTP client fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Builds HTTP response fixtures for poller tests.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Poller_Http_Client_Fixtures {
	/**
	 * Creates a successful status HTTP client fixture.
	 *
	 * @param array<string,mixed> $payload_overrides Payload overrides.
	 * @return callable
	 */
	private function successful_http_client( array $payload_overrides = array() ) {
		return function () use ( $payload_overrides ) {
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
		};
	}
}
