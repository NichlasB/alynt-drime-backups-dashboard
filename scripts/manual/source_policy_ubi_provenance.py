"""Bounded failure correlation DATA only; no IO or acceptance decisions."""
import hashlib
import json
import re


def shape(value, fields):
    return type(value) is dict and all(type(k) is str for k in value) and set(value) == set(fields)


def hexadecimal(value, length):
    return type(value) is str and re.fullmatch('[a-f0-9]{'+str(length)+'}', value) is not None


def owner_snapshot(value):
    work = ('work_used', 'parser_work', 'model_work', 'burned_work')
    counts = ('grants', 'settled', 'verified_layers', 'records', 'observation_bytes')
    if not shape(value, (*work, *counts, 'state', 'model_bound', 'runtime_grant')): return None
    if not (all(type(value[k]) is int and 0 <= value[k] <= 2097152 for k in (*work, *counts))
            and type(value['model_bound']) is bool and value['runtime_grant'] is False
            and type(value['state']) is str and value['state'] in ('ready', 'poisoned', 'closed-model')): return None
    if not (value['verified_layers'] <= 10 and value['records'] <= value['settled'] <= value['grants'] <= 256
            and value['grants']-value['settled'] <= 1
            and value['work_used'] == value['parser_work']+value['model_work']+value['grants']+value['settled']+value['burned_work']): return None
    if value['state'] == 'closed-model' and (not value['model_bound'] or value['grants'] != value['settled']): return None
    return dict(value)


def summary(row):
    if not shape(row, ('characters', 'hashed_characters', 'hash_scope', 'sha256', 'literal')):
        return False
    if row['characters'] is None:
        return (type(row['hashed_characters']) is int and row['hashed_characters'] == 0
                and type(row['hash_scope']) is str and row['hash_scope'] == 'not-string'
                and row['sha256'] is None and row['literal'] is None)
    if (type(row['characters']) is not int or not 0 <= row['characters'] <= 134217728
            or type(row['hashed_characters']) is not int or row['hashed_characters'] != min(row['characters'], 1024)
            or type(row['hash_scope']) is not str
            or row['hash_scope'] != ('full-name' if row['characters'] <= 1024 else 'prefix')
            or not hexadecimal(row['sha256'], 64)):
        return False
    value = row['literal']
    return value is None or (type(value) is str and 0 < len(value) <= 240 and len(value) == row['characters']
        and all(32 <= ord(c) <= 126 for c in value) and '\\' not in value and not value.startswith('//')
        and all(p not in ('', '.', '..') for p in value.lstrip('/').split('/'))
        and (value.lstrip('/') in ('lib', 'lib64', 'usr/lib', 'usr/lib64', 'usr/sbin')
             or value.lstrip('/').startswith(('lib/', 'lib64/', 'usr/lib/', 'usr/lib64/', 'usr/sbin/')))
        and hashlib.sha256(value.encode('ascii')).hexdigest() == row['sha256'])


def packet(facts, context):
    if not shape(context, ('head', 'image', 'run', 'manifest', 'config', 'layer', 'compressed', 'compressed_verified', 'diff_id_verified')):
        return None
    if (not hexadecimal(context['head'], 40) or not hexadecimal(context['image'], 64) or not hexadecimal(context['run'], 32)
            or type(context['manifest']) is not str or context['manifest'] != '14bab8cc945c08109a2175e844d46f4eaff1fbf4f59e95dcf2f9cac7961510e1'
            or type(context['config']) is not str or context['config'] != '2a46b014d1a6182f9f8240db6bae96566cdb11178f878ffe794f15095b745f35'
            or type(context['layer']) is not int or not 0 <= context['layer'] < 10
            or not hexadecimal(context['compressed'], 64) or context['compressed_verified'] is not True or context['diff_id_verified'] is not False):
        return None
    if not shape(facts, ('reason', 'kind', 'role', 'entry', 'layer_entry', 'name', 'target')):
        return None
    if (type(facts['reason']) is not str or facts['reason'] not in ('critical-parent-not-directory', 'critical-linked-path')
            or type(facts['kind']) is not str or facts['kind'] not in ('regular', 'directory', 'symlink', 'hardlink', 'other')
            or type(facts['role']) is not str or facts['role'] not in ('server', 'loader', 'other')
            or any(type(facts[k]) is not int or not 1 <= facts[k] <= 65536 for k in ('entry', 'layer_entry'))
            or facts['layer_entry'] > facts['entry']
            or not summary(facts['name']) or not summary(facts['target'])):
        return None
    raw = json.dumps(dict(format='ADBUPF1', context=context, failure=facts), ensure_ascii=True, separators=(',', ':'))
    return json.loads(raw) if len(raw.encode('ascii')) <= 2048 else None
