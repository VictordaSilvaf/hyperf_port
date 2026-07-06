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
use function Hyperf\Support\env;

return [
    'mail' => [
        'enabled' => filter_var(env('MAIL_ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN),
        /* DSN Symfony Mailer; vazio monta smtp://MAIL_HOST:MAIL_PORT (Mailpit no Docker). */
        'dsn' => env('MAILER_DSN', ''),
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'noreply@localhost'),
            'name' => env('MAIL_FROM_NAME', 'Hyperf Skeleton'),
        ],
        'reset_subject' => env('MAIL_RESET_SUBJECT', 'Redefinição de senha'),
        'contact_subject' => env('MAIL_CONTACT_SUBJECT', 'Nova mensagem de contacto'),
        'contact_to' => env('MAIL_CONTACT_TO', ''),
        /* Opcional: URL com {code}, {token} (igual ao code) ou {email} no link do e-mail. */
        'reset_url_template' => env('APP_PASSWORD_RESET_URL', ''),
    ],
];
