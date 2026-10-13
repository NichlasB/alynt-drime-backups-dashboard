import { readdirSync, readFileSync, statSync } from 'node:fs';
import { basename, join, relative } from 'node:path';

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
  {
    directory: 'tests',
    pattern: /\.php$/,
    maxLines: 150,
    label: 'test class',
    exclude: /^tests[\\/]support[\\/]/,
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
    .filter((path) => !check.exclude || !check.exclude.test(relative(repoRoot, path)))
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

const testClassFiles = phpFiles('tests', /\.php$/)
  .filter((path) => !/^tests[\\/]support[\\/]/.test(relative(repoRoot, path)))
  .filter((path) => /Test\.php$/.test(path))
  .map((path) => {
    const content = readFileSync(path, 'utf8');
    const match = content.match(/class\s+([A-Za-z0-9_]+)\s+extends\s+TestCase/);

    return {
      path,
      actual: match ? match[1] : '',
      expected: basename(path, '.php'),
    };
  });

const mismatchedTestClasses = testClassFiles.filter(
  (file) => file.actual !== file.expected
);

if (mismatchedTestClasses.length > 0) {
  failures.push({
    check: {
      label: 'test class name',
    },
    mismatched: mismatchedTestClasses,
  });
}

console.log(`test class names: ${testClassFiles.length} files checked`);

if (failures.length > 0) {
  console.error('\nStructure check failed:');

  for (const failure of failures) {
    for (const file of failure.oversized || []) {
      console.error(`- ${relative(repoRoot, file.path)} has ${file.lines} lines; ${failure.check.label} limit is ${failure.check.maxLines}.`);
    }

    for (const file of failure.mismatched || []) {
      console.error(`- ${relative(repoRoot, file.path)} declares ${file.actual || 'no TestCase class'}; expected ${file.expected}.`);
    }
  }

  process.exit(1);
}

console.log('Structure check passed.');
