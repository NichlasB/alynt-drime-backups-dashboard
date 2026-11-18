import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {createHash} from 'node:crypto';
import {nonTargetInvariantSql} from '../../scripts/ci/source-policy-http-owned-controller.mjs';
import {command} from '../../scripts/ci/source-policy-http-owned-source.mjs';
const storage={database:'adb_http_abcdef123456',prefix:'adbh_abcdef123456_',options:'adbh_abcdef123456_options'};
const source=readFileSync(new URL('../../scripts/ci/source-policy-http-owned-controller.mjs',import.meta.url),'utf8');
// Independent SQL expectation: every nullable value has a framed byte encoding.
const field=c=>`IF(${c} IS NULL,'N',CONCAT('V',OCTET_LENGTH(${c}),':',HEX(${c})))`;
const expected=`SELECT option_id,SHA2(CONCAT('option-v1:',${['option_name','option_value','autoload'].map(field).join(',')}),256) FROM ${storage.database}.${storage.options} WHERE BINARY option_name<>BINARY 'alynt_drime_backups_dashboard_source_policies' ORDER BY option_id;SELECT umeta_id,user_id,SHA2(CONCAT('usermeta-v1:',${['meta_key','meta_value'].map(field).join(',')}),256) FROM ${storage.database}.${storage.prefix}usermeta WHERE (meta_key IS NULL OR BINARY meta_key<>BINARY 'session_tokens') ORDER BY umeta_id;CHECKSUM TABLE ${['commentmeta','comments','links','postmeta','posts','term_relationships','term_taxonomy','termmeta','terms','users','alynt_drime_dashboard_sites'].map(t=>storage.database+'.'+storage.prefix+t).join(',')};`;
test('actual invariant SQL retains all protected fields, IDs, ordering, exclusions and table checksums',()=>{
 assert.equal(nonTargetInvariantSql(storage),expected);
 for(const delta of [{database:'foreign'},{prefix:'foreign_'},{options:'foreign_options'},{database:'adb_http_abcdef123456;DROP DATABASE x'}])assert.throws(()=>nonTargetInvariantSql({...storage,...delta}));
 assert.ok(source.includes('const invariant=()=>sha(db.sql(nonTargetInvariantSql(s),Math.min(5000,left(LIMITS.job_ms-LIMITS.cleanup_ms))));'));
 assert.ok(source.includes("need(sentinel()===sentinelHash&&invariant()===nonTarget,'non-target-preserved')"));
});
test('independent framing distinguishes null/empty/binary/separator changes and large-value mutations',()=>{
 const hash=parts=>createHash('sha256').update(Buffer.concat([Buffer.from('option-v1:'),...parts.map(x=>x===null?Buffer.from('N'):Buffer.from('V'+x.length+':'+x.toString('hex').toUpperCase()))])).digest('hex');
 const values=[null,Buffer.alloc(0),Buffer.from('N'),Buffer.from('V1:'),Buffer.from([0,255,58]),Buffer.alloc(70000,65)];
 const fingerprints=values.map(v=>hash([Buffer.from('cache'),v,Buffer.from('off')]));assert.equal(new Set(fingerprints).size,values.length);
 const large=values.at(-1),changed=Buffer.from(large);changed[changed.length-1]^=1;assert.notEqual(hash([large]),hash([changed]));assert.notEqual(hash([Buffer.from('a'),Buffer.from('bc')]),hash([Buffer.from('ab'),Buffer.from('c')]));
 assert.notEqual(hash([large,Buffer.from('off')]),hash([large,Buffer.from('on')]));assert.equal(fingerprints.at(-1).length,64);
});
test('unchanged command output budget still refuses large raw values and admits a compact digest',()=>{
 assert.throws(()=>command(process.execPath,['-e',"process.stdout.write('a'.repeat(70000))"]),/Owned HTTP refused: command/);
 assert.equal(command(process.execPath,['-e',"process.stdout.write('a'.repeat(64))"]),'a'.repeat(64));
 const primitive=readFileSync(new URL('../../scripts/ci/source-policy-http-owned-source.mjs',import.meta.url),'utf8');assert.match(primitive,/maxBuffer=65536/);
});
test('removing a protected column, nullable framing or table checksum fails the exact projection oracle',()=>{
 const start=source.indexOf('export function nonTargetInvariantSql('),end=source.indexOf('\nfunction privateConfig',start);assert.ok(start>0&&end>start);const section=source.slice(start,end);
 for(const [from,to]of [["['option_name','option_value','autoload']","['option_name','autoload']"],["'N'","'V0:'"],["'comments','links'","'comments'"]]){
  assert.ok(section.includes(from));const changed=section.replace(from,to);assert.notEqual(changed,section);
  const actual=new Function('need',changed.replace('export ','')+';return nonTargetInvariantSql;')(ok=>assert.ok(ok));
  assert.throws(()=>assert.equal(actual(storage),expected),{code:'ERR_ASSERTION'});
 }
});
