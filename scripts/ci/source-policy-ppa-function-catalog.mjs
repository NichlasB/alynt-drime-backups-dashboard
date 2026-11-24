// Fixed audited source DATA,not an observer,core loader or native authority.
import {createHash} from 'node:crypto';
const freeze=value=>{if(value&&typeof value==='object'){for(const item of Object.values(value))freeze(item);Object.freeze(value);}return value;};
export const CATALOG_MODEL='ppa-source-function-catalog-v1';
export const SOURCE_CATALOG=freeze({model:CATALOG_MODEL,
  commit:'db96f5eaa77a8d3e8eec8923a518fa2326f0d46a',
  manifest:'3b0b19969bfaf09c13eb89c1cf8a335eee2c3c984012e15cfb783a9f983725b8',
  anchors:[
    {path:'core/wp-includes/compat.php',size:22479,digest:'6687dd4bf958e127a9d9df3fcc70545d8a0bea2d94410dc2a38b6ed2ee7df58b'},
    {path:'core/wp-includes/compat-utf8.php',size:19386,digest:'7a922c1d15f8d1464c18d005d6e094126c4c82a85f672b1b9d8616db67f700ea'},
    {path:'core/wp-includes/class-wp-recovery-mode-cookie-service.php',size:6877,digest:'0290d5e6b418968e1bc03b2618593a1822e148a6e19e4b7accd35aefd58f6b59'},
    {path:'core/wp-includes/class-wp-recovery-mode.php',size:11460,digest:'cbb58ae15fd0cd848408313f2aab7eb05c94b0d1a983dcf6fe9f9e4bc76d4dbc'},
    {path:'core/wp-includes/pluggable.php',size:127919,digest:'3a2482a65b50d62ebae75a728be98b78985d3a3f5a3e007cb2d0c1338bf05777'},
    {path:'core/wp-includes/class-wp-session-tokens.php',size:7319,digest:'0708de7171949675139eda1e476935763f65e8a3a0cf40fc534dfffa00fb04cb'},
    {path:'core/wp-includes/class-wpdb.php',size:121437,digest:'e15403e90032dd0508811301505e491d4212de5152581a98f51b744a1a3903bd'},
    {path:'core/wp-includes/option.php',size:105246,digest:'fe3f0b7e115b2a50c31162ed6428abe4088c080b0f71b9678d84806168fe90d0'},
    {path:'core/wp-settings.php',size:33152,digest:'2e3185ee44bcdd9d9c4bef8ce4891827711dec2b5d5d4c08c951738ccef51f8d'},
  ],
  roles:['environment','audit','adapter','request-audit','request','installer','initializer','server'],
  http_roles:['installer','initializer','server'],
  // Classification is fixed audit DATA. Callers cannot declare an UNKNOWN path excluded.
  pre_core:[{id:'stock-bootstrap',status:'excluded',condition:'pre-core-only',anchor:8}],
  post_core:[
    {id:'mb-explicit-utf8',status:'required',condition:'explicit-UTF-8',anchor:0},
    {id:'mb-default-encoding',status:'unknown',condition:'option-effects-unproved',anchor:7},
    {id:'recovery-cookie-ctype',status:'unknown',condition:'request-exclusion-unproved',anchor:2},
    {id:'iconv-guarded-use',status:'guarded',condition:'function-exists-branch-only',anchor:null},
    {id:'other-core-vendor-uses',status:'unknown',condition:'consumer-closure-unproved',anchor:null},
  ],
  // Separate algorithm domains: hash availability does not imply HMAC availability.
  algorithms:{hash:['sha256','sha384'],hmac:['md5','sha256','sha384'],password:['bcrypt']},
  environment_algorithms:{hash:['sha256','sha384'],hmac:['sha256','sha384'],password:[]},
  fallback:{encoding:'UTF-8',functions:['mb_strlen','mb_substr'],helpers:[0,1],
    native:['function_exists','is_string','strcasecmp','strlen','substr','is_null','max','min','strspn','ord']},
});
export const SOURCE_CATALOG_DIGEST=createHash('sha256').update(JSON.stringify(SOURCE_CATALOG)).digest('hex');
