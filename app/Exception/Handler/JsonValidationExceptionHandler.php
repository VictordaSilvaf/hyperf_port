<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use Hyperf\Codec\Json;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Validation\ValidationException;
use Swow\Psr7\Message\ResponsePlusInterface;
use Throwable;

use function Hyperf\Translation\trans;

final class JsonValidationExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponsePlusInterface $response)
    {
        $this->stopPropagation();
        /** @var ValidationException $throwable */
        $payload = Json::encode([
            'message' => trans('http.validation_failed'),
            'errors' => $throwable->errors(),
        ]);

        return $response
            ->setStatus($throwable->status)
            ->addHeader('content-type', 'application/json; charset=utf-8')
            ->setBody(new SwooleStream($payload));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ValidationException;
    }
}
