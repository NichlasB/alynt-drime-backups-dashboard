<?php
/**
 * Fixed installer DATA only; never loads core or reads storage/environment.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Exact bounded array record, with no object coercion.
 *
 * @param mixed $row Supplied DATA.
 * @param array $keys Fixed keys.
 * @return bool
 */
function adbh_installer_shape( $row, $keys ) {
	return is_array( $row ) && count( $row ) === count( $keys ) && ! array_diff( $keys, array_keys( $row ) );
}

/**
 * Private fixture identity DATA, not source/ownership attestation.
 *
 * @param mixed $row Supplied identity.
 * @return bool
 */
function adbh_installer_identity( $row ) {
	if ( ! adbh_installer_shape( $row, array( 'run', 'head', 'image', 'root' ) ) ) {
		return false;
	}
	foreach ( array(
		'run'   => 32,
		'head'  => 40,
		'image' => 64,
	) as $key => $length ) {
		if ( ! is_string( $row[ $key ] ) || 1 !== preg_match( '/\A[a-f0-9]{' . $length . '}\z/', $row[ $key ] ) ) {
			return false;
		}
	}
	return is_string( $row['root'] ) && strlen( $row['root'] ) <= 128
		&& 1 === preg_match( '#\A/(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9_-]+\z#', $row['root'] );
}

/**
 * Pinned WP_Http::request defaults after _redirection is added, before transport.
 *
 * @param string $core Fixed owned core root DATA, not a path reader.
 * @return array
 */
function adbh_installer_args( $core ) {
	return array(
		'method'              => 'GET',
		'timeout'             => 5,
		'redirection'         => 5,
		'httpversion'         => '1.0',
		'user-agent'          => 'WordPress/7.1.4; http://127.0.0.1:18777',
		'reject_unsafe_urls'  => false,
		'blocking'            => true,
		'headers'             => array(),
		'cookies'             => array(),
		'body'                => null,
		'compress'            => false,
		'decompress'          => true,
		'sslverify'           => true,
		'sslcertificates'     => $core . '/wp-includes/certificates/ca-bundle.crt',
		'stream'              => false,
		'filename'            => null,
		'limit_response_size' => null,
		'_redirection'        => 5,
	);
}

/**
 * Independent actual-post DATA to expected probe URLs. No clock or SQL read.
 *
 * @param mixed $post Independently read owned post fields.
 * @return array|false
 */
function adbh_installer_urls( $post ) {
	if ( ! adbh_installer_shape( $post, array( 'post_date', 'post_name' ) ) || 'hello-world' !== $post['post_name']
		|| ! is_string( $post['post_date'] ) || 1 !== preg_match( '/\A([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})\z/', $post['post_date'], $date )
		|| ! checkdate( (int) $date[2], (int) $date[3], (int) $date[1] ) || (int) $date[4] > 23 || (int) $date[5] > 59 || (int) $date[6] > 59 ) {
		return false;
	}
	$path = '/' . $date[1] . '/' . $date[2] . '/' . $date[3] . '/hello-world/';
	return array( 'http://127.0.0.1:18777' . $path, 'http://127.0.0.1:18777/index.php' . $path );
}
