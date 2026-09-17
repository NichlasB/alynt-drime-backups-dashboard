<?php
/**
 * Dashboard status classifier.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classifies redacted uploader status without performing HTTP transport.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Status_Classifier {
	use Alynt_Drime_Backups_Dashboard_Status_Classifier_Backup_Sources;
	use Alynt_Drime_Backups_Dashboard_Status_Classifier_Helpers;

	const CATEGORY_PENDING         = 'pending';
	const CATEGORY_PAUSED          = 'paused';
	const CATEGORY_INCOMPATIBLE    = 'incompatible';
	const CATEGORY_NOT_REPORTING   = 'not_reporting';
	const CATEGORY_NEEDS_ATTENTION = 'needs_attention';
	const CATEGORY_NOT_CONFIGURED  = 'not_configured';
	const CATEGORY_WORKING         = 'working';

	const SUPPORTED_SCHEMA_VERSION    = 1;
	const DEFAULT_STALE_AFTER_SECONDS = 3600;
	const WPVIVID_POLICY_WINDOW       = 1296000; // 15 days.

	/**
	 * Dashboard-owned source policy.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Source_Policy
	 */
	private $source_policy;

	/**
	 * Constructor.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Source_Policy|null $source_policy Source policy store.
	 */
	public function __construct( $source_policy = null ) {
		$this->source_policy = $source_policy instanceof Alynt_Drime_Backups_Dashboard_Source_Policy ? $source_policy : new Alynt_Drime_Backups_Dashboard_Source_Policy();
	}

	/**
	 * Classifies one site using its latest snapshot.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string,mixed>      $site Site row.
	 * @param array<string,mixed>|null $snapshot Latest snapshot row.
	 * @param int|null                 $now Unix timestamp.
	 * @return array<string,string>
	 */
	public function classify( array $site, $snapshot = null, $now = null ) {
		$now = null === $now ? time() : (int) $now;

		if ( ! empty( $site['paused_at'] ) ) {
			return $this->result( self::CATEGORY_PAUSED, __( 'Polling is paused for this site.', 'alynt-drime-backups-dashboard' ) );
		}

		$site_status = isset( $site['overall_status'] ) ? (string) $site['overall_status'] : ( isset( $site['status'] ) ? (string) $site['status'] : '' );

		if ( self::CATEGORY_PENDING === $site_status ) {
			return $this->result( self::CATEGORY_PENDING, __( 'Waiting for the client site to opt in and complete pairing.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( empty( $snapshot ) ) {
			return $this->result( self::CATEGORY_NOT_REPORTING, __( 'No status snapshot has been received yet.', 'alynt-drime-backups-dashboard' ) );
		}

		$payload = $this->payload_from_snapshot( $snapshot );

		if ( empty( $payload ) ) {
			return $this->result( self::CATEGORY_NOT_REPORTING, __( 'The latest status snapshot could not be decoded.', 'alynt-drime-backups-dashboard' ) );
		}

		$schema = isset( $payload['schema_version'] ) ? (int) $payload['schema_version'] : ( isset( $snapshot['schema_version'] ) ? (int) $snapshot['schema_version'] : 0 );

		if ( self::SUPPORTED_SCHEMA_VERSION !== $schema ) {
			return $this->result( self::CATEGORY_INCOMPATIBLE, __( 'The client status schema is not supported by this dashboard version.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( $this->is_stale( $site, $snapshot, $now ) ) {
			return $this->result( self::CATEGORY_NOT_REPORTING, __( 'The last status snapshot is stale.', 'alynt-drime-backups-dashboard' ) );
		}

		$attention_message = $this->attention_message( $payload, $site );

		if ( '' !== $attention_message ) {
			return $this->result( self::CATEGORY_NEEDS_ATTENTION, $attention_message );
		}

		if ( $this->is_not_configured( $payload ) ) {
			return $this->result( self::CATEGORY_NOT_CONFIGURED, __( 'No supported backup source appears configured on the client site.', 'alynt-drime-backups-dashboard' ) );
		}

		return $this->result( self::CATEGORY_WORKING, __( 'The latest redacted status payload looks healthy.', 'alynt-drime-backups-dashboard' ) );
	}

	/**
	 * Builds a result.
	 *
	 * @param string $category Category slug.
	 * @param string $message Human-readable message.
	 * @return array<string,string>
	 */
	private function result( $category, $message ) {
		return array(
			'category' => $category,
			'label'    => $this->label( $category ),
			'message'  => $message,
		);
	}

	/**
	 * Gets a label for a category.
	 *
	 * @since 0.1.0
	 *
	 * @param string $category Category slug.
	 * @return string
	 */
	public function label( $category ) {
		$labels = array(
			self::CATEGORY_PENDING         => __( 'Pending', 'alynt-drime-backups-dashboard' ),
			self::CATEGORY_PAUSED          => __( 'Paused', 'alynt-drime-backups-dashboard' ),
			self::CATEGORY_INCOMPATIBLE    => __( 'Incompatible', 'alynt-drime-backups-dashboard' ),
			self::CATEGORY_NOT_REPORTING   => __( 'Not reporting', 'alynt-drime-backups-dashboard' ),
			self::CATEGORY_NEEDS_ATTENTION => __( 'Needs attention', 'alynt-drime-backups-dashboard' ),
			self::CATEGORY_NOT_CONFIGURED  => __( 'Not configured', 'alynt-drime-backups-dashboard' ),
			self::CATEGORY_WORKING         => __( 'Working', 'alynt-drime-backups-dashboard' ),
		);

		return isset( $labels[ $category ] ) ? $labels[ $category ] : $category;
	}
}
