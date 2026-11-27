// Versioned source/stock-hook DATA only. Never a source reader or callback registry.
import {createHash} from 'node:crypto';
import {freezeData} from './source-policy-ppa-function-data.mjs';
import {CONDITION_CATALOG,CONDITION_CATALOG_DIGEST} from './source-policy-ppa-http-condition-catalog.mjs';
const extra=[
  ['wp-includes/default-filters.php',39500,'55bbf47fb835a0f64f28423d475b51f7fa72479f319fca417b4f23990d44f897'],
  ['wp-includes/plugin.php',37123,'d70b9e18d34ab3fe46e15548a5e8e089ea5920832f06a4d89a9d64b2f7938b0d'],
  ['wp-includes/class-wp-hook.php',17584,'b839c0e5672246bca8db1ab781ec8835f7732f253c375a237cbf6ec536e8d12e'],
  ['wp-includes/class-wp-filter-sentinel.php',760,'68c9c675d3805a343835728d98a0efedf0df9833cb904bebf909c7e0fda5c61c'],
  ['wp-includes/formatting.php',357678,'350f6b32303915df970bb0e3ea360fcd54bb0b8a7ecc3d504c3d860ab9468d32'],
  ['wp-includes/functions.php',295481,'cac8a17e293df99f0683a88debd3ba5e5cce274183a4e9fd3c9f18375747f41c'],
].map(([path,size,digest])=>({path:'core/'+path,size,digest}));
const callback=(name,index)=>({name,priority:10,accepted_args:1,source:extra[index].path,digest:extra[index].digest});
export const STOCK_CHARSET_CATALOG=freezeData({model:'ppa-stock-charset-catalog-v2',
  previous_catalog:CONDITION_CATALOG_DIGEST,core_commit:CONDITION_CATALOG.core_commit,
  profile:CONDITION_CATALOG.profile,proof_revision:CONDITION_CATALOG.proof_revision,
  design_revision:'4b8030b0c43063e603883b2efc3941ec676e68e6',flow_digest:CONDITION_CATALOG.flow_digest,
  sources:[...CONDITION_CATALOG.sources.map(row=>({...row})),...extra],
  hooks:[...CONDITION_CATALOG.hooks,'all','wp_autoload_values_to_autoload'].map(name=>({name,
    callbacks:name==='option_blog_charset'?[callback('_wp_specialchars',4),callback('_canonical_charset',5)]:[]})),
});
export const STOCK_CHARSET_CATALOG_DIGEST=createHash('sha256').update(JSON.stringify(STOCK_CHARSET_CATALOG)).digest('hex');
