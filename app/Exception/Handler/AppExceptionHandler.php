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

namespace App\Exception\Handler;

use Hyperf\Codec\Json;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Validation\ValidationException;
use Swow\Psr7\Message\ResponsePlusInterface;
use Throwable;

use function Hyperf\Translation\trans;

class AppExceptionHandler extends ExceptionHandler
{
    public function __construct(
        protected StdoutLoggerInterface $logger,
        protected ConfigInterface $config,
    ) {
    }

    public function handle(Throwable $throwable, ResponsePlusInterface $response)
    {
        $this->stopPropagation();

        $this->logger->error(sprintf('%s: %s in %s:%s', $throwable::class, $throwable->getMessage(), $throwable->getFile(), (string) $throwable->getLine()));
        $this->logger->error($throwable->getTraceAsString());

        $debug = (bool) $this->config->get('debug', false);

        $payload = [
            'message' => $debug ? $throwable->getMessage() : trans('http.internal_server_error'),
        ];

        if ($debug) {
            $payload['exception'] = $throwable::class;
            $payload['file'] = $throwable->getFile();
            $payload['line'] = $throwable->getLine();
            $payload['trace'] = $this->formatTrace($throwable);
            $prev = $throwable->getPrevious();
            if ($prev instanceof Throwable) {
                $payload['previous'] = [
                    'message' => $prev->getMessage(),
                    'exception' => $prev::class,
                    'file' => $prev->getFile(),
                    'line' => $prev->getLine(),
                ];
            }
        }

        $body = Json::encode($payload);

        return $response
            ->setStatus(500)
            ->addHeader('content-type', 'application/json; charset=utf-8')
            ->setBody(new SwooleStream($body));
    }

    /**
     * @return list<string>
     */
    private function formatTrace(Throwable $throwable): array
    {
        $lines = explode("\n", $throwable->getTraceAsString());

        return array_values(array_slice($lines, 0, 80));
    }

    public function isValid(Throwable $throwable): bool
    {
        if ($throwable instanceof HttpException) {
            return false;
        }

        if ($throwable instanceof ValidationException) {
            return false;
        }

        return true;
    }
}
