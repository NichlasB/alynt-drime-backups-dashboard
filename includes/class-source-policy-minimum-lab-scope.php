<?php
/**
 * Private proof-only minimum-lab binding; no factory, discovery or native IO.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Only the fixed trusted proof composes this private object after prerequisites. */
final class Alynt_Drime_Backups_Dashboard_Source_Policy_Minimum_Lab_Scope {
	/** Exact receiver identity, not an imported handle.
	 *
	 * @var object|null
	 */
	private $receiver = null;
	/** Exact privately owned context.
	 *
	 * @var object|null
	 */
	private $context = null;
	/** Non-secret immutable frame projection.
	 *
	 * @var array|null
	 */
	private $frame = null;
	/** Source/run correlation, never distribution attestation.
	 *
	 * @var array|null
	 */
	private $evidence = null;

	/**
	 * No public mint. PHP reflection in the trusted proof is not a security boundary.
	 *
	 * @param object $receiver Exact fresh proof receiver.
	 * @param object $context Exact owned context.
	 * @param array  $frame Already source-owned fixture settings.
	 * @param array  $evidence Trusted-lab prerequisite correlation, not caller authority.
	 */
	private function __construct( $receiver, $context, $frame, $evidence ) {
		$fixed = array(
			'model'         => 'trusted-lab-functional-v1',
			'platform'      => 'Linux',
			'arch'          => 'x86_64',
			'php'           => '7.4.33',
			'client'        => 'mysqlnd 7.4.33',
			'core_commit'   => 'cc101b64012b16d087780657a2b828ccd7794a63',
			'runtime_image' => 'sha256:08a1cb1fbe022cd08d5d19e6f95ab549194927959a297da41590d4f2d884f7b4',
			'server_image'  => 'sha256:161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5',
			'initial_mode'  => 0,
		);
		if ( ! is_array( $evidence ) || 12 !== count( $evidence ) || array_diff( array_keys( $evidence ), array_merge( array_keys( $fixed ), array( 'head', 'image', 'run' ) ) )
			|| ! is_object( $receiver ) || 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection' !== get_class( $receiver )
			|| ( new ReflectionClass( $receiver ) )->getFileName() !== realpath( __DIR__ . '/class-source-policy-no-reconnect-connection.php' )
			|| ! $context instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context || ! $context->owns( $receiver )
			|| ! Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::settings( $frame ) ) {
			return;
		}
		foreach ( $fixed as $key => $value ) {
			if ( ! array_key_exists( $key, $evidence ) || $evidence[ $key ] !== $value ) {
				return;
			}
		}
		foreach ( array(
			'head'  => 40,
			'image' => 64,
			'run'   => 32,
		) as $key => $size ) {
			if ( ! isset( $evidence[ $key ] ) || ! is_string( $evidence[ $key ] ) || 1 !== preg_match( '/\A[a-f0-9]{' . $size . '}\z/', $evidence[ $key ] ) ) {
				return;
			}
		}
		if ( '6.0' !== $frame['core'] || 'adb_minimum' !== $frame['database'] || 'adbm_' . substr( $evidence['run'], 0, 12 ) . '_options' !== $frame['table']
			|| 'adbm' !== $frame['user'] || '127.0.0.1' !== $frame['host'] || 3306 !== $frame['port'] || null !== $frame['socket']
			|| 'tcp_loopback' !== $frame['transport'] || '127.0.0.1:3306' !== $frame['db_host'] ) {
			return;
		}
		$this->receiver = $receiver;
		$this->context  = $context;
		$this->frame    = self::projection( $frame );
		$this->evidence = $evidence;
	}

	/**
	 * Returns fixture identity DATA only; never scope/context, credentials or a grant.
	 *
	 * @param object $receiver Actual receiver.
	 * @param object $context Actual retained context.
	 * @return array|false Frozen non-secret expectation.
	 */
	public function expectation( $receiver, $context ) {
		if ( null === $this->frame || null === $this->evidence || $this->receiver !== $receiver || $this->context !== $context || ! $context->owns( $receiver ) ) {
			return false;
		}
		return array(
			'core'     => '6.0',
			'server'   => '10.11.18-MariaDB-ubu2204',
			'database' => $this->frame['database'],
			'table'    => $this->frame['table'],
			'context'  => spl_object_hash( $context ),
		);
	}

	/** Frozen non-secret frame comparison; password is never retained or hashed.
	 *
	 * @param array $frame Current private frame.
	 * @return bool
	 */
	public function frame_matches( $frame ) {
		return is_array( $frame ) && null !== $this->frame && self::projection( $frame ) === $this->frame && 'adbm' === ( $frame['user'] ?? null );
	}

	/**
	 * Projects only non-secret fixture identity.
	 *
	 * @param array $frame Private settings.
	 * @return array
	 */
	private static function projection( $frame ) {
		$result = array();
		foreach ( array( 'core', 'database', 'table', 'host', 'port', 'socket', 'db_host', 'transport', 'charset', 'collate' ) as $key ) {
			$result[ $key ] = $frame[ $key ] ?? null;
		}
		return $result;
	}
}
