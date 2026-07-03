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
use App\Application\Acl\CreateRole\CreateRoleCommand;
use App\Application\Acl\CreateRole\CreateRoleHandler;
use App\Application\Acl\SyncRolePermissions\SyncRolePermissionsCommand;
use App\Application\Acl\SyncRolePermissions\SyncRolePermissionsHandler;
use App\Application\Acl\SyncUserRoles\SyncUserRolesCommand;
use App\Application\Acl\SyncUserRoles\SyncUserRolesHandler;
use App\Application\User\RegisterUser\RegisterUserCommand;
use App\Application\User\RegisterUser\RegisterUserHandler;
use App\Infrastructure\Acl\InMemoryAclStore;
use App\Infrastructure\Acl\InMemoryEffectivePermissionsProvider;
use App\Infrastructure\Acl\InMemoryPermissionRepository;
use App\Infrastructure\Acl\InMemoryRolePermissionWriter;
use App\Infrastructure\Acl\InMemoryRoleRepository;
use App\Infrastructure\Acl\InMemoryUserRoleRepository;
use App\Infrastructure\Event\NoOpDomainEventPublisher;
use App\Infrastructure\Persistence\User\InMemoryUserRepository;
use App\Infrastructure\Security\NativePasswordHasher;

test('create custom role and assign permissions and user roles', function () {
    $store = InMemoryAclStore::seeded();
    $roles = new InMemoryRoleRepository($store);
    $perms = new InMemoryPermissionRepository($store);
    $userRoles = new InMemoryUserRoleRepository($store, $roles);
    $roleWriter = new InMemoryRolePermissionWriter($store);

    $create = new CreateRoleHandler($roles);
    $syncPerms = new SyncRolePermissionsHandler($roles, $perms, $roleWriter);

    $customId = $create->handle(new CreateRoleCommand('Suporte', 'support'));
    expect($customId)->toMatch('/^[0-9a-f-]{36}$/');

    $syncPerms->handle(new SyncRolePermissionsCommand($customId, ['permissions.view', 'roles.view']));

    $repo = new InMemoryUserRepository();
    $register = new RegisterUserHandler($repo, new NativePasswordHasher(), new NoOpDomainEventPublisher(), $userRoles);
    $userId = $register->handle(new RegisterUserCommand('Bob', 'bob@example.com', 'Secret1a'));

    $syncUser = new SyncUserRolesHandler($repo, $roles, $userRoles);
    $syncUser->handle(new SyncUserRolesCommand($userId, ['support']));

    $effective = new InMemoryEffectivePermissionsProvider($store);
    expect($effective->roleSlugsForUser($userId))->toContain('support');
    expect($effective->permissionSlugsForUser($userId))->toContain('roles.view');
});
