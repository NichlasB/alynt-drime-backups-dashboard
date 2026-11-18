// Bounded native-observer projections/receipt DATA. No SQL execution or IO here.
import {record,hex} from './source-policy-http-profile.mjs';
export function initializationAnchorSql(s){
 if(!/^adb_http_[a-f0-9]{12}$/.test(s.database)||!/^adbh_[a-f0-9]{12}_$/.test(s.prefix)||s.options!==s.prefix+'options')throw Error('Initialized anchor storage refused');
 const p=s.prefix,d=s.database,table=d+'.'+s.options;
 const keys=['alynt_drime_backups_dashboard_source_policies','owned_non_target','siteurl','home','active_plugins','template','stylesheet','current_theme','db_version','initial_db_version',p+'user_roles'];
 const frame=c=>`IF(${c} IS NULL,'N',CONCAT('V',OCTET_LENGTH(${c}),':',HEX(${c})))`;
 // Deliberately NOT a pre-initialization full baseline: normal option/cron/widget/
 // transient changes are allowed here. Allocator counters are not schema DDL.
 return `SELECT option_id,SHA2(CONCAT(${['option_name','option_value','autoload'].map(frame).join(',')}),256) FROM ${table} WHERE BINARY option_name IN (${keys.map(k=>"'"+k+"'").join(',')}) ORDER BY option_id;CHECKSUM TABLE ${['users','usermeta','alynt_drime_dashboard_sites'].map(t=>d+'.'+p+t).join(',')};SELECT TABLE_NAME,ENGINE,TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA='${d}' ORDER BY TABLE_NAME;SELECT TABLE_NAME,ORDINAL_POSITION,COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,HEX(COLUMN_DEFAULT),EXTRA,COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='${d}' ORDER BY TABLE_NAME,ORDINAL_POSITION;SELECT TABLE_NAME,INDEX_NAME,NON_UNIQUE,SEQ_IN_INDEX,COLUMN_NAME,COLLATION,SUB_PART,INDEX_TYPE FROM information_schema.STATISTICS WHERE TABLE_SCHEMA='${d}' ORDER BY TABLE_NAME,INDEX_NAME,SEQ_IN_INDEX;`;
}
export function nativeInitializerCompletion(value,ticket,before,after){
 const packet=record(value,['event','stock_shutdown','completion']);
 const keys=[...Object.keys(ticket),'sql','full_bootstrap','installing','short_init','protected_reads','protected_writes','owner_allocations','session_writes','mail','outbound','file_writes','source_unchanged'];
 const completion=packet&&record(packet.completion,keys);
 if(!packet||packet.event!=='owned-http-initializer-complete'||packet.stock_shutdown!==true||!completion||!Object.keys(ticket).every(k=>completion[k]===ticket[k])||!hex(before,64)||before!==ticket.anchors||after!==before)throw Error('Initialized native receipt/anchor refused');
 // This one field is derived from the parent's actual independent before/after
 // observation, not supplied/filled by the child. Remaining fields stay exact.
 return {...completion,anchors_unchanged:true};
}
