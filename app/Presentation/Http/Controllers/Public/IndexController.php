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

namespace App\Presentation\Http\Controllers\Public;

use App\Presentation\Http\Controllers\AbstractController;
use Hyperf\Swagger\Annotation as SA;

#[SA\HyperfServer('openapi')]
class IndexController extends AbstractController
{
    #[SA\Get(path: '/api/v1/', summary: 'Index / hello', tags: ['Health'])]
    #[SA\Post(path: '/api/v1/', summary: 'Index / hello (POST)', tags: ['Health'])]
    #[SA\Head(path: '/api/v1/', summary: 'Index / hello (HEAD)', tags: ['Health'])]
    #[SA\QueryParameter(name: 'user', description: 'Nome na mensagem', required: false, schema: new SA\Schema(type: 'string', default: 'Hyperf'))]
    #[SA\Response(response: 200, description: 'OK', content: new SA\JsonContent(ref: '#/components/schemas/IndexResponse'))]
    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }
}
