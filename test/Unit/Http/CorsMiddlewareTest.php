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
use App\Presentation\Http\Middleware\CorsMiddleware;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\HttpServer\Contract\ResponseInterface as HyperfResponseInterface;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CorsTestConfig implements ConfigInterface
{
    public function __construct(private readonly array $values = [])
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    public function has(string $keys): bool
    {
        return true;
    }

    public function set(string $key, mixed $value): void
    {
    }
}

final class CorsTestHyperfResponse implements HyperfResponseInterface
{
    public function __construct(private ResponseInterface $inner = new Response())
    {
    }

    public function json($data): ResponseInterface
    {
        return $this->inner;
    }

    public function xml($data, string $root = 'root', string $charset = 'utf-8'): ResponseInterface
    {
        return $this->inner;
    }

    public function raw($data, string $charset = 'utf-8'): ResponseInterface
    {
        return $this->inner;
    }

    public function html(string $html, string $charset = 'utf-8'): ResponseInterface
    {
        return $this->inner;
    }

    public function redirect(string $toUrl, int $status = 302, string $schema = 'http'): ResponseInterface
    {
        return $this->inner;
    }

    public function download(string $file, string $name = ''): ResponseInterface
    {
        return $this->inner;
    }

    public function write(string $data): bool
    {
        return true;
    }

    public function withCookie(Cookie $cookie): HyperfResponseInterface
    {
        return $this;
    }
}

test('cors middleware answers OPTIONS preflight with 204 and allow headers', function () {
    $config = new CorsTestConfig([
        'cors.origins' => 'https://victorsf.com,http://localhost:5173',
        'cors.allow_credentials' => false,
    ]);
    $middleware = new CorsMiddleware($config, new CorsTestHyperfResponse());

    $request = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('getHeaderLine')->with('Origin')->andReturn('https://victorsf.com');
    $request->shouldReceive('getMethod')->andReturn('OPTIONS');

    $handler = Mockery::mock(RequestHandlerInterface::class);
    $handler->shouldNotReceive('handle');

    $response = $middleware->process($request, $handler);

    expect($response->getStatusCode())->toBe(204);
    expect($response->getHeaderLine('Access-Control-Allow-Origin'))->toBe('https://victorsf.com');
    expect($response->getHeaderLine('Access-Control-Allow-Methods'))->toContain('POST');
});

test('cors middleware skips headers for disallowed origin', function () {
    $config = new CorsTestConfig([
        'cors.origins' => 'https://victorsf.com',
        'cors.allow_credentials' => false,
    ]);
    $middleware = new CorsMiddleware($config, new CorsTestHyperfResponse());

    $request = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('getHeaderLine')->with('Origin')->andReturn('https://evil.example');
    $request->shouldReceive('getMethod')->andReturn('GET');

    $inner = (new Response())->withStatus(200);
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $handler->shouldReceive('handle')->once()->andReturn($inner);

    $response = $middleware->process($request, $handler);

    expect($response->getHeaderLine('Access-Control-Allow-Origin'))->toBe('');
});
