<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
require __DIR__ . '/commit-message-lib.php';

$input = $argv[1] ?? '-';

if ($input === '-') {
    $raw = stream_get_contents(STDIN);
} elseif (is_readable($input)) {
    $raw = (string) file_get_contents($input);
} else {
    $raw = $input;
}

if ($raw === false || $raw === '') {
    fwrite(STDERR, "Empty commit message.\n");
    exit(1);
}

$lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
$header = '';

foreach ($lines as $line) {
    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }
    $header = $line;
    break;
}

if ($header === '') {
    exit(0);
}

if (commit_header_should_skip($header)) {
    exit(0);
}

if (! commit_header_is_valid($header)) {
    fwrite(STDERR, <<<TXT
Invalid commit message format.

Expected Conventional Commits:
  <type>(<scope>): <subject>

Types: feat, fix, docs, style, refactor, perf, test, build, ci, chore, revert

Examples:
  feat(auth): add refresh token endpoint
  fix(user): prevent duplicate email registration

See CONTRIBUTING.md

Got: {$header}
TXT);
    exit(1);
}
