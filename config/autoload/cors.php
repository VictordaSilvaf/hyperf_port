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
use function Hyperf\Support\env;

// Keys are mounted under `cors.*` (filename = top-level key in Hyperf autoload).
return [
    'origins' => env(
        'CORS_ORIGINS',
        'https://victorsf.com,https://www.victorsf.com,http://localhost:5173',
    ),
    'allow_credentials' => filter_var(env('CORS_ALLOW_CREDENTIALS', 'false'), FILTER_VALIDATE_BOOLEAN),
];
