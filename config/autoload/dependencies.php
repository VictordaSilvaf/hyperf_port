<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use App\Application\Auth\AccessTokenIssuerInterface;
use App\Application\Auth\PasswordReset\PasswordResetNotifierInterface;
use App\Application\Auth\PasswordReset\PasswordResetTokenStoreInterface;
use App\Application\Shared\Security\PasswordHasherInterface;
use App\Domain\Shared\Event\DomainEventPublisherInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Infrastructure\Auth\ArrayPasswordResetTokenStore;
use App\Infrastructure\Auth\RedisPasswordResetTokenStore;
use App\Infrastructure\Auth\SignedAccessTokenIssuer;
use App\Infrastructure\Event\NoOpDomainEventPublisher;
use App\Infrastructure\Mail\SmtpPasswordResetNotifier;
use App\Infrastructure\Persistence\User\DbUserRepository;
use App\Infrastructure\Persistence\User\InMemoryUserRepository;
use App\Infrastructure\Security\NativePasswordHasher;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use function Hyperf\Support\env;

return [
    /*
     * Hexagonal (driven) adapters: troque para DbUserRepository quando a tabela
     * `users` existir (após migrate) e o MySQL estiver configurado.
     */
    UserRepositoryInterface::class => env('APP_USER_REPOSITORY', 'memory') === 'db'
        ? DbUserRepository::class
        : InMemoryUserRepository::class,

    DomainEventPublisherInterface::class => NoOpDomainEventPublisher::class,

    PasswordHasherInterface::class => NativePasswordHasher::class,
    AccessTokenIssuerInterface::class => SignedAccessTokenIssuer::class,
    PasswordResetTokenStoreInterface::class => env('APP_AUTH_RESET_STORE', 'array') === 'redis'
        ? RedisPasswordResetTokenStore::class
        : ArrayPasswordResetTokenStore::class,

    MailerInterface::class => static function (ContainerInterface $container): MailerInterface {
        $config = $container->get(ConfigInterface::class);
        $dsn = (string) $config->get('mail.dsn', '');
        if ($dsn === '') {
            $host = (string) env('MAIL_HOST', '127.0.0.1');
            $port = (int) env('MAIL_PORT', 1025);
            $user = env('MAIL_USERNAME');
            $pass = env('MAIL_PASSWORD');
            $hasAuth = is_string($user) && $user !== '' && $user !== 'null'
                && is_string($pass) && $pass !== '' && $pass !== 'null';
            if ($hasAuth) {
                $dsn = sprintf(
                    'smtp://%s:%s@%s:%d',
                    rawurlencode($user),
                    rawurlencode($pass),
                    $host,
                    $port
                );
            } else {
                $dsn = sprintf('smtp://%s:%d', $host, $port);
            }
        }

        return new Mailer(Transport::fromDsn($dsn));
    },

    PasswordResetNotifierInterface::class => SmtpPasswordResetNotifier::class,
];
