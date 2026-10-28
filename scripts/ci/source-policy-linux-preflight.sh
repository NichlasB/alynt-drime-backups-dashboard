#!/usr/bin/env bash
# Owned ephemeral environment only. No database startup, SQL or WordPress execution.
set -euo pipefail
[[ "${GITHUB_ACTIONS:-}" == true && "${GITHUB_EVENT_NAME:-}" == workflow_dispatch ]]
[[ "${GITHUB_REPOSITORY:-}" == NichlasB/alynt-drime-backups-dashboard && "${GITHUB_REF:-}" == refs/heads/master ]]
[[ "${GITHUB_SHA:-}" =~ ^[a-f0-9]{40}$ && "${EXPECTED_SHA:-}" == "$GITHUB_SHA" ]]
[[ "${GITHUB_RUN_ID:-}" =~ ^[0-9]+$ && "${GITHUB_RUN_ATTEMPT:-}" =~ ^[0-9]+$ ]]
[[ "${RUNNER_OS:-}" == Linux && "$(uname -m)" == x86_64 ]]
[[ "$(git rev-parse HEAD)" == "$GITHUB_SHA" ]]
[[ "$PWD" == "$GITHUB_WORKSPACE" && "$(realpath "$RUNNER_TEMP")" == "$RUNNER_TEMP" ]]
owner="adb-linux-preflight-$GITHUB_RUN_ID-$GITHUB_RUN_ATTEMPT"
stage="$RUNNER_TEMP/$owner"
core="$GITHUB_WORKSPACE/.adb-linux-preflight-core"
image='mariadb@sha256:161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5'

cleanup() {
  local cid label containers
  containers="$(docker container ls --all --filter "label=com.alynt.preflight=$owner" --quiet --no-trunc)"
  if [[ -e "$stage" ]]; then
    [[ ! -L "$stage" && "$(realpath "$stage")" == "$stage" && "$(stat -c %u "$stage")" == "$(id -u)" ]]
    if [[ -e "$stage/owned.cid" ]]; then
      [[ -f "$stage/owned.cid" && ! -L "$stage/owned.cid" ]]
      cid="$(cat "$stage/owned.cid")"
      [[ "$cid" =~ ^[a-f0-9]{64}$ ]]
      if [[ -n "$containers" ]]; then
        [[ "$containers" == "$cid" ]]
        label="$(docker container inspect --format '{{ index .Config.Labels "com.alynt.preflight" }}' "$cid")"
        [[ "$label" == "$owner" ]]
        docker container rm --force --volumes "$cid" >/dev/null
      fi
      containers="$(docker container ls --all --filter "label=com.alynt.preflight=$owner" --quiet --no-trunc)"
      [[ -z "$containers" ]]
      rm -- "$stage/owned.cid"
    fi
    rmdir -- "$stage" # Unexpected contents refuse deletion, not recursive cleanup.
  fi
  [[ -z "$containers" ]]
  if [[ -e "$core" ]]; then
    [[ ! -L "$core" && "$(realpath "$core")" == "$core" && "$(stat -c %u "$core")" == "$(id -u)" ]]
    [[ "$(git -C "$core" rev-parse HEAD)" == db96f5eaa77a8d3e8eec8923a518fa2326f0d46a ]]
    rm -rf -- "$core" # Exact freshly checked-out core child only, never the workspace.
  fi
  printf 'OWNED_PREFLIGHT_CLEANUP containers:0 private_directory:0 core_checkout:0\n'
}

if [[ "$#" == 1 && "$1" == --cleanup ]]; then cleanup; exit 0; fi
[[ "$#" == 0 && -d "$stage" && ! -L "$stage" && "$(stat -c %a "$stage")" == 700 ]]
[[ "$(stat -c %u "$stage")" == "$(id -u)" && "$(git -C "$core" rev-parse HEAD)" == db96f5eaa77a8d3e8eec8923a518fa2326f0d46a ]]
[[ ! -e "$stage/native.sock" && ${#stage} -le 84 ]]
printf 'OWNED_DIRECTORY mode:%s uid:%s socket_path_bytes:%s socket_created:0\n' "$(stat -c %a "$stage")" "$(id -u)" "$((${#stage} + 12))"
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

# Exact Ubuntu package module layout; refusal rather than an alternate PHP/backend.
timeout --signal=TERM --kill-after=2s 30s node <<'NODE'
const {spawnSync}=require('node:child_process');
const args=['-n','-d','extension=mysqlnd','-d','extension=mysqli','-d','memory_limit=64M',
  '-d','mysqli.allow_persistent=0','scripts/ci/source-policy-linux-preflight.php','--owned-profile-preflight'];
const result=spawnSync('php',args,{timeout:20000,killSignal:'SIGKILL',maxBuffer:16384,encoding:'utf8'});
if(result.error||result.signal||result.status!==0||result.stderr!==''||!/^\{[^\r\n]+\}\n$/.test(result.stdout))
  throw new Error('Owned PHP profile failed or emitted diagnostics; no alternate module/backend attempted');
const facts=JSON.parse(result.stdout);
if(facts.php!=='8.5.12'||facts.client!=='mysqlnd 8.5.12'||facts.core_pins!==11||
  facts.native_connections!==0||facts.sql_queries!==0||facts.qualification!=='not-established')
  throw new Error('Owned profile report is not exact environment-only evidence');
process.stdout.write(result.stdout);
NODE
timeout --signal=TERM --kill-after=5s 180s docker pull --platform linux/amd64 "$image"
[[ "$(docker image inspect --format '{{.Os}}/{{.Architecture}}' "$image")" == linux/amd64 ]]

# Override entrypoint and healthcheck; no daemon, config, network, host mount or DB volume.
timeout --signal=TERM --kill-after=2s 20s docker run --rm --pull never \
  --name "$owner" --cidfile "$stage/owned.cid" --label "com.alynt.preflight=$owner" \
  --network none --read-only --user "$(id -u):$(id -g)" --cap-drop ALL \
  --security-opt no-new-privileges --pids-limit 16 --memory 128m --cpus 1 --no-healthcheck \
  --mount type=tmpfs,destination=/var/lib/mysql,tmpfs-size=4096,tmpfs-mode=700 \
  --entrypoint /bin/sh "$image" -ceu '
    test "$(id -u)" -ne 0
    server="$(mariadbd --no-defaults --version)"
    client="$(mariadb --no-defaults --version)"
    case "$server" in *"Ver 10.11.18-MariaDB"*) ;; *) exit 1 ;; esac
    case "$client" in *"Distrib 10.11.18-MariaDB"*) ;; *) exit 1 ;; esac
    printf "%s\n%s\n" "$server" "$client"
    printf "owned_uid:%s owned_gid:%s\n" "$(id -u)" "$(id -g)"
    sha256sum "$(command -v mariadbd)" "$(command -v mariadb)"
  '
printf 'ENVIRONMENT_PREFLIGHT_COMPLETE source:%s image:%s database_started:0 sql:0 wordpress_execution:0 native_qualification:not-established\n' "$GITHUB_SHA" "$image"
