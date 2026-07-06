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

namespace App\Application\Contact\SubmitContactMessage;

use App\Application\Contact\ContactCaptchaVerifierInterface;
use App\Application\Contact\ContactMessageNotifierInterface;
use App\Domain\Contact\Entity\ContactMessage;
use App\Domain\Contact\Exception\ContactCaptchaFailedException;
use App\Domain\Contact\Repository\ContactMessageRepositoryInterface;
use App\Domain\Site\Repository\SiteSettingsRepositoryInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;

final class SubmitContactMessageHandler
{
    public function __construct(
        private readonly ContactMessageRepositoryInterface $messages,
        private readonly SiteSettingsRepositoryInterface $settings,
        private readonly ContactMessageNotifierInterface $notifier,
        private readonly ContactCaptchaVerifierInterface $captcha,
        private readonly ConfigInterface $config,
        private readonly StdoutLoggerInterface $logger,
    ) {
    }

    public function handle(SubmitContactMessageCommand $command): void
    {
        $token = trim((string) ($command->captchaToken ?? ''));
        if (! $this->captcha->verify($token, $command->ipAddress)) {
            throw ContactCaptchaFailedException::invalid();
        }

        $message = ContactMessage::create(
            $command->name,
            $command->email,
            $command->subject,
            $command->body,
            $command->ipAddress,
            $command->userAgent,
        );

        $this->messages->save($message);

        $recipient = $this->resolveRecipient();
        if ($recipient === null) {
            $this->logger->warning('[contact] no notification recipient configured; message saved only');

            return;
        }

        $this->notifier->notify($recipient, $message);
    }

    private function resolveRecipient(): ?string
    {
        $fromSettings = $this->settings->get()->contact()->notificationEmail();
        if (is_string($fromSettings) && $fromSettings !== '') {
            return $fromSettings;
        }

        $fallback = trim((string) $this->config->get('mail.contact_to', ''));

        return $fallback !== '' ? $fallback : null;
    }
}
