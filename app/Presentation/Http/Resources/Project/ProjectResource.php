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

namespace App\Presentation\Http\Resources\Project;

use App\Application\Project\GetProject\GetProjectResult;

final class ProjectResource
{
    public static function fromResult(GetProjectResult $result): array
    {
        return [
            'id' => $result->id,
            'title' => $result->title,
            'slug' => $result->slug,
            'description' => $result->description,
            'status' => $result->status,
            'sort_order' => $result->sortOrder,
            'image_path' => $result->imagePath,
            'owner_id' => $result->ownerId,
        ];
    }
}
