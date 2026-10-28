<?php
/** Pure protocol/counter projections over existing actual isolated owner execution. */
require_once __DIR__.'/source-policy-cleanup-fixtures.php';

function connected_cleanup_fixture_rows($divergent=false) {
 $built=array();
 foreach(Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::cases() as $case){
  $row=cleanup_fixture_rows()[$case];
  // Only protocol identity and explicit synthetic accounting change. Owner facts are never stamped.
  $row['version']='ADBC2';$row['profile']=Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::PROFILE;
  $row['counter_policy']=Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::POLICY;
  if($divergent){
   foreach(array('bootstrap','after') as $field)cleanup_fixture_counter($row,$field,'pconnect_success','1');
   if($case==='missing-table')cleanup_fixture_counter($row,'bootstrap','active_connections',Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::WRAP);
   cleanup_fixture_counter($row,'after','active_connections',Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::WRAP);
  }
  $built[$case]=$row;
 }
 return $built;
}
function connected_cleanup_inspect($case,$row){return Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::inspect($row,str_repeat('a',32),$case,hash('sha256','isolated-cleanup-fixture'),$case==='positive'?106:105);}
