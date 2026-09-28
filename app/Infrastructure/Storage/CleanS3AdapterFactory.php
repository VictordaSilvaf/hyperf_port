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
use Aws\S3\S3Client;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Filesystem\Contract\AdapterFactoryInterface;
use Hyperf\Filesystem\Exception\InvalidArgumentException;
use Hyperf\Guzzle\CoroutineHandler;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

/**
 * Like Hyperf's S3AdapterFactory, but strips non-SDK keys (`driver`) before constructing S3Client.
 * Avoids InvalidArgumentException on stricter aws-sdk-php versions.
 */
final class CleanS3AdapterFactory implements AdapterFactoryInterface
{
    public function make(array $options)
    {
        $bucket = (string) ($options['bucket_name'] ?? '');
        if ($bucket === '') {
            throw new InvalidArgumentException('S3/R2 bucket_name is required.');
        }

        unset($options['driver'], $options['bucket_name']);
        $options['http_handler'] = new GuzzleHandler(new Client([
            'handler' => HandlerStack::create(new CoroutineHandler()),
        ]));

        return new AwsS3V3Adapter(new S3Client($options), $bucket, '');
    }
}
