<?php
/**
 * Dormant source-policy observation, planning and result DATA contract.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once __DIR__ . '/class-source-policy-error.php';

/**
 * Models saves without a storage adapter, injected callbacks or native writer.
 *
 * Values are synthetic DATA, not authenticated observations or native receipts.
 */
final class Alynt_Drime_Backups_Dashboard_Source_Policy_Save_Contract {
	/** Fixed model revision domain. */
	const DOMAIN = 'adbd-source-policy-save-v1';

	/**
	 * Owner label; future native owners must supply their own binding.
	 *
	 * @var string
	 */
	private $owner;
	/**
	 * Whether the modeled row exists.
	 *
	 * @var bool
	 */
	private $exists;
	/**
	 * Exact modeled serialized bytes, never deserialized here.
	 *
	 * @var string
	 */
	private $bytes;
	/**
	 * Exact modeled autoload bytes.
	 *
	 * @var string
	 */
	private $autoload;
	/**
	 * Normalized effective policy view.
	 *
	 * @var array
	 */
	private $policies;
	/**
	 * Domain-separated consistency digest, not authorization.
	 *
	 * @var string
	 */
	private $revision;

	/**
	 * Validates a data-only observation; does not read an option or database.
	 *
	 * @param mixed $owner Model owner identity.
	 * @param mixed $exists Literal existence flag.
	 * @param mixed $bytes Exact serialized array bytes, or empty for absence.
	 * @param mixed $autoload Autoload bytes, or empty for absence.
	 * @param mixed $map Explicit primitive map corresponding to the bytes.
	 * @return self|WP_Error
	 */
	public static function observe( $owner, $exists, $bytes, $autoload, $map ) {
		if ( ! is_string( $owner ) || '' === $owner || strlen( $owner ) > 191 || preg_match( '/[\x00-\x1f\x7f]/', $owner )
			|| ! is_bool( $exists ) || ! is_string( $bytes ) || ! is_string( $autoload ) || ! self::plain_map( $map ) ) {
			return self::failure( 'invalid' );
		}
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Primitive reference-free map checked above; byte comparison only, never deserialization.
		if ( $exists ? serialize( $map ) !== $bytes : ( '' !== $bytes || '' !== $autoload || array() !== $map ) ) {
			return self::failure( 'invalid' );
		}
		return new self( $owner, $exists, $bytes, $autoload, $map );
	}

	/**
	 * Stores immutable scalar/array DATA; the explicit normalizer override cannot read options.
	 *
	 * @param string $owner Owner identity.
	 * @param bool   $exists Row existence.
	 * @param string $bytes Serialized bytes.
	 * @param string $autoload Autoload bytes.
	 * @param array  $map Primitive policy map.
	 */
	private function __construct( $owner, $exists, $bytes, $autoload, array $map ) {
		$this->owner    = $owner;
		$this->exists   = $exists;
		$this->bytes    = $bytes;
		$this->autoload = $autoload;
		$this->policies = ( new Alynt_Drime_Backups_Dashboard_Source_Policy( $map ) )->all_policies();
		$framed         = self::DOMAIN;
		foreach ( array( $owner, $exists ? '1' : '0', $bytes, $autoload ) as $part ) {
			$framed .= strlen( $part ) . ':' . $part;
		}
		$this->revision = hash( 'sha256', $framed );
	}

	/**
	 * Gets consistency DATA, not a nonce, grant or write handle.
	 *
	 * @return string
	 */
	public function revision() {
		return $this->revision;
	}

	/**
	 * Checks exact modeled state for the in-memory conditional-store tests.
	 *
	 * @param mixed $other Other modeled observation.
	 * @return bool
	 */
	public function matches( $other ) {
		return $other instanceof self && $this->owner === $other->owner && $this->exists === $other->exists
			&& $this->bytes === $other->bytes && $this->autoload === $other->autoload && $this->policies === $other->policies;
	}

	/**
	 * Plans one change; produces no mutation and supplies no callable storage handle.
	 *
	 * @param mixed $expected Expected lowercase revision.
	 * @param mixed $site_id Dashboard site identity.
	 * @param mixed $source Source key.
	 * @param mixed $mode Desired mode.
	 * @return array|WP_Error Internal DATA only; never an admin-handler success result.
	 */
	public function plan( $expected, $site_id, $source, $mode ) {
		if ( ! is_string( $expected ) || 1 !== preg_match( '/\A[a-f0-9]{64}\z/', $expected )
			|| ! is_scalar( $site_id ) || ! is_scalar( $source ) || ! is_scalar( $mode ) ) {
			return self::failure( 'invalid' );
		}
		if ( ! hash_equals( $this->revision, $expected ) ) {
			return self::failure( 'conflict' );
		}
		$site_id = absint( $site_id );
		$source  = sanitize_key( (string) $source );
		$mode    = sanitize_key( (string) $mode );
		if ( $site_id <= 0 || Alynt_Drime_Backups_Dashboard_Source_Policy::SOURCE_WPVIVID !== $source ) {
			return self::failure( 'invalid' );
		}
		$after = $this->policies;
		if ( Alynt_Drime_Backups_Dashboard_Source_Policy::MODE_EXTERNAL_OPTIONAL === $mode ) {
			$after[ $site_id ][ $source ] = Alynt_Drime_Backups_Dashboard_Source_Policy::MODE_EXTERNAL_OPTIONAL;
		} else {
			unset( $after[ $site_id ][ $source ] );
			if ( isset( $after[ $site_id ] ) && empty( $after[ $site_id ] ) ) {
				unset( $after[ $site_id ] );
			}
		}
		$after = ( new Alynt_Drime_Backups_Dashboard_Source_Policy( $after ) )->all_policies();
		return array(
			'expected'  => $this,
			'operation' => $after === $this->policies ? 'noop' : ( $this->exists ? 'update' : 'insert' ),
			'policies'  => $after,
			'autoload'  => false,
		);
	}

	/**
	 * Classifies modeled acknowledgement DATA; this is not a native receipt factory.
	 *
	 * @param mixed $operation Planned operation.
	 * @param mixed $affected Modeled row count; false definite error, unknown/unavailable explicit.
	 * @param mixed $postconditions Literal confirmation of modeled cache/hook completion.
	 * @return array|WP_Error Internal DATA; the boolean admin entry must reject this array.
	 */
	public static function classify_result( $operation, $affected, $postconditions = true ) {
		if ( ! in_array( $operation, array( 'noop', 'insert', 'update' ), true ) ) {
			return self::failure( 'invalid' );
		}
		if ( 'unavailable' === $affected || false === $affected ) {
			return self::failure( false === $affected ? 'storage_error' : 'unavailable' );
		}
		if ( 0 === $affected && 'noop' !== $operation ) {
			return self::failure( 'conflict' );
		}
		if ( true !== $postconditions || ( 'noop' === $operation ? 0 !== $affected : 1 !== $affected ) ) {
			return self::failure( 'write_uncertain' );
		}
		return array( 'status' => 'noop' === $operation ? 'unchanged' : 'saved' );
	}

	/**
	 * Rejects object/resource/reference graphs before serialization or normalization.
	 *
	 * @param mixed $map Primitive two-level map.
	 * @return bool
	 */
	private static function plain_map( $map ) {
		if ( ! is_array( $map ) ) {
			return false;
		}
		foreach ( $map as $site => $sources ) {
			if ( null !== ReflectionReference::fromArrayElement( $map, $site ) || ! is_array( $sources ) ) {
				return false;
			}
			foreach ( $sources as $source => $value ) {
				if ( null !== ReflectionReference::fromArrayElement( $sources, $source ) || ( null !== $value && ! is_scalar( $value ) ) ) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Produces only fixed, support-safe error DATA.
	 *
	 * @param string $reason Internal reason.
	 * @return WP_Error
	 */
	private static function failure( $reason ) {
		return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_' . $reason, true );
	}
}
