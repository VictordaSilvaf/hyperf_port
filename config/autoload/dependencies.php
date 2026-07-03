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
use App\Application\Project\ProjectPublicCacheInterface;
use App\Application\Project\ProjectViewCounterInterface;
use App\Application\Shared\Security\PasswordHasherInterface;
use App\Application\Storage\ObjectStorageInterface;
use App\Application\Upload\ImageProcessorInterface;
use App\Application\Upload\UploadJobDispatcherInterface;
use App\Domain\Acl\Repository\PermissionRepositoryInterface;
use App\Domain\Acl\Repository\RolePermissionWriterInterface;
use App\Domain\Acl\Repository\RoleRepositoryInterface;
use App\Domain\Acl\Repository\UserRoleRepositoryInterface;
use App\Domain\Category\Repository\CategoryRepositoryInterface;
use App\Domain\Post\Repository\PostRepositoryInterface;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Shared\Event\DomainEventPublisherInterface;
use App\Domain\Tag\Repository\TagRepositoryInterface;
use App\Domain\Technology\Repository\TechnologyRepositoryInterface;
use App\Domain\Upload\Repository\UploadRepositoryInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Infrastructure\Auth\SignedAccessTokenIssuer;
use App\Infrastructure\Cache\ArrayPasswordResetTokenStore;
use App\Infrastructure\Cache\ArrayProjectPublicCache;
use App\Infrastructure\Cache\ArrayProjectViewCounter;
use App\Infrastructure\Cache\RedisPasswordResetTokenStore;
use App\Infrastructure\Cache\RedisProjectPublicCache;
use App\Infrastructure\Cache\RedisProjectViewCounter;
use App\Infrastructure\Event\NoOpDomainEventPublisher;
use App\Infrastructure\Health\ApplicationHealthProbe;
use App\Infrastructure\Health\DatabaseHealthProbe;
use App\Infrastructure\Health\RedisHealthProbe;
use App\Infrastructure\Image\GdImageProcessor;
use App\Infrastructure\Mail\SmtpPasswordResetNotifier;
use App\Infrastructure\Persistence\Acl\DbEffectivePermissionsProvider;
use App\Infrastructure\Persistence\Acl\DbPermissionRepository;
use App\Infrastructure\Persistence\Acl\DbRolePermissionRepository;
use App\Infrastructure\Persistence\Acl\DbRoleRepository;
use App\Infrastructure\Persistence\Acl\DbUserRoleRepository;
use App\Infrastructure\Persistence\Acl\InMemoryAclStore;
use App\Infrastructure\Persistence\Acl\InMemoryEffectivePermissionsProvider;
use App\Infrastructure\Persistence\Acl\InMemoryPermissionRepository;
use App\Infrastructure\Persistence\Acl\InMemoryRolePermissionWriter;
use App\Infrastructure\Persistence\Acl\InMemoryRoleRepository;
use App\Infrastructure\Persistence\Acl\InMemoryUserRoleRepository;
use App\Infrastructure\Persistence\Category\DbCategoryRepository;
use App\Infrastructure\Persistence\Category\InMemoryCategoryRepository;
use App\Infrastructure\Persistence\Post\DbPostRepository;
use App\Infrastructure\Persistence\Post\InMemoryPostRepository;
use App\Infrastructure\Persistence\Project\DbProjectRepository;
use App\Infrastructure\Persistence\Project\InMemoryProjectRepository;
use App\Infrastructure\Persistence\Tag\DbTagRepository;
use App\Infrastructure\Persistence\Tag\InMemoryTagRepository;
use App\Infrastructure\Persistence\Technology\DbTechnologyRepository;
use App\Infrastructure\Persistence\Technology\InMemoryTechnologyRepository;
use App\Infrastructure\Persistence\Upload\DbUploadRepository;
use App\Infrastructure\Persistence\Upload\InMemoryUploadRepository;
use App\Infrastructure\Persistence\User\DbUserRepository;
use App\Infrastructure\Persistence\User\InMemoryUserRepository;
use App\Infrastructure\Queue\AsyncQueueUploadJobDispatcher;
use App\Infrastructure\Queue\SyncUploadJobDispatcher;
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
     * `users` existir (após migrate) e o PostgreSQL estiver configurado.
     */
    InMemoryAclStore::class => static function (): InMemoryAclStore {
        static $store = null;

        return $store ??= InMemoryAclStore::seeded();
    },

    UserRepositoryInterface::class => env('APP_USER_REPOSITORY', 'memory') === 'db'
        ? DbUserRepository::class
        : InMemoryUserRepository::class,

    ProjectRepositoryInterface::class => env('APP_USER_REPOSITORY', 'memory') === 'db'
        ? DbProjectRepository::class
        : InMemoryProjectRepository::class,

    PostRepositoryInterface::class => env('APP_USER_REPOSITORY', 'memory') === 'db'
        ? DbPostRepository::class
        : InMemoryPostRepository::class,

    CategoryRepositoryInterface::class => env('APP_USER_REPOSITORY', 'memory') === 'db'
        ? DbCategoryRepository::class
        : InMemoryCategoryRepository::class,

    TechnologyRepositoryInterface::class => env('APP_USER_REPOSITORY', 'memory') === 'db'
        ? DbTechnologyRepository::class
        : InMemoryTechnologyRepository::class,

    TagRepositoryInterface::class => env('APP_USER_REPOSITORY', 'memory') === 'db'
        ? DbTagRepository::class
        : InMemoryTagRepository::class,

    UploadRepositoryInterface::class => env('APP_USER_REPOSITORY', 'memory') === 'db'
        ? DbUploadRepository::class
        : InMemoryUploadRepository::class,

    ProjectViewCounterInterface::class => env('APP_USER_REPOSITORY', 'memory') === 'db'
        ? RedisProjectViewCounter::class
        : ArrayProjectViewCounter::class,

    ProjectPublicCacheInterface::class => env('APP_USER_REPOSITORY', 'memory') === 'db'
        ? RedisProjectPublicCache::class
        : ArrayProjectPublicCache::class,

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

    ImageProcessorInterface::class => GdImageProcessor::class,

    UploadJobDispatcherInterface::class => static function (ContainerInterface $container): UploadJobDispatcherInterface {
        $useQueue = (bool) \Hyperf\Config\config('upload.queue_processing', true);
        $isDb = env('APP_USER_REPOSITORY', 'memory') === 'db';

        if ($isDb && $useQueue) {
            return $container->get(AsyncQueueUploadJobDispatcher::class);
        }

        return $container->get(SyncUploadJobDispatcher::class);
    },

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
