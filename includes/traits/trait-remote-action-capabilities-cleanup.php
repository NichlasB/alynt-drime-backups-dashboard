<?php
/**
 * Remote action cleanup-preview capability helper trait.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.51
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitizes cleanup-preview capability and result payload sections.
 *
 * @since 0.1.51
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Cleanup {
	/**
	 * Sanitizes cleanup-preview capability reporting.
	 *
	 * @param mixed $capability Cleanup capability payload.
	 * @return array<string,mixed>
	 */
	private function cleanup_management( $capability ) {
		if ( ! is_array( $capability ) ) {
			return array();
		}

		if ( self::PROTOCOL_VERSION !== absint( isset( $capability['protocol_version'] ) ? $capability['protocol_version'] : 0 ) ) {
			return array();
		}

		$scope                    = isset( $capability['scope'] ) ? sanitize_key( (string) $capability['scope'] ) : '';
		$supported_categories     = $this->cleanup_categories( isset( $capability['supported_categories'] ) ? $capability['supported_categories'] : array() );
		$apply_supported          = ! empty( $capability['apply_supported'] );
		$remote_cleanup_available = ! empty( $capability['remote_cleanup_available'] ) || ! empty( $capability['cleanup_apply_available'] ) || ! empty( $capability['drime_cleanup_available'] ) || ! empty( $capability['backup_deletion_available'] );
		$preview_supported        = ! empty( $capability['preview_supported'] ) && ! $apply_supported && ! $remote_cleanup_available;

		return array(
			'protocol_version'           => self::PROTOCOL_VERSION,
			'capability_version'         => $this->non_negative_int( $capability, 'capability_version' ),
			'enabled'                    => ! empty( $capability['enabled'] ) && $preview_supported && self::CLEANUP_SCOPE_SAFE_LOCAL === $scope && ! empty( $supported_categories ),
			'preview_supported'          => $preview_supported,
			'apply_supported'            => false,
			'scope'                      => self::CLEANUP_SCOPE_SAFE_LOCAL === $scope ? $scope : '',
			'supported_categories'       => $supported_categories,
			'requires_fresh_preview'     => ! empty( $capability['requires_fresh_preview'] ),
			'max_preview_age_seconds'    => $this->non_negative_int( $capability, 'max_preview_age_seconds' ),
			'paths_exposed'              => false,
			'remote_cleanup_available'   => false,
			'cleanup_apply_available'    => false,
			'drime_cleanup_available'    => false,
			'backup_deletion_available'  => false,
			'restore_actions_available'  => false,
			'credential_actions_allowed' => false,
		);
	}

	/**
	 * Gets whether sanitized capabilities allow a cleanup preview action.
	 *
	 * @since 0.1.51
	 *
	 * @param array<string,mixed> $capabilities Sanitized capabilities.
	 * @return bool
	 */
	public function supports_cleanup_preview_action( array $capabilities ) {
		$cleanup_management = isset( $capabilities['cleanup_management'] ) && is_array( $capabilities['cleanup_management'] ) ? $capabilities['cleanup_management'] : array();

		return ! empty( $capabilities['enabled'] )
			&& ! empty( $capabilities['sodium_available'] )
			&& ! empty( $capabilities['allowed_actions'] )
			&& in_array( self::ACTION_CLEANUP_PREVIEW, (array) $capabilities['allowed_actions'], true )
			&& ! empty( $cleanup_management['enabled'] )
			&& ! empty( $cleanup_management['preview_supported'] )
			&& empty( $cleanup_management['apply_supported'] )
			&& self::CLEANUP_SCOPE_SAFE_LOCAL === ( isset( $cleanup_management['scope'] ) ? (string) $cleanup_management['scope'] : '' )
			&& in_array( self::CLEANUP_CATEGORY_UPLOADER_TEMP, isset( $cleanup_management['supported_categories'] ) ? (array) $cleanup_management['supported_categories'] : array(), true );
	}

	/**
	 * Sanitizes supported cleanup categories.
	 *
	 * @param mixed $categories Category list.
	 * @return array<int,string>
	 */
	private function cleanup_categories( $categories ) {
		if ( ! is_array( $categories ) ) {
			return array();
		}

		$clean = array();

		foreach ( array_slice( $categories, 0, self::MAX_CLEANUP_CATEGORIES ) as $category ) {
			$category = sanitize_key( (string) $category );

			if ( self::CLEANUP_CATEGORY_UPLOADER_TEMP === $category && ! in_array( $category, $clean, true ) ) {
				$clean[] = $category;
			}
		}

		return $clean;
	}

	/**
	 * Sanitizes a client-reported cleanup preview result.
	 *
	 * @param mixed $preview Cleanup preview result.
	 * @return array<string,mixed>
	 */
	private function cleanup_preview( $preview ) {
		if ( ! is_array( $preview ) ) {
			return array();
		}

		return array(
			'preview_action_id'    => isset( $preview['preview_action_id'] ) ? $this->sanitize_uuid( (string) $preview['preview_action_id'] ) : '',
			'preview_fingerprint'  => isset( $preview['preview_fingerprint'] ) ? $this->sha256_or_empty( (string) $preview['preview_fingerprint'] ) : '',
			'capability_version'   => $this->non_negative_int( $preview, 'capability_version' ),
			'scope'                => self::CLEANUP_SCOPE_SAFE_LOCAL === ( isset( $preview['scope'] ) ? sanitize_key( (string) $preview['scope'] ) : '' ) ? self::CLEANUP_SCOPE_SAFE_LOCAL : '',
			'preview_created_at'   => isset( $preview['preview_created_at'] ) ? sanitize_text_field( (string) $preview['preview_created_at'] ) : '',
			'expires_at'           => isset( $preview['expires_at'] ) ? sanitize_text_field( (string) $preview['expires_at'] ) : '',
			'categories'           => $this->cleanup_preview_categories( isset( $preview['categories'] ) ? $preview['categories'] : array() ),
			'total_eligible_count' => $this->non_negative_int( $preview, 'total_eligible_count' ),
			'total_approx_bytes'   => $this->non_negative_int( $preview, 'total_approx_bytes' ),
			'apply_supported'      => false,
		);
	}

	/**
	 * Sanitizes cleanup-preview category summaries.
	 *
	 * @param mixed $categories Category summaries.
	 * @return array<int,array<string,mixed>>
	 */
	private function cleanup_preview_categories( $categories ) {
		if ( ! is_array( $categories ) ) {
			return array();
		}

		$clean = array();

		foreach ( array_slice( $categories, 0, self::MAX_CLEANUP_CATEGORIES ) as $category ) {
			if ( ! is_array( $category ) ) {
				continue;
			}

			$category_id = isset( $category['category'] ) ? sanitize_key( (string) $category['category'] ) : '';
			if ( self::CLEANUP_CATEGORY_UPLOADER_TEMP !== $category_id ) {
				continue;
			}

			$clean[] = array(
				'category'       => $category_id,
				'eligible_count' => $this->non_negative_int( $category, 'eligible_count' ),
				'approx_bytes'   => $this->non_negative_int( $category, 'approx_bytes' ),
				'age_band'       => isset( $category['age_band'] ) ? sanitize_key( (string) $category['age_band'] ) : '',
				'reason_code'    => isset( $category['reason_code'] ) ? sanitize_key( (string) $category['reason_code'] ) : '',
			);
		}

		return $clean;
	}
}
