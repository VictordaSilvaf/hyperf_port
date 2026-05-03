<?php

declare(strict_types=1);

namespace App\Application\Acl\CreateRole;

final class CreateRoleCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
    ) {
    }
}
