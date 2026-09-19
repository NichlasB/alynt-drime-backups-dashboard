<?php
/**
 * Admin page Sites list shell.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the dashboard Sites list screen and request-local status context.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List {
	/**
	 * Renders the Sites shell.
	 *
	 * @return void
	 */
	private function render_sites_shell() {
		$show_archived  = $this->show_archived_records();
		$context        = $this->site_status_context( $show_archived ? 'archived' : 'visible' );
		$sites          = $context['sites'];
		$snapshots      = $context['snapshots'];
		$archived_count = count(
			$this->sites->all(
				array(
					'archived' => 'only',
					'limit'    => 500,
				)
			)
		);

		echo '<section aria-labelledby="adbd-sites-heading">';
		echo '<h2 id="adbd-sites-heading">' . esc_html( $show_archived ? __( 'Archived Local Records', 'alynt-drime-backups-dashboard' ) : __( 'Sites', 'alynt-drime-backups-dashboard' ) ) . '</h2>';

		$this->render_archived_records_toggle( $show_archived, $archived_count );

		if ( empty( $sites ) ) {
			if ( $show_archived ) {
				echo '<div class="adbd-empty-state"><span class="dashicons dashicons-archive" aria-hidden="true"></span><h3>' . esc_html__( 'No Archived Local Records', 'alynt-drime-backups-dashboard' ) . '</h3><p>' . esc_html__( 'Archived dashboard records remain retained locally for audit/history. None are archived right now.', 'alynt-drime-backups-dashboard' ) . '</p></div>';
			} else {
				$this->render_empty_state();
			}
			echo '</section>';
			return;
		}

		echo '<p class="adbd-screen-intro">';
		if ( $show_archived ) {
			printf(
				esc_html(
					/* translators: %d: number of archived dashboard records. */
					_n( '%d archived local dashboard record. Archived records are retained for audit/history and are not polled.', '%d archived local dashboard records. Archived records are retained for audit/history and are not polled.', count( $sites ), 'alynt-drime-backups-dashboard' )
				),
				esc_html( number_format_i18n( count( $sites ) ) )
			);
		} else {
			printf(
				esc_html(
					/* translators: %d: number of dashboard sites. */
					_n( '%d paired client site. Status reflects its most recent redacted snapshot, not a live connection.', '%d paired client sites. Status reflects each site\'s most recent redacted snapshot, not a live connection.', count( $sites ), 'alynt-drime-backups-dashboard' )
				),
				esc_html( number_format_i18n( count( $sites ) ) )
			);
		}
		echo '</p>';

		$this->render_status_summary( $context['counts'], count( $sites ), $context['attention_count'] );
		$this->render_sites_table( $sites, $snapshots, $context['statuses'] );
		echo '<p class="description adbd-table-note">' . esc_html__( 'Check Now re-polls the site\'s fixed authenticated read-only status endpoint. It does not start, stop, or alter a backup.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '</section>';
	}

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

	/**
	 * Renders the archived-records toggle.
	 *
	 * @param bool $show_archived Whether archived records are currently shown.
	 * @param int  $archived_count Archived record count.
	 * @return void
	 */
	private function render_archived_records_toggle( $show_archived, $archived_count ) {
		$url = add_query_arg(
			array(
				'page' => self::MENU_SLUG,
				'tab'  => 'sites',
			),
			admin_url( 'tools.php' )
		);

		if ( ! $show_archived ) {
			$url = add_query_arg( 'archived', '1', $url );
		}

		echo '<p class="adbd-view-toggle">';
		if ( $show_archived ) {
			echo '<a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Back to Active Sites', 'alynt-drime-backups-dashboard' ) . '</a>';
		} else {
			printf(
				'<a class="button" href="%1$s">%2$s</a>',
				esc_url( $url ),
				esc_html(
					sprintf(
						/* translators: %d: archived local dashboard record count. */
						_n( 'View Archived Record (%d)', 'View Archived Records (%d)', $archived_count, 'alynt-drime-backups-dashboard' ),
						(int) $archived_count
					)
				)
			);
		}
		echo '<span class="description">' . esc_html__( 'Archive is dashboard-local visibility only; it does not contact client sites, change backups, or alter Drime.', 'alynt-drime-backups-dashboard' ) . '</span></p>';
	}
}
