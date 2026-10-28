<?php
/** Repository-only declaration/source checks. No installed core, driver or SQL. */
if ( defined( 'ABSPATH' ) ) return;
if ( PHP_SAPI !== 'cli' || 2 !== count( $argv ) || '--isolated-observer-binding' !== $argv[1] || extension_loaded( 'mysqli' ) || extension_loaded( 'pdo_mysql' ) ) exit( 1 );
define( 'ABSPATH', dirname( __DIR__, 2 ) . '/' );
define( 'ADB_POLICY_ISOLATED', true );
require ABSPATH . 'vendor/autoload.php';
require ABSPATH . 'tests/support/source-policy-isolated-wordpress.php';
require ABSPATH . 'scripts/manual/source-policy-ambient-proof-contract.php';
if ( defined( 'ADB_POLICY_ISOLATED' ) ) {
 /** The source inspection must not execute the actual fixture callback body. */
 function adbb_check() { throw new RuntimeException( 'No fixture execution permitted' ); }
}
require ABSPATH . 'scripts/manual/source-policy-adapter-proof-cases.php';
foreach ( array( 'class-source-policy.php', 'class-source-policy-native-profile.php', 'class-source-policy-bootstrap-profile.php' ) as $file ) require ABSPATH . 'includes/' . $file;
try {
 $definition = new ReflectionFunction( 'adba_hook' );
 $file = $definition->getFileName();
 $actual = adb0_observer_binding();
 PHPUnit\Framework\Assert::assertSame( array( $file => hash_file( 'sha256', $file ) ), $actual, 'Canonical genuine fixture declaration key, not equivalent mixed path.' );
 $hook = 'sanitize_option_' . Alynt_Drime_Backups_Dashboard_Source_Policy::OPTION_SOURCE_POLICIES;
 $GLOBALS['wp_filter'] = array(); $GLOBALS['policy_trace'] = array();
 policy_isolated_hook( $hook, 'adba_hook' );
 $binding = array( 'observers' => array( $hook => $GLOBALS['wp_filter'][$hook]->callbacks ), 'observer_files' => $actual );
 PHPUnit\Framework\Assert::assertTrue( Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::hooks( $binding ), 'Non-empty exact source must admit metadata, not always unavailable.' );
 $alias = dirname( $file ) . DIRECTORY_SEPARATOR . '.' . DIRECTORY_SEPARATOR . basename( $file );
 PHPUnit\Framework\Assert::assertSame( $file, realpath( $alias ) );
 $bad = $binding; $bad['observer_files'] = array( $alias => hash_file( 'sha256', $file ) );
 PHPUnit\Framework\Assert::assertFalse( Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::hooks( $bad ), 'Runtime must not normalize unknown supplied source keys.' );
 $bad = $binding; $bad['observer_files'][$file] = str_repeat( '0', 64 );
 PHPUnit\Framework\Assert::assertFalse( Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::hooks( $bad ) );
 $bad = $binding; $bad['observer_files'] = array();
 PHPUnit\Framework\Assert::assertFalse( Alynt_Drime_Backups_Dashboard_Source_Policy_Bootstrap_Profile::hooks( $bad ) );
 PHPUnit\Framework\Assert::assertSame( array(), $GLOBALS['policy_trace'], 'No callback execution while checking provenance.' );
 echo 'checks:', PHPUnit\Framework\Assert::getCount(), " native_connections:0 installed_reads:0\n";
} catch ( Throwable $error ) {
 fwrite( STDERR, get_class( $error ) . ': ' . $error->getMessage() . "\n" ); exit( 1 );
}
