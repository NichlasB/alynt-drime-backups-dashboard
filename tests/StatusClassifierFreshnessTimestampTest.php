<?php
/**
 * Isolated snapshot-freshness timestamp regressions.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/status-classifier-test-bootstrap.php';

/**
 * Exercises the real classifier with fixed-clock DATA, not WordPress storage.
 */
class StatusClassifierFreshnessTimestampTest extends TestCase {

	use Alynt_Drime_Backups_Dashboard_Status_Classifier_Test_Fixtures;

	const NOW = 1700000300; // 2023-11-14 22:18:20 UTC.

	/**
	 * Every supplied time must be valid and fresh; neither can mask the other.
	 *
	 * @dataProvider freshness_cases
	 * @param mixed  $seen Site last-seen value.
	 * @param mixed  $observed Snapshot observed value.
	 * @param string $expected Expected category.
	 */
	public function test_freshness_requires_valid_non_future_evidence( $seen, $observed, $expected ) {
		$site                 = $this->active_site();
		$site['last_seen_at'] = $seen;
		$snapshot             = $this->snapshot( $this->healthy_payload() );
		$snapshot['observed_at'] = $observed;
		unset( $snapshot['captured_at'] );
		$result = ( new Alynt_Drime_Backups_Dashboard_Status_Classifier() )->classify( $site, $snapshot, self::NOW );
		$this->assertSame( $expected, $result['category'] );
	}

	/** @return array<string,array<mixed>> */
	public static function freshness_cases() {
		$fresh = '2023-11-14 22:17:20';
		$stale = gmdate( 'Y-m-d H:i:s', self::NOW - 3601 );
		$cases = array(
			'fresh SQL pair'       => array( $fresh, $fresh, 'working' ),
			'current second'       => array( self::NOW, (string) self::NOW, 'working' ),
			'exact age limit'      => array( self::NOW - 3600, self::NOW - 3600, 'working' ),
			'stale site'           => array( $stale, $fresh, 'not_reporting' ),
			'stale snapshot'       => array( $fresh, $stale, 'not_reporting' ),
			'missing site'         => array( null, $fresh, 'working' ),
			'missing snapshot'     => array( $fresh, null, 'working' ),
			'both missing'         => array( '', null, 'not_reporting' ),
			'UTC ISO'              => array( $fresh, '2023-11-14T22:17:20Z', 'working' ),
			'offset equivalent'    => array( $fresh, '2023-11-15T00:17:20+02:00', 'working' ),
			'fractional ISO'       => array( $fresh, '2023-11-14T22:17:20.123456Z', 'working' ),
			'valid leap day stale' => array( $fresh, '2020-02-29 12:00:00', 'not_reporting' ),
		);
		$invalid = array(
			'future'          => self::NOW + 1,
			'far future'      => '2030-01-01 00:00:00',
			'relative'        => 'now',
			'relative suffix' => $fresh . ' +1 day',
			'impossible day'  => '2023-02-31 22:17:20',
			'bad leap day'    => '2023-02-29 22:17:20',
			'bad time'        => '2023-11-14 24:00:00',
			'date only'       => '2023-11-14',
			'zero year'       => '0000-11-14 22:17:20',
			'bad offset'      => '2023-11-14T22:17:20+99:99',
			'whitespace'      => ' ' . $fresh,
			'embedded NUL'    => $fresh . "\0",
			'oversized'       => str_repeat( '9', 10000 ),
			'exponent'        => '170000024e1',
			'fraction number' => 1700000240.5,
			'compound'        => array( $fresh ),
			'object'          => new stdClass(),
			'boolean'         => true,
			'negative'        => -1,
			'epoch'           => 0,
		);
		foreach ( $invalid as $name => $value ) {
			$cases[ $name . ' site' ]     = array( $value, $fresh, 'not_reporting' );
			$cases[ $name . ' snapshot' ] = array( $fresh, $value, 'not_reporting' );
		}
		return $cases;
	}

	/** SQL times are UTC even when the PHP default timezone differs. */
	public function test_sql_time_is_independent_of_default_timezone() {
		$previous = date_default_timezone_get();
		try {
			foreach ( array( 'Pacific/Honolulu', 'Asia/Tokyo' ) as $timezone ) {
				date_default_timezone_set( $timezone );
				$site     = $this->active_site( '2023-11-14 22:17:20' );
				$snapshot = $this->snapshot( $this->healthy_payload(), '2023-11-14 22:17:20' );
				$result   = ( new Alynt_Drime_Backups_Dashboard_Status_Classifier() )->classify( $site, $snapshot, self::NOW );
				$this->assertSame( 'working', $result['category'], $timezone );
			}
		} finally {
			date_default_timezone_set( $previous );
		}
	}

	/** Legacy captured_at remains a fallback, never an override for bad observed_at. */
	public function test_legacy_capture_fallback_preserves_primary_time_validation() {
		$classifier = new Alynt_Drime_Backups_Dashboard_Status_Classifier();
		$site       = $this->active_site( '' );
		$snapshot   = $this->snapshot( $this->healthy_payload(), '2023-11-14 22:17:20' );
		$this->assertSame( 'working', $classifier->classify( $site, $snapshot, self::NOW )['category'] );
		$snapshot['observed_at'] = 'tomorrow';
		$this->assertSame( 'not_reporting', $classifier->classify( $site, $snapshot, self::NOW )['category'] );
	}

	/** A rolled invalid date must fail even when PHP would normalize it to now. */
	public function test_calendar_validation_is_not_merely_an_age_or_future_check() {
		$classifier = new Alynt_Drime_Backups_Dashboard_Status_Classifier();
		$now        = ( new DateTimeImmutable( '2023-03-03T12:00:00Z' ) )->getTimestamp();
		$site       = $this->active_site( '2023-03-03 12:00:00' );
		$snapshot   = $this->snapshot( $this->healthy_payload(), '2023-02-31 12:00:00' );
		$this->assertSame( 'not_reporting', $classifier->classify( $site, $snapshot, $now )['category'] );
		$snapshot['captured_at'] = '2023-03-03 12:00:00';
		$this->assertSame( 'working', $classifier->classify( $site, $snapshot, $now )['category'] );
	}
}
