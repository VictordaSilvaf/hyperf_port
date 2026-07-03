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

namespace App\Presentation\Http\Resources\Post;

use App\Application\Post\GetPost\GetPostResult;

final class PostResource
{
    public static function fromResult(GetPostResult $result): array
    {
        return [
            'id' => $result->id,
            'project_id' => $result->projectId,
            'title' => $result->title,
            'body' => $result->body,
            'status' => $result->status,
        ];
    }
}
