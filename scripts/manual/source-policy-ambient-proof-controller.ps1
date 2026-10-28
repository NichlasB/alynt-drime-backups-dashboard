param([switch]$ConfirmedPluginTesterOwnedAmbient,[string]$Prefix)
$ErrorActionPreference='Stop'
if(-not $ConfirmedPluginTesterOwnedAmbient -or $Prefix -cnotmatch '\Aadbb_[a-f0-9]{12}_\z' -or $args.Count -ne 0 -or -not $IsWindows -or -not $env:MYSQL_PWD){throw 'Exact confirmed owned ambient scope required'}
$script:AdbcScope=$true
. "$PSScriptRoot/source-policy-cleanup-process.ps1"
$php='C:/Users/Captain/AppData/Roaming/Local/lightning-services/php-8.5.12+0/bin/win64/php.exe'
$mysql='C:/Users/Captain/AppData/Roaming/Local/lightning-services/mariadb-10.6.23+0/bin/win32/bin/mysql.exe'
$argsBase=@('-n','-d','default_socket_timeout=3','-d','extension_dir=C:/Users/Captain/AppData/Roaming/Local/lightning-services/php-8.5.12+0/bin/win64/ext','-d','extension=mysqli',"$PSScriptRoot/source-policy-ambient-owned-proof.php",'--confirmed-plugin-tester-owned-ambient',$Prefix)
$run=[guid]::NewGuid().ToString('N');$children=[Collections.Generic.List[object]]::new();$stage='audit'
function Assert-AdboClear($Clock,[int]$Limit){
 # All server sessions including NULL/foreign DBs, not merely the fixture database.
 $observer=New-AdbcProcess $mysql @('--no-defaults','-h','127.0.0.1','-P','10004','-u','root','-D','local','--connect-timeout=2','-N','-B','-r','-e','SELECT (SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()),(SELECT COUNT(*) FROM information_schema.INNODB_TRX);')
 try{$line=Read-AdbcLine $observer $Clock $Limit;Complete-AdbcProcess $observer $Clock $Limit;if($line.Replace("`r`n","`n") -cne "0`t0`n"){throw 'All-server reconciliation refused'}}finally{Stop-AdbcProcess $observer}
}
try{
 $sites=Get-Content -Raw 'C:/Users/Captain/AppData/Roaming/Local/sites.json'|ConvertFrom-Json
 $site=@($sites.PSObject.Properties.Value|Where-Object{$_.domain -ceq 'plugin-tester.local'})
 if($site.Count -ne 1 -or $site[0].name -cne 'Plugin Tester' -or [IO.Path]::GetFullPath([string]$site[0].path).TrimEnd('\','/') -ine 'C:\Users\Captain\Local Sites\plugin-tester' -or $site[0].services.mariadb.version -cne '10.6.23' -or $site[0].services.php.version -cne '8.5.12' -or $site[0].services.mariadb.ports.MYSQL[0] -ne 10004){throw 'Fixed metadata refused'}
 $clock=[Diagnostics.Stopwatch]::StartNew();Assert-AdboClear $clock 10000
 $audit=New-AdbcProcess $php ($argsBase+@($run,('0'*64),'audit'));$children.Add($audit)
 $auditLine=Read-AdbcLine $audit $clock 10000;Complete-AdbcProcess $audit $clock 10000
 if($auditLine -cnotmatch '\AADBO-AUDIT\|([a-f0-9]{64})\|([1-9][0-9]{0,4})\|3\n\z'){throw 'Ambient source audit refused'}
 $source=$Matches[1];$clock.Restart();$stage='native'
 $child=New-AdbcProcess $php ($argsBase+@($run,$source,'proof'));$children.Add($child)
 $wire=Read-AdbcLine $child $clock 25000
 if($wire -cmatch '\AADBO-FAIL1\|[a-z-]{1,32}\|[0-9]{1,2}\|[0-9]{1,5}\|[a-f0-9]{64}\n\z'){Write-Output $wire.TrimEnd("`n");throw 'Source-owned ambient assertion refused'}
 $expected='\AADBO1\|'+$run+'\|'+$source+'\|'+$child.Id+'\|([1-9][0-9]{0,4})\|9\|0\|closed\n\z'
 if($wire -cnotmatch $expected -or $child.Process.HasExited){throw 'Native ambient body refused'}
 $stage='live';Assert-AdboClear $clock 25000
 if($child.Process.HasExited){throw 'Owned child exited during live reconciliation'}
 Write-AdbcLine $child ('ADBO-ACK|'+$run+'|'+$source+'|'+$child.Id) $clock 25000
 Complete-AdbcProcess $child $clock 25000
 $stage='post';Assert-AdboClear $clock 25000
 Write-Output ('AMBIENT_OWNED_COMPLETE elapsed_ms:'+$clock.ElapsedMilliseconds+' live/post:true terminal:'+$wire.TrimEnd("`n")+' production_wiring:disabled')
}catch{Write-Output ('AMBIENT_OWNED_UNCONFIRMED stage:'+$stage);throw [InvalidOperationException]::new('Owned ambient qualification stopped; no automatic retry')}
finally{$failed=$false;foreach($child in $children){try{Stop-AdbcProcess $child}catch{$failed=$true}};if($failed){throw 'Exact owned process cleanup unconfirmed'}}
