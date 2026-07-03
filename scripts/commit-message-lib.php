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
const COMMIT_HEADER_PATTERN = '/^(feat|fix|docs|style|refactor|perf|test|build|ci|chore|revert)'
    . '(\([a-z0-9._-]+\))?!?: [a-z][^\n.]{0,98}$/';

function commit_header_is_valid(string $header): bool
{
    return $header !== '' && preg_match(COMMIT_HEADER_PATTERN, $header) === 1 && strlen($header) <= 100;
}

function commit_header_should_skip(string $header): bool
{
    return preg_match('/^(Merge|Revert|fixup!|squash!)/', $header) === 1;
}

function commit_infer_scope(string $subject, string $stagedFiles): ?string
{
    $checks = [
        'cursor' => ['/.cursor/', '/\.cursorrules/', '/AGENTS\.md/'],
        'hooks' => ['/.githooks/', '/scripts\/validate-commit-msg/', '/scripts\/format-commit-msg/', '/scripts\/lint-staged/'],
        'ci' => ['/.github\//', '/\.gitlab-ci\.yml/'],
        'docs' => ['/\/docs\//', '/README\.md/', '/CONTRIBUTING\.md/', '/docs\/API\.md/'],
        'auth' => ['/\/Auth\//', '/AuthController/'],
        'user' => ['/\/User\//', '/UserController/'],
        'acl' => ['/\/Acl\//', '/Rbac/'],
        'admin' => ['/\/Admin\//', '/admin/'],
        'api' => ['/\/Controller\//', '/routes\.php/', '/Http\/Request/'],
    ];

    $haystack = $subject . "\n" . $stagedFiles;

    foreach ($checks as $scope => $patterns) {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $haystack)) {
                return $scope;
            }
        }
    }

    return null;
}

function commit_infer_type(string $subject, string $stagedFiles): string
{
    $lower = strtolower($subject . ' ' . $stagedFiles);

    if (preg_match('/\b(fix|bug|correct|resolve|hotfix|patch)\b/', $lower)) {
        return 'fix';
    }
    if (preg_match('/\b(test|spec|pest|phpunit)\b/', $lower) || preg_match('/\/test\//', $stagedFiles)) {
        return 'test';
    }
    if (preg_match('/\b(readme|contributing|documentation|docs?|api\.md|comment)\b/', $lower)
        || preg_match('/\/docs\/|README\.md|CONTRIBUTING\.md/', $stagedFiles)) {
        return 'docs';
    }
    if (preg_match('/\b(refactor|restructure|reorganize|rename|extract|move)\b/', $lower)) {
        return 'refactor';
    }
    if (preg_match('/\b(perf|performance|optimize|optimise|speed)\b/', $lower)) {
        return 'perf';
    }
    if (preg_match('/\b(style|format|lint|cs-fixer|pint)\b/', $lower)) {
        return 'style';
    }
    if (preg_match('/\b(workflow|github actions|gitlab|pipeline|hook|githook|husky)\b/', $lower)
        || preg_match('/\.github\/|\.githooks\//', $stagedFiles)) {
        return 'ci';
    }
    if (preg_match('/\b(docker|dockerfile|compose|build|deps|dependency|composer\.json|package)\b/', $lower)) {
        return 'build';
    }
    if (preg_match('/\b(add|implement|introduce|create|enable|support|new feature|endpoint)\b/', $lower)
        && preg_match('/\/app\//', $stagedFiles)) {
        return 'feat';
    }

    return 'chore';
}

function commit_normalize_subject(string $subject): string
{
    $subject = trim($subject);
    $subject = rtrim($subject, '.');

    if ($subject === '') {
        return $subject;
    }

    return mb_strtolower(mb_substr($subject, 0, 1)) . mb_substr($subject, 1);
}

function commit_format_header(string $header, string $stagedFiles): string
{
    $header = trim($header);

    if (commit_header_should_skip($header) || commit_header_is_valid($header)) {
        return $header;
    }

    // Malformed "type: subject" or "type(scope): Subject" — strip and rebuild.
    if (preg_match('/^(feat|fix|docs|style|refactor|perf|test|build|ci|chore|revert)(\([a-z0-9._-]+\))?!?:\s*(.+)$/i', $header, $m)) {
        $subject = commit_normalize_subject($m[3]);
        $type = strtolower($m[1]);
        $scope = isset($m[2]) ? trim($m[2], '()') : commit_infer_scope($subject, $stagedFiles);
        $scopePart = $scope ? "({$scope})" : '';

        return "{$type}{$scopePart}: {$subject}";
    }

    $subject = commit_normalize_subject($header);
    $type = commit_infer_type($subject, $stagedFiles);
    $scope = commit_infer_scope($subject, $stagedFiles);
    $scopePart = $scope ? "({$scope})" : '';

    $formatted = "{$type}{$scopePart}: {$subject}";

    if (strlen($formatted) > 100) {
        $maxSubject = 100 - strlen("{$type}{$scopePart}: ");
        $subject = mb_substr($subject, 0, max(1, $maxSubject));
        $formatted = "{$type}{$scopePart}: {$subject}";
    }

    return rtrim($formatted, '.');
}

function commit_get_staged_files(): string
{
    $output = [];
    exec('git diff --cached --name-only 2>/dev/null', $output);

    return implode("\n", $output);
}

function commit_parse_message_file(string $path): array
{
    $raw = (string) file_get_contents($path);
    $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
    $headerIndex = null;
    $header = '';

    foreach ($lines as $index => $line) {
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $headerIndex = $index;
        $header = $line;
        break;
    }

    return [$raw, $lines, $headerIndex, $header];
}

function commit_write_message_file(string $path, array $lines, ?int $headerIndex, string $newHeader): void
{
    if ($headerIndex === null) {
        array_unshift($lines, $newHeader, '');
    } else {
        $lines[$headerIndex] = $newHeader;
    }

    file_put_contents($path, implode("\n", $lines));
}
