#!/usr/bin/env bash
# One approved empty owned instance; never WordPress, adapter execution or a live DB.
set -euo pipefail
[[ "${GITHUB_ACTIONS:-}" == true && "${GITHUB_EVENT_NAME:-}" == workflow_dispatch ]]
[[ "${GITHUB_REPOSITORY:-}" == NichlasB/alynt-drime-backups-dashboard && "${GITHUB_REF:-}" == refs/heads/master ]]
[[ "${GITHUB_SHA:-}" =~ ^[a-f0-9]{40}$ && "${EXPECTED_SHA:-}" == "$GITHUB_SHA" ]]
[[ "${GITHUB_RUN_ID:-}" =~ ^[0-9]+$ && "${GITHUB_RUN_ATTEMPT:-}" =~ ^[0-9]+$ ]]
[[ "${RUNNER_OS:-}" == Linux && "$(uname -m)" == x86_64 && "$(git rev-parse HEAD)" == "$GITHUB_SHA" ]]
[[ "$PWD" == "$GITHUB_WORKSPACE" && "$(realpath "$RUNNER_TEMP")" == "$RUNNER_TEMP" ]]
[[ -z "${DOCKER_HOST:-}" && -z "${DOCKER_CONTEXT:-}" ]]
[[ "$(docker context inspect --format '{{.Endpoints.docker.Host}}')" == unix:///var/run/docker.sock ]]
owner="adb-linux-identity-$GITHUB_RUN_ID-$GITHUB_RUN_ATTEMPT"
stage="$RUNNER_TEMP/$owner"
image='mariadb@sha256:161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5'
uid="$(id -u)"; gid="$(id -g)"

diagnostics() {
  node <<'NODE'
const fs=require('node:fs');
const base=process.env.RUNNER_TEMP+'/adb-linux-identity-'+process.env.GITHUB_RUN_ID+'-'+process.env.GITHUB_RUN_ATTEMPT+'/socket/';
for(const name of ['bootstrap.log','server.log']){
  const p=base+name;if(!fs.existsSync(p))continue;
  const s=fs.lstatSync(p);if(!s.isFile()||s.isSymbolicLink()||s.uid!==process.getuid()||s.size>65536)throw Error('Unbounded/unowned audit diagnostic file');
  const lines=fs.readFileSync(p,'utf8').split(/\r?\n/).filter(x=>/\[Warning\]|\[ERROR\]|ready for connections|Shutdown complete/.test(x));
  if(lines.length>32||lines.some(x=>x.length>1024))throw Error('Unbounded audit diagnostics');
  process.stdout.write(JSON.stringify({diagnostics:name,bytes:s.size,sha256:require('node:crypto').createHash('sha256').update(fs.readFileSync(p)).digest('hex'),lines})+'\n');
}
NODE
}

cleanup() {
  local containers cid label leaf diagnostic_failure=0
  containers="$(docker container ls --all --filter "label=com.alynt.identity=$owner" --quiet --no-trunc)"
  if [[ -e "$stage" ]]; then
    [[ ! -L "$stage" && "$(realpath "$stage")" == "$stage" && "$(stat -c %u "$stage")" == "$uid" ]]
    if [[ -e "$stage/owned.cid" ]]; then
      [[ -f "$stage/owned.cid" && ! -L "$stage/owned.cid" ]]
      cid="$(cat "$stage/owned.cid")"; [[ "$cid" =~ ^[a-f0-9]{64}$ ]]
      if [[ -n "$containers" ]]; then
        [[ "$containers" == "$cid" ]]
        label="$(docker container inspect --format '{{ index .Config.Labels "com.alynt.identity" }}' "$cid")"
        [[ "$label" == "$owner" ]]
        if [[ "$(docker container inspect --format '{{.State.Running}}' "$cid")" == true ]]; then
          timeout --signal=TERM --kill-after=2s 8s docker stop --time 5 "$cid" >/dev/null
        fi
        [[ "$(docker container inspect --format '{{.State.Running}}' "$cid")" == false ]]
        docker container rm --volumes "$cid" >/dev/null
      fi
      containers="$(docker container ls --all --filter "label=com.alynt.identity=$owner" --quiet --no-trunc)"
      [[ -z "$containers" ]]
      rm -- "$stage/owned.cid"
    fi
    if [[ -e "$stage/socket" ]]; then
      [[ ! -L "$stage/socket" && "$(realpath "$stage/socket")" == "$stage/socket" && "$(stat -c %u "$stage/socket")" == "$uid" ]]
      if ! diagnostics; then diagnostic_failure=1; fi # Still remove exact owned resources if evidence capture fails.
      for leaf in bootstrap.log server.log native.pid native.sock; do
        if [[ -e "$stage/socket/$leaf" || -S "$stage/socket/$leaf" || -L "$stage/socket/$leaf" ]]; then
          [[ ! -L "$stage/socket/$leaf" && "$(stat -c %u "$stage/socket/$leaf")" == "$uid" ]]
          rm -- "$stage/socket/$leaf"
        fi
      done
      rmdir -- "$stage/socket"
    fi
    rmdir -- "$stage" # Never recursively delete unexpected contents.
  fi
  [[ -z "$containers" ]]
  [[ "$diagnostic_failure" == 0 ]]
  printf 'OWNED_IDENTITY_CLEANUP containers:0 directories:0 database_tmpfs:released\n'
}

if [[ "$#" == 1 && "$1" == --cleanup ]]; then cleanup; exit 0; fi
[[ "$#" == 0 && "$uid" != 0 && -d "$stage/socket" && ! -L "$stage" && ! -L "$stage/socket" ]]
[[ "$(realpath "$stage/socket")" == "$stage/socket" && "$(stat -c %a "$stage")" == 700 && "$(stat -c %a "$stage/socket")" == 700 ]]
[[ "$(stat -c %u "$stage/socket")" == "$uid" && ! -e "$stage/socket/native.sock" && ${#stage} -le 77 ]]
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

# Fresh unchanged prerequisite must have removed its own checkout/directory.
[[ ! -e "$GITHUB_WORKSPACE/.adb-linux-preflight-core" && ! -e "$RUNNER_TEMP/adb-linux-preflight-$GITHUB_RUN_ID-$GITHUB_RUN_ATTEMPT" ]]
[[ "$(docker image inspect --format '{{.Os}}/{{.Architecture}}' "$image")" == linux/amd64 ]]
timeout --signal=TERM --kill-after=2s 15s docker run --detach --pull never \
  --name "$owner" --cidfile "$stage/owned.cid" --label "com.alynt.identity=$owner" \
  --network none --read-only --user "$uid:$gid" --cap-drop ALL --security-opt no-new-privileges \
  --pids-limit 64 --memory 512m --cpus 1 --no-healthcheck \
  --mount "type=bind,source=$stage/socket,destination=/run/adb-owned" \
  --tmpfs "/var/lib/mysql:rw,nosuid,nodev,noexec,size=256m,mode=700,uid=$uid,gid=$gid" \
  --tmpfs "/tmp:rw,nosuid,nodev,noexec,size=16m,mode=700,uid=$uid,gid=$gid" \
  --entrypoint /bin/sh "$image" -ceu '
    test ! -e /var/lib/mysql/mysql
    timeout --signal=TERM --kill-after=2s 40s mariadb-install-db --no-defaults --basedir=/usr \
      --datadir=/var/lib/mysql --auth-root-authentication-method=normal --skip-test-db --skip-name-resolve \
      --skip-log-bin --innodb-buffer-pool-size=32M --innodb-log-file-size=8M --aria-pagecache-buffer-size=8M \
      >/run/adb-owned/bootstrap.log 2>&1
    exec mariadbd --no-defaults --datadir=/var/lib/mysql --socket=/run/adb-owned/native.sock \
      --pid-file=/run/adb-owned/native.pid --log-error=/run/adb-owned/server.log \
      --skip-networking --skip-name-resolve --read-only --skip-log-bin --max-connections=10 \
      --innodb-buffer-pool-size=32M --innodb-log-file-size=8M --aria-pagecache-buffer-size=8M \
      --max-statement-time=2 --innodb-lock-wait-timeout=1 --lock-wait-timeout=1 --wait-timeout=15 --connect-timeout=2
  '
cid="$(cat "$stage/owned.cid")"; [[ "$cid" =~ ^[a-f0-9]{64}$ ]]
[[ "$(docker container inspect --format '{{.HostConfig.NetworkMode}}' "$cid")" == none ]]
start=$SECONDS
until [[ -S "$stage/socket/native.sock" && -f "$stage/socket/server.log" ]] && grep -qF 'ready for connections.' "$stage/socket/server.log"; do
  [[ $((SECONDS-start)) -lt 50 && "$(docker container inspect --format '{{.State.Running}}' "$cid")" == true ]]
  sleep 0.25
done

# Three fixed SELECTs total: independent baseline, PHP identity, independent post-close.
observer_sql="SELECT (SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()),(SELECT COUNT(*) FROM information_schema.INNODB_TRX),(SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME NOT IN ('mysql','information_schema','performance_schema','sys'));"
baseline="$(timeout --signal=TERM --kill-after=2s 10s docker exec --user "$uid:$gid" "$cid" mariadb --no-defaults --protocol=SOCKET --socket=/run/adb-owned/native.sock --user=root --skip-reconnect --connect-timeout=2 --batch --raw --skip-column-names --execute="$observer_sql")"
[[ "$baseline" == $'0\t0\t0' ]]; printf 'OWNED_IDENTITY_BASELINE other_sessions:0 transactions:0 application_schemas:0\n'
timeout --signal=TERM --kill-after=2s 25s node <<'NODE'
const {spawnSync}=require('node:child_process');
const r=spawnSync('php',['-n','-d','extension=mysqlnd','-d','extension=mysqli','-d','memory_limit=64M',
  '-d','mysqli.allow_persistent=0','scripts/ci/source-policy-linux-identity.php','--owned-identity-audit'],
  {timeout:15000,killSignal:'SIGKILL',maxBuffer:16384,encoding:'utf8'});
if(r.error||r.signal||r.status!==0||r.stderr!==''||!/^\{[^\r\n]+\}\n$/.test(r.stdout))
  throw new Error('Fixed owned identity worker failed or emitted diagnostics');
const f=JSON.parse(r.stdout);
if(f.close_ack!==true||f.client_queries!==1||f.qualification!=='not-established')throw new Error('Invalid identity/release report');
process.stdout.write(r.stdout);
NODE
released="$(timeout --signal=TERM --kill-after=2s 10s docker exec --user "$uid:$gid" "$cid" mariadb --no-defaults --protocol=SOCKET --socket=/run/adb-owned/native.sock --user=root --skip-reconnect --connect-timeout=2 --batch --raw --skip-column-names --execute="$observer_sql")"
[[ "$released" == $'0\t0\t0' ]]; printf 'OWNED_IDENTITY_POST_CLOSE other_sessions:0 transactions:0 application_schemas:0\n'
timeout --signal=TERM --kill-after=2s 8s docker stop --time 5 "$cid" >/dev/null
[[ "$(docker container inspect --format '{{.State.Running}}/{{.State.ExitCode}}/{{.State.OOMKilled}}' "$cid")" == false/0/false ]]
cleanup
trap - EXIT
printf 'OWNED_IDENTITY_AUDIT_COMPLETE source:%s sql_reads:3 wordpress_execution:0 adapter_execution:0 native_qualification:not-established\n' "$GITHUB_SHA"
