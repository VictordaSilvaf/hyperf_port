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

namespace App\Presentation\Http\OpenApi;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Engine\Constant\SocketType;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Server\Event;
use Hyperf\Server\Server;
use Hyperf\Swagger\Generator;
use Hyperf\Swagger\HttpServer;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

/**
 * Replaces Hyperf\Swagger\Listener\BootSwaggerListener: serves Swagger UI + generates
 * OpenAPI JSON, but does NOT register SA\Get/Post paths as Hyperf routes
 * (those live in config/routes.php).
 */
final class BootSwaggerDocsListener implements ListenerInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $config = $this->container->get(ConfigInterface::class);
        if (! $config->get('swagger.enable', false)) {
            return;
        }

        $port = (int) $config->get('swagger.port', 9500);

        $servers = $config->get('server.servers');
        foreach ($servers as $server) {
            if ((int) ($server['port'] ?? 0) === $port) {
                throw new InvalidArgumentException(sprintf(
                    'The swagger server port is invalid. Because it is conflicted with %s server.',
                    $server['name'],
                ));
            }
        }

        $servers[] = [
            'name' => 'swagger_' . uniqid(),
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => $port,
            'sock_type' => SocketType::TCP,
            'callbacks' => [
                Event::ON_REQUEST => [HttpServer::class, 'onRequest'],
            ],
        ];

        $config->set('server.servers', $servers);

        if ($config->get('swagger.auto_generate', false)) {
            $this->container->get(Generator::class)->generate();
        }
    }
}
