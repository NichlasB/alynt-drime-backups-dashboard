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
		$context   = $this->site_status_context();
		$sites     = $context['sites'];
		$snapshots = $context['snapshots'];

		echo '<section aria-labelledby="adbd-sites-heading">';
		echo '<h2 id="adbd-sites-heading">' . esc_html__( 'Sites', 'alynt-drime-backups-dashboard' ) . '</h2>';

		if ( empty( $sites ) ) {
			$this->render_empty_state();
			echo '</section>';
			return;
		}

		echo '<p class="adbd-screen-intro">';
		printf(
			esc_html(
				/* translators: %d: number of dashboard sites. */
				_n( '%d paired client site. Status reflects its most recent redacted snapshot, not a live connection.', '%d paired client sites. Status reflects each site\'s most recent redacted snapshot, not a live connection.', count( $sites ), 'alynt-drime-backups-dashboard' )
			),
			esc_html( number_format_i18n( count( $sites ) ) )
		);
		echo '</p>';

		$this->render_status_summary( $context['counts'], count( $sites ), $context['attention_count'] );
		$this->render_sites_table( $sites, $snapshots, $context['statuses'] );
		echo '<p class="description adbd-table-note">' . esc_html__( 'Check Status Now re-polls the site\'s fixed authenticated read-only status endpoint. It does not start, stop, or alter a backup.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '</section>';
	}

	/**
	 * Gets the request-local site, snapshot, and classification context.
	 *
	 * @return array<string,mixed>
	 */
	private function site_status_context() {
		if ( is_array( $this->site_status_context ) ) {
			return $this->site_status_context;
		}

		$sites     = $this->sites->all();
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

		$this->site_status_context = array(
			'sites'           => $sites,
			'snapshots'       => $snapshots,
			'statuses'        => $statuses,
			'counts'          => $counts,
			'attention_count' => $attention_count,
		);

		return $this->site_status_context;
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
}
