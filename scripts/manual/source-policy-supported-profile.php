<?php
/**
 * Fixed proof candidate DATA only; no discovery, IO, allocation or native grant.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Returns a fixed reviewed candidate, never a caller-supplied source manifest.
 *
 * @param mixed $core Exact core row.
 * @return array|false
 */
function adbp_profile( $core ) {
	if ( ! in_array( $core, array( '6.0', '7.1.3' ), true ) ) {
		return false;
	}
	$profiles = require __DIR__ . '/source-policy-supported-core-pins.php';
	return $profiles[ $core ];
}

/** Pure expected-startup-mode consistency, never a reporting-mode setter.
 *
 * @param mixed $core Exact privately selected core candidate.
 * @param mixed $php Observed PHP version.
 * @param mixed $mode Observed shared reporting mode.
 * @return bool Consistency only, not authority to construct/connect.
 */
function adbp_initial_mode( $core, $php, $mode ) {
	$profile = adbp_profile( $core );
	return false !== $profile && $profile['php'] === $php && $profile['initial_mode'] === $mode;
}
