"""Fixed owned UBI static prerequisite. Never load/execute candidate contents."""
import hashlib
import importlib.util
import json
import os
from pathlib import Path
import re
import stat
import sys
import time

ROOT = Path(__file__).resolve().parents[2]
spec = importlib.util.spec_from_file_location('legacy_acquire', ROOT / 'scripts/manual/source-policy-linux-minimum-acquire.py')
acquire = importlib.util.module_from_spec(spec)
spec.loader.exec_module(acquire)
s = acquire.static
spec = importlib.util.spec_from_file_location('ubi_provenance', ROOT / 'scripts/manual/source_policy_ubi_provenance.py')
provenance = importlib.util.module_from_spec(spec)
spec.loader.exec_module(provenance)
PIN = '14bab8cc945c08109a2175e844d46f4eaff1fbf4f59e95dcf2f9cac7961510e1'
CONFIG = '2a46b014d1a6182f9f8240db6bae96566cdb11178f878ffe794f15095b745f35'
LAYERS = (
    ('2920d84eafa0cf94806ab58f0a2124f7b2d35bcbb06fc89a9106dcc28efe397a', 39653524),
    ('46735ccec920ba5e6df52c8787af94e3d7ea0fedb92ca37f7e0279c89506e13a', 4771),
    ('189b0ff547a3cff8241702bd70b6be360d4841e72a74e438b3bd60d530e43007', 2001814),
    ('d982fdd339edcf849f97671ec949c25b570de306244a2b6b03e6441a586ffc8a', 6944875),
    ('82c48ac2fca221a7aa5d650e52f057a21f73cec9e1202ff995e30276c620c276', 338),
    ('444b4ce536f188518cbcc061ab236a7e8db460af28e5f870fe200a7ba0790b6b', 332),
    ('617276d6715b91a6fedc8af16badcec2e0520cc2c04a2f3b169027a47057abfb', 100462402),
    ('e6a3432e4f830821a37b4186e566ae46c3d9f09844600cb3b78d469c8d7ad8d5', 116),
    ('d1540db65d371923fb149b6317345a5c5670c090f8506e30947d59463716d300', 4018),
    ('c1c3acb5fb6a522a08e27e306a765c58b15407644a6d204e2c57035a840bf07a', 8227),
)
WANTED = ('usr/sbin/mariadbd', 'lib64/ld-linux-x86-64.so.2')
BOUNDED_SOURCES = (
    'scripts/ci/source-policy-legacy-ubi-static.py', '.github/workflows/source-policy-legacy-ubi-static.yml',
    'scripts/manual/source-policy-linux-minimum-acquire.py', 'scripts/manual/source_policy_linux_minimum_static.py',
    'scripts/manual/source_policy_ubi_provenance.py', 'scripts/manual/source_policy_ubi_alias_static.py',
    'scripts/manual/source_policy_virtual_closure.py', 'scripts/manual/source_policy_ubi_elf_static.py',
)


def admit(manifest, config):
    s.require(manifest.get('schemaVersion') == 2 and manifest.get('mediaType') == 'application/vnd.oci.image.manifest.v1+json', 'manifest-profile')
    s.require(s.descriptor(manifest['config'], s.MIB) == (CONFIG, 13902), 'config-descriptor')
    layers = manifest.get('layers')
    s.require(isinstance(layers, list) and len(layers) == 10, 'layer-count')
    for row, expected in zip(layers, LAYERS):
        s.require(s.descriptor(row, 128 * s.MIB) == expected
                  and row.get('mediaType') == 'application/vnd.oci.image.layer.v1.tar+gzip', 'layer-identity')
    s.require(sum(size for _, size in LAYERS) == 149080417 < 256 * s.MIB, 'compressed-budget')
    s.require(config.get('os') == 'linux' and config.get('architecture') == 'amd64', 'platform')
    fs, cfg = config['rootfs'], config['config']
    s.require(fs['type'] == 'layers' and len(fs['diff_ids']) == 10, 'diff-id-count')
    for value in fs['diff_ids']:
        s.digest(value)
    s.require(cfg.get('User') == 'mysql' and cfg.get('Entrypoint') == ['docker-entrypoint.sh']
              and cfg.get('Cmd') == ['mariadbd'] and cfg.get('Volumes') == {'/var/lib/mysql': {}}, 'entry-volume')
    s.require(cfg.get('Env') == ['PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
                               'container=oci', 'GOSU_VERSION=1.17', 'MARIADB_VERSION=10.6.23'], 'environment')
    return True


def target(env):
    s.require(env.get('GITHUB_ACTIONS') == 'true' and env.get('GITHUB_EVENT_NAME') == 'workflow_dispatch'
              and env.get('GITHUB_REPOSITORY') == 'NichlasB/alynt-drime-backups-dashboard'
              and env.get('GITHUB_REF') == 'refs/heads/master'
              and env.get('GITHUB_WORKFLOW_REF') == 'NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-legacy-ubi-static.yml@refs/heads/master'
              and re.fullmatch('[a-f0-9]{40}', env.get('EXPECTED_SHA', ''))
              and env['EXPECTED_SHA'] == env.get('GITHUB_SHA')
              and all(re.fullmatch('[0-9]+', env.get(k, '')) for k in ('GITHUB_RUN_ID', 'GITHUB_RUN_ATTEMPT')), 'exact-job')
    parent = Path(env['RUNNER_TEMP'])
    s.require(parent.is_absolute() and parent.resolve() == parent and not parent.is_symlink(), 'temporary-parent')
    return parent / ('adb-legacy-ubi-' + env['GITHUB_RUN_ID'] + '-' + env['GITHUB_RUN_ATTEMPT'])


class Owned(acquire.Owned):
    def __init__(self, root):
        self.root, self.parent = root, root.parent
        self.files, self.reserved, self.total = set(), set(), 0
        root.mkdir(mode=0o700)
        self.write('owner.json', json.dumps({'root': root.name, 'head': os.environ.get('EXPECTED_SHA')}, sort_keys=True).encode())

    def cleanup(self):
        return cleanup(self.root)


def cleanup(root):
    if not root.exists() and not root.is_symlink():
        return 0
    info = root.lstat()
    s.require(stat.S_ISDIR(info.st_mode) and not root.is_symlink() and root.resolve() == root
              and info.st_uid == os.getuid() and stat.S_IMODE(info.st_mode) == 0o700, 'owned-root')
    allowed = {'owner.json', 'manifest.json', 'config.json'} | {sha + '.blob' for sha, _ in LAYERS}
    children = list(root.iterdir())
    s.require(len(children) <= 13, 'owned-count')
    for path in children:
        entry = path.lstat()
        s.require(path.name in allowed and stat.S_ISREG(entry.st_mode) and not path.is_symlink()
                  and entry.st_uid == os.getuid() and entry.st_nlink == 1, 'owned-membership')
    if children:
        marker = root / 'owner.json'
        s.require(marker.is_file() and marker.stat().st_size <= 256, 'owner-marker')
        s.require(marker.read_bytes() == json.dumps({'root': root.name, 'head': os.environ.get('EXPECTED_SHA')}, sort_keys=True).encode(), 'owner-binding')
    for path in children:
        path.unlink()
    root.rmdir()
    s.require(not root.exists(), 'cleanup-unconfirmed')
    return len(children)


def policy_flags(arguments, alias_value, elf_value):
    s.require(type(arguments) is list and len(arguments) == 1 and type(arguments[0]) is str
              and arguments[0] in ('--approved-static-prerequisite', '--approved-alias-static-prerequisite', '--approved-elf-static-prerequisite'), 'explicit-scope')
    inspect_elf = arguments[0] == '--approved-elf-static-prerequisite'
    alias_mode = inspect_elf or arguments[0] == '--approved-alias-static-prerequisite'
    s.require(type(alias_value) is str and alias_value == ('true' if alias_mode else 'false'), 'explicit-alias-mode')
    s.require(type(elf_value) is str and elf_value == ('true' if inspect_elf else 'false'), 'explicit-elf-mode')
    return alias_mode, inspect_elf


def selection(arguments, alias_value, elf_value, bounded_value='false'):
    s.require(type(bounded_value) is str and bounded_value in ('true', 'false'), 'explicit-bounded-mode')
    if bounded_value == 'false':
        return (*policy_flags(arguments, alias_value, elf_value), False)
    s.require(type(arguments) is list and len(arguments) == 1 and type(arguments[0]) is str
              and arguments == ['--approved-bounded-elf-static-prerequisite'], 'explicit-scope')
    s.require(type(alias_value) is str and alias_value == 'true', 'explicit-alias-mode')
    s.require(type(elf_value) is str and elf_value == 'true', 'explicit-elf-mode')
    return True, True, True


def create_work_authority(alias, config, frozen, deadline):
    s.require(alias.PIN == PIN and alias.CONFIG == CONFIG, 'alias-candidate-binding')
    head, run, attempt = (os.environ[k] for k in ('GITHUB_SHA', 'GITHUB_RUN_ID', 'GITHUB_RUN_ATTEMPT'))
    s.require(re.fullmatch('[a-f0-9]{40}', head) and all(re.fullmatch('[0-9]+', value) for value in (run, attempt)), 'exact-job')
    return alias.v._FixedWorkAuthority(dict(manifest=PIN, config=CONFIG,
        source=hashlib.sha256(json.dumps(frozen, sort_keys=True).encode()).hexdigest()),
        [(layer[0], s.digest(value)) for layer, value in zip(LAYERS, config['rootfs']['diff_ids'])],
        dict(head=head, run=hashlib.sha256((run+':'+attempt).encode()).hexdigest()[:32], profile='bounded-strings-v1'), deadline)


def compact_layer(collected):
    return {key: collected[key] for key in ('metadata', 'elf', 'elf_bindings')}


def apply_bounded_layer(alias, tree, work_owner, collected, ordinal):
    if ordinal == 0:
        s.require(tree is None, 'single-model')
        tree = alias._owned_model(work_owner, collected)
    else:
        s.require(type(tree) is alias.v.VirtualClosure and tree._work_owner is work_owner, 'single-model')
    tree.apply(dict(collected['metadata'], ordinal=ordinal), collected['records'], work_owner.now())
    return tree, compact_layer(collected)


def bounded_graph(alias, elf, tree, work_owner, layers):
    roles = {key: tree.resolve(key, work_owner.now()) for key in WANTED}
    elf.admit_selected(roles)
    return roles, alias._owned_graph(tree, work_owner, layers)


def successful(report, bounded):
    common = (report.get('status') in ('selected-origins-verified', 'static-dt-needed-closure-verified')
              and report.get('source_unchanged') is True and report.get('cleanup') == 'complete'
              and report.get('reporting') == 'complete' and report.get('reported') is True
              and all(report.get(k) is False for k in ('runtime_grant', 'candidate_execution', 'image_loading', 'database_operations', 'rootfs_extraction')))
    if not bounded: return common
    packet = provenance.owner_snapshot(report.get('work_owner'))
    return bool(common and report.get('status') == 'static-dt-needed-closure-verified'
                and report.get('access_profile') == 'bounded-strings-v1' and packet is not None
                and packet['state'] == 'closed-model' and packet['model_bound'] is True
                and packet['verified_layers'] == report.get('layers_verified') == report.get('namespace_layers_committed') == 10
                and report.get('elf_graph', {}).get('static_dependency_closure_verified') is True
                and report['elf_graph'].get('runtime_grant') is False and report['elf_graph'].get('model_only') is True)


def finish_report(report, root, frozen, work_owner=None):
    # Readback/accounting/report failures cannot bypass exact ownership cleanup.
    try:
        report['source_unchanged'] = bool(frozen) and all(acquire.file_hash(ROOT / p) == sha for p, sha in frozen.items())
        report['source_sha256'] = hashlib.sha256(json.dumps(frozen, sort_keys=True).encode()).hexdigest()
    except Exception:
        report['source_unchanged'] = False
    if not report['source_unchanged']:
        report['status'] = 'refused'; report.setdefault('failure', 'source-readback')
    if work_owner is not None:
        try: report['work_owner'] = provenance.owner_snapshot(work_owner.snapshot())
        except Exception: report['work_owner'] = None
        if report['work_owner'] is None:
            report['status'] = 'refused'; report.setdefault('failure', 'owner-accounting')
    try:
        report['owned_files_removed'] = cleanup(root)
        report['cleanup'] = 'complete'
    except Exception:
        report['cleanup'] = 'unconfirmed'; report['status'] = 'refused'; report.setdefault('failure', 'owned-cleanup')
    report['reporting'], report['reported'] = 'complete', True
    try: print(json.dumps(report, separators=(',', ':')), flush=True)
    except Exception:
        report['reporting'], report['reported'], report['status'] = 'unconfirmed', False, 'refused'
        report.setdefault('failure', 'reporting-failed')


def retain_string_table_diagnostic(report, value, elf):
    if elf is None or 'elf_string_table_facts' in report: return
    try: report['elf_string_table_facts'] = elf.string_table_packet(value)
    except Exception: report['elf_string_table_facts'] = None


def proof(root, alias_policy=False, elf_policy=False, bounded_access_policy=False):
    s.require(all(type(value) is bool for value in (alias_policy, elf_policy, bounded_access_policy))
              and (not elf_policy or alias_policy) and (not bounded_access_policy or elf_policy), 'explicit-elf-mode')
    owner, phase, budget, work_owner = None, 'metadata', None, None
    report = {'status': 'refused', 'candidate_execution': False, 'image_loading': False,
              'database_operations': False, 'rootfs_extraction': False, 'layers_verified': 0,
              'runtime_grant': False, 'alias_policy': alias_policy, 'elf_policy': elf_policy, 'namespace_layers_committed': 0}
    if bounded_access_policy: report['access_profile'] = 'bounded-strings-v1'
    report['tool'] = {'python': sys.version.split()[0], 'ssl': acquire.ssl.OPENSSL_VERSION,
                      'unicode_db': s.unicodedata.unidata_version}
    paths = (Path(__file__), Path(acquire.__file__), Path(s.__file__), Path(provenance.__file__),
             ROOT / '.github/workflows/source-policy-legacy-ubi-static.yml')
    alias, elf = None, None
    if alias_policy is True:
        spec = importlib.util.spec_from_file_location('fixed_ubi_alias', ROOT / 'scripts/manual/source_policy_ubi_alias_static.py')
        alias = importlib.util.module_from_spec(spec)
        spec.loader.exec_module(alias)
        paths += (Path(alias.__file__), Path(alias.v.__file__))
        if elf_policy is True:
            elf = alias.load('source_policy_ubi_elf_static.py', 'fixed_ubi_elf')
            paths += (Path(elf.__file__),)
    frozen = {p.relative_to(ROOT).as_posix(): acquire.file_hash(p) for p in paths}
    if bounded_access_policy: s.require(set(frozen) == set(BOUNDED_SOURCES), 'fixed-source-closure')
    deadline = time.monotonic() + 300
    try:
        owner = Owned(root)
        client = acquire.Client(owner, deadline)
        base = 'https://registry-1.docker.io/v2/library/mariadb'
        token = s.decode(client.get('https://auth.docker.io/token?service=registry.docker.io&scope=repository:library/mariadb:pull', 65536))['token']
        s.require(isinstance(token, str) and len(token) <= 16384, 'read-token')
        raw = client.get(base + '/manifests/sha256:' + PIN, s.MIB, token, expected_hash=PIN)
        manifest = s.decode(raw)
        owner.write('manifest.json', raw)
        raw = client.get(base + '/blobs/sha256:' + CONFIG, s.MIB, token, expected_size=13902, expected_hash=CONFIG, blob=True)
        config = s.decode(raw)
        owner.write('config.json', raw)
        admit(manifest, config)
        report['metadata_verified'] = True
        if bounded_access_policy: work_owner = create_work_authority(alias, config, frozen, deadline)
        roles, budget = {}, {'expanded': 0, 'entries': 0, 'deadline': deadline}
        tree, observations, compact = None, {}, []
        for i, ((sha, size), diff) in enumerate(zip(LAYERS, config['rootfs']['diff_ids'])):
            phase = 'layer-' + str(i)
            path = owner.path(sha + '.blob')
            remaining = 256 * s.MIB - client.payload
            s.require(remaining > 0, 'compressed-budget')
            client.get(base + '/blobs/sha256:' + sha, min(128 * s.MIB, remaining), token, path, size, sha, True)
            if alias_policy:
                if bounded_access_policy:
                    collected = alias.collect(path, sha, size, diff, budget, elf_policy=True, work_owner=work_owner, layer_ordinal=i)
                else:
                    collected = alias.collect(path, sha, size, diff, budget, elf_policy=elf_policy)
                report['layers_verified'] += 1
                scan = collected['scan']
                if i == 0:
                    s.require(alias.PIN == PIN and alias.CONFIG == CONFIG, 'alias-candidate-binding')
                    if not bounded_access_policy:
                        tree = alias.v.VirtualClosure(dict(manifest=PIN, config=CONFIG,
                            source=hashlib.sha256(json.dumps(frozen, sort_keys=True).encode()).hexdigest()),
                            [(layer[0], s.digest(value)) for layer, value in zip(LAYERS, config['rootfs']['diff_ids'])],
                            alias.approved_profile(collected, i), hardlinks=True)
                if elf_policy and not bounded_access_policy:
                    tree.charge(collected['elf_work'], int((time.monotonic()-deadline+300)*1000))
                if bounded_access_policy:
                    tree, envelope = apply_bounded_layer(alias, tree, work_owner, collected, i)
                else:
                    tree.apply(dict(collected['metadata'], ordinal=i), collected['records'], int((time.monotonic()-deadline+300)*1000))
                report['namespace_layers_committed'] += 1
                if bounded_access_policy:
                    compact.append(envelope)
                    del envelope, collected  # No retained complete layer record journal.
                elif elf_policy:
                    observations.update({str(i)+':'+str(index): value for index, value in collected['elf'].items()})
            else:
                scan = s.scan_layer(path, diff, WANTED, roles, budget)
                report['layers_verified'] += 1
            print(json.dumps({'phase': phase, 'scan': scan}), flush=True)
        if alias_policy:
            phase = 'final-namespace'
            tree.finalize(work_owner.now() if bounded_access_policy else int((time.monotonic()-deadline+300)*1000))
            if not bounded_access_policy:
                roles = {key: tree.resolve(key, int((time.monotonic()-deadline+300)*1000)) for key in WANTED}
        if not bounded_access_policy:
            s.require(set(roles) == set(WANTED), 'critical-roles')
            report['roles'] = roles
        # A successful selected-origin scan is not a runtime/dependency grant.
        if elf_policy:
            phase = 'elf-dependency-closure'
            if bounded_access_policy:
                roles, report['elf_graph'] = bounded_graph(alias, elf, tree, work_owner, compact)
            else:
                elf.admit_selected(roles)
                report['elf_graph'] = elf.graph(tree, observations, deadline)
            report.update(status='static-dt-needed-closure-verified', runtime_feasibility='not-run', dependency_closure='static-dt-needed-only')
        else:
            report.update(status='selected-origins-verified', runtime_feasibility='not-run', dependency_closure='not-established')
        s.require(set(roles) == set(WANTED), 'critical-roles')
        report['roles'] = roles
    except Exception as error:
        if work_owner is not None: work_owner.fail(error)
        refusals = (s.Refusal, alias.s.Refusal, alias.v.Refusal) if alias is not None else (s.Refusal,)
        if elf is not None: refusals += (elf.Refusal,)
        code = str(error) if isinstance(error, refusals) else 'unexpected-tool-failure'
        report.update(failure_stage=phase, failure=code if re.fullmatch('[a-z0-9-]{1,64}', code) else 'bounded-refusal')
        if isinstance(error, s.PathRefusal):
            report['path_facts'] = error.facts
        if alias is not None and isinstance(error, alias.s.Refusal) and 'alias_facts' in error.__dict__:
            report['alias_facts'] = error.alias_facts
        if alias is not None and isinstance(error, alias.v.Refusal) and 'hardlink_facts' in error.__dict__:
            report['hardlink_facts'] = error.hardlink_facts
        if elf is not None and 'elf_facts' in error.__dict__:
            try: report['elf_facts'] = elf.failure_packet(error.elf_facts)
            except Exception: report['elf_facts'] = None
        if 'elf_string_table_facts' in error.__dict__:
            retain_string_table_diagnostic(report, error.elf_string_table_facts, elf)
        if type(error) is s.Refusal and error.__dict__.get('critical_facts') is not None:
            try:
                context = dict(head=os.environ['GITHUB_SHA'], image=hashlib.sha256(json.dumps(frozen, sort_keys=True).encode()).hexdigest(),
                    run=hashlib.sha256((os.environ['GITHUB_RUN_ID']+':'+os.environ['GITHUB_RUN_ATTEMPT']).encode()).hexdigest()[:32],
                    manifest=PIN, config=CONFIG, layer=i, compressed=sha, compressed_verified=True, diff_id_verified=False)
                report['critical_provenance'] = provenance.packet(error.critical_facts, context)
            except Exception:
                report['critical_provenance'] = None  # Preserve the original reason.
    finally:
        try:
            if budget is not None:
                report['scan_budget'] = {key: budget[key] for key in ('expanded', 'entries', 'elf_objects', 'elf_work', 'elf_metadata') if key in budget}
        except Exception:
            report['scan_budget'] = None; report['status'] = 'refused'; report.setdefault('failure', 'scan-accounting')
        finally:
            finish_report(report, root, frozen, work_owner)
    return 0 if successful(report, bounded_access_policy) else 1


if __name__ == '__main__':
    try:
        s.require(sys.flags.isolated and sys.dont_write_bytecode and sys.platform == 'linux', 'isolated-linux-tool')
        root = target(os.environ)
        s.require(sys.argv[1:] in (['--approved-static-prerequisite'], ['--approved-alias-static-prerequisite'], ['--approved-elf-static-prerequisite'], ['--approved-bounded-elf-static-prerequisite'], ['--cleanup']), 'explicit-scope')
        if sys.argv[1:] == ['--cleanup']:
            print(json.dumps({'always_cleanup': 'complete', 'files_removed': cleanup(root)}))
        else:
            mode, inspect_elf, bounded = selection(sys.argv[1:], os.environ.get('ALIAS_POLICY', 'false'),
                os.environ.get('ELF_POLICY', 'false'), os.environ.get('BOUNDED_ACCESS_POLICY', 'false'))
            sys.exit(proof(root, mode, inspect_elf, bounded))
    except s.Refusal:
        print('{"status":"scope-or-cleanup-refused"}')
        sys.exit(1)
