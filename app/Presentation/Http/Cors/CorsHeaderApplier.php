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

namespace App\Presentation\Http\Cors;

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swow\Psr7\Message\ResponsePlusInterface;

/**
 * Applies Access-Control-* headers when Origin is allowed.
 * Used by CorsMiddleware and exception handlers (errors bypass middleware wrapping).
 */
final class CorsHeaderApplier
{
    public static function apply(ResponseInterface $response, ?string $allowedOrigin, bool $allowCredentials = false): ResponseInterface
    {
        if ($allowedOrigin === null || $allowedOrigin === '') {
            return $response;
        }

        if ($response instanceof ResponsePlusInterface) {
            $response = $response
                ->addHeader('Access-Control-Allow-Origin', $allowedOrigin)
                ->addHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->addHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, Origin')
                ->addHeader('Access-Control-Max-Age', '86400')
                ->addHeader('Vary', 'Origin');

            if ($allowCredentials) {
                $response = $response->addHeader('Access-Control-Allow-Credentials', 'true');
            }

            return $response;
        }

        $response = $response
            ->withHeader('Access-Control-Allow-Origin', $allowedOrigin)
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, Origin')
            ->withHeader('Access-Control-Max-Age', '86400')
            ->withHeader('Vary', 'Origin');

        if ($allowCredentials) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }

    /**
     * Resolve allowed Origin from config + current request (for exception handlers).
     */
    public static function allowedOriginForCurrentRequest(): ?string
    {
        $origin = '';
        $request = Context::get(ServerRequestInterface::class);
        if ($request instanceof ServerRequestInterface) {
            $origin = $request->getHeaderLine('Origin');
        }

        $config = self::config();
        if ($config === null) {
            return null;
        }

        return self::resolveAllowedOrigin($config, $origin);
    }

    public static function allowCredentials(ConfigInterface $config): bool
    {
        return (bool) $config->get('cors.allow_credentials', false);
    }

    public static function resolveAllowedOrigin(ConfigInterface $config, string $origin): ?string
    {
        if ($origin === '') {
            return null;
        }

        $raw = (string) $config->get('cors.origins', '');
        $origins = array_values(array_filter(array_map(
            static fn (string $o): string => trim($o),
            explode(',', $raw),
        ), static fn (string $o): bool => $o !== ''));

        foreach ($origins as $allowed) {
            if (strcasecmp($allowed, $origin) === 0) {
                return $origin;
            }
        }

        return null;
    }

    private static function config(): ?ConfigInterface
    {
        if (! ApplicationContext::hasContainer()) {
            return null;
        }

        $container = ApplicationContext::getContainer();
        if (! $container->has(ConfigInterface::class)) {
            return null;
        }

        return $container->get(ConfigInterface::class);
    }
}
