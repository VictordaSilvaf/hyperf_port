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

namespace App\Application\Acl;

interface EffectivePermissionsProviderInterface
{
    /**
     * @return list<string> distinct permission slugs
     */
    public function permissionSlugsForUser(string $userId): array;

    /**
     * @return list<string> role slugs
     */
    public function roleSlugsForUser(string $userId): array;
}
