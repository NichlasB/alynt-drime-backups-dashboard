<?php
/**
 * Dashboard diagnostics.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds redacted operator diagnostics for the dashboard admin UI.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Diagnostics {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Scheduler;
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Support;
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Site_Metrics;

	/**
	 * Site repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Site_Repository
	 */
	private $sites;

	/**
	 * Snapshot repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Snapshot_Repository
	 */
	private $snapshots;

	/**
	 * Status classifier.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Status_Classifier
	 */
	private $classifier;

	/**
	 * Structured event log.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Event_Log
	 */
	private $event_log;

	/**
	 * Constructor.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Site_Repository|null     $sites Site repository.
	 * @param Alynt_Drime_Backups_Dashboard_Snapshot_Repository|null $snapshots Snapshot repository.
	 * @param Alynt_Drime_Backups_Dashboard_Status_Classifier|null   $classifier Status classifier.
	 * @param Alynt_Drime_Backups_Dashboard_Event_Log|null           $event_log Event log.
	 */
	public function __construct( $sites = null, $snapshots = null, $classifier = null, $event_log = null ) {
		$this->sites      = $sites instanceof Alynt_Drime_Backups_Dashboard_Site_Repository ? $sites : new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$this->snapshots  = $snapshots instanceof Alynt_Drime_Backups_Dashboard_Snapshot_Repository ? $snapshots : new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
		$this->classifier = $classifier instanceof Alynt_Drime_Backups_Dashboard_Status_Classifier ? $classifier : new Alynt_Drime_Backups_Dashboard_Status_Classifier();
		$this->event_log  = $event_log instanceof Alynt_Drime_Backups_Dashboard_Event_Log ? $event_log : new Alynt_Drime_Backups_Dashboard_Event_Log();
	}

	/**
	 * Collects safe diagnostics for operator display.
	 *
	 * @return array<string,mixed>
	 */
	public function collect() {
		$sites     = $this->sites->all();
		$site_ids  = $this->site_ids( $sites );
		$snapshots = $this->snapshots->latest_by_site_ids( $site_ids );
		$now       = time();

		return array(
			'scheduler' => $this->scheduler_diagnostics( $now ),
			'counts'    => $this->count_diagnostics( $sites, $snapshots, $now ),
			'recent'    => $this->recent_poll_outcomes( $sites ),
			'logging'   => $this->logging_diagnostics(),
			'support'   => $this->support_summary( $sites, $snapshots, $now ),
		);
	}

	/**
	 * Gets the structured event log.
	 *
	 * @return Alynt_Drime_Backups_Dashboard_Event_Log
	 */
	public function event_log() {
		return $this->event_log;
	}

	/**
	 * Builds logging diagnostics.
	 *
	 * @return array<string,mixed>
	 */
	private function logging_diagnostics() {
		$settings = $this->event_log->settings();

		return array(
			'settings' => $settings,
			'summary'  => $this->event_log->summary(),
			'events'   => $this->event_log->recent_events( 25 ),
		);
	}
}
