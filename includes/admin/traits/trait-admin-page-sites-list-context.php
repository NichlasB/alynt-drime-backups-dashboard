<?php
/**
 * Admin page Sites list context helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.59
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds request-local Sites list status context.
 *
 * @since 0.1.59
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Context {
	/**
	 * Gets the request-local site, snapshot, and classification context.
	 *
	 * @param string $visibility Visible or archived record context.
	 * @return array<string,mixed>
	 */
	private function site_status_context( $visibility = 'visible' ) {
		$visibility = 'archived' === $visibility ? 'archived' : 'visible';

		if ( is_array( $this->site_status_context ) && isset( $this->site_status_context[ $visibility ] ) ) {
			return $this->site_status_context[ $visibility ];
		}

		$sites     = 'archived' === $visibility ? $this->sites->all(
			array(
				'archived' => 'only',
				'limit'    => 500,
			)
		) : $this->without_superseded_revoked_sites( $this->sites->all( array( 'archived' => 'exclude' ) ) );
		$snapshots = $this->snapshots->latest_by_site_ids( wp_list_pluck( $sites, 'id' ) );
		$statuses  = array();
		$counts    = array(
			'working'         => 0,
			'pending'         => 0,
			'paused'          => 0,
			'incompatible'    => 0,
			'not_reporting'   => 0,
			'needs_attention' => 0,
			'not_configured'  => 0,
		);

		foreach ( $sites as $site ) {
			$site_id              = (int) $site['id'];
			$snapshot             = isset( $snapshots[ $site_id ] ) ? $snapshots[ $site_id ] : null;
			$status               = $this->classifier->classify( $site, $snapshot );
			$statuses[ $site_id ] = $status;

			if ( isset( $counts[ $status['category'] ] ) ) {
				++$counts[ $status['category'] ];
			}
		}

		$attention_count = $counts['incompatible'] + $counts['not_reporting'] + $counts['needs_attention'] + $counts['not_configured'];

		if ( ! is_array( $this->site_status_context ) ) {
			$this->site_status_context = array();
		}

		$this->site_status_context[ $visibility ] = array(
			'sites'           => $sites,
			'snapshots'       => $snapshots,
			'statuses'        => $statuses,
			'counts'          => $counts,
			'attention_count' => $attention_count,
		);

		return $this->site_status_context[ $visibility ];
	}

	/**
	 * Removes revoked rows that have been superseded by an active row for the same origin.
	 *
	 * The dashboard preserves revoked rows in storage for audit/history, but the
	 * main Sites tab should not show a stale revoked duplicate next to the
	 * healthy re-enrolled row for the same client origin.
	 *
	 * @param array<int,array<string,mixed>> $sites Sites.
	 * @return array<int,array<string,mixed>>
	 */
	private function without_superseded_revoked_sites( array $sites ) {
		$active_origins = array();

		foreach ( $sites as $site ) {
			$status = isset( $site['enrollment_status'] ) ? sanitize_key( $site['enrollment_status'] ) : '';

			if ( ! in_array( $status, array( 'active', 'awaiting_first_poll' ), true ) ) {
				continue;
			}

			$origin = $this->normalized_expected_origin( isset( $site['expected_origin'] ) ? $site['expected_origin'] : '' );
			if ( '' !== $origin ) {
				$active_origins[ $origin ] = true;
			}
		}

		if ( empty( $active_origins ) ) {
			return $sites;
		}

		$filtered = array();

		foreach ( $sites as $site ) {
			$status = isset( $site['enrollment_status'] ) ? sanitize_key( $site['enrollment_status'] ) : '';
			$origin = $this->normalized_expected_origin( isset( $site['expected_origin'] ) ? $site['expected_origin'] : '' );

			if ( 'revoked' === $status && '' !== $origin && isset( $active_origins[ $origin ] ) ) {
				continue;
			}

			$filtered[] = $site;
		}

		return $filtered;
	}

	/**
	 * Normalizes a stored expected origin for duplicate-row comparisons.
	 *
	 * @param mixed $origin Expected origin.
	 * @return string
	 */
	private function normalized_expected_origin( $origin ) {
		return rtrim( strtolower( trim( (string) $origin ) ), '/' );
	}

	/**
	 * Counts sites represented by the Attention tab.
	 *
	 * @return int
	 */
	private function attention_count() {
		$context = $this->site_status_context();

		return isset( $context['attention_count'] ) ? (int) $context['attention_count'] : 0;
	}

	/**
	 * Determines whether the Sites tab should show archived local records.
	 *
	 * @return bool
	 */
	private function show_archived_records() {
		return isset( $_GET['archived'] ) && '1' === sanitize_key( wp_unslash( $_GET['archived'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only presentation filter.
	}
}
