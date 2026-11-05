// Proof routing only, never a production policy selector or native grant.
import {distributionJob} from './source-policy-minimum-distribution-contract.mjs';
export const PRODUCTION_JOIN='production-origin-join-v1';
export function minimumOperation(env){
  if((env.MINIMUM_OPERATION===undefined||env.MINIMUM_OPERATION==='')&&(env.PRODUCTION_JOIN_APPROVED===undefined||env.PRODUCTION_JOIN_APPROVED==='false'))return 'adapter';
  return distributionJob(env)&&env.MINIMUM_OPERATION===PRODUCTION_JOIN&&env.PRODUCTION_JOIN_APPROVED==='true'?'production-join':null;
}
