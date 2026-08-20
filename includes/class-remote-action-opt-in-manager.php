<?php
/**
 * Remote action opt-in token workflow.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.15
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates display-once V2 action opt-in tokens for already enrolled sites.
 *
 * @since 0.1.15
 */
class Alynt_Drime_Backups_Dashboard_Remote_Action_Opt_In_Manager {
	const DEFAULT_TOKEN_TTL_SECONDS = 900;

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
	 * Credential vault.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Credential_Vault
	 */
	private $vault;

	/**
	 * Signer.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Signer
	 */
	private $signer;

	/**
	 * Constructor.
	 *
	 * @since 0.1.15
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Site_Repository|null      $sites Site repository.
	 * @param Alynt_Drime_Backups_Dashboard_Origin_Validator|null     $origins Origin validator.
	 * @param Alynt_Drime_Backups_Dashboard_Credential_Vault|null     $vault Credential vault.
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Signer|null $signer Signer.
	 */
	public function __construct( $sites = null, $origins = null, $vault = null, $signer = null ) {
		$this->sites   = $sites instanceof Alynt_Drime_Backups_Dashboard_Site_Repository ? $sites : new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$this->origins = $origins instanceof Alynt_Drime_Backups_Dashboard_Origin_Validator ? $origins : new Alynt_Drime_Backups_Dashboard_Origin_Validator();
		$this->vault   = $vault instanceof Alynt_Drime_Backups_Dashboard_Credential_Vault ? $vault : new Alynt_Drime_Backups_Dashboard_Credential_Vault();
		$this->signer  = $signer instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Signer ? $signer : new Alynt_Drime_Backups_Dashboard_Remote_Action_Signer();
	}

	/**
	 * Creates a display-once V2 action opt-in token for one enrolled site.
	 *
	 * @since 0.1.15
	 *
	 * @param int      $site_id Site ID.
	 * @param string   $dashboard_origin Dashboard origin.
	 * @param int|null $now Current timestamp override.
	 * @return array<string,mixed>|WP_Error
	 */
	public function create_opt_in_token( $site_id, $dashboard_origin, $now = null ) {
		$site_id = absint( $site_id );
		$now     = null === $now ? time() : (int) $now;

		if ( 0 === $site_id ) {
			return new WP_Error( 'action_opt_in_site_required', __( 'Choose an enrolled site before generating an action opt-in token.', 'alynt-drime-backups-dashboard' ) );
		}

		$site = $this->sites->get( $site_id );

		if ( ! is_array( $site ) ) {
			return new WP_Error( 'action_opt_in_site_not_found', __( 'The dashboard site record was not found.', 'alynt-drime-backups-dashboard' ) );
		}

		$dashboard_origin = $this->origins->normalize_public_https_origin( $dashboard_origin );
		$client_origin    = $this->origins->normalize_public_https_origin( isset( $site['expected_origin'] ) ? (string) $site['expected_origin'] : '' );
		$public_id        = isset( $site['public_id'] ) ? sanitize_text_field( (string) $site['public_id'] ) : '';
		$site_uuid        = isset( $site['site_uuid'] ) ? sanitize_text_field( (string) $site['site_uuid'] ) : '';

		if ( '' === $dashboard_origin ) {
			return new WP_Error( 'action_opt_in_dashboard_origin_invalid', __( 'The dashboard must run from a public HTTPS origin before it can generate action opt-in tokens.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( '' === $client_origin || '' === $public_id || '' === $site_uuid ) {
			return new WP_Error( 'action_opt_in_requires_enrollment', __( 'Complete read-only pairing before generating a V2 action opt-in token.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( empty( $site['polling_key_id'] ) || empty( $site['polling_secret_ciphertext'] ) ) {
			return new WP_Error( 'action_opt_in_requires_credentials', __( 'Active polling credentials are required before generating a V2 action opt-in token.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( ! $this->signer->is_supported() ) {
			return new WP_Error( 'action_signing_unavailable', __( 'Remote action signing is unavailable because PHP Sodium support is missing.', 'alynt-drime-backups-dashboard' ) );
		}

		$keys = $this->signer->create_key_pair();

		if ( is_wp_error( $keys ) ) {
			return $keys;
		}

		$ciphertext = $this->vault->encrypt( $keys['private_key'], 'action:' . $public_id );

		if ( is_wp_error( $ciphertext ) ) {
			return $ciphertext;
		}

		$expires_at = $now + self::DEFAULT_TOKEN_TTL_SECONDS;
		$token      = Alynt_Drime_Backups_Dashboard_Remote_Action_Opt_In_Tokens::format_token(
			array(
				'dashboard_origin'         => $dashboard_origin,
				'expected_client_origin'   => $client_origin,
				'dashboard_site_public_id' => $public_id,
				'site_uuid'                => $site_uuid,
				'action_key_id'            => $keys['key_id'],
				'action_public_key'        => $keys['public_key'],
				'expires_at'               => gmdate( 'c', $expires_at ),
			)
		);

		if ( is_wp_error( $token ) ) {
			return $token;
		}

		if ( ! $this->sites->store_action_signing_key( $site_id, $keys['key_id'], $ciphertext ) ) {
			return new WP_Error( 'action_key_store_failed', __( 'The dashboard could not store the encrypted action signing key.', 'alynt-drime-backups-dashboard' ) );
		}

		return array(
			'action'                   => 'generate_action_opt_in_token',
			'site_id'                  => $site_id,
			'dashboard_site_public_id' => $public_id,
			'site_uuid'                => $site_uuid,
			'expected_origin'          => $client_origin,
			'action_key_id'            => $keys['key_id'],
			'action_opt_in_token'      => $token,
			'action_token_expires_at'  => gmdate( 'c', $expires_at ),
			'allowed_actions'          => array( Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCAN_UPLOAD_NOW ),
		);
	}
}
