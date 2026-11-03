#!/usr/bin/env python3
"""Default-off owned ABI profile. No candidate, SQL, installation or site entry."""
import base64
import hashlib
import importlib.util
import json
import os
from pathlib import Path
import platform
import re
import selectors
import signal
import stat
import subprocess
import sys
import time

ROOT = Path(__file__).resolve().parents[2]
WORKFLOW = 'NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-owned-abi-canary.yml@refs/heads/master'


def load(name, relative):
    spec=importlib.util.spec_from_file_location(name, ROOT/relative)
    module=importlib.util.module_from_spec(spec); spec.loader.exec_module(module)
    return module


c=load('owned_abi_contract', 'scripts/manual/source_policy_abi_canary_contract.py')


class Owner:
    def __init__(self):
        self.started=time.monotonic(); self.phase='admission'; self.observed=0; self.control=0; self.native=0
        self.cleaning=False; self.cleanup_bytes=0
        self.diagnostics=[]; self.profile_diagnostic=None
        env=os.environ
        c.need(sys.argv[1:] in (['--approved-owned-abi-proof'], ['--cleanup-only'])
            and platform.system() == 'Linux' and platform.machine() == 'x86_64'
            and env.get('GITHUB_ACTIONS') == 'true' and env.get('GITHUB_EVENT_NAME') == 'workflow_dispatch'
            and env.get('GITHUB_REPOSITORY') == 'NichlasB/alynt-drime-backups-dashboard'
            and env.get('GITHUB_REF') == 'refs/heads/master' and env.get('GITHUB_WORKFLOW_REF') == WORKFLOW
            and env.get('AUDIT_RUNNER_ENVIRONMENT') == 'github-hosted'
            and env.get('ENABLE_OWNED_ABI') == 'true' and env.get('EXPECTED_SHA') == env.get('GITHUB_SHA')
            and not any(k.startswith('DOCKER_') for k in env), 'fixed-entry')
        c.need(Path.cwd() == ROOT and str(ROOT) == env.get('GITHUB_WORKSPACE'), 'workspace')
        self.source=self.freeze()
        self.frame=c.context(env.get('GITHUB_SHA'),env.get('GITHUB_RUN_ID'),env.get('GITHUB_RUN_ATTEMPT'),self.source)
        temporary=Path(env['RUNNER_TEMP'])
        c.need(temporary.is_dir() and temporary.resolve() == temporary, 'temporary-root')
        self.stage=temporary/('adb-owned-abi-'+self.frame['run']+'-'+self.frame['attempt'])
        self.labels=c.labels(self.frame); self.owner=self.labels[c.LABEL]
        self.image=None; self.image_intent=False
        self.env={'PATH':'/usr/bin:/bin','HOME':str(self.stage),'TMPDIR':str(self.stage),'LANG':'C','LC_ALL':'C'}
        c.need(self.run(['/usr/bin/git','rev-parse','HEAD']).strip().decode() == self.frame['head'], 'exact-head')
        self.run(['/usr/bin/git','diff','--no-ext-diff','--no-textconv','--exit-code','HEAD','--',*c.SOURCES])

    def freeze(self):
        rows=[]
        for relative in c.SOURCES:
            path=ROOT/relative; info=path.lstat()
            c.need(stat.S_ISREG(info.st_mode) and path.resolve() == path and info.st_size <= 131072, 'regular-source')
            rows.append((relative,c.sha(path.read_bytes())))
        return c.sha(c.encode(rows))

    def run(self, argv, timeout=10, limit=65536, input_path=None, native=False, cleanup=False):
        c.need(type(argv) is list and argv and all(type(a) is str and '\x00' not in a for a in argv), 'closed-argv')
        # No shell or inherited credentials. Kill/reap the owned command group on any failure.
        cleanup=cleanup or self.cleaning
        c.need(cleanup or time.monotonic()-self.started+timeout < 510, 'cleanup-reserve')
        if self.cleaning:
            timeout=min(timeout,self.cleanup_deadline-time.monotonic())
            c.need(timeout > 0, 'cleanup-deadline')
        out=bytearray(); err=bytearray(); deadline=time.monotonic()+timeout
        source=open(input_path,'rb') if input_path else subprocess.DEVNULL
        try:
            p=subprocess.Popen(argv, stdin=source, stdout=subprocess.PIPE, stderr=subprocess.PIPE,
                               env=self.env, cwd=ROOT, start_new_session=True)
            try:
                with selectors.DefaultSelector() as selector:
                    selector.register(p.stdout,selectors.EVENT_READ,out); selector.register(p.stderr,selectors.EVENT_READ,err)
                    while selector.get_map():
                        c.need(time.monotonic() < deadline, 'command-deadline')
                        for key,_ in selector.select(min(.1,max(0,deadline-time.monotonic()))):
                            chunk=os.read(key.fileobj.fileno(),8192)
                            if not chunk:
                                selector.unregister(key.fileobj); continue
                            key.data.extend(chunk); self.observed+=len(chunk)
                            c.need(len(out)+len(err) <= limit and self.observed <= 2097152, 'command-output-budget')
                    p.wait(timeout=max(.01,deadline-time.monotonic()))
                if native:
                    self.native+=len(out)+len(err); c.need(self.native <= 16384, 'native-output-budget')
                elif cleanup:
                    self.cleanup_bytes+=len(out)+len(err); c.need(self.cleanup_bytes <= 524288, 'cleanup-output-budget')
                else:
                    self.control+=len(out)+len(err); c.need(self.control <= 524288, 'control-budget')
                if p.returncode != 0 or err:
                    self.diagnostics.append(dict(phase=self.phase,returncode=p.returncode,stderr_bytes=len(err),
                                                 stderr_sha=c.sha(bytes(err))))
                    raise c.Refusal('bounded-command-failed')
                return bytes(out)
            finally:
                if p.poll() is None:
                    os.killpg(p.pid,signal.SIGKILL)
                p.wait(timeout=2); p.stdout.close(); p.stderr.close()
        finally:
            if input_path:
                source.close()

    def docker(self, *args, **kwargs):
        return self.run(['/usr/bin/docker','--host','unix:///var/run/docker.sock',*args],**kwargs)

    def inspect(self, identity):
        c.need(re.fullmatch('(sha256:)?[a-f0-9]{64}',identity), 'full-identity')
        return json.loads(self.docker('inspect',identity,limit=65536))[0]

    def ids(self, kind):
        args=('ps','--all','--no-trunc','--quiet') if kind == 'containers' else ('image','ls','--all','--no-trunc','--quiet')
        wire=self.docker(*args,'--filter','label='+c.LABEL+'='+self.owner,cleanup=True).decode()
        ids=list(dict.fromkeys(wire.splitlines()))
        c.need(len(ids) <= 6 and all(re.fullmatch('[a-f0-9]{64}' if kind == 'containers'
                else 'sha256:[a-f0-9]{64}',item) for item in ids), 'owned-inventory')
        return ids

    def write(self, name, data):
        c.need(re.fullmatch('[a-zA-Z0-9_.-]{1,64}',name), 'stage-name')
        with open(self.stage/name,'xb') as target:
            target.write(data)

    def marker(self):
        data=dict(frame=self.frame,image=self.image,image_intent=self.image_intent)
        path=self.stage/'marker.json'; temporary=self.stage/'marker.next'
        c.need(not temporary.exists(), 'marker-swap')
        self.write('marker.next',c.encode(data)); temporary.replace(path)

    def prerequisites(self):
        self.phase='existing-tool-runtime-audit'
        c.need(self.run(['/usr/bin/docker','context','inspect','--format','{{.Endpoints.docker.Host}}']).strip()
               == b'unix:///var/run/docker.sock', 'local-engine')
        version=json.loads(self.docker('version','--format','{{json .}}'))
        for key,commit in (('Server','6430e49'),('Client','b8034c0')):
            row=version[key]
            c.need(row['Version'] == '28.0.4' and row['GitCommit'] == commit
                   and row['Os'] == 'linux' and row['Arch'] == 'amd64', 'pinned-engine-client')
        info=json.loads(self.docker('info','--format','{{json .}}'))
        c.need(info['DefaultRuntime'] == 'runc' and info['Runtimes']['runc']['path'] == 'runc'
               and 'name=seccomp,profile=builtin' in info['SecurityOptions']
               and 'name=apparmor' in info['SecurityOptions'], 'runtime-profile')
        runtime=self.run(['/usr/bin/runc','--version'],limit=8192)
        c.need(re.fullmatch(rb'runc version 1\.5\.2\ncommit: v1\.5\.2-0-g29dd3dc2\nspec: 1\.3\.0\n'
                           rb'go: go[0-9.]+\nlibseccomp: 2\.5\.5\n',runtime), 'runtime-library')
        components=[r for r in version['Server']['Components'] if r['Name'] == 'runc']
        c.need(len(components) == 1 and components[0]['Version'] == '1.5.2'
               and components[0]['Details']['GitCommit'] == 'v1.5.2-0-g29dd3dc2', 'runtime-mapping')
        tools={}
        for name in ('cc','as','ld'):
            path=Path('/usr/bin/'+name).resolve(); size=path.stat().st_size
            c.need(path.is_file() and 0 < size <= 33554432 and not path.stat().st_mode & 0o022, 'existing-tool')
            tools[name]=dict(path=str(path),sha=c.sha(path.read_bytes()),
                            version_sha=c.sha(self.run(['/usr/bin/'+name,'--version'],limit=16384)))
        c.need(self.run(['/usr/bin/cc','-dumpmachine']) == b'x86_64-linux-gnu\n', 'compiler-target')
        release=platform.release(); c.need(re.fullmatch('[a-zA-Z0-9.+_-]{1,96}',release), 'kernel-release')
        config=Path('/boot/config-'+release); kernel=dict(status='unavailable',boot_correspondence=False)
        try:
            info=config.lstat()
            c.need(stat.S_ISREG(info.st_mode) and config.resolve() == config and info.st_size <= 1048576, 'kernel-file')
            raw=config.read_bytes(); lines=raw.splitlines()
            declarations={}
            for key in ('CONFIG_IA32_EMULATION','CONFIG_X86_X32_ABI'):
                matches=[line.decode() for line in lines if line.startswith(key.encode()+b'=')
                         or line == ('# '+key+' is not set').encode()]
                c.need(len(matches) == 1 and matches[0] in (key+'=y',key+'=n','# '+key+' is not set'), 'kernel-declaration')
                declarations[key]='y' if matches[0] == key+'=y' else 'n'
            kernel.update(status='observed-file',sha=c.sha(raw),declarations=declarations)
        except (FileNotFoundError,PermissionError):
            pass # Explicit unavailable evidence; never an eligibility fallback.
        return dict(engine='28.0.4/6430e49',client='28.0.4/b8034c0',
                    runtime=runtime.decode().splitlines(),tools=tools,kernel=release,kernel_config=kernel)

    def build(self):
        self.phase='owned-static-build'
        c.need(not self.stage.exists() and not self.ids('containers') and not self.ids('images'), 'fresh-owned-namespace')
        self.stage.mkdir(mode=0o700); self.marker()
        for name in ('source-policy-abi-core.h','source-policy-abi-host.c','source-policy-abi-canary.c'):
            self.write(name,(ROOT/'scripts/ci'/name).read_bytes())
        for role in ('host','canary'):
            self.run(['/usr/bin/cc','-O2','-Wall','-Wextra','-Werror','-mno-red-zone','-fno-builtin','-fno-stack-protector',
                      '-fno-pie','-no-pie','-fno-asynchronous-unwind-tables','-fno-unwind-tables',
                      '-nostdlib','-nostdinc','-static','-Wl,--build-id=none,-z,noexecstack',
                      '-o',str(self.stage/role),str(self.stage/('source-policy-abi-'+role+'.c'))])
            c.elf((self.stage/role).read_bytes())
        c.need(self.freeze() == self.source, 'source-changed')
        self.phase='host-availability-only'
        host=c.numbers(self.run([str(self.stage/'host')],native=True),6)
        c.need(host[0] == os.getuid() and host[3] > 0 and host[4] == host[3]
               and host[1] in (0,1) and host[2] in (0,2), 'host-health')
        return dict(uid=host[0],nnp=host[1],seccomp=host[2],amd64_pid=host[3],i386_pid=host[4],x32_getpid=host[5])

    def import_owned(self):
        self.phase='owned-image-import'
        canary=(self.stage/'canary').read_bytes()
        self.image,archive,layer=c.image(canary,self.frame)
        inventory=self.docker('image','ls','--all','--no-trunc','--quiet').decode().splitlines()
        c.need(len(inventory) <= 256 and all(re.fullmatch('sha256:[a-f0-9]{64}',item) for item in inventory)
               and self.image not in inventory, 'preexisting-image')
        self.write('owned-image.tar',archive)
        self.image_intent=True; self.marker() # established absence + intent before fallible load
        self.docker('load','--quiet',input_path=self.stage/'owned-image.tar',timeout=60)
        row=self.inspect(self.image); c.image_readback(row,self.image,self.frame)
        c.need(row['RootFS'] == dict(Type='layers',Layers=['sha256:'+layer]), 'layer-readback')
        return dict(image=self.image,canary_sha=c.sha(canary),host_sha=c.sha((self.stage/'host').read_bytes()))

    def profile_readback(self, row, abi, security, point):
        # The old guard is authoritative; diagnostics run ONLY after its failure.
        try:
            c.container_readback(row,self.image,self.frame,abi,security)
        except Exception:
            if self.profile_diagnostic is None:
                try:
                    c.need(point in ('created','pre-start','post-exit'), 'diagnostic-point')
                    report=c.container_diagnostics(row,self.image,self.frame,abi,security)
                    phase=self.phase if self.phase in tuple(a+s for a in c.ABIS for s in ('-builtin','-derived')) else 'unclassified'
                    detail=dict(status='captured',phase=phase,point=point,report=report)
                    size=len(c.encode(detail))
                    c.need(size <= 8192 and self.control+size <= 524288 and self.observed+size <= 2097152,
                           'diagnostic-existing-budget')
                    self.control+=size; self.observed+=size; self.profile_diagnostic=detail
                except Exception:
                    self.profile_diagnostic=dict(status='unavailable',authority=False)
            raise # Preserve the exact first guard exception, even if diagnostics fail.

    def probe(self, abi, negative, policy):
        self.phase=abi+('-derived' if negative else '-builtin')
        name='adb-abi-'+self.owner[:24]+'-'+abi+('-d' if negative else '-b')
        security='seccomp='+policy.decode() if negative else 'seccomp=builtin'
        options=['create','--name',name,'--hostname','abi-canary','--network','none','--ipc','private','--cgroupns','private',
                 '--read-only','--cap-drop','ALL','--security-opt','no-new-privileges',
                 '--security-opt','seccomp='+str(self.stage/'policy.json') if negative else security,
                 '--security-opt','apparmor=docker-default',
                 '--user','65532:65532','--memory','512m','--memory-swap','512m','--cpus','1',
                 '--pids-limit','64','--restart','no','--log-driver','local','--log-opt','max-size=16k',
                 '--log-opt','max-file=1','--ulimit','nofile=64:64','--ulimit','core=0:0','--entrypoint','/canary']
        for key,value in self.labels.items():
            options+=['--label',key+'='+value]
        cid=self.docker(*options,self.image,abi).strip().decode()
        c.need(re.fullmatch('[a-f0-9]{64}',cid), 'created-cid')
        row=self.inspect(cid); self.profile_readback(row,abi,security,'created')
        c.need(row['State']['Status'] == 'created' and self.freeze() == self.source, 'before-start')
        target=self.stage/(abi+('-derived' if negative else '-builtin')+'.readback')
        c.need(not target.exists(), 'fresh-copy')
        self.docker('cp',cid+':/canary',str(target))
        c.need(target.is_file() and not target.is_symlink() and target.stat().st_size <= 131072
               and c.sha(target.read_bytes()) == c.sha((self.stage/'canary').read_bytes()), 'executable-readback')
        self.profile_readback(self.inspect(cid),abi,security,'pre-start')
        wire=self.docker('start','--attach',cid,timeout=15,limit=4096,native=True)
        ended=self.inspect(cid); self.profile_readback(ended,abi,security,'post-exit')
        c.need(ended['State']['Running'] is False and ended['State']['ExitCode'] == 0
               and ended['State']['OOMKilled'] is False and not ended['State']['Error'], 'native-exit')
        if abi == 'x32' and not negative:
            rows=c.numbers(wire,len(c.SUBS[2])+5)
            if rows[:3] == [65532,1,2] and all(n == -38 for n in rows[3:]):
                return dict(abi='x32',availability='ENOSYS-observed',qualification=False,output_sha=c.sha(wire))
        result=c.observation(wire,abi,negative)
        result['transport_sha']=c.sha(policy) if negative else None
        return result

    def cleanup(self):
        self.phase='owned-cleanup'
        self.cleaning=True; self.cleanup_deadline=time.monotonic()+30
        if self.stage.exists():
            c.need(self.stage.resolve() == self.stage and not self.stage.is_symlink()
                   and self.stage.stat().st_uid == os.getuid()
                   and stat.S_IMODE(self.stage.stat().st_mode) == 0o700, 'stage-ownership')
            marker=self.stage/'marker.json'
            c.need(marker.is_file() and not marker.is_symlink() and marker.stat().st_size <= 4096, 'owned-marker')
            data=json.loads(marker.read_bytes()); c.need(data['frame'] == self.frame, 'marker-context')
            self.image=data['image']; self.image_intent=data['image_intent']
        else:
            c.need(not self.ids('containers') and not self.ids('images'), 'unmarked-resources')
            return dict(containers=0,images=0,stage_absent=True)
        for cid in self.ids('containers'):
            row=self.inspect(cid)
            c.need(row['Config']['Labels'] == self.labels and row['Image'] == self.image, 'cleanup-cid-owner')
            if row['State']['Running']:
                self.docker('stop','--time','2',cid,timeout=5,cleanup=True)
            row=self.inspect(cid)
            c.need(row['Config']['Labels'] == self.labels and row['Image'] == self.image
                   and not row['State']['Running'], 'cleanup-stop-readback')
            self.docker('rm',cid,cleanup=True)
        c.need(not self.ids('containers'), 'containers-remain')
        images=self.ids('images')
        c.need(not images or (self.image_intent is True and images == [self.image]), 'cleanup-image-owner')
        for identity in images:
            c.image_readback(self.inspect(identity),identity,self.frame)
            self.docker('image','rm',identity,cleanup=True)
        c.need(not self.ids('images'), 'images-remain')
        allowed={'marker.json','owned-image.tar','policy.json','host','canary',
                 'source-policy-abi-core.h','source-policy-abi-host.c','source-policy-abi-canary.c'}
        allowed.update(abi+'-'+kind+'.readback' for abi in c.ABIS for kind in ('builtin','derived'))
        children=list(self.stage.iterdir())
        c.need(all(p.name in allowed and p.is_file() and not p.is_symlink() and p.stat().st_uid == os.getuid()
                   for p in children), 'stage-inventory')
        for path in children:
            path.unlink()
        self.stage.rmdir()
        c.need(not self.stage.exists(), 'stage-remains')
        return dict(containers=0,images=0,stage_absent=True)


def main():
    owner=Owner(); result=dict(format='ADBOWNEDABIPROOF1',context=owner.frame,candidate_execution=False,
                              sql=False,production_wiring=False,abi_coverage='unestablished',qualification=False)
    failure=None
    try:
        if sys.argv[1] != '--cleanup-only':
            result['prerequisites']=owner.prerequisites()
            result['host']=owner.build()
            moby=load('owned_abi_moby','scripts/manual/source_policy_moby_policy.py')
            codec=load('owned_abi_meter','scripts/manual/source_policy_ubi_import_contract.py')
            raw=base64.b64decode(b''.join((ROOT/c.SOURCES[-1]).read_bytes().splitlines()),validate=True)
            profile=c.policy(raw,owner.frame,moby,codec.Meter())
            owner.write('policy.json',profile)
            result['build']=owner.import_owned(); result['policy_sha']=c.sha(profile)
            result['observations']=[]
            for abi in c.ABIS:
                positive=owner.probe(abi,False,profile); result['observations'].append(positive)
                if positive.get('qualification') is False:
                    result['x32_limit']='ENOSYS is not independent complete-ABI non-callability proof'
                    continue
                result['observations'].append(owner.probe(abi,True,profile))
            c.need(owner.freeze() == owner.source, 'final-source-readback')
            result['available_abi_controls_verified']=True
            # Never turn unavailable x32/config declarations into an all-ABI grant.
            if len(result['observations']) == 6:
                result['abi_coverage']='amd64+i386+x32-tested'
                result['qualification']=True
            else:
                result['abi_coverage']='amd64+i386-tested;x32-unestablished'
    except Exception as error:
        failure=error; result['failure']=dict(phase=owner.phase,reason=str(error) if isinstance(error,c.Refusal)
                                             else type(error).__name__,commands=owner.diagnostics)
        if owner.profile_diagnostic is not None:
            result['failure']['profile_diagnostic']=owner.profile_diagnostic
    finally:
        try:
            result['cleanup']=owner.cleanup()
        except Exception as error:
            result['cleanup']=dict(complete=False,reason=str(error) if isinstance(error,c.Refusal) else type(error).__name__)
            failure=failure or error
        result['observed_bytes']=owner.observed
        result['native_output_bytes']=owner.native
        wire=c.encode(result); c.need(len(wire) <= 65536,'summary-budget')
        print(wire.decode())
    return 1 if failure else 0


if __name__ == '__main__':
    try:
        sys.exit(main())
    except c.Refusal as error:
        print(c.encode(dict(status='owned-abi-refused',reason=str(error))).decode())
        sys.exit(1)
