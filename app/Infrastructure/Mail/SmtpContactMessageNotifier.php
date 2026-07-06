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

namespace App\Infrastructure\Mail;

use App\Application\Contact\ContactMessageNotifierInterface;
use App\Domain\Contact\Entity\ContactMessage;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Throwable;

final class SmtpContactMessageNotifier implements ContactMessageNotifierInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ConfigInterface $config,
        private readonly StdoutLoggerInterface $logger,
    ) {
    }

    public function notify(string $to, ContactMessage $message): void
    {
        if (! (bool) $this->config->get('mail.enabled', true)) {
            return;
        }

        $fromAddress = (string) $this->config->get('mail.from.address', 'noreply@localhost');
        $fromName = (string) $this->config->get('mail.from.name', 'App');
        $subject = (string) $this->config->get('mail.contact_subject', 'Nova mensagem de contacto');

        $emailSubject = $message->subject() ?? $subject;
        $replyTo = $message->email()->value();

        $textBody = $this->textBody($message);
        $htmlBody = $this->htmlBody($message, $fromName);

        try {
            $mail = (new Email())
                ->from(new Address($fromAddress, $fromName))
                ->to($to)
                ->replyTo($replyTo)
                ->subject($emailSubject)
                ->text($textBody)
                ->html($htmlBody);

            $this->mailer->send($mail);
        } catch (Throwable $e) {
            $this->logger->error(sprintf(
                '[mail] contact notification failed for %s: %s',
                $to,
                $e->getMessage()
            ));
        }
    }

    private function textBody(ContactMessage $message): string
    {
        $lines = [
            'Nova mensagem de contacto',
            '',
            'Nome: ' . $message->name(),
            'Email: ' . $message->email()->value(),
        ];

        if ($message->subject() !== null) {
            $lines[] = 'Assunto: ' . $message->subject();
        }

        $lines[] = '';
        $lines[] = $message->body();
        $lines[] = '';
        $lines[] = 'ID: ' . $message->id()->value();
        $lines[] = 'Recebida em: ' . $message->createdAt()->format(DATE_ATOM);

        return implode("\n", $lines);
    }

    private function htmlBody(ContactMessage $message, string $appName): string
    {
        $escName = htmlspecialchars($message->name(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $escEmail = htmlspecialchars($message->email()->value(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $escBody = nl2br(htmlspecialchars($message->body(), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $escApp = htmlspecialchars($appName, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $subjectBlock = $message->subject() !== null
            ? '<p style="margin:0 0 8px 0;font-size:14px;color:#334155;"><strong>Assunto:</strong> '
            . htmlspecialchars($message->subject(), ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</p>'
            : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Nova mensagem de contacto</title></head>
<body style="margin:0;padding:24px;font-family:system-ui,sans-serif;background:#f8fafc;color:#0f172a;">
  <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;padding:24px;border:1px solid #e2e8f0;">
    <p style="margin:0 0 8px 0;font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;">{$escApp}</p>
    <h1 style="margin:0 0 16px 0;font-size:20px;">Nova mensagem de contacto</h1>
    <p style="margin:0 0 8px 0;font-size:14px;color:#334155;"><strong>Nome:</strong> {$escName}</p>
    <p style="margin:0 0 8px 0;font-size:14px;color:#334155;"><strong>Email:</strong> {$escEmail}</p>
    {$subjectBlock}
    <div style="margin:16px 0;padding:16px;background:#f8fafc;border-radius:8px;font-size:14px;line-height:1.6;">{$escBody}</div>
  </div>
</body>
</html>
HTML;
    }
}
