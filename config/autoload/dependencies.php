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
use App\Application\Acl\EffectivePermissionsProviderInterface;
use App\Application\Auth\AccessTokenIssuerInterface;
use App\Application\Auth\PasswordReset\PasswordResetNotifierInterface;
use App\Application\Auth\PasswordReset\PasswordResetTokenStoreInterface;
use App\Application\Health\GetHealth\GetHealthHandler;
use App\Application\Shared\Security\PasswordHasherInterface;
use App\Application\Storage\ObjectStorageInterface;
use App\Domain\Acl\Repository\PermissionRepositoryInterface;
use App\Domain\Acl\Repository\RolePermissionWriterInterface;
use App\Domain\Acl\Repository\RoleRepositoryInterface;
use App\Domain\Acl\Repository\UserRoleRepositoryInterface;
use App\Domain\Shared\Event\DomainEventPublisherInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Infrastructure\Acl\DbEffectivePermissionsProvider;
use App\Infrastructure\Acl\DbPermissionRepository;
use App\Infrastructure\Acl\DbRolePermissionRepository;
use App\Infrastructure\Acl\DbRoleRepository;
use App\Infrastructure\Acl\DbUserRoleRepository;
use App\Infrastructure\Acl\InMemoryAclStore;
use App\Infrastructure\Acl\InMemoryEffectivePermissionsProvider;
use App\Infrastructure\Acl\InMemoryPermissionRepository;
use App\Infrastructure\Acl\InMemoryRolePermissionWriter;
use App\Infrastructure\Acl\InMemoryRoleRepository;
use App\Infrastructure\Acl\InMemoryUserRoleRepository;
use App\Infrastructure\Auth\ArrayPasswordResetTokenStore;
use App\Infrastructure\Auth\RedisPasswordResetTokenStore;
use App\Infrastructure\Auth\SignedAccessTokenIssuer;
use App\Infrastructure\Event\NoOpDomainEventPublisher;
use App\Infrastructure\Health\ApplicationHealthProbe;
use App\Infrastructure\Health\DatabaseHealthProbe;
use App\Infrastructure\Health\RedisHealthProbe;
use App\Infrastructure\Health\StorageHealthProbe;
use App\Infrastructure\Mail\SmtpPasswordResetNotifier;
use App\Infrastructure\Persistence\User\DbUserRepository;
use App\Infrastructure\Persistence\User\InMemoryUserRepository;
use App\Infrastructure\Security\NativePasswordHasher;
use App\Infrastructure\Storage\FlysystemObjectStorage;
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
    InMemoryAclStore::class => static function (): InMemoryAclStore {
        static $store = null;

        return $store ??= InMemoryAclStore::seeded();
    },

    UserRepositoryInterface::class => env('APP_USER_REPOSITORY', 'memory') === 'db'
        ? DbUserRepository::class
        : InMemoryUserRepository::class,

    RoleRepositoryInterface::class => env('APP_USER_REPOSITORY', 'memory') === 'db'
        ? DbRoleRepository::class
        : InMemoryRoleRepository::class,

    PermissionRepositoryInterface::class => env('APP_USER_REPOSITORY', 'memory') === 'db'
        ? DbPermissionRepository::class
        : InMemoryPermissionRepository::class,

    UserRoleRepositoryInterface::class => env('APP_USER_REPOSITORY', 'memory') === 'db'
        ? DbUserRoleRepository::class
        : InMemoryUserRoleRepository::class,

    RolePermissionWriterInterface::class => env('APP_USER_REPOSITORY', 'memory') === 'db'
        ? DbRolePermissionRepository::class
        : InMemoryRolePermissionWriter::class,

    EffectivePermissionsProviderInterface::class => env('APP_USER_REPOSITORY', 'memory') === 'db'
        ? DbEffectivePermissionsProvider::class
        : InMemoryEffectivePermissionsProvider::class,

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

    ObjectStorageInterface::class => FlysystemObjectStorage::class,

    GetHealthHandler::class => static function (ContainerInterface $container): GetHealthHandler {
        $probes = [
            $container->get(ApplicationHealthProbe::class),
        ];

        if (env('APP_USER_REPOSITORY', 'memory') === 'db') {
            $probes[] = $container->get(DatabaseHealthProbe::class);
        }

        if (env('APP_AUTH_RESET_STORE', 'array') === 'redis') {
            $probes[] = $container->get(RedisHealthProbe::class);
        }

        $filesystemDriver = env('FILESYSTEM_DRIVER', 'minio');
        if (in_array($filesystemDriver, ['minio', 'r2'], true)) {
            $probes[] = $container->get(StorageHealthProbe::class);
        }

        return new GetHealthHandler($probes, $container->get(ConfigInterface::class));
    },
];
