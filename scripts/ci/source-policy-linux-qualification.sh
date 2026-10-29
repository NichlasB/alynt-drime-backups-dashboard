#!/usr/bin/env bash
# Explicitly approved fresh lab only. No installed site, existing DB or runtime edit.
set -euo pipefail
[[ "${GITHUB_ACTIONS:-}" == true && "${GITHUB_EVENT_NAME:-}" == workflow_dispatch ]]
[[ "${GITHUB_REPOSITORY:-}" == NichlasB/alynt-drime-backups-dashboard && "${GITHUB_REF:-}" == refs/heads/master ]]
[[ "${GITHUB_SHA:-}" =~ ^[a-f0-9]{40}$ && "${EXPECTED_SHA:-}" == "$GITHUB_SHA" ]]
[[ "${GITHUB_RUN_ID:-}" =~ ^[0-9]+$ && "${GITHUB_RUN_ATTEMPT:-}" =~ ^[0-9]+$ ]]
[[ "$RUNNER_OS" == Linux && "$(uname -m)" == x86_64 && "$(git rev-parse HEAD)" == "$GITHUB_SHA" ]]
[[ "$PWD" == "$GITHUB_WORKSPACE" && "$(realpath "$RUNNER_TEMP")" == "$RUNNER_TEMP" ]]
[[ -z "${DOCKER_HOST:-}" && -z "${DOCKER_CONTEXT:-}" ]]
[[ "$(docker context inspect --format '{{.Endpoints.docker.Host}}')" == unix:///var/run/docker.sock ]]
owner="adb-linux-qualification-$GITHUB_RUN_ID-$GITHUB_RUN_ATTEMPT"
stage="$RUNNER_TEMP/$owner"
image='mariadb@sha256:161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5'
uid="$(id -u)"; gid="$(id -g)"
run="$(printf '%s' "$GITHUB_RUN_ID:$GITHUB_RUN_ATTEMPT" | sha256sum | cut -c1-32)"
table="adbb_${run:0:12}_options"

root_sql() {
  # Fixed internal statements via stdin; no secret-bearing argv or raw errors.
  local answer
  if ! answer="$(timeout --signal=TERM --kill-after=2s 10s docker exec --interactive --user "$uid:$gid" "$cid" \
    mariadb --no-defaults --protocol=SOCKET --socket=/run/adb-owned/native.sock --user=root --skip-reconnect \
    --connect-timeout=2 --batch --raw --skip-column-names 2>&1)"; then
    printf 'Fixed owned administrative statement failed\n' >&2; return 1
  fi
  printf '%s' "$answer"
}

cleanup() {
  local containers cid label leaf failed=0
  containers="$(docker container ls --all --filter "label=com.alynt.qualification=$owner" --quiet --no-trunc)"
  if [[ -e "$stage" ]]; then
    [[ ! -L "$stage" && "$(realpath "$stage")" == "$stage" && "$(stat -c %u "$stage")" == "$uid" ]]
    if [[ -e "$stage/owned.cid" ]]; then
      [[ -f "$stage/owned.cid" && ! -L "$stage/owned.cid" ]]
      cid="$(cat "$stage/owned.cid")"; [[ "$cid" =~ ^[a-f0-9]{64}$ ]]
      if [[ -n "$containers" ]]; then
        [[ "$containers" == "$cid" ]]
        label="$(docker container inspect --format '{{ index .Config.Labels "com.alynt.qualification" }}' "$cid")"
        [[ "$label" == "$owner" ]]
        if [[ "$(docker container inspect --format '{{.State.Running}}' "$cid")" == true ]]; then
          if [[ -S "$stage/socket/native.sock" ]]; then
            # Only the exact schema/account in this newly-created labeled instance.
            local before after
            before="$(printf 'SELECT (SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()),(SELECT COUNT(*) FROM information_schema.INNODB_TRX);' | root_sql)" || failed=1
            [[ "$before" == $'0\t0' ]] || failed=1
            after="$(printf "DROP DATABASE IF EXISTS adb_owned; DROP USER IF EXISTS 'adbp'@'localhost'; SELECT (SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME NOT IN ('mysql','information_schema','performance_schema','sys')),(SELECT COUNT(*) FROM mysql.global_priv WHERE User='adbp'),(SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()),(SELECT COUNT(*) FROM information_schema.INNODB_TRX);" | root_sql)" || failed=1
            [[ "$after" == $'0\t0\t0\t0' ]] || failed=1
          fi
          timeout --signal=TERM --kill-after=2s 8s docker stop --time 5 "$cid" >/dev/null || failed=1
        fi
        [[ "$(docker container inspect --format '{{.State.Running}}/{{.State.ExitCode}}/{{.State.OOMKilled}}' "$cid")" == false/0/false ]] || failed=1
        if [[ -f "$stage/socket/bootstrap.log" && -f "$stage/socket/server.log" ]]; then
          node scripts/ci/source-policy-linux-qualification-controller.mjs diagnostics || failed=1
        else failed=1; fi
        docker container rm --volumes "$cid" >/dev/null
      fi
      [[ -z "$(docker container ls --all --filter "label=com.alynt.qualification=$owner" --quiet --no-trunc)" ]]
      rm -- "$stage/owned.cid"
    fi
    if [[ -e "$stage/socket" ]]; then
      [[ ! -L "$stage/socket" && "$(realpath "$stage/socket")" == "$stage/socket" && "$(stat -c %u "$stage/socket")" == "$uid" ]]
      for leaf in bootstrap.log server.log native.pid native.sock; do
        if [[ -e "$stage/socket/$leaf" || -S "$stage/socket/$leaf" || -L "$stage/socket/$leaf" ]]; then
          [[ ! -L "$stage/socket/$leaf" && "$(stat -c %u "$stage/socket/$leaf")" == "$uid" ]]
          rm -- "$stage/socket/$leaf"
        fi
      done
      rmdir -- "$stage/socket"
    fi
    rmdir -- "$stage"
  fi
  [[ -z "$(docker container ls --all --filter "label=com.alynt.qualification=$owner" --quiet --no-trunc)" ]]
  unset QUALIFICATION_PASSWORD QUALIFICATION_IMAGE
  printf 'OWNED_QUALIFICATION_CLEANUP containers:0 directories:0 tmpfs:released reconciliation_failure:%s\n' "$failed"
  [[ "$failed" == 0 ]]
}
if [[ "$#" == 1 && "$1" == --cleanup ]]; then cleanup; exit 0; fi
[[ "$#" == 0 && "$uid" != 0 && -d "$stage/socket" && ! -L "$stage" && ! -L "$stage/socket" ]]
[[ "$(realpath "$stage/socket")" == "$stage/socket" && "$(stat -c %a "$stage")" == 700 && "$(stat -c %a "$stage/socket")" == 700 ]]
[[ "$(stat -c %u "$stage/socket")" == "$uid" && ! -e "$stage/socket/native.sock" && ${#stage} -le 77 ]]
[[ "$(git -C .adb-linux-preflight-core rev-parse HEAD)" == db96f5eaa77a8d3e8eec8923a518fa2326f0d46a ]]
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

audit="$(node scripts/ci/source-policy-linux-qualification-controller.mjs audit)"
printf '%s\n' "$audit"
export QUALIFICATION_IMAGE="$(printf '%s' "$audit" | node -e 'let s="";process.stdin.on("data",b=>s+=b);process.stdin.on("end",()=>process.stdout.write(JSON.parse(s).image))')"
export QUALIFICATION_PASSWORD="$(node -e 'process.stdout.write(require("node:crypto").randomBytes(32).toString("hex"))')"
[[ "$QUALIFICATION_IMAGE" =~ ^[a-f0-9]{64}$ && "$QUALIFICATION_PASSWORD" =~ ^[a-f0-9]{64}$ ]]
[[ "$(docker image inspect --format '{{.Os}}/{{.Architecture}}' "$image")" == linux/amd64 ]]
timeout --signal=TERM --kill-after=2s 15s docker run --detach --pull never \
  --name "$owner" --cidfile "$stage/owned.cid" --label "com.alynt.qualification=$owner" \
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
      --skip-networking --skip-name-resolve --skip-log-bin --max-connections=10 \
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
node scripts/ci/source-policy-linux-qualification-controller.mjs diagnostics
profile="$(printf 'SELECT VERSION(),@@skip_networking,@@wsrep_on,@@innodb_use_native_aio,@@autocommit,@@in_transaction,(SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()),(SELECT COUNT(*) FROM information_schema.INNODB_TRX),(SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME NOT IN (\x27mysql\x27,\x27information_schema\x27,\x27performance_schema\x27,\x27sys\x27));' | root_sql)"
[[ "$profile" == $'10.11.18-MariaDB-ubu2204\t1\t0\t0\t1\t0\t0\t0\t0' || "$profile" == $'10.11.18-MariaDB-ubu2204\t1\t0\t1\t1\t0\t0\t0\t0' ]]
printf 'OWNED_QUALIFICATION_BASELINE version/no_tcp/wsrep/native_aio/ac/tx/sessions/trx/schemas:%s\n' "$profile"
printf 'OWNED_KERNEL io_uring_disabled:%s\n' "$(cat /proc/sys/kernel/io_uring_disabled)"
printf "CREATE DATABASE adb_owned CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci; CREATE TABLE adb_owned.%s (option_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,option_name varchar(191) NOT NULL,option_value longtext NOT NULL,autoload varchar(20) NOT NULL,PRIMARY KEY(option_id),UNIQUE KEY option_name(option_name),KEY autoload(autoload)) ENGINE=InnoDB COLLATE=utf8mb4_unicode_520_ci; CREATE USER 'adbp'@'localhost' IDENTIFIED BY '%s'; GRANT SELECT,INSERT,UPDATE,DELETE ON adb_owned.%s TO 'adbp'@'localhost';" "$table" "$QUALIFICATION_PASSWORD" "$table" | root_sql
node scripts/ci/source-policy-linux-qualification-controller.mjs proof
cleanup
trap - EXIT
printf 'OWNED_QUALIFICATION_COMPLETE source:%s groups:9 lab_only:1 production_wiring:0\n' "$GITHUB_SHA"
