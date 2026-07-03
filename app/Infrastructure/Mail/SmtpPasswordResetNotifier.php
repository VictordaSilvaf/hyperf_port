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

use App\Application\Auth\PasswordReset\PasswordResetNotifierInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Throwable;

use function Hyperf\Support\env;

final class SmtpPasswordResetNotifier implements PasswordResetNotifierInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ConfigInterface $config,
        private readonly StdoutLoggerInterface $logger,
    ) {
    }

    public function notify(string $email, string $plainToken): void
    {
        if (! (bool) $this->config->get('mail.enabled', true)) {
            return;
        }

        $fromAddress = (string) $this->config->get('mail.from.address', 'noreply@localhost');
        $fromName = (string) $this->config->get('mail.from.name', 'App');
        $subject = (string) $this->config->get('mail.reset_subject', 'Password reset');
        $template = (string) $this->config->get('mail.reset_url_template', '');

        $code = $plainToken;
        $link = $template !== ''
            ? str_replace(
                ['{code}', '{token}', '{email}'],
                [rawurlencode($code), rawurlencode($code), rawurlencode($email)],
                $template
            )
            : '';

        $textBody = $this->textBody($email, $code, $link);
        $htmlBody = $this->htmlBody($email, $code, $link, $fromName);

        try {
            $message = (new Email())
                ->from(new Address($fromAddress, $fromName))
                ->to($email)
                ->subject($subject)
                ->text($textBody)
                ->html($htmlBody);

            $this->mailer->send($message);
        } catch (Throwable $e) {
            $this->logger->error(sprintf('[mail] password reset failed for %s: %s', $email, $e->getMessage()));
        }

        if (filter_var((string) env('APP_LOG_PASSWORD_RESET_TOKEN', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->logger->info(sprintf('[password-reset] email=%s code=%s', $email, $code));
        }
    }

    private function textBody(string $email, string $code, string $link): string
    {
        $lines = [
            'Olá,',
            '',
            'Recebemos um pedido para redefinir a senha da conta associada a este e-mail.',
            '',
            'Seu código de verificação (válido pelo tempo configurado no servidor):',
            $code,
            '',
        ];
        if ($link !== '') {
            $lines[] = 'Ou acesse o link abaixo:';
            $lines[] = $link;
            $lines[] = '';
        }
        $lines[] = 'Se você não solicitou isso, ignore este e-mail com segurança.';

        return implode("\n", $lines);
    }

    private function htmlBody(string $email, string $code, string $link, string $appName): string
    {
        $escEmail = htmlspecialchars($email, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $escCode = htmlspecialchars($code, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $escApp = htmlspecialchars($appName, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $escLink = htmlspecialchars($link, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $buttonBlock = $link !== ''
            ? '<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:24px 0;"><tr><td style="border-radius:8px;background:#2563eb;"><a href="' . $escLink . '" style="display:inline-block;padding:14px 28px;font-family:system-ui,-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;">Redefinir senha</a></td></tr></table>'
            : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Redefinição de senha</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f1f5f9;padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:480px;background:#ffffff;border-radius:12px;box-shadow:0 4px 24px rgba(15,23,42,0.08);overflow:hidden;">
          <tr>
            <td style="padding:28px 32px 8px 32px;">
              <p style="margin:0;font-size:13px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#64748b;">{$escApp}</p>
              <h1 style="margin:12px 0 0 0;font-size:22px;font-weight:700;color:#0f172a;line-height:1.3;">Redefinir sua senha</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:8px 32px 24px 32px;">
              <p style="margin:0 0 16px 0;font-size:15px;line-height:1.6;color:#334155;">Olá,</p>
              <p style="margin:0 0 16px 0;font-size:15px;line-height:1.6;color:#334155;">Use o código abaixo para continuar a redefinição de senha da conta <strong style="color:#0f172a;">{$escEmail}</strong>.</p>
              <div style="margin:24px 0;padding:20px 24px;background:linear-gradient(145deg,#f8fafc 0%,#f1f5f9 100%);border-radius:10px;border:1px solid #e2e8f0;text-align:center;">
                <p style="margin:0 0 8px 0;font-size:12px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;color:#64748b;">Seu código</p>
                <p style="margin:0;font-size:32px;font-weight:700;letter-spacing:0.35em;color:#0f172a;font-family:'SF Mono',Consolas,monospace;">{$escCode}</p>
              </div>
              {$buttonBlock}
              <p style="margin:24px 0 0 0;font-size:13px;line-height:1.5;color:#94a3b8;">Se você não pediu este e-mail, pode ignorá-lo. O código expira em breve por motivos de segurança.</p>
            </td>
          </tr>
          <tr>
            <td style="padding:16px 32px 28px 32px;border-top:1px solid #f1f5f9;">
              <p style="margin:0;font-size:12px;color:#94a3b8;text-align:center;">Enviado automaticamente · não responda</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }
}
