"""Exact-job owned prefix proofs. Candidate execution requires its separate contract.

The legacy prefix-only mode never executes a payload. No production entry.
"""
import importlib.util
import json
import math
import os
from pathlib import Path
import signal
import sys
import time
import urllib.error

sys.path.insert(0, str(Path(__file__).resolve().parent))
import source_policy_ppa_join as J

SPEC = importlib.util.spec_from_file_location('fixed_ppa_owner', Path(__file__).with_name('source-policy-ppa-deb-static.py'))
M = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(M)
S = J.D.S


def _runtime(root):
    # target() has already admitted the exact job and canonical parent.
    return root.parent/('adb-ppa-runtime-'+os.environ['GITHUB_RUN_ID'])


def _absent(root):
    S.require(not root.exists() and not root.is_symlink(), 'prefix-presence-unknown')


class _FixedEntry:
    """Only this trusted manual owner selects the concrete adapter/context."""
    def __init__(self, budget):
        root = M.target(os.environ)
        self._context = J._Context(J._KEY, str(root.parent), os.environ['EXPECTED_SHA'],
                                   os.environ['GITHUB_RUN_ID'], budget, J.E.Budget())

    def context(self):
        root = M.target(os.environ)  # Re-admit immediately before source IO.
        S.require((str(root.parent), os.environ['EXPECTED_SHA'], os.environ['GITHUB_RUN_ID'])
                  == self._context._origin, 'prefix-job-changed')
        return self._context

    def run(self):
        provider = J.N._PosixCalls()
        provider.profile()
        previous = J._ENTRY, J.N._CALLS
        try:
            J._ENTRY, J.N._CALLS = self, provider
            result = J._run()
            storage = self._context._storage
            S.require(result['join_policy_complete'] is True and result['materialized'] is False
                      and result['qualified'] is False and result['executed'] is False
                      and type(storage) is J.N._Storage and storage._api is provider
                      and self._context._output_sealed and self._context._source_count == 2
                      and not self._context._decoder_unknown, 'prefix-native-join-barrier')
            S.require(not storage._created and not storage._uncertain
                      and all(row[1] for row in storage._records)
                      and all(row[1] for row in storage._iterators), 'prefix-native-retirement')
            S.require(set(storage._sizes) == {'owner.json', *(row[3] for row in J.P._CATALOG)}
                      and all(storage._sizes[row[3]] == row[4] for row in J.P._CATALOG), 'prefix-native-sizes')
            return {'payload_bytes': sum(row[4] for row in J.P._CATALOG),
                    'payloads': len(J.P._CATALOG), 'output_mode': '0400',
                    'scratch_peak': self._context._scratch.usage[1]}
        finally:
            J._ENTRY, J.N._CALLS = previous


class _ConnectionFreeEntry(_FixedEntry):
    def run(self):
        import source_policy_ppa_launch as X
        S.require(os.environ.get('PPA_EXECUTION_CONTRACT') == X.CONTRACT, 'candidate-contract')
        previous = J._JoinedOutput, X._PROCESS
        try:
            J._JoinedOutput, X._PROCESS = X._ConnectionFreeOutput, X._NativeProcess()
            facts = super().run()
            observed = self._context._candidate_observation
            S.require(observed.complete and observed.started and not observed.unknown, 'candidate-final-barrier')
            return {**facts, 'connection_free': observed.receipt()}
        finally:
            J._JoinedOutput, X._PROCESS = previous


def main():
    root = M.target(os.environ)  # No file/API access before exact-job admission.
    runtime = _runtime(root)
    cleanup_only = sys.argv[1:] == ['--reconcile-prefix-proof']
    connection_free = sys.argv[1:] == ['--approved-connection-free-proof']
    S.require(cleanup_only or connection_free or sys.argv[1:] == ['--approved-prefix-proof'], 'prefix-proof-entry')
    if connection_free:
        S.require(os.environ.get('PPA_EXECUTION_CONTRACT') == 'ppa-connection-free-functional-v1', 'candidate-contract')
    entry, cleanup_deadline = None, None

    def arm_cleanup():
        nonlocal cleanup_deadline
        if cleanup_deadline is None:
            cleanup_deadline = entry._context.cleanup_deadline() if entry is not None else time.monotonic()+30
        remaining = cleanup_deadline-time.monotonic()
        S.require(remaining > 0, 'prefix-proof-cleanup-deadline')
        signal.alarm(max(1, math.ceil(remaining)))  # Remaining window, never a new30s.

    def expired(signum, frame):
        if cleanup_deadline is None:
            arm_cleanup()
        raise S.Refusal('prefix-proof-deadline')

    signal.signal(signal.SIGALRM, expired)
    if cleanup_only:
        arm_cleanup()
        # Lost runtime ownership permits reconciliation ONLY, never marker-only deletion.
        _absent(runtime)
        M._fixture_cleanup(root)
        M.cleanup(root)  # Existing SOURCE-root authority only.
        _absent(root)
        M._children_empty()
        signal.alarm(0)
        print('{"owned_deb_remaining":0,"owned_runtime_remaining":0}')
        return 0
    signal.alarm(540)
    receipt = {'format': 'ADBPPAPREFIX1', 'prefix_proof_complete': False,
               'materialized': False, 'extracted': False, 'executed': False,
               'qualified': False, 'dependency_closure_verified': False,
               'versioned_symbols': 'Unknown', 'production_wiring': False}
    if connection_free:
        receipt.update(format='ADBPPARUNTIME1', connection_free_complete=False)
    created, complete, baseline, mask, facts = False, False, None, None, None
    budget = J.D.Budget()  # ONE original ledger for acquisition,guards,both passes/output.
    try:
        receipt['stage'] = 'preflight'
        baseline = M._fd_state()
        mask = signal.pthread_sigmask(signal.SIG_BLOCK, set())
        M._children_empty()
        receipt['preflight'] = M.preflight()  # Original40/24MiB,zero-core,fixed codecs.
        J.N._PosixCalls().profile()
        S.require(M._fd_state() == baseline, 'prefix-preflight-reconciliation')
        _absent(root)
        _absent(runtime)
        root.mkdir(mode=0o700)
        created = True
        with (root/'owner.json').open('xb') as owner:
            marker = M.marker(root)
            budget.charge(len(marker))
            owner.write(marker)
        receipt['stage'] = 'parent-admission'
        M._must_refuse(lambda: M._descriptor_sources(budget), 'lease-membership')
        S.require(M._fd_state() == baseline, 'prefix-parent-reconciliation')
        receipt['stage'] = 'acquisition'
        receipt['identities'] = []
        M.acquire(root, budget, receipt['identities'])
        receipt['transport_released'] = True
        receipt['stage'] = 'fresh-owned-source-guards'
        receipt['groups'] = M._descriptor_checks(root, budget)
        receipt['stage'] = 'fixed-native-prefix-source-join'
        entry = _ConnectionFreeEntry(budget) if connection_free else _FixedEntry(budget)
        facts = entry.run()
        _absent(runtime)
        complete = True
        receipt['reason'] = 'connection-free-functional-only' if connection_free else 'materialized-sealed-cleaned-only-no-runtime-admission'
    except S.Refusal as error:
        receipt['reason'] = str(error)
    except urllib.error.HTTPError as error:
        try:
            receipt.update(M.http_refusal(error))
        finally:
            error.close()
    except Exception as error:
        receipt['reason'], receipt['error_class'] = 'prefix-proof-error', type(error).__name__
    finally:
        try:
            arm_cleanup()
            storage = entry._context._storage if entry is not None else None
            if storage is not None:
                # Truthful even on partial-write failure;never call RAM DATA physical proof.
                receipt['materialized_payload_bytes'] = sum(size for leaf, size in storage._sizes.items() if leaf != 'owner.json')
                receipt['materialized'] = receipt['extracted'] = any(leaf != 'owner.json' for leaf in storage._sizes)
            observed = getattr(entry._context, '_candidate_observation', None) if entry is not None else None
            if observed is not None:
                receipt['candidate'] = observed.receipt()
                receipt['executed'] = observed.started
            _absent(runtime)  # No cleanup fallback for an unknown/retained prefix.
            if created:
                M._fixture_cleanup(root)
                M.cleanup(root)
            _absent(root)
            M._children_empty()
            S.require(baseline is not None and M._fd_state() == baseline, 'prefix-final-fd-reconciliation')
            S.require(mask is not None and signal.pthread_sigmask(signal.SIG_BLOCK, set()) == mask, 'prefix-final-mask-reconciliation')
            receipt.update(owned_deb_remaining=0, owned_runtime_remaining=0,
                           owned_descriptor_delta=0, owned_children_remaining=0)
        except Exception as error:
            complete = False
            receipt['reason'], receipt['cleanup_error_class'] = 'prefix-cleanup-unknown', type(error).__name__
            if receipt['materialized'] is False:
                receipt['materialized'] = receipt['extracted'] = 'Unknown'
            for name in ('owned_deb_remaining', 'owned_runtime_remaining', 'owned_descriptor_delta', 'owned_children_remaining'):
                receipt[name] = 'Unknown'
        finally:
            signal.alarm(0)
    if complete:
        try:
            budget.charge(0)  # Cleanup never extends original work acceptance.
            S.require(receipt['materialized'] is True, 'prefix-materialization-evidence')
            if connection_free:
                S.require(receipt.get('candidate', {}).get('complete') is True and receipt['executed'] is True
                          and receipt['candidate']['release_unknown'] is False, 'candidate-result-barrier')
            receipt.update(prefix_proof_complete=True, stage='complete', output=facts)
            if connection_free:
                receipt['connection_free_complete'] = True
        except S.Refusal as error:
            complete, receipt['reason'] = False, str(error)
    receipt['work_bytes'] = budget.total
    output = json.dumps(receipt, sort_keys=True)
    S.require(len(output.encode('ascii')) <= 65536, 'diagnostic-bound')
    print(output)
    return 0 if complete else 1


if __name__ == '__main__':
    sys.exit(main())
