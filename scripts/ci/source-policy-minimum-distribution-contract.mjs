// Pure candidate-bound contracts. Importing this module performs no IO or admission.
export const DISTRIBUTION_MODEL='trusted-distribution-legacy-v1';
export const DISTRIBUTION_WORKFLOW='NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-minimum-distribution.yml@refs/heads/master';
export const distributionServer=Object.freeze({ref:'mariadb@sha256:14bab8cc945c08109a2175e844d46f4eaff1fbf4f59e95dcf2f9cac7961510e1',config:'sha256:2a46b014d1a6182f9f8240db6bae96566cdb11178f878ffe794f15095b745f35',volume:'/var/lib/mysql'});
export function distributionJob(env) {
  return Boolean(env&&env.GITHUB_ACTIONS==='true'&&env.GITHUB_EVENT_NAME==='workflow_dispatch'
    &&env.GITHUB_REPOSITORY==='NichlasB/alynt-drime-backups-dashboard'&&env.GITHUB_REF==='refs/heads/master'
    &&env.GITHUB_WORKFLOW_REF===DISTRIBUTION_WORKFLOW&&env.GITHUB_ACTOR==='NichlasB'
    &&env.RUNNER_OS==='Linux'&&env.RUNNER_ENVIRONMENT==='github-hosted'&&env.GITHUB_RUN_ATTEMPT==='1'
    &&typeof env.GITHUB_RUN_ID==='string'&&/^[1-9][0-9]{0,19}$/.test(env.GITHUB_RUN_ID)
    &&typeof env.GITHUB_SHA==='string'&&/^[a-f0-9]{40}$/.test(env.GITHUB_SHA)&&env.EXPECTED_SHA===env.GITHUB_SHA
    &&env.MINIMUM_MODEL===DISTRIBUTION_MODEL&&env.DISTRIBUTION_EXECUTION_APPROVED==='true'
    &&env.MINIMUM_SCOPE===undefined&&!env.DOCKER_HOST&&!env.DOCKER_CONTEXT);
}
export function distributionContainment(row) {
  return Boolean(row&&Array.isArray(row.security)&&row.security.length===2
    &&row.security.includes('no-new-privileges')&&row.security.includes('apparmor=docker-default')
    &&row.apparmor==='docker-default'&&(row.cap_add===null||(Array.isArray(row.cap_add)&&row.cap_add.length===0))
    &&row.pid_mode===''&&row.ipc_mode==='private'&&row.userns_mode===''
    &&row.log?.Type==='local'&&row.log.Config&&Reflect.ownKeys(row.log.Config).length===3
    &&row.log.Config['max-size']==='16k'&&row.log.Config['max-file']==='1'&&row.log.Config.compress==='false');
}
