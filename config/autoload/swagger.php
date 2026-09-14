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

return [
    'enable' => (bool) env('SWAGGER_ENABLE', true),
    'port' => (int) env('SWAGGER_PORT', 9500),
    'json_dir' => BASE_PATH . '/storage/swagger',
    // Custom UI loads openapi.json (HyperfServer name must match server config key below).
    'html' => <<<'HTML'
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="VictorDev API — Swagger UI" />
    <title>VictorDev API — Swagger</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui.css" />
  </head>
  <body>
  <div id="swagger-ui"></div>
  <script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-bundle.js" crossorigin></script>
  <script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-standalone-preset.js" crossorigin></script>
  <script>
    window.onload = () => {
      window.ui = SwaggerUIBundle({
        url: '/openapi.json',
        dom_id: '#swagger-ui',
        presets: [
          SwaggerUIBundle.presets.apis,
          SwaggerUIStandalonePreset
        ],
        layout: 'StandaloneLayout',
        persistAuthorization: true,
      });
    };
  </script>
  </body>
</html>
HTML,
    'url' => '/swagger',
    'auto_generate' => (bool) env('SWAGGER_AUTO_GENERATE', true),
    'scan' => [
        'paths' => [
            BASE_PATH . '/app/Presentation/Http/Controllers',
            BASE_PATH . '/app/Presentation/Http/OpenApi',
        ],
    ],
    'processors' => [
        // users can append their own processors here
    ],
    // Key "openapi" matches HyperfServer("openapi") — not "http", so docs do not clash with routes.php.
    'server' => [
        'openapi' => [
            'servers' => [
                [
                    'url' => env('APP_URL', 'http://127.0.0.1:9501'),
                    'description' => 'VictorDev API',
                ],
            ],
            'info' => [
                'title' => 'VictorDev API',
                'description' => 'REST API Hyperf 3.x — auth Bearer, RBAC e portfolio. Prefixo `/api/v1`.',
                'version' => '1.0.0',
            ],
        ],
    ],
];
