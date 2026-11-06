// Fixed proof routing/source DATA only. No import-time IO, acquisition or dispatch.
import {distributionJob} from './source-policy-minimum-distribution-contract.mjs';
export const GENUINE_REQUEST='genuine-session-request-v1';
export const AUTH_FILES=Object.freeze(['class-wp-role.php','class-wp-roles.php','class-wp-user.php','capabilities.php','meta.php','class-wp-session-tokens.php','class-wp-user-meta-session-tokens.php','user.php','pluggable.php']);
export const BASE_CORE_FILES=Object.freeze(['version.php','load.php','plugin.php','class-wp-hook.php','class-wp-error.php','wp-db.php','cache.php','class-wp-object-cache.php','formatting.php','functions.php','option.php']);
export function genuineRequestJob(env){
  return distributionJob(env)&&env.MINIMUM_OPERATION===GENUINE_REQUEST&&env.GENUINE_REQUEST_APPROVED==='true'
    &&(env.PRODUCTION_JOIN_APPROVED===undefined||env.PRODUCTION_JOIN_APPROVED==='false');
}
export function requestFailureEvidence(raw){
  if(typeof raw!=='string'||Buffer.byteLength(raw)>256)return null;
  const m=/^ADBMGRF1\|(parent|child)\|([0-9]|1[0-9]|2[0-2])\|(audit|ambient|setup|auth|entry|verify|cleanup)\|(RuntimeException|Error|TypeError|ValueError|ParseError|ArgumentCountError|ReflectionException|other)\|([a-f0-9]{64})\n(?![\s\S])/.exec(raw);
  return m?{operation:GENUINE_REQUEST,role:m[1],case_index:Number(m[2]),stage:m[3],exception:m[4],assertion:m[5]}:null;
}
export function requestCoreFiles(operation){return [...BASE_CORE_FILES,...(operation==='genuine-request'?AUTH_FILES:[])];}
export function requestSchemaSQL(prefix){
  if(typeof prefix!=='string'||!/^adbm_[a-f0-9]{12}_$/.test(prefix))return null;
  const names=['users','usermeta','alynt_drime_dashboard_sites'].map(name=>prefix+name);
  return `CREATE TABLE adb_minimum.${names[0]} (ID bigint unsigned NOT NULL,user_login varchar(60) NOT NULL,user_pass varchar(255) NOT NULL,user_nicename varchar(50) NOT NULL,user_email varchar(100) NOT NULL,user_url varchar(100) NOT NULL,user_registered datetime NOT NULL,user_activation_key varchar(255) NOT NULL,user_status int NOT NULL,display_name varchar(250) NOT NULL,PRIMARY KEY(ID),UNIQUE KEY user_login(user_login)) ENGINE=InnoDB; CREATE TABLE adb_minimum.${names[1]} (umeta_id bigint unsigned NOT NULL AUTO_INCREMENT,user_id bigint unsigned NOT NULL,meta_key varchar(255),meta_value longtext,PRIMARY KEY(umeta_id),KEY user_id(user_id),KEY meta_key(meta_key(191))) ENGINE=InnoDB; CREATE TABLE adb_minimum.${names[2]} (id bigint unsigned NOT NULL,PRIMARY KEY(id)) ENGINE=InnoDB; ${names.map(name=>`GRANT SELECT,INSERT,UPDATE,DELETE ON adb_minimum.${name} TO 'adbm'@'127.0.0.1';`).join(' ')}`;
}
