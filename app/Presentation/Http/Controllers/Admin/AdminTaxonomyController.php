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

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\Category\CreateCategory\CreateCategoryCommand;
use App\Application\Category\CreateCategory\CreateCategoryHandler;
use App\Application\Tag\CreateTag\CreateTagCommand;
use App\Application\Tag\CreateTag\CreateTagHandler;
use App\Application\Technology\CreateTechnology\CreateTechnologyCommand;
use App\Application\Technology\CreateTechnology\CreateTechnologyHandler;
use App\Infrastructure\Auth\AuthContext;
use App\Presentation\Http\Controllers\AbstractController;
use App\Presentation\Http\OpenApi\OpenApiRefs;
use App\Presentation\Http\Requests\Admin\CreateTaxonomyRequest;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Swagger\Annotation as SA;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

#[SA\HyperfServer('openapi')]
final class AdminTaxonomyController extends AbstractController
{
    #[Inject]
    protected CreateCategoryHandler $createCategory;

    #[Inject]
    protected CreateTechnologyHandler $createTechnology;

    #[Inject]
    protected CreateTagHandler $createTag;

    #[SA\Post(path: '/api/v1/admin/categories', summary: 'Criar categoria', description: 'Requires permission: projects.create or projects.update', security: OpenApiRefs::BEARER, tags: ['Admin Taxonomies'])]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/CreateTaxonomyRequest'))]
    #[SA\Response(response: 200, description: 'Categoria', content: new SA\JsonContent(ref: '#/components/schemas/TaxonomyItemEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function storeCategory(CreateTaxonomyRequest $request): array|PsrResponseInterface
    {
        if ($denied = $this->denyUnlessCanManageProjects()) {
            return $denied;
        }

        $data = $request->validated();

        try {
            return $this->createCategory->handle(new CreateCategoryCommand(
                (string) $data['name'],
                isset($data['slug']) ? (string) $data['slug'] : null,
            ));
        } catch (InvalidArgumentException $exception) {
            return $this->response->json([
                'message' => $exception->getMessage() !== '' ? $exception->getMessage() : trans('http.validation_failed'),
            ])->withStatus(422);
        }
    }

    #[SA\Post(path: '/api/v1/admin/technologies', summary: 'Criar tecnologia', description: 'Requires permission: projects.create or projects.update', security: OpenApiRefs::BEARER, tags: ['Admin Taxonomies'])]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/CreateTaxonomyRequest'))]
    #[SA\Response(response: 200, description: 'Tecnologia', content: new SA\JsonContent(ref: '#/components/schemas/TaxonomyItemEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function storeTechnology(CreateTaxonomyRequest $request): array|PsrResponseInterface
    {
        if ($denied = $this->denyUnlessCanManageProjects()) {
            return $denied;
        }

        $data = $request->validated();

        try {
            return $this->createTechnology->handle(new CreateTechnologyCommand(
                (string) $data['name'],
                isset($data['slug']) ? (string) $data['slug'] : null,
            ));
        } catch (InvalidArgumentException $exception) {
            return $this->response->json([
                'message' => $exception->getMessage() !== '' ? $exception->getMessage() : trans('http.validation_failed'),
            ])->withStatus(422);
        }
    }

    #[SA\Post(path: '/api/v1/admin/tags', summary: 'Criar tag', description: 'Requires permission: projects.create or projects.update', security: OpenApiRefs::BEARER, tags: ['Admin Taxonomies'])]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/CreateTaxonomyRequest'))]
    #[SA\Response(response: 200, description: 'Tag', content: new SA\JsonContent(ref: '#/components/schemas/TaxonomyItemEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function storeTag(CreateTaxonomyRequest $request): array|PsrResponseInterface
    {
        if ($denied = $this->denyUnlessCanManageProjects()) {
            return $denied;
        }

        $data = $request->validated();

        try {
            return $this->createTag->handle(new CreateTagCommand(
                (string) $data['name'],
                isset($data['slug']) ? (string) $data['slug'] : null,
            ));
        } catch (InvalidArgumentException $exception) {
            return $this->response->json([
                'message' => $exception->getMessage() !== '' ? $exception->getMessage() : trans('http.validation_failed'),
            ])->withStatus(422);
        }
    }

    private function denyUnlessCanManageProjects(): ?PsrResponseInterface
    {
        if (AuthContext::can('projects.update') || AuthContext::can('projects.create')) {
            return null;
        }

        return $this->response->json(['message' => trans('http.forbidden')])->withStatus(403);
    }
}
