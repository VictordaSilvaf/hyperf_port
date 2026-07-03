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

namespace App\Presentation\Http\Requests\Admin;

use Hyperf\Validation\Request\FormRequest;

class PatchProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|min:2|max:200',
            'slug' => 'sometimes|string|min:2|max:200|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'description' => 'sometimes|nullable|string|max:5000',
            'content' => 'sometimes|nullable|string|max:100000',
            'repository_url' => 'sometimes|nullable|url|max:500',
            'demo_url' => 'sometimes|nullable|url|max:500',
            'thumbnail' => 'sometimes|nullable|string|max:500',
            'cover' => 'sometimes|nullable|string|max:500',
            'status' => 'sometimes|in:draft,published,archived',
            'featured' => 'sometimes|boolean',
            'published_at' => 'sometimes|nullable|date',
            'order' => 'sometimes|integer|min:0',
            'categories' => 'sometimes|array',
            'categories.*' => 'uuid',
            'technologies' => 'sometimes|array',
            'technologies.*' => 'uuid',
            'tags' => 'sometimes|array',
            'tags.*' => 'uuid',
        ];
    }
}
