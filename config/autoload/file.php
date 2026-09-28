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
use App\Infrastructure\Storage\CleanS3AdapterFactory;
use Hyperf\Filesystem\Adapter\MemoryAdapterFactory;

use function Hyperf\Support\env;

$s3Options = static function (
    string $keyEnv,
    string $secretEnv,
    string $regionEnv,
    string $endpointEnv,
    string $bucketEnv,
    bool $pathStyle,
): array {
    return [
        'driver' => CleanS3AdapterFactory::class,
        'credentials' => [
            'key' => env($keyEnv, ''),
            'secret' => env($secretEnv, ''),
        ],
        'region' => env($regionEnv, 'auto'),
        'version' => 'latest',
        'bucket_endpoint' => false,
        'use_path_style_endpoint' => $pathStyle,
        'endpoint' => env($endpointEnv, ''),
        'bucket_name' => env($bucketEnv, ''),
        // aws-sdk-php ≥ 3.337 defaults break Cloudflare R2 (x-amz-checksum-crc32 NotImplemented).
        'request_checksum_calculation' => 'when_required',
        'response_checksum_validation' => 'when_required',
    ];
};

return [
    // minio (dev/testes Docker) | r2 (Cloudflare produção)
    'default' => env('FILESYSTEM_DRIVER', 'minio'),
    // Prefixo dentro do bucket (ex.: victorsf/development, victorsf/production)
    'prefix' => env('FILESYSTEM_PREFIX', 'development'),
    // Empty FILESYSTEM_PUBLIC_URL= in .env must not shadow R2_PUBLIC_URL / MINIO_PUBLIC_URL.
    'public_url' => (string) (env('FILESYSTEM_PUBLIC_URL') ?: env('R2_PUBLIC_URL') ?: env('MINIO_PUBLIC_URL') ?: ''),
    'storage' => [
        'minio' => $s3Options(
            'MINIO_ACCESS_KEY_ID',
            'MINIO_SECRET_ACCESS_KEY',
            'MINIO_REGION',
            'MINIO_ENDPOINT',
            'MINIO_BUCKET',
            true,
        ),
        'r2' => $s3Options(
            'R2_ACCESS_KEY_ID',
            'R2_SECRET_ACCESS_KEY',
            'R2_REGION',
            'R2_ENDPOINT',
            'R2_BUCKET',
            filter_var(env('R2_USE_PATH_STYLE', 'true'), FILTER_VALIDATE_BOOLEAN),
        ),
        'memory' => [
            'driver' => MemoryAdapterFactory::class,
        ],
    ],
];
