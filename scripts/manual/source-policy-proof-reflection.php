<?php
/**
 * Private proof composition compatibility; never production provisioning.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Composes private proof inputs without changing reflection visibility globally.
 *
 * @param object $receiver Proof-owned receiver.
 * @param string $name Existing composition field.
 * @param mixed  $value Fixed proof input.
 * @return void
 */
function adbb_bind( $receiver, $name, $value ) {
	$property = new ReflectionProperty( $receiver, $name );
	if ( PHP_VERSION_ID < 80100 ) {
		$property->setAccessible( true );
	}
	$property->setValue( $receiver, $value );
}

/** Invokes only the fixed owner's actual handle-free constructor.
 *
 * @param ReflectionClass $receiver_class Exact dormant receiver class.
 * @return object Uninitialized native owner, not an active connection.
 * @throws RuntimeException For an unrelated receiver.
 */
function adbb_construct( ReflectionClass $receiver_class ) {
	if ( 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection' !== $receiver_class->getName() ) {
		throw new RuntimeException( 'Fixed proof receiver required' );
	}
	$receiver    = $receiver_class->newInstanceWithoutConstructor();
	$constructor = $receiver_class->getConstructor();
	if ( PHP_VERSION_ID < 80100 ) {
		$constructor->setAccessible( true );
	}
	$constructor->invoke( $receiver );
	return $receiver;
}
