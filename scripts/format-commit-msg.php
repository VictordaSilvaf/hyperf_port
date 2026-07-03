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

$path = $argv[1] ?? '';

if ($path === '' || ! is_readable($path)) {
    fwrite(STDERR, "Usage: php scripts/format-commit-msg.php <commit-msg-file>\n");
    exit(1);
}

[, $lines, $headerIndex, $header] = commit_parse_message_file($path);

if ($header === '' || commit_header_should_skip($header)) {
    exit(0);
}

if (commit_header_is_valid($header)) {
    exit(0);
}

$stagedFiles = commit_get_staged_files();
$formatted = commit_format_header($header, $stagedFiles);

if ($formatted === $header) {
    exit(0);
}

commit_write_message_file($path, $lines, $headerIndex, $formatted);

fwrite(STDERR, "Commit message auto-formatted to Conventional Commits:\n  {$formatted}\n");
