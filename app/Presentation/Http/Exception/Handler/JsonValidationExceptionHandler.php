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

use App\Presentation\Http\Cors\CorsHeaderApplier;
use Hyperf\Codec\Json;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
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

        $response = $response
            ->setStatus($throwable->status)
            ->addHeader('content-type', 'application/json; charset=utf-8')
            ->setBody(new SwooleStream($payload));

        $config = ApplicationContext::hasContainer()
            ? ApplicationContext::getContainer()->get(ConfigInterface::class)
            : null;
        $allowed = CorsHeaderApplier::allowedOriginForCurrentRequest();
        $credentials = $config instanceof ConfigInterface && CorsHeaderApplier::allowCredentials($config);

        return CorsHeaderApplier::apply($response, $allowed, $credentials);
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ValidationException;
    }
}
