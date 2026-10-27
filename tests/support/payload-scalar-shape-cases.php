<?php
/** Independent field/type cases for the approved top-level scalar contract. */
trait Alynt_Drime_Backups_Dashboard_Test_Payload_Scalar_Cases {
	public static function scalar_groups() {
		return array(
			'schema' => array( 'schema_version' ), 'uuid' => array( 'site_uuid' ),
			'number' => array( 'queue_count', 'uploaded_count', 'failed_count', 'warning_count', 'last_runner_at', 'last_scheduled_scan_at', 'last_wp_cli_scan_at' ),
			'boolean' => array( 'active_upload', 'auto_scan_enabled', 'server_cron_expected', 'server_outbox_configured', 'server_outbox_readable', 'wpvivid_override_configured', 'old_wpvivid_uploader_active', 'wp_cron_disabled' ),
			'text' => array( 'plugin_version', 'cron_status', 'cron_reason', 'last_runner' ),
		);
	}

	public static function invalid_shapes() {
		$cases = array();
		foreach ( self::scalar_groups() as $group => $fields ) {
			$code = 'schema' === $group ? 'schema_unsupported' : ( 'uuid' === $group ? 'site_uuid_mismatch' : 'payload_invalid' );
			foreach ( $fields as $field ) {
				foreach ( array( '[]', '[1]', '{"value":1}', '{"nested":{"value":1}}', 'object', 'stringable' ) as $shape ) {
					$cases[ $field . ':' . $shape ] = array( $field, $shape, $code );
				}
			}
		}
		return $cases;
	}

	public static function legacy_scalars() {
		$vectors = array(
			'number' => array( array( 0, 0 ), array( '0', 0 ), array( 2, 2 ), array( '2', 2 ), array( 2.75, 2 ), array( -2, 2 ), array( true, 1 ), array( false, 0 ), array( 'false', 0 ) ),
			'boolean' => array( array( 0, false ), array( '0', false ), array( 2, true ), array( '2', true ), array( 2.75, true ), array( -2, true ), array( true, true ), array( false, false ), array( 'false', true ) ),
			'text' => array( array( 'fixture', 'fixture' ), array( 0, '0' ), array( true, '1' ) ),
			'schema' => array( array( 1, 1 ), array( '1', 1 ), array( true, 1 ), array( 1.75, 1 ) ),
			'uuid' => array( array( ' 11111111-1111-4111-8111-111111111111 ', '11111111-1111-4111-8111-111111111111' ) ),
		);
		$cases = array();
		foreach ( self::scalar_groups() as $group => $fields ) {
			foreach ( $fields as $field ) {
				foreach ( $vectors[ $group ] as $index => $vector ) {
					$cases[ $field . ':' . $index ] = array( $field, $vector[0], $vector[1] );
				}
			}
		}
		return $cases;
	}

	public static function absent_scalars() {
		$cases = array();
		foreach ( self::scalar_groups() as $group => $fields ) {
			foreach ( $fields as $field ) {
				$code = 'schema' === $group ? 'schema_unsupported' : ( 'uuid' === $group ? 'site_uuid_mismatch' : '' );
				if ( in_array( $field, array( 'plugin_version', 'cron_status' ), true ) ) $code = 'payload_invalid';
				$default = 'number' === $group ? 0 : ( 'boolean' === $group ? false : '' );
				foreach ( array( 'missing', 'null' ) as $state ) $cases[ $field . ':' . $state ] = array( $field, $state, $code, $default );
			}
		}
		return $cases;
	}
}
