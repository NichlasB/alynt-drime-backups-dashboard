<?php
/**
 * Poller scheduling tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

if ( ! function_exists( 'wp_schedule_event' ) ) {
	/**
	 * Minimal wp_schedule_event shim.
	 *
	 * @param int          $timestamp Timestamp.
	 * @param string       $recurrence Recurrence.
	 * @param string       $hook Hook.
	 * @param array<mixed> $args Args.
	 * @param bool         $wp_error Whether to return WP_Error on failure.
	 * @return bool|WP_Error
	 */
	function wp_schedule_event( $timestamp, $recurrence, $hook, $args = array(), $wp_error = false ) {
		unset( $timestamp, $args, $wp_error );

		$GLOBALS['adbd_test_scheduled_events'][] = array(
			'recurrence' => $recurrence,
			'hook'       => $hook,
		);

		return array_key_exists( 'adbd_test_schedule_event_result', $GLOBALS ) ? $GLOBALS['adbd_test_schedule_event_result'] : true;
	}
}

/**
 * Poller scheduling tests.
 */
class PollerSchedulingTest extends TestCase {
	/**
	 * Clears scheduler globals after each test.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		unset( $GLOBALS['adbd_test_scheduled_events'], $GLOBALS['adbd_test_schedule_event_result'] );
	}

	/**
	 * Scheduler returns a structured error when WordPress cannot register an event.
	 *
	 * @return void
	 */
	public function test_schedule_event_returns_error_when_cron_registration_fails(): void {
		$GLOBALS['adbd_test_schedule_event_result'] = false;

		$result = $this->invoke_schedule_event( Alynt_Drime_Backups_Dashboard_Poller::CRON_RECURRENCE, Alynt_Drime_Backups_Dashboard_Poller::CRON_HOOK );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'dashboard_cron_schedule_failed', $result->get_error_code() );
	}

	/**
	 * Scheduler registers poll and cleanup events when none are scheduled.
	 *
	 * @return void
	 */
	public function test_schedule_event_returns_true_when_cron_registration_succeeds(): void {
		$result = $this->invoke_schedule_event( 'daily', Alynt_Drime_Backups_Dashboard_Poller::CLEANUP_HOOK );

		$this->assertTrue( $result );
		$this->assertSame(
			array(
				array(
					'recurrence' => 'daily',
					'hook'       => Alynt_Drime_Backups_Dashboard_Poller::CLEANUP_HOOK,
				),
			),
			$GLOBALS['adbd_test_scheduled_events']
		);
	}

	/**
	 * Invokes the private scheduler helper.
	 *
	 * @param string $recurrence Recurrence.
	 * @param string $hook Hook.
	 * @return true|WP_Error
	 */
	private function invoke_schedule_event( $recurrence, $hook ) {
		$method = new ReflectionMethod( Alynt_Drime_Backups_Dashboard_Poller::class, 'schedule_event' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		return $method->invoke( null, time() + 60, $recurrence, $hook );
	}
}
