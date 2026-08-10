<?php
/**
 * Pending enrollment workflow.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates local pending enrollment records and one-time pairing tokens.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Enrollment_Manager {
	const DEFAULT_PAIRING_TTL_SECONDS = 900;

	/**
	 * Site repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Site_Repository
	 */
	private $sites;

	/**
	 * Origin validator.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Origin_Validator
	 */
	private $origins;

	/**
	 * Constructor.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Site_Repository|null  $sites Site repository.
	 * @param Alynt_Drime_Backups_Dashboard_Origin_Validator|null $origins Origin validator.
	 */
	public function __construct( $sites = null, $origins = null ) {
		$this->sites   = $sites instanceof Alynt_Drime_Backups_Dashboard_Site_Repository ? $sites : new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$this->origins = $origins instanceof Alynt_Drime_Backups_Dashboard_Origin_Validator ? $origins : new Alynt_Drime_Backups_Dashboard_Origin_Validator();
	}

	/**
	 * Creates a pending dashboard site and one display-once pairing token.
	 *
	 * @param array<string,mixed> $raw Raw form data.
	 * @param string              $dashboard_origin Raw dashboard origin.
	 * @param int|null            $now Current timestamp override.
	 * @return array<string,mixed>|WP_Error
	 */
	public function create_pending_site( array $raw, $dashboard_origin, $now = null ) {
		$now              = null === $now ? time() : (int) $now;
		$label            = isset( $raw['site_label'] ) ? sanitize_text_field( (string) wp_unslash( $raw['site_label'] ) ) : '';
		$environment      = isset( $raw['environment'] ) ? sanitize_key( (string) wp_unslash( $raw['environment'] ) ) : 'production';
		$expected_origin  = $this->origins->normalize_public_https_origin( isset( $raw['expected_origin'] ) ? (string) wp_unslash( $raw['expected_origin'] ) : '' );
		$dashboard_origin = $this->origins->normalize_public_https_origin( $dashboard_origin );

		if ( '' === $label ) {
			return new WP_Error( 'site_label_required', __( 'Enter a site label before generating a pairing token.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( '' === $expected_origin ) {
			return new WP_Error( 'expected_origin_invalid', __( 'Enter a public HTTPS client origin before generating a pairing token.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( '' === $dashboard_origin ) {
			return new WP_Error( 'dashboard_origin_invalid', __( 'The dashboard must run from a public HTTPS origin before it can generate pairing tokens.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( ! in_array( $environment, array( 'production', 'staging', 'development', 'other' ), true ) ) {
			$environment = 'production';
		}

		$public_id  = $this->create_uuid();
		$expires_at = $now + self::DEFAULT_PAIRING_TTL_SECONDS;
		$material   = Alynt_Drime_Backups_Dashboard_Pairing_Tokens::create_pairing_token(
			$public_id,
			$dashboard_origin,
			$expected_origin,
			$expires_at
		);

		$site_id = $this->sites->create_pending(
			array(
				'public_id'           => $public_id,
				'site_uuid'           => null,
				'site_label'          => $label,
				'expected_origin'     => $expected_origin,
				'environment'         => $environment,
				'enrollment_status'   => 'pending',
				'overall_status'      => 'pending',
				'pairing_secret_hash' => $material['secret_hash'],
				'pairing_expires_at'  => gmdate( 'Y-m-d H:i:s', $expires_at ),
			)
		);

		if ( is_wp_error( $site_id ) ) {
			return $site_id;
		}

		if ( $site_id <= 0 ) {
			return new WP_Error( 'site_create_failed', __( 'The dashboard could not create the pending site record. Please try again before sharing a pairing token.', 'alynt-drime-backups-dashboard' ) );
		}

		return array(
			'site_id'                 => $site_id,
			'public_id'               => $public_id,
			'site_label'              => $label,
			'expected_origin'         => $expected_origin,
			'environment'             => $environment,
			'pairing_token'           => $material['token'],
			'pairing_expires_at'      => $material['expires_at'],
			'status_endpoint_preview' => $this->origins->status_endpoint_for_origin( $expected_origin ),
		);
	}

	/**
	 * Creates a UUID for the public pending enrollment identifier.
	 *
	 * @return string
	 */
	private function create_uuid() {
		if ( function_exists( 'wp_generate_uuid4' ) ) {
			return wp_generate_uuid4();
		}

		return sprintf(
			'%04x%04x-%04x-4%03x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ),
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff )
		);
	}
}
