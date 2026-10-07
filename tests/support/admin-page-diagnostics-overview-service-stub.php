<?php
/**
 * Admin diagnostics overview service stub.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake diagnostics service for overview rendering tests.
 */
class Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Service_Stub {
	/**
	 * Diagnostics payload.
	 *
	 * @var array<string,mixed>
	 */
	private $diagnostics;

	/**
	 * Constructor.
	 *
	 * @param array<string,mixed> $diagnostics Diagnostics payload.
	 */
	public function __construct( array $diagnostics ) {
		$this->diagnostics = $diagnostics;
	}

	/**
	 * Returns the diagnostics payload.
	 *
	 * @return array<string,mixed>
	 */
	public function collect() {
		return $this->diagnostics;
	}
}
