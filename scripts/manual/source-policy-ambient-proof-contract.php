<?php
/**
 * Finite ambient proof receipt validation; no session, SQL or runtime grant.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Validates a terminal receipt against the parent's exact owned identities.
 *
 * @param mixed $wire Bounded record.
 * @param mixed $run Parent run identifier.
 * @param mixed $image Frozen source identifier.
 * @param mixed $pid Owned child identity.
 * @return bool
 */
function adb0_terminal( $wire, $run, $image, $pid ) {
	if ( ! is_string( $wire ) || strlen( $wire ) > 256 || ! is_string( $run ) || ! preg_match( '/\A[a-f0-9]{32}\z/', $run )
		|| ! is_string( $image ) || ! preg_match( '/\A[a-f0-9]{64}\z/', $image ) || ! is_int( $pid ) || $pid < 1 ) {
		return false;
	}
	return 1 === preg_match( '/\AADBO1\|' . $run . '\|' . $image . '\|' . $pid . '\|[1-9][0-9]{0,4}\|9\|0\|closed\n\z/', $wire );
}
