<?php
/**
 * Fixed ambient callback-source contract; no session, SQL or runtime grant.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Fixed proof-owned callback source; consistency metadata, not an admission grant.
 *
 * @return array Exact file/hash binding, never caller-selected metadata.
 */
function adb0_observer_binding() {
	$file = realpath( __DIR__ . '/source-policy-minimum-adapter-cases.php' );
	return false === $file ? array() : array( $file => hash_file( 'sha256', $file ) );
}
