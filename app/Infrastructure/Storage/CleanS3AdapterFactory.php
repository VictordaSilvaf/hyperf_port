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

namespace App\Infrastructure\Storage;

use Aws\Handler\Guzzle\GuzzleHandler;
use Aws\Handler\GuzzleV6\GuzzleHandler as V6GuzzleHandler;
use Aws\S3\S3Client;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Filesystem\Contract\AdapterFactoryInterface;
use Hyperf\Filesystem\Exception\InvalidArgumentException;
use Hyperf\Filesystem\Version;
use Hyperf\Guzzle\CoroutineHandler;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

/**
 * Like Hyperf's S3AdapterFactory, but strips non-SDK keys (`driver`) before constructing S3Client.
 * Avoids InvalidArgumentException on stricter aws-sdk-php versions.
 */
final class CleanS3AdapterFactory implements AdapterFactoryInterface
{
    public function make(array $options)
    {
        $handlerClass = match (true) {
            class_exists(GuzzleHandler::class) => GuzzleHandler::class,
            class_exists(V6GuzzleHandler::class) => V6GuzzleHandler::class,
            default => throw new InvalidArgumentException('The default guzzle handler not found.'),
        };

        $handler = new $handlerClass(new Client([
            'handler' => HandlerStack::create(new CoroutineHandler()),
        ]));

        $bucket = (string) ($options['bucket_name'] ?? '');
        if ($bucket === '') {
            throw new InvalidArgumentException('S3/R2 bucket_name is required.');
        }

        unset($options['driver'], $options['bucket_name']);
        $options['http_handler'] = $handler;

        $client = new S3Client($options);

        if (Version::isV2()) {
            return new AwsS3V3Adapter($client, $bucket, '');
        }

        return new AwsS3Adapter($client, $bucket, '', ['override_visibility_on_copy' => true]);
    }
}
