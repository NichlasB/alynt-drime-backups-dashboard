<?php
/**
 * Fixed owned Linux proof receipt DATA, never native admission authority.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Contains no backend, callback or execution provider. */
final class Alynt_Drime_Backups_Dashboard_Linux_Qualification_Contract {
	/**
	 * Checks a terminal against the parent's fixed child/source/run identities.
	 *
	 * @param mixed $row Bounded decoded terminal DATA.
	 * @param mixed $image Parent's audited source image.
	 * @param mixed $run Parent's owned run.
	 * @param mixed $pid Actual owned child ID.
	 * @return bool Correlation only, not authentication or native proof.
	 */
	public static function terminal( $row, $image, $run, $pid ) {
		$keys = array( 'event', 'image', 'run', 'pid', 'checks', 'groups', 'mode', 'closed' );
		return is_array( $row ) && array_keys( $row ) === $keys && is_string( $image ) && 1 === preg_match( '/\A[a-f0-9]{64}\z/', $image )
			&& is_string( $run ) && 1 === preg_match( '/\A[a-f0-9]{32}\z/', $run ) && is_int( $pid ) && $pid > 0
			&& 'closed' === $row['event'] && $image === $row['image'] && $run === $row['run'] && $pid === $row['pid']
			&& is_int( $row['checks'] ) && $row['checks'] > 0 && $row['checks'] <= 99999
			&& 9 === $row['groups'] && 0 === $row['mode'] && true === $row['closed'];
	}
}
