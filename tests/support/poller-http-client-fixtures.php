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

	/**
	 * Creates a successful status HTTP client fixture and captures the request.
	 *
	 * @param array<string,mixed> $captured Captured request details.
	 * @param array<string,mixed> $payload_overrides Payload overrides.
	 * @return callable
	 */
	private function successful_capturing_http_client( array &$captured, array $payload_overrides = array() ) {
		return function ( $url, $args ) use ( &$captured, $payload_overrides ) {
			$captured = array(
				'url'  => $url,
				'args' => $args,
			);

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
