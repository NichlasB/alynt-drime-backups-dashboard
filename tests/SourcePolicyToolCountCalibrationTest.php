<?php
use PHPUnit\Framework\TestCase;

/** Source contracts supplement the actual pure parser and separately owned proof. */
final class SourcePolicyToolCountCalibrationTest extends TestCase {

	private function source( $suffix ) {
		$path = dirname( __DIR__ ) . '/scripts/manual/source-policy-tool-count-' . $suffix . '.ps1';
		$this->assertFileExists( $path );
		return file_get_contents( $path );
	}

	public function test_count_lane_is_explicit_fixed_and_does_not_change_full_admission() {
		$c = $this->source( 'owned-proof' );
		foreach ( array( 'param([switch]$RunOwnedCountCalibration)', '-not $RunOwnedCountCalibration', '$args.Count -ne 0', '-not $IsWindows', "'7.6.5'", "'.NET 10.0.11'", '362a356ce7f0940ec74f73a8fc2c990a2cc24a38a11c90bbd8eca947110ad139', 'source-policy-tool-observer-transport.ps1', 'Read-ToolCountReceipt', 'Confirm-ToolCountCompletion' ) as $text ) {
			$this->assertStringContainsString( $text, $c );
		}
		foreach ( array( 'RunOwnedProof', 'Start-ToolWorker', '.Modules', '.MainModule', '::GetProcessById', 'Read-ToolSummary', 'Read-ToolDiagnostic', 'WaitForExitAsync' ) as $text ) {
			$this->assertStringNotContainsString( $text, $c );
		}
		$old = file_get_contents( dirname( __DIR__ ) . '/scripts/manual/source-policy-tool-observer-child.ps1' );
		$this->assertStringContainsString( '$modules.Count -gt 128', $old );
		foreach ( array( 'host-admission', 'launch-data', 'preflight-transcript' ) as $name ) {
			$source = file_get_contents( dirname( __DIR__ ) . '/scripts/manual/source-policy-minimum-' . $name . '.php' );
			$this->assertStringContainsString( '128', $source );
			$this->assertStringNotContainsString( 'ADBTC1', $source );
		}
	}

	public function test_one_scalar_read_is_bracketed_by_identity_without_row_iteration() {
		$o = $this->source( 'child' );
		foreach ( array( '$target.Refresh()', '$target.HasExited', '$target.StartTime.ToUniversalTime().Ticks -ne $TargetTick', '$target.MainModule.FileName -cne $Image', '$modules=$target.Modules', '$count=$modules.Count', '$count -isnot [int]', '$count -lt 2', "'ADBTC1|'", '|count-only|', '|not-admission', '$target.Dispose()' ) as $text ) {
			$this->assertStringContainsString( $text, $o );
		}
		$this->assertSame( 1, substr_count( $o, '$target.Modules' ) );
		$observation = strpos( $o, '$modules=$target.Modules' );
		$this->assertLessThan( $observation, strpos( $o, '$target.MainModule.FileName -cne $Image' ) );
		$this->assertGreaterThan( $observation, strrpos( $o, '$target.MainModule.FileName -cne $Image' ) );
		foreach ( array( 'foreach', 'for (', 'while', 'ModuleName', 'ModuleMemorySize', 'modules.txt', 'SortedDictionary', 'WriteAllText', 'Hold', 'Math]::Min' ) as $text ) {
			$this->assertStringNotContainsString( $text, $o );
		}
	}

	public function test_receipt_has_separate_purpose_exact_correlation_and_no_authority() {
		$d = $this->source( 'receipt' );
		foreach ( array( "'ADBTC1|'", '|count-only|', "'not-admission'", '$f.Count -ne 10', '$Line.Length -gt 512', '$Observer.TargetId -ne $Worker.Id', '$Observer.TargetTick -ne $Worker.Tick', '[int]::TryParse', '$count -lt 2', '2147483647', 'CountOnlyObserved=$false', 'FullSnapshot=$false', 'ObserverQualified=$false', 'LoadAuthorized=$false', 'RuntimeQualified=$false', 'NativeQualified=$false', '$Elapsed -ge 25000', '$fact -isnot [bool]' ) as $text ) {
			$this->assertStringContainsString( $text, $d );
		}
		foreach ( array( 'ADBT1', 'ADBTD1', 'ADBMP1', '.Modules', 'Process]::', 'File]::', 'Directory]::' ) as $text ) {
			$this->assertStringNotContainsString( $text, $d );
		}
	}

	public function test_owned_lifecycle_keeps_drains_deadlines_readbacks_and_exact_cleanup() {
		$c = $this->source( 'owned-proof' );
		foreach ( array( '20000', '2000', '25000', 'Complete-ToolProcess $o $observerLimit $true', 'Complete-ToolProcess $w 20000 $true', 'Stop-ToolProcess $child 25000', 'Assert-ToolIdentity $child $false', '$null -eq $child.OutRead -and $null -eq $child.ErrRead', '$child.Out.Length -eq 0 -and $child.ErrBytes -eq 0', 'Get-ToolHash $canary', 'Get-ToolHash $image', 'Get-ToolHash $sourceFiles[$role]', '[IO.Path]::GetFullPath($root)', '[IO.FileAttributes]::ReparsePoint', '[IO.Directory]::Delete($root,$false)', 'failure_cleanup_confirmed:', 'runtime_load_authorized:0' ) as $text ) {
			$this->assertStringContainsString( $text, $c );
		}
		foreach ( array( $c, $this->source( 'child' ), $this->source( 'receipt' ) ) as $source ) {
			foreach ( array( 'Invoke-Expression', 'Add-Type', 'Stop-Process', 'Get-CimInstance', 'Get-WmiObject', 'Kill($true)', '::GetProcesses(', 'MYSQL_PWD', 'wp-load', 'Invoke-WebRequest', 'System.Net.Sockets', 'Remove-Item' ) as $text ) {
				$this->assertStringNotContainsString( $text, $source );
			}
		}
		$this->assertStringNotContainsString( 'source-policy-tool-count', file_get_contents( dirname( __DIR__ ) . '/alynt-drime-backups-dashboard.php' ) );
	}
}
