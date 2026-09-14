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

use App\Application\Contact\GetContactMessage\GetContactMessageHandler;
use App\Application\Contact\GetContactMessage\GetContactMessageQuery;
use App\Application\Contact\ListContactMessages\ListContactMessagesHandler;
use App\Application\Contact\ListContactMessages\ListContactMessagesQuery;
use App\Application\Contact\UpdateContactMessageStatus\UpdateContactMessageStatusCommand;
use App\Application\Contact\UpdateContactMessageStatus\UpdateContactMessageStatusHandler;
use App\Domain\Contact\Exception\ContactMessageNotFoundException;
use App\Presentation\Http\Controllers\AbstractController;
use App\Presentation\Http\OpenApi\OpenApiRefs;
use App\Presentation\Http\Requests\Admin\ListContactMessagesRequest;
use App\Presentation\Http\Requests\Admin\UpdateContactMessageRequest;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Swagger\Annotation as SA;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

#[SA\HyperfServer('openapi')]
final class AdminContactMessageController extends AbstractController
{
    #[Inject]
    protected ListContactMessagesHandler $listContactMessages;

    #[Inject]
    protected GetContactMessageHandler $getContactMessage;

    #[Inject]
    protected UpdateContactMessageStatusHandler $updateContactMessageStatus;

    #[SA\Get(path: '/api/v1/admin/contact/messages', summary: 'Listar mensagens de contacto', description: 'Requires permission: contact.view', security: OpenApiRefs::BEARER, tags: ['Admin Contact'])]
    #[SA\QueryParameter(name: 'page', required: false, schema: new SA\Schema(type: 'integer', default: 1))]
    #[SA\QueryParameter(name: 'per_page', required: false, schema: new SA\Schema(type: 'integer', default: 15))]
    #[SA\QueryParameter(name: 'status', required: false, schema: new SA\Schema(type: 'string', enum: ['new', 'read', 'archived']))]
    #[SA\Response(response: 200, description: 'Lista', content: new SA\JsonContent(ref: '#/components/schemas/ContactMessageList'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function index(ListContactMessagesRequest $request): array
    {
        $data = $request->validated();

        return $this->listContactMessages->handle(new ListContactMessagesQuery(
            page: (int) ($data['page'] ?? 1),
            perPage: (int) ($data['per_page'] ?? 15),
            status: isset($data['status']) ? (string) $data['status'] : null,
        ));
    }

    #[SA\Get(path: '/api/v1/admin/contact/messages/{id}', summary: 'Obter mensagem de contacto', description: 'Requires permission: contact.view', security: OpenApiRefs::BEARER, tags: ['Admin Contact'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Mensagem', content: new SA\JsonContent(ref: '#/components/schemas/ContactMessageEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrada', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function show(string $id): array|PsrResponseInterface
    {
        try {
            return $this->getContactMessage->handle(new GetContactMessageQuery($id, true));
        } catch (ContactMessageNotFoundException) {
            return $this->response->json(['message' => trans('http.contact_message_not_found')])->withStatus(404);
        }
    }

    #[SA\Patch(path: '/api/v1/admin/contact/messages/{id}', summary: 'Actualizar estado da mensagem', description: 'Requires permission: contact.update', security: OpenApiRefs::BEARER, tags: ['Admin Contact'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/UpdateContactMessageRequest'))]
    #[SA\Response(response: 200, description: 'Actualizada', content: new SA\JsonContent(ref: '#/components/schemas/ContactMessageEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrada', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function update(string $id, UpdateContactMessageRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();

        try {
            return $this->updateContactMessageStatus->handle(new UpdateContactMessageStatusCommand(
                $id,
                (string) $data['status'],
            ));
        } catch (ContactMessageNotFoundException) {
            return $this->response->json(['message' => trans('http.contact_message_not_found')])->withStatus(404);
        }
    }
}
