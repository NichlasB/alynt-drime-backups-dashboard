<?php
/**
 * Fake action repository for admin schedule-management rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-schedule-management-action-lookups.php';

/**
 * Fake action repository for schedule apply and rollback-preview rendering tests.
 */
class Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Actions extends Alynt_Drime_Backups_Dashboard_Remote_Action_Repository {
	use Alynt_Drime_Backups_Dashboard_Schedule_Management_Action_Lookups;

	/**
	 * Recent action rows.
	 *
	 * @param int $site_id Site ID.
	 * @param int $limit Limit.
	 * @return array<int,array<string,mixed>>
	 */
	public function recent_for_site( $site_id, $limit = 10 ) {
		unset( $site_id, $limit );

		return array(
			array(
				'public_id'   => '22222222-2222-4222-8222-222222222222',
				'action_type' => 'schedule_preview',
				'state'       => 'succeeded',
			),
		);
	}

}
