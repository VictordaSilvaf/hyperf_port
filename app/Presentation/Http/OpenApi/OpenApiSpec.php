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

namespace App\Presentation\Http\OpenApi;

use Hyperf\Swagger\Annotation as SA;

#[SA\OpenApi(
    openapi: '3.0.3',
    info: new SA\Info(
        title: 'VictorDev API',
        version: '1.0.0',
        description: 'REST API Hyperf 3.x — Bearer auth, RBAC e portfolio. Prefixo `/api/v1`.',
    ),
)]
#[SA\SecurityScheme(
    securityScheme: 'BearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'HMAC',
    description: 'Access token emitido em `/api/v1/auth/login` ou `/register`. Header: `Authorization: Bearer <token>`.',
)]
#[SA\Tag(name: 'Health', description: 'Liveness e readiness')]
#[SA\Tag(name: 'Auth', description: 'Registo, login e gestão de password')]
#[SA\Tag(name: 'Users', description: 'Perfil e utilizadores públicos')]
#[SA\Tag(name: 'Pages (Public)', description: 'Páginas e block types públicos')]
#[SA\Tag(name: 'Portfolio (Public)', description: 'Projetos, taxonomias e pesquisa')]
#[SA\Tag(name: 'Contact (Public)', description: 'Formulário de contacto')]
#[SA\Tag(name: 'Admin Users', description: 'CRUD de utilizadores (RBAC)')]
#[SA\Tag(name: 'Admin RBAC', description: 'Roles e permissões')]
#[SA\Tag(name: 'Admin Uploads', description: 'Upload de ficheiros')]
#[SA\Tag(name: 'Admin Projects', description: 'Gestão de projetos')]
#[SA\Tag(name: 'Admin Pages', description: 'Gestão de páginas e blocos')]
#[SA\Tag(name: 'Admin Site', description: 'Site settings')]
#[SA\Tag(name: 'Admin Contact', description: 'Mensagens de contacto')]
final class OpenApiSpec
{
}
