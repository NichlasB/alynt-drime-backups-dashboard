import { readdirSync, readFileSync, statSync } from 'node:fs';
import { join, relative } from 'node:path';

const repoRoot = process.cwd();

const checks = [
  {
    directory: 'includes',
    pattern: /\.php$/,
    maxLines: 300,
    label: 'runtime include',
  },
  {
    directory: 'tests/support',
    pattern: /\.php$/,
    maxLines: 150,
    label: 'test support helper',
  },
];

function phpFiles(directory, pattern) {
  const root = join(repoRoot, directory);
  const entries = readdirSync(root);
  const files = [];

  for (const entry of entries) {
    const path = join(root, entry);
    const stats = statSync(path);

    if (stats.isDirectory()) {
      files.push(...phpFiles(relative(repoRoot, path), pattern));
      continue;
    }

    if (pattern.test(entry)) {
      files.push(path);
    }
  }

  return files;
}

function countLines(path) {
  const content = readFileSync(path, 'utf8');
  if (content.length === 0) {
    return 0;
  }

  return content.split(/\r\n|\r|\n/).length;
}

const failures = [];

for (const check of checks) {
  const files = phpFiles(check.directory, check.pattern)
    .map((path) => ({
      path,
      lines: countLines(path),
    }))
    .sort((a, b) => b.lines - a.lines);

  const oversized = files.filter((file) => file.lines > check.maxLines);

  if (oversized.length > 0) {
    failures.push({ check, oversized });
  }

  const largest = files.slice(0, 5).map((file) => `${relative(repoRoot, file.path)} (${file.lines})`);
  console.log(`${check.label}: ${files.length} files, max ${check.maxLines} lines, largest: ${largest.join(', ')}`);
}

if (failures.length > 0) {
  console.error('\nStructure check failed:');

  for (const failure of failures) {
    for (const file of failure.oversized) {
      console.error(`- ${relative(repoRoot, file.path)} has ${file.lines} lines; ${failure.check.label} limit is ${failure.check.maxLines}.`);
    }
  }

  process.exit(1);
}

console.log('Structure check passed.');
