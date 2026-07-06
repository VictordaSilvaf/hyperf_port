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
use App\Presentation\Http\Requests\Admin\ListContactMessagesRequest;
use App\Presentation\Http\Requests\Admin\UpdateContactMessageRequest;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

final class AdminContactMessageController extends AbstractController
{
    #[Inject]
    protected ListContactMessagesHandler $listContactMessages;

    #[Inject]
    protected GetContactMessageHandler $getContactMessage;

    #[Inject]
    protected UpdateContactMessageStatusHandler $updateContactMessageStatus;

    public function index(ListContactMessagesRequest $request): array
    {
        $data = $request->validated();

        return $this->listContactMessages->handle(new ListContactMessagesQuery(
            page: (int) ($data['page'] ?? 1),
            perPage: (int) ($data['per_page'] ?? 15),
            status: isset($data['status']) ? (string) $data['status'] : null,
        ));
    }

    public function show(string $id): array|PsrResponseInterface
    {
        try {
            return $this->getContactMessage->handle(new GetContactMessageQuery($id, true));
        } catch (ContactMessageNotFoundException) {
            return $this->response->json(['message' => trans('http.contact_message_not_found')])->withStatus(404);
        }
    }

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
