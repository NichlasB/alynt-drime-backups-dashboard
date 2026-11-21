"""ONE fixed PPA tuple: DATA inventory and owned cleanup, never installation."""
import hashlib
import json
import os
from pathlib import Path
import re
import signal
import stat
import sys
import urllib.error
import urllib.parse
import urllib.request

sys.path.insert(0, str(Path(__file__).resolve().parent))
from source_policy_deb_data import A, S, PINS, BASE, REVISION, Budget, Slice, ar_members, tar_inventory
from source_policy_deb_stream import Decoder, preflight

WORKFLOW = 'source-policy-ppa-deb-static.yml'


def http_refusal(error):
    # Status only: never URL, headers, message, body or transport credentials.
    code = error.code
    return {'reason': 'ppa-http-refusal', 'http_status': code if type(code) is int and 100 <= code <= 599 else 'Unknown'}


def filename(package):
    S.require(package in {row[0] for row in PINS}, 'package-selection')
    return package+'_'+REVISION+'_amd64.deb'


def target(env):
    S.require(env.get('GITHUB_ACTIONS') == 'true' and env.get('GITHUB_EVENT_NAME') == 'workflow_dispatch'
              and env.get('GITHUB_REPOSITORY') == 'NichlasB/alynt-drime-backups-dashboard'
              and env.get('GITHUB_REF') == 'refs/heads/master' and env.get('GITHUB_ACTOR') == 'NichlasB'
              and env.get('GITHUB_WORKFLOW_REF') == 'NichlasB/alynt-drime-backups-dashboard/.github/workflows/'+WORKFLOW+'@refs/heads/master'
              and re.fullmatch('[a-f0-9]{40}', env.get('EXPECTED_SHA', ''))
              and env['EXPECTED_SHA'] == env.get('GITHUB_SHA') and env.get('GITHUB_RUN_ATTEMPT') == '1'
              and re.fullmatch('[0-9]+', env.get('GITHUB_RUN_ID', '')), 'exact-job')
    parent = Path(env['RUNNER_TEMP'])
    S.require(parent.is_absolute() and parent.resolve() == parent and not parent.is_symlink(), 'temporary-parent')
    return parent/('adb-ppa-deb-'+env['GITHUB_RUN_ID'])


def marker(root):
    return json.dumps({'head': os.environ['EXPECTED_SHA'], 'root': root.name}, sort_keys=True).encode('ascii')


def cleanup(root):
    if not root.exists() and not root.is_symlink():
        return 0
    info = root.lstat()
    S.require(stat.S_ISDIR(info.st_mode) and not root.is_symlink() and root.resolve() == root
              and info.st_uid == os.getuid() and stat.S_IMODE(info.st_mode) == 0o700, 'owned-root')
    allowed = {'owner.json'} | {filename(row[0]) for row in PINS}
    children = []
    with os.scandir(root) as entries:
        for entry in entries:
            S.require(len(children) < 4 and entry.name in allowed, 'owned-membership')
            path = root/entry.name
            item = path.lstat()
            S.require(stat.S_ISREG(item.st_mode) and item.st_uid == os.getuid() and item.st_nlink == 1, 'owned-file')
            children.append(path)
    owner = root/'owner.json'
    S.require(owner.stat().st_size <= 256 and owner.read_bytes() == marker(root), 'owned-marker')
    for path in children:
        if path != owner:
            path.unlink()
    owner.unlink()
    root.rmdir()
    S.require(not root.exists(), 'cleanup-remaining')
    return 0


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        raise S.Refusal('ppa-redirect-refused')


def download(root, pin, budget):
    package, expected_size, expected_sha = pin
    path = root/filename(package)
    uri = BASE+urllib.parse.quote(path.name)
    opener = urllib.request.build_opener(urllib.request.ProxyHandler({}), NoRedirect())
    size, digest = 0, hashlib.sha256()
    with opener.open(uri, timeout=10) as response, path.open('xb') as output:
        S.require(response.status == 200 and response.headers.get('Content-Length') == str(expected_size)
                  and response.headers.get('Content-Encoding') in (None, 'identity'), 'ppa-response')
        while True:
            chunk = response.read(65536)
            if not chunk:
                break
            size += len(chunk)
            budget.charge(len(chunk))
            S.require(size <= expected_size, 'ppa-size')
            digest.update(chunk)
            output.write(chunk)
    S.require(size == expected_size and digest.hexdigest() == expected_sha, 'ppa-identity')
    return {'package': package, 'bytes': size, 'sha256': digest.hexdigest()}


def inspect(root, budget):
    results = []
    for package, size, sha in PINS:
        with (root/filename(package)).open('rb') as file:
            members = ar_members(file, budget)
            result = {'package': package, 'streams': []}
            for (name, start, length), category in zip(members, ('control', 'data')):
                source = Slice(file, start, length, budget)
                suffix = name[len(category+'.tar'):]
                decoder = Decoder(source, suffix, budget) if suffix else None
                try:
                    facts = tar_inventory(decoder or source, budget, package, category)
                finally:
                    if decoder:
                        decoder.close()
                S.require(source.remaining == 0, 'member-consumption')
                result['streams'].append({'member': name, 'compressed_bytes': length, 'decoder_reaped': decoder is not None, **facts})
            results.append(result)
    S.require(set(budget.roles) == {'cli', 'mysqlnd', 'mysqli'}, 'selected-roles-missing')
    return {'packages': results, 'roles': budget.roles, 'entries': budget.entries, 'work_bytes': budget.total,
            'types': budget.types, 'inventory_sha256': budget.inventory.hexdigest(), 'dependency_closure_verified': False}


def main():
    root = target(os.environ)
    def expired(signum, frame):
        raise S.Refusal('job-deadline')
    signal.signal(signal.SIGALRM, expired)
    if sys.argv[1:] == ['--cleanup']:
        signal.alarm(30)
        cleanup(root)
        signal.alarm(0)
        print('{"owned_deb_remaining":0}')
        return 0
    S.require(sys.argv[1:] == ['--approved-static-proof'], 'entry-mode')
    receipt = {'format': 'ADBPPADATA1', 'extracted': False, 'executed': False, 'qualified': False, 'static_inventory_complete': False}
    created = False
    signal.alarm(540)
    try:
        S.require(not root.exists() and not root.is_symlink(), 'preexisting-owned-root')
        budget = Budget()
        receipt['preflight'] = preflight()
        root.mkdir(mode=0o700)
        created = True
        with (root/'owner.json').open('xb') as owner:
            data = marker(root)
            budget.charge(len(data))
            owner.write(data)
        receipt['identities'] = []
        for pin in PINS:
            receipt['identities'].append(download(root, pin, budget))
        receipt['inventory'] = inspect(root, budget)
        receipt['static_inventory_complete'] = True
        receipt['reason'] = 'data-inventory-only-no-runtime-admission'
    except S.Refusal as error:
        receipt['reason'] = str(error)
    except urllib.error.HTTPError as error:
        try:
            receipt.update(http_refusal(error))
        finally:
            error.close()
    except Exception as error:
        receipt['reason'], receipt['error_class'] = 'bounded-proof-error', type(error).__name__
    finally:
        signal.alarm(30)
        if created:
            cleanup(root)
        receipt['owned_deb_remaining'] = 'Unknown' if root.exists() or root.is_symlink() else 0
        signal.alarm(0)
    output = json.dumps(receipt, sort_keys=True)
    S.require(len(output.encode('ascii')) <= 65536, 'diagnostic-bound')
    print(output)
    return 0 if receipt['static_inventory_complete'] else 1


if __name__ == '__main__':
    sys.exit(main())
