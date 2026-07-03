<?php

declare(strict_types=1);
/**
 * Hyperf API — DDD / Hexagonal
 *
 * @link     https://github.com/VictordaSilvaf/hyperf_port
 * @document https://github.com/VictordaSilvaf/hyperf_port/doc
 * @contact  victordasilvafernandes@gmail.com
 * @see      https://github.com/VictordaSilvaf/hyperf_port.git
 */
$root = dirname(__DIR__);
$target = $root . '/hyper';
$binDir = $root . '/vendor/bin';
$link = $binDir . '/hyper';

if (! is_file($target)) {
    return;
}

if (! is_dir($binDir)) {
    mkdir($binDir, 0755, true);
}

if (is_link($link) || file_exists($link)) {
    unlink($link);
}

symlink('../../hyper', $link);
