// Recorded source identities only. No reads,bootstrap or execution authority.
import {createHash} from 'node:crypto';
import {freezeData} from './source-policy-ppa-function-data.mjs';
import {SOURCE_CATALOG,SOURCE_CATALOG_DIGEST} from './source-policy-ppa-function-catalog.mjs';
const core=[
  ['wp-includes/default-constants.php',11365,'95c3a9ac07e13ced91017b059c3d38c1a3d827ba0293ff162da83e3d9bee4e72'],
  ['wp-admin/includes/upgrade.php',116674,'0d1d0e6e9c135452641b5a518ba41f6907e67ac668d27d5e38690f53e846527e'],
  ['wp-admin/includes/schema.php',45583,'a446ddd76ce401fc8173bfee4e79a98f8c3567ed3ba7560c2f167ca85e6976b0'],
  ['wp-includes/class-wp-recovery-mode-link-service.php',3523,'999f257e2e696474340758028f7dd8adf58e28fd36e7e0f8c84e7ea63eb2e740'],
];
const proof=[
  ['source-policy-http-owned-wire.mjs',7684,'a0207c24bb97682a06c5feea81c95136bdf8cfea6d8c63c8956dd7793530e9d0'],
  ['source-policy-http-owned-controller.mjs',26303,'fb84996a64e7669de601cab41e73d1274843e3f88dd015264441a67c3ac14a36'],
  ['source-policy-http-owned-router.php',6643,'3a518db828113d690e391ecf4ca511e5da54b9ebab1e35261243a47c6bdde4bb'],
  ['source-policy-http-owned-worker.php',6422,'6af2a8c2c32ce6773e4636cf9e6be61068b33f4860d662f3117e766c5ec31780'],
  ['source-policy-http-owned-setup.php',7516,'54ff334ad68bc6af7b8edbe707d51432e3eb43506dfe8bb887f63148f9772834'],
  ['source-policy-http-initialized-native.php',8484,'580ff710cc0243aaa777fbd08b19797bc3d2c2b6fc6bfc49f9bb7acd715c3d11'],
  ['source-policy-http-supervisor.mjs',4366,'52be25b6daba19d101eac7c0a481e1999038ce8f205160dfc14671ef76f10096'],
  ['source-policy-http-contract.mjs',6141,'1fc29c673ff1dc0d38fd7d4351f346d212ac38c1f956596d632ffa221b92661e'],
  ['source-policy-http-initialization.php',8241,'7f7b22eca5c6ef061572b6cc1cc8451d634535a55eb68182327a6e17b7335ba4'],
  ['source-policy-http-initialized-evidence.mjs',2601,'ce8f6b84af3843f617f0b7eb4e4cfb4e17ef6b7eebd5f02f8933fe123e67b21d'],
  ['source-policy-http-owned-source.mjs',6377,'8d3f73d868138ce3599de5bf3bbbf908e766535543e76110c1f2c282c1521914'],
];
export const CONDITION_CATALOG=freezeData({model:'ppa-http-condition-catalog-v1',core_commit:SOURCE_CATALOG.commit,
  source_catalog:SOURCE_CATALOG_DIGEST,proof_revision:'6bb399a8969f3c2c57febf2f2ec40d08b741a0a6',
  profile:'owned-http-l714-initialized-v2',flow_digest:'403b05acb27d92390b68330625e82fc2ffbef32624c5bc698b5b768a6b85a394',
  sources:[...SOURCE_CATALOG.anchors.map(row=>({...row})),
    ...core.map(([path,size,digest])=>({path:'core/'+path,size,digest})),
    ...proof.map(([path,size,digest])=>({path:(path.endsWith('.mjs')?'workspace/':'proof/')+'scripts/ci/'+path,size,digest}))],
  hooks:['pre_option_blog_charset','pre_option','default_option_blog_charset','option_blog_charset',
    'pre_wp_load_alloptions','pre_cache_alloptions','alloptions'],
});
export const CONDITION_CATALOG_DIGEST=createHash('sha256').update(JSON.stringify(CONDITION_CATALOG)).digest('hex');
