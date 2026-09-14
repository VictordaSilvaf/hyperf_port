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

/**
 * Helpers for repeating OpenAPI response / security attributes on controllers.
 */
final class OpenApiRefs
{
    public const ERR = '#/components/schemas/ErrorMessage';

    public const VALIDATION = '#/components/schemas/ValidationError';

    public const MESSAGE = '#/components/schemas/MessageResponse';

    public const BEARER = [['BearerAuth' => []]];
}
