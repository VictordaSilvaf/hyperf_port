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

namespace App\Presentation\Http\Exception\Handler;

use Hyperf\Codec\Json;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Swow\Psr7\Message\ResponsePlusInterface;
use Throwable;

final class JsonHttpExceptionHandler extends ExceptionHandler
{
    public function __construct(
        private readonly StdoutLoggerInterface $logger,
        private readonly FormatterInterface $formatter,
    ) {
    }

    public function handle(Throwable $throwable, ResponsePlusInterface $response)
    {
        $this->stopPropagation();
        /* @var HttpException $throwable */
        $this->logger->debug($this->formatter->format($throwable));

        $payload = Json::encode([
            'message' => $throwable->getMessage() ?: 'Error',
        ]);

        return $response
            ->setStatus($throwable->getStatusCode())
            ->addHeader('content-type', 'application/json; charset=utf-8')
            ->setBody(new SwooleStream($payload));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof HttpException;
    }
}
