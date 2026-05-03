<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    'locale' => env('APP_LOCALE', 'pt_BR'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'path' => BASE_PATH . '/storage/languages',
];
