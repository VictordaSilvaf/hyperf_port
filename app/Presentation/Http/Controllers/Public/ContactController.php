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

use App\Application\Contact\SubmitContactMessage\SubmitContactMessageCommand;
use App\Application\Contact\SubmitContactMessage\SubmitContactMessageHandler;
use App\Domain\Contact\Exception\ContactCaptchaFailedException;
use App\Presentation\Http\Controllers\AbstractController;
use App\Presentation\Http\Requests\Public\SubmitContactRequest;
use Hyperf\Di\Annotation\Inject;

use function Hyperf\Translation\trans;

final class ContactController extends AbstractController
{
    #[Inject]
    protected SubmitContactMessageHandler $submitContactMessage;

    public function submit(SubmitContactRequest $request): array
    {
        $data = $request->validated();

        try {
            $this->submitContactMessage->handle(new SubmitContactMessageCommand(
                name: (string) $data['name'],
                email: (string) $data['email'],
                subject: isset($data['subject']) ? (string) $data['subject'] : null,
                body: (string) $data['message'],
                captchaToken: isset($data['cf_turnstile_response']) ? (string) $data['cf_turnstile_response'] : null,
                ipAddress: $this->request->getServerParams()['remote_addr'] ?? null,
                userAgent: $this->request->getHeaderLine('user-agent') ?: null,
            ));
        } catch (ContactCaptchaFailedException) {
            // Anti-enumeration: same generic response as success.
        }

        return [
            'message' => trans('http.contact_submitted'),
        ];
    }
}
